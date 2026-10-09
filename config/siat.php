<?php

/* Zona horaria de Bolivia (UTC-4, sin horario de verano).

   CRITICO: el php.ini de esta maquina trae date.timezone=Europe/Berlin, que va
   6 horas por delante de la hora del SIN y de la de MySQL (que corre en
   America/La_Paz). Ese desfase rompia tres cosas a la vez:

     1. fechaEnvio de los paquetes salia 6 horas en el futuro.
     2. La ventana de 24 h del CUFD se guardaba 6 horas mas tarde de lo que el
        SIN la concede, asi que la app creia vigente un CUFD que el SIN ya
        habia vencido -> error 123 "CUFD FUERA DE TOLERANCIA" en cada envio.
     3. strtotime() sobre las fechas guardadas por MySQL las leia en hora de
        Berlin, con lo que cualquier comparacion de vigencia salia corrida.

   Se fija aqui, en el archivo que carga todo el modulo SIAT, para que el
   ajuste aplique a web, CLI y cron por igual. */
if (date_default_timezone_get() !== 'America/La_Paz') {
    date_default_timezone_set('America/La_Paz');
}

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

/* Modalidad de facturación: 1 = Computarizada en Línea, 2 = Electrónica en Línea.

   Va en 1 porque es la modalidad con la que el SIN emite los CUFD de este
   CUIS: pedir un CUFD con modalidad 2 devuelve 917 "EL PARAMETRO MODALIDAD ES
   INVALIDO". Como el CUFD y el paquete tienen que ir en la misma modalidad
   (si no, el paquete se rechaza con 902/914 "CUFD INVALIDO"), el paquete se
   envia con 1. El XML sigue siendo facturaElectronicaCompraVenta, que es el
   esquema contra el que valida el sistema. */
define('SIAT_MODALIDAD', 1);

/* Límite de facturas por paquete de contingencia enviado al SIN.
   El máximo del SIN es 500 (error 954 "LA CANTIDAD DE FACTURAS EN EL PAQUETE
   EMITIDO POR CONTINGENCIA HA EXCEDIDO EL MÁXIMO PERMITIDO"). Las pruebas de
   certificación exigen justamente paquetes de 500, asi que el tope queda en 500
   y no en un valor propio más bajo. */
define('SIAT_MAX_FACTURAS_CONTINGENCIA', 500);

/* Tipo de punto de venta ante el SIN (parametrica oficial):
     1 Comisionista   2 Ventanilla de Cobranza   3 Moviles
     4 YPFB           5 Cajeros                 6 Conjunta
   Una farmacia opera en caja, asi que corresponde 5. */
define('SIAT_TIPO_PUNTO_VENTA', 5);

/* Actividad economica del EMISOR. No es la del cliente: sale de la sucursal, y
   todas las lineas de detalle de la factura viajan con la misma.

Catalogo real del SIN, consultado en vivo con sincronizarActividades:
     4772100  Venta al por menor de productos farmaceuticos y medicinales
     477300   Venta al por menor de productos farmaceuticos, medicinales,
               cosmeticos y articulos de tocador
     4772200  Venta al por menor de articulos de perfumeria y cosmeticos (Sec.)

   Va 4772100 porque es la actividad inscrita bajo la que el SIN tiene dados de
   alta los medicamentos, y el SIN valida esa asociacion producto-actividad. Con
   477300 el SIN responde 1017 "EL CODIGO DE PRODUCTO NO ESTA RELACIONADO A
   NINGUN ACTIVIDAD ECONOMICA DEL CONTRIBUYENTE / No existe asociacion de:
   actividad 477300 - producto 1005611": el Paracetamol esta bajo 4772100.

   Ademas hay un desacople de SECTOR, que es la razon de fondo:
     4772100  sector 1  = FCV (factura de compra-venta), 1382 productos
     4772200  sector 1  = FCV, 26 productos
     477300   sector 34 = FAC_SEG (factura de seguridad), solo 18 productos
   y la emision declara sector de documento 1 (compra-venta) en
   ventaController.php y en siat_certificacion.php. 477300 pertenece a otro
   sector y su catalogo no tiene praticamente ningun medicamento. Los cosmeticos
   que tambien se venden pueden llevar su propia linea de detalle con otra
   actividad; no obligan a que toda la factura viaje como FAC_SEG.

   OJO: el codigo es de SEIS digitos, 4772100. Si el SIN rechazara las facturas
   por actividad, se corrige aqui y en configuracion_empresa.ce_codigo_actividad,
   no en el XML. */
define('SIAT_ACTIVIDAD_ECONOMICA', '4772100');

/* --- Cola de envioAutomatico ------------------------------------------------
   El envio NO ocurre dentro de la transaccion de venta. Al cerrar la venta se
   firma y se guarda el XML; el envio lo drena este cron. Asi el cajero no
   espera los 30-45 s que tarda el SIN y dos cajas nunca pelean por el mismo
   punto de venta. */

/* Facturas que se toman por pasada. Bajo a proposito: cada envio es una llamada
   SOAP de medio minuto, y tomar de mas solo hace que la pasada tarde mas. */
define('SIAT_COLA_LIMITE', 10);

/* Intentos antes de abandonar. A partir de ahi la factura se marca para
   revision manual en vez de reintentar para siempre. */
define('SIAT_COLA_MAX_INTENTOS', 5);

/* Espera entre intentos. Crece x3 por cada intento (1, 3, 9, 27, 81 minutos) y
   se corta en el tope. El SIN se degrada con rafagas: reintentar de inmediato
   no acelera la recuperacion, la empeora. */
define('SIAT_COLA_ESPERA_BASE', 1);
define('SIAT_COLA_ESPERA_TOPE', 135);


/* Activación opcional de facturación electrónica. */
define('SIAT_HABILITADO', true);

/* Certificado digital P12 para firma XMLDSig.
   FIRMA_PILOTO.p12 se genera combinando:
     - CER_MARTIN_HUAYHUA_YANA.pem (certificado de PRUEBA del SIN,
       emisor "Impuestos Fake", emitido para el piloto)
     - MARTIN_HUAYHUA_YANA_KEY.pem (llave privada PKCS#1 de XCA)
   mediante firma/generar_firma_piloto.php.
   Contraseña: 69902738 (la que se uso al generar el CSR en XCA).
   Nombre distinto de MARTIN_HUAYHUA_YANA.p12 (que es la firma
   Digicert de produccion y NO sirve para el piloto). */
define('SIAT_CERT_P12_PATH', dirname(__DIR__) . '/firma/firma_digital_digicert.p12');
define('SIAT_CERT_PASSWORD', '6006007');
