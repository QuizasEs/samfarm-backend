-- =============================================================
--  MIGRACION 02  CUFD anterior por punto de venta
--  samfarm_db
--
--  Por que hace falta:
--    El SIN distingue dos CUFD al registrar un evento significativo:
--
--      cufd       = CUFD vigente al momento de registrar el evento
--      cufdEvento = CUFD que estaba vigente CUANDO OCURRIO la contingencia
--
--    Deben ser distintos. Si coinciden, el SIN responde 984
--    ("CUFD actual/anterior incorrectos"). Y el periodo del evento tiene
--    que caber dentro de la vigencia del cufdEvento, o responde 981
--    ("rango de fechas invalido").
--
--    pv_cufd ya guarda el CUFD vigente. Hace falta conservar el que
--    estaba activo antes de la ultima renovacion, porque ese es
--    precisamente el que hay que reportar como cufdEvento.
--
--  Cuando aplicar:
--    Antes de registrar el primer evento significativo real con la caja
--    0. Es idempotente: se puede volver a ejecutar sin romper nada.
-- =============================================================

USE samfarm_db;

-- -------------------------------------------------------------
--  Idempotencia: solo agrega la columna si todavia no existe.
-- -------------------------------------------------------------
SET @existe = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = 'samfarm_db'
    AND TABLE_NAME   = 'puntos_venta'
    AND COLUMN_NAME  = 'pv_cufd_anterior'
);

SET @sql = IF(
  @existe > 0,
  'SELECT ''pv_cufd_anterior ya existe, no hay nada que hacer'' AS aviso',
  'ALTER TABLE `puntos_venta`
     ADD COLUMN `pv_cufd_anterior` TEXT NULL
     COMMENT ''CUFD vigente antes de la ultima renovacion; se reporta como cufdEvento'''
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -------------------------------------------------------------
--  Sembrado opcional.
--
--  La migracion 01 dejo puntos_venta sin CUIS/CUFD porque las
--  credenciales de siat_configuracion no son transferibles: el CUFD
--  guardado ya fue invalidado por el SIN (error 914) y el CUIS
--  F4CCAF31 pertenece a la sucursal 1, no a la caja 0.
--
--  Si en este momento ya registraste credenciales nuevas en la caja 0
--  y ya rotaste un CUFD, descomenta esto para dejar constancia del
--  CUFD previo. Si no, dejalo comentado: la columna quedara en NULL
--  y registrarEventoSignificativo() avisara que falta el cufdEvento
--  en vez de mandar un 984.
--
--  UPDATE `puntos_venta`
--  SET `pv_cufd_anterior` = 'CUFD_ANTERIOR_AQUI'
--  WHERE `pv_id` = 1;
-- -------------------------------------------------------------

-- =============================================================
--  VERIFICACION
-- =============================================================

SELECT '--- columnas de puntos_venta ---' AS seccion;
SELECT `COLUMN_NAME`, `COLUMN_TYPE`, `IS_NULLABLE`
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'samfarm_db'
  AND TABLE_NAME   = 'puntos_venta'
  AND COLUMN_NAME LIKE 'pv_cufd%'
ORDER BY `ORDINAL_POSITION`;

-- Debe devolver una fila por caja con CUFD vigente. Si pv_cufd_anterior
-- esta en NULL, todavia no se ha rotado el CUFD de esa caja y no se
-- pueden registrar eventos significativos sobre ella.
SELECT '--- estado de credenciales por caja ---' AS seccion;
SELECT `pv_id`, `su_id`, `pv_codigo`, `pv_nombre`,
       `pv_registrado_sin`, `pv_cuis`,
       (`pv_cufd` IS NOT NULL)       AS tiene_cufd,
       `pv_cufd_vigente_hasta`,
       (`pv_cufd_anterior` IS NOT NULL) AS tiene_cufd_anterior
FROM `puntos_venta`
ORDER BY `su_id`, `pv_codigo`;
