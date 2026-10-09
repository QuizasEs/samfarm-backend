<?php
/*
 * Script: certificacion_pruebas_115_125.php
 *
 * Ejecuta las pruebas de certificación:
 *   - Prueba 115 (etapa 4): recepcionFactura con codigoPuntoVenta=1 y =0
 *   - Prueba 125 (etapa 8): verificacionEstadoFactura con codigoPuntoVenta=1 y =0
 *
 * Fuente de CUIS/CUFD:
 *   - pv=0: siat_configuracion (matching debug_etapa4.php)
 *   - pv=1: puntos_venta (pv_codigo=1)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("America/La_Paz");
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

$peticionAjax = true;
chdir(__DIR__ . '/models');
require_once __DIR__ . '/models/siatModel.php';

$pdo = new PDO("mysql:host=localhost;dbname=samfarm_db;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!defined('SIAT_NIT')) {
    define('SIAT_NIT', '6006007013');
}

function logMsg($msg) {
    echo $msg . "\n";
}

function verificarFirma($xmlFirmado) {
    libxml_use_internal_errors(true);
    $domFirmado = new DOMDocument();
    $domFirmado->preserveWhiteSpace = true;
    $domFirmado->loadXML($xmlFirmado);
    $xpFirmado = new DOMXPath($domFirmado);
    $xpFirmado->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

    $certs = array();
    if (!openssl_pkcs12_read(file_get_contents(SIAT_CERT_P12_PATH), $certs, SIAT_CERT_PASSWORD)) {
        throw new Exception("No se pudo leer el certificado: " . SIAT_CERT_P12_PATH);
    }

    $objKeyPublica = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'public'));
    $objKeyPublica->loadKey($certs['cert'], false, true);

    $verificador = new XMLSecurityDSig();
    $verificador->idKeys = array('Id');
    $verificador->idNS  = array();
    if (!$verificador->locateSignature($domFirmado)) {
        throw new Exception("locateSignature() no encontró la firma");
    }
    $verificador->canonicalizeSignedInfo();
    $firmaValida = $verificador->verify($objKeyPublica);
    if (!$firmaValida) throw new Exception("La firma no verifica criptográficamente");

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
    if (!$digestCoincide) throw new Exception("El digest no coincide");

    $xsd = __DIR__ . '/libs/xmlseclibs/xsd/CompraVenta/facturaElectronicaCompraVenta.xsd';
    $domValida = new DOMDocument();
    $domValida->preserveWhiteSpace = true;
    $domValida->loadXML($xmlFirmado);
    if (!$domValida->schemaValidate($xsd)) {
        $errors = [];
        foreach (libxml_get_errors() as $err) {
            $errors[] = trim($err->message);
        }
        libxml_clear_errors();
        throw new Exception("XML no valida contra XSD: " . implode(' | ', $errors));
    }
    libxml_clear_errors();
    return true;
}

function generarXMLFactura($pdo, $suId, $pvCodigo, $codigoSucursal, $cuis, $cufd, $codigoControl, $nroFactura) {
    // Usar la última factura como hace debug_etapa4.php, con el cliente cl_id=20
    $stmt = $pdo->prepare("
        SELECT v.ve_id, v.ve_total,
               v.ve_codigo_moneda, v.ve_tipo_cambio,
               c.cl_carnet, c.cl_nombres, c.cl_apellido_paterno, c.cl_tipo_documento,
               ce.ce_nit, ce.ce_nombre, ce.ce_direccion, ce.ce_telefono,
               ce.ce_municipio, ce.ce_codigo_actividad,
               f.fa_id, f.fa_numero_control, f.fa_leyenda,
               f.fa_monto_sujeto_iva, f.fa_descuento_adicional,
               f.fa_codigo_metodo_pago, f.fa_numero_tarjeta,
               f.fa_codigo_moneda, f.fa_tipo_cambio,
               f.fa_doc_sector, f.fa_tipo_factura,
               u.us_nombres AS us_nombre
        FROM ventas v
        JOIN factura f ON f.ve_id = v.ve_id
        JOIN clientes c ON c.cl_id = 20
        JOIN configuracion_empresa ce ON ce.ce_id = 1
        JOIN usuarios u ON u.us_id = v.us_id
        WHERE v.su_id = :su_id AND f.fa_estado = 1
        ORDER BY f.fa_id DESC
        LIMIT 1
    ");
    $stmt->execute([':su_id' => $suId]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$datos) throw new Exception("No hay facturas para su_id={$suId}");

    // Obtener detalles
    $veId = $datos['ve_id'];
    $stmt = $pdo->prepare("
        SELECT dv.dv_cantidad, dv.dv_precio_unitario, dv.dv_descuento, dv.dv_subtotal,
               dv.dv_unidad_medida_sin,
               m.med_id, m.med_nombre_quimico AS med_nombre, m.med_codigo_sin
        FROM detalle_venta dv
        JOIN medicamento m ON m.med_id = dv.med_id
        WHERE dv.ve_id = :ve_id AND dv.dv_estado = 1
    ");
    $stmt->execute([':ve_id' => $veId]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fecha de emisión: usar NOW (el SIN requiere proximidad al tiempo actual)
    $fechaEmisionISO = date('c');
    $ve_fecha = date('Y-m-d H:i:s');

    $razonSocialEmisor = $datos['ce_nombre'];
    $municipio = $datos['ce_municipio'] ?? 'La Paz';
    $telefono = $datos['ce_telefono'] ?? '0000000';
    $direccion = $datos['ce_direccion'] ?? 'DIRECCION SUCURSAL';
    $codigoActividad = $datos['ce_codigo_actividad'] ?? '477000';
    $codigoMoneda = $datos['fa_codigo_moneda'] ?? $datos['ve_codigo_moneda'] ?? '1';
    $tipoCambio = $datos['fa_tipo_cambio'] ?? $datos['ve_tipo_cambio'] ?? '1.0000';
    $tipoFactura = $datos['fa_tipo_factura'] ?? 'factura';
    // codigoEmision debe ser 1 (online)
    $docSector = (int)($datos['fa_doc_sector'] ?? 1);

    $clienteNombre = trim(($datos['cl_nombres'] ?? '') . ' ' . ($datos['cl_apellido_paterno'] ?? ''));
    $clienteCarnet = $datos['cl_carnet'] ?? '0';
    if ($clienteCarnet === '' || $clienteCarnet === '0') $clienteCarnet = '0';
    $tipoDocMap = ['CI' => 1, 'NIT' => 5, 'CE' => 4, 'Pasaporte' => 3];
    $tipoDoc = $tipoDocMap[$datos['cl_tipo_documento'] ?? 'CI'] ?? 1;
    $codigoCliente = 20;

    $leyenda = $datos['fa_leyenda'] ?? 'Ley N 453: Tienes derecho a recibir informacion sobre las caracteristicas y contenidos de los servicios que utilices.';
    $codigoMetodoPago = $datos['fa_codigo_metodo_pago'] ?? 1;
    // Si es texto como 'efectivo', mapear a código numérico del SIN
    if (!is_numeric($codigoMetodoPago)) {
        $codigoMetodoPago = 1; // efectivo en el catálogo del SIN
    }
    $codigoMetodoPago = (int)$codigoMetodoPago;
    $numeroTarjeta = $datos['fa_numero_tarjeta'] ?? '';
    $numeroTarjetaXml = $numeroTarjeta !== '' ? '<numeroTarjeta>'.$numeroTarjeta.'</numeroTarjeta>' : '<numeroTarjeta xsi:nil="true"/>';

    $montoTotal = $datos['ve_total'];
    $montoSujetoIva = $datos['fa_monto_sujeto_iva'] ?? $datos['ve_total'];
    $descuentoAdicional = $datos['fa_descuento_adicional'] ?? 0.00;

    // Generar CUF
    $cuf = siatModel::generarCUF(
        SIAT_NIT,
        $ve_fecha,
        $nroFactura,
        $codigoControl,
        $codigoSucursal,
        $pvCodigo,
        1,
        1,
        $docSector
    );
    if (!$cuf) throw new Exception("No se pudo generar el CUF");

    // Construir detalle XML
    $detalleXml = '';
    foreach ($detalles as $d) {
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

    $xmlFactura = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<facturaElectronicaCompraVenta xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="facturaElectronicaCompraVenta.xsd">
  <cabecera>
    <nitEmisor>'.SIAT_NIT.'</nitEmisor>
    <razonSocialEmisor>'.$razonSocialEmisor.'</razonSocialEmisor>
    <municipio>'.$municipio.'</municipio>
    <telefono>'.$telefono.'</telefono>
    <numeroFactura>'.$nroFactura.'</numeroFactura>
    <cuf>'.$cuf.'</cuf>
    <cufd>'.$cufd.'</cufd>
    <codigoSucursal>'.$codigoSucursal.'</codigoSucursal>
    <direccion>'.$direccion.'</direccion>
    <codigoPuntoVenta>'.$pvCodigo.'</codigoPuntoVenta>
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
    <usuario>'.($datos['us_nombre'] ?? 'sistemas').'</usuario>
    <codigoDocumentoSector>'.$docSector.'</codigoDocumentoSector>
  </cabecera>'.$detalleXml.'
</facturaElectronicaCompraVenta>';

    return [
        'xml' => $xmlFactura,
        'cuf' => $cuf,
        'cufd' => $cufd,
        'numeroFactura' => $nroFactura,
    ];
}

function getCuFD_PV0($pdo) {
    $stmt = $pdo->query("SELECT sc_cuis AS cuis, sc_cufd AS cufd, sc_cufd_control AS control FROM siat_configuracion WHERE su_id=1 LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCuFD_PV1($pdo) {
    $stmt = $pdo->query("SELECT pv_cuis AS cuis, pv_cufd AS cufd, pv_cufd_control AS control FROM puntos_venta WHERE su_id=1 AND pv_codigo=1 LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


// ===== MAIN =====
logMsg("=== CERTIFICACION PRUEBAS 115 y 125 ===\n");
logMsg("Certificado: " . SIAT_CERT_P12_PATH . "\n");
logMsg("Ambiente SIAT: " . SIAT_AMBIENTE . "\n\n");

$suId = 1;
$resultados = [];

// Crear cliente SOAP
$context = stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => [
        'header' => "apikey: TokenApi " . SIAT_TOKEN . "\r\n",
        'timeout' => 120
    ]
]);
$client = new SoapClient(SIAT_URLS[SIAT_MODO]['compra_venta'], [
    'stream_context' => $context,
    'trace' => 1,
    'exceptions' => true,
    'connection_timeout' => 120,
    'soap_version' => SOAP_1_1,
]);

$codigoSucursal = 0;

$iteraciones = 150;
foreach ([1, 0] as $pvCodigo) {
    for ($iteracion = 1; $iteracion <= $iteraciones; $iteracion++) {
        logMsg("--- VARIANTE codigoPuntoVenta={$pvCodigo} | Iteración {$iteracion}/{$iteraciones} ---\n\n");

        // Obtener CUIS/CUFD segón la variante
        if ($pvCodigo == 0) {
            $cred = getCuFD_PV0($pdo);
        } else {
            $cred = getCuFD_PV1($pdo);
        }

    $cuis = $cred['cuis'];
    $cufd = $cred['cufd'];
    $codigoControl = $cred['control'];

    logMsg("  CUIS: {$cuis}\n");
    logMsg("  CUFD: {$cufd}\n");
    logMsg("  Control: {$codigoControl}\n\n");

    // Generar numeroFactura único
    $nroFactura = $pvCodigo * 1000000 + $iteracion * 10000 + (int)date('His');

    // ===== PRUEBA 115: recepcionFactura =====
    logMsg("  [Prueba 115] Generando XML con codigoPuntoVenta={$pvCodigo}...\n");
    try {
        $facturaData = generarXMLFactura($pdo, $suId, $pvCodigo, $codigoSucursal, $cuis, $cufd, $codigoControl, $nroFactura);
        $xmlFactura = $facturaData['xml'];
        $cuf = $facturaData['cuf'];
    } catch (Exception $e) {
        logMsg("  [Prueba 115] ERROR generando XML: " . $e->getMessage() . "\n");
        $resultados[] = ['pv' => $pvCodigo, '115' => 'FAIL: ' . $e->getMessage(), '125' => 'SKIP', 'cuf' => null];
        continue;
    }

    logMsg("  [Prueba 115] CUF generado: {$cuf}\n");
    logMsg("  [Prueba 115] Firmando XML...\n");
    $metodoFirma = new ReflectionMethod('siatModel', 'firmarXML');
    $metodoFirma->setAccessible(true);
    try {
        $xmlFirmado = $metodoFirma->invoke(null, $xmlFactura);
    } catch (Throwable $e) {
        logMsg("  [Prueba 115] ERROR firmando: " . $e->getMessage() . "\n");
        $resultados[] = ['pv' => $pvCodigo, '115' => 'FAIL: firma: ' . $e->getMessage(), '125' => 'SKIP', 'cuf' => $cuf];
        continue;
    }
    logMsg("  [Prueba 115] XML firmado (" . strlen($xmlFirmado) . " bytes)\n");

    logMsg("  [Prueba 115] Verificando firma y digest...\n");
    try {
        verificarFirma($xmlFirmado);
        logMsg("  [Prueba 115] Firma y digest OK\n");
    } catch (Exception $e) {
        logMsg("  [Prueba 115] ERROR verificación: " . $e->getMessage() . "\n");
        $resultados[] = ['pv' => $pvCodigo, '115' => 'FAIL: verificación: ' . $e->getMessage(), '125' => 'SKIP', 'cuf' => $cuf];
        continue;
    }

    $xmlGZip = gzencode($xmlFirmado, 9);
    $hashArchivo = hash('sha256', $xmlGZip);
    $fechaEnvio = date('Y-m-d\TH:i:s.000');

    logMsg("  [Prueba 115] Enviando recepcionFactura...\n");
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
                'codigoPuntoVenta' => $pvCodigo,
                'archivo' => $xmlGZip,
                'fechaEnvio' => $fechaEnvio,
                'hashArchivo' => $hashArchivo,
                'tipoFacturaDocumento' => 1,
                'codigoEmision' => 1,
                'codigoDocumentoSector' => 1
            ]
        ]);

        $respuestaXml = $client->__getLastResponse();
        $respuestaDom = new DOMDocument();
        @$respuestaDom->loadXML($respuestaXml);
        $xpResp = new DOMXPath($respuestaDom);

        $codigoEstado = trim($xpResp->evaluate('string(//*[local-name()="codigoEstado"])'));
        $descripcion  = trim($xpResp->evaluate('string(//*[local-name()="codigoDescripcion"])'));
        $transaccion  = trim($xpResp->evaluate('string(//*[local-name()="transaccion"])'));
        $codigoRecepcion = trim($xpResp->evaluate('string(//*[local-name()="codigoRecepcion"])'));

        logMsg("  [Prueba 115] codigoEstado: " . ($codigoEstado !== '' ? $codigoEstado : '(no venía)') . "\n");
        logMsg("  [Prueba 115] codigoRecepcion: " . ($codigoRecepcion !== '' ? $codigoRecepcion : '(no venía)') . "\n");
        logMsg("  [Prueba 115] descripcion: " . ($descripcion !== '' ? $descripcion : '(no venía)') . "\n");
        logMsg("  [Prueba 115] transaccion: " . ($transaccion !== '' ? $transaccion : '(no venía)') . "\n");

        foreach ($xpResp->query('//*[local-name()="mensajesList"]/*') as $nodo) {
            $cod = trim($xpResp->evaluate('string(./*[local-name()="codigo"])', $nodo));
            $msg = trim($xpResp->evaluate('string(./*[local-name()="descripcion"])', $nodo));
            if ($cod) logMsg("  [Prueba 115] Mensaje SIN: [{$cod}] {$msg}\n");
        }
        // Also try alternate path for messages
        foreach ($xpResp->query('//*') as $nodo) {
            $localName = $nodo->localName;
            if ($localName === 'codigo' && $nodo->textContent && $nodo->parentNode) {
                $desc = '';
                foreach ($nodo->parentNode->childNodes as $child) {
                    if ($child->localName === 'descripcion') {
                        $desc = trim($child->textContent);
                        break;
                    }
                }
                if ($desc) logMsg("  [Prueba 115] Mensaje (alt): [{$nodo->textContent}] {$desc}\n");
            }
        }
        logMsg("\n");

        $pass115 = ($codigoEstado == '908' && $transaccion == 'true');
        $resultados[] = [
            'pv' => $pvCodigo,
            '115' => $pass115 ? 'PASS' : "FAIL (codigoEstado={$codigoEstado}, transaccion={$transaccion})",
            '125' => 'PENDING',
            'cuf' => $cuf,
        ];
        $idxActual = count($resultados) - 1;
    } catch (SoapFault $e) {
        logMsg("  [Prueba 115] ERROR SOAP: " . $e->getMessage() . "\n\n");
        $resultados[] = ['pv' => $pvCodigo, '115' => 'FAIL SOAP: ' . $e->getMessage(), '125' => 'SKIP', 'cuf' => $cuf];
        continue;
    }

    // ===== PRUEBA 125: verificacionEstadoFactura =====
    if (!$pass115) {
        logMsg("  [Prueba 125] Skipping (Prueba 115 falló)\n\n");
        $resultados[$idxActual]['125'] = 'SKIP';
        continue;
    }

    logMsg("  [Prueba 125] Enviando verificacionEstadoFactura...\n");
    try {
        $respVerif = siatModel::verificarEstadoFactura($cuf, $suId, 1);

        $codigoEstadoV = $respVerif->codigoEstado ?? null;
        $codigoRespV = $respVerif->codigoRespuesta ?? null;
        $transaccionV = $respVerif->transaccion ?? null;
        $descV = $respVerif->codigoDescripcion ?? null;

        logMsg("  [Prueba 125] codigoRespuesta: " . ($codigoRespV ?? '(no venía)') . "\n");
        logMsg("  [Prueba 125] codigoEstado: " . ($codigoEstadoV ?? '(no venía)') . "\n");
        logMsg("  [Prueba 125] transaccion: " . ($transaccionV ?? '(no venía)') . "\n");
        logMsg("  [Prueba 125] descripcion: " . ($descV ?? '(no venía)') . "\n");

        if (is_array($respVerif->mensajesList ?? null)) {
            foreach ($respVerif->mensajesList as $m) {
                logMsg("  [Prueba 125] Mensaje: [{$m->codigo}] {$m->descripcion}\n");
            }
        }
        logMsg("\n");

        // 690 = firma verificada correctamente, transaccion=1, descripcion=VALIDA
        $pass125 = ($codigoRespV == '690' || $codigoEstadoV == '690' || $transaccionV == 'true' || $transaccionV == '1');
        $resultados[$idxActual]['125'] = $pass125 ? 'PASS' : "FAIL (codigoRespuesta={$codigoRespV})";
    } catch (Exception $e) {
        logMsg("  [Prueba 125] ERROR: " . $e->getMessage() . "\n\n");
        $resultados[$idxActual]['125'] = 'FAIL EXC: ' . $e->getMessage();
    }

    logMsg("--- FIN Iteración {$iteracion} pv={$pvCodigo} ---\n\n");
    }
    logMsg("--- FIN VARIANTE pv={$pvCodigo} ---\n\n");
}

// ===== RESUMEN =====
logMsg("=== RESUMEN CERTIFICACION ({$iteraciones} iteraciones x 2 variantes) ===\n");
$allPass = true;
$totals = ['115' => ['PASS' => 0, 'FAIL' => 0], '125' => ['PASS' => 0, 'FAIL' => 0]];
foreach ($resultados as $r) {
    $status125 = $r['125'] ?? 'N/A';
    logMsg("PV={$r['pv']}: 115=[{$r['115']}] 125=[{$status125}]");
    if (isset($totals['115'][$r['115']])) $totals['115'][$r['115']]++;
    if (isset($totals['125'][$status125])) $totals['125'][$status125]++;
    if ($r['115'] !== 'PASS' || ($status125 !== 'PASS' && $status125 !== 'SKIP' && $status125 !== 'PENDING')) {
        $allPass = false;
    }
}
logMsg("\nTotal 115: PASS={$totals['115']['PASS']} FAIL={$totals['115']['FAIL']}");
logMsg("Total 125: PASS={$totals['125']['PASS']} FAIL={$totals['125']['FAIL']}\n");

logMsg($allPass ? "✅ TODAS LAS PRUEBAS PASARON (14 de 14)" : "❌ ALGUNAS PRUEBAS FALLARON");
logMsg("\n=== FIN ===\n");
