-- ============================================================
-- Script: agregar el estado 'deshabilitado' al ENUM `lm_estado`
-- Tabla : `lote_medicamento`   (BD: samfarm_db)
-- Módulo: Lotes / borrado lógico (soft delete)
--
-- MOTIVO
--   El borrado lógico del módulo de lotes lo ejecuta
--   loteController::deshabilitar_lote_controller():
--
--     UPDATE lote_medicamento
--        SET lm_estado = 'deshabilitado', lm_actualizado_en = NOW()
--      WHERE lm_id = :id
--
--   pero el ENUM original NO contenía ese valor:
--
--     enum('en_espera','activo','terminado','caducado','devuelto','bloqueado')
--
--   Como el servidor (MariaDB 10.4 - XAMPP) NO está en modo estricto
--   (sql_mode = NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION), MariaDB
--   no fallaba: truncaba el valor y guardaba lm_estado = '' con
--   "Warning 1265: Data truncated for column 'lm_estado'". Aun así el UPDATE
--   afectaba 1 fila (por lm_actualizado_en), por lo que el controlador
--   respondía "Lote eliminado correctamente" SIN marcar realmente el lote.
--   Resultado: el lote seguía mostrándose en el listado como si nada.
--
-- EFECTO DE ESTE SCRIPT
--   - El ENUM pasa a admitir 'deshabilitado' (el MISMO nombre que usa el
--     borrado lógico), por lo que el soft delete ya guarda el valor real.
--   - El valor se AGREGA AL FINAL: los valores existentes mantienen su
--     índice y sus datos no se alteran (no hay conversión de filas).
--   - Este script, por sí solo, NO modifica ninguna fila de datos.
--
-- EJECUCIÓN
--   mysql -u root -D samfarm_db < alter_lote_estado_deshabilitado.sql
--   (o pegarlo en phpMyAdmin con la base samfarm_db seleccionada)
-- ============================================================

SET NAMES utf8mb4;

-- 1) Verificación previa (debe NO aparecer 'deshabilitado')
SELECT COLUMN_TYPE AS antes
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME   = 'lote_medicamento'
  AND COLUMN_NAME  = 'lm_estado';

-- 2) Agregar el estado de borrado lógico al ENUM
ALTER TABLE `lote_medicamento`
  MODIFY COLUMN `lm_estado`
    ENUM('en_espera','activo','terminado','caducado','devuelto','bloqueado','deshabilitado')
    NOT NULL DEFAULT 'en_espera'
    COMMENT 'estado del lote; deshabilitado = eliminado logicamente (soft delete)';

-- 3) Verificación posterior (debe mostrarse 'deshabilitado' al final)
SELECT COLUMN_TYPE AS despues
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME   = 'lote_medicamento'
  AND COLUMN_NAME  = 'lm_estado';

-- 4) OPCIONAL - reparar lotes que quedaron corruptos con estado '' (cero bytes)
--    Esas filas son lotes a los que se les intentó hacer soft delete ANTES de
--    este script: MariaDB truncó 'deshabilitado' y guardó ''. Descomenta si tu
--    base ya arrastra filas así (revisar primero con la consulta de abajo).
--
-- SELECT COUNT(*) AS filas_estado_vacio FROM `lote_medicamento` WHERE `lm_estado` = '';
--
-- UPDATE `lote_medicamento`
--    SET `lm_estado` = 'deshabilitado'
--  WHERE `lm_estado` = '';
--
-- SELECT COUNT(*) AS eliminados_marcados
-- FROM `lote_medicamento` WHERE `lm_estado` = 'deshabilitado';

-- ============================================================
-- ROLLBACK (revertir el ENUM a su estado original)
--   ALTER TABLE `lote_medicamento`
--     MODIFY COLUMN `lm_estado`
--       ENUM('en_espera','activo','terminado','caducado','devuelto','bloqueado')
--       NOT NULL DEFAULT 'en_espera';
--
--   IMPORTANTE: si existen filas con lm_estado = 'deshabilitado', el rollback
--   las convertirá a '' (o dará error si algún día se activa sql_mode estricto).
--   Antes de revertir, reclasificar esos lotes (p. ej. a 'terminado').
-- ============================================================
