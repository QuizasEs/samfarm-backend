-- =============================================================
--  PUNTOS DE VENTA CON DATOS SIAT (CUIS / CUFD POR CAJA)
--  samfarm_db
--
--  Objetivo: que cada caja tenga su propio CUIS y CUFD, en vez de
--  que la sucursal entera comparta un solo par de credenciales.
--
--  Contexto:
--    - Es ambiente PILOTO. No hay datos reales que preservar.
--    - No se migran datos a proposito: el CUFD guardado en
--      siat_configuracion ya fue invalidado por el SIN (error 914) y
--      el CUIS pertenece a la sucursal 1, mientras el portal de
--      pruebas exige sucursal 0. Se pediran credenciales nuevas.
--    - siat_configuracion NO se modifica salvo el paso 3, que es
--      opcional. Sus columnas sc_cuis / sc_cufd quedan sin uso y
--      podran borrarse mas adelante, con el sistema ya estable.
-- =============================================================

USE samfarm_db;

-- =============================================================
--  PASO 1  Columnas de SIAT por punto de venta
--
--    pv_codigo_sin     : codigo que asigna el SIN al registrar la
--                        caja. Puede no coincidir con pv_codigo.
--    pv_registrado_sin : 0 = la caja aun no existe en el SIN
--    pv_cuis           : CUIS propio de la caja (dura 365 dias)
--    pv_cufd*          : CUFD diario de la caja (se renueva cada dia)
-- =============================================================
ALTER TABLE `puntos_venta`
  ADD COLUMN `pv_codigo_sin`         INT          NULL COMMENT 'Codigo de punto de venta asignado por el SIN',
  ADD COLUMN `pv_registrado_sin`     TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1=ya registrado en el SIN',
  ADD COLUMN `pv_cuis`               VARCHAR(20)  NULL COMMENT 'CUIS propio de este punto de venta',
  ADD COLUMN `pv_cuis_vigente_hasta` DATETIME     NULL COMMENT 'Fin de vigencia del CUIS',
  ADD COLUMN `pv_cufd`               TEXT         NULL COMMENT 'CUFD vigente de este punto de venta',
  ADD COLUMN `pv_cufd_control`       VARCHAR(200) NULL COMMENT 'Codigo de control del CUFD',
  ADD COLUMN `pv_cufd_vigente_hasta` DATETIME     NULL COMMENT 'Fin de vigencia del CUFD';

-- =============================================================
--  PASO 2  Usuario anclado a una caja
--
--    Se agrega pv_id porque us_punto_venta es un codigo suelto que
--    se repite entre sucursales: no identifica una caja unica.
--    pv_id si es un identificador unico de toda la base.
--
--    ON DELETE SET NULL para que dar de baja una caja no borre ni
--    bloquee al personal que la usaba.
-- =============================================================
ALTER TABLE `usuarios`
  ADD COLUMN `pv_id` BIGINT UNSIGNED NULL COMMENT 'Punto de venta asignado (pv_id)';

UPDATE `usuarios` u
SET u.`pv_id` = (
    SELECT pv.`pv_id` FROM `puntos_venta` pv
    WHERE pv.`su_id` = u.`su_id`
      AND pv.`pv_codigo` = u.`us_punto_venta`
)
WHERE u.`us_punto_venta` IS NOT NULL;

ALTER TABLE `usuarios`
  ADD KEY `ix_us_pv_id` (`pv_id`),
  ADD CONSTRAINT `fk_us_pv` FOREIGN KEY (`pv_id`)
    REFERENCES `puntos_venta` (`pv_id`) ON DELETE SET NULL;

-- =============================================================
--  PASO 3  Opcional: apuntar la sucursal principal al codigo 0
--
--  La especificacion de la Etapa 5 exige codigoSucursal = 0, pero
--  hoy la sucursal 1 esta registrada con sc_sucursal_codigo = 1.
--
--  Ojo: siat_configuracion tiene UNIQUE(su_id), asi que solo existe
--  una fila por sucursal. No se puede agregar una segunda fila para
--  la sucursal 0: habria que cambiar el codigo de la existente.
--
--  Como es PILOTO y no hay datos reales, lo mas simple es reapuntar
--  la fila actual. El CUIS F4CCAF31 quedara desalineado, pero eso no
--  importa: el sistema dejara de leerlo y pediremos un CUIS nuevo
--  para la sucursal 0.
--
--  Si prefieres conservar la fila original, NO ejecutes este paso
--  y en su lugar crea una sucursal nueva desde el modulo de
--  sucursales.
-- =============================================================
-- UPDATE `siat_configuracion`
-- SET `sc_sucursal_codigo` = 0
-- WHERE `su_id` = 1 AND `sc_sucursal_codigo` = 1;

-- =============================================================
--  VERIFICACION
-- =============================================================

SELECT '--- 1. puntos_venta ---' AS seccion;
SELECT `pv_id`, `su_id`, `pv_codigo`, `pv_codigo_sin`, `pv_nombre`,
       `pv_registrado_sin`, `pv_cuis`, `pv_cufd_vigente_hasta`
FROM `puntos_venta`
ORDER BY `su_id`, `pv_codigo`;

SELECT '--- 2. usuarios anclados ---' AS seccion;
SELECT `us_id`, `us_punto_venta` AS codigo_viejo, `pv_id` AS id_caja
FROM `usuarios`
ORDER BY `us_id`
LIMIT 20;

-- Debe devolver 0. Si devuelve filas, hay usuarios cuyo us_punto_venta
-- no coincide con ninguna caja de su sucursal.
SELECT '--- 3. usuarios sin anclar (debe ser 0) ---' AS seccion;
SELECT COUNT(*) AS sin_anclar
FROM `usuarios`
WHERE `us_punto_venta` IS NOT NULL AND `pv_id` IS NULL;

SELECT '--- 4. siat_configuracion ---' AS seccion;
SELECT `su_id`, `sc_sucursal_codigo`, `sc_punto_venta_codigo`,
       `sc_cuis`, `sc_habilitado`
FROM `siat_configuracion`
ORDER BY `su_id`;
