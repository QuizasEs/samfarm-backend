<?php
/* Rutas absolutas: los includes relativos fallan cuando el modelo se carga
   desde un script que no vive en el directorio de la app (CLI, cronjob). */
require_once __DIR__ . '/mainModel.php';
require_once __DIR__ . '/../libs/phpqrcode.php';
require_once __DIR__ . '/../libs/xmlseclibs/xmlseclibs.php';

class siatModel extends mainModel
{
    private static function clienteSOAP($servicio)
    {
        $wsdl = SIAT_URLS[SIAT_MODO][$servicio];

        // Autenticación SIAT: el SIN exige el token delegado como HTTP header
        // "apikey: TokenApi <token>" (el WSDL no declara SOAP header). Se envía vía stream_context.
        $token = defined('SIAT_TOKEN') ? SIAT_TOKEN : '';
        $context = stream_context_create([
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            'http' => ['header' => "apikey: TokenApi " . $token . "\r\n"]
        ]);
        $client = new SoapClient($wsdl, [
            'stream_context' => $context,
            'trace' => 1,
            'exceptions' => true,
            'connection_timeout' => 30,
            // SoapClient usa por defecto el stream wrapper https:// de PHP, que
            // en Windows/XAMPP se queda colgado contra el piloto del SIAT
            // (timeout, "Could not connect to host") mientras libcurl conecta
            // sin problema al mismo host. use_curl cambia el transporte.
            'use_curl' => true,
        ]);

        return $client;
    }

    /* procesa la firma digital */
    private static function cargarCertificadoP12()
    {
        $p12Path = SIAT_CERT_P12_PATH;
        $password = SIAT_CERT_PASSWORD;

        /* Soporta tanto P12 (PKCS#12) como PEM directo.
           Si el P12 no existe o falla, intenta leer el archivo PEM como formato
           certificado+clave Privada mixto: certificado -----BEGIN CERTIFICATE-----
           ... clave privada -----BEGIN PRIVATE KEY----- ... ." */
        if (file_exists($p12Path)) {
            $pkcs12 = file_get_contents($p12Path);
            $certs = [];
            if (openssl_pkcs12_read($pkcs12, $certs, $password)) {
                return ['cert' => $certs['cert'], 'pkey' => $certs['pkey']];
            }
            /* Si el P12 no se pudo leer (por ejemplo, formato incorrecto), se cae
               a PEM cuando el nombre del archivo termina en .pem. */
        }

        /* Archivo PEM alternativo .pem: formato mixto "certificado en crudo + clave privada" */
        $pemPath = dirname(__DIR__) . '/firma/CER_MARTIN_HUAYHUA_YANA.pem';
        if (!file_exists($pemPath)) {
            /* Como fallback, el .p12 original puede estar presente pero no ser
               PKCS#12 válido; permitir que el .pem actue como fuente principal. */
            if (!file_exists($p12Path)) {
                throw new Exception("No se encuentra ni P12 ni PEM: $p12Path y $pemPath");
            }
            $pemPath = $p12Path;
        }

        $content = file_get_contents($pemPath);
        $certs = [];
        $privateKey = null;
        $certificate = null;

        /* Dividir por delimitadores de límites */
        $parts = preg_split('/-----.*-----(?:\\r?\\n)?/', $content);
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;
            if (strpos($part, 'BEGIN CERTIFICATE') === 0) {
                $certificate = $part;
            } elseif (strpos($part, 'BEGIN PRIVATE KEY') === 0 || strpos($part, 'BEGIN RSA PRIVATE KEY') === 0 || strpos($part, 'BEGIN ENCRYPTED PRIVATE KEY') === 0) {
                $privateKey = $part;
            }
        }

        if (!$certificate || !$privateKey) {
            throw new Exception("Archivo PEM no contiene certificado y/o clave privada válidos");
        }

        return ['cert' => $certificate, 'pkey' => $privateKey];
    }
    /* manipula la firma usando librerias xml */
    private static function firmarXML($xmlString)
    {
        $certData = self::cargarCertificadoP12();
        $certPem = $certData['cert'];
        $pkeyPem = $certData['pkey'];

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        if (!$dom->loadXML($xmlString)) {
            throw new Exception("No se pudo cargar el XML a firmar");
        }

        /* Estructura, algoritmos y canonicalizacion calcados del ejemplo oficial
           del SIN (facturaElectronicaCompraVenta.xml):
             - <Signature> con namespace por defecto, sin prefijo
             - CanonicalizationMethod: C14N inclusivo
             - Transforms: enveloped-signature + c14n inclusivo con comentarios
             - Reference URI="" (documento completo)
             - SignatureMethod rsa-sha256, DigestMethod sha256
           El SignatureSchema.xsd solo exige que Signature exista, pero el SIN
           valida ademas la firma criptograficamente, asi que se sigue el
           formato de su codigo de referencia. */
        $objDSig = new XMLSecurityDSig('');
        $objDSig->setCanonicalMethod(XMLSecurityDSig::C14N);
        $objDSig->addReference(
            $dom,
            XMLSecurityDSig::SHA256,
            array(
                'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                XMLSecurityDSig::C14N_COMMENTS,
            ),
            array('force_uri' => true)
        );

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));

        /* loadKey() no devuelve valor: hay que capturar la excepcion, no probar
           el retorno, o se pisaria la llave ya cargada. */
        try {
            $objKey->loadKey($pkeyPem, false, false);
        } catch (Throwable $e) {
            throw new Exception("No se pudo cargar la llave privada del certificado: " . $e->getMessage());
        }

        $objDSig->sign($objKey, $dom->documentElement);
        $objDSig->add509Cert($certPem, true, false);

        return $dom->saveXML();
    }

    public static function listarSucursales()
    {
        $db = mainModel::conectar();
        return $db->query("SELECT su_id FROM siat_configuracion ORDER BY su_id")->fetchAll(PDO::FETCH_COLUMN);
    }

    /* Devuelve el par (codigoSucursal, codigoPuntoVenta) para una sucursal.

       LAS DOS MITADES TIENEN QUE SALIR DE LA MISMA CAJA. Antes el CUIS lo
       resolvia leerCUIS(), que va por pv_codigo = 0, mientras este metodo
       elegia la caja con
           ORDER BY (pv_registrado_sin = 1) DESC, (pv_codigo_sin IS NULL), pv_codigo
       o sea la que el SIN tiene registrada. Para su_id=1 eso terminaba
       mandando el CUIS de la caja 0 (5B4D47E3) junto con codigoPuntoVenta=1, y
       el SIN responde

           933 EL PUNTO DE VENTA ES INEXISTENTE O INVALIDO   transaccion=false

       SIN SoapFault y sin exception, asi que el fallo pasaba inadvertido:
       sincronizarCatalogos() devolvia todo en cero y los catalogos quedaban
       vacios, y en la misma razon la validacion de facturas en linea no podia
       funcionar. Es la confusion de identificadores que describe AGENTS.md.

       Por eso ahora ambas cosas salen de obtenerConfiguracionPuntoVenta(), que
       es la unica ruta que resuelve WHERE su_id = ? AND pv_codigo = 0. */
    private static function obtenerCodigosSucursal($suId, $pvId = null)
    {
        $sucursal = self::obtenerCodigoSucursal($suId);

        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);

        $puntoVenta = 0;
        if ($pv) {
            $puntoVenta = $pv->pv_codigo_sin !== null ? (int)$pv->pv_codigo_sin : (int)$pv->pv_codigo;
            if (empty($pv->pv_registrado_sin) || $pv->pv_codigo_sin === null) {
                error_log(
                    "SIAT WARNING: la caja usada para su_id={$suId} no esta registrada en el SIN; " .
                    "codigoPuntoVenta={$puntoVenta} es una aproximacion local."
                );
            }
        } else {
            error_log("SIAT WARNING: su_id={$suId} no tiene filas en puntos_venta; se envia codigoPuntoVenta=0.");
        }

        if ($sucursal === 0) {
            error_log("SIAT WARNING: sc_sucursal_codigo=0 para su_id={$suId}. Actualizar con el codigo real del Portal SIAT.");
        }

        return [
            'sucursal' => $sucursal,
            'punto_venta' => $puntoVenta,
        ];
    }

    public static function obtenerNitEmpresa()
    {
        static $nit = null;
        if ($nit === null) {
            $db = mainModel::conectar();
            $stmt = $db->query("SELECT ce_nit FROM configuracion_empresa WHERE ce_id = 1 LIMIT 1");
            $nit = $stmt->fetchColumn();
            if (!$nit) {
                throw new Exception("SIAT: configuracion_empresa.ce_nit esta vacio");
            }
        }
        return $nit;
    }

    /* ================= PUNTOS DE VENTA =================
       Cada caja tiene su propio CUIS (365 dias) y su propio CUFD (diario).
       El destino es que CUIS/CUFD vivan en puntos_venta, una fila por caja.
       El CUIS de la caja 0 (pv_codigo = 0) es el CUIS de sucursal y es el
       que sirve para registrar las demas cajas ante el SIN.

       MIGRACION POR FASES: hoy las cajas siguen sin credenciales y el unico
       CUIS/CUFD real esta en siat_configuracion. Por eso esta funcion
       devuelve una fila normalizada con las mismas claves que devuelve
       puntos_venta, y rellena desde siat_configuracion lo que la caja aun
       no tenga, marcando pv_origen = 'siat_configuracion' para que quede
       visible que la credencial es heredada y no propia de la caja. */

    /* Resuelve la caja a trabajar. Sin $pvId usa la caja 0 de la sucursal,
       que es el comportamiento historico. */
    public static function obtenerConfiguracionPuntoVenta($suId, $pvId = null)
    {
        $db = mainModel::conectar();

        // pv_cufd_anterior llega con la migracion de eventos significativos.
        // Se lee solo si ya existe para que el modelo funcione en ambos estados.
        $colAnterior = self::tieneColumna($db, 'puntos_venta', 'pv_cufd_anterior')
            ? 'pv_cufd_anterior'
            : 'NULL AS pv_cufd_anterior';

        if ($pvId === null) {
            $stmt = $db->prepare("
                SELECT pv_id, su_id, pv_codigo, pv_codigo_sin, pv_nombre, pv_registrado_sin,
                       pv_cuis, pv_cuis_vigente_hasta, pv_cufd, pv_cufd_control,
                       pv_cufd_vigente_hasta, {$colAnterior}
                FROM puntos_venta
                WHERE su_id = :su_id AND pv_codigo = 0
                LIMIT 1
            ");
            $stmt->execute([':su_id' => $suId]);
        } else {
            $stmt = $db->prepare("
                SELECT pv_id, su_id, pv_codigo, pv_codigo_sin, pv_nombre, pv_registrado_sin,
                       pv_cuis, pv_cuis_vigente_hasta, pv_cufd, pv_cufd_control,
                       pv_cufd_vigente_hasta, {$colAnterior}
                FROM puntos_venta
                WHERE su_id = :su_id AND pv_id = :pv_id
                LIMIT 1
            ");
            $stmt->execute([':su_id' => $suId, ':pv_id' => $pvId]);
        }
        $row = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$row) {
            return null;
        }

        $row->pv_origen = 'puntos_venta';

        // Un CUIS o CUFD placeholder de la migracion inicial no sirve para el
        // SIN, asi que se descarta ANTES de heredar: si no, el placeholder
        // bloquearia el fallback.
        if (!empty($row->pv_cuis) && strpos($row->pv_cuis, 'PRUEBA') !== false) {
            $row->pv_cuis = null;
        }
        if (!empty($row->pv_cufd) && strpos($row->pv_cufd, 'PRUEBA') !== false) {
            $row->pv_cufd = null;
            $row->pv_cufd_control = null;
        }

        // Fase 1 de la migracion: cada credencial cae por separado al valor
        // heredado de la sucursal. Una caja puede tener CUIS propio y aun asi
        // no tener CUFD, porque el CUFD se renueva a diario y el CUIS dura 365.
        if (empty($row->pv_cuis) || empty($row->pv_cufd)) {
            $scStmt = $db->prepare("SELECT sc_cuis, sc_cuis_vigente_hasta, sc_cufd,
                                           sc_cufd_control, sc_cufd_vigente_hasta
                                    FROM siat_configuracion WHERE su_id = :su_id");
            $scStmt->execute([':su_id' => $suId]);
            $sc = $scStmt->fetch(PDO::FETCH_OBJ);

            if ($sc) {
                $heredo = false;

                if (empty($row->pv_cuis) && !empty($sc->sc_cuis)
                    && strpos($sc->sc_cuis, 'PRUEBA') === false) {
                    $row->pv_cuis = $sc->sc_cuis;
                    $row->pv_cuis_vigente_hasta = $sc->sc_cuis_vigente_hasta;
                    $heredo = true;
                }

                if (empty($row->pv_cufd) && !empty($sc->sc_cufd)
                    && strpos($sc->sc_cufd, 'PRUEBA') === false) {
                    $row->pv_cufd = $sc->sc_cufd;
                    $row->pv_cufd_control = $sc->sc_cufd_control;
                    $row->pv_cufd_vigente_hasta = $sc->sc_cufd_vigente_hasta;
                    $heredo = true;
                }

                if ($heredo) {
                    $row->pv_origen = 'siat_configuracion';
                }
            }
        }

        return $row;
    }

    /* Resuelve que caja emite una venta. Prioridad: la caja del usuario, luego
       la caja indicada, luego la caja 0 de la sucursal. */
    public static function resolverPuntoVentaDeUsuario($usId, $suId, $pvId = null)
    {
        $db = mainModel::conectar();

        if ($pvId === null && $usId) {
            $stmt = $db->prepare("
                SELECT pv_id FROM usuarios
                WHERE us_id = :us_id AND su_id = :su_id AND pv_id IS NOT NULL
                LIMIT 1
            ");
            $stmt->execute([':us_id' => $usId, ':su_id' => $suId]);
            $pvId = $stmt->fetchColumn();
        }

        return self::resolverPuntoVenta($suId, $pvId);
    }

    /* Devuelve el pv_id real de la caja, o null si la caja no existe. */
    public static function resolverPuntoVenta($suId, $pvId = null)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        return $pv ? (int)$pv->pv_id : null;
    }

    /* Todo lo que hace falta para emitir con una caja concreta, en una sola
       llamada. Sustituye a que ventaController alcanzara helpers privados
       del modelo, lo que reventaba con un error fatal. */
    public static function obtenerContextoCaja($suId, $pvId = null)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);

        if (!$pv) {
            throw new Exception("SIAT: la sucursal {$suId} no tiene ninguna caja en puntos_venta");
        }

        $codigoPuntoVenta = $pv->pv_codigo_sin !== null
            ? (int)$pv->pv_codigo_sin
            : (int)$pv->pv_codigo;

        return [
            'pv_id'          => (int)$pv->pv_id,
            'pv_codigo'      => (int)$pv->pv_codigo,
            'pv_nombre'      => $pv->pv_nombre,
            'registrado_sin' => (bool)$pv->pv_registrado_sin,
            'origen'         => $pv->pv_origen,
            'cuis'           => $pv->pv_cuis,
            'cufd'           => $pv->pv_cufd,
            'cufd_control'   => $pv->pv_cufd_control,
            'cufd_vigente_hasta' => $pv->pv_cufd_vigente_hasta,
            'codigo_sucursal'    => self::obtenerCodigoSucursal($suId),
            'codigo_punto_venta' => $codigoPuntoVenta,
        ];
    }

    /* Lista las cajas de la sucursal. */
    public static function obtenerPuntosVenta($suId)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            SELECT pv_id, pv_codigo, pv_codigo_sin, pv_nombre, pv_activo, pv_registrado_sin,
                   pv_cuis, pv_cuis_vigente_hasta, pv_cufd, pv_cufd_control, pv_cufd_vigente_hasta
            FROM puntos_venta
            WHERE su_id = :su_id
            ORDER BY pv_codigo
        ");
        $stmt->execute([':su_id' => $suId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /* Codigo de sucursal ante el SIN. Vive en siat_configuracion. */
    public static function obtenerCodigoSucursal($suId)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("SELECT sc_sucursal_codigo FROM siat_configuracion WHERE su_id = :su_id LIMIT 1");
        $stmt->execute([':su_id' => $suId]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ? (int)$row->sc_sucursal_codigo : 0;
    }

    /* CUIS de una caja concreta. */
    public static function obtenerCuisPorPuntoVenta($suId, $pvId = null)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        return ($pv && !empty($pv->pv_cuis)) ? $pv->pv_cuis : false;
    }

    /* CUFD vigente de una caja concreta. */
    public static function obtenerCufdPorPuntoVenta($suId, $pvId = null)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        return ($pv && !empty($pv->pv_cufd)) ? $pv->pv_cufd : false;
    }

    /* Registra una caja ante el SIN y guarda el codigo que este asigna. */
    public static function registrarPuntoVenta($suId, $nombre, $pvId = null)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        if (!$pv) {
            throw new Exception("SIAT registrarPuntoVenta: la caja pv_id=" . var_export($pvId, true) . " no existe");
        }

        $cuis = !empty($pv->pv_cuis) ? $pv->pv_cuis : self::obtenerCuisPorPuntoVenta($suId, null);
        if (!$cuis) {
            throw new Exception("SIAT registrarPuntoVenta: falta el CUIS de sucursal para registrar cajas");
        }

        $client = self::clienteSOAP('operaciones');
        $resp = $client->registroPuntoVenta(['SolicitudRegistroPuntoVenta' => [
            'codigoAmbiente'       => SIAT_AMBIENTE,
            'codigoModalidad'      => SIAT_MODALIDAD,
            'codigoSistema'        => SIAT_COD_SISTEMA,
            'codigoSucursal'       => self::obtenerCodigoSucursal($suId),
            // Catalogo parametrico del SIN para codigoTipoPuntoVenta:
            //   1 Punto Venta Comisionista   2 Punto Venta Ventanilla de Cobranza
            //   3 Punto de Venta Moviles      4 Punto de Venta YPFB
            //   5 Punto de Venta Cajeros      6 Punto de Venta Conjunta
            // La sucursal es el punto de venta 0 y no se registra por este
            // servicio; una caja adicional de una farmacia es un cajero, o sea
            // 5. Mandar 1 (Comisionista) hace que el SIN responda 947.
            'codigoTipoPuntoVenta' => SIAT_TIPO_PUNTO_VENTA,
            'cuis'                 => $cuis,
            'descripcion'          => $nombre,
            'nit'                  => self::obtenerNitEmpresa(),
            'nombrePuntoVenta'     => $nombre,
        ]]);

        $r = $resp->RespuestaRegistroPuntoVenta ?? null;
        if (!$r || empty($r->transaccion) || empty($r->codigoPuntoVenta)) {
            $msgs = [];
            $lista = $r->mensajesList ?? null;
            foreach (self::aLista($lista) as $m) {
                $msgs[] = ($m->codigo ?? '?') . ': ' . ($m->descripcion ?? '?');
            }
            throw new Exception("SIAT registrarPuntoVenta fallo: " . implode(' | ', $msgs));
        }

        $codigoSin = (int)$r->codigoPuntoVenta;
        $db = mainModel::conectar();
        $db->prepare("UPDATE puntos_venta SET pv_codigo_sin = :sin, pv_registrado_sin = 1 WHERE pv_id = :id")
            ->execute([':sin' => $codigoSin, ':id' => $pv->pv_id]);

        return $codigoSin;
    }

    /* Cierra definitivamente una caja ante el SIN. El codigo no se recicra. */
    public static function cerrarPuntoVenta($suId, $pvId)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        if (!$pv) {
            throw new Exception("SIAT cerrarPuntoVenta: caja no encontrada");
        }
        if (empty($pv->pv_codigo_sin)) {
            throw new Exception("SIAT cerrarPuntoVenta: la caja nunca se registro en el SIN");
        }

        $client = self::clienteSOAP('operaciones');
        $resp = $client->cierrePuntoVenta(['SolicitudCierrePuntoVenta' => [
            'codigoAmbiente'   => SIAT_AMBIENTE,
            'codigoPuntoVenta' => (int)$pv->pv_codigo_sin,
            'codigoSistema'    => SIAT_COD_SISTEMA,
            'codigoSucursal'   => self::obtenerCodigoSucursal($suId),
            'cuis'             => self::obtenerCuisPorPuntoVenta($suId, $pvId),
            'nit'              => self::obtenerNitEmpresa(),
        ]]);

        $r = $resp->RespuestaCierrePuntoVenta ?? null;
        if (!$r || empty($r->transaccion)) {
            $msgs = [];
            foreach (self::aLista($r->mensajesList ?? null) as $m) {
                $msgs[] = ($m->codigo ?? '?') . ': ' . ($m->descripcion ?? '?');
            }
            throw new Exception("SIAT cerrarPuntoVenta fallo: " . implode(' | ', $msgs));
        }

        return true;
    }

    /* ================= FIN PUNTOS DE VENTA ================= */

    public static function obtenerCUIS($suId, $codigoSucursal = null, $codigoPuntoVenta = null, $pvId = null)
    {
        $codigos = self::obtenerCodigosSucursal($suId);
        $codigoSucursal = $codigoSucursal ?? $codigos['sucursal'];

        // Si se indica la caja, se pide el CUIS con su codigo propio.
        // Sin $pvId se pide el CUIS de sucursal (punto de venta 0).
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        if ($pv) {
            $codigoPuntoVenta = $pv->pv_codigo_sin !== null
                ? (int)$pv->pv_codigo_sin
                : (int)$pv->pv_codigo;
        }
        $codigoPuntoVenta = $codigoPuntoVenta ?? $codigos['punto_venta'];

        $intentos = 0;
        $maxIntentos = 3;
        $ultimoError = null;

        while ($intentos < $maxIntentos) {
            $intentos++;
            try {
                $client = self::clienteSOAP('codigos');
                $resp = $client->cuis([
                    'SolicitudCuis' => [
                        'codigoAmbiente'   => SIAT_AMBIENTE,
                        'codigoModalidad'  => SIAT_MODALIDAD,
                        'codigoSistema'    => SIAT_COD_SISTEMA,
                        'nit'              => self::obtenerNitEmpresa(),
                        'codigoSucursal'   => $codigoSucursal,
                        'codigoPuntoVenta' => $codigoPuntoVenta
                    ]
                ]);

                $r = $resp->RespuestaCuis;

                


                if (!isset($r->codigo) || trim($r->codigo) === '') {
                    $ultimoError = 'Respuesta CUIS sin codigo';
                    error_log("SIAT obtenerCUIS: {$ultimoError} (intento {$intentos})");
                    if (!empty($r->mensajesList)) {
                        error_log("SIAT obtenerCUIS mensajes: " . json_encode($r->mensajesList));
                    }
                    sleep(1);
                    continue;
                }

                self::guardarCUIS($suId, $r->codigo, $r->fechaVigencia ?? null, $pvId);
                return $r->codigo;
            } catch (SoapFault $e) {
                $ultimoError = $e->getMessage();
                error_log("SIAT obtenerCUIS SoapFault (intento {$intentos}/{$maxIntentos}): " . $ultimoError);
                sleep(1);
            } catch (Exception $e) {
                $ultimoError = $e->getMessage();
                error_log("SIAT obtenerCUIS Exception (intento {$intentos}/{$maxIntentos}): " . $ultimoError);
                sleep(1);
            }
        }

        return false;
    }

    private static function guardarCUIS($suId, $cuis, $vigencia, $pvId = null)
    {
        $pvIdReal = self::resolverPuntoVenta($suId, $pvId);
        if (!$pvIdReal) {
            error_log("SIAT guardarCUIS: no existe la caja para su_id={$suId}");
            return false;
        }

        $db = mainModel::conectar();
        $stmt = $db->prepare("
            UPDATE puntos_venta
            SET pv_cuis = :cuis, pv_cuis_vigente_hasta = :vigencia
            WHERE pv_id = :pv_id
        ");
        return $stmt->execute([
            ':cuis'    => $cuis,
            ':vigencia' => $vigencia,
            ':pv_id'   => $pvIdReal,
        ]);
    }

    private static function aLista($data)
    {
        if ($data === null) {
            return [];
        }
        if (is_array($data)) {
            return $data;
        }
        return [$data];
    }

    private static function leerCUIS($suId, $pvId = null)
    {
        return self::obtenerCuisPorPuntoVenta($suId, $pvId);
    }

    public static function obtenerCUFD($suId, $codigoSucursal = null, $codigoPuntoVenta = null, $pvId = null)
    {
        $codigos = self::obtenerCodigosSucursal($suId);
        $codigoSucursal = $codigoSucursal ?? $codigos['sucursal'];

        // Si se indica la caja, su codigo ante el SIN manda sobre el de sucursal
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        if ($pv) {
            $codigoPuntoVenta = $pv->pv_codigo_sin !== null
                ? (int)$pv->pv_codigo_sin
                : (int)$pv->pv_codigo;
        }
        $codigoPuntoVenta = $codigoPuntoVenta ?? $codigos['punto_venta'];

        $cuis = self::leerCUIS($suId, $pvId);
        if (!$cuis) {
            error_log("SIAT obtenerCUFD: no hay CUIS para su_id={$suId} pv_id=" . var_export($pvId, true));
            return false;
        }

        $intentos = 0;
        $maxIntentos = 3;
        $ultimoError = null;

        while ($intentos < $maxIntentos) {
            $intentos++;
            try {
                $client = self::clienteSOAP('codigos');
                $resp = $client->cufd([
                    'SolicitudCufd' => [
                        'codigoAmbiente'   => SIAT_AMBIENTE,
                        'codigoModalidad'  => SIAT_MODALIDAD,
                        'codigoSistema'    => SIAT_COD_SISTEMA,
                        'nit'              => self::obtenerNitEmpresa(),
                        'cuis'             => $cuis,
                        'codigoSucursal'   => $codigoSucursal,
                        'codigoPuntoVenta' => $codigoPuntoVenta
                    ]
                ]);

                $r = $resp->RespuestaCufd;

                if (!isset($r->codigo) || trim($r->codigo) === '') {
                    $ultimoError = 'Respuesta CUFD sin codigo';
                    error_log("SIAT obtenerCUFD: {$ultimoError} (intento {$intentos})");
                    if (!empty($r->mensajesList)) {
                        error_log("SIAT obtenerCUFD mensajes: " . json_encode($r->mensajesList));
                    }
                    sleep(1);
                    continue;
                }

                self::guardarCUFD($suId, $r->codigo, $r->codigoControl ?? null, $r->fechaVigencia ?? null, $pvId);
                return $r->codigo;
            } catch (SoapFault $e) {
                $ultimoError = $e->getMessage();
                error_log("SIAT obtenerCUFD SoapFault (intento {$intentos}/{$maxIntentos}): " . $ultimoError);
                sleep(1);
            } catch (Exception $e) {
                $ultimoError = $e->getMessage();
                error_log("SIAT obtenerCUFD Exception (intento {$intentos}/{$maxIntentos}): " . $ultimoError);
                sleep(1);
            }
        }

        return false;
    }

    public static function cufdVigente($suId, $pvId = null)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        if (!$pv || empty($pv->pv_cufd_vigente_hasta)) {
            return false;
        }
        return strtotime($pv->pv_cufd_vigente_hasta) > time();
    }

    private static function guardarCUFD($suId, $codigo, $control, $vigencia, $pvId = null)
    {
        $pvIdReal = self::resolverPuntoVenta($suId, $pvId);
        if (!$pvIdReal) {
            error_log("SIAT guardarCUFD: no existe la caja para su_id={$suId}");
            return false;
        }

        $db = mainModel::conectar();
        $stmt = $db->prepare("
            UPDATE puntos_venta
            SET pv_cufd = :codigo, pv_cufd_control = :control, pv_cufd_vigente_hasta = :vigencia
            WHERE pv_id = :pv_id
        ");
        $stmt->execute([
            ':codigo'   => $codigo,
            ':control'  => $control,
            ':vigencia' => $vigencia,
            ':pv_id'    => $pvIdReal
        ]);
        return true;
    }

    private static function dec2hexString($dec)
    {
        $dec = ltrim((string) $dec, '0');
        if ($dec === '') {
            return '0';
        }
        $hexChars = '0123456789ABCDEF';
        $num = $dec;
        $hex = '';
        while ($num !== '' && $num !== '0') {
            $rem = 0;
            $quot = '';
            for ($i = 0; $i < strlen($num); $i++) {
                $cur = $rem * 10 + (int) $num[$i];
                $q = intdiv($cur, 16);
                $rem = $cur % 16;
                if ($quot !== '' || $q !== 0) {
                    $quot .= $q;
                }
            }
            $hex = $hexChars[$rem] . $hex;
            $num = $quot;
        }
        return $hex;
    }

    public static function generarCUF($nit, $fechaHora, $nroFactura, $codigoControl, $codigoSucursal = 0, $puntoVenta = 0, $tipoEmision = 1, $tipoFactura = 1, $tipoDocumentoSector = 1)
    {
        if (!ctype_digit((string) $nroFactura)) {
            error_log("SIAT generarCUF: nroFactura no numerico: {$nroFactura}");
            return false;
        }

        $nit = str_pad((string) $nit, 13, '0', STR_PAD_LEFT);

        $dt = new DateTime($fechaHora, new DateTimeZone('America/La_Paz'));
        $fecha = $dt->format('YmdHis') . $dt->format('u');
        $fecha = substr($fecha, 0, 17);

        $suc = str_pad((string) $codigoSucursal, 4, '0', STR_PAD_LEFT);
        $puntoVenta = str_pad((string) $puntoVenta, 4, '0', STR_PAD_LEFT);
        $nro = str_pad((string) $nroFactura, 10, '0', STR_PAD_LEFT);
        $modalidad = str_pad((string) SIAT_MODALIDAD, 1, '0', STR_PAD_LEFT);
        $tipoEmision = str_pad((string) $tipoEmision, 1, '0', STR_PAD_LEFT);
        $tipoFactura = str_pad((string) $tipoFactura, 1, '0', STR_PAD_LEFT);
        $tipoDocumentoSector = str_pad((string) $tipoDocumentoSector, 2, '0', STR_PAD_LEFT);

        $cadena = $nit . $fecha . $suc . $modalidad . $tipoEmision . $tipoFactura . $tipoDocumentoSector . $nro . $puntoVenta;

        $suma = 0;
        $multi = 2;
        for ($i = strlen($cadena) - 1; $i >= 0; $i--) {
            $suma += (int) $cadena[$i] * $multi;
            $multi = ($multi == 9) ? 2 : $multi + 1;
        }
        $resto = $suma % 11;
        // El digito verificador del CUF es el resto tal cual, con 10 -> 1 y
        // 11 -> 0. Calcularlo como 11 - resto (que es lo habitual en otros
        // padrones) produce un CUF distinto del que espera el SIN, que responde
        // 1002 "EL CUF ENVIADO EN EL XML ES INVALIDO CUF esperado ... enviado
        // ...": los dos CUF solo difieren en el ultimo digito, el del verificador.
        $verif = $resto >= 10 ? ($resto === 10 ? 1 : 0) : $resto;

        $cadena54 = $cadena . $verif;
        $hex = self::dec2hexString($cadena54);

        return strtoupper($hex) . $codigoControl;
    }

    private static function tieneColumna($db, $tabla, $columna)
    {
        $dbname = $db->query("SELECT DATABASE()")->fetchColumn();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM information_schema.columns
            WHERE table_schema = :db AND table_name = :tabla AND column_name = :col
        ");
        $stmt->execute([':db' => $dbname, ':tabla' => $tabla, ':col' => $columna]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function obtenerDatosFacturaXML($veId)
    {
        $db = mainModel::conectar();
        // El CUFD va en la caja que emite, no en la sucursal. La caja se
        // resuelve por usuarios.pv_id del operador que vendio.
        $stmt = $db->prepare("
            SELECT v.ve_total, v.ve_fecha_emision AS ve_fecha,
                   v.ve_codigo_moneda, v.ve_tipo_cambio,
                   c.cl_carnet, c.cl_nombres, c.cl_apellido_paterno, c.cl_id,
                   c.cl_tipo_documento,
                   ce.ce_nit, ce.ce_nombre, ce.ce_direccion, ce.ce_telefono,
                   ce.ce_municipio, ce.ce_codigo_actividad,
                   f.fa_numero_control, f.fa_cuf,
                   f.fa_numero_siat, f.fa_cufd, f.fa_cufd_control,
                   f.fa_leyenda, f.fa_monto_sujeto_iva, f.fa_descuento_adicional,
                   f.fa_codigo_metodo_pago, f.fa_numero_tarjeta,
                   f.fa_codigo_moneda, f.fa_tipo_cambio,
                   f.fa_doc_sector, f.fa_codigo_emision, f.fa_tipo_factura, f.fa_modalidad,
                   pv.pv_id, pv.pv_codigo, pv.pv_codigo_sin, pv.pv_nombre,
                   pv.pv_cuis, pv.pv_cufd, pv.pv_cufd_control,
                   sc.sc_cuis   AS sc_cuis,
                   sc.sc_cufd   AS sc_cufd,
                   sc.sc_cufd_control AS sc_cufd_control,
                   sc.sc_sucursal_codigo,
                   u.us_nombres AS us_nombre,
                   v.su_id
            FROM ventas v
            JOIN factura f ON f.ve_id = v.ve_id
            LEFT JOIN clientes c ON c.cl_id = v.cl_id
            JOIN configuracion_empresa ce ON ce.ce_id = 1
            JOIN siat_configuracion sc ON sc.su_id = v.su_id
            LEFT JOIN usuarios u ON u.us_id = v.us_id
            LEFT JOIN puntos_venta pv ON pv.pv_id = u.pv_id
            WHERE v.ve_id = :ve_id
            LIMIT 1
        ");
        $stmt->execute([':ve_id' => $veId]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return $fila;
        }

        // Fase 1 de la migracion: si la caja todavia no tiene CUFD propio,
        // se usa el heredado de la sucursal. Al registrar las cajas en el
        // SIN este bloque dejara de ser necesario.
        if (empty($fila['pv_cufd']) && !empty($fila['sc_cufd'])) {
            $fila['pv_cufd'] = $fila['sc_cufd'];
            $fila['pv_cufd_control'] = $fila['sc_cufd_control'];
            $fila['cufd_origen'] = 'siat_configuracion';
        } else {
            $fila['cufd_origen'] = 'puntos_venta';
        }
        if (empty($fila['pv_cuis']) && !empty($fila['sc_cuis'])) {
            $fila['pv_cuis'] = $fila['sc_cuis'];
        }

        return $fila;
    }

    public static function obtenerDetalleFacturaXML($veId)
    {
        $db = mainModel::conectar();
        $sql = "
            SELECT dv.dv_cantidad, dv.dv_precio_unitario, dv.dv_descuento, dv.dv_subtotal,
                   dv.dv_unidad_medida_sin,
                   m.med_id, m.med_nombre_quimico AS med_nombre
            FROM detalle_venta dv
            JOIN medicamento m ON m.med_id = dv.med_id
            WHERE dv.ve_id = :ve_id AND dv.dv_estado = 1
        ";
        if (self::tieneColumna($db, 'medicamento', 'med_codigo_sin')) {
            $sql = str_replace(
                'm.med_nombre_quimico AS med_nombre',
                'm.med_nombre_quimico AS med_nombre, m.med_codigo_sin AS codigo_sin',
                $sql
            );
        }
        $stmt = $db->prepare($sql);
        $stmt->execute([':ve_id' => $veId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Traduce el metodo de pago de la base de datos al codigo numerico que
       exige el XSD.

       La columna factura.fa_codigo_metodo_pago es un varchar(20) y en las
       facturas existentes guarda texto ('efectivo'). El XSD define
       codigoMetodoPago como un entero entre 1 y 308, asi que mandar el texto
       crudo produce un documento que no valida. Catalogos del SIN:
       1 Efectivo, 2 Tarjeta, 3 Codigo QR, 4 Transferencia bancaria, 5 Cheque.

       Se traduce aqui en lugar de forzar un UPDATE sobre datos ya emitidos,
       porque el texto guardado es valido como referencia interna. */
    public static function codigoMetodoPago($valor)
    {
        $texto = strtolower(trim((string)$valor));

        $mapa = [
            ''                   => 1,
            'efectivo'           => 1,
            'contado'            => 1,
            'tarjeta'            => 2,
            'tarjeta debito'     => 2,
            'tarjeta credito'    => 2,
            'debito'             => 2,
            'credito'            => 2,
            'codigo qr'          => 3,
            'codigoqr'           => 3,
            'qr'                 => 3,
            'transferencia'      => 4,
            'transferencia bancaria' => 4,
            'cheque'             => 5,
        ];

        // Un valor que ya venga numerico se respeta siempre que este en rango.
        if ($texto !== '' && ctype_digit($texto)) {
            $n = (int)$texto;
            if ($n >= 1 && $n <= 308) {
                return $n;
            }
        }

        return $mapa[$texto] ?? 1;
    }

    /* Numero de factura que debe viajar en el XML.

       Este es el correlativo real de facturacion, el mismo que va embebido
       dentro del CUF. Viene de la tabla factura_secuencia y se guarda en
       factura.fa_numero_siat.

       No se debe usar fa_numero_control: esa columna guarda un codigo interno
       con formato de texto ("F-1-20260930024528"), y mandarlo en
       <numeroFactura> haria que el numero del XML no coincida con el que
       contiene el CUF, que el SIN rechaza. */
    private static function numeroFacturaXml($datos)
    {
        if (!empty($datos['fa_numero_siat'])) {
            return (int)$datos['fa_numero_siat'];
        }

        // Sin correlativo asignado todavia: no se inventa ninguno aqui. El
        // numero lo asigna quien emite (el flujo de venta, antes de generar el
        // XML), porque es el que consume la secuencia y arma el CUF.
        return '';
    }

    public static function generarXML($datos, $detalles, $leyenda, $suId, $pvId = null)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);

        if ($pv) {
            $sucursal   = self::obtenerCodigoSucursal($suId);
            $puntoVenta = $pv->pv_codigo_sin !== null
                ? (int)$pv->pv_codigo_sin
                : (int)$pv->pv_codigo;
        } else {
            $codigos = self::obtenerCodigosSucursal($suId);
            $sucursal   = $codigos['sucursal'];
            $puntoVenta = $codigos['punto_venta'];
        }

        // Usar datos de la BD en lugar de hardcoded
        $municipio = $datos['ce_municipio'] ?? 'La Paz';
        // La actividad se resuelve contra el catalogo del SIN (etapa 2) en vez
        // de mandarse la que trae configuracion_empresa, que es 477000 y no
        // existe en el catalogo. Ver resolverActividadEconomica().
        $codigoActividad = self::resolverActividadEconomica($datos['ce_codigo_actividad'] ?? null);
        $codigoMoneda = $datos['fa_codigo_moneda'] ?? ($datos['ve_codigo_moneda'] ?? '1');
        $tipoCambio = $datos['fa_tipo_cambio'] ?? ($datos['ve_tipo_cambio'] ?? 1.0000);
        $docSector = $datos['fa_doc_sector'] ?? '1';
        $tipoFactura = $datos['fa_tipo_factura'] ?? 'factura';
        // El tipo de emision NO va en el XML: no existe en el XSD de la
        // factura. Vive codificado dentro del CUF y, en el sobre SOAP, en
        // codigoEmision. Se mantiene la variable solo para el CUF, que se
        // genera aparte.
        $modalidad = $datos['fa_modalidad'] ?? SIAT_MODALIDAD;

        // Tipo documento cliente desde BD
        $carnet = $datos['cl_carnet'] ?? '0';
        $tipoDocMap = ['CI' => 1, 'NIT' => 5, 'CE' => 4, 'Pasaporte' => 3];
        $tipoDoc = $tipoDocMap[$datos['cl_tipo_documento'] ?? 'CI'] ?? 1;

        // fa_cufd es el CUFD con el que se emitio la factura: manda sobre el
        // vigente de la caja, que pudo rotar entre la venta y el envio.
        $cufdXml = $datos['fa_cufd'] ?: ($datos['pv_cufd'] ?? ($datos['sc_cufd'] ?? ''));

        $nombre = trim(($datos['cl_nombres'] ?? '') . ' ' . ($datos['cl_apellido_paterno'] ?? ''));
        if ($nombre === '') { $nombre = 'SIN NOMBRE'; }

        /* La modalidad de este sistema es Electronica en Linea (2), asi que el
           documento va contra facturaElectronicaCompraVenta.xsd y NO contra el
           de la modalidad Computarizada. Con la raiz computarizada el SIN
           rechazaba la factura: ademas de la raiz, faltaban montoGiftCard,
           codigoExcepcion y cafc, sobraba codigoEmision (que el XSD no
           define) y descuentoAdicional iba antes de codigoMoneda cuando el
           esquema lo exige despues de montoTotalMoneda. El XSD es una
           xs:sequence, asi que el orden tambien es obligatorio.

           El documento se arma como texto y no con SimpleXMLElement porque
           hace falta emitir <campo xsi:nil="true"/>, y addChild() no permite
           agregar el atributo xsi:nil. Ademas asi se escapan los textos con
           ENT_XML1: un nombre de cliente con & o < rompe el XML. */

        // complemento y numeroTarjeta son nillable pero con tipo restringido
        // (numeroTarjeta es un entero 0..9999999999999999), asi que una cadena
        // vacia NO valida: hay que mandar el elemento nulo con xsi:nil.
        $complemento = trim((string)($datos['complemento'] ?? ''));
        $complementoXml = $complemento !== ''
            ? '<complemento>' . htmlspecialchars($complemento, ENT_XML1) . '</complemento>'
            : '<complemento xsi:nil="true"/>';

        $tarjeta = trim((string)($datos['fa_numero_tarjeta'] ?? ''));
        $tarjetaXml = ($tarjeta !== '' && $tarjeta !== '0')
            ? '<numeroTarjeta>' . $tarjeta . '</numeroTarjeta>'
            : '<numeroTarjeta xsi:nil="true"/>';

        $descuento = $datos['fa_descuento_adicional'] ?? 0.00;
        $descuentoXml = ((float)$descuento > 0)
            ? '<descuentoAdicional>' . $descuento . '</descuentoAdicional>'
            : '<descuentoAdicional xsi:nil="true"/>';

        // La leyenda es obligatoria: el XSD exige minLength 1, asi que una
        // cadena vacia invalida el documento. La tabla siat_leyendas puede
        // estar vacia, y en ese caso obtener_leyenda_siat_model() devuelve ''
        // (cadena vacia, NO null), por eso el fallback tiene que comprobar el
        // emptiness y no solo el null.
        $textoLeyenda = trim((string)$leyenda);
        if ($textoLeyenda === '') {
            $textoLeyenda = trim((string)($datos['fa_leyenda'] ?? ''));
        }
        if ($textoLeyenda === '') {
            $textoLeyenda = 'Ley N 453: Tienes derecho a recibir informacion sobre las caracteristicas y contenidos de los servicios que utilices.';
        }

        $cabeceraXml =
            '<cabecera>' .
            '<nitEmisor>' . htmlspecialchars((string)$datos['ce_nit'], ENT_XML1) . '</nitEmisor>' .
            '<razonSocialEmisor>' . htmlspecialchars((string)($datos['razon_social'] ?? $datos['ce_nombre']), ENT_XML1) . '</razonSocialEmisor>' .
            '<municipio>' . htmlspecialchars((string)$municipio, ENT_XML1) . '</municipio>' .
            '<telefono>' . htmlspecialchars((string)($datos['ce_telefono'] ?? ''), ENT_XML1) . '</telefono>' .
            '<numeroFactura>' . htmlspecialchars((string)self::numeroFacturaXml($datos), ENT_XML1) . '</numeroFactura>' .
            '<cuf>' . htmlspecialchars((string)$datos['fa_cuf'], ENT_XML1) . '</cuf>' .
            // fa_cufd es el CUFD con el que se emitio la factura: manda sobre
            // el vigente de la caja, que pudo rotar entre venta y envio.
            '<cufd>' . htmlspecialchars((string)$cufdXml, ENT_XML1) . '</cufd>' .
            '<codigoSucursal>' . $sucursal . '</codigoSucursal>' .
            '<direccion>' . htmlspecialchars((string)($datos['ce_direccion'] ?? ''), ENT_XML1) . '</direccion>' .
            '<codigoPuntoVenta>' . $puntoVenta . '</codigoPuntoVenta>' .
            '<fechaEmision>' . date('c', strtotime($datos['ve_fecha'])) . '</fechaEmision>' .
            '<nombreRazonSocial>' . htmlspecialchars($nombre, ENT_XML1) . '</nombreRazonSocial>' .
            '<codigoTipoDocumentoIdentidad>' . $tipoDoc . '</codigoTipoDocumentoIdentidad>' .
            '<numeroDocumento>' . htmlspecialchars((string)($carnet !== '' ? $carnet : '0'), ENT_XML1) . '</numeroDocumento>' .
            $complementoXml .
            '<codigoCliente>' . ($datos['cl_id'] ?? 0) . '</codigoCliente>' .
            '<codigoMetodoPago>' . self::codigoMetodoPago($datos['fa_codigo_metodo_pago'] ?? 1) . '</codigoMetodoPago>' .
            $tarjetaXml .
            '<montoTotal>' . $datos['ve_total'] . '</montoTotal>' .
            '<montoTotalSujetoIva>' . ($datos['fa_monto_sujeto_iva'] ?? $datos['ve_total']) . '</montoTotalSujetoIva>' .
            '<codigoMoneda>' . $codigoMoneda . '</codigoMoneda>' .
            '<tipoCambio>' . $tipoCambio . '</tipoCambio>' .
            '<montoTotalMoneda>' . $datos['ve_total'] . '</montoTotalMoneda>' .
            '<montoGiftCard xsi:nil="true"/>' .
            $descuentoXml .
            '<codigoExcepcion xsi:nil="true"/>' .
            // El CAFC solo aplica a facturas manuales de contingencia
            // impresas por una imprenta autorizada. En emision por sistema
            // va nulo.
            '<cafc xsi:nil="true"/>' .
            '<leyenda>' . htmlspecialchars($textoLeyenda, ENT_XML1) . '</leyenda>' .
            '<usuario>' . htmlspecialchars((string)($datos['us_nombre'] ?? ''), ENT_XML1) . '</usuario>' .
            '<codigoDocumentoSector>1</codigoDocumentoSector>' .
            '</cabecera>';

        $detallesXml = '';
        foreach ($detalles as $d) {
            // codigoProductoSin se busca en siat_productos (etapa 2) en vez de
            // mandar siempre 99900. Ver resolverCodigoProductoSIN().
            $codigoSin = trim((string)($d['codigo_sin'] ?? ''));
            if ($codigoSin === '') {
                $codigoSin = (string)(self::resolverCodigoProductoSIN(
                    $d['med_nombre'] ?? '',
                    $codigoActividad
                ) ?? '99900');
            }

            $detallesXml .=
                '<detalle>' .
                '<actividadEconomica>' . htmlspecialchars((string)$codigoActividad, ENT_XML1) . '</actividadEconomica>' .
                '<codigoProductoSin>' . htmlspecialchars($codigoSin, ENT_XML1) . '</codigoProductoSin>' .
                '<codigoProducto>' . htmlspecialchars((string)$d['med_id'], ENT_XML1) . '</codigoProducto>' .
                '<descripcion>' . htmlspecialchars((string)$d['med_nombre'], ENT_XML1) . '</descripcion>' .
                '<cantidad>' . $d['dv_cantidad'] . '</cantidad>' .
                '<unidadMedida>' . ($d['dv_unidad_medida_sin'] ?? 1) . '</unidadMedida>' .
                '<precioUnitario>' . $d['dv_precio_unitario'] . '</precioUnitario>' .
                '<montoDescuento>' . ($d['dv_descuento'] ?? 0) . '</montoDescuento>' .
                '<subTotal>' . $d['dv_subtotal'] . '</subTotal>' .
                '<numeroSerie></numeroSerie>' .
                '<numeroImei></numeroImei>' .
                '</detalle>';
        }

        $xmlSinFirmar =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<facturaElectronicaCompraVenta ' .
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
            'xsi:noNamespaceSchemaLocation="facturaElectronicaCompraVenta.xsd">' .
            $cabeceraXml . $detallesXml .
            '</facturaElectronicaCompraVenta>';

        return self::firmarXML($xmlSinFirmar);
    }

    public static function enviarFactura($xmlString, $faId, $cuf, $suId, $pvId = null)
    {
        try {
            return self::enviarFacturaOnline($xmlString, $faId, $cuf, $suId, $pvId);
        } catch (Exception $e) {
            error_log("SIAT enviarFactura fallo, activando contingencia fa_id={$faId}: " . $e->getMessage());
            self::emitirEnContingencia($faId, $cuf, $xmlString, $suId, $pvId);
            return null;
        }
    }

    private static function enviarFacturaOnline($xmlString, $faId, $cuf, $suId, $pvId = null)
    {
        // El CUIS y el CUFD son los de la caja que emite la factura
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        if (!$pv || empty($pv->pv_cuis) || empty($pv->pv_cufd)) {
            throw new Exception("SIAT enviarFactura: la caja no tiene CUIS o CUFD para su_id={$suId}");
        }

        $codigoSucursal   = self::obtenerCodigoSucursal($suId);
        $codigoPuntoVenta = $pv->pv_codigo_sin !== null
            ? (int)$pv->pv_codigo_sin
            : (int)$pv->pv_codigo;

        $xmlGzip = gzencode($xmlString, 9);
        // OJO: 'archivo' es xs:base64Binary y SoapClient ya codifica en base64
        // lo que se le pasa. Si se le entrega el base64, se va doblemente
        // codificado y el SIN recibe texto en vez del gzip, con lo que responde
        // 920 "EL PARAMETRO ARCHIVO ES INVALIDO No se desempaqueto XMLs". Se
        // manda el binario crudo.
        $xmlB64 = $xmlGzip;
        $hash = hash('sha256', $xmlGzip);

        $client = self::clienteSOAP('compra_venta');
        $resp = $client->recepcionFactura([
            'SolicitudServicioRecepcionFactura' => [
                'codigoAmbiente'       => SIAT_AMBIENTE,
                'codigoModalidad'      => SIAT_MODALIDAD,
                'codigoSistema'        => SIAT_COD_SISTEMA,
                'nit'                  => self::obtenerNitEmpresa(),
                'cuis'                 => $pv->pv_cuis,
                'cufd'                 => $pv->pv_cufd,
                'codigoSucursal'       => $codigoSucursal,
                'codigoPuntoVenta'     => $codigoPuntoVenta,
                'archivo'              => $xmlB64,
                'hashArchivo'          => $hash,
                // Mismo criterio que enviarPaqueteContingencia(): sin desfase,
                // porque el parser del SIN no acepta fraccion + offset.
                'fechaEnvio'           => date('Y-m-d\TH:i:s.000'),
                'tipoFacturaDocumento' => 1,
                'codigoEmision'        => 1,
                'codigoDocumentoSector' => 1
            ]
        ]);

        $r = $resp->RespuestaServicioFacturacion;
        $estado = $r->codigoEstado ?? null;
        $ticket = $r->codigoRecepcion ?? null;

        $estadoLocal = ($estado == 908) ? 'VALIDADO' : 'RECHAZADO';
        $mensajes = isset($r->mensajesList) ? json_encode($r->mensajesList) : null;

        // fe_cuf ya no existe: el CUF se guarda en factura.fa_cuf.
        // En facturacion_electronica queda el codigo de recepcion del SIN.
        $db = mainModel::conectar();
        $ins = $db->prepare("
            INSERT INTO facturacion_electronica
            (fa_id, fe_codigo_recepcion, fe_estado_siat, fe_ticket, fe_fecha_envio, fe_payload, fe_tipo_emision,
             fe_estado_local, fe_mensajes, fe_intentos, fe_hash_archivo, fe_ultimo_intento)
            VALUES (:fa_id, :recepcion, :estado, :ticket, NOW(), :payload, 1,
                    :estado_local, :mensajes, 0, :hash, NOW())
        ");
        $ins->execute([
            ':fa_id'        => $faId,
            ':recepcion'    => $ticket,
            ':estado'       => $estado,
            ':ticket'       => $ticket,
            ':payload'      => $xmlString,
            ':estado_local' => $estadoLocal,
            ':mensajes'     => $mensajes,
            ':hash'         => $hash,
        ]);

        if ($estado == 908) {
            $db->prepare("UPDATE factura SET fa_estado = 1 WHERE fa_id = :fa_id")
                ->execute([':fa_id' => $faId]);
        }

        return $r;
    }

    /* Registra la factura electronica en la base de datos SIN enviarla al SIN.

       Se usa mientras no se tenga firma de piloto valida. El objetivo es dejar
       la factura completa y trazable en la base (CUF, CUFD, XML firmado) tal
       como quedaria si se hubiera enviado, para poder revisar y despues
       transmitir en lote.

       No hace ninguna llamada SOAP y no toca la red. Es el equivalente local de
       enviarFactura().

       A diferencia de emitirEnContingencia(), aqui la emision es ONLINE
       (fe_tipo_emision = 1), porque no se trata de una contingencia real: la
       factura sale con normalidad y lo unico que falta es la transmision.

       facturacion_electronica tiene UNIQUE ux_fe_fa_id, asi que solo puede
       haber una fila por factura. Si ya existe, no se inserta otra: se
       actualiza. Eso hace el metodo idempotente, a diferencia de
       emitirEnContingencia(). */
    public static function registrarFacturaLocal($faId, $xmlString, $motivo = null)
    {
        $db = mainModel::conectar();
        $xmlGzip = gzencode($xmlString, 9);
        $hash = hash('sha256', $xmlGzip);

        $existe = $db->prepare("SELECT fe_id FROM facturacion_electronica
                                WHERE fa_id = :fa_id LIMIT 1");
        $existe->execute([':fa_id' => $faId]);
        $feId = (int)$existe->fetchColumn();

        if ($feId > 0) {
            // Ya habia una fila (por ejemplo de un intento anterior). Se
            // refresca el payload para que quede el XML vigente, sin duplicar.
            $db->prepare("
                UPDATE facturacion_electronica
                SET fe_payload = :payload, fe_hash_archivo = :hash, fe_ultimo_intento = NOW()
                WHERE fe_id = :fe_id
            ")->execute([
                ':payload' => $xmlString,
                ':hash'    => $hash,
                ':fe_id'   => $feId,
            ]);
            return $feId;
        }

        $mensaje = $motivo ?: 'Generada localmente, pendiente de envio al SIN.';

        /* fe_estado_local tiene que quedar en 'PENDIENTE': es el unico valor que
           procesarColaEnvio() selecciona (WHERE fe_estado_local = 'PENDIENTE').
           Aqui se escribia 'PENDIENTE_ENVIO', un estado que ninguna otra parte
           del codigo produce ni vuelve a poner, asi que la factura entraba en
           la cola y el drenador no la veia nunca: se quedaba ahi para siempre
           sin un solo intento de envio. */
        $ins = $db->prepare("
            INSERT INTO facturacion_electronica
            (fa_id, fe_estado_siat, fe_payload, fe_tipo_emision, fe_estado_local,
             fe_mensajes, fe_intentos, fe_hash_archivo, fe_ultimo_intento)
            VALUES (:fa_id, 'SIN_ENVIAR', :payload, 1, 'PENDIENTE',
                    :msgs, 0, :hash, NOW())
        ");
        $ins->execute([
            ':fa_id'   => $faId,
            ':payload' => $xmlString,
            ':msgs'    => json_encode(['descripcion' => $mensaje]),
            ':hash'    => $hash,
        ]);

        return (int)$db->lastInsertId();
    }

    public static function emitirEnContingencia($faId, $cuf, $xmlString, $suId, $pvId = null)
    {
        $db = mainModel::conectar();
        $xmlGzip = gzencode($xmlString, 9);
        $hash = hash('sha256', $xmlGzip);

        // fe_cuf fue eliminada: el CUF queda en factura.fa_cuf y aqui solo
        // se registra el estado de la emision en contingencia.
        $ins = $db->prepare("
            INSERT INTO facturacion_electronica
            (fa_id, fe_estado_siat, fe_payload, fe_tipo_emision, fe_fecha_envio,
             fe_estado_local, fe_intentos, fe_hash_archivo, fe_ultimo_intento)
            VALUES (:fa_id, 'CONTINGENCIA', :payload, 2, NOW(),
                    'PENDIENTE', 0, :hash, NOW())
        ");
        $ins->execute([
            ':fa_id'   => $faId,
            ':payload' => $xmlString,
            ':hash'    => $hash,
        ]);
    }

    /* Escribe una cabecera tar POSIX (ustar) de 512 bytes.

       Se arma a mano en vez de usar PharData porque el SIN desempaqueta con
       Apache Commons Compress, que es mas estricto que PHP: PharData deja
       campos vacios y escribe un solo bloque de ceros al final. La referencia
       que si funciona contra el SIN (go-siat) genera las cabeceras a mano con
       mode 0600 y cierra con los dos bloques nulos que pide el formato. */
    private static function tarHeaderUstar($nombre, $size, $mtime)
    {
        // Ojo: str_pad('', N, ...) devuelve '' en PHP, no N nulos. Para los
        // campos que van vacios hay que usar str_repeat.
        $octal = function ($n, $len) {
            return str_pad(sprintf('%0' . $len . 'o', $n), $len, "\0");
        };

        $h  = str_pad(substr($nombre, 0, 100), 100, "\0");    // name
        $h .= $octal(0600, 7) . "\0";                          // mode
        $h .= $octal(0, 7) . "\0";                             // uid
        $h .= $octal(0, 7) . "\0";                             // gid
        $h .= $octal($size, 11) . "\0";                        // size
        $h .= $octal($mtime, 11) . "\0";                       // mtime
        $h .= '        ';                                      // 8 espacios para el checksum
        $h .= '0';                                             // typeflag: archivo regular
        $h .= str_repeat("\0", 100);                           // linkname
        $h .= "ustar\0" . '00';                                // magic + version POSIX
        $h .= str_repeat("\0", 32);                            // uname
        $h .= str_repeat("\0", 32);                            // gname
        $h .= $octal(0, 7) . "\0";                             // devmajor
        $h .= $octal(0, 7) . "\0";                             // devminor
        $h .= str_repeat("\0", 155);                           // prefix

        // El checksum es la suma de los 512 bytes con los 8 del campo puestos
        // a espacio, y se escribe como 6 digitos octales + NUL + espacio.
        $chk = 0;
        for ($i = 0; $i < 512; $i++) {
            $chk += ord($h[$i]);
        }

        // El checksum sustituye solo los 8 bytes del campo 148..155. El resto
        // del header (typeflag, linkname, magic, uname, gname, dev*, prefix)
        // va despues y hay que conservarlo: un substr($h, 0, 148) a secas
        // lo tiraria y el archivo quedaria sin magic.
        $bloque = substr($h, 0, 148)
                . sprintf('%06o', $chk) . "\0 "
                . substr($h, 156, 512 - 156);

        // El ensamble deberia dar exactamente 512; si no, el payload se
        // solapa con el header.
        return str_pad($bloque, 512, "\0");
    }

    /* Arma el TAR.GZ que se envia al SIN: un .xml firmado por factura dentro
       de un tar, comprimido con gzip y codificado en base64.

       El hash que exige el SIN es el SHA256 en hexadecimal de los datos ya
       comprimidos, no del tar ni del XML suelto, asi que se calcula sobre el
       mismo binario que despues se codifica en base64. */
    public static function armarPaqueteTargz(array $facturas)
    {
        $mtime = time();
        $tar   = '';

        foreach ($facturas as $i => $f) {
            // El SIN no exige un nombre concreto: la referencia que funciona
            // usa factura_1.xml, factura_2.xml, ...
            $nombre  = 'factura_' . ($i + 1) . '.xml';
            $payload = (string)$f->fe_payload;

            $tar .= self::tarHeaderUstar($nombre, strlen($payload), $mtime);
            $tar .= $payload;

            // El contenido se rellena a multiplos de 512 con ceros.
            $resto = strlen($payload) % 512;
            if ($resto !== 0) {
                $tar .= str_repeat("\0", 512 - $resto);
            }
        }

        // Dos bloques de 512 ceros marcan el final del tar. Sin el segundo,
        // algunos lectores consideran el archivo truncado.
        $tar .= str_repeat("\0", 1024);

        $gzip = gzencode($tar, 9);

        return [
            'base64' => base64_encode($gzip),
            'hash'   => hash('sha256', $gzip),
            'bytes'  => strlen($gzip),
            'tar'    => $tar,
        ];
    }

    /* Arma el cuerpo de SolicitudServicioRecepcionPaquete. Se separa del
       envio para que el reintento de un paquete ya guardado reutilice
       exactamente la misma estructura. */
    private static function solicitudPaquete($pv, $codigoSucursal, $codigoPuntoVenta,
                                            $archivo, $hash, $cantidad, $evCodigo)
    {
        return ['SolicitudServicioRecepcionPaquete' => [
            'codigoAmbiente'        => SIAT_AMBIENTE,
            'codigoModalidad'       => SIAT_MODALIDAD,
            'codigoSistema'         => SIAT_COD_SISTEMA,
            'nit'                   => self::obtenerNitEmpresa(),
            'cuis'                  => $pv->pv_cuis,
            // El CUFD vigente al ENVIAR el paquete, no el que se uso al
            // emitir: ese va dentro del XML de cada factura.
            'cufd'                  => $pv->pv_cufd,
            'codigoSucursal'        => $codigoSucursal,
            'codigoPuntoVenta'      => $codigoPuntoVenta,
            'codigoDocumentoSector' => 1,
            // 2 = fuera de linea. En el sobre SOAP este campo si dice que el
            // paquete es de contingencia; dentro del XML de la factura no
            // aparece, y el tipo de emision viaja codificado en el CUF.
            'codigoEmision'         => 2,
            'tipoFacturaDocumento'  => 1,
            // $archivo llega en base64 (asi se guarda en siat_paquetes) pero
            // SoapClient codifica en base64 por su cuenta, asi que se decodifica
            // antes: mandar el base64 tal cual produce un base64(base64(gzip))
            // y el SIN responde 920 "No se desempaqueto XMLs".
            'archivo'               => base64_decode($archivo),
            'hashArchivo'           => $hash,
            // Sin desfase horario a proposito. El SIN lo parsea con un
            // DateTimeFormatter de Java que no acepta la combinacion fraccion
            // + offset ("...T19:03:20.000-04:00"): responde
            // DateTimeParseException, unparsed text found at index 23.
            'fechaEnvio'            => date('Y-m-d\TH:i:s.000'),
            'cantidadFacturas'      => $cantidad,
            'codigoEvento'          => $evCodigo,
        ]];
    }

    /* Reenvia un paquete que ya quedo registrado en siat_paquetes.

       Hace falta porque el SIN se cae a menudo: si la llamada falla por red
       despues de armar el archivo, ese archivo se pierde salvo que se guarde.
       Como pa_archivo y pa_hash_archivo quedaron persistidos, el reintento
       reconstruye la peticion sin volver a generar el TAR.GZ, y el hash
       vuelve a coincidir byte a byte. */
    public static function reenviarPaquete($paId)
    {
        $db = mainModel::conectar();

        $st = $db->prepare("SELECT * FROM siat_paquetes WHERE pa_id = :id");
        $st->execute([':id' => $paId]);
        $paq = $st->fetch(PDO::FETCH_ASSOC);

        if (!$paq) {
            throw new Exception("SIAT reenviarPaquete: no existe el paquete pa_id={$paId}");
        }
        if (empty($paq['pa_archivo'])) {
            throw new Exception(
                "SIAT reenviarPaquete: el paquete pa_id={$paId} no tiene pa_archivo. "
                . "Solo se pueden reenviar los paquetes armados despues de la migracion 03."
            );
        }

        $suId = (int)$paq['su_id'];
        $pv = self::obtenerConfiguracionPuntoVenta($suId, null);
        if (!$pv || empty($pv->pv_cuis) || empty($pv->pv_cufd)) {
            throw new Exception("SIAT reenviarPaquete: la caja de su_id={$suId} no tiene CUIS o CUFD");
        }

        $codigoPuntoVenta = $pv->pv_codigo_sin !== null
            ? (int)$pv->pv_codigo_sin
            : (int)$pv->pv_codigo;

        $client = self::clienteSOAP('compra_venta');
        $resp = $client->recepcionPaqueteFactura(self::solicitudPaquete(
            $pv,
            self::obtenerCodigoSucursal($suId),
            $codigoPuntoVenta,
            $paq['pa_archivo'],
            $paq['pa_hash_archivo'],
            (int)$paq['pa_cantidad_facturas'],
            $paq['pa_ev_codigo']
        ));

        $r = $resp->RespuestaServicioFacturacion ?? $resp;
        $codigoRecepcion = $r->codigoRecepcion ?? null;
        $mensajes = !empty($r->mensajesList) ? (array)$r->mensajesList : [];

        if ($codigoRecepcion) {
            $db->prepare("UPDATE siat_paquetes
                          SET pa_codigo_recepcion = :rec, pa_fecha_envio = NOW(),
                              pa_estado = 'enviado', pa_mensajes = :msgs
                          WHERE pa_id = :id")
                ->execute([':rec' => $codigoRecepcion, ':msgs' => null, ':id' => $paId]);

            $ids = array_map(
                fn($f) => (int)$f['fe_id'],
                json_decode($paq['pa_facturas'] ?? '[]', true) ?: []
            );
            if ($ids) {
                $marcadores = [];
                $params = [':ticket' => $codigoRecepcion, ':pa_id' => $paId];
                foreach ($ids as $i => $id) {
                    $marcadores[] = ":id{$i}";
                    $params[":id{$i}"] = $id;
                }
                $db->prepare("UPDATE facturacion_electronica
                              SET fe_ticket = :ticket, fe_codigo_recepcion = :ticket,
                                  fe_paquete_id = :pa_id, fe_fecha_envio = NOW(),
                                  fe_estado_siat = 'PENDIENTE_VALIDACION',
                                  fe_estado_local = 'ENVIADO', fe_ultimo_intento = NOW()
                              WHERE fe_id IN (" . implode(',', $marcadores) . ")")
                    ->execute($params);
            }
        } else {
            $db->prepare("UPDATE siat_paquetes
                          SET pa_estado = 'rechazado', pa_mensajes = :msgs
                          WHERE pa_id = :id")
                ->execute([
                    ':msgs' => json_encode([
                        'codigoEstado'      => $r->codigoEstado ?? null,
                        'codigoDescripcion' => $r->codigoDescripcion ?? null,
                        'mensajes'          => $mensajes,
                    ]),
                    ':id'   => $paId,
                ]);
        }

        return $codigoRecepcion;
    }

    /* Envia al SIN un paquete de facturas emitidas en contingencia.

       $evCodigo es el codigo de recepcion del evento significativo devuelto
       por registroEventoSignificativo. El SIN lo exige para saber a que
       contingencia corresponde el paquete; sin el, la recepcion se rechaza.

       El esquema de solicitudRecepcionPaquete (verificado contra el XSD del
       WSDL de ServicioFacturacionCompraVenta) es:

         solicitudRecepcionPaquete
           + solicitudRecepcionFactura: archivo, fechaEnvio, hashArchivo
             + solicitudRecepcion:      codigoAmbiente, codigoDocumentoSector,
                                        codigoEmision, codigoModalidad,
                                        codigoPuntoVenta (opcional),
                                        codigoSistema, codigoSucursal, cufd,
                                        cuis, nit, tipoFacturaDocumento
           + cafc (opcional), cantidadFacturas, codigoEvento

       Nótese que <archivo> es un unico base64Binary con el TAR.GZ completo:
       no es un arreglo de archivos sueltos. */
    /* $feIds acota el contenido del paquete. Sin el, se mandan TODAS las
       facturas que esten en CONTINGENCIA de la sucursal, y eso es un problema:
       el script que arma el paquete decide cual va (por ejemplo, una factura
       forzada de otra sucursal) y si el modelo re-consulta, manda tambien las
       que el script dejo afuera, el SIN las mezcla y el paquete se rechaza
       entero. Con la lista explicita, el TAR.GZ contiene exactamente lo que se
       mostro antes de enviar. */
    public static function enviarPaqueteContingencia($suId, $pvId = null, $evCodigo = null, array $feIds = null)
    {
        if (empty($evCodigo)) {
            throw new Exception(
                "SIAT enviarPaquete: falta el codigo de recepcion del evento significativo. "
                . "Sin el, el SIN no puede asociar el paquete a la contingencia."
            );
        }

        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        if (!$pv || empty($pv->pv_cuis) || empty($pv->pv_cufd)) {
            throw new Exception("SIAT enviarPaquete: la caja no tiene CUIS o CUFD para su_id={$suId}");
        }

        $db = mainModel::conectar();

        // fe_cuf fue eliminada de facturacion_electronica. El CUF de cada
        // factura vive en la tabla factura, por eso el JOIN.
        $filtro = '';
        if ($feIds) {
            $ids = array_map('intval', $feIds);
            if (!$ids) {
                return null;
            }
            // Con la lista explicita no se filtra por sucursal: la factura
            // puede ser de otra (mientras esas sucursales no tengan CUFD
            // propio, se emite con las credenciales de la del evento) y el
            // filtro las dejaba fuera del TAR.GZ para siempre.
            $filtro = ' AND fe.fe_id IN (' . implode(',', $ids) . ')';
        }

        $filtroSucursal = $feIds ? '' : ' AND f.su_id = :su_id';

        $sql = "
            SELECT fe.fe_id, f.fa_cuf, fe.fe_payload
            FROM facturacion_electronica fe
            JOIN factura f ON f.fa_id = fe.fa_id
            WHERE fe.fe_tipo_emision = 2
              AND fe.fe_estado_siat = 'CONTINGENCIA'
              {$filtroSucursal}
              {$filtro}
            ORDER BY fe.fe_id
            LIMIT " . (int)SIAT_MAX_FACTURAS_CONTINGENCIA;

        $stmt = $db->prepare($sql);
        $stmt->execute($feIds ? [] : [':su_id' => $suId]);
        $facturas = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!$facturas) {
            return null;
        }

        $paquete = self::armarPaqueteTargz($facturas);

        $codigoSucursal   = self::obtenerCodigoSucursal($suId);
        $codigoPuntoVenta = $pv->pv_codigo_sin !== null
            ? (int)$pv->pv_codigo_sin
            : (int)$pv->pv_codigo;

        $listaFacturas = array_map(function ($f) {
            return ['fe_id' => (int)$f->fe_id, 'cuf' => $f->fa_cuf];
        }, $facturas);

        // El paquete queda registrado antes de enviarse, para que quede
        // rastro aunque la llamada al SIN falle y para poder reintentarlo
        // sin volver a construir el archivo.
        $paStmt = $db->prepare("
            INSERT INTO siat_paquetes
                (su_id, pa_ev_codigo, pa_cantidad_facturas, pa_hash_archivo,
                 pa_archivo, pa_facturas, pa_estado, pa_creado_en)
            VALUES (:su_id, :ev_codigo, :cantidad, :hash,
                    :archivo, :facturas, 'pendiente', NOW())
        ");
        $paStmt->execute([
            ':su_id'     => $suId,
            ':ev_codigo' => $evCodigo,
            ':cantidad'  => count($facturas),
            ':hash'      => $paquete['hash'],
            ':archivo'   => $paquete['base64'],
            ':facturas'  => json_encode($listaFacturas),
        ]);
        $paId = (int)$db->lastInsertId();

        $client = self::clienteSOAP('compra_venta');
        $resp = $client->recepcionPaqueteFactura(self::solicitudPaquete(
            $pv, $codigoSucursal, $codigoPuntoVenta,
            $paquete['base64'], $paquete['hash'], count($facturas), $evCodigo
        ));

        // La respuesta llega envuelta en RespuestaServicioFacturacion; leer
        // codigoRecepcion del nivel superior devuelve null siempre.
        $r = $resp->RespuestaServicioFacturacion ?? $resp;
        $codigoRecepcion = $r->codigoRecepcion ?? null;
        $mensajes = !empty($r->mensajesList) ? (array)$r->mensajesList : [];

        if ($codigoRecepcion) {
            $db->prepare("
                UPDATE siat_paquetes
                SET pa_codigo_recepcion = :recepcion, pa_fecha_envio = NOW(), pa_estado = 'enviado'
                WHERE pa_id = :pa_id
            ")->execute([':recepcion' => $codigoRecepcion, ':pa_id' => $paId]);

            $ids = array_map('intval', array_column($facturas, 'fe_id'));
            // Marcadores con nombre para todos: mixing placeholders con nombre
            // y posicionales en la misma sentencia es un error de PDO
            // ("Invalid parameter number: mixed named and positional
            // parameters") y hacia fallar la actualizacion justo despues de
            // haber conseguido la recepcion del SIN.
            $marcadores = [];
            $params = [':ticket' => $codigoRecepcion, ':pa_id' => $paId];
            foreach ($ids as $i => $id) {
                $marcadores[] = ":id{$i}";
                $params[":id{$i}"] = $id;
            }
            $in = implode(',', $marcadores);

            $stmtUpdate = $db->prepare("
                UPDATE facturacion_electronica
                SET fe_ticket = :ticket,
                    fe_codigo_recepcion = :ticket,
                    fe_paquete_id = :pa_id,
                    fe_fecha_envio = NOW(),
                    fe_estado_siat = 'PENDIENTE_VALIDACION',
                    fe_estado_local = 'ENVIADO',
                    fe_ultimo_intento = NOW()
                WHERE fe_id IN ({$in})
            ");
            $stmtUpdate->execute($params);
        } else {
            // Sin codigo de recepcion solo se sabe que el SIN no acepto el
            // paquete. Se guardan los mensajes para poder ver por que.
            $db->prepare("UPDATE siat_paquetes
                          SET pa_estado = 'rechazado', pa_mensajes = :msgs
                          WHERE pa_id = :pa_id")
                ->execute([
                    ':msgs' => json_encode([
                        'codigoEstado'      => $r->codigoEstado ?? null,
                        'codigoDescripcion' => $r->codigoDescripcion ?? null,
                        'mensajes'          => $mensajes,
                    ]),
                    ':pa_id' => $paId,
                ]);
        }

        return $codigoRecepcion;
    }

    /* Consulta el resultado de un paquete ya enviado. El SIN procesa la
       recepcion de forma asincrona: recepcionPaqueteFactura devuelve un
       codigo de recepcion de inmediato, pero las facturasTodavia pueden estar
       observadas. Esta operacion es la que dice si las acepto o no.

       A diferencia del envio, aqui el CUFD que se manda es el vigente: la
       validacion se consulta con las credenciales del dia. */
    public static function validarPaqueteContingencia($suId, $pvId, $codigoRecepcion)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        if (!$pv || empty($pv->pv_cuis) || empty($pv->pv_cufd)) {
            throw new Exception("SIAT validarPaquete: la caja no tiene CUIS o CUFD para su_id={$suId}");
        }

        $client = self::clienteSOAP('compra_venta');
        // OJO: la operacion se llama validacionRecepcionPaqueteFactura, NO
        // validacionRecepcionPaquete. Consultando __getFunctions() del WSDL de
        // ServicioFacturacionCompraVenta solo existen estas de validacion:
        //   validacionRecepcionFactura
        //   validacionRecepcionMasivaFactura
        //   validacionRecepcionPaqueteFactura
        // Llamar a "validacionRecepcionPaquete" reventaba con SoapFault
        // "Could not find operation" en el paso 6 de la Etapa 6.
        $resp = $client->validacionRecepcionPaqueteFactura([
            'SolicitudServicioValidacionRecepcionPaquete' => [
                'codigoAmbiente'        => SIAT_AMBIENTE,
                'codigoModalidad'       => SIAT_MODALIDAD,
                'codigoSistema'         => SIAT_COD_SISTEMA,
                'nit'                   => self::obtenerNitEmpresa(),
                'cuis'                  => $pv->pv_cuis,
                'cufd'                  => $pv->pv_cufd,
                'codigoSucursal'        => self::obtenerCodigoSucursal($suId),
                'codigoPuntoVenta'      => $pv->pv_codigo_sin !== null
                        ? (int)$pv->pv_codigo_sin
                        : (int)$pv->pv_codigo,
                'codigoDocumentoSector' => 1,
                'codigoEmision'         => 2,
                'tipoFacturaDocumento'  => 1,
                'codigoRecepcion'      => $codigoRecepcion,
            ]
        ]);

        $db = mainModel::conectar();
        // La respuesta viene envuelta en RespuestaServicioFacturacion. Leyendo
        // el nivel superior, transaccion y codigoEstado salian vacios y el
        // paquete se marcaba como rechazado sin que el SIN lo hubiera dicho.
        $r = $resp->RespuestaServicioFacturacion ?? $resp;
        $codigoEstado = (int)($r->codigoEstado ?? 0);
        $validado = !empty($r->transaccion);
        $mensajes = !empty($r->mensajesList) ? (array)$r->mensajesList : [];
        $observado = $codigoEstado === 904;

        $paq = $db->prepare("SELECT pa_id, pa_facturas FROM siat_paquetes
                             WHERE pa_codigo_recepcion = :rec LIMIT 1");
        $paq->execute([':rec' => $codigoRecepcion]);
        $fila = $paq->fetch(PDO::FETCH_ASSOC);

        if ($fila) {
            // 908 = validada. 901 = pendiente, 904 = observada: en los dos
            // ultimos casos el paquete se puede volver a consultar, asi que no
            // se marca como rechazado.
            $estadoPaquete = $codigoEstado === 908 ? 'validado' : 'enviado';
            $db->prepare("UPDATE siat_paquetes
                          SET pa_estado = :estado, pa_mensajes = :msgs
                          WHERE pa_id = :id")
                ->execute([
                    ':estado' => $estadoPaquete,
                    ':msgs'   => json_encode($mensajes),
                    ':id'     => $fila['pa_id'],
                ]);

            if ($fila['pa_facturas']) {
                $ids = array_map(
                    fn($f) => (int)$f['fe_id'],
                    json_decode($fila['pa_facturas'], true) ?: []
                );
                if ($ids) {
                    // Todo con marcadores con nombre: mezclar '?' con ':est'
                    // hace que PDO rechace la sentencia entera.
                    $estadoFactura = $codigoEstado === 908 ? 'VALIDADO'
                        : ($observado ? 'OBSERVADO' : 'PENDIENTE_VALIDACION');
                    $marcadores = [];
                    $params = [':est' => $estadoFactura];
                    foreach ($ids as $i => $id) {
                        $marcadores[] = ":id{$i}";
                        $params[":id{$i}"] = $id;
                    }
                    $db->prepare("UPDATE facturacion_electronica
                                  SET fe_estado_local = :est, fe_estado_siat = :est
                                  WHERE fe_id IN (" . implode(',', $marcadores) . ")")
                        ->execute($params);
                }
            }
        }

        return $resp;
    }

    public static function sincronizarCatalogos($suId = 1)
    {
        $cuis = self::leerCUIS($suId);
        if (!$cuis) {
            error_log("SIAT sincronizarCatalogos: no hay CUIS para su_id={$suId}");
            return false;
        }

        return [
            'fechaHora'   => self::sincronizarFechaHora($cuis, $suId),
            'actividades' => self::sincronizarActividades($cuis, $suId),
            'productos'   => self::sincronizarProductos($cuis, $suId),
            'leyendas'    => self::sincronizarLeyendas($cuis, $suId),
            'unidades'    => self::sincronizarUnidadesMedida($cuis, $suId),
        ];
    }

    private static function sincronizarFechaHora($cuis, $suId = null)
    {
        try {
            $codigos = $suId !== null ? self::obtenerCodigosSucursal($suId) : ['sucursal' => 0, 'punto_venta' => 0];
            $client = self::clienteSOAP('sincronizacion_datos');
            $resp = $client->sincronizarFechaHora([
                'SolicitudSincronizacion' => [
                    'codigoAmbiente'  => SIAT_AMBIENTE,
                    'codigoSistema'   => SIAT_COD_SISTEMA,
                    'nit'             => self::obtenerNitEmpresa(),
                    'cuis'            => $cuis,
                    'codigoModalidad' => SIAT_MODALIDAD,
                    'codigoSucursal'   => (int)$codigos['sucursal'],
                    'codigoPuntoVenta' => (int)$codigos['punto_venta'],
                ]
            ]);
            $fh = $resp->RespuestaFechaHora->fechaHora ?? null;
            if ($fh) {
                error_log("SIAT fechaHora SIN: {$fh}");
                if ($suId !== null) {
                    $db = mainModel::conectar();
                    $stmt = $db->prepare("UPDATE siat_configuracion SET sc_fecha_siat = ? WHERE su_id = ?");
                    $stmt->execute([$fh, $suId]);
                }
            }
            return $fh;
        } catch (Exception $e) {
            error_log("SIAT sincronizarFechaHora: " . $e->getMessage());
            return false;
        }
    }

    private static function sincronizarActividades($cuis, $suId = null)
    {
        try {
            $codigos = $suId !== null ? self::obtenerCodigosSucursal($suId) : ['sucursal' => 0, 'punto_venta' => 0];
            $client = self::clienteSOAP('sincronizacion_datos');
            $resp = $client->sincronizarListaActividadesDocumentoSector([
                'SolicitudSincronizacion' => [
                    'codigoAmbiente'  => SIAT_AMBIENTE,
                    'codigoSistema'   => SIAT_COD_SISTEMA,
                    'nit'             => self::obtenerNitEmpresa(),
                    'cuis'            => $cuis,
                    'codigoModalidad' => SIAT_MODALIDAD,
                    'codigoSucursal'   => (int)$codigos['sucursal'],
                    'codigoPuntoVenta' => (int)$codigos['punto_venta'],
                ]
            ]);
            $root = $resp->RespuestaListaActividadesDocumentoSector ?? null;
            $lista = $root->listaActividadesDocumentoSector ?? [];
            $lista = self::aLista($lista);
            self::guardarActividades($lista);
            return count($lista);
        } catch (Exception $e) {
            error_log("SIAT sincronizarActividades: " . $e->getMessage());
            return false;
        }
    }

    private static function sincronizarProductos($cuis, $suId = null)
    {
        try {
            $codigos = $suId !== null ? self::obtenerCodigosSucursal($suId) : ['sucursal' => 0, 'punto_venta' => 0];
            $client = self::clienteSOAP('sincronizacion_datos');
            $resp = $client->sincronizarListaProductosServicios([
                'SolicitudSincronizacion' => [
                    'codigoAmbiente'  => SIAT_AMBIENTE,
                    'codigoSistema'   => SIAT_COD_SISTEMA,
                    'nit'             => self::obtenerNitEmpresa(),
                    'cuis'            => $cuis,
                    'codigoModalidad' => SIAT_MODALIDAD,
                    'codigoActividad' => self::resolverActividadEconomica(),
                    'codigoSucursal'   => (int)$codigos['sucursal'],
                    'codigoPuntoVenta' => (int)$codigos['punto_venta'],
                ]
            ]);
            $root = $resp->RespuestaListaProductos ?? null;
            $lista = $root->listaCodigos ?? [];
            $lista = self::aLista($lista);
            self::guardarProductos($lista);
            return count($lista);
        } catch (Exception $e) {
            error_log("SIAT sincronizarProductos: " . $e->getMessage());
            return false;
        }
    }

    private static function sincronizarLeyendas($cuis, $suId = null)
    {
        try {
            $codigos = $suId !== null ? self::obtenerCodigosSucursal($suId) : ['sucursal' => 0, 'punto_venta' => 0];
            $client = self::clienteSOAP('sincronizacion_datos');
            $resp = $client->sincronizarListaLeyendasFactura([
                'SolicitudSincronizacion' => [
                    'codigoAmbiente'  => SIAT_AMBIENTE,
                    'codigoSistema'   => SIAT_COD_SISTEMA,
                    'nit'             => self::obtenerNitEmpresa(),
                    'cuis'            => $cuis,
                    'codigoModalidad' => SIAT_MODALIDAD,
                    'codigoSucursal'   => (int)$codigos['sucursal'],
                    'codigoPuntoVenta' => (int)$codigos['punto_venta'],
                ]
            ]);
            $root = $resp->RespuestaListaParametricasLeyendas ?? null;
            $lista = $root->listaLeyendas ?? [];
            $lista = self::aLista($lista);
            self::guardarLeyendas($lista);
            return count($lista);
        } catch (Exception $e) {
            error_log("SIAT sincronizarLeyendas: " . $e->getMessage());
            return false;
        }
    }

    private static function guardarLeyendas($lista)
    {
        $db = mainModel::conectar();
        $db->beginTransaction();
        try {
            $db->exec("DELETE FROM siat_leyendas");
            // OJO: las columnas reales llevan prefijo ley_. Antes se insertaba
            // en (codigo_actividad, leyenda, tipo_leyenda), que no existen, y
            // el INSERT moria con "Unknown column" dejando la tabla vacia para
            // siempre: por eso obtener_leyenda_siat_model() caia siempre al
            // texto fijo de la Ley 453.
            $stmt = $db->prepare("
                INSERT INTO siat_leyendas
                    (ley_codigo_actividad, ley_texto, ley_tipo_leyenda, ley_es_fija, ley_estado)
                VALUES (:act, :txt, :tipo, 1, 1)
            ");
            foreach ($lista as $l) {
                $txt = trim((string)($l->descripcionLeyenda ?? ''));
                if ($txt === '') {
                    continue;
                }
                $stmt->execute([
                    ':act'  => $l->codigoActividad ?? null,
                    ':txt'  => $txt,
                    ':tipo' => 'LEYENDA',
                ]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("SIAT guardarLeyendas: " . $e->getMessage());
        }
    }

    private static function guardarActividades($lista)
    {
        $db = mainModel::conectar();
        $db->beginTransaction();
        try {
            $db->exec("DELETE FROM siat_actividades");
            // act_codigo es UNIQUE y el SIN manda la MISMA actividad repetida
            // por cada tipo de documento sector: 4772100 aparece con los
            // sectores 1, 35, 24 y 47. Insertar los 14 tal cual revienta por
            // duplicado, asi que se agrupa por codigo: se guarda el sector 1
            // (FCV, el de las facturas de venta, que es el que usa el sistema)
            // y la lista completa de sectores queda en act_descripcion_completa.
            $stmt = $db->prepare("
                INSERT INTO siat_actividades
                    (act_codigo, act_descripcion, act_codigo_sector,
                     act_descripcion_completa, act_estado, act_sincronizado_en)
                VALUES (:cod, :desc, :sector, :completa, 1, NOW())
                ON DUPLICATE KEY UPDATE
                    act_descripcion = VALUES(act_descripcion),
                    act_codigo_sector = VALUES(act_codigo_sector),
                    act_descripcion_completa = VALUES(act_descripcion_completa),
                    act_estado = 1,
                    act_sincronizado_en = NOW()
            ");

            $porCodigo = [];
            foreach ($lista as $a) {
                $cod = trim((string)($a->codigoActividad ?? ''));
                if ($cod === '') {
                    continue;
                }
                $sector = (string)($a->codigoDocumentoSector ?? '');
                $tipo   = trim((string)($a->tipoDocumentoSector ?? ''));

                if (!isset($porCodigo[$cod])) {
                    $porCodigo[$cod] = ['sector' => $sector, 'desc' => $tipo, 'todos' => []];
                }
                $porCodigo[$cod]['todos'][] = $sector . '=' . $tipo;
            }

            foreach ($porCodigo as $cod => $info) {
                $stmt->execute([
                    ':cod'      => $cod,
                    ':desc'     => $info['desc'],
                    ':sector'   => $info['sector'],
                    ':completa' => implode(' | ', $info['todos']),
                ]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("SIAT guardarActividades: " . $e->getMessage());
        }
    }

    private static function guardarProductos($lista)
    {
        $db = mainModel::conectar();
        $db->beginTransaction();
        try {
            $db->exec("DELETE FROM siat_productos");
            // Mismo problema que en actividades: las columnas reales llevan
            // prefijo pro_. Se usan INSERT IGNORE porque pro_codigo es UNIQUE y
            // el SIN repite codigoProducto entre actividades (1004879 sale
            // para 4772100 y para 4772200).
            $stmt = $db->prepare("
                INSERT IGNORE INTO siat_productos
                    (pro_codigo, pro_descripcion, pro_codigo_actividad,
                     pro_nandina, pro_tipo_producto, pro_unidad_medida_default,
                     pro_estado, pro_sincronizado_en)
                VALUES (:cod, :desc, :act, :nandina, 'bien', '1', 1, NOW())
            ");
            foreach ($lista as $p) {
                $cod = trim((string)($p->codigoProducto ?? ''));
                if ($cod === '') {
                    continue;
                }
                $nandina = isset($p->nandina)
                    ? (is_array($p->nandina) ? implode(',', $p->nandina) : (string)$p->nandina)
                    : null;

                $stmt->execute([
                    ':cod'     => $cod,
                    ':desc'    => trim((string)($p->descripcionProducto ?? '')),
                    ':act'     => (string)($p->codigoActividad ?? ''),
                    ':nandina' => ($nandina === '' ? null : $nandina),
                ]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("SIAT guardarProductos: " . $e->getMessage());
        }
    }

    private static function sincronizarUnidadesMedida($cuis, $suId = null)
    {
        try {
            $codigos = $suId !== null ? self::obtenerCodigosSucursal($suId) : ['sucursal' => 0, 'punto_venta' => 0];
            $client = self::clienteSOAP('sincronizacion_datos');
            $resp = $client->sincronizarParametricaUnidadMedida([
                'SolicitudSincronizacion' => [
                    'codigoAmbiente'  => SIAT_AMBIENTE,
                    'codigoSistema'   => SIAT_COD_SISTEMA,
                    'nit'             => self::obtenerNitEmpresa(),
                    'cuis'            => $cuis,
                    'codigoModalidad' => SIAT_MODALIDAD,
                    'codigoSucursal'   => (int)$codigos['sucursal'],
                    'codigoPuntoVenta' => (int)$codigos['punto_venta'],
                ]
            ]);
            $root = $resp->RespuestaListaParametricas ?? null;

            // OJO: el SIN no llama a este nodo listaUnidadesMedida. Para
            // sincronizarParametricaUnidadMedida devuelve listaCodigos, igual
            // que sincronizarListaProductosServicios. Buscar el nombre
            // "correcto" hacia que la sincronizacion guarde cero filas y solo
            // se note porque las unidades de medida nunca se sincronizan.
            $lista = $root->listaUnidadesMedida ?? ($root->listaCodigos ?? []);
            $lista = self::aLista($lista);
            self::guardarParametrica($lista, 'UNIDAD_MEDIDA');
            return count($lista);
        } catch (Exception $e) {
            error_log("SIAT sincronizarUnidadesMedida: " . $e->getMessage());
            return false;
        }
    }

    private static function guardarParametrica($lista, $tipo)
    {
        $db = mainModel::conectar();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO siat_parametricas (par_tipo, par_codigo, par_descripcion, par_vigente, par_sincronizado_en)
                VALUES (:tipo, :cod, :desc, 1, NOW())
                ON DUPLICATE KEY UPDATE par_descripcion = :desc, par_vigente = 1, par_sincronizado_en = NOW()
            ");
            $vistos = [];
            foreach ($lista as $u) {
                // El SIN usa codigoClasificador en las sincronizaciones de
                // unidades de medida; otras parametricas usan codigo o
                // codigoUnidadMedida. Se aceptan los tres para no depender de
                // adivinar el nombre del nodo.
                $cod = $u->codigoClasificador
                    ?? ($u->codigoUnidadMedida ?? ($u->codigo ?? ''));
                $cod = trim((string)$cod);

                // Sin codigo no hay nada que guardar, y guardarlo con ''
                // crearia una fila inutil que el ON DUPLICATE KEY no distingue.
                if ($cod === '' || isset($vistos[$cod])) {
                    continue;
                }
                $vistos[$cod] = true;

                $stmt->execute([
                    ':tipo' => $tipo,
                    ':cod'  => $cod,
                    ':desc' => $u->descripcion ?? '',
                ]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("SIAT guardarParametrica($tipo): " . $e->getMessage());
        }
    }

    /* Resuelve el codigo de actividad economica del EMISOR.

       Orden de decision, y por que en ese orden:

         1. SIAT_ACTIVIDAD_ECONOMICA (config/siat.php). Es la fuente de verdad
            porque es la unica que decide el negocio, y el SIN valida contra lo
            que esta inscrito en el NIT, no contra lo que hay en la base local.
         2. Lo que traiga configuracion_empresa, SOLO si existe en el catalogo
            sincronizado. Sirve para no perder una correccion puntual sin tocar
            el codigo.
         3. Lo que diga el catalogo del SIN.

       Por que no se manda sin verificar: configuracion_empresa trae 477000, que
       NO existe en el catalogo, y mandarlo hace que el SIN descarte la linea del
       detalle (codigo de error 1008). Un codigo de actividad no valido no
       invalida la factura entera, pero se queda sin detalle facturado. */

    public static function resolverActividadEconomica($codigoConfigurado = null)
    {
        $db = mainModel::conectar();

        $catalogo = function ($codigo) use ($db) {
            $codigo = trim((string)$codigo);
            if ($codigo === '' || $codigo === '477000') {
                return false;
            }
            $s = $db->prepare("SELECT act_codigo FROM siat_actividades WHERE act_codigo = :c LIMIT 1");
            $s->execute([':c' => $codigo]);
            return $s->fetchColumn() ? $codigo : false;
        };

        // 1) La constante de configuracion manda.
        $desdeConstante = $catalogo(SIAT_ACTIVIDAD_ECONOMICA);
        if ($desdeConstante) {
            return $desdeConstante;
        }
        // Si la constante no esta en el catalogo sincronizado aun, se usa igual:
        // es el valor que el SIN ya acepto en las etapas de certificacion, y el
        // catalogado local puede estar simply desactualizado.
        if (trim((string)SIAT_ACTIVIDAD_ECONOMICA) !== '') {
            return (string)SIAT_ACTIVIDAD_ECONOMICA;
        }

        // 2) La configuracion de la empresa, si existe en el catalogo.
        $desdeConfig = $catalogo($codigoConfigurado);
        if ($desdeConfig) {
            return $desdeConfig;
        }
        if (trim((string)$codigoConfigurado) !== '' && $codigoConfigurado !== '477000') {
            error_log(
                "SIAT resolverActividadEconomica: '{$codigoConfigurado}' no esta en siat_actividades; "
                . "se usa SIAT_ACTIVIDAD_ECONOMICA (" . SIAT_ACTIVIDAD_ECONOMICA . ")."
            );
        }

        // 3) Ultimo recurso: el catalogo del SIN.
        $primero = $db->query("
            SELECT act_codigo FROM siat_actividades
            WHERE act_codigo IS NOT NULL AND act_codigo <> ''
            ORDER BY act_id LIMIT 1
        ")->fetchColumn();

        if ($primero) {
            error_log(
                "SIAT resolverActividadEconomica: sin constante utilizable, se cae al catalogo ({$primero})."
            );
            return (string)$primero;
        }

        error_log('SIAT resolverActividadEconomica: catalogo vacio y sin constante; no se puede emitir.');
        return null;
    }

    /* Resuelve el codigo de producto del SIN para un medicamento.

       Antes se mandaba siempre 99900 ("OTROS"), un valor de relleno que el SIN
       acepta pero que no identifica el medicamento. Con el catalogo de la etapa
       2 (siat_productos, 1426 filas) se busca el producto por descripcion
       exacta y, si no hay coincidencia exacta, por la mejor coincidencia
       parcial dentro de la misma actividad economica.

       Devuelve null cuando no encuentra nada, para que el que arma el XML decida
       si usa 99900 o lanza un error. */
    public static function resolverCodigoProductoSIN($nombreMedicamento, $codigoActividad = null)
    {
        $nombre = trim((string)$nombreMedicamento);
        if ($nombre === '') {
            return null;
        }

        $db = mainModel::conectar();

        // 1) Coincidencia exacta de descripcion, dentro de la actividad pedida.
        if ($codigoActividad) {
            $s = $db->prepare("
                SELECT pro_codigo FROM siat_productos
                WHERE pro_descripcion = :n AND pro_codigo_actividad = :a
                LIMIT 1
            ");
            $s->execute([':n' => $nombre, ':a' => $codigoActividad]);
            $c = $s->fetchColumn();
            if ($c !== false && $c !== null) {
                return (string)$c;
            }
        }

        // 2) Coincidencia exacta sin filtrar por actividad.
        $s = $db->prepare("SELECT pro_codigo FROM siat_productos WHERE pro_descripcion = :n LIMIT 1");
        $s->execute([':n' => $nombre]);
        $c = $s->fetchColumn();
        if ($c !== false && $c !== null) {
            return (string)$c;
        }

        // 3) Coincidencia parcial sobre el nombre COMPLETO. El catalogo del SIN
        //    trae nombres genericos ("Antibioticos") mientras que la farmacia
        //    usa nombres comerciales, asi que un LIKE del nombre entero es lo
        //    mas conservador que se puede probar.
        $s = $db->prepare("
            SELECT pro_codigo, pro_descripcion FROM siat_productos
            WHERE pro_descripcion LIKE :n
            ORDER BY CHAR_LENGTH(pro_descripcion) ASC
            LIMIT 1
        ");
        $s->execute([':n' => '%' . $nombre . '%']);
        $r = $s->fetch(PDO::FETCH_OBJ);
        if ($r && !empty($r->pro_codigo) && !self::esCategoriaGenerica($r->pro_descripcion)) {
            error_log("SIAT resolverCodigoProductoSIN: '{$nombre}' -> {$r->pro_codigo} ({$r->pro_descripcion}) por coincidencia parcial.");
            return (string)$r->pro_codigo;
        }

        // 4) Ultimo recurso: palabra clave del nombre comercial.
        //
        //    OJO: esto es peligroso y por eso se acota a palabras de 6 letras o
        //    mas, de la mas larga a la mas corta, y se descarta cualquier
        //    categoria generica. Las categorias paraguas del SIN ("PRODUCTOS DE
        //    MAQUILLAJE", "OTROS PRODUCTOS FARMACEUTICOS") matchean casi
        //    cualquier palabra comun como "PRODUCTO", asi que sin el filtro un
        //    medicamento desconocido terminaria clasificado como algo que no
        //    es. Mandar un codigo que NO corresponde al medicamento es peor que
        //    mandar 99900 ("OTROS"), que es un valor legitimo del catalogo y el
        //    SIN acepta, asi que ante la duda se devuelve null.
        $tokens = preg_split('/[\s\/]+/u', mb_strtoupper($nombre, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY);
        $tokens = array_values(array_unique(array_filter($tokens, function ($t) {
            return mb_strlen($t, 'UTF-8') >= 6
                && !in_array($t, ['PRODUCTO', 'PRODUCTOS', 'OTROS', 'SERVICIO', 'SERVICIOS'], true);
        })));
        // De la palabra mas larga a la mas corta: "ACETAMIN PARACETAMOL" prueba
        // primero PARACETAMOL y da con "Paracetamol" antes que con "Antibioticos".
        usort($tokens, function ($a, $b) {
            return mb_strlen($b, 'UTF-8') - mb_strlen($a, 'UTF-8');
        });

        foreach (array_slice($tokens, 0, 3) as $t) {
            $s = $db->prepare("
                SELECT pro_codigo, pro_descripcion FROM siat_productos
                WHERE UPPER(pro_descripcion) LIKE :t
                ORDER BY CHAR_LENGTH(pro_descripcion) ASC LIMIT 1
            ");
            $s->execute([':t' => '%' . $t . '%']);
            $r = $s->fetch(PDO::FETCH_OBJ);
            if ($r && !empty($r->pro_codigo) && !self::esCategoriaGenerica($r->pro_descripcion)) {
                error_log("SIAT resolverCodigoProductoSIN: '{$nombre}' -> {$r->pro_codigo} ({$r->pro_descripcion}) por palabra clave '{$t}'.");
                return (string)$r->pro_codigo;
            }
        }

        error_log("SIAT resolverCodigoProductoSIN: '{$nombre}' no se pudo mapear con certeza; se usara 99900 (OTROS).");
        return null;
    }

    /* Una categoria del SIN es "generica" cuando describe un grupo amplio y no
       un producto concreto. Mandar una de estas como codigoProductoSin de un
       medicamento concreto es incorrecto, asi que se descartan siempre. */
    private static function esCategoriaGenerica($descripcion)
    {
        $d = mb_strtoupper(trim((string)$descripcion), 'UTF-8');
        if ($d === '') {
            return true;
        }
        foreach (['PRODUCTO', 'PRODUCTOS', 'OTROS', 'SERVICIO', 'SERVICIOS', 'ALCANZADOS POR EL IVA', 'N.C.P.'] as $generico) {
            if (mb_strpos($d, $generico) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function obtener_leyenda_siat_model($codigoActividad = '477000')
    {
        $db = mainModel::conectar();

        // La actividad configurada puede no existir en el catalogo del SIN.
        // Los codigos reales que devuelve sincronizarListaActividades son
        // 4772100, 4772200 y 477300; valores como 477000 (el que trae
        // configuracion_empresa) no aparecen nunca. Por eso, si no hay leyenda
        // para la actividad pedida, se recurre a la primera vigente en vez de
        // devolver cadena vacia y dejar que el XML viaje con el texto fijo.
        $stmt = $db->prepare("
            SELECT ley_texto FROM siat_leyendas
            WHERE ley_codigo_actividad = :act
            ORDER BY ley_id DESC
            LIMIT 1
        ");
        $stmt->execute([':act' => $codigoActividad]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if ($row && !empty(trim((string)$row->ley_texto))) {
            return trim($row->ley_texto);
        }

        $fallback = $db->query("
            SELECT ley_texto FROM siat_leyendas
            WHERE ley_texto IS NOT NULL AND TRIM(ley_texto) <> ''
            ORDER BY ley_id DESC
            LIMIT 1
        ")->fetch(PDO::FETCH_OBJ);

        if ($fallback && !empty(trim((string)$fallback->ley_texto))) {
            error_log("SIAT obtener_leyenda: la actividad {$codigoActividad} no tiene leyenda; se usa la primera vigente.");
            return trim($fallback->ley_texto);
        }

        return '';
    }

    /* Estado real de una factura ante el SIN.

       PARA UNA FACTURA EN LINEA NO EXISTE UNA OPERACION "validar el codigo de
       recepcion". El WSDL de ServicioFacturacionCompraVenta declara:

           recepcionFactura, recepcionMasivaFactura, recepcionPaqueteFactura,
           validacionRecepcionPaqueteFactura, validacionRecepcionMasivaFactura,
           verificacionEstadoFactura, anulacionFactura,
           reversionAnulacionFactura, verificarComunicacion, recepcionAnexos

       y NO declara validacionRecepcionFactura. Invocar un nombre inexistente
       hace que SoapClient responda

           [Client] Function ("validacionRecepcionFactura") is not a valid
           method for this service

       que es SoapFatal: tumba el proceso, no una excepcion manejable. Asi
       estaba, la validacion de facturas en linea no podia funcionar nunca.

       Lo que el SIN si expone para una factura suelta es
       verificacionEstadoFactura, que se consulta por CUF. El codigo de
       recepcion es transitorio y ademas es propio de la respuesta de
       recepcionFactura; el CUF es el identificador estable de la factura. */
    public static function verificarEstadoFactura($cuf, $suId, $codigoEmision = 1)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId);
        if (!$pv || empty($pv->pv_cuis) || empty($pv->pv_cufd)) {
            throw new Exception("SIAT verificarEstadoFactura: la caja no tiene CUIS o CUFD para su_id={$suId}");
        }

        $codigoSucursal = self::obtenerCodigoSucursal($suId);
        // Del mismo pv sale el CUIS que se manda, para que no puedan separarse.
        $codigoPuntoVenta = $pv->pv_codigo_sin !== null ? (int)$pv->pv_codigo_sin : (int)$pv->pv_codigo;

        $client = self::clienteSOAP('compra_venta');
        $resp = $client->verificacionEstadoFactura([
            'SolicitudServicioVerificacionEstadoFactura' => [
                'codigoAmbiente'        => SIAT_AMBIENTE,
                'codigoModalidad'       => SIAT_MODALIDAD,
                'codigoSistema'         => SIAT_COD_SISTEMA,
                'codigoSucursal'        => $codigoSucursal,
                'codigoPuntoVenta'      => $codigoPuntoVenta,
                'codigoDocumentoSector' => 1,
                'tipoFacturaDocumento'  => 1,
                'codigoEmision'         => (int)$codigoEmision,
                'cuis'                  => $pv->pv_cuis,
                'cufd'                  => $pv->pv_cufd,
                'nit'                   => self::obtenerNitEmpresa(),
                'cuf'                   => $cuf,
            ]
        ]);

        return $resp->RespuestaServicioFacturacion ?? $resp;
    }

    /* Verifica una factura a partir de su codigo de recepcion.

       Se conservan el nombre y los argumentos porque es lo que llama
       procesarValidacionPendiente() y, por medio de el, cronjob/siat_validar.php.
       Por dentro resuelve el CUF, que es el dato que el SIN entiende para una
       factura en linea, y devuelve la misma forma de respuesta que antes para
       que el llamador no cambie. */
    public static function validarRecepcionFactura($ticket, $suId)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            SELECT f.fa_cuf, fe.fe_tipo_emision
              FROM facturacion_electronica fe
              JOIN factura f ON f.fa_id = fe.fa_id
             WHERE fe.fe_codigo_recepcion = :ticket
          ORDER BY fe.fe_id DESC
             LIMIT 1
        ");
        $stmt->execute([':ticket' => $ticket]);
        $fila = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$fila || empty($fila->fa_cuf)) {
            throw new Exception(
                "SIAT validarRecepcionFactura: no hay CUF guardado para el codigo de recepcion {$ticket}"
            );
        }

        $emision = $fila->fe_tipo_emision !== null ? (int)$fila->fe_tipo_emision : 1;
        $r = self::verificarEstadoFactura($fila->fa_cuf, $suId, $emision);

        // La respuesta del SIN no incluye el CUF, y procesarValidacionPendiente()
        // lo necesita para armar el QR. Se agrega al mismo objeto.
        $r->cuf = $fila->fa_cuf;

        return $r;
    }

    /* URL publica de consulta del QR en el portal del SIN.

       Se separa de generarQR() a proposito: la URL es deterministica a partir de
       (nit, cuf, numero), asi que se puede volver a construir en cualquier
       momento para reimprimir un comprobante viejo sin tocar la base. Lo que se
       guarda en fe_qr es la IMAGEN; la URL se reconstruye con este metodo. */
    public static function urlQR($nit, $cuf, $nroFactura)
    {
        return 'https://siat.impuestos.gob.bo/consulta/QR?'
            . 'nit=' . urlencode((string)$nit)
            . '&cuf=' . urlencode((string)$cuf)
            . '&numero=' . urlencode(str_pad((string)$nroFactura, 10, '0', STR_PAD_LEFT));
    }

    public static function generarQR($nit, $cuf, $nroFactura)
    {
        return QRCode::render(self::urlQR($nit, $cuf, $nroFactura));
    }

    /* Escribe el QR en un PNG temporal y devuelve la ruta.

       FPDF no acepta base64 ni data URIs: Image() necesita un archivo en disco.
       Por eso esta funcion existe y no se llama a generarQR() desde el PDF.

       El archivo es de un solo uso: quien la llama tiene que borrarlo con
       borrarQR(), y si no se hace, se va al archivo del sistema y no se limpia
       solo. Se devuelve null si algo falla, y el PDF se sigue haciendo sin QR
       en vez de romperse: es preferible una factura sin QR visible a una venta
       que no se puede cobrar. */
    public static function qrEnDisco($nit, $cuf, $nroFactura)
    {
        $png = self::generarQR($nit, $cuf, $nroFactura);
        if (empty($png)) {
            return null;
        }

        $ruta = tempnam(sys_get_temp_dir(), 'siatqr_');
        if ($ruta === false) {
            error_log("SIAT qrEnDisco: no se pudo crear el archivo temporal");
            return null;
        }

        $pngPath = $ruta . '.png';
        $bytes = @file_put_contents($pngPath, base64_decode($png));
        if ($bytes === false) {
            @unlink($ruta);
            error_log("SIAT qrEnDisco: no se pudo escribir el PNG en {$pngPath}");
            return null;
        }
        @unlink($ruta);

        return $pngPath;
    }

    /* Borra el PNG temporal que creo qrEnDisco(). */
    public static function borrarQR($ruta)
    {
        if (!empty($ruta) && is_file($ruta)) {
            @unlink($ruta);
        }
    }

    /* Estado de una sucursal para poder facturar.

       Devuelve el diagnostico de por que una sucursal todavia no puede emitir, en
       vez de dejar que falle en el cierre de venta con un error de SOAP. Sirve
       para que el cron decida que credenciales pedir y para que el historial
       pueda explicar el motivo.

       Dos claves de texto, y no una a proposito:

         - 'motivo'       lo que ve el cajero, en un cartel. Sin siglas y sin
                          codigos: el CUIS vencido no le dice nada a quien esta
                          cobrando, y el codigo lo tiene el que administra la
                          caja. Cada caso dice que hacer.
         - 'diagnostico'  lo mismo con los codigos puestos, para el log del cron
                          y para error_log, que ahi si son lo unico que sirve.

       Los pasos son los del SIN, en orden: existir como sucursal, tener CUIS
       vigente, tener CUFD vigente. */
    public static function estadoCajaParaFacturacion($suId, $pvId = null)
    {
        $salida = [
            'su_id'         => $suId,
            'pv_id'         => $pvId,
            'listo'         => false,
            'motivo'        => '',
            'diagnostico'   => '',
            'pv_nombre'     => null,
            'pv_codigo'     => null,
            'cuis'          => null,
            'cuis_vigencia' => null,
            'cufd'          => null,
            'cufd_control'  => null,
            'cufd_vigencia' => null,
        ];

        $db = mainModel::conectar();

        // Codigo de sucursal que viajaria al SIN.
        $codigos = self::obtenerCodigosSucursal($suId);
        if (empty($codigos['sucursal']) && $codigos['sucursal'] !== 0) {
            $salida['motivo'] = "Esta sucursal todavia no esta habilitada para emitir facturas. Avise al administrador.";
            $salida['diagnostico'] = "su_id={$suId} no tiene codigoSucursal en siat_configuracion";
            return $salida;
        }

        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        if (!$pv) {
            $salida['motivo'] = "Esta sucursal no tiene caja habilitada para emitir facturas. Avise al administrador.";
            $salida['diagnostico'] = "su_id={$suId} no tiene punto de venta con pv_codigo = 0";
            return $salida;
        }

        $salida['pv_nombre'] = $pv->pv_nombre ?? null;
        $salida['pv_codigo'] = $pv->pv_codigo ?? null;

        // El CUFD caduca a las 24 h; el CUIS dura 365 dias.
        $cufdHasta = $pv->pv_cufd_vigente_hasta ?? null;
        $cuisHasta = $pv->pv_cuis_vigente_hasta ?? null;

        $salida['cuis']          = $pv->pv_cuis ?? null;
        $salida['cuis_vigencia'] = $cuisHasta;
        $salida['cufd']          = $pv->pv_cufd ?? null;
        $salida['cufd_control']  = $pv->pv_cufd_control ?? null;
        $salida['cufd_vigencia'] = $cufdHasta;

        if (empty($salida['cuis'])) {
            $salida['motivo'] = "Esta caja aun no esta registrada ante el Servicio de Impuestos. Avise al administrador.";
            $salida['diagnostico'] = "pv '{$salida['pv_nombre']}' (pv_id={$pv->pv_id}) sin CUIS: hay que registrarlo con registrarPuntoVenta()";
            return $salida;
        }

        if ($cuisHasta && strtotime($cuisHasta) <= time()) {
            $salida['motivo'] = "Esta caja tiene su habilitancio vencida. Avise al administrador para que la renueve.";
            $salida['diagnostico'] = "CUIS {$salida['cuis']} de pv '{$salida['pv_nombre']}' vencio el {$cuisHasta}";
            return $salida;
        }

        if (empty($salida['cufd']) || empty($salida['cufd_control'])) {
            $salida['motivo'] = "Esta caja todavia no tiene permiso para emitir. Avise al administrador.";
            $salida['diagnostico'] = "pv '{$salida['pv_nombre']}' sin CUFD ni control: pedir uno con obtenerCUFD()";
            return $salida;
        }

        if (!$cufdHasta) {
            $salida['motivo'] = "A esta caja le falta el permiso de emision. Avise al administrador.";
            $salida['diagnostico'] = "pv '{$salida['pv_nombre']}' tiene CUFD {$salida['cufd']} pero sin pv_cufd_vigente_hasta";
            return $salida;
        }

        if (strtotime($cufdHasta) <= time()) {
            $salida['motivo'] = "El permiso de emision de esta caja vencio. Se renueva solo, intente de nuevo en un momento.";
            $salida['diagnostico'] = "CUFD {$salida['cufd']} de pv '{$salida['pv_nombre']}' vencio el {$cufdHasta}";
            return $salida;
        }

        $salida['listo']  = true;
        $salida['motivo'] = 'ok';
        $salida['diagnostico'] = 'ok';
        return $salida;
    }

    /* Espera, en minutos, antes del siguiente intento de una factura.

       El numero de intentos ya va leído de la fila, asi que el primero que se
       reclama (intentos 0) no espera nada. */
    public static function esperaColaMinutos($intentos)
    {
        $intentos = (int)$intentos;
        if ($intentos <= 0) {
            return 0;
        }
        $espera = SIAT_COLA_ESPERA_BASE * pow(3, $intentos - 1);
        return (int)min(SIAT_COLA_ESPERA_TOPE, $espera);
    }

    /* Error que NO se arregla reintentando.

       El 123 es el caso claro: el CUFD que viaja dentro del XML firmado caduco.
       El XML es inmutable a proposito, asi que volver a mandarlo con ese mismo
       CUFD va a seguir fallando siempre. Reintentar cada hora no lo arregla:
       lo unico que sirve es rehacer la factura por contingencia, y eso ya es
       una decision del operador. */
    private static function errorEsPermanente($mensaje)
    {
        $mensaje = (string)$mensaje;
        foreach (['123', 'CUFD FUERA DE TOLERANCIA', 'CUFD INVALIDO'] as $m) {
            if (stripos($mensaje, $m) !== false) {
                return true;
            }
        }
        return false;
    }

    /* Drena la cola de envio.

       Por que NO se envia desde el cierre de venta: cada llamada al SIN tarda
       30-45 s. Si el cierre la espera, el cajero ve una pantalla congelada y dos
       cajas pueden acabar pegando al mismo punto de venta a la vez. Al cerrar
       solo se firma y se guarda; el envio pasa aqui, una factura por vez y
       serializado.

       Garantias que aporta:
         - Nunca dos llamadas simultaneas a la misma sucursal. Se agrupa por
           su_id y de cada grupo solo se toma un lote.
         - Nunca se regenera el XML. Se reenvia fe_payload tal cual.
         - Nunca dos workers toman la misma fila: el reclamo es condicional
           (WHERE fe_estado_local = 'PENDIENTE') y atomico.

       Devuelve un resumen con lo que se hizo, para que el cron lo registre. */
    public static function procesarColaEnvio($limite = null)
    {
        $limite = $limite === null ? SIAT_COLA_LIMITE : (int)$limite;
        $db = mainModel::conectar();

        $resumen = [
            'candidatas' => 0,
            'enviadas'  => 0,
            'reintentadas' => 0,
            'permanentes' => 0,
            'descartadas'  => 0,
            'detalle'   => [],
        ];

        /* Se leen mas candidatas que el limite porque despues se descartan las
           que estan esperando su turno de backoff y las que son de una sucursal
           que ya trae trabajo en esta pasada. */
        $lote = max($limite * 10, 50);

        $filas = $db->query("
            SELECT fe.fe_id, fe.fa_id, fe.fe_intentos, fe.fe_ultimo_intento,
                   f.su_id, f.fa_cuf, f.fa_numero_siat
            FROM facturacion_electronica fe
            JOIN factura f ON f.fa_id = fe.fa_id
            WHERE fe.fe_tipo_emision = '1'
              AND fe.fe_estado_local = 'PENDIENTE'
              AND fe.fe_payload IS NOT NULL
              AND fe.fe_intentos < " . (int)SIAT_COLA_MAX_INTENTOS . "
            ORDER BY f.su_id ASC, fe.fe_id ASC
            LIMIT " . (int)$lote . "
        ")->fetchAll(PDO::FETCH_ASSOC);

        $resumen['candidatas'] = count($filas);

        $porSucursal = [];
        $tomadas = [];

        foreach ($filas as $f) {
            if (count($tomadas) >= $limite) {
                break;
            }

            $suId = (int)$f['su_id'];
            $intentos = (int)$f['fe_intentos'];

            // Una sola tanda por sucursal en esta pasada: es lo que impide que
            // dos cajas del mismo punto de venta hablen con el SIN a la vez.
            $usadas = isset($porSucursal[$suId]) ? $porSucursal[$suId] : 0;
            if ($usadas >= $limite) {
                $resumen['descartadas']++;
                continue;
            }

            // Backoff: todavia no le toca volver a intentarse.
            $espera = self::esperaColaMinutos($intentos);
            if ($espera > 0 && $f['fe_ultimo_intento'] !== null) {
                $proximo = strtotime($f['fe_ultimo_intento']) + ($espera * 60);
                if ($proximo > time()) {
                    $resumen['descartadas']++;
                    continue;
                }
            }

            $porSucursal[$suId] = $usadas + 1;
            $tomadas[] = $f;
        }

        foreach ($tomadas as $f) {
            $feId  = (int)$f['fe_id'];
            $faId  = (int)$f['fa_id'];
            $suId  = (int)$f['su_id'];
            $fila = ['fe_id' => $feId, 'fa_id' => $faId, 'su_id' => $suId];

            /* Reclamo atomico. Si otro worker entro primero, rowCount() da 0 y
               esta fila se deja intacta. */
            $reclama = $db->prepare("
                UPDATE facturacion_electronica
                SET fe_estado_local = 'ENVIANDO',
                    fe_ultimo_intento = NOW(),
                    fe_intentos = fe_intentos + 1
                WHERE fe_id = :fe_id AND fe_estado_local = 'PENDIENTE'
            ");
            $reclama->execute([':fe_id' => $feId]);

            if ($reclama->rowCount() === 0) {
                continue;
            }

            // El payload se relee JUNTO con el reclamo, nunca se regenera.
            $datos = $db->query("
                SELECT fe.fe_payload, f.fa_cuf, f.fa_numero_siat
                FROM facturacion_electronica fe
                JOIN factura f ON f.fa_id = fe.fa_id
                WHERE fe.fe_id = " . $feId . "
            ")->fetch(PDO::FETCH_ASSOC);

            if (!$datos || empty($datos['fe_payload'])) {
                self::marcarCola($db, $feId, 'PENDIENTE', 'No hay payload firmado que enviar.');
                $resumen['descartadas']++;
                continue;
            }

            // La caja es la de pv_codigo 0 de esa sucursal, la misma que uso el
            // cierre de venta al armar el XML.
            $pvId = self::resolverPuntoVenta($suId, null);

            try {
                $resp = self::enviarFacturaOnline(
                    $datos['fe_payload'],
                    $faId,
                    $datos['fa_cuf'],
                    $suId,
                    $pvId
                );

                $fila['respuesta'] = self::describirRespuesta($resp);
                self::marcarCola($db, $feId, 'ENVIADO', $fila['respuesta']);

                if (self::respuestaEsAceptada($resp)) {
                    $resumen['enviadas']++;
                } else {
                    // Aceptada por el transporte pero con observacion: no se
                    // reintenta, porque reenviar el mismo XML no lo arregla.
                    $resumen['permanentes']++;
                }
            } catch (Throwable $e) {
                $fila['error'] = $e->getMessage();

                if (self::errorEsPermanente($e->getMessage())) {
                    self::marcarCola($db, $feId, 'REQUIERE_REVISION', $e->getMessage());
                    $resumen['permanentes']++;
                    $fila['permanente'] = true;
                } else {
                    $intentosNuevo = (int)$f['fe_intentos'] + 1;
                    if ($intentosNuevo >= SIAT_COLA_MAX_INTENTOS) {
                        self::marcarCola(
                            $db,
                            $feId,
                            'REQUIERE_REVISION',
                            "Se agotaron los intentos ({$intentosNuevo}): " . $e->getMessage()
                        );
                        $resumen['permanentes']++;
                        $fila['agotada'] = true;
                    } else {
                        self::marcarCola($db, $feId, 'PENDIENTE', $e->getMessage());
                        $resumen['reintentadas']++;
                        $fila['reintento_min'] = self::esperaColaMinutos($intentosNuevo);
                    }
                }
            }

            $resumen['detalle'][] = $fila;
        }

        return $resumen;
    }

    /* Deja la fila de la cola en un estado, con el motivo ya legible. */
    private static function marcarCola($db, $feId, $estado, $mensaje)
    {
        $db->prepare("
            UPDATE facturacion_electronica
            SET fe_estado_local = :estado,
                fe_mensajes = :msj
            WHERE fe_id = :fe_id
        ")->execute([
            ':estado' => $estado,
            ':msj'    => json_encode([
                'cola'    => $estado,
                'mensaje' => (string)$mensaje,
                'hora'    => date('c'),
            ], JSON_UNESCAPED_UNICODE),
            ':fe_id'  => $feId,
        ]);
    }

    private static function respuestaEsAceptada($resp)
    {
        if (!is_object($resp)) {
            return false;
        }
        $t = $resp->transaccion ?? null;
        return $t === true || $t === 'true' || $t === 1;
    }

    private static function describirRespuesta($resp)
    {
        if (!is_object($resp)) {
            return 'respuesta vacia del SIN';
        }
        $partes = [];
        if (isset($resp->codigoEstado)) {
            $partes[] = "codigoEstado={$resp->codigoEstado}";
        }
        if (!empty($resp->codigoRecepcion)) {
            $partes[] = "codigoRecepcion={$resp->codigoRecepcion}";
        }
        if (isset($resp->codigoDescripcion) && $resp->codigoDescripcion !== '') {
            $partes[] = (string)$resp->codigoDescripcion;
        }
        if (isset($resp->mensajesList) && is_array($resp->mensajesList)) {
            foreach ($resp->mensajesList as $m) {
                $codigo = $m->codigo ?? '';
                $desc   = $m->descripcion ?? '';
                $numDet = $m->numeroDetalle ?? '';
                $parte = trim("{$codigo} {$desc}");
                if ($parte !== '') {
                    $partes[] = ($numDet !== '' && $numDet !== null ? "detalle {$numDet}: " : '') . $parte;
                }
            }
        }
        return $partes ? implode(' | ', $partes) : 'sin detalle en la respuesta';
    }

    public static function procesarValidacionPendiente($faId, $ticket, $suId, $nroFactura)
    {
        $estadoResp = self::validarRecepcionFactura($ticket, $suId);

        $estado = $estadoResp->codigoEstado ?? null;

        $qr = null;
        if ($estado == 908) {
            $qr = self::generarQR(self::obtenerNitEmpresa(), $estadoResp->cuf ?? '', $nroFactura);
        }

        $db = mainModel::conectar();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                UPDATE facturacion_electronica
                SET fe_estado_siat = :estado
                WHERE fa_id = :fa_id
            ");
            $stmt->execute([
                ':estado'  => $estado,
                ':fa_id'   => $faId
            ]);

            if ($estado == 908 && $qr) {
                $stmtQr = $db->prepare("
                    UPDATE facturacion_electronica
                    SET fe_qr = :qr
                    WHERE fa_id = :fa_id
                ");
                $stmtQr->execute([':qr' => $qr, ':fa_id' => $faId]);

                $db->prepare("UPDATE factura SET fa_estado = 1 WHERE fa_id = :fa_id")
                    ->execute([':fa_id' => $faId]);
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("SIAT procesarValidacionPendiente fallo fa_id={$faId}: " . $e->getMessage());
            return false;
        }

        return $estado;
    }

    /* Motivos de anulacion que la aplicacion puede ofrecer.

       OJO, esto no es el catalogo completo del SIN. El catalogo oficial tiene
       11 motivos, pero no esta sincronizado en siat_parametricas (hoy solo hay
       METODO_PAGO, MONEDA, TIPO_DOCUMENTO_IDENTIDAD, TIPO_EMISION,
       TIPO_FACTURA_DOCUMENTO y UNIDAD_MEDIDA) y el servicio parametrico del
       piloto esta caido, asi que no se puede leer de ahi en este momento.

       En vez de escribir a mano los 11 textos del catalogo, que se desactualizan
       y ademas nadie puede contrastar, se ofrecen solo los DOS motivos que
       estan probados contra el SIN piloto:
         1  - ver debug_etapa7.php: dio 905 ANULACION CONFIRMADA
         2  - lo usa devolucionController al registrar una devolucion de
              mercaderia

       Cuando el servicio parametrico vuelva hay que sincronizar el catalogo
       completo y reemplazar este metodo por la lectura de siat_parametricas con
       par_tipo = 'MOTIVO_ANULACION'. Mandar un codigo sin verificar devuelve
       932 del SIN y la anulacion no se hace. */
    public static function motivosAnulacion()
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $codigos = [];
        try {
            $db = mainModel::conectar();
            $stmt = $db->query("
                SELECT par_codigo, par_descripcion
                  FROM siat_parametricas
                 WHERE par_tipo = 'MOTIVO_ANULACION' AND par_vigente = 1
                 ORDER BY par_codigo
            ");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $codigos[(int)$fila['par_codigo']] = $fila['par_descripcion'];
            }
        } catch (Exception $e) {
            error_log("SIAT motivosAnulacion: no se pudo leer siat_parametricas: " . $e->getMessage());
        }

        if (!$codigos) {
            $codigos = [
                1 => 'Error en la factura',
                2 => 'Devolucion de mercaderia',
            ];
        }

        $cache = $codigos;
        return $cache;
    }

    /* Traduce fe_estado_siat a algo que un humano pueda leer.

       fe_estado_siat mezcla dos cosas: los codigos numericos que devuelve el SIN
       (902, 908) y las etiquetas internas del flujo de contingencia (VALIDADO,
       OBSERVADO, ANULADA). Sin esta tabla, todo lo que no sea 908 caia en
       "RECHAZADA", que es una conclusion falsa para 902 y para una factura
       anulada a proposito. */
    public static function etiquetaEstadoFactura($estado)
    {
        $e = strtoupper(trim((string)$estado));

        if ($e === '') {
            return ['PENDIENTE DE ENVIO', 'bwar'];
        }

        $mapa = [
            '908'              => ['ACEPTADA POR EL SIN', 'bgr'],
            'VALIDADO'         => ['ACEPTADA POR EL SIN', 'bgr'],
            '902'              => ['RECHAZADA POR EL SIN', 'bdan'],
            'RECHAZADA'        => ['RECHAZADA POR EL SIN', 'bdan'],
            '901'              => ['EN CONTINGENCIA', 'bwar'],
            'CONTINGENCIA'     => ['EN CONTINGENCIA', 'bwar'],
            'PENDIENTE'        => ['EN CONTINGENCIA', 'bwar'],
            'PENDIENTE_VALIDACION' => ['EN CONTINGENCIA', 'bwar'],
            'SIN_ENVIAR'       => ['PENDIENTE DE ENVIO', 'bwar'],
            'OBSERVADO'        => ['OBSERVADA POR EL SIN', 'bwar'],
            'ANULADA'          => ['ANULADA', 'bdan'],
        ];

        if (isset($mapa[$e])) {
            return $mapa[$e];
        }

        // Cualquier otro codigo del SIN se muestra tal cual, sin inventar un
        // significado: es mejor mostrar "904" que mentir diciendo "RECHAZADA".
        return [$e, 'bgry'];
    }

    public static function anularFactura($cuf, $codigoMotivo, $suId, $pvId = null)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        if (!$pv || empty($pv->pv_cuis) || empty($pv->pv_cufd)) {
            throw new Exception("SIAT anularFactura: la caja no tiene CUIS o CUFD para su_id={$suId}");
        }

        /* La anulacion es una operacion en linea, asi que necesita un CUFD
           vigente AHORA, no el que se uso al emitir la factura. Si el guardado ya
           caduco, el SIN responde 123 "CUFD FUERA DE TOLERANCIA" y ese error no
           se recupera reintentando: hace falta pedir uno nuevo. Se piden con el
           mismo metodo que usa la rotacion de contingencia. */
        $restan = empty($pv->pv_cufd_vigente_hasta)
            ? -1
            : strtotime($pv->pv_cufd_vigente_hasta) - time();
        if ($restan <= 1800) {
            try {
                $nuevo = self::obtenerCUFD($suId, null, null, $pvId);
                if (!empty($nuevo['cufd'])) {
                    $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
                }
            } catch (Exception $e) {
                error_log("SIAT anularFactura: no se pudo renovar el CUFD: " . $e->getMessage());
            }
        }

        $codigoSucursal = self::obtenerCodigoSucursal($suId);
        $codigoPuntoVenta = $pv->pv_codigo_sin !== null
            ? (int)$pv->pv_codigo_sin
            : (int)$pv->pv_codigo;

        $client = self::clienteSOAP('compra_venta');

        /* Solicitud copiada del WSDL tal como la manda debug_etapa7.php, que es
           la unica que se ha visto funcionar contra el piloto (905 con
           transaccion en true). CodigoDocumentoSector, tipoFacturaDocumento y
           codigoEmision no son opcionales segun solicitudRecepcion: si faltan,
           el SIN no puede resolver la caja y contesta 914 o 959. */
        $resp = $client->anulacionFactura([
            'SolicitudServicioAnulacionFactura' => [
                'codigoAmbiente'        => SIAT_AMBIENTE,
                'codigoModalidad'       => SIAT_MODALIDAD,
                'codigoSistema'         => SIAT_COD_SISTEMA,
                'codigoSucursal'        => $codigoSucursal,
                'codigoPuntoVenta'      => $codigoPuntoVenta,
                'codigoDocumentoSector' => 1,
                'tipoFacturaDocumento'  => 1,
                'codigoEmision'         => 1,
                'nit'                   => self::obtenerNitEmpresa(),
                'cuis'                  => $pv->pv_cuis,
                'cufd'                  => $pv->pv_cufd,
                'codigoMotivo'          => (int)$codigoMotivo,
                'cuf'                   => $cuf,
            ]
        ]);

        /* anulacionFactura reutiliza el tipo de respuesta de la facturacion:
           RespuestaServicioFacturacion, no RespuestaServicioAnulacion. Leyendo
           el nombre equivocado $r llegaba null y ni se actualizaba el estado
           local ni el llamador se enteraba del codigo. */
        $r = $resp->RespuestaServicioFacturacion ?? $resp->RespuestaServicioAnulacion ?? $resp;

        if ($r && !empty($r->transaccion)) {
            $db = mainModel::conectar();
            $db->beginTransaction();
            try {
                $db->prepare("UPDATE factura SET fa_estado = 2 WHERE fa_cuf = :cuf")
                    ->execute([':cuf' => $cuf]);

                // fe_cuf fue eliminada: se localiza por el CUF de la factura
                $db->prepare("
                    UPDATE facturacion_electronica fe
                    JOIN factura f ON f.fa_id = fe.fa_id
                    SET fe_estado_siat = 'ANULADA'
                    WHERE f.fa_cuf = :cuf
                ")->execute([':cuf' => $cuf]);

                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                error_log("SIAT anularFactura: fallo al actualizar estados localmente: " . $e->getMessage());
            }
        }

        return $r;
    }

    /* Estado local de una factura a efectos de poder anularla.

       Devuelve un array con dos claves:
         'anulable'  bool,   si se puede pedir la anulacion ahora
         'motivo'    string, por que no, o 'ok'

       Existe para que la interfaz no ofrezca un boton que va a fallar, y para
       que el controller no le mande al SIN una peticion que se sabe que va a
       ser rechazada. Las reglas salen del flujo real, no de supuestos:

         - Sin CUF no hay nada que anular en el SIN. Una nota de venta nunca
           lleva CUF: es un comprobante interno, no un documento fiscal.
         - Una factura ya ANULADA no se anula dos veces: el SIN responde 936.
         - Una factura en CONTINGENCIA no se anula en linea. Su anulacion va
           por el paquete que se emitio, y el SIN responde 924. */
    public static function estadoAnulacionFactura($faId)
    {
        $faId = (int)$faId;
        if ($faId <= 0) {
            return ['anulable' => false, 'motivo' => 'Factura no valida', 'estado' => null,
                    'cuf' => null, 'numero' => null];
        }

        $db = mainModel::conectar();
        $stmt = $db->prepare("
            SELECT f.fa_id, f.fa_cuf, f.fa_numero_siat, f.fa_numero_control, f.su_id,
                   f.fa_estado, fe.fe_estado_siat
              FROM factura f
              LEFT JOIN facturacion_electronica fe ON fe.fa_id = f.fa_id
             WHERE f.fa_id = :fa_id
             LIMIT 1
        ");
        $stmt->execute([':fa_id' => $faId]);
        $f = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$f) {
            return ['anulable' => false, 'motivo' => 'La factura no existe', 'estado' => null,
                    'cuf' => null, 'numero' => null];
        }

        $estado = strtoupper(trim((string)($f['fe_estado_siat'] ?? '')));

        $base = [
            'cuf'        => $f['fa_cuf'] ?: null,
            'numero'     => $f['fa_numero_siat'] !== null ? (string)(int)$f['fa_numero_siat'] : null,
            'estado'     => $estado !== '' ? $estado : null,
            'su_id'      => (int)$f['su_id'],
        ];

        if (empty($f['fa_cuf'])) {
            $base['anulable'] = false;
            $base['motivo'] = 'Esta venta es una nota de venta: no tiene CUF y no se anula ante el SIN.';
            return $base;
        }

        if ($estado === 'ANULADA' || (int)$f['fa_estado'] === 2) {
            $base['anulable'] = false;
            $base['motivo'] = 'Esta factura ya esta anulada.';
            return $base;
        }

        if ($estado === 'CONTINGENCIA') {
            $base['anulable'] = false;
            $base['motivo'] = 'Esta factura se emitio por contingencia. Su anulacion va dentro del paquete, no en linea.';
            return $base;
        }

        if ($estado === '902' || $estado === 'RECHAZADA') {
            $base['anulable'] = false;
            $base['motivo'] = 'El SIN rechazo esta factura. No hay nada que anular.';
            return $base;
        }

        if (empty($f['fa_numero_siat'])) {
            $base['anulable'] = false;
            $base['motivo'] = 'La factura no tiene numero fiscal asignado.';
            return $base;
        }

        /* Solo se anula lo que el SIN ya acepto.

           El CUF se genera aqui, en local, asi que una factura con CUF todavia
           no la conoce el SIN: no esta en su base y no hay nada que anular.
           Mandarsela seria gastar una llamada SOAP para recibir un rechazo. Los
           estados que SI valen son los que el SIN confirmo, mas 690 y OBSERVADO,
           que tambien constan en el SIN aunque con observaciones. */
        $aceptadaPorSin = ['908', 'VALIDADO', '690', 'OBSERVADO'];
        if (!in_array($estado, $aceptadaPorSin, true)) {
            $base['anulable'] = false;
            $base['motivo'] = 'El SIN todavia no a aceptado esta factura'
                . ($estado === '' ? ' (esta pendiente de envio).' : " (estado {$estado}).")
                . ' Solo se pueden anular las facturas que el SIN ya valido.';
            return $base;
        }

        $base['anulable'] = true;
        $base['motivo'] = 'ok';
        return $base;
    }

    /* Anula una factura en el SIN, verificando antes las condiciones.

       Envuelve a anularFactura() con las comprobaciones de
       estadoAnulacionFactura() y con la validacion del motivo, para que el
       llamador no pueda, por un descuido, gastar una llamada SOAP en una
       peticion que el SIN va a rechazar.

       Devuelve un array con 'ok' y 'mensaje'. No lanza excepcion: el flujo de
       la interfaz necesita poder mostrar el motivo del rechazo sin romperse. */
    public static function anularFacturaConControl($faId, $codigoMotivo)
    {
        $estado = self::estadoAnulacionFactura($faId);

        if (!$estado['anulable']) {
            return ['ok' => false, 'mensaje' => $estado['motivo'], 'codigo' => null];
        }

        $codigoMotivo = (int)$codigoMotivo;
        $motivos = self::motivosAnulacion();
        if (!isset($motivos[$codigoMotivo])) {
            $permitidos = implode(', ', array_keys($motivos));
            return [
                'ok' => false,
                'mensaje' => "Motivo {$codigoMotivo} no valido. Solo se aceptan: {$permitidos}.",
                'codigo' => null,
            ];
        }

        try {
            $r = self::anularFactura($estado['cuf'], $codigoMotivo, $estado['su_id']);
        } catch (Exception $e) {
            error_log("SIAT anularFacturaConControl fa_id={$faId}: " . $e->getMessage());
            return [
                'ok' => false,
                'mensaje' => 'No se pudo comunicar con el SIN: ' . $e->getMessage(),
                'codigo' => null,
            ];
        }

        $codigo = $r->codigoEstado ?? null;
        $descripcion = trim((string)($r->codigoDescripcion ?? ''));

        // El SIN confirma con 905 y transaccion en true. 906 es rechazo con
        // detalle en mensajesList, 936 ya estaba anulada, 924 no existe.
        $ok = !empty($r->transaccion);

        if ($ok) {
            return [
                'ok' => true,
                'mensaje' => 'Factura anulada ante el SIN. ' . ($descripcion !== '' ? $descripcion : ''),
                'codigo' => $codigo,
            ];
        }

        $detalle = [];
        foreach (self::aLista($r->mensajesList ?? null) as $m) {
            $detalle[] = ($m->codigo ?? '?') . ': ' . ($m->descripcion ?? '?');
        }
        $mensaje = trim($descripcion);
        if ($detalle) {
            $mensaje = ($mensaje !== '' ? $mensaje . ' | ' : '') . implode(' | ', array_slice($detalle, 0, 3));
        }
        if ($mensaje === '') {
            $mensaje = 'El SIN no acepto la anulacion.';
        }

        return ['ok' => false, 'mensaje' => $mensaje, 'codigo' => $codigo];
    }

    /* =================================================================
       EVENTOS SIGNIFICATIVOS (contingencia)

       Reglas que el SIN impone y que hacen fallar el registro si no se
       respetan (codigos de error del catalogo oficial):

       - El SIN distingue dos CUFD y ambos son obligatorios:
           cufd       = CUFD vigente al momento de registrar el evento
           cufdEvento = CUFD que estaba vigente CUANDO OCURRIO la contingencia
         Si se mandan iguales: error 984.
       - 914 CUFD invalido: el cufd guardado puede estar invalidado en el SIN.
       - 981 Rango de fechas invalido: se cumple cuando el periodo no cae
         dentro de la vigencia del cufdEvento. El periodo ademas debe tener
         duracion (inicio < fin) y estar en el pasado.
       - Un mismo cufdEvento no puede reutilizarse con un rango solapado.
       ================================================================= */
    public static function registrarEventoSignificativo($suId, $pvId, $codigoMotivoEvento, $descripcion, $fechaInicio = null, $fechaFin = null)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        if (!$pv) {
            throw new Exception("SIAT registrarEvento: la caja no existe para su_id={$suId}");
        }
        if (empty($pv->pv_cuis) || empty($pv->pv_cufd)) {
            throw new Exception(
                "SIAT registrarEvento: la caja {$pv->pv_nombre} (pv_id={$pv->pv_id}) no tiene CUIS ni CUFD.\n" .
                "Registre la caja con registrarPuntoVenta() y pida un CUFD antes de seguir."
            );
        }
        if (empty($pv->pv_registrado_sin)) {
            throw new Exception(
                "SIAT registrarEvento: la caja {$pv->pv_nombre} (pv_id={$pv->pv_id}) todavia no esta\n" .
                "registrada en el SIN (pv_registrado_sin = 0). Llamar a registrarPuntoVenta() primero:\n" .
                "si se envia sin registrar, el SIN responde 982 'NO EXISTEN PUNTOS DE VENTA ASOCIADOS'."
            );
        }

        $codigoSucursal   = self::obtenerCodigoSucursal($suId);
        $codigoPuntoVenta = $pv->pv_codigo_sin !== null
            ? (int)$pv->pv_codigo_sin
            : (int)$pv->pv_codigo;

        // El CUFD vigente es el que se registro ante el SIN. El periodo del
        // evento debe describir una contingencia anterior a esta emision y
        // caer dentro de la vigencia del CUFD que estaba activo entonces.
        $cufd        = $pv->pv_cufd;
        $cufdEvento  = $pv->pv_cufd_anterior;
        $cufdVigente = $pv->pv_cufd_vigente_hasta;

        if (empty($cufdEvento)) {
            throw new Exception(
                "SIAT registrarEvento: falta el CUFD vigente al ocurrir la contingencia.\n" .
                "El campo pv_cufd_anterior quedo vacio: rotate el CUFD primero y registre\n" .
                "el evento con el CUFD guardado en esa rotacion."
            );
        }

        // La vigencia del CUFD es de 24 horas: se deriva de la fecha fin.
        $cufdEventoEmitido = strtotime($cufdVigente . ' -24 hours');

        $fechaFinEvento    = $fechaFin    ?: date('Y-m-d\TH:i:s', time() - 120) . '.000';
        $fechaInicioEvento = $fechaInicio ?: date('Y-m-d\TH:i:s', strtotime($fechaFinEvento) - 600) . '.000';

        $tsInicio = strtotime($fechaInicioEvento);
        $tsFin    = strtotime($fechaFinEvento);

        if ($tsInicio >= $tsFin) {
            throw new Exception("SIAT registrarEvento: fechaHoraInicioEvento debe ser anterior a fechaHoraFinEvento");
        }
        if ($tsInicio < $cufdEventoEmitido) {
            throw new Exception(
                "SIAT registrarEvento: el periodo no cae dentro de la vigencia del cufdEvento.\n" .
                "El cufdEvento se emitio el " . date('Y-m-d H:i:s', $cufdEventoEmitido) . "."
            );
        }

        $db = mainModel::conectar();
        $evCodigo = 'EVT-' . date('YmdHis') . '-' . $suId . '-' . $pv->pv_codigo . '-' . $codigoMotivoEvento;

        // Se registra localmente ANTES de llamar al SIN, para que quede
        // rastro aunque la llamada falle.
        $ins = $db->prepare("
            INSERT INTO siat_eventos
                (su_id, ev_codigo, ev_codigo_evento, ev_cufd, ev_cufd_evento,
                 ev_fecha_inicio, ev_fecha_fin, ev_descripcion, ev_estado, ev_creado_en)
            VALUES (:su_id, :codigo, :motivo, :cufd, :cufd_evento,
                    :inicio, :fin, :descripcion, 'registrado', NOW())
        ");
        $ins->execute([
            ':su_id'        => $suId,
            ':codigo'       => $evCodigo,
            ':motivo'       => (string)$codigoMotivoEvento,
            ':cufd'         => $cufd,
            ':cufd_evento'  => $cufdEvento,
            ':inicio'       => $fechaInicioEvento,
            ':fin'          => $fechaFinEvento,
            ':descripcion'  => $descripcion,
        ]);

        $client = self::clienteSOAP('operaciones');

        try {
            $resp = $client->registroEventoSignificativo([
                'SolicitudEventoSignificativo' => [
                    'codigoAmbiente'        => SIAT_AMBIENTE,
                    'codigoMotivoEvento'    => (int)$codigoMotivoEvento,
                    'codigoPuntoVenta'      => $codigoPuntoVenta,
                    'codigoSistema'         => SIAT_COD_SISTEMA,
                    'codigoSucursal'        => $codigoSucursal,
                    'cuis'                  => $pv->pv_cuis,
                    'cufd'                  => $cufd,
                    'cufdEvento'            => $cufdEvento,
                    'descripcion'           => $descripcion,
                    'fechaHoraFinEvento'    => $fechaFinEvento,
                    'fechaHoraInicioEvento' => $fechaInicioEvento,
                    'nit'                   => self::obtenerNitEmpresa(),
                ]
            ]);

            $d = $resp->RespuestaListaEventos ?? null;
        } catch (SoapFault $e) {
            $db->prepare("UPDATE siat_eventos
                         SET ev_estado = 'rechazado', ev_respuesta_siat = :resp
                         WHERE ev_codigo = :codigo")
                ->execute([':resp' => json_encode(['error' => $e->getMessage()]), ':codigo' => $evCodigo]);
            throw new Exception("SIAT registrarEvento: " . $e->getMessage());
        }

        $estado = 'enviado';
        if ($d && !empty($d->transaccion)) {
            $estado = 'validado';
        }
        if ($d && !empty($d->mensajesList)) {
            $estado = 'rechazado';
        }

        $db->prepare("
            UPDATE siat_eventos
            SET ev_estado = :estado, ev_respuesta_siat = :respuesta
            WHERE ev_codigo = :codigo
        ")->execute([
            ':estado'    => $estado,
            ':respuesta' => json_encode($d),
            ':codigo'    => $evCodigo,
        ]);

        return [
            'ev_codigo'        => $evCodigo,
            'estado'           => $estado,
            'codigo_recepcion' => $d->codigoRecepcionEventoSignificativo ?? null,
            'mensajes'         => self::aLista($d->mensajesList ?? null),
            'cufd'             => $cufd,
            'cufd_evento'      => $cufdEvento,
            'fecha_inicio'     => $fechaInicioEvento,
            'fecha_fin'        => $fechaFinEvento,
        ];
    }

    /* Rota el CUFD de una caja. El CUFD anterior queda guardado como
       pv_cufd_anterior porque es el que hay que reportar al SIN como
       cufdEvento de una contingencia ocurierta antes de la rotacion. */
    public static function rotarCufdEvento($suId, $pvId)
    {
        $pv = self::obtenerConfiguracionPuntoVenta($suId, $pvId);
        if (!$pv) {
            throw new Exception("SIAT rotarCufdEvento: la caja no existe");
        }

        $db = mainModel::conectar();
        $nuevo = self::obtenerCUFD($suId, null, null, $pvId);
        if (!$nuevo) {
            return false;
        }

        // Guardar el anterior ANTES de que obtenerCUFD lo pise: el metodo
        // ya actualizo pv_cufd, asi que aqui solo queda registro del previo.
        $db->prepare("UPDATE puntos_venta
                      SET pv_cufd_anterior = :previo
                      WHERE pv_id = :pv_id")
            ->execute([':previo' => $pv->pv_cufd, ':pv_id' => $pv->pv_id]);

        return $nuevo;
    }
}
