<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Compare the certificate in the XML signature with the one in the PEM/P12
// Also check if there's a cert chain issue

echo "=== COMPARACIÓN DE CERTIFICADOS ===\n\n";

// 1. Get cert from P12
$p12 = file_get_contents('firma/FIRMA_PILOTO.p12');
$certs = [];
openssl_pkcs12_read($p12, $certs, '69902738');
$p12CertPem = $certs['cert'];
$p12KeyPem = $certs['pkey'];

// 2. Get cert from PEM file
$pemCert = file_get_contents('firma/CER_MARTIN_HUAYHUA_YANA.pem');
$pemKey = file_get_contents('firma/MARTIN_HUAYHUA_YANA_KEY.pem');

// 3. Compare
$p12CertInfo = openssl_x509_parse($p12CertPem);
$pemCertInfo = openssl_x509_parse($pemCert);

echo "P12 cert serial:  " . $p12CertInfo['serialNumber'] . "\n";
echo "PEM cert serial:  " . $pemCertInfo['serialNumber'] . "\n";
echo "Mismo cert (serial): " . ($p12CertInfo['serialNumber'] === $pemCertInfo['serialNumber'] ? 'SÍ' : 'NO') . "\n\n";

// 4. Extract cert from the signed XML
$signedXml = file_get_contents('debug_xml_factura_firmado.xml');
$dom = new DOMDocument();
$dom->loadXML($signedXml);
$xp = new DOMXPath($dom);
$xp->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
$x509CertB64 = $xp->evaluate('string(//ds:X509Certificate)');
$x509CertDer = base64_decode($x509CertB64);

// Convert DER to PEM for comparison
$x509CertPem = "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($x509CertDer), 64, "\n") . "-----END CERTIFICATE-----\n";
$x509CertInfo = openssl_x509_parse($x509CertPem);

echo "XML cert serial:  " . $x509CertInfo['serialNumber'] . "\n";
echo "Mismo cert (XML vs P12): " . ($x509CertInfo['serialNumber'] === $p12CertInfo['serialNumber'] ? 'SÍ' : 'NO') . "\n\n";

// 5. Check if cert chain is in P12
echo "extracerts en P12: " . (isset($certs['extracerts']) ? count($certs['extracerts']) : 0) . "\n";
echo "X509Data extra certs in XML: " . ($xp->evaluate('count(//ds:X509Data/ds:X509Certificate)') - 1) . " (more than 1)\n\n";

// 6. Check certificate extensions
echo "=== Extensiones del certificado ===\n";
if (empty($p12CertInfo['extensions'])) {
    echo "⚠️  EL CERTIFICADO NO TIENE EXTENSIONES X.509\n";
    echo "   Un certificado de firma digital debería tener:\n";
    echo "   - Key Usage: Digital Signature\n";
    echo "   - Extended Key Usage: emailProtection, clientAuth\n";
    echo "   - Basic Constraints: CA:FALSE\n";
    echo "   - Subject Key Identifier\n";
    echo "   - Authority Key Identifier\n";
} else {
    foreach ($p12CertInfo['extensions'] as $k => $v) {
        $v = is_array($v) ? implode(', ', $v) : $v;
        echo "  $k = $v\n";
    }
}

// 7. Check key usage
echo "\n=== Key Usage (raw) ===\n";
echo "signatureTypeSN: " . ($p12CertInfo['signatureTypeSN'] ?? 'N/A') . "\n";
echo "signatureTypeLN: " . ($p12CertInfo['signatureTypeLN'] ?? 'N/A') . "\n";

// 8. Verify the private key matches the certificate
echo "\n=== Key-Cert match ===\n";
$certPubKey = openssl_pkey_get_public($p12CertPem);
$certPrivKey = openssl_pkey_get_private($p12KeyPem);
echo "Public key from cert: " . (is_resource($certPubKey) ? 'OK' : 'FAIL') . "\n";
echo "Private key loaded: " . (is_resource($certPrivKey) ? 'OK' : 'FAIL') . "\n";

// Get key details
$pubKeyDetails = openssl_pkey_get_details($certPubKey);
$privKeyDetails = openssl_pkey_get_details($certPrivKey);
echo "Cert public key bits: " . ($pubKeyDetails['bits'] ?? '?') . "\n";
echo "Priv key bits: " . ($privKeyDetails['bits'] ?? '?') . "\n";
echo "Keys match (modulus): " . (($pubKeyDetails['rsa']['modulus'] ?? '') === ($privKeyDetails['rsa']['modulus'] ?? '') ? 'SÍ' : 'NO') . "\n";

echo "\n=== RESUMEN ===\n";
echo "El certificado en el XML es el MISMO que en el P12/PEM: " . ($x509CertInfo['serialNumber'] === $p12CertInfo['serialNumber'] ? 'SÍ' : 'NO') . "\n";
echo "La clave privada corresponde al certificado: " . (($pubKeyDetails['rsa']['modulus'] ?? '') === ($privKeyDetails['rsa']['modulus'] ?? '') ? 'SÍ' : 'NO') . "\n";
echo "El certificado tiene extensiones X.509: " . (empty($p12CertInfo['extensions']) ? 'NO ❌' : 'SÍ ✅') . "\n";
