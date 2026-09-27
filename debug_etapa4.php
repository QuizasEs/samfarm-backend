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

// 3. Obtener datos reales de factura desde BD para el XML (MOVIDO ANTES DE SOAP)
$stmt = $pdo->prepare("
    SELECT v.ve_id, v.ve_total, v.ve_fecha_emision AS ve_fecha,
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
           sc.sc_cufd, sc.sc_cufd_control,
           u.us_nombres AS us_nombre,
           v.su_id
    FROM ventas v
    JOIN factura f ON f.ve_id = v.ve_id
    LEFT JOIN clientes c ON c.cl_id = v.cl_id
    JOIN configuracion_empresa ce ON ce.ce_id = 1
    JOIN siat_configuracion sc ON sc.su_id = v.su_id
    JOIN usuarios u ON u.us_id = v.us_id
    WHERE v.su_id = :su_id AND f.fa_estado = 1
      AND EXISTS (SELECT 1 FROM detalle_venta dv WHERE dv.ve_id = v.ve_id AND dv.dv_estado = 1)
    ORDER BY f.fa_id DESC
    LIMIT 1
");
$stmt->execute([':su_id' => $suId]);
$datos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$datos) {
    die("❌ No hay facturas para probar en su_id=$suId\n");
}

// Detalle - usar el ve_id de la factura seleccionada
$veId = $datos['ve_id'];
$stmt = $pdo->prepare("
    SELECT dv.dv_cantidad, dv.dv_precio_unitario, dv.dv_descuento, dv.dv_subtotal,
           dv.dv_unidad_medida_sin,
           m.med_id, m.med_nombre_quimico AS med_nombre,
           m.med_codigo_sin
    FROM detalle_venta dv
    JOIN medicamento m ON m.med_id = dv.med_id
    WHERE dv.ve_id = :ve_id AND dv.dv_estado = 1
");
$stmt->execute([':ve_id' => $veId]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Detalles encontrados: " . count($detalles) . "\n";

echo "Datos de factura obtenidos:\n";
echo "   fa_numero_control: {$datos['fa_numero_control']}\n";
echo "   fa_cuf: {$datos['fa_cuf']}\n";
echo "   ce_municipio: {$datos['ce_municipio']}\n";
echo "   ce_codigo_actividad: {$datos['ce_codigo_actividad']}\n";
echo "   ve_codigo_moneda: {$datos['ve_codigo_moneda']}\n";
echo "   ve_tipo_cambio: {$datos['ve_tipo_cambio']}\n\n";

// 4. Generar XML de prueba para enviar al SIAT usando datos reales de BD
$faId = $datos['fa_numero_control']; // Usar el número de control real

$fechaEmisionISO = date('c', strtotime($datos['ve_fecha']));

// Usar datos de BD en lugar de hardcoded
$razonSocialEmisor = $datos['ce_nombre'];
$municipio = $datos['ce_municipio'] ?? 'La Paz';
$telefono = $datos['ce_telefono'] ?? '0000000';
$direccion = $datos['ce_direccion'] ?? 'DIRECCION SUCURSAL';

// Usar fa_numero_siat (numérico puro para XML), si NULL generar secuencial al momento
$numeroFacturaSiat = $datos['fa_numero_siat'] ?? null;
if (!$numeroFacturaSiat) {
    // Obtener siguiente de factura_secuencia
    $stmt = $pdo->prepare("
        UPDATE factura_secuencia 
        SET fs_ultimo_nro = fs_ultimo_nro + 1 
        WHERE su_id = :su_id AND fs_anio = YEAR(:fecha) AND fs_punto_venta = :pv
    ");
    $stmt->execute([
        ':su_id' => $datos['su_id'],
        ':fecha' => $datos['ve_fecha'],
        ':pv' => $codigoPuntoVenta
    ]);
    $stmt = $pdo->prepare("
        SELECT fs_ultimo_nro FROM factura_secuencia 
        WHERE su_id = :su_id AND fs_anio = YEAR(:fecha) AND fs_punto_venta = :pv
    ");
    $stmt->execute([
        ':su_id' => $datos['su_id'],
        ':fecha' => $datos['ve_fecha'],
        ':pv' => $codigoPuntoVenta
    ]);
    $seq = $stmt->fetch(PDO::FETCH_OBJ);
    $numeroFacturaSiat = $seq ? (int)$seq->fs_ultimo_nro : 1;
}
$numeroFactura = $numeroFacturaSiat;  // Para el XML (integer)
$cuf = $datos['fa_cuf'] ?? str_repeat('0', 58);
$codigoActividad = $datos['ce_codigo_actividad'] ?? '477000';
$codigoMoneda = $datos['fa_codigo_moneda'] ?? $datos['ve_codigo_moneda'] ?? '1';
$tipoCambio = $datos['fa_tipo_cambio'] ?? $datos['ve_tipo_cambio'] ?? '1.0000';
$docSector = $datos['fa_doc_sector'] ?? '1';
$codigoEmision = $datos['fa_codigo_emision'] ?? '1';
$tipoFactura = $datos['fa_tipo_factura'] ?? 'factura';
$modalidad = $datos['fa_modalidad'] ?? 1;

// Cliente (fallback si no hay cliente en la factura)
$clienteNombre = trim(($datos['cl_nombres'] ?? '') . ' ' . ($datos['cl_apellido_paterno'] ?? ''));
if ($clienteNombre === '') {
    $clienteNombre = 'Consumidor Final';
}
$clienteCarnet = $datos['cl_carnet'] ?? '0';
if ($clienteCarnet === '' || $clienteCarnet === '0') {
    $clienteCarnet = '0';
    $clienteCarnet = '0';
}
$tipoDocMap = ['CI' => 1, 'NIT' => 5, 'CE' => 4, 'Pasaporte' => 3];
$tipoDoc = $tipoDocMap[$datos['cl_tipo_documento'] ?? 'CI'] ?? 1;
$codigoCliente = $datos['cl_id'] ?? 0;

// Leyenda
$leyenda = $datos['fa_leyenda'] ?? 'Ley N 453: Tienes derecho a recibir informacion sobre las caracteristicas y contenidos de los servicios que utilices.';

// Método de pago
$codigoMetodoPago = $datos['fa_codigo_metodo_pago'] ?? 1;
$numeroTarjeta = $datos['fa_numero_tarjeta'] ?? '';
$numeroTarjetaXml = $numeroTarjeta !== '' ? '<numeroTarjeta>'.$numeroTarjeta.'</numeroTarjeta>' : '<numeroTarjeta xsi:nil="true"/>';

// Montos
$montoTotal = $datos['ve_total'];
$montoSujetoIva = $datos['fa_monto_sujeto_iva'] ?? $datos['ve_total'];
$descuentoAdicional = $datos['fa_descuento_adicional'] ?? 0.00;

// CUFD
$cufd = $datos['fa_cufd'] ?? $datos['sc_cufd'];

// Construir detalle XML desde BD
$detalleXml = '';
echo "DEBUG: detalles count = " . count($detalles) . "\n";
foreach ($detalles as $d) {
    echo "DEBUG: med_id={$d['med_id']}, med_nombre={$d['med_nombre']}\n";
    $detalleXml .= '
    <detalle>
      <actividadEconomica>'.$codigoActividad.'</actividadEconomica>
      <codigoProductoSin>'.($d['med_codigo_sin'] ?? '99900').'</codigoProductoSin>
      <codigoProducto>'.$d['med_id'].'</codigoProducto>
      <descripcion>'.$d['med_nombre'].'</descripcion>
      <cantidad>'.$d['dv_cantidad'].'</cantidad>
      <unidadMedida>'.($d['dv_unidad_medida_sin'] ?? 1).'</unidadMedida>
      <precioUnitario>'.$d['dv_precio_unitario'].'</precioUnitario>
      <montoDescuento>'.($d['dv_descuento'] ?? 0).'</montoDescuento>
      <subTotal>'.$d['dv_subtotal'].'</subTotal>
      <numeroSerie></numeroSerie>
      <numeroImei></numeroImei>
    </detalle>';
}
echo "DEBUG: detalleXml length = " . strlen($detalleXml) . "\n";

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
    <nombreRazonSocial>'.$clienteNombre.'</nombreRazonSocial>
    <codigoTipoDocumentoIdentidad>'.$tipoDoc.'</codigoTipoDocumentoIdentidad>
    <numeroDocumento>'.$clienteCarnet.'</numeroDocumento>
    <complemento xsi:nil="true"/>
    <codigoCliente>'.$codigoCliente.'</codigoCliente>
    <codigoMetodoPago>'.$codigoMetodoPago.'</codigoMetodoPago>
    '.$numeroTarjetaXml.'
    <montoTotal>'.$montoTotal.'</montoTotal>
    <montoTotalSujetoIva>'.$montoSujetoIva.'</montoTotalSujetoIva>
    <codigoMoneda>'.$codigoMoneda.'</codigoMoneda>
    <tipoCambio>'.$tipoCambio.'</tipoCambio>
    <montoTotalMoneda>'.$montoTotal.'</montoTotalMoneda>
    <montoGiftCard xsi:nil="true"/>
    <descuentoAdicional>'.$descuentoAdicional.'</descuentoAdicional>
    <codigoExcepcion xsi:nil="true"/>
    <cafc xsi:nil="true"/>
    <leyenda>'.$leyenda.'</leyenda>
    <usuario>'.($datos['us_nombre'] ?? 'admin').'</usuario>
    <codigoDocumentoSector>'.$docSector.'</codigoDocumentoSector>
  </cabecera>'.$detalleXml.'
</facturaElectronicaCompraVenta>';
echo "\n========== XML FACTURA ORIGINAL ==========\n";
echo $xmlFactura;
echo "\n==========================================\n";

// Guardar XML en archivo para verificación completa
file_put_contents(__DIR__ . '/debug_xml_factura.xml', $xmlFactura);
echo "✅ XML guardado en debug_xml_factura.xml\n";

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

// Agregar Signature placeholder (requerido por XSD para facturaElectronicaCompraVenta)
// En producción usar siatModel::firmarXML() con certificado real
$signaturePlaceholder = '
<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
  <SignedInfo>
    <CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
    <SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
    <Reference URI="#factura">
      <Transforms>
        <Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
        <Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
      </Transforms>
      <DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
      <DigestValue>PLACEHOLDER</DigestValue>
    </Reference>
  </SignedInfo>
  <SignatureValue>PLACEHOLDER</SignatureValue>
  <KeyInfo>
    <X509Data>
      <X509Certificate>PLACEHOLDER</X509Certificate>
    </X509Data>
  </KeyInfo>
</Signature>';

// Insertar Signature antes del cierre de facturaElectronicaCompraVenta
$xmlFactura = str_replace('</facturaElectronicaCompraVenta>', $signaturePlaceholder . '</facturaElectronicaCompraVenta>', $xmlFactura);

echo "✅ Signature placeholder agregado\n";

// Procesar XML: comprimir y codificar
$xmlGZip = gzencode($xmlFactura, 9);
echo "\n========== GZIP ==========\n";
echo "Bytes XML original: " . strlen($xmlFactura) . "\n";
echo "Bytes GZIP: " . strlen($xmlGZip) . "\n";
echo "Primeros bytes HEX: " . bin2hex(substr($xmlGZip, 0, 10)) . "\n";
echo "==========================\n";

$hashArchivo = hash('sha256', $xmlGZip);

$fechaEnvio = date('Y-m-d\TH:i:s.000');

// 3. Crear cliente SOAP (movido aquí para validar XML primero)
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