-- ============================================================
-- SCRIPT DE MODIFICACIÓN: Eliminar tabla laboratorios
--                y modificar tabla proveedores
-- ============================================================
-- Este script debe ejecutarse en la base de datos samfarm_db
-- para eliminar la tabla laboratorios y sus relaciones
-- y modificar la tabla proveedores
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. Eliminar foreign keys que referencian a laboratorios
-- ============================================================

-- Eliminar foreign key de compras que referencia laboratorios
ALTER TABLE `compras` DROP FOREIGN KEY IF EXISTS `fk_compras_laboratorio`;

-- Eliminar foreign key de medicamento que referencia laboratorios  
ALTER TABLE `medicamento` DROP FOREIGN KEY IF EXISTS `fk_medicamento_laboratorio`;

-- Eliminar foreign keys de proveedores_laboratorio
ALTER TABLE `proveedores_laboratorio` DROP FOREIGN KEY IF EXISTS `fk_pl_la`;
ALTER TABLE `proveedores_laboratorio` DROP FOREIGN KEY IF EXISTS `fk_pl_pr`;

-- ============================================================
-- 2. Eliminar índices que referencian a laboratorios
-- ============================================================

-- Eliminar índice de compras para la_id
ALTER TABLE `compras` DROP INDEX IF EXISTS `fk_compras_laboratorios`;

-- Eliminar índice de medicamento para la_id
ALTER TABLE `medicamento` DROP INDEX IF EXISTS `fk_med_la`;

-- Eliminar índices de proveedores_laboratorio
ALTER TABLE `proveedores_laboratorio` DROP INDEX IF EXISTS `ux_pl_pr_la`;
ALTER TABLE `proveedores_laboratorio` DROP INDEX IF EXISTS `fk_pl_la`;

-- ============================================================
-- 3. Eliminar columna la_id de las tablas
-- ============================================================

-- Eliminar columna la_id de compras
ALTER TABLE `compras` DROP COLUMN IF EXISTS `la_id`;

-- Eliminar columna la_id de medicamento
ALTER TABLE `medicamento` DROP COLUMN IF EXISTS `la_id`;

-- ============================================================
-- 4. Eliminar tablas
-- ============================================================

-- Eliminar tabla proveedores_laboratorio
DROP TABLE IF EXISTS `proveedores_laboratorio`;

-- Eliminar tabla laboratorios
DROP TABLE IF EXISTS `laboratorios`;

-- ============================================================
-- 5. Modificar tabla proveedores
-- ============================================================

-- Eliminar columnas antiguas de proveedores
ALTER TABLE `proveedores` DROP COLUMN IF EXISTS `pr_nombres`;
ALTER TABLE `proveedores` DROP COLUMN IF EXISTS `pr_apellido_paterno`;
ALTER TABLE `proveedores` DROP COLUMN IF EXISTS `pr_apellido_materno`;
ALTER TABLE `proveedores` DROP COLUMN IF EXISTS `pr_direccion`;

-- Agregar nuevas columnas a proveedores
ALTER TABLE `proveedores` ADD COLUMN `pr_razon_social` varchar(250) DEFAULT NULL AFTER `pr_id`;
ALTER TABLE `proveedores` ADD COLUMN `pr_nombre_comercial` varchar(250) DEFAULT NULL AFTER `pr_razon_social`;
ALTER TABLE `proveedores` ADD COLUMN `pr_correo` varchar(200) DEFAULT NULL AFTER `pr_telefono`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Verificación
-- ============================================================

SELECT 'Modificación completada correctamente' AS resultado;

-- Verificar que las tablas eliminadas no existan
SELECT TABLE_NAME 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'samfarm_db' 
AND TABLE_NAME IN ('laboratorios', 'proveedores_laboratorio');

-- Verificar que las columnas la_id fueron eliminadas
SELECT TABLE_NAME, COLUMN_NAME 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'samfarm_db' 
AND TABLE_NAME IN ('compras', 'medicamento') 
AND COLUMN_NAME = 'la_id';

-- Verificar la nueva estructura de proveedores
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT, IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'samfarm_db'
AND TABLE_NAME = 'proveedores'
ORDER BY ORDINAL_POSITION;
