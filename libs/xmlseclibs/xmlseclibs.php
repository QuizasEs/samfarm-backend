<?php
/**
 * Cargador de xmlseclibs (robrichards/xmlseclibs).
 *
 * Antes este archivo contenia xmlseclibs 1.3.1-dev (2011), que:
 *   - exigia el paquete PEAR File_X509 (linea 48 del original), inexistente en el
 *     servidor, provocando error fatal antes de poder firmar;
 *   - generaba un <X509IssuerSerial> dentro de <X509Data>, elemento que el
 *     SignatureSchema.xsd del SIN no permite.
 *
 * La version antigua quedo en xmlseclibs_1.3.1_respaldo.php y tambien en git
 * (commit 403d4c9).
 *
 * La version actual de la libreria usa namespace (RobRichards\XMLSecLibs), asi que
 * aqui se exponen tambien los nombres globales para que models/siatModel.php
 * siga usando XMLSecurityDSig / XMLSecurityKey sin cambios.
 */

require_once __DIR__ . '/XMLSecurityKey.php';
require_once __DIR__ . '/XMLSecurityDSig.php';

if (!class_exists('XMLSecurityKey', false)) {
    class_alias('RobRichards\\XMLSecLibs\\XMLSecurityKey', 'XMLSecurityKey');
}
if (!class_exists('XMLSecurityDSig', false)) {
    class_alias('RobRichards\\XMLSecLibs\\XMLSecurityDSig', 'XMLSecurityDSig');
}
