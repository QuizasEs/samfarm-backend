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


/* Activación opcional de facturación electrónica.
   false = solo nota de venta (sin tocar el flujo actual).
   true  = se genera XML/factura SIAT cuando aplique. */
define('SIAT_HABILITADO', true);
