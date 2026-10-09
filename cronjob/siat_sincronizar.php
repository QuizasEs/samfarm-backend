<?php

/* Sincronizacion con el SIAT: CUIS, catalogos y CUFD.

   El trabajo se hace SOLO cuando falta algo. Antes este script pedia las tres
   cosas en cada pasada, sin mirar si ya estaban al dia. Eso hacia dos danos:
   un CUFD (que dura 24 h) se rotaba aunque le quedaran 23 h de vida, y se
   descargaban 1430 productos del SIN cada vez. Con la comprobacion previa la
   misma tarea puede correr cada pocos minutos sin gastare nada en el SIN.

   Esa logica es ademas lo que hace que la tarea se recupere sola. Si la
   maquina se apaga un dia entero, al volver la primera pasada ve las fechas
   de caducidad en el pasado y repone lo que falte. No hace falta ningun modo
   "primera vez": la primera ejecucion es simplemente el caso en que no hay
   nada, y se pide todo. */

$peticionAjax = true;
chdir(__DIR__);

require_once dirname(__DIR__) . '/config/SERVER.php';
require_once dirname(__DIR__) . '/config/siat.php';
require_once dirname(__DIR__) . '/models/siatModel.php';
require_once dirname(__DIR__) . '/models/mainModel.php';

/* Bloqueo contra pasada simultanea.

   Sin esto, dos ejecuciones que se pisan (la programada arrancando mientras
   alguien la lanza a mano, o un reinicio de la maquina con la anterior
   todavia viva) piden las dos el mismo CUFD caducado: se gastan dos permisos
   y se descargan dos veces los 1430 productos del catalogo. Solo una puede
   tener el paso.

   Va DESPUES de los require, no antes: config/siat.php es lo que fija la zona
   horaria en America/La_Paz, y el php.ini de esta maquina viene en
   Europe/Berlin. Si se comprobara el bloqueo antes, el mensaje de "ya hay una
   pasada" saldría con seis horas de atraso. */
$lockPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'siat_sincronizar.lock';
$lock = fopen($lockPath, 'c');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
    echo date('Y-m-d H:i:s') . " - SIAT: ya hay una pasada en marcha; esta se sale.\n";
    exit(0);
}

/* Margenes, en minutos.

   El CUFD nuevo dura 24 h. Renovar cuando le quedan menos de 6 h deja
   margen de sobra para un dia entero de caidas sin dejar una venta sin
   facturar. El CUIS dura 365 dias, asi que 24 h de margen solo obliga a
   renovarlo cuando ya esta cerca. */
define('SIAT_SYNC_MARGEN_CUFD_MIN', 300);
define('SIAT_SYNC_MARGEN_CUIS_MIN', 1440);

/* El catalogo del SIN cambia por fuerza, pero no por minuto. Una vez al dia
   sobra y es lo que mantiene fresca la lista de productos y leyendas. */
define('SIAT_SYNC_CATALOGOS_HORAS', 24);

/* Ejecutable por terminal (php cronjob/siat_sincronizar.php 1) o por navegador
   (http://localhost/samfarm-backend/cronjob/siat_sincronizar.php?su_id=1  |  ?all=1) */
$suId = null;
if (isset($argv[1])) {
    $suId = (int) $argv[1];
} elseif (isset($_GET['su_id'])) {
    $suId = (int) $_GET['su_id'];
}

if (isset($_GET['all'])) {
    $procesarTodos = true;            // navegador: procesa todas las sucursales
} elseif ($suId !== null) {
    $procesarTodos = false;           // sucursal concreta (CLI o ?su_id=)
} else {
    $procesarTodos = false;           // terminal sin argumento: sucursal 1 (comportamiento original)
    $suId = 1;
}

/* Minutos que le quedan a una vigencia, o null si no hay fecha guardada. */
function minutosRestantes($vigencia)
{
    if (empty($vigencia)) {
        return null;
    }
    return (int) round((strtotime($vigencia) - time()) / 60);
}

/* true cuando no hay fecha, ya paso, o queda dentro del margen.

   Sin fecha y con margen 0 se cumple, que es justo el caso "primera vez":
   no hay nada guardado, asi que hay que pedirlo. */
function haceFalta($vigencia, $margenMin)
{
    $restan = minutosRestantes($vigencia);
    if ($restan === null) {
        return true;
    }
    return $restan <= $margenMin;
}

/* Ultima sincronizacion de catalogos registrada para la sucursal. Se apoya en
   sc_fecha_siat, que sincronizarCatalogos() actualiza cuando el SIN responde
   bien: una marca local, sin tocar el esquema y sin llamar al SIN para
   comprobar si hay que llamar al SIN. */
function ultimaSyncCatalogos($suId)
{
    $db = mainModel::conectarTransaccional();
    $stmt = $db->prepare("SELECT sc_fecha_siat FROM siat_configuracion WHERE su_id = :su_id");
    $stmt->execute([':su_id' => (int) $suId]);
    $valor = $stmt->fetchColumn();
    return empty($valor) ? null : $valor;
}

/* Vigencia que quedo REALMENTE guardada en la base, no la que devolvio el SIN.

   obtenerCUIS() y obtenerCUFD() devuelven el codigo que les respondio el SIN,
   pero no miran si guardarCUIS()/guardarCUFD() lograron escribirlo: si la
   sucursal no tiene caja, el codigo se queda en el aire y el cron lo
   reportaba como "renovado". Esto relee la base y dice la verdad. */
function vigenciaEnBase($suId, $campo)
{
    $salud = siatModel::estadoCajaParaFacturacion($suId, null);
    return $salud[$campo] ?? null;
}

try {
    $filas = $procesarTodos
        ? siatModel::listarSucursales()
        : [(int) $suId];

    foreach ($filas as $suId) {
        $suId = (int) $suId;

        /* Una sola lectura de la caja por sucursal: de aqui salen las dos
           vigencias y no se vuelve a tocar la base dentro del bucle. */
        $salud = siatModel::estadoCajaParaFacturacion($suId, null);
        $hecho = [];

        // --- 1) CUIS -------------------------------------------------------
        if (haceFalta($salud['cuis_vigencia'], SIAT_SYNC_MARGEN_CUIS_MIN)) {
            try {
                siatModel::obtenerCUIS($suId);
                $guardado = vigenciaEnBase($suId, 'cuis_vigencia');
                if ($guardado && minutosRestantes($guardado) > 0) {
                    $hecho[] = 'CUIS renovado hasta ' . $guardado;
                } else {
                    $hecho[] = 'CUIS NO guardado';
                }
            } catch (Exception $e) {
                error_log("SIAT obtenerCUIS su_id={$suId}: " . $e->getMessage());
                $hecho[] = 'CUIS FALLO';
            }
        } else {
            $hecho[] = 'CUIS vigente hasta ' . $salud['cuis_vigencia'];
        }

        // --- 2) Catalogos --------------------------------------------------
        $ultimaSync = ultimaSyncCatalogos($suId);
        $catalogoVigente = $ultimaSync !== null
            && (time() - strtotime($ultimaSync)) < SIAT_SYNC_CATALOGOS_HORAS * 3600;

        if ($catalogoVigente) {
            $hecho[] = 'catalogos al dia (' . $ultimaSync . ')';
        } else {
            $res = siatModel::sincronizarCatalogos($suId);
            if ($res === false) {
                $hecho[] = 'catalogos FALLO';
            } else {
                $hecho[] = 'catalogos=' . json_encode($res);
            }
        }

        // --- 3) CUFD -------------------------------------------------------
        if (haceFalta($salud['cufd_vigencia'], SIAT_SYNC_MARGEN_CUFD_MIN)) {
            siatModel::obtenerCUFD($suId);
            $guardado = vigenciaEnBase($suId, 'cufd_vigencia');
            if ($guardado && minutosRestantes($guardado) > 0) {
                $hecho[] = 'CUFD renovado hasta ' . $guardado;
            } else {
                $hecho[] = 'CUFD NO guardado';
            }
        } else {
            $hecho[] = 'CUFD vigente hasta ' . $salud['cufd_vigencia'];
        }

        echo date('Y-m-d H:i:s') . " - SIAT su_id={$suId}: " . implode(' | ', $hecho) . "\n";
    }

    exit(0);
} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " - SIAT ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
