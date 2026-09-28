<?php
/**
 * DEBUG FIRMA DIGITAL - verificacion local, sin enviar nada al SIN.
 *
 * Comprueba tres cosas sobre un XML de factura de prueba:
 *   1. que models/siatModel::firmarXML() produce un XML firmado
 *   2. que la firma verifica criptograficamente con la clave publica
 *   3. que el XML firmado valida contra el XSD oficial del SIN
 *
 * No contacta al SIN. La validacion contra el XSD es el filtro real: si pasa
 * aqui, la estructura es la que el SIN espera.
 */

$peticionAjax = true;
chdir(__DIR__ . '/models');

require_once __DIR__ . '/models/siatModel.php';

echo "=== DEBUG FIRMA DIGITAL (local, sin enviar al SIN) ===\n\n";

/* ---------------------------------------------------------------- 1. XML de prueba */
$xmlPrueba = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
    . '<facturaElectronicaCompraVenta xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
    . ' xsi:noNamespaceSchemaLocation="facturaElectronicaCompraVenta.xsd">' . "\n"
    . '  <cabecera>' . "\n"
    . '    <nitEmisor>6006007013</nitEmisor>' . "\n"
    . '    <razonSocialEmisor>FARMACIA DE PRUEBA</razonSocialEmisor>' . "\n"
    . '    <municipio>La Paz</municipio>' . "\n"
    . '    <telefono>2000000</telefono>' . "\n"
    . '    <numeroFactura>1</numeroFactura>' . "\n"
    . '    <cuf>44AAEC00DBD34C819B4D7AFD5F91900D3A059E06A467A75AC82F24C74</cuf>' . "\n"
    . '    <cufd>BQUE+QytqQUDBKVUFOSVRPQkxVRFZNVFVJBMDAwMDAwM</cufd>' . "\n"
    . '    <codigoSucursal>0</codigoSucursal>' . "\n"
    . '    <direccion>DIRECCION DE PRUEBA</direccion>' . "\n"
    . '    <codigoPuntoVenta xsi:nil="true"/>' . "\n"
    . '    <fechaEmision>2026-09-28T10:00:00.000</fechaEmision>' . "\n"
    . '    <nombreRazonSocial>CLIENTE DE PRUEBA</nombreRazonSocial>' . "\n"
    . '    <codigoTipoDocumentoIdentidad>1</codigoTipoDocumentoIdentidad>' . "\n"
    . '    <numeroDocumento>1234567</numeroDocumento>' . "\n"
    . '    <complemento xsi:nil="true"/>' . "\n"
    . '    <codigoCliente>1234567</codigoCliente>' . "\n"
    . '    <codigoMetodoPago>1</codigoMetodoPago>' . "\n"
    . '    <numeroTarjeta xsi:nil="true"/>' . "\n"
    . '    <montoTotal>100</montoTotal>' . "\n"
    . '    <montoTotalSujetoIva>100</montoTotalSujetoIva>' . "\n"
    . '    <codigoMoneda>1</codigoMoneda>' . "\n"
    . '    <tipoCambio>1</tipoCambio>' . "\n"
    . '    <montoTotalMoneda>100</montoTotalMoneda>' . "\n"
    . '    <montoGiftCard xsi:nil="true"/>' . "\n"
    . '    <descuentoAdicional>0</descuentoAdicional>' . "\n"
    . '    <codigoExcepcion>0</codigoExcepcion>' . "\n"
    . '    <cafc xsi:nil="true"/>' . "\n"
    . '    <leyenda>Ley N 453: Tienes derecho a recibir informacion sobre las caracteristicas y contenidos de los servicios que utilices.</leyenda>' . "\n"
    . '    <usuario>PRUEBA</usuario>' . "\n"
    . '    <codigoDocumentoSector>1</codigoDocumentoSector>' . "\n"
    . '  </cabecera>' . "\n"
    . '  <detalle>' . "\n"
    . '    <actividadEconomica>477000</actividadEconomica>' . "\n"
    . '    <codigoProductoSin>49111</codigoProductoSin>' . "\n"
    . '    <codigoProducto>PROD-001</codigoProducto>' . "\n"
    . '    <descripcion>PRODUCTO DE PRUEBA</descripcion>' . "\n"
    . '    <cantidad>1</cantidad>' . "\n"
    . '    <unidadMedida>1</unidadMedida>' . "\n"
    . '    <precioUnitario>100</precioUnitario>' . "\n"
    . '    <montoDescuento>0</montoDescuento>' . "\n"
    . '    <subTotal>100</subTotal>' . "\n"
    . '    <numeroSerie xsi:nil="true"/>' . "\n"
    . '    <numeroImei xsi:nil="true"/>' . "\n"
    . '  </detalle>' . "\n"
    . '</facturaElectronicaCompraVenta>';

echo "XML de prueba construido (" . strlen($xmlPrueba) . " bytes)\n\n";

/* ---------------------------------------------------------------- 2. Firmar */
$metodo = new ReflectionMethod('siatModel', 'firmarXML');
$metodo->setAccessible(true);

echo "--- 1. Firma ---\n";
try {
    $firmado = $metodo->invoke(null, $xmlPrueba);
    echo "  XML firmado: " . strlen($firmado) . " bytes\n\n";
} catch (Throwable $e) {
    echo "  [X] " . get_class($e) . ": " . $e->getMessage() . "\n";
    exit(1);
}

/* Al verificar hay que preservar el whitespace: la canonicalizacion que declara
   el documento es C14N inclusivo, que SI conserva los nodos de texto en blanco.
   Si se reparsa con preserveWhiteSpace=false el SignedInfo cambia y la
   verificacion falla aunque la firma sea correcta. */
$dom = new DOMDocument();
$dom->preserveWhiteSpace = true;
$dom->loadXML($firmado);
$xp = new DOMXPath($dom);
$xp->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

$algC14N   = (string) $xp->evaluate('string(//*[local-name()="CanonicalizationMethod"]/@Algorithm)');
$algFirma  = (string) $xp->evaluate('string(//*[local-name()="SignatureMethod"]/@Algorithm)');
$algDigest = (string) $xp->evaluate('string(//*[local-name()="DigestMethod"]/@Algorithm)');
$uri       = (string) $xp->evaluate('string(//*[local-name()="Reference"]/@URI)');
$transforms = array();
foreach ($xp->query('//*[local-name()="Reference"]/*[local-name()="Transforms"]/*') as $t) {
    $transforms[] = $t->getAttribute('Algorithm');
}

echo "  CanonicalizationMethod : $algC14N\n";
echo "  SignatureMethod        : $algFirma\n";
echo "  DigestMethod           : $algDigest\n";
echo "  Reference URI          : '$uri'\n";
echo "  Transforms declarados  :\n";
foreach ($transforms as $t) { echo "      - $t\n"; }
echo "\n";

echo "  Elementos dentro de X509Data:\n";
$hijos = $xp->query('//*[local-name()="X509Data"]/*');
if ($hijos->length) {
    foreach ($hijos as $h) { echo "      - <" . $h->localName . ">\n"; }
} else {
    echo "      (vacio)\n";
}
echo "\n";

/* ---------------------------------------------------------------- 3. Verificar */
echo "--- 2. Verificacion criptografica ---\n";

$certs = array();
if (!openssl_pkcs12_read(file_get_contents(SIAT_CERT_P12_PATH), $certs, SIAT_CERT_PASSWORD)) {
    echo "  [X] No se pudo leer el certificado\n";
    exit(1);
}
$infoCert = openssl_x509_parse($certs['cert']);

/* verify() espera un XMLSecurityKey de tipo publico, no la llave cruda de OpenSSL */
$objKeyPublica = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'public'));
$objKeyPublica->loadKey($certs['cert'], false, true);

echo "  Certificado: " . $infoCert['subject']['CN'] . " (NIT " . $infoCert['subject']['serialNumber'] . ")\n";
echo "  Vigente hasta: " . date('Y-m-d', $infoCert['validTo_time_t']) . "\n\n";

$verificador = new XMLSecurityDSig();
$verificador->idKeys = array('Id');
$verificador->idNS  = array();
$encontro = $verificador->locateSignature($dom);

if (!$encontro) {
    echo "  [X] locateSignature() no encontro la firma\n";
    exit(1);
}
$verificador->canonicalizeSignedInfo();
$valida = $verificador->verify($objKeyPublica);

echo "  Firma valida: " . ($valida ? "SI" : "NO") . "\n";

/* Digest: validar que el documento firmado no fue alterado despues de firmar.
   La libreria NO implementa el transform enveloped-signature en processTransforms(),
   asi que hay que quitar el nodo Signature antes de canonicalizar, que es
   justamente lo que ese transform significa. */
$docSinFirma = new DOMDocument();
$docSinFirma->preserveWhiteSpace = true;
$docSinFirma->loadXML($firmado);
$xpSin = new DOMXPath($docSinFirma);
$xpSin->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
$nodoFirma = $xpSin->query('//ds:Signature')->item(0);
if ($nodoFirma) {
    $nodoFirma->parentNode->removeChild($nodoFirma);
}

/* processTransforms() recibe por separado el nodo Reference (de donde lee la lista
   de transforms y el digest) y el nodo de datos a canonicalizar (que ya no debe
   contener la firma, porque eso es justamente enveloped-signature). */
$refNode = $xp->query('//*[local-name()="Reference"]')->item(0);
$procesar = new ReflectionMethod('XMLSecurityDSig', 'processTransforms');
$procesar->setAccessible(true);
$datosTransformados = $procesar->invoke($verificador, $refNode, $docSinFirma->documentElement);

$digestDeclarado = (string) $xp->evaluate('string(//*[local-name()="Reference"]/*[local-name()="DigestValue"])');
$digestCalculado = base64_encode(hash('sha256', $datosTransformados, true));

echo "  Digest declarado  : $digestDeclarado\n";
echo "  Digest recalculado: $digestCalculado\n";
echo "  Digest coincide   : " . ($digestDeclarado === $digestCalculado ? "SI" : "NO") . "\n\n";

/* ---------------------------------------------------------------- 4. XSD */
echo "--- 3. Validacion contra el XSD oficial del SIN ---\n";
$xsd = __DIR__ . '/libs/xmlseclibs/xsd/CompraVenta/facturaElectronicaCompraVenta.xsd';

if (!file_exists($xsd)) {
    echo "  [X] No se encuentra el XSD en: $xsd\n";
    exit(1);
}

libxml_use_internal_errors(true);
libxml_clear_errors();

$domValida = new DOMDocument();
$domValida->preserveWhiteSpace = true;
$domValida->loadXML($firmado);

if ($domValida->schemaValidate($xsd)) {
    echo "  [OK] El XML firmado VALIDA contra el XSD del SIN\n";
    $errores = libxml_get_errors();
    if ($errores) {
        echo "\n  Avisos del validador:\n";
        foreach ($errores as $e) { echo "      - " . trim($e->message) . "\n"; }
    }
    libxml_clear_errors();
} else {
    echo "  [X] El XML firmado NO valida contra el XSD del SIN:\n\n";
    foreach (libxml_get_errors() as $e) {
        echo "      linea " . $e->line . ": " . trim($e->message) . "\n";
    }
    libxml_clear_errors();
    echo "\n";
    exit(1);
}

echo "\n=== FIN ===\n";
echo "El XML firmado cumple la estructura que pide el SIN.\n";
echo "Este script no envio nada al servicio del SIN.\n";
