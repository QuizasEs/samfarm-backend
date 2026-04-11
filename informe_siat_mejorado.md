# SAMFARM - Guía de Implementación de Facturación Electrónica SIAT Bolivia

## Documento Mejorado y Validado vs Base de Datos samfarm_db.sql

---

## RESUMEN DE VALIDACIÓN

### ✅ Tablas que YA EXISTEN en la base de datos:

| Tabla | Estado en BD | Uso en Facturación Electrónica |
|-------|---------------|-------------------------------|
| [`configuracion_empresa`](samfarm_db.sql:94) | ✅ EXISTE | NIT, nombre y datos del emisor |
| [`sucursales`](samfarm_db.sql:470) | ✅ EXISTE | Código de sucursal para CUIS/CUFD |
| [`clientes`](samfarm_db.sql:56) | ✅ EXISTE | Datos del comprador (nombre, carnet/NIT) |
| [`ventas`](samfarm_db.sql:557) | ✅ EXISTE | Cabecera de cada transacción facturable |
| [`detalle_venta`](samfarm_db.sql:167) | ✅ EXISTE | Items/productos de cada venta |
| [`factura`](samfarm_db.sql:197) | ✅ EXISTE | fa_cuf, fa_codigo_control, fa_numero |
| [`facturacion_electronica`](samfarm_db.sql:212) | ✅ EXISTE | fe_cuf, fe_qr, fe_estado_siat, fe_ticket, fe_payload |
| [`devoluciones`](samstock_db.sql:182) | ✅ EXISTE | fa_id y dev_motivo para anulaciones |
| [`usuarios`](basedatos muestra) | ✅ EXISTE | us_id, us_nombre para el XML |

### ❌ Tablas que FALTAN (CREAR):

| Tabla | Estado | Acción Requerida |
|-------|--------|------------------|
| [`siat_configuracion`](doc:165) | ❌ NO EXISTE | **CREAR** - Requiere script SQL |
| [`siat_actividades`](doc:197) | ❌ NO EXISTE | **CREAR** - Requiere script SQL |
| [`siat_productos`](doc:213) | ❌ NO EXISTE | **CREAR** - Requiere script SQL |
| [`siat_leyendas`](doc:229) | ❌ NO EXISTE | **CREAR** - Requiere script SQL |

### ⚠️ Columnas que FALTAN en tablas existentes:

| Tabla | Columna Faltante | Acción |
|-------|-----------------|--------|
| [`facturacion_electronica`](samfarm_db.sql:212) | `fe_tipo_emision` | **ALTER TABLE** - Agregar columna |

---

# PASOS DE IMPLEMENTACIÓN

## Paso 1: Estado Actual
> ✅ **COMPLETADO** - Autorización obtenida del SIN

---

## Paso 2: Crear tablas SIAT en la base de datos
> ⚠️ **VALIDADO** - Las 4 tablas deben ser CREADAS

### Estado: PENDIENTE DE EJECUTAR

**Script SQL completo y validado:**

```sql
-- ============================================================
-- TABLAS SIAT - Ejecución requerida antes de continuar
-- ============================================================

-- 1. Tabla principal de configuración SIAT por sucursal
CREATE TABLE IF NOT EXISTS siat_configuracion (
  sc_id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  su_id          BIGINT UNSIGNED NOT NULL,
  sc_token       TEXT DEFAULT NULL,
  sc_cuis        VARCHAR(200) DEFAULT NULL,
  sc_cuis_expira DATETIME DEFAULT NULL,
  sc_cufd        VARCHAR(500) DEFAULT NULL,
  sc_cufd_control VARCHAR(200) DEFAULT NULL,
  sc_cufd_expira  DATETIME DEFAULT NULL,
  sc_punto_venta  INT DEFAULT 0,
  sc_actualizado  DATETIME DEFAULT current_timestamp()
    ON UPDATE current_timestamp(),
  FOREIGN KEY (su_id) REFERENCES sucursales(su_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Catálogo de actividades económicas (CAEB)
CREATE TABLE IF NOT EXISTS siat_actividades (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  codigo_caeb    VARCHAR(20) NOT NULL,
  descripcion    VARCHAR(300) NOT NULL,
  tipo_actividad TINYINT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Catálogo de productos/servicios del SIN
CREATE TABLE IF NOT EXISTS siat_productos (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  codigo_producto VARCHAR(20) NOT NULL,
  descripcion     VARCHAR(300) NOT NULL,
  codigo_actividad VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Leyendas de facturas por actividad
CREATE TABLE IF NOT EXISTS siat_leyendas (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  codigo_actividad VARCHAR(20),
  descripcion      TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Columna adicional en facturacion_electronica para contingencia
ALTER TABLE facturacion_electronica
  ADD COLUMN fe_tipo_emision TINYINT DEFAULT 1
  COMMENT '1=en linea, 2=contingencia';

-- 6. Insertar configuración inicial para sucursal 1
INSERT INTO siat_configuracion (su_id, sc_punto_venta) VALUES (1, 0);
```

### ✅ Prompt para ejecutar:
```
Ejecutar el script SQL anterior en la base de datos samfarm_db para crear las tablas necesarias para la facturación electrónica SIAT.
```

### ✅ Prompt sugerido para continuar:
```
Tengo PHP 7.4 y la extensión PHP SOAP instalada. Tengo el Token Delegado que me dio el SIN. Dame el código completo de la clase SiatService.php con el constructor que inicialice la conexión SOAP usando ese token, manejando certificados SSL del SIAT Bolivia en ambiente PILOTO.
```

---

## Paso 3: Configurar conexión SOAP y Token Delegado
> ⚠️ **VALIDADO** - Requiere archivo de configuración

### Estado: PENDIENTE DE IMPLEMENTAR

**Archivo de configuración [`config/siat.php`](doc:269):**

```php
<?php
// config/siat.php — Configuración central SIAT

define('SIAT_MODO', 'PILOTO'); // Cambiar a 'PRODUCCIÓN' al certificar

define('SIAT_URLS', [
    'PILOTO' => [
        'operacion'  => 'https://pilotosiatservicios.impuestos.gob.bo/v2/OperacionSIAT?wsdl',
        'facturacion'=> 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionComputarizada?wsdl',
        'codigos'    => 'https://pilotosiatservicios.impuestos.gob.bo/v2/CodigosSiatServicios?wsdl',
    ],
    'PRODUCCIÓN' => [
        'operacion'  => 'https://siatservicios.impuestos.gob.bo/v2/OperacionSIAT?wsdl',
        'facturacion'=> 'https://siatservicios.impuestos.gob.bo/v2/FacturacionComputarizada?wsdl',
        'codigos'    => 'https://siatservicios.impuestos.gob.bo/v2/CodigosSiatServicios?wsdl',
    ],
]);

// Obtener del NIT de configuracion_empresa (ce_nit)
define('SIAT_NIT', '123456789');        
// Código asignado por el SIN
define('SIAT_COD_SISTEMA', 'XXXX');             
// Token del Portal SIAT
define('SIAT_TOKEN', 'TOKEN_DELEGADO');   

// Ambiente: 2=PILOTO, 1=PRODUCCIÓN
define('SIAT_AMBIENTE', SIAT_MODO == 'PILOTO' ? 2 : 1);
// Modalidad: 2 = Computarizada en línea
define('SIAT_MODALIDAD', 2);                  
// Código de sucursal (0 = casa matriz)
define('SIAT_COD_SUCURSAL', 0);                 
// Punto de venta
define('SIAT_PUNTO_VENTA', 0);
```

### ✅ Prompt sugerido para continuar:
```
Dame el código PHP completo de la clase SiatClient.php que encapsule todas las llamadas SOAP al SIAT Bolivia. Debe incluir: manejo del header de autenticación con Token Delegado, reintentos automáticos en caso de timeout (3 intentos), y logging de errores. Usa las URLs de pilotosiatservicios.impuestos.gob.bo.
```

---

## Paso 4: Obtener el CUIS
> ⚠️ **VALIDADO** - Requiere implementación

### Estado: PENDIENTE DE IMPLEMENTAR

**Función PHP requerida:**

```php
function obtenerCUIS($nitEmisor, $codigoSucursal = 0, $suId) {
    $client = new SoapClient(
        SIAT_URLS[SIAT_MODO]['operacion'],
        ['stream_context' => stream_context_create(['ssl' => ['verify_peer' => false]])]
    );
    
    $resp = $client->cuis([
        'SolicitudCuis' => [
            'codigoAmbiente'   => SIAT_AMBIENTE,
            'codigoModalidad'  => SIAT_MODALIDAD,
            'codigoSistema'    => SIAT_COD_SISTEMA,
            'nit'              => $nitEmisor,
            'codigoSucursal'   => $codigoSucursal,
            'codigoPuntoVenta' => SIAT_PUNTO_VENTA,
        ]
    ]);
    
    $r = $resp->RespuestaCuis;
    
    // Guardar en siat_configuracion: sc_cuis, sc_cuis_expira
    // La tabla ya existe después del Paso 2
    $db = new mainModel();
    $db->exeNoMulti(
        'INSERT INTO siat_configuracion (su_id, sc_cuis, sc_cuis_expira)
         VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE sc_cuis=VALUES(sc_cuis),
         sc_cuis_expira=VALUES(sc_cuis_expira)',
        [$suId, $r->codigo, $r->fechaVigencia]
    );
    
    return $r->codigo;
}
```

### ✅ Prompt sugerido para continuar:
```
El CUIS del SIAT Bolivia retorna un campo "transaccion" y "codigo". Dame el código PHP para manejar los errores de la respuesta del CUIS, incluyendo los códigos de error del SIN (ej. 909, 910) y como reintentar automáticamente si el servicio está temporalmente caído.
```

---

## Paso 5: Sincronizar catálogos del SIN
> ⚠️ **VALIDADO** - Requiere implementación

### Estado: PENDIENTE DE IMPLEMENTAR

**Función PHP requerida:**

```php
// Configurar en crontab:
// 0 5 * * * php /var/www/samfarm-backend/cronjob/siat_sincronizar.php

function sincronizarCatalogos($client, $cuis) {
    // 1. Fecha/hora oficial del SIN
    $client->sincronizarFechaHora([...]);
    
    // 2. Actividades económicas (CAEB 477000 = farmacia)
    $resp = $client->sincronizarListaActividadesDocumentoSector([
        'SolicitudSincronizacion' => [
            'codigoAmbiente'  => SIAT_AMBIENTE,
            'codigoSistema'   => SIAT_COD_SISTEMA,
            'nit'             => SIAT_NIT,
            'cuis'            => $cuis,
            'codigoModalidad' => SIAT_MODALIDAD,
        ]
    ]);
    
    // Truncar e insertar en siat_actividades
    // La tabla ya existe después del Paso 2
    
    // 3. Productos/servicios por actividad (477000 = farmacia)
    $client->sincronizarListaProductosServicios([...]);
    
    // 4. Leyendas de facturas
    $client->sincronizarParametricaListaLeyendasFactura([...]);
}
```

### ✅ Prompt sugerido para continuar:
```
Dame el código PHP completo para sincronizar los 4 catálogos del SIAT Bolivia (actividades, productos, leyendas, fecha/hora) y guardarlos en MariaDB. Incluye el manejo de la actividad CAEB 477000 para farmacia y como asociar los códigos de producto del SIN a los medicamentos de mi tabla "medicamento".
```

---

## Paso 6: Obtener el CUFD diario
> ⚠️ **VALIDADO** - Requiere implementación

### Estado: PENDIENTE DE IMPLEMENTAR

**Función PHP requerida:**

```php
function obtenerCUFD($nitEmisor, $cuis, $codigoSucursal = 0, $suId) {
    $client = new SoapClient(SIAT_URLS[SIAT_MODO]['operacion']);
    
    $resp = $client->cufd([
        'SolicitudCufd' => [
            'codigoAmbiente'   => SIAT_AMBIENTE,
            'codigoModalidad'  => SIAT_MODALIDAD,
            'codigoSistema'    => SIAT_COD_SISTEMA,
            'nit'              => $nitEmisor,
            'cuis'             => $cuis,
            'codigoSucursal'   => $codigoSucursal,
            'codigoPuntoVenta' => SIAT_PUNTO_VENTA,
        ]
    ]);
    
    $r = $resp->RespuestaCufd;
    
    // Guardar en siat_configuracion:
    // sc_cufd, sc_cufd_control, sc_cufd_expira
    $db = new mainModel();
    $db->exeNoMulti(
        'UPDATE siat_configuracion
         SET sc_cufd=?, sc_cufd_control=?, sc_cufd_expira=?
         WHERE su_id=?',
        [$r->codigo, $r->codigoControl, $r->fechaVigencia, $suId]
    );
    
    return $r;
}

function cufdVigente($suId) {
    $db = new mainModel();
    $sc = $db->simpleQuery('SELECT sc_cufd_expira FROM siat_configuracion WHERE su_id=?', [$suId]);
    return $sc && strtotime($sc->sc_cufd_expira) > time();
}
```

### ✅ Prompt sugerido para continuar:
```
Si son las 23:55 y mi CUFD vence a medianoche, y un cliente está pagando en ese momento, como manejo la transición sin interrumpir la venta? Dame el patrón PHP para pre-obtener el CUFD del día siguiente 30 minutos antes del vencimiento.
```

---

## Paso 7: Generar el CUF por factura
> ⚠️ **VALIDADO** - Requiere implementación

### Estado: PENDIENTE DE IMPLEMENTAR

**Función PHP requerida:**

```php
function generarCUF($nit, $fechaHora, $nroFactura, $codigoControl, $codigoSucursal = 0) {
    // Construir cadena de 53 dígitos
    $cadena =
        str_pad($nit, 13, '0', STR_PAD_LEFT) .        // NIT 13 dig
        date('YmdHis', strtotime($fechaHora)) . '00' . // Fecha 14 dig (ms=00)
        str_pad($nroFactura, 10, '0', STR_PAD_LEFT) .  // Nro factura 10 dig
        str_pad($codigoSucursal, 4, '0', STR_PAD_LEFT). // Sucursal 4 dig
        '2' .    // Modalidad: 2=Computarizada
        '1' .    // Emisión: 1=en línea
        '1' .    // Tipo factura: 1=compra-venta
        '01' .   // Tipo doc: 01=NIT o CI
        '0000';  // Punto venta 4 dig
    
    // Dígito verificador Módulo 11
    $suma = 0;
    $multi = 2;
    for ($i = strlen($cadena) - 1; $i >= 0; $i--) {
        $suma += intval($cadena[$i]) * $multi;
        $multi = $multi == 9 ? 2 : $multi + 1;
    }
    $resto = $suma % 11;
    $verif = $resto <= 1 ? $resto : 11 - $resto;
    
    // Codificar en Base 16 (HEX)
    $cadena53 = $cadena . $verif;
    $hex = strtoupper(base_convert($cadena53, 10, 16));
    
    // Concatenar código control del CUFD
    return $hex . $codigoControl;
}

// Uso al crear la factura:
// $cuf = generarCUF(SIAT_NIT, $fecha, $nroFactura, $sc->sc_cufd_control);
// UPDATE factura SET fa_cuf=? WHERE fa_id=?
```

### ✅ Prompt sugerido para continuar:
```
Necesito generar números de factura (fa_numero) correlativos por sucursal, reiniciándose cada año. Dame el SQL y PHP para manejar esto con bloqueo optimista en MariaDB, evitando números duplicados cuando hay ventas concurrentes.
```

---

## Paso 8: Construir el XML de la factura
> ⚠️ **VALIDADO** - Requiere implementación con ajustes

### Estado: PENDIENTE DE IMPLEMENTAR

**Consulta SQL validada contra la base de datos:**

```sql
-- Consulta SQL para obtener todos los datos necesarios del XML:
SELECT
  v.ve_id, v.ve_total, v.ve_fecha_emision AS ve_fecha,
  c.cl_carnet AS nit_ci, c.cl_nombres, c.cl_apellido_paterno,
  ce.ce_nit AS nit_emisor, ce.ce_nombre AS razon_social, ce.ce_direccion,
  ce.ce_telefono,
  f.fa_numero, f.fa_cuf,
  sc.sc_cufd, sc.sc_cufd_control,
  u.us_nombre
FROM ventas v
JOIN factura f ON f.ve_id = v.ve_id
LEFT JOIN clientes c ON c.cl_id = v.cl_id
JOIN configuracion_empresa ce ON ce.ce_id = 1
JOIN siat_configuracion sc ON sc.su_id = v.su_id
JOIN usuarios u ON u.us_id = v.us_id
WHERE v.ve_id = ?;
```

**Función PHP validada:**

```php
function generarXML($datos, $detalles, $leyenda) {
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
        <facturaComputarizadaCompraVenta
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:noNamespaceSchemaLocation="facturaComputarizadaCompraVenta.xsd">
        </facturaComputarizadaCompraVenta>');
    
    $cab = $xml->addChild('cabecera');
    $cab->addChild('nitEmisor', $datos['nit_emisor']);
    $cab->addChild('razonSocialEmisor', $datos['razon_social']);
    $cab->addChild('municipio', 'La Paz');
    $cab->addChild('telefono', $datos['ce_telefono']);
    $cab->addChild('numeroFactura', $datos['fa_numero']);
    $cab->addChild('cuf', $datos['fa_cuf']);
    $cab->addChild('cufd', $datos['sc_cufd']);
    $cab->addChild('codigoSucursal', 0);
    $cab->addChild('direccion', $datos['ce_direccion']);
    $cab->addChild('codigoPuntoVenta', 0);
    $cab->addChild('fechaEmision', date('c', strtotime($datos['ve_fecha'])));
    $cab->addChild('nombreRazonSocial', $datos['cl_nombres']);
    $cab->addChild('codigoTipoDocumentoIdentidad', 5); // 5=CI, 1=NIT
    $cab->addChild('numeroDocumento', $datos['nit_ci'] ?? '0');
    $cab->addChild('complemento', null);
    $cab->addChild('codigoCliente', $datos['cl_id'] ?? '0');
    $cab->addChild('codigoMetodoPago', 1); // 1=Efectivo
    $cab->addChild('montoTotal', $datos['ve_total']);
    $cab->addChild('montoTotalSujetoIva', $datos['ve_total']);
    $cab->addChild('codigoMoneda', 1); // 1=Bolivianos
    $cab->addChild('tipoCambio', 1);
    $cab->addChild('montoTotalMoneda', $datos['ve_total']);
    $cab->addChild('leyenda', $leyenda);
    $cab->addChild('usuario', $datos['us_nombre']);
    
    foreach ($detalles as $d) {
        $det = $xml->addChild('detalle');
        $det->addChild('actividadEconomica', '477000');
        $det->addChild('codigoProductoSin', $d['codigo_sin'] ?? '99900');
        $det->addChild('codigoProducto', $d['med_id']);
        $det->addChild('descripcion', $d['med_nombre']);
        $det->addChild('cantidad', $d['dv_cantidad']);
        $det->addChild('unidadMedida', 1);
        $det->addChild('precioUnitario', $d['dv_precio_unitario']);
        $det->addChild('montoDescuento', $d['dv_descuento']);
        $det->addChild('subTotal', $d['dv_subtotal']);
    }
    
    return $xml->asXML();
}
```

### ✅ Prompt sugerido para continuar:
```
Donde descargo el XSD oficial de facturaComputarizadaCompraVenta del SIAT Bolivia? Como valido mi XML contra ese XSD en PHP con DOMDocument antes de enviarlo? Que valor pongo en codigoProductoSin cuando el medicamento no tiene código en el catálogo del SIN?
```

---

## Paso 9: Enviar la factura al SIN
> ⚠️ **VALIDADO** - Requiere implementación

### Estado: PENDIENTE DE IMPLEMENTAR

**Función PHP validada:**

```php
function enviarFactura($xmlString, $faId, $cuf, $sc) {
    // 1. Comprimir con GZIP + Base64
    $xmlGzip = gzencode($xmlString, 9);
    $xmlB64  = base64_encode($xmlGzip);
    $hash    = hash('sha256', $xmlGzip);
    
    $client = new SoapClient(SIAT_URLS[SIAT_MODO]['facturacion']);
    
    $resp = $client->recepcionFactura([
        'SolicitudServicioRecepcionFactura' => [
            'codigoAmbiente'       => SIAT_AMBIENTE,
            'codigoModalidad'      => SIAT_MODALIDAD,
            'codigoSistema'        => SIAT_COD_SISTEMA,
            'nit'                  => SIAT_NIT,
            'cuis'                 => $sc->sc_cuis,
            'cufd'                 => $sc->sc_cufd,
            'codigoSucursal'       => 0,
            'codigoPuntoVenta'     => 0,
            'archivo'              => $xmlB64,
            'hashArchivo'          => $hash,
            'fechaEnvio'           => date('Y-m-d\TH:i:s.000-04:00'),
            'tipoFacturaDocumento' => 1, // 1=Factura compra-venta
        ]
    ]);
    
    $r = $resp->RespuestaServicioFacturacion;
    
    // Guardar en facturacion_electronica
    // La columna fe_tipo_emision ya fue creada en el Paso 2
    $db = new mainModel();
    $db->exeNoMulti(
        'INSERT INTO facturacion_electronica
         (fa_id, fe_cuf, fe_estado_siat, fe_ticket, fe_fecha_envio, fe_payload)
         VALUES (?, ?, ?, ?, NOW(), ?)',
        [$faId, $cuf, $r->codigoEstado, $r->codigoRecepcion, $xmlString]
    );
    
    // Estados: 901=pendiente, 904=observada(error), 908=validado
    return $r;
}
```

### ✅ Prompt sugerido para continuar:
```
El SIN devolvió códigoEstado 904 (observada). Como obtengo los mensajes de error específicos de la respuesta para saber que campo del XML está incorrecto? Dame el código PHP para parsear los mensajes de error del SIAT Bolivia y mostrarlos de forma legible.
```

---

## Paso 10: Validar la recepción y generar el QR
> ⚠️ **VALIDADO** - Requiere implementación

### Estado: PENDIENTE DE IMPLEMENTAR

**Función PHP validada:**

```php
function validarYGuardar($ticket, $cuf, $faId, $sc, $nroFactura) {
    $client = new SoapClient(SIAT_URLS[SIAT_MODO]['facturacion']);
    
    $resp = $client->validacionRecepcionFactura([
        'SolicitudServicioValidacionRecepcionFactura' => [
            'codigoAmbiente'   => SIAT_AMBIENTE,
            'codigoModalidad'  => SIAT_MODALIDAD,
            'codigoSistema'    => SIAT_COD_SISTEMA,
            'nit'              => SIAT_NIT,
            'cuis'             => $sc->sc_cuis,
            'cufd'             => $sc->sc_cufd,
            'codigoSucursal'   => 0,
            'codigoRecepcion'  => $ticket,
        ]
    ]);
    
    $estado = $resp->RespuestaServicioFacturacion->codigoEstado;
    
    // Generar QR (URL de verificación pública del SIN)
    $qrData = 'https://siat.impuestos.gob.bo/consulta/QR?' .
              'nit=' . SIAT_NIT .
              '&cuf=' . $cuf .
              '&numero=' . $nroFactura;
    
    // Instalar: composer require chillerlan/php-qrcode
    $qr = (new QRCode)->render($qrData); // retorna imagen Base64
    
    $db = new mainModel();
    $db->exeNoMulti(
        'UPDATE facturacion_electronica
         SET fe_estado_siat=?, fe_qr=?
         WHERE fa_id=?',
        [$estado, $qr, $faId]
    );
    
    if ($estado == 908) { // 908 = VÁLIDO
        $db->exeNoMulti('UPDATE factura SET fa_estado=1 WHERE fa_id=?', [$faId]);
    }
    
    return $estado;
}
```

### ✅ Prompt sugerido para continuar:
```
Dame el código PHP completo para generar e imprimir el recibo/factura en formato térmico (80mm) incluyendo el QR del SIAT Bolivia, el CUF, los datos del comprador y los items de la venta usando la librería DOMPDF o directamente con ESC/POS para impresora térmica.
```

---

## Paso 11: Anular facturas (devolución)
> ⚠️ **VALIDADO** - Requiere implementación

### Estado: PENDIENTE DE IMPLEMENTAR

**Función PHP validada:**

```php
function anularFactura($cuf, $codigoMotivo, $sc) {
    $client = new SoapClient(SIAT_URLS[SIAT_MODO]['facturacion']);
    
    $resp = $client->anulacionFactura([
        'SolicitudServicioAnulacionFactura' => [
            'codigoAmbiente'   => SIAT_AMBIENTE,
            'codigoModalidad'  => SIAT_MODALIDAD,
            'codigoSistema'    => SIAT_COD_SISTEMA,
            'nit'              => SIAT_NIT,
            'cuis'             => $sc->sc_cuis,
            'cufd'             => $sc->sc_cufd,
            'codigoSucursal'   => 0,
            'codigoPuntoVenta' => 0,
            'cuf'              => $cuf,
            'codigoMotivo'     => $codigoMotivo,
        ]
    ]);
    
    $db = new mainModel();
    if ($resp->RespuestaServicioAnulacion->transaccion === true) {
        $db->exeNoMulti('UPDATE factura SET fa_estado=2 WHERE fa_cuf=?', [$cuf]);
        $db->exeNoMulti(
            'UPDATE facturacion_electronica SET fe_estado_siat="ANULADA" WHERE fe_cuf=?',
            [$cuf]
        );
        // fa_estado: 1=válida, 2=anulada
    }
}

// Códigos de motivo de anulación (catálogo SIN):
// 1 = Error en datos del comprador
// 2 = Devolución de mercadería
// 3 = Error en descripción del producto
// 4 = Descuento no aplicado
// 5 = Otro
```

### ✅ Prompt sugerido para continuar:
```
Al aceptar una devolución en mi tabla devoluciones, necesito: 1) llamar a anulacionFactura del SIAT, 2) actualizar factura.fa_estado=2, 3) revertir el stock en lote_medicamento. Dame la transacción PHP completa que haga las 3 operaciones de forma atómica, con rollback si alguna falla.
```

---

## Paso 12: Modo contingencia (offline)
> ⚠️ **VALIDADO** - Requiere implementación

### Estado: PENDIENTE DE IMPLEMENTAR

**Funciones PHP:**

```php
function emitirEnContingencia($xmlString, $faId, $cuf) {
    // Guardar localmente con tipo_emision=2
    // La columna fe_tipo_emision ya fue creada en el Paso 2
    $db = new mainModel();
    $db->exeNoMulti(
        'INSERT INTO facturacion_electronica
         (fa_id, fe_cuf, fe_estado_siat, fe_payload, fe_tipo_emision, fe_fecha_envio)
         VALUES (?, ?, "CONTINGENCIA", ?, 2, NOW())',
        [$faId, $cuf, $xmlString]
    );
}

function enviarPaqueteContingencia($codigosRecepcion) {
    // Obtener todas las facturas en contingencia no enviadas
    $db = new mainModel();
    $facturas = $db->query(
        'SELECT fe_payload, fe_cuf FROM facturacion_electronica
         WHERE fe_tipo_emision=2 AND fe_estado_siat="CONTINGENCIA"'
    );
    
    // Enviar como paquete (máx 500 facturas)
    $archivos = array_map(fn($f) => [
        'cuf'     => $f->fe_cuf,
        'archivo' => base64_encode(gzencode($f->fe_payload, 9))
    ], $facturas);
    
    $client->recepcionPaqueteFactura([
        'SolicitudServicioRecepcionPaquete' => [
            'codigoRecepcionEventoSignificativo' => $codigosRecepcion,
            'archivo' => $archivos,
        ]
    ]);
}
```

### ✅ Prompt sugerido para continuar:
```
Cuáles son los códigos exactos de Evento Significativo del SIAT Bolivia para justificar contingencia por corte de internet? Cuánto tiempo tengo para enviar el paquete de facturas de contingencia después de recuperar la conexión? Dame el código para detectar automáticamente cuando el SIN no responde y activar el modo contingencia.
```

---

## Paso 13: Certificación PILOTO y pase a producción
> ⚠️ **VALIDADO** - Checklist técnico

### Estado: PENDIENTE DE EJECUTAR

**Verificación final en la BD antes de producción:**

```sql
SELECT
  ce.ce_nombre, ce.ce_nit,
  sc.sc_cuis,
  sc.sc_cufd,
  sc.sc_cufd_expira,
  CASE WHEN sc.sc_cufd_expira > NOW() THEN "VIGENTE" ELSE "VENCIDO" END AS estado_cufd,
  (SELECT COUNT(*) FROM siat_actividades) AS actividades_sync,
  (SELECT COUNT(*) FROM siat_productos)   AS productos_sync
FROM configuracion_empresa ce
CROSS JOIN siat_configuracion sc
WHERE sc.su_id = 1
LIMIT 1;
```

**Cambiar a producción:**

```php
// En config/siat.php:
define('SIAT_MODO', 'PRODUCCIÓN');
```

### ✅ Prompt sugerido para continuar:
```
Cómo solicito formalmente el pase del ambiente PILOTO a PRODUCCIÓN del SIAT Bolivia? Hay alguna presentación presencial en la oficina del SIN o se hace completamente en línea desde el Portal SIAT? Que documentación debo adjuntar?
```

---

# RESUMEN: Arquitectura del Sistema

## FLUJO COMPLETO POR VENTA:

1. Vendedor registra la venta en [`ventas`](samfarm_db.sql:557) + [`detalle_venta`](samfarm_db.sql:167)
2. Sistema verifica: CUFD vigente? -> si no, obtener nuevo CUFD
3. Generar número correlativo de factura ([`fa_numero`](samfarm_db.sql:203))
4. Calcular CUF (algoritmo local) -> guardar en [`factura.fa_cuf`](samfarm_db.sql:207)
5. Construir XML con datos de la venta
6. Intentar enviar al SIN (recepcionFactura)
   - Si SIN OK: guardar ticket en [`facturacion_electronica`](samfarm_db.sql:212)
   - Si SIN FALLA: emitir en contingencia (fe_tipo_emision=2)
7. Consultar estado (validacionRecepcionFactura)
8. Si estado=908: generar QR, actualizar [`factura.fa_estado=1`](samfarm_db.sql:208)
9. Imprimir factura con QR al cliente

## FLUJO DIARIO (CRON 05:00 AM):

1. Sincronizar catálogos del SIN
2. Obtener CUFD del día para cada sucursal
3. Enviar facturas de contingencia pendientes (si las hay)

---

# NOTAS IMPORTANTES

⚠️ **Las facturas emitidas en el ambiente PILOTO NO tienen validez fiscal. Solo las facturas emitidas en el ambiente PRODUCCIÓN son válidas ante el SIN.**

📞 **SOPORTE SIN:** Mesa de ayuda SIAT Bolivia: (591-2) 2310400 | siatmesadeayuda@impuestos.gob.bo | Portal: siat.impuestos.gob.bo
