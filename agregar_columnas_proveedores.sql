-- ============================================================
-- Script para agregar columnas faltantes a la tabla proveedores
-- Ejecutar este script en phpMyAdmin o MySQL Workbench
-- ============================================================

-- Agregar columnas faltantes (ignora errores si ya existen)
ALTER TABLE `proveedores` ADD COLUMN `pr_razon_social` varchar(250) DEFAULT NULL AFTER `pr_id`;
ALTER TABLE `proveedores` ADD COLUMN `pr_nombre_comercial` varchar(250) DEFAULT NULL AFTER `pr_razon_social`;
ALTER TABLE `proveedores` ADD COLUMN `pr_correo` varchar(200) DEFAULT NULL AFTER `pr_telefono`;

-- Copiar datos existentes de pr_nombres a pr_razon_social
UPDATE proveedores SET pr_razon_social = pr_nombres WHERE pr_nombres IS NOT NULL AND pr_nombres != '';

-- Verificar estructura actual
SELECT COLUMN_NAME FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'samfarm_db' AND TABLE_NAME = 'proveedores';
