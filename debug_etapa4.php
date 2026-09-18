<?php
// DEBUG Etapa 4 - Simulación de Facturación Electrónica
// Basado en el patrón de debug_etapa2.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("America/La_Paz");

// Configuración SIAT
define('SIAT_MODO', 'PILOTO');
define('SIAT_AMBIENTE', 2);
define('SIAT_MODALIDAD', 1);
define('SIAT_COD_SISTEMA', '373641B66F08A38C69CE');
define('SIAT_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJtYXJ0aW55YW5hMTc0QGdtYWlsLmNvbSIsImNvZGlnb1Npc3RlbWEiOiIzNzM2NDFCNjZGMDhBMzhDNjlDRSIsIm5pdCI6Ikg0c0lBQUFBQUFBQUFETXpNREF6TURBM01EUUdBTWxsYVhNS0FBQUEiLCJpZCI6NTA2MTYyNCwiZXhwIjoxNzkyNzg0OTI0LCJpYXQiOjE3ODYzMTkyOTQsIm5pdERlbGVnYWRvIjo2MDA2MDA3MDEzLCJzdWJzaXN0ZW1hIjoiU0ZFIn0.v8f1k7jyOF7lc7CaRdlAB799OGSqBI8gk33zwSUNN5Fb_sJvzXJr8oCV8bFBpQCaQS8aSIKcFSNoiHhGFygTAgeyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJtYXJ0aW55YW5hMTc0QGdtYWlsLmNvbSIsImNvZGlnb1Npc3RlbWEiOiIzNzM2NDFCNjZGMDhBMzhDNjlDRSIsIm5pdCI6Ikg0c0lBQUFBQUFBQUFETXpNREF6TURBM01EUUdBTWxsYVhNS0FBQUEiLCJpZCI6NTA2MTYyNCwiZXhwIjoxNzkyNzg0OTI0LCJpYXQiOjE3ODYzMTkyOTQsIm5pdERlbGVnYWRvIjo2MDA2MDA3MDEzLCJzdWJzaXN0ZW1hIjoiU0ZFIn0.v8f1k7jyOF7lc7CaRdlAB799OGSqBI8gk33zwSUNN5Fb_sJvzXJr8oCV8bFBpQCaQS8aSIKcFSNoiHhGFygTAg');
define('SIAT_NIT', '6006007013');

define('SIAT_URLS', [
    'PILOTO' => [
        'sincronizacion_datos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion?wsdl',
        'operaciones' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionOperaciones?wsdl',
        'codigos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos?wsdl',
        'compra_venta' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta?wsdl',
    ],
]);

echo "=== DEBUG ETIQUETA 4 - Simulación de Facturación ===\n\n";

// 1. Conexión a BD
try {
    $pdo = new PDO("mysql:host=localhost;dbname=samfarm_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión BD OK\n\n";
} catch (PDOException $e) {
    die("❌ Error conexión BD: " . $e->getMessage() . "\n");
}

// 2. Obtener configuración SIAT
$stmt = $pdo->prepare("SELECT su_id, sc_cuis, sc_cufd, sc_sucursal_codigo, sc_punto_venta_codigo FROM siat_configuracion WHERE sc_cuis IS NOT NULL AND sc_cuis != '' LIMIT 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_OBJ);

if (!$config) {
    die("❌ No existe configuración SIAT con CUIS válido\n");
}

$suId = $config->su_id;
$cuis = $config->sc_cuis;
$cufd = $config->sc_cufd;
$codigoSucursal = (int)($config->sc_sucursal_codigo ?? 0);
$codigoPuntoVenta = (int)($config->sc_punto_venta_codigo ?? 0);

echo "Configuración SIAT:\n";
echo "   su_id: {$suId}\n";
echo "   CUIS: {$cuis}\n";
echo "   CUFD: {$cufd}\n";
echo "   Sucursal: {$codigoSucursal}\n";
echo "   PtoVenta: {$codigoPuntoVenta}\n\n";

// 3. Crear cliente SOAP
$context = stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => [
        'header' => "apikey: TokenApi " . SIAT_TOKEN . "\r\n",
        'timeout' => 30
    ]
]);

try {
    $client = new SoapClient(SIAT_URLS[SIAT_MODO]['compra_venta'], [
        'stream_context' => $context,
        'trace' => 1,
        'exceptions' => true,
        'connection_timeout' => 60,
        'soap_version' => SOAP_1_1,
    ]);
    echo "✅ Cliente SOAP creado\n\n";
} catch (Exception $e) {
    die("❌ Error cliente SOAP: " . $e->getMessage() . "\n");
}

// 4. Generar XML de prueba para enviar al SIAT (estructura oficial facturaElectronicaCompraVenta,
//    verificada contra facturaElectronicaCompraVenta.xsd y facturaElectronicaCompraVenta.xml del SIAT)
$faId = 1001;

// Reutilizamos el mismo formato de fecha que ya usas para fechaEnvio
$fechaEmisionISO = date('Y-m-d\TH:i:s.000');

// TODO: reemplazar por los datos reales de tu empresa/sucursal
$razonSocialEmisor = 'FARMACIAS SASUKE';
$municipio = 'El Alto';
$telefono = '0000000';
$direccion = 'DIRECCION SUCURSAL';
$numeroFactura = $faId;

// ⚠️ PENDIENTE: el CUF (Código Único de Factura) es OBLIGATORIO y se calcula con el
// algoritmo oficial del SIAT (Anexo Técnico). No tengo ese algoritmo verificado, así
// que NO lo estoy inventando: lo dejo como placeholder de 58 caracteres (mismo largo
// que el CUF de ejemplo del XML oficial) solo para probar la validación estructural.
$cuf = str_repeat('0', 58); // PLACEHOLDER — reemplazar con el cálculo real del CUF

// ⚠️ PENDIENTE: esta modalidad (Electrónica en Línea) exige además un elemento
// <Signature> (firma digital XMLDSig, RSA-SHA256) al final del XML, obligatorio según
// el XSD (<xs:element ref="ds:Signature"/> sin minOccurs="0"). Requiere un certificado
// digital real emitido para tu NIT. A propósito NO lo incluyo todavía: primero
// confirmamos que cabecera y detalle pasan la validación por sí solos.
$xmlFactura = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<facturaElectronicaCompraVenta xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="facturaElectronicaCompraVenta.xsd">
  <cabecera>
    <nitEmisor>'.SIAT_NIT.'</nitEmisor>
    <razonSocialEmisor>'.$razonSocialEmisor.'</razonSocialEmisor>
    <municipio>'.$municipio.'</municipio>
    <telefono>'.$telefono.'</telefono>
    <numeroFactura>'.$numeroFactura.'</numeroFactura>
    <cuf>'.$cuf.'</cuf>
    <cufd>'.$cufd.'</cufd>
    <codigoSucursal>'.$codigoSucursal.'</codigoSucursal>
    <direccion>'.$direccion.'</direccion>
    <codigoPuntoVenta>'.$codigoPuntoVenta.'</codigoPuntoVenta>
    <fechaEmision>'.$fechaEmisionISO.'</fechaEmision>
    <nombreRazonSocial>TEST</nombreRazonSocial>
    <codigoTipoDocumentoIdentidad>1</codigoTipoDocumentoIdentidad>
    <numeroDocumento>0</numeroDocumento>
    <complemento xsi:nil="true"/>
    <codigoCliente>0</codigoCliente>
    <codigoMetodoPago>1</codigoMetodoPago>
    <numeroTarjeta xsi:nil="true"/>
    <montoTotal>100.00</montoTotal>
    <montoTotalSujetoIva>100.00</montoTotalSujetoIva>
    <codigoMoneda>1</codigoMoneda>
    <tipoCambio>1.00</tipoCambio>
    <montoTotalMoneda>100.00</montoTotalMoneda>
    <montoGiftCard xsi:nil="true"/>
    <descuentoAdicional>0.00</descuentoAdicional>
    <codigoExcepcion xsi:nil="true"/>
    <cafc xsi:nil="true"/>
    <leyenda>Ley N 453: Tienes derecho a recibir informacion sobre las caracteristicas y contenidos de los servicios que utilices.</leyenda>
    <usuario>admin</usuario>
    <codigoDocumentoSector>1</codigoDocumentoSector>
  </cabecera>
  <detalle>
    <actividadEconomica>4772100</actividadEconomica>
    <codigoProductoSin>99999</codigoProductoSin>
    <codigoProducto>1001418</codigoProducto>
    <descripcion>Producto Test</descripcion>
    <cantidad>1.00</cantidad>
    <unidadMedida>1</unidadMedida>
    <precioUnitario>100.00</precioUnitario>
    <montoDescuento>0.00</montoDescuento>
    <subTotal>100.00</subTotal>
    <numeroSerie></numeroSerie>
    <numeroImei></numeroImei>
  </detalle>
</facturaElectronicaCompraVenta>';
echo "\n========== XML FACTURA ORIGINAL ==========\n";
echo $xmlFactura;
echo "\n==========================================\n";

libxml_use_internal_errors(true);

$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;

if (!$dom->loadXML($xmlFactura)) {
    echo "❌ XML MAL FORMADO\n";

    foreach (libxml_get_errors() as $error) {
        echo $error->message . "\n";
    }

    exit;
}

echo "✅ XML BIEN FORMADO\n";
// Procesar XML: comprimir y codificar
$xmlGZip = gzencode($xmlFactura, 9);
echo "\n========== GZIP ==========\n";
echo "Bytes XML original: " . strlen($xmlFactura) . "\n";
echo "Bytes GZIP: " . strlen($xmlGZip) . "\n";
echo "Primeros bytes HEX: " . bin2hex(substr($xmlGZip, 0, 10)) . "\n";
echo "==========================\n";

$hashArchivo = hash('sha256', $xmlGZip);

$fechaEnvio = date('Y-m-d\TH:i:s.000');

echo "➡️  Enviando factura al SIAT (recepcionFactura)...\n";

try {
    $response = $client->recepcionFactura([
        'SolicitudServicioRecepcionFactura' => [
            'codigoAmbiente' => SIAT_AMBIENTE,
            'codigoModalidad' => SIAT_MODALIDAD,
            'codigoSistema' => SIAT_COD_SISTEMA,
            'nit' => SIAT_NIT,
            'cuis' => $cuis,
            'cufd' => $cufd,
            'codigoSucursal' => $codigoSucursal,
            'codigoPuntoVenta' => $codigoPuntoVenta,
            'archivo' => $xmlGZip,
            'fechaEnvio' => $fechaEnvio,
            'hashArchivo' => $hashArchivo,
            'tipoFacturaDocumento' => 1,
            'codigoEmision' => 1,
            'codigoDocumentoSector' => 1
        ]
    ]);

    echo "✅ Respuesta SIAT:\n";
    echo $client->__getLastResponse();
} catch (SoapFault $e) {
    echo "❌ Error SOAP: " . $e->getMessage() . "\n";
    echo "XML enviado:\n" . $client->__getLastRequest() . "\n";
}

echo "\n=== FIN DEBUG ===\n";