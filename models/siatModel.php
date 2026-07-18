<?php

require_once "mainModel.php";

class siatModel extends mainModel
{
    private static function clienteSOAP($servicio)
    {
        $wsdl = SIAT_URLS[SIAT_MODO][$servicio];
        $context = stream_context_create([
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        return new SoapClient($wsdl, [
            'stream_context' => $context,
            'trace' => 1,
            'exceptions' => true,
            'connection_timeout' => 30
        ]);
    }

    public static function obtenerCUIS($suId, $codigoSucursal = null, $codigoPuntoVenta = null)
    {
        $codigoSucursal = $codigoSucursal ?? SIAT_COD_SUCURSAL;
        $codigoPuntoVenta = $codigoPuntoVenta ?? SIAT_PUNTO_VENTA;

        $intentos = 0;
        $maxIntentos = 3;
        $ultimoError = null;

        while ($intentos < $maxIntentos) {
            $intentos++;
            try {
                $client = self::clienteSOAP('operacion');
                $resp = $client->cuis([
                    'SolicitudCuis' => [
                        'codigoAmbiente'   => SIAT_AMBIENTE,
                        'codigoModalidad'  => SIAT_MODALIDAD,
                        'codigoSistema'    => SIAT_COD_SISTEMA,
                        'nit'              => SIAT_NIT,
                        'codigoSucursal'   => $codigoSucursal,
                        'codigoPuntoVenta' => $codigoPuntoVenta
                    ]
                ]);

                $r = $resp->RespuestaCuis;

                if (!isset($r->codigo) || trim($r->codigo) === '') {
                    $ultimoError = 'Respuesta CUIS sin codigo';
                    error_log("SIAT obtenerCUIS: {$ultimoError} (intento {$intentos})");
                    if (!empty($r->mensajesList)) {
                        error_log("SIAT obtenerCUIS mensajes: " . json_encode($r->mensajesList));
                    }
                    sleep(1);
                    continue;
                }

                self::guardarCUIS($suId, $r->codigo, $r->fechaVigencia ?? null);
                return $r->codigo;
            } catch (SoapFault $e) {
                $ultimoError = $e->getMessage();
                error_log("SIAT obtenerCUIS SoapFault (intento {$intentos}/{$maxIntentos}): " . $ultimoError);
                sleep(1);
            } catch (Exception $e) {
                $ultimoError = $e->getMessage();
                error_log("SIAT obtenerCUIS Exception (intento {$intentos}/{$maxIntentos}): " . $ultimoError);
                sleep(1);
            }
        }

        return false;
    }

    private static function guardarCUIS($suId, $cuis, $vigencia)
    {
        $db = mainModel::conectar();

        $check = $db->prepare("SELECT sc_id FROM siat_configuracion WHERE su_id = :su_id");
        $check->execute([':su_id' => $suId]);

        if ($check->fetch()) {
            $stmt = $db->prepare("
                UPDATE siat_configuracion
                SET sc_cuis = :cuis, sc_cuis_expira = :vigencia, sc_actualizado = NOW()
                WHERE su_id = :su_id
            ");
        } else {
            $stmt = $db->prepare("
                INSERT INTO siat_configuracion (su_id, sc_cuis, sc_cuis_expira, sc_punto_venta)
                VALUES (:su_id, :cuis, :vigencia, :pv)
            ");
            $stmt->bindValue(':pv', SIAT_PUNTO_VENTA);
        }

        $stmt->bindValue(':su_id', $suId, PDO::PARAM_INT);
        $stmt->bindValue(':cuis', $cuis);
        $stmt->bindValue(':vigencia', $vigencia);
        $stmt->execute();
    }

    private static function aLista($data)
    {
        if ($data === null) {
            return [];
        }
        if (is_array($data)) {
            return $data;
        }
        return [$data];
    }

    private static function leerCUIS($suId)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("SELECT sc_cuis FROM siat_configuracion WHERE su_id = :su_id");
        $stmt->execute([':su_id' => $suId]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return ($row && !empty($row->sc_cuis)) ? $row->sc_cuis : false;
    }

    public static function obtenerCUFD($suId, $codigoSucursal = null, $codigoPuntoVenta = null)
    {
        $codigoSucursal = $codigoSucursal ?? SIAT_COD_SUCURSAL;
        $codigoPuntoVenta = $codigoPuntoVenta ?? SIAT_PUNTO_VENTA;

        $cuis = self::leerCUIS($suId);
        if (!$cuis) {
            error_log("SIAT obtenerCUFD: no hay CUIS para su_id={$suId}");
            return false;
        }

        $intentos = 0;
        $maxIntentos = 3;
        $ultimoError = null;

        while ($intentos < $maxIntentos) {
            $intentos++;
            try {
                $client = self::clienteSOAP('operacion');
                $resp = $client->cufd([
                    'SolicitudCufd' => [
                        'codigoAmbiente'   => SIAT_AMBIENTE,
                        'codigoModalidad'  => SIAT_MODALIDAD,
                        'codigoSistema'    => SIAT_COD_SISTEMA,
                        'nit'              => SIAT_NIT,
                        'cuis'             => $cuis,
                        'codigoSucursal'   => $codigoSucursal,
                        'codigoPuntoVenta' => $codigoPuntoVenta
                    ]
                ]);

                $r = $resp->RespuestaCufd;

                if (!isset($r->codigo) || trim($r->codigo) === '') {
                    $ultimoError = 'Respuesta CUFD sin codigo';
                    error_log("SIAT obtenerCUFD: {$ultimoError} (intento {$intentos})");
                    if (!empty($r->mensajesList)) {
                        error_log("SIAT obtenerCUFD mensajes: " . json_encode($r->mensajesList));
                    }
                    sleep(1);
                    continue;
                }

                self::guardarCUFD($suId, $r->codigo, $r->codigoControl ?? null, $r->fechaVigencia ?? null);
                return $r->codigo;
            } catch (SoapFault $e) {
                $ultimoError = $e->getMessage();
                error_log("SIAT obtenerCUFD SoapFault (intento {$intentos}/{$maxIntentos}): " . $ultimoError);
                sleep(1);
            } catch (Exception $e) {
                $ultimoError = $e->getMessage();
                error_log("SIAT obtenerCUFD Exception (intento {$intentos}/{$maxIntentos}): " . $ultimoError);
                sleep(1);
            }
        }

        return false;
    }

    public static function cufdVigente($suId)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("SELECT sc_cufd_expira FROM siat_configuracion WHERE su_id = :su_id");
        $stmt->execute([':su_id' => $suId]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if (!$row || empty($row->sc_cufd_expira)) {
            return false;
        }
        return strtotime($row->sc_cufd_expira) > time();
    }

    private static function guardarCUFD($suId, $codigo, $control, $vigencia)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            UPDATE siat_configuracion
            SET sc_cufd = :codigo, sc_cufd_control = :control, sc_cufd_expira = :vigencia, sc_actualizado = NOW()
            WHERE su_id = :su_id
        ");
        $stmt->execute([
            ':codigo'   => $codigo,
            ':control'  => $control,
            ':vigencia' => $vigencia,
            ':su_id'    => $suId
        ]);
    }

    private static function dec2hexString($dec)
    {
        $dec = ltrim((string) $dec, '0');
        if ($dec === '') {
            return '0';
        }
        $hexChars = '0123456789ABCDEF';
        $num = $dec;
        $hex = '';
        while ($num !== '' && $num !== '0') {
            $rem = 0;
            $quot = '';
            for ($i = 0; $i < strlen($num); $i++) {
                $cur = $rem * 10 + (int) $num[$i];
                $q = intdiv($cur, 16);
                $rem = $cur % 16;
                if ($quot !== '' || $q !== 0) {
                    $quot .= $q;
                }
            }
            $hex = $hexChars[$rem] . $hex;
            $num = $quot;
        }
        return $hex;
    }

    public static function generarCUF($nit, $fechaHora, $nroFactura, $codigoControl, $codigoSucursal = 0)
    {
        if (!ctype_digit((string) $nroFactura)) {
            error_log("SIAT generarCUF: nroFactura no numerico: {$nroFactura}");
            return false;
        }

        $nit = str_pad((string) $nit, 13, '0', STR_PAD_LEFT);
        $fecha = date('YmdHis', strtotime($fechaHora)) . '00';
        $nro = str_pad((string) $nroFactura, 10, '0', STR_PAD_LEFT);
        $suc = str_pad((string) $codigoSucursal, 4, '0', STR_PAD_LEFT);

        $cadena = $nit . $fecha . $nro . $suc . '2' . '1' . '1' . '01' . '0000';

        $suma = 0;
        $multi = 2;
        for ($i = strlen($cadena) - 1; $i >= 0; $i--) {
            $suma += (int) $cadena[$i] * $multi;
            $multi = ($multi == 9) ? 2 : $multi + 1;
        }
        $resto = $suma % 11;
        $verif = ($resto <= 1) ? $resto : 11 - $resto;

        $cadena54 = $cadena . $verif;
        $hex = self::dec2hexString($cadena54);

        return strtoupper($hex) . $codigoControl;
    }

    private static function tieneColumna($db, $tabla, $columna)
    {
        $dbname = $db->query("SELECT DATABASE()")->fetchColumn();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM information_schema.columns
            WHERE table_schema = :db AND table_name = :tabla AND column_name = :col
        ");
        $stmt->execute([':db' => $dbname, ':tabla' => $tabla, ':col' => $columna]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function obtenerDatosFacturaXML($veId)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            SELECT v.ve_total, v.ve_fecha_emision AS ve_fecha,
                   c.cl_carnet, c.cl_nombres, c.cl_apellido_paterno, c.cl_id,
                   ce.ce_nit, ce.ce_nombre, ce.ce_direccion, ce.ce_telefono,
                   f.fa_numero, f.fa_cuf,
                   sc.sc_cufd, sc.sc_cufd_control,
                   u.us_nombres AS us_nombre,
                   v.su_id
            FROM ventas v
            JOIN factura f ON f.ve_id = v.ve_id
            LEFT JOIN clientes c ON c.cl_id = v.cl_id
            JOIN configuracion_empresa ce ON ce.ce_id = 1
            JOIN siat_configuracion sc ON sc.su_id = v.su_id
            JOIN usuarios u ON u.us_id = v.us_id
            WHERE v.ve_id = :ve_id
            LIMIT 1
        ");
        $stmt->execute([':ve_id' => $veId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function obtenerDetalleFacturaXML($veId)
    {
        $db = mainModel::conectar();
        $sql = "
            SELECT dv.dv_cantidad, dv.dv_precio_unitario, dv.dv_descuento, dv.dv_subtotal,
                   m.med_id, m.med_nombre_quimico AS med_nombre
            FROM detalle_venta dv
            JOIN medicamento m ON m.med_id = dv.med_id
            WHERE dv.ve_id = :ve_id AND dv.dv_estado = 1
        ";
        if (self::tieneColumna($db, 'medicamento', 'med_codigo_sin')) {
            $sql = str_replace(
                'm.med_nombre_quimico AS med_nombre',
                'm.med_nombre_quimico AS med_nombre, m.med_codigo_sin AS codigo_sin',
                $sql
            );
        }
        $stmt = $db->prepare($sql);
        $stmt->execute([':ve_id' => $veId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function generarXML($datos, $detalles, $leyenda)
    {
        $xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<facturaComputarizadaCompraVenta ' .
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
            'xsi:noNamespaceSchemaLocation="facturaComputarizadaCompraVenta.xsd">' .
            '</facturaComputarizadaCompraVenta>'
        );

        $cab = $xml->addChild('cabecera');
        $cab->addChild('nitEmisor', $datos['ce_nit']);
        $cab->addChild('razonSocialEmisor', $datos['razon_social'] ?? $datos['ce_nombre']);
        $cab->addChild('municipio', 'La Paz');
        $cab->addChild('telefono', $datos['ce_telefono']);
        $cab->addChild('numeroFactura', $datos['fa_numero']);
        $cab->addChild('cuf', $datos['fa_cuf']);
        $cab->addChild('cufd', $datos['sc_cufd']);
        $cab->addChild('codigoSucursal', SIAT_COD_SUCURSAL);
        $cab->addChild('direccion', $datos['ce_direccion']);
        $cab->addChild('codigoPuntoVenta', SIAT_PUNTO_VENTA);
        $cab->addChild('fechaEmision', date('c', strtotime($datos['ve_fecha'])));
        $nombre = trim(($datos['cl_nombres'] ?? '') . ' ' . ($datos['cl_apellido_paterno'] ?? ''));
        $cab->addChild('nombreRazonSocial', $nombre !== '' ? $nombre : 'SIN NOMBRE');
        $carnet = $datos['cl_carnet'] ?? '0';
        $tipoDoc = (ctype_digit((string) $carnet) && strlen($carnet) === 13) ? 1 : 5;
        $cab->addChild('codigoTipoDocumentoIdentidad', $tipoDoc);
        $cab->addChild('numeroDocumento', $carnet !== '' ? $carnet : '0');
        $cab->addChild('complemento', '');
        $cab->addChild('codigoCliente', $datos['cl_id'] ?? 0);
        $cab->addChild('codigoMetodoPago', 1);
        $cab->addChild('montoTotal', $datos['ve_total']);
        $cab->addChild('montoTotalSujetoIva', $datos['ve_total']);
        $cab->addChild('codigoMoneda', 1);
        $cab->addChild('tipoCambio', 1);
        $cab->addChild('montoTotalMoneda', $datos['ve_total']);
        $cab->addChild('leyenda', $leyenda);
        $cab->addChild('usuario', $datos['us_nombre'] ?? '');

        foreach ($detalles as $d) {
            $det = $xml->addChild('detalle');
            $det->addChild('actividadEconomica', '477000');
            $det->addChild('codigoProductoSin', $d['codigo_sin'] ?? '99900');
            $det->addChild('codigoProducto', $d['med_id']);
            $det->addChild('descripcion', $d['med_nombre']);
            $det->addChild('cantidad', $d['dv_cantidad']);
            $det->addChild('unidadMedida', 1);
            $det->addChild('precioUnitario', $d['dv_precio_unitario']);
            $det->addChild('montoDescuento', $d['dv_descuento'] ?? 0);
            $det->addChild('subTotal', $d['dv_subtotal']);
        }

        return $xml->asXML();
    }

    public static function enviarFactura($xmlString, $faId, $cuf, $suId)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("SELECT sc_cuis, sc_cufd FROM siat_configuracion WHERE su_id = :su_id");
        $stmt->execute([':su_id' => $suId]);
        $sc = $stmt->fetch(PDO::FETCH_OBJ);
        if (!$sc || empty($sc->sc_cuis) || empty($sc->sc_cufd)) {
            throw new Exception("SIAT enviarFactura: falta CUIS o CUFD para su_id={$suId}");
        }

        $xmlGzip = gzencode($xmlString, 9);
        $xmlB64 = base64_encode($xmlGzip);
        $hash = hash('sha256', $xmlGzip);

        $client = self::clienteSOAP('facturacion');
        $resp = $client->recepcionFactura([
            'SolicitudServicioRecepcionFactura' => [
                'codigoAmbiente'       => SIAT_AMBIENTE,
                'codigoModalidad'      => SIAT_MODALIDAD,
                'codigoSistema'        => SIAT_COD_SISTEMA,
                'nit'                  => SIAT_NIT,
                'cuis'                 => $sc->sc_cuis,
                'cufd'                 => $sc->sc_cufd,
                'codigoSucursal'       => SIAT_COD_SUCURSAL,
                'codigoPuntoVenta'     => SIAT_PUNTO_VENTA,
                'archivo'              => $xmlB64,
                'hashArchivo'          => $hash,
                'fechaEnvio'           => date('Y-m-d\TH:i:s.000-04:00'),
                'tipoFacturaDocumento' => 1
            ]
        ]);

        $r = $resp->RespuestaServicioFacturacion;
        $estado = $r->codigoEstado ?? null;
        $ticket = $r->codigoRecepcion ?? null;

        $ins = $db->prepare("
            INSERT INTO facturacion_electronica
            (fa_id, fe_cuf, fe_estado_siat, fe_ticket, fe_fecha_envio, fe_payload, fe_tipo_emision)
            VALUES (:fa_id, :cuf, :estado, :ticket, NOW(), :payload, 1)
        ");
        $ins->execute([
            ':fa_id'   => $faId,
            ':cuf'     => $cuf,
            ':estado'  => $estado,
            ':ticket'  => $ticket,
            ':payload' => $xmlString
        ]);

        if ($estado == 908) {
            $db->prepare("UPDATE factura SET fa_estado = 1 WHERE fa_id = :fa_id")
               ->execute([':fa_id' => $faId]);
        }

        return $r;
    }

    public static function sincronizarCatalogos($suId = 1)
    {
        $cuis = self::leerCUIS($suId);
        if (!$cuis) {
            error_log("SIAT sincronizarCatalogos: no hay CUIS para su_id={$suId}");
            return false;
        }

        return [
            'fechaHora'   => self::sincronizarFechaHora($cuis),
            'actividades' => self::sincronizarActividades($cuis),
            'productos'   => self::sincronizarProductos($cuis),
            'leyendas'    => self::sincronizarLeyendas($cuis),
        ];
    }

    private static function sincronizarFechaHora($cuis)
    {
        try {
            $client = self::clienteSOAP('operacion');
            $resp = $client->sincronizarFechaHora([
                'SolicitudSincronizacion' => [
                    'codigoAmbiente'  => SIAT_AMBIENTE,
                    'codigoSistema'   => SIAT_COD_SISTEMA,
                    'nit'             => SIAT_NIT,
                    'cuis'            => $cuis,
                    'codigoModalidad' => SIAT_MODALIDAD,
                ]
            ]);
            $fh = $resp->RespuestaFechaHora->fechaHora ?? null;
            if ($fh) {
                error_log("SIAT fechaHora SIN: {$fh}");
            }
            return $fh;
        } catch (Exception $e) {
            error_log("SIAT sincronizarFechaHora: " . $e->getMessage());
            return false;
        }
    }

    private static function sincronizarActividades($cuis)
    {
        try {
            $client = self::clienteSOAP('codigos');
            $resp = $client->sincronizarListaActividadesDocumentoSector([
                'SolicitudSincronizacion' => [
                    'codigoAmbiente'  => SIAT_AMBIENTE,
                    'codigoSistema'   => SIAT_COD_SISTEMA,
                    'nit'             => SIAT_NIT,
                    'cuis'            => $cuis,
                    'codigoModalidad' => SIAT_MODALIDAD,
                ]
            ]);
            $lista = $resp->RespuestaListaActividades->listaActividades ?? [];
            self::guardarActividades(self::aLista($lista));
            return count(self::aLista($lista));
        } catch (Exception $e) {
            error_log("SIAT sincronizarActividades: " . $e->getMessage());
            return false;
        }
    }

    private static function sincronizarProductos($cuis)
    {
        try {
            $client = self::clienteSOAP('codigos');
            $resp = $client->sincronizarListaProductosServicios([
                'SolicitudSincronizacion' => [
                    'codigoAmbiente'  => SIAT_AMBIENTE,
                    'codigoSistema'   => SIAT_COD_SISTEMA,
                    'nit'             => SIAT_NIT,
                    'cuis'            => $cuis,
                    'codigoModalidad' => SIAT_MODALIDAD,
                    'codigoActividad' => '477000',
                ]
            ]);
            $lista = $resp->RespuestaListaProductos->listaProductos ?? [];
            self::guardarProductos(self::aLista($lista));
            return count(self::aLista($lista));
        } catch (Exception $e) {
            error_log("SIAT sincronizarProductos: " . $e->getMessage());
            return false;
        }
    }

    private static function sincronizarLeyendas($cuis)
    {
        try {
            $client = self::clienteSOAP('codigos');
            $resp = $client->sincronizarParametricaListaLeyendasFactura([
                'SolicitudSincronizacion' => [
                    'codigoAmbiente'  => SIAT_AMBIENTE,
                    'codigoSistema'   => SIAT_COD_SISTEMA,
                    'nit'             => SIAT_NIT,
                    'cuis'            => $cuis,
                    'codigoModalidad' => SIAT_MODALIDAD,
                ]
            ]);
            $lista = $resp->RespuestaListaLeyendas->listaLeyendas ?? [];
            self::guardarLeyendas(self::aLista($lista));
            return count(self::aLista($lista));
        } catch (Exception $e) {
            error_log("SIAT sincronizarLeyendas: " . $e->getMessage());
            return false;
        }
    }

    private static function guardarActividades($lista)
    {
        $db = mainModel::conectar();
        $db->beginTransaction();
        try {
            $db->exec("DELETE FROM siat_actividades");
            $stmt = $db->prepare("
                INSERT INTO siat_actividades (codigo_caeb, descripcion, tipo_actividad)
                VALUES (:caeb, :desc, :tipo)
            ");
            foreach ($lista as $a) {
                $stmt->execute([
                    ':caeb' => $a->codigoCaeb ?? '',
                    ':desc' => $a->descripcion ?? '',
                    ':tipo' => $a->tipoActividad ?? 1,
                ]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("SIAT guardarActividades: " . $e->getMessage());
        }
    }

    private static function guardarProductos($lista)
    {
        $db = mainModel::conectar();
        $db->beginTransaction();
        try {
            $db->exec("DELETE FROM siat_productos");
            $stmt = $db->prepare("
                INSERT INTO siat_productos (codigo_producto, descripcion, codigo_actividad)
                VALUES (:cod, :desc, :act)
            ");
            foreach ($lista as $p) {
                $stmt->execute([
                    ':cod' => $p->codigoProducto ?? '',
                    ':desc' => $p->descripcion ?? '',
                    ':act' => $p->codigoActividad ?? null,
                ]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("SIAT guardarProductos: " . $e->getMessage());
        }
    }

    private static function guardarLeyendas($lista)
    {
        $db = mainModel::conectar();
        $db->beginTransaction();
        try {
            $db->exec("DELETE FROM siat_leyendas");
            $stmt = $db->prepare("
                INSERT INTO siat_leyendas (codigo_actividad, descripcion)
                VALUES (:act, :desc)
            ");
            foreach ($lista as $l) {
                $stmt->execute([
                    ':act' => $l->codigoActividad ?? null,
                    ':desc' => $l->descripcion ?? '',
                ]);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("SIAT guardarLeyendas: " . $e->getMessage());
        }
    }
}
