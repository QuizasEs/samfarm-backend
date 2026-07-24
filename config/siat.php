<?php

/* Ambiente de trabajo: 'PILOTO' (pruebas, sin validez fiscal) o 'PRODUCCIÓN' */
define('SIAT_MODO', 'PILOTO');

/* Endpoints SOAP del SIN (WSDL) por ambiente */
define('SIAT_URLS', [
    'PILOTO' => [
        'operacion'   => 'https://pilotosiatservicios.impuestos.gob.bo/v2/OperacionSIAT?wsdl',
        'facturacion' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionComputarizada?wsdl',
        'codigos'     => 'https://pilotosiatservicios.impuestos.gob.bo/v2/CodigosSiatServicios?wsdl',
    ],
    'PRODUCCIÓN' => [
        'operacion'   => 'https://siatservicios.impuestos.gob.bo/v2/OperacionSIAT?wsdl',
        'facturacion' => 'https://siatservicios.impuestos.gob.bo/v2/FacturacionComputarizada?wsdl',
        'codigos'     => 'https://siatservicios.impuestos.gob.bo/v2/CodigosSiatServicios?wsdl',
    ],
]);

/* NIT del emisor (13 dígitos). Reemplazar por el NIT real del contribuyente.
   Idealmente debe coincidir con configuracion_empresa.ce_nit. */
define('SIAT_NIT', '123456789');

/* Código de sistema asignado por el SIN (placeholder) */
define('SIAT_COD_SISTEMA', 'XXXX');

/* Token Delegado obtenido en el Portal SIAT (placeholder) */
define('SIAT_TOKEN', 'TOKEN_DELEGADO');

/* Ambiente numérico: 2 = PILOTO, 1 = PRODUCCIÓN */
define('SIAT_AMBIENTE', SIAT_MODO == 'PILOTO' ? 2 : 1);

/* Modalidad: 2 = Facturación Computarizada en Línea (fijo) */
define('SIAT_MODALIDAD', 2);

/* Código de sucursal asignado por el SIN (0 = casa matriz).
   ⚠️ El proyecto usa su_id 1 y 7; mapear al código SIN real al certificar. */
define('SIAT_COD_SUCURSAL', 0);

/* Código de punto de venta asignado por el SIN */
define('SIAT_PUNTO_VENTA', 0);

/* Activación opcional de facturación electrónica.
   false = solo nota de venta (sin tocar el flujo actual).
   true  = se genera XML/factura SIAT cuando aplique. */
define('SIAT_HABILITADO', false);
