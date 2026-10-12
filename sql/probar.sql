
-- ============================================================
-- E1. puntos_venta: 7 columnas SIAT (FALTA pv_cufd_anterior a propósito)
-- Riesgo: alto | Idempotente: SÍ (IF NOT EXISTS)
-- ============================================================
ALTER TABLE `puntos_venta`
  ADD COLUMN IF NOT EXISTS `pv_codigo_sin` INT NULL COMMENT 'Codigo de punto de venta asignado por el SIN',
  ADD COLUMN IF NOT EXISTS `pv_registrado_sin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=ya registrado en el SIN',
  ADD COLUMN IF NOT EXISTS `pv_cuis` VARCHAR(20) NULL COMMENT 'CUIS propio de este punto de venta',
  ADD COLUMN IF NOT EXISTS `pv_cuis_vigente_hasta` DATETIME NULL COMMENT 'Fin de vigencia del CUIS',
  ADD COLUMN IF NOT EXISTS `pv_cufd` TEXT NULL COMMENT 'CUFD vigente de este punto de venta',
  ADD COLUMN IF NOT EXISTS `pv_cufd_control` VARCHAR(200) NULL COMMENT 'Codigo de control del CUFD',
  ADD COLUMN IF NOT EXISTS `pv_cufd_vigente_hasta` DATETIME NULL COMMENT 'Fin de vigencia del CUFD';
-- ============================================================
-- E2. siat_paquetes: pa_archivo + pa_facturas
-- Riesgo: controlado/SIAT | Idempotente: SÍ
-- ============================================================
ALTER TABLE `siat_paquetes`
  ADD COLUMN IF NOT EXISTS `pa_archivo` LONGTEXT NULL COMMENT 'TAR.GZ del paquete en base64',
  ADD COLUMN IF NOT EXISTS `pa_facturas` LONGTEXT NULL COMMENT 'JSON con las facturas del paquete';
-- ============================================================
-- E3. siat_productos: columna muerta `nandina` para igualar a local
-- Riesgo: controlado | Idempotente: SÍ
-- Nota: `pro_nandina` ya existe y es la que usa el sistema. `nandina` viene NULL en local.
-- Alternativa (no incluida): eliminarla del local en vez de agregarla aquí.
-- ============================================================
ALTER TABLE `siat_productos`
  ADD COLUMN IF NOT EXISTS `nandina` TEXT NULL;

-- ============================================================
-- E4. factura: renombre preservando datos + 14 columnas
-- Riesgo: CRÍTICO | Idempotente: NO (CHANGE y ADD UNIQUE son UNA SOLA VEZ)
-- Previa obligatoria: E0.3 en 0. Backup verificado.
-- ============================================================
-- 4.1 Renombre fa_numero -> fa_numero_control (preserva valores). UNA SOLA VEZ.
-- Si SHOW CREATE TABLE ya muestra fa_numero_control y no fa_numero, SALTA 4.1.
ALTER TABLE `factura`
  CHANGE COLUMN `fa_numero` `fa_numero_control` VARCHAR(100) NOT NULL COMMENT 'Número preimpreso (ej: 001-001-0000123)';

-- 4.2 Columnas SIAT. Idempotente SÍ.
ALTER TABLE `factura`
  ADD COLUMN IF NOT EXISTS `fa_numero_siat` BIGINT(20) UNSIGNED NULL COMMENT 'Número secuencial SIAT (solo números)',
  ADD COLUMN IF NOT EXISTS `fa_cufd` VARCHAR(255) NULL COMMENT 'CUFD usado al emitir',
  ADD COLUMN IF NOT EXISTS `fa_cufd_control` VARCHAR(200) NULL COMMENT 'Código de control del CUFD',
  ADD COLUMN IF NOT EXISTS `fa_leyenda` TEXT NULL COMMENT 'Leyenda legal obligatoria',
  ADD COLUMN IF NOT EXISTS `fa_monto_sujeto_iva` DECIMAL(14,2) NULL COMMENT 'Monto sujeto a IVA (base imponible)',
  ADD COLUMN IF NOT EXISTS `fa_descuento_adicional` DECIMAL(14,2) NULL DEFAULT 0.00 COMMENT 'Descuento global de la factura',
  ADD COLUMN IF NOT EXISTS `fa_codigo_metodo_pago` VARCHAR(20) NULL COMMENT 'Código SIN método pago (1=Efectivo, 2=Tarjeta...)',
  ADD COLUMN IF NOT EXISTS `fa_numero_tarjeta` VARCHAR(50) NULL COMMENT 'Últimos 4 dígitos tarjeta',
  ADD COLUMN IF NOT EXISTS `fa_codigo_moneda` VARCHAR(3) NULL DEFAULT '1' COMMENT 'Código SIN moneda: 1=BOB, 2=USD',
  ADD COLUMN IF NOT EXISTS `fa_tipo_cambio` DECIMAL(10,4) NULL DEFAULT 1.0000 COMMENT 'Tipo de cambio del día',
  ADD COLUMN IF NOT EXISTS `fa_doc_sector` VARCHAR(10) NULL DEFAULT '1' COMMENT 'Código documento sector',
  ADD COLUMN IF NOT EXISTS `fa_codigo_emision` VARCHAR(10) NULL DEFAULT '1' COMMENT '1=Online, 2=Computarizada',
  ADD COLUMN IF NOT EXISTS `fa_tipo_factura` VARCHAR(20) NULL DEFAULT 'factura' COMMENT 'factura, nota_credito, nota_venta...',
  ADD COLUMN IF NOT EXISTS `fa_modalidad` TINYINT(4) NULL DEFAULT 1 COMMENT '1=Electrónica, 2=Computarizada';

-- 4.3 Índices nuevos. UNA SOLA VEZ cada uno. Verificar antes con:
-- SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='factura';
-- Si ya existen, saltar. Requieren E0.3 = 0.
ALTER TABLE `factura` ADD UNIQUE KEY `ux_fa_ve_id` (`ve_id`);
ALTER TABLE `factura` ADD UNIQUE KEY `ux_fa_cuf` (`fa_cuf`);
ALTER TABLE `factura` ADD KEY `ix_fa_numero_siat` (`fa_numero_siat`);
-- ============================================================
-- E5. factura_secuencia: columna + cambio de PK
-- Riesgo: CRÍTICO | Idempotente: NO
-- Previa: SHOW CREATE TABLE (E0.5). Si PK ya es (su_id,fs_anio,fs_punto_venta), saltar todo E5.
-- ============================================================
-- 5.1 Columna (idempotente SÍ)
ALTER TABLE `factura_secuencia`
  ADD COLUMN IF NOT EXISTS `fs_punto_venta` INT(11) NOT NULL DEFAULT 0 COMMENT 'Código SIN punto de venta (0, 1, 2...)';

-- 5.2 Cambio de PK (UNA SOLA VEZ, DDL no reversible, ventana de mantenimiento)
-- Solo si PK actual es (su_id,fs_anio):
ALTER TABLE `factura_secuencia` DROP PRIMARY KEY, ADD PRIMARY KEY (`su_id`,`fs_anio`,`fs_punto_venta`);
-- ============================================================
-- E6. facturacion_electronica: 10 columnas nuevas
-- Riesgo: CRÍTICO | Columnas idempotentes SÍ, índices UNA SOLA VEZ
-- Previa: E0.3 dup_fe_fa_id = 0 para el UNIQUE
-- ============================================================
ALTER TABLE `facturacion_electronica`
  ADD COLUMN IF NOT EXISTS `fe_tipo_emision` ENUM('1','2') NULL DEFAULT '1' COMMENT '1=Online, 2=Contingencia',
  ADD COLUMN IF NOT EXISTS `fe_estado_local` VARCHAR(30) NULL DEFAULT 'PENDIENTE' COMMENT 'Estado interno: PENDIENTE, ENVIADO, VALIDADO, RECHAZADO, ANULADO',
  ADD COLUMN IF NOT EXISTS `fe_mensajes` LONGTEXT NULL COMMENT 'Errores/respuestas del SIN',
  ADD COLUMN IF NOT EXISTS `fe_intentos` INT(11) NULL DEFAULT 0 COMMENT 'Contador de reintentos',
  ADD COLUMN IF NOT EXISTS `fe_paquete_id` BIGINT(20) UNSIGNED NULL COMMENT 'FK a siat_paquetes (contingencia)',
  ADD COLUMN IF NOT EXISTS `fe_anulado_motivo` VARCHAR(255) NULL COMMENT 'Código motivo anulación SIN',
  ADD COLUMN IF NOT EXISTS `fe_anulado_en` DATETIME NULL COMMENT 'Fecha de anulación',
  ADD COLUMN IF NOT EXISTS `fe_codigo_recepcion` VARCHAR(100) NULL COMMENT 'Ticket/código recepción SIN',
  ADD COLUMN IF NOT EXISTS `fe_hash_archivo` VARCHAR(255) NULL COMMENT 'SHA256 del XML enviado',
  ADD COLUMN IF NOT EXISTS `fe_ultimo_intento` DATETIME NULL COMMENT 'Último reintento de envío';

-- Índices (UNA SOLA VEZ; verificar en information_schema.STATISTICS antes):
ALTER TABLE `facturacion_electronica` ADD UNIQUE KEY `ux_fe_fa_id` (`fa_id`);
ALTER TABLE `facturacion_electronica` ADD KEY `ix_fe_estado_siat` (`fe_estado_siat`);
ALTER TABLE `facturacion_electronica` ADD KEY `ix_fe_paquete_id` (`fe_paquete_id`);

-- ============================================================
-- E6-BLOQUE GATED: eliminar fe_cuf (SOLO tras E0.4 + backup + decisión)
-- Riesgo: CRÍTICO/pérdida definitiva | UNA SOLA VEZ
-- NO ejecutar si fe_cuf_con_valor > 0 salvo que hayas respaldado y aceptado la pérdida.
-- El código ya no la usa, pero eso no autoriza borrar datos sin verificar.
-- ============================================================
-- DESCOMENTAR SOLO SI E0.4 dio 0 en fe_cuf_con_valor:
-- ALTER TABLE `facturacion_electronica` DROP COLUMN `fe_cuf`;
-- ============================================================
-- E7. usuarios.pv_id + índice + backfill opcional + FK
-- Riesgo: CRÍTICO | Columna idempotente SÍ, KEY/FK UNA SOLA VEZ
-- Dependencia: E1 ya aplicado (puntos_venta existe)
-- ============================================================
-- 7.1 Columna (idempotente)
ALTER TABLE `usuarios`
  ADD COLUMN IF NOT EXISTS `pv_id` BIGINT(20) UNSIGNED NULL COMMENT 'Punto de venta asignado (pv_id)';

-- 7.2 Backfill SOLO con datos de producción (no toca volcado local). Es operación de DATOS, separada del DDL.
-- Idempotente (solo NULLs). Verificar después el conteo de sin_anclar.
UPDATE `usuarios` u
LEFT JOIN `puntos_venta` pv ON pv.`su_id` = u.`su_id` AND pv.`pv_codigo` = u.`us_punto_venta`
SET u.`pv_id` = pv.`pv_id`
WHERE u.`pv_id` IS NULL AND u.`us_punto_venta` IS NOT NULL AND pv.`pv_id` IS NOT NULL;

-- Diagnóstico: debe dar 0, o lista a resolver manualmente (no forzar FK si >0 con huérfanos reales)
SELECT COUNT(*) AS sin_anclar FROM `usuarios` WHERE `us_punto_venta` IS NOT NULL AND `pv_id` IS NULL;
-- SELECT us_id, su_id, us_punto_venta, pv_id FROM usuarios WHERE us_punto_venta IS NOT NULL AND pv_id IS NULL LIMIT 50;

-- 7.3 Índice + FK (UNA SOLA VEZ; verificar antes):
-- SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='usuarios' AND INDEX_NAME='ix_us_pv_id';
-- SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='usuarios' AND CONSTRAINT_NAME='fk_us_pv';
ALTER TABLE `usuarios` ADD KEY `ix_us_pv_id` (`pv_id`);
ALTER TABLE `usuarios` ADD CONSTRAINT `fk_us_pv` FOREIGN KEY (`pv_id`) REFERENCES `puntos_venta` (`pv_id`) ON DELETE SET NULL;
ALTER TABLE `facturacion_electronica` DROP COLUMN `fe_cuf`;


















































-- ejecutar despues
-- ============================================================
-- CORRECTIVO: deja samfarm_db (XAMPP) idéntica a samfarm_db.sql
-- Estado: PROBADO SOBRE COPIA (2026-10-11). Sin cambios de datos.
-- NO EJECUTAR SIN REVISAR + BACKUP PREVIO (mysqldump).
-- ============================================================

-- DIF 1: recolocar pa_archivo / pa_facturas en su posición original.
-- MODIFY redefine la columna con la misma definición; no toca datos.
ALTER TABLE `siat_paquetes`
  MODIFY COLUMN `pa_archivo` longtext DEFAULT NULL COMMENT 'TAR.GZ del paquete en base64' AFTER `pa_hash_archivo`,
  MODIFY COLUMN `pa_facturas` longtext DEFAULT NULL COMMENT 'JSON con las facturas del paquete' AFTER `pa_archivo`;

-- DIF 2: fe_mensajes con collation binaria + validación JSON (patrón local).
-- Cambiar collation dentro de utf8mb4 no reescribe valores; solo semántica de comparación.
ALTER TABLE `facturacion_electronica`
  MODIFY COLUMN `fe_mensajes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
    DEFAULT NULL COMMENT 'Errores/respuestas del SIN' CHECK (json_valid(`fe_mensajes`));
-- Si en XAMPP hubiera filas con fe_mensajes inválido como JSON, este ALTER fallará
-- (eso sería un hallazgo: datos que la BD local rechazaría). Verificar antes con:
-- SELECT fe_id FROM samfarm_db.facturacion_electronica
--   WHERE fe_mensajes IS NOT NULL AND NOT json_valid(fe_mensajes);








-- actualiza la iformacion de la empresa
UPDATE configuracion_empresa
SET ce_nit = '6006007013',
    ce_direccion = 'Final Los Andes',
    ce_municipio = 'La paz',
    ce_codigo_actividad = '4772100',
    ce_correo = 'contacto@samfarm.com',
    ce_actualizado_en = NOW()
WHERE ce_id = 1;
















-- actualizar la tabla de configuracion siat
-- Su_id=1: código 0
UPDATE siat_configuracion SET 
    sc_sucursal_codigo = 0,
    sc_cuis = '5B4D47E3',
    sc_cufd = 'FBQWVCSDlBR0E=A4QTM4QzY5Q0U=Qn...',
    sc_cufd_control = '19A8F8C7784BF74',
    sc_cufd_vigente_hasta = '2026-10-13 01:23:33',
    sc_cuis_vigente_hasta = '2027-08-09 20:39:25'
WHERE su_id = 1;

-- Su_id=4: código 1  
UPDATE siat_configuracion SET
    sc_sucursal_codigo = 1,
    sc_cuis = 'F4CCAF31',
    sc_cufd = 'FBQmVCSDlBR0E=A4QTM4QzY5Q0U=Qj...',
    sc_cufd_control = '723AF8C7784BF74',
    sc_cufd_vigente_hasta = '2026-10-13 01:23:33',
    sc_cuis_vigente_hasta = '2027-08-09 20:56:36'
WHERE su_id = 4;

-- Su_id=5: código 2
UPDATE siat_configuracion SET
    sc_sucursal_codigo = 2,
    sc_cuis = 'DE9C2012',
    sc_cufd = 'FBQ2VCSDlBR0E=A4QTM4QzY5Q0U=Qn...',
    sc_cufd_control = '474BF8C7784BF74',
    sc_cufd_vigente_hasta = '2026-10-13 01:23:33',
    sc_cuis_vigente_hasta = '2027-08-09 20:56:37'
WHERE su_id = 5;


-- auto increment agregado
