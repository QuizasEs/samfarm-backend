<?php
// DEBUG Etapa 4 - Simulación de Facturación Electrónica
// Basado en el patrón de debug_etapa2.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("America/La_Paz");

// Salida legible en navegador (UTF-8) y en CLI no afecta
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

/* Configuración SIAT: se toma de config/siat.php (la misma fuente que usa el
   sistema en producción) cargándola a través de models/siatModel.php, que es
   donde vive firmarXML() con el certificado digital real. */
$peticionAjax = true;
chdir(__DIR__ . '/models');
require_once __DIR__ . '/models/siatModel.php';

if (!defined('SIAT_NIT')) {
    define('SIAT_NIT', '6006007013');
}

echo "=== DEBUG ETAPA 4 - Simulación de Facturación ===\n\n";
echo "Certificado digital: " . SIAT_CERT_P12_PATH . "\n\n";

// 1. Conexión a BD
try {
    $pdo = new PDO("mysql:host=localhost;dbname=samfarm_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión BD OK\n\n";
} catch (PDOException $e) {
    die("❌ Error conexión BD: " . $e->getMessage() . "\n");
}

// 2. Obtener configuración SIAT
$stmt = $pdo->prepare("SELECT su_id, sc_cuis, sc_cuis_vigente_hasta, sc_cufd, sc_cufd_control, sc_cufd_vigente_hasta, sc_sucursal_codigo, sc_punto_venta_codigo FROM siat_configuracion WHERE sc_cuis IS NOT NULL AND sc_cuis != '' LIMIT 1");
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
echo "   PtoVenta: {$codigoPuntoVenta}\n";
echo "   CUIS vigente hasta: " . ($config->sc_cuis_vigente_hasta ?? 'sin fecha') . "\n";
echo "   CUFD vigente hasta: " . ($config->sc_cufd_vigente_hasta ?? 'sin fecha') . "\n\n";

/* 2b. Asegurar CUIS/CUFD vigentes ANTES de armar el XML (mismo flujo que
   ventaController). Si el CUFD caducó, el SIN rechaza con 902/código 123
   "CUFD fuera de tolerancia", así que se renueva automáticamente aquí. */
$renovado = false;

$cuisVigente = !empty($config->sc_cuis_vigente_hasta) && strtotime($config->sc_cuis_vigente_hasta) > time();
if (!$cuisVigente) {
    echo "⚠️  CUIS vencido (" . ($config->sc_cuis_vigente_hasta ?? 'sin fecha') . "). Solicitando uno nuevo...\n";
    if (!siatModel::obtenerCUIS($suId, $codigoSucursal, $codigoPuntoVenta)) {
        die("❌ No se pudo obtener un CUIS nuevo\n");
    }
    $renovado = true;
}

if (!siatModel::cufdVigente($suId)) {
    echo "⚠️  CUFD vencido (" . ($config->sc_cufd_vigente_hasta ?? 'sin fecha') . "). Solicitando uno nuevo...\n";
    if (!siatModel::obtenerCUFD($suId, $codigoSucursal, $codigoPuntoVenta)) {
        die("❌ No se pudo obtener un CUFD nuevo\n");
    }
    $renovado = true;
}

if ($renovado) {
    $stmt = $pdo->prepare("SELECT su_id, sc_cuis, sc_cuis_vigente_hasta, sc_cufd, sc_cufd_control, sc_cufd_vigente_hasta, sc_sucursal_codigo, sc_punto_venta_codigo FROM siat_configuracion WHERE su_id = :su_id");
    $stmt->execute([':su_id' => $suId]);
    $config = $stmt->fetch(PDO::FETCH_OBJ);
    $cuis = $config->sc_cuis;
    $cufd = $config->sc_cufd;
    echo "✅ CUFD renovado: {$cufd}\n";
    echo "   Vigencia: " . ($config->sc_cufd_vigente_hasta ?? 'sin fecha') . "\n\n";
}

// 3. Obtener datos reales de factura desde BD para el XML (MOVIDO ANTES DE SOAP)
$stmt = $pdo->prepare("
    SELECT v.ve_id, v.ve_total, v.ve_fecha_emision AS ve_fecha,
           v.ve_codigo_moneda, v.ve_tipo_cambio,
           c.cl_carnet, c.cl_nombres, c.cl_apellido_paterno, c.cl_id,
           c.cl_tipo_documento,
           ce.ce_nit, ce.ce_nombre, ce.ce_direccion, ce.ce_telefono,
           ce.ce_municipio, ce.ce_codigo_actividad,
           f.fa_id, f.fa_numero_control, f.fa_cuf,
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

/* CUF: si la factura no tiene uno generado (fa_cuf NULL), el XML anterior
   enviaba 58 ceros y el SIN lo rechazaría con 946 "CUF no existe en BD del
   SIN". Se genera con el mismo algoritmo que usa producción (ventaController
   → siatModel::generarCUF) y se guarda en la factura. */
$cuf = trim((string) ($datos['fa_cuf'] ?? ''));
if ($cuf === '' || $cuf === str_repeat('0', 58)) {
    $codigoControlCufd = $datos['fa_cufd_control'] ?? $datos['sc_cufd_control'] ?? null;
    if (!$codigoControlCufd) {
        die("❌ No hay código de control de CUFD (sc_cufd_control) para generar el CUF\n");
    }

    $cufGenerado = siatModel::generarCUF(
        SIAT_NIT,
        $datos['ve_fecha'],
        $numeroFactura,
        $codigoControlCufd,
        $codigoSucursal,
        $codigoPuntoVenta,
        1, // tipoEmision: online
        1, // tipoFactura: con derecho a crédito fiscal
        (int) ($datos['fa_doc_sector'] ?? 1) // tipoDocumentoSector: compra-venta
    );

    if (!$cufGenerado) {
        die("❌ No se pudo generar el CUF (revisar número de factura: {$numeroFactura})\n");
    }

    $cuf = $cufGenerado;
    echo "✅ CUF generado: {$cuf}\n";

    $stmt = $pdo->prepare("UPDATE factura SET fa_cuf = :cuf WHERE fa_id = :fa_id");
    $stmt->execute([':cuf' => $cuf, ':fa_id' => $datos['fa_id']]);
    echo "   Guardado en factura.fa_id = {$datos['fa_id']}\n";
} else {
    echo "   CUF existente en BD: {$cuf}\n";
}
echo "\n";
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

/* ---------------------------------------------------------------- 4. FIRMA
   Se firma con siatModel::firmarXML(), que usa el certificado real
   (firma/MARTIN_YANA_MODERNO.p12) y la librería xmlseclibs. Es la misma
   función que usa el sistema en producción; el método es privado, así que se
   invoca por reflexión (igual que en debug_firma.php). */
echo "\n========== FIRMA DIGITAL ==========\n";

$metodoFirma = new ReflectionMethod('siatModel', 'firmarXML');
$metodoFirma->setAccessible(true);

try {
    $xmlFirmado = $metodoFirma->invoke(null, $xmlFactura);
} catch (Throwable $e) {
    echo "❌ Error al firmar: " . get_class($e) . ": " . $e->getMessage() . "\n";
    exit(1);
}

echo "✅ XML firmado: " . strlen($xmlFirmado) . " bytes\n";

// Guardar el XML firmado para inspección
file_put_contents(__DIR__ . '/debug_xml_factura_firmado.xml', $xmlFirmado);
echo "✅ XML firmado guardado en debug_xml_factura_firmado.xml\n";

/* Al verificar hay que preservar el whitespace: la canonicalización que declara
   el documento es C14N inclusivo, que sí conserva los nodos de texto en blanco.
   Si se reparsa con preserveWhiteSpace=false el SignedInfo cambia y la
   verificación falla aunque la firma sea correcta. */
$domFirmado = new DOMDocument();
$domFirmado->preserveWhiteSpace = true;
$domFirmado->loadXML($xmlFirmado);
$xpFirmado = new DOMXPath($domFirmado);
$xpFirmado->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

$algC14N   = (string) $xpFirmado->evaluate('string(//*[local-name()="CanonicalizationMethod"]/@Algorithm)');
$algFirma  = (string) $xpFirmado->evaluate('string(//*[local-name()="SignatureMethod"]/@Algorithm)');
$algDigest = (string) $xpFirmado->evaluate('string(//*[local-name()="DigestMethod"]/@Algorithm)');
$uri       = (string) $xpFirmado->evaluate('string(//*[local-name()="Reference"]/@URI)');

echo "  CanonicalizationMethod : $algC14N\n";
echo "  SignatureMethod        : $algFirma\n";
echo "  DigestMethod           : $algDigest\n";
echo "  Reference URI          : '$uri'\n";

// Verificación criptográfica con la clave pública del certificado
$certs = array();
if (!openssl_pkcs12_read(file_get_contents(SIAT_CERT_P12_PATH), $certs, SIAT_CERT_PASSWORD)) {
    echo "❌ No se pudo leer el certificado: " . SIAT_CERT_P12_PATH . "\n";
    exit(1);
}
$infoCert = openssl_x509_parse($certs['cert']);
echo "  Certificado            : " . $infoCert['subject']['CN'] . " (NIT " . $infoCert['subject']['serialNumber'] . ")\n";
echo "  Vigente hasta          : " . date('Y-m-d', $infoCert['validTo_time_t']) . "\n";

$objKeyPublica = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'public'));
$objKeyPublica->loadKey($certs['cert'], false, true);

$verificador = new XMLSecurityDSig();
$verificador->idKeys = array('Id');
$verificador->idNS  = array();
if (!$verificador->locateSignature($domFirmado)) {
    echo "❌ locateSignature() no encontró la firma\n";
    exit(1);
}
$verificador->canonicalizeSignedInfo();
$firmaValida = $verificador->verify($objKeyPublica);
echo "  Firma válida           : " . ($firmaValida ? "SI" : "NO") . "\n";

if (!$firmaValida) {
    echo "❌ La firma no verifica criptográficamente. No se envía al SIN.\n";
    exit(1);
}

/* Digest: la librería NO implementa el transform enveloped-signature en
   processTransforms(), así que hay que quitar el nodo Signature antes de
   canonicalizar, que es justamente lo que ese transform significa. */
$docSinFirma = new DOMDocument();
$docSinFirma->preserveWhiteSpace = true;
$docSinFirma->loadXML($xmlFirmado);
$xpSin = new DOMXPath($docSinFirma);
$xpSin->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
$nodoFirma = $xpSin->query('//ds:Signature')->item(0);
if ($nodoFirma) {
    $nodoFirma->parentNode->removeChild($nodoFirma);
}

$refNode = $xpFirmado->query('//*[local-name()="Reference"]')->item(0);
$procesar = new ReflectionMethod('XMLSecurityDSig', 'processTransforms');
$procesar->setAccessible(true);
$datosTransformados = $procesar->invoke($verificador, $refNode, $docSinFirma->documentElement);

$digestDeclarado  = (string) $xpFirmado->evaluate('string(//*[local-name()="Reference"]/*[local-name()="DigestValue"])');
$digestCalculado  = base64_encode(hash('sha256', $datosTransformados, true));
$digestCoincide   = ($digestDeclarado === $digestCalculado);

echo "  Digest declarado       : $digestDeclarado\n";
echo "  Digest recalculado     : $digestCalculado\n";
echo "  Digest coincide        : " . ($digestCoincide ? "SI" : "NO") . "\n";

if (!$digestCoincide) {
    echo "❌ El digest no coincide. No se envía al SIN.\n";
    exit(1);
}

/* Validación contra el XSD oficial: es el filtro real de estructura. Si falla
   aquí, el SIN rechazaría la recepción. */
$xsd = __DIR__ . '/libs/xmlseclibs/xsd/CompraVenta/facturaElectronicaCompraVenta.xsd';
libxml_use_internal_errors(true);
libxml_clear_errors();

$domValida = new DOMDocument();
$domValida->preserveWhiteSpace = true;
$domValida->loadXML($xmlFirmado);

if (!$domValida->schemaValidate($xsd)) {
    echo "❌ El XML firmado NO valida contra el XSD del SIN:\n";
    foreach (libxml_get_errors() as $err) {
        echo "      línea " . $err->line . ": " . trim($err->message) . "\n";
    }
    libxml_clear_errors();
    exit(1);
}
libxml_clear_errors();
echo "✅ XML firmado VALIDA contra el XSD del SIN\n";
echo "==================================\n\n";

// El XML que se comprime y envía es el firmado, no el original
$xmlFactura = $xmlFirmado;

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
    $respuestaXml = $client->__getLastResponse();
    echo $respuestaXml . "\n";

    /* Decodificar el resultado con la tabla oficial de códigos del SIN
       (siatinfo.impuestos.gob.bo → Códigos Error SIAT) */
    $respuestaDom = new DOMDocument();
    if (@$respuestaDom->loadXML($respuestaXml)) {
        $xpResp = new DOMXPath($respuestaDom);

        $codigoEstado = trim($xpResp->evaluate('string(//*[local-name()="codigoEstado"])'));
        $descripcion  = trim($xpResp->evaluate('string(//*[local-name()="codigoDescripcion"])'));
        $transaccion  = trim($xpResp->evaluate('string(//*[local-name()="transaccion"])'));

        $codigosOficiales = [
            123 => 'CUFD fuera de tolerancia (el CUFD caducó: renovar con la Etapa 1)',
            901 => 'Recepción pendiente',
            902 => 'Recepción rechazada (ver mensaje de mensagensList)',
            903 => 'Recepción procesada',
            908 => 'Recepción VALIDADA - factura aceptada por el SIN',
            921 => 'El firmado del XML es incorrecto',
            922 => 'La firma del XML no corresponde al contribuyente',
            927 => 'El certificado de la firma es inválido',
            928 => 'El certificado se encuentra revocado',
            939 => 'La factura no cumple el formato del XSD',
            946 => 'El CUF no existe en la base de datos del SIN',
            953 => 'El CUFD no se encuentra vigente',
            965 => 'El contribuyente NO cuenta con firma vigente registrada (certificado no asociado/registrado en el SIN para este NIT y ambiente)',
        ];

        echo "\n========== DIAGNÓSTICO ==========\n";
        echo "codigoEstado  : " . ($codigoEstado !== '' ? $codigoEstado : '(no venía)') . "\n";
        echo "codigoDescrip : " . ($descripcion !== '' ? $descripcion : '(no venía)') . "\n";
        echo "transaccion   : " . ($transaccion !== '' ? $transaccion : '(no venía)') . "\n";

        $codigoInt = (int) $codigoEstado;
        if (isset($codigosOficiales[$codigoInt])) {
            echo "Significado   : " . $codigosOficiales[$codigoInt] . "\n";
        }

        foreach ($xpResp->query('//*[local-name()="mensajesList"]') as $nodo) {
            $cod = trim($xpResp->evaluate('string(./*[local-name()="codigo"])', $nodo));
            $msg = trim($xpResp->evaluate('string(./*[local-name()="descripcion"])', $nodo));
            echo "Mensaje SIN   : [{$cod}] {$msg}\n";
            if (isset($codigosOficiales[(int) $cod])) {
                echo "Significado   : " . $codigosOficiales[(int) $cod] . "\n";
            }
        }

        if ($codigoInt === 908) {
            echo "\n✅ ETAPA 4 COMPLETA: el SIN aceptó la factura firmada con el certificado nuevo.\n";
        }
        echo "==================================\n";
    }
} catch (SoapFault $e) {
    echo "❌ Error SOAP: " . $e->getMessage() . "\n";
    echo "XML enviado:\n" . $client->__getLastRequest() . "\n";
}

echo "\n=== FIN DEBUG ===\n";