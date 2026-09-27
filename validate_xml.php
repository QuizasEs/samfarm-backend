<?php
$xml = file_get_contents('C:\xampp\htdocs\samfarm-backend\debug_xml_factura.xml');
$dom = new DOMDocument();
if ($dom->loadXML($xml)) {
    echo "✅ XML BIEN FORMADO - " . strlen($xml) . " bytes\n";
    echo "Root: " . $dom->documentElement->tagName . "\n";
    // Check key elements
    $cabecera = $dom->getElementsByTagName('cabecera')->item(0);
    if ($cabecera) {
        echo "✅ cabecera existe\n";
        foreach ($cabecera->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                echo "  - {$node->tagName}: " . substr($node->textContent, 0, 50) . "\n";
            }
        }
    }
    $detalles = $dom->getElementsByTagName('detalle');
    echo "✅ detalles: " . $detalles->length . "\n";
} else {
    echo "❌ XML MAL FORMADO\n";
    foreach (libxml_get_errors() as $error) {
        echo $error->message . "\n";
    }
}