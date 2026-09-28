<?php

/* Ambiente de trabajo: 'PILOTO' (pruebas, sin validez fiscal) o 'PRODUCCIÓN' */
define('SIAT_MODO', 'PILOTO');

/* Endpoints SOAP del SIN (WSDL) por ambiente */
define('SIAT_URLS', [
    'PILOTO' => [
        'sincronizacion_datos'   => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion?wsdl',
        'operaciones' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionOperaciones?wsdl',
        'codigos'     => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos?wsdl',
        'compra_venta' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta?wsdl',
    ],

    'PRODUCCIÓN' => [
        'sincronizacion_datos'   => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion?wsdl',
        'operaciones' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionOperaciones?wsdl',
        'codigos'     => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos?wsdl',
        'compra_venta' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta?wsdl',
    ],
]);


/* Código de sistema asignado por el SIN (placeholder) */
define('SIAT_COD_SISTEMA', '373641B66F08A38C69CE');

/* Token Delegado obtenido en el Portal SIAT (placeholder) */
define('SIAT_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJtYXJ0aW55YW5hMTc0QGdtYWlsLmNvbSIsImNvZGlnb1Npc3RlbWEiOiIzNzM2NDFCNjZGMDhBMzhDNjlDRSIsIm5pdCI6Ikg0c0lBQUFBQUFBQUFETXpNREF6TURBM01EUUdBTWxsYVhNS0FBQUEiLCJpZCI6NTA2MTYyNCwiZXhwIjoxNzkyNzg0OTI0LCJpYXQiOjE3ODYzMTkyOTQsIm5pdERlbGVnYWRvIjo2MDA2MDA3MDEzLCJzdWJzaXN0ZW1hIjoiU0ZFIn0.v8f1k7jyOF7lc7CaRdlAB799OGSqBI8gk33zwSUNN5Fb_sJvzXJr8oCV8bFBpQCaQS8aSIKcFSNoiHhGFygTAg');

/* Ambiente numérico: 2 = PILOTO, 1 = PRODUCCIÓN */
define('SIAT_AMBIENTE', SIAT_MODO == 'PILOTO' ? 2 : 1);

/* Modalidad: 2 = Facturación Computarizada en Línea (fijo) */
define('SIAT_MODALIDAD', 1);

/* Límite de facturas por paquete de contingencia enviado al SIN */
define('SIAT_MAX_FACTURAS_CONTINGENCIA', 100);


/* Activación opcional de facturación electrónica. */
define('SIAT_HABILITADO', true);

/* Certificado digital P12 para firma XMLDSig.
   El original (MARTIN_YANA.p12) usa PKCS#12 PBE con SHA-1 y RC2-128 / 3DES,
   algoritmos que OpenSSL 3.x moved al proveedor "legacy" y no lee por defecto,
   por lo que openssl_pkcs12_read() devolvia false y la firma nunca se generaba.
   MARTIN_YANA_MODERNO.p12 es el mismo certificado reexportado en AES-256-CBC +
   SHA-256; el original se conserva como respaldo.
   Ver firma/convertir_certificado.php para regenerarlo. */
define('SIAT_CERT_P12_PATH', dirname(__DIR__) . '/firma/MARTIN_YANA_MODERNO.p12');
define('SIAT_CERT_PASSWORD', '6006007');
