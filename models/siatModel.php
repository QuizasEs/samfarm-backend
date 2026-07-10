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
}
