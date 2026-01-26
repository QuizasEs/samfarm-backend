<?php

if (file_exists(__DIR__ . "/../config/SERVER.php")) {
    require_once __DIR__ . "/../config/SERVER.php";
}

class preciosActualizarModel
{
    public function conectar()
    {
        // Usar constantes de SERVER.php si están disponibles
        $host = defined('SERVER') ? SERVER : 'localhost';
        $dbname = defined('DB') ? DB : 'samfarm_db';
        $user = defined('USER') ? USER : 'root';
        $pass = defined('PASS') ? PASS : '';

        try {
            $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Forzar el collation para evitar conflictos
            $conexion->exec("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");
            return $conexion;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function actualizarPreciosDesdeCSV($filePath)
    {
        $db = $this->conectar();

        $logMultiplesResultados = [];
        $logNoEncontrados = [];
        $contadorActualizados = 0;
        $fila = 0;

        // Detectar delimitador
        $file = new SplFileObject($filePath);
        $firstLine = $file->fgets();
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        $file = null; // Cerrar SplFileObject

        if (($gestor = fopen($filePath, "r")) === FALSE) {
            return [
                'error' => 'No se pudo abrir el archivo CSV.'
            ];
        }

        while (($datos = fgetcsv($gestor, 1000, $delimiter)) !== FALSE) {
            $fila++;
            if ($fila == 1) { // Omitir la cabecera
                continue;
            }

            if (count($datos) < 2) continue;

            $nombreMedicamento = trim($datos[0]);
            $precioVenta = trim($datos[1]);

            if (empty($nombreMedicamento)) {
                continue;
            }

            // En caso que se encuentre el medicamento pero no tenga precio el cual actualizar en el archivo excel, dejar sin cambios
            if ($precioVenta === '' || $precioVenta === null) {
                continue; 
            }

            if (!is_numeric($precioVenta)) {
                continue;
            }

            // Búsqueda en lote_medicamento joined con medicamento
            // Nivel 1: Búsqueda por nombre químico exacto (sin signos de puntuación)
            $nombreSinSignos = preg_replace('/[.,]/', '', $nombreMedicamento);
            $stmt = $db->prepare("SELECT l.lm_id FROM lote_medicamento l JOIN medicamento m ON l.med_id = m.med_id WHERE REPLACE(REPLACE(m.med_nombre_quimico, '.', ''), ',', '') = :nombre AND l.lm_estado = 'activo'");
            $stmt->bindParam(":nombre", $nombreSinSignos);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $totalFilas = count($resultados);

            if ($totalFilas == 1) {
                $id_lote = $resultados[0];
                $updateStmt = $db->prepare("UPDATE lote_medicamento SET lm_precio_venta = :precio WHERE lm_id = :id");
                $updateStmt->bindParam(":precio", $precioVenta);
                $updateStmt->bindParam(":id", $id_lote);
                if ($updateStmt->execute()) {
                    $contadorActualizados++;
                }
            } elseif ($totalFilas >= 2) {
                $logMultiplesResultados[] = $nombreMedicamento;
            } else {
                // Nivel 2: Búsqueda Flexible con ratio al 40% del nombre (sin signos de puntuación)
                $nombreSinSignos = preg_replace('/[.,]/', '', $nombreMedicamento);
                $longitud = strlen($nombreSinSignos);
                $nombre40 = substr($nombreSinSignos, 0, floor($longitud * 0.4));
                
                if (strlen($nombre40) > 0) {
                    $nombreLike = $nombre40 . '%';
                    $stmtFlex = $db->prepare("SELECT l.lm_id FROM lote_medicamento l JOIN medicamento m ON l.med_id = m.med_id WHERE REPLACE(REPLACE(m.med_nombre_quimico, '.', ''), ',', '') LIKE :nombre_like AND l.lm_estado = 'activo'");
                    $stmtFlex->bindParam(":nombre_like", $nombreLike);
                    $stmtFlex->execute();
                    $resultadosFlex = $stmtFlex->fetchAll(PDO::FETCH_COLUMN);
                    $totalFilasFlex = count($resultadosFlex);

                    if ($totalFilasFlex == 1) {
                        $id_lote = $resultadosFlex[0];
                        $updateStmt = $db->prepare("UPDATE lote_medicamento SET lm_precio_venta = :precio WHERE lm_id = :id");
                        $updateStmt->bindParam(":precio", $precioVenta);
                        $updateStmt->bindParam(":id", $id_lote);
                        if ($updateStmt->execute()) {
                            $contadorActualizados++;
                        }
                    } elseif ($totalFilasFlex >= 2) {
                        $logMultiplesResultados[] = $nombreMedicamento . " (Búsqueda 40%)";
                    } else {
                        // Nivel 3: Concatenación completa de columnas con ratio al 100%
                        $stmtConcat = $db->prepare("SELECT l.lm_id FROM lote_medicamento l JOIN medicamento m ON l.med_id = m.med_id WHERE REPLACE(REPLACE(CONCAT(COALESCE(m.med_nombre_quimico, ''), ' ', COALESCE(m.med_principio_activo, ''), ' ', COALESCE(m.med_accion_farmacologica, ''), ' ', COALESCE(m.med_presentacion, ''), ' ', COALESCE(m.med_descripcion, '')), '.', ''), ',', '') = :nombre AND l.lm_estado = 'activo'");
                        $stmtConcat->bindParam(":nombre", $nombreSinSignos);
                        $stmtConcat->execute();
                        $resultadosConcat = $stmtConcat->fetchAll(PDO::FETCH_COLUMN);
                        $totalFilasConcat = count($resultadosConcat);

                        if ($totalFilasConcat == 1) {
                            $id_lote = $resultadosConcat[0];
                            $updateStmt = $db->prepare("UPDATE lote_medicamento SET lm_precio_venta = :precio WHERE lm_id = :id");
                            $updateStmt->bindParam(":precio", $precioVenta);
                            $updateStmt->bindParam(":id", $id_lote);
                            if ($updateStmt->execute()) {
                                $contadorActualizados++;
                            }
                        } elseif ($totalFilasConcat >= 2) {
                            $logMultiplesResultados[] = $nombreMedicamento . " (Concatenación completa)";
                        } else {
                            // Nivel 4: Concatenación con ratio al 60%
                            $longitudConcat = strlen($nombreSinSignos);
                            $nombre60 = substr($nombreSinSignos, 0, floor($longitudConcat * 0.6));
                            
                            if (strlen($nombre60) > 0) {
                                $nombreLikeConcat = $nombre60 . '%';
                                $stmtConcatFlex = $db->prepare("SELECT l.lm_id FROM lote_medicamento l JOIN medicamento m ON l.med_id = m.med_id WHERE REPLACE(REPLACE(CONCAT(COALESCE(m.med_nombre_quimico, ''), ' ', COALESCE(m.med_principio_activo, ''), ' ', COALESCE(m.med_accion_farmacologica, ''), ' ', COALESCE(m.med_presentacion, ''), ' ', COALESCE(m.med_descripcion, '')), '.', ''), ',', '') LIKE :nombre_like AND l.lm_estado = 'activo'");
                                $stmtConcatFlex->bindParam(":nombre_like", $nombreLikeConcat);
                                $stmtConcatFlex->execute();
                                $resultadosConcatFlex = $stmtConcatFlex->fetchAll(PDO::FETCH_COLUMN);
                                $totalFilasConcatFlex = count($resultadosConcatFlex);

                                if ($totalFilasConcatFlex == 1) {
                                    $id_lote = $resultadosConcatFlex[0];
                                    $updateStmt = $db->prepare("UPDATE lote_medicamento SET lm_precio_venta = :precio WHERE lm_id = :id");
                                    $updateStmt->bindParam(":precio", $precioVenta);
                                    $updateStmt->bindParam(":id", $id_lote);
                                    if ($updateStmt->execute()) {
                                        $contadorActualizados++;
                                    }
                                } elseif ($totalFilasConcatFlex >= 2) {
                                    $logMultiplesResultados[] = $nombreMedicamento . " (Concatenación 60%)";
                                } else {
                                    $logNoEncontrados[] = $nombreMedicamento;
                                }
                            } else {
                                $logNoEncontrados[] = $nombreMedicamento;
                            }
                        }
                    }
                } else {
                    $logNoEncontrados[] = $nombreMedicamento;
                }
            }
        }
        fclose($gestor);

        return [
            'actualizados' => $contadorActualizados,
            'multiples' => $logMultiplesResultados,
            'no_encontrados' => $logNoEncontrados
        ];
    }
}
