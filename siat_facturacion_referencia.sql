-- ============================================================================
--  SIAT v2 - FACTURACION ELECTRONICA EN LINEA (BOLIVIA)
--  ESTRUCTURA DE REFERENCIA (PROPUESTA OPTIMA) - SOLO PARA REVISION
--  ----------------------------------------------------------------------------
--  Archivo : siat_facturacion_referencia.sql
--  Alcance : UNICAMENTE facturacion electronica + conexion con el SIAT.
--  Base    : estructura real de samfarm_db (auditoria 2026-09-25) + WSDL oficial
--            del ambiente PILOTO consultado el mismo dia.
--
--  COMO USAR ESTE ARCHIVO (leer antes de ejecutar nada):
--    * Seccion 2 y 3 : diseno optimo (CREATE)  -> reemplazan las tablas actuales
--    * Seccion 4     : ajustes minimos a tablas existentes (ALTER)
--    * Seccion 7     : MIGRACION real desde la estructura actual (ALTER)
--    * Seccion 8     : lo que hay que cambiar en el codigo PHP
--    * NO ejecutar tal cual sobre samfarm_db. Este archivo es de REFERENCIA.
--
--  CAMPOS VERIFICADOS EN EL WSDL (no inventados):
--    registroEventoSignificativo -> solicitudEventoSignificativo:
--      codigoAmbiente, codigoMotivoEvento, codigoPuntoVenta(opcional),
--      codigoSistema, codigoSucursal, cufd, cufdEvento, cuis, descripcion,
--      fechaHoraInicioEvento, fechaHoraFinEvento, nit
--      respuesta: RespuestaListaEventos { codigoRecepcionEventoSignificativo,
--                 listaCodigos[eventosSignificativosDto], mensajesList, transaccion }
--    recepcionPaqueteFactura -> solicitudRecepcionPaquete =
--      solicitudRecepcionFactura + cafc(opcional) + cantidadFacturas + codigoEvento
--    solicitudRecepcionFactura = solicitudRecepcion + archivo(base64) +
--      fechaEnvio + hashArchivo
--    solicitudRecepcion = codigoAmbiente, codigoDocumentoSector, codigoEmision,
--      codigoModalidad, codigoPuntoVenta(opcional), codigoSistema, codigoSucursal,
--      cufd, cuis, nit, tipoFacturaDocumento
--    FacturacionOperaciones tambien expone: verificarComunicacion (sin parametros),
--      consultaEventoSignificativo, consultaPuntoVenta, cierrePuntoVenta.
--
--  REGLA DE ORO (para eliminar duplicaciones):
--    Cada dato tiene UN SOLO dueno. Lo demas son referencias o copias de
--    auditoria marcadas como tales. No se guarda en la BD lo que ya vive en el
--    codigo, ni se repite un catalogo en dos tablas.
-- ============================================================================

-- ============================================================================
--  SECCION 1. MAPA DE DUENOS (fuente unica de verdad)
-- ============================================================================
--  DATO                          DUENO (fuente unica)                  ANTES ESTABA EN
--  -----------------------------------------------------------------------------------
--  NIT del emisor                configuracion_empresa.ce_nit          + SIAT_NIT (codigo)
--  Razon social                  configuracion_empresa.ce_nombre       -
--  Actividad economica (CAEB)    configuracion_empresa.ce_codigo_actividad  + '477000' fijo
--  Municipio del emisor          configuracion_empresa.ce_municipio    + 'La Paz' fijo
--  Direccion/telefono sucursal   sucursales.su_direccion / su_telefono -
--  Token delegado                siat_configuracion.sc_token           + SIAT_TOKEN (codigo)
--  Ambiente (piloto/produccion)  SIAT_MODO (constante del codigo)      siat_configuracion.sc_modo (muerta)
--  Codigo de sistema             SIAT_COD_SISTEMA (constante)          -
--  CUIS / CUFD / vigencias       siat_configuracion                    -
--  Certificado .p12              SIAT_CERT_P12_PATH (archivo local)    -
--  Catalogos parametrizables     siat_parametricas (llenada por sync)  + codigos fijos en el codigo
--  Actividades + sector          siat_actividades                      -
--  Productos/servicios del SIN   siat_productos                        + medicamento.med_codigo_sin
--  Vinculo medicamento <-> SIN   medicamento.med_codigo_sin / med_unidad_sin (referencia)
--  Leyendas                      siat_leyendas                         -
--  Numero de factura (visible)   factura.fa_numero                     -
--  Numero de factura (SIAT)      factura.fa_numero_siat                + factura_secuencia (sin persistir)
--  CUF de la factura             factura.fa_cuf                        + facturacion_electronica.fe_cuf
--  CUFD usado al emitir          factura.fa_cufd                       -
--  Estado del documento          factura.fa_estado (interno)           + fe_estado_siat (mezclado)
--  Estado del envio al SIN       facturacion_electronica.fe_estado_siat (codigo del SIN)
--  Codigo del evento significativo  siat_eventos.ev_codigo             - (no existia)
--  Metodo de pago de la venta    ventas.ve_metodo_pago                 + codigoMetodoPago=1 fijo
-- ============================================================================

-- ============================================================================
--  SECCION 2. CONFIGURACION Y CATALOGOS DEL SIAT
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 2.1 siat_configuracion : credenciales y datos de conexion POR SUCURSAL
--     (una fila por sucursal; la estructura actual ya va bien encaminada)
-- ----------------------------------------------------------------------------
CREATE TABLE siat_configuracion (
  sc_id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  su_id                  BIGINT UNSIGNED NOT NULL,
  -- credenciales del SIN (antes vivian hardcodeadas en config/siat.php)
  sc_token               TEXT            NULL COMMENT 'Token Delegado (apikey)',
  sc_token_aplicacion    VARCHAR(500)    NULL COMMENT 'TokenApi alterno / apiKey de aplicacion',
  -- codigos de habilitacion
  sc_cuis                VARCHAR(200)    NULL COMMENT 'CUIS vigente',
  sc_cuis_vigente_hasta  DATETIME        NULL COMMENT 'fechaVigencia del CUIS (la devuelve el SIN)',
  sc_cufd                TEXT            NULL COMMENT 'CUFD vigente (cambia cada dia)',
  sc_cufd_control        VARCHAR(200)    NULL COMMENT 'codigoControl del CUFD (se usa en el CUF)',
  sc_cufd_vigente_hasta  DATETIME        NULL COMMENT 'fechaVigencia del CUFD',
  -- ubicacion
  sc_sucursal_codigo     INT             NOT NULL DEFAULT 0 COMMENT 'codigoSucursal del SIN (0 = casa matriz)',
  sc_punto_venta_codigo  INT             NOT NULL DEFAULT 0 COMMENT 'codigoPuntoVenta del SIN (0 = sin punto de venta)',
  -- estado
  sc_habilitado          TINYINT(1)      NOT NULL DEFAULT 1 COMMENT '1 = emitir al SIN para esta sucursal',
  sc_fecha_siat          DATETIME        NULL COMMENT 'ultima fecha/hora oficial devuelta por el SIN',
  sc_creado_en           DATETIME        NOT NULL DEFAULT current_timestamp(),
  sc_actualizado_en      DATETIME        NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (sc_id),
  UNIQUE KEY ux_sc_su (su_id),
  CONSTRAINT fk_sc_sucursal FOREIGN KEY (su_id) REFERENCES sucursales (su_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Credenciales SIAT por sucursal';

-- ----------------------------------------------------------------------------
-- 2.2 siat_parametricas : TODOS los catalogos parametricos del SIN en UNA tabla
--     Se llenan con los servicios sincronizarParametrica* del SIN
--     (RespuestaListaParametricas -> listaCodigos[{codigoClasificador, descripcion}])
--     Reemplaza a la tabla suelta siat_unidades_medida (mismo concepto = un dueno).
--     Tipos: UNIDAD_MEDIDA, TIPO_MONEDA, METODO_PAGO, TIPO_DOCUMENTO_IDENTIDAD,
--            TIPO_DOCUMENTO_SECTOR, TIPO_EMISION, TIPO_FACTURA_DOCUMENTO,
--            MOTIVO_ANULACION, MOTIVO_EVENTO, PAIS_ORIGEN, TIPO_PUNTO_VENTA, ...
-- ----------------------------------------------------------------------------
CREATE TABLE siat_parametricas (
  par_id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  par_tipo            VARCHAR(40)     NOT NULL COMMENT 'familia de la parametrica',
  par_codigo          VARCHAR(10)     NOT NULL COMMENT 'codigoClasificador del SIN',
  par_descripcion     VARCHAR(300)    NOT NULL,
  par_vigente         TINYINT(1)      NOT NULL DEFAULT 1,
  par_sincronizado_en DATETIME        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (par_id),
  UNIQUE KEY ux_par_tipo_codigo (par_tipo, par_codigo),
  KEY ix_par_tipo (par_tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catalogos parametricos del SIN (todas las familias)';

-- ----------------------------------------------------------------------------
-- 2.3 siat_actividades : CAEB + documento sector (servicio
--     sincronizarListaActividadesDocumentoSector)
--     OJO: la tabla actual no tenia PK ni AUTO_INCREMENT, y el codigo guardaba
--          el codigo de sector dentro del campo descripcion.
-- ----------------------------------------------------------------------------
CREATE TABLE siat_actividades (
  act_id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  act_codigo          VARCHAR(20)     NOT NULL COMMENT 'codigoActividad (CAEB)',
  act_descripcion     VARCHAR(500)    NOT NULL COMMENT 'descripcion de la actividad',
  act_doc_sector      INT             NOT NULL DEFAULT 1 COMMENT 'codigoDocumentoSector',
  act_tipo_sector     VARCHAR(80)     NULL COMMENT 'tipoDocumentoSector (texto del SIN)',
  act_estado          TINYINT(1)      NOT NULL DEFAULT 1,
  act_sincronizado_en DATETIME        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (act_id),
  UNIQUE KEY ux_act_codigo_sector (act_codigo, act_doc_sector),
  KEY ix_act_codigo (act_codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Actividades economicas (CAEB) y documento sector';

-- ----------------------------------------------------------------------------
-- 2.4 siat_productos : productos/servicios del SIN
--     (servicio sincronizarListaProductosServicios)
--     Este es el catalogo MAESTRO: medicamento.med_codigo_sin solo lo referencia.
-- ----------------------------------------------------------------------------
CREATE TABLE siat_productos (
  pro_id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  pro_codigo          VARCHAR(50)     NOT NULL COMMENT 'codigoProducto del SIN',
  pro_descripcion     VARCHAR(500)    NOT NULL COMMENT 'descripcionProducto',
  pro_actividad       VARCHAR(20)     NULL COMMENT 'codigoActividad a la que pertenece',
  pro_codigo_unidad   VARCHAR(10)     NOT NULL DEFAULT '1' COMMENT 'unidad de medida sugerida',
  pro_nandina         VARCHAR(60)     NULL COMMENT 'codigo NANDINA si el SIN lo devuelve',
  pro_tipo            VARCHAR(20)     NOT NULL DEFAULT 'bien' COMMENT 'bien | servicio',
  pro_sincronizado_en DATETIME        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (pro_id),
  UNIQUE KEY ux_pro_codigo (pro_codigo),
  KEY ix_pro_actividad (pro_actividad),
  KEY ix_pro_descripcion (pro_descripcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catalogo de productos/servicios del SIN';

-- ----------------------------------------------------------------------------
-- 2.5 siat_leyendas : leyendas obligatorias de factura por actividad
--     (servicio sincronizarListaLeyendasFactura -> descripcionLeyenda)
--     OJO: la tabla actual no tenia PK/AUTO_INCREMENT y el codigo leia una
--          columna inexistente (descripcion). Aqui el nombre es explicito.
-- ----------------------------------------------------------------------------
CREATE TABLE siat_leyendas (
  ley_id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ley_codigo_actividad VARCHAR(20)     NULL COMMENT 'codigoActividad (CAEB)',
  ley_texto            TEXT            NOT NULL COMMENT 'texto de la leyenda',
  ley_tipo             VARCHAR(50)     NOT NULL DEFAULT 'LEYENDA',
  ley_vigente          TINYINT(1)      NOT NULL DEFAULT 1,
  ley_sincronizado_en  DATETIME        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (ley_id),
  KEY ix_ley_actividad (ley_codigo_actividad, ley_vigente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Leyendas obligatorias de factura (Ley 453 y otras)';

-- ============================================================================
--  SECCION 3. FACTURACION ELECTRONICA
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 3.1 factura : DOCUMENTO FISCAL. Guarda (en el momento de emitir) todos los
--     datos obligatorios de la cabecera del XML, para que la factura sea
--     reproducible y auditable aunque despues cambien las configuraciones.
--     Dueno de: CUF, CUFD usado, numero SIAT, leyenda usada, metodo de pago.
-- ----------------------------------------------------------------------------
CREATE TABLE factura (
  fa_id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ve_id                  BIGINT UNSIGNED NOT NULL,
  cl_id                  BIGINT UNSIGNED NULL,
  us_id                  BIGINT UNSIGNED NOT NULL,
  su_id                  BIGINT UNSIGNED NOT NULL,
  -- identificacion
  fa_numero              VARCHAR(100)    NOT NULL COMMENT 'numero visible / interno (ej: F-1-20260901184710-678)',
  fa_numero_siat         INT             NULL COMMENT 'numeroFactura NUMERICO correlativo (obligatorio en XML y CUF)',
  fa_fecha_emision       DATETIME        NOT NULL DEFAULT current_timestamp(),
  -- montos
  fa_monto_total         DECIMAL(14,2)   NOT NULL,
  fa_monto_sujeto_iva    DECIMAL(14,2)   NOT NULL DEFAULT 0.00 COMMENT 'montoTotalSujetoIva',
  fa_descuento_adicional DECIMAL(14,2)   NOT NULL DEFAULT 0.00 COMMENT 'descuentoAdicional',
  -- pago y moneda (codigos del SIN)
  fa_codigo_metodo_pago  INT             NOT NULL DEFAULT 1 COMMENT 'codigoMetodoPago (1 = efectivo)',
  fa_numero_tarjeta      VARCHAR(30)     NULL COMMENT 'numeroTarjeta si aplica',
  fa_codigo_moneda       INT             NOT NULL DEFAULT 1 COMMENT 'codigoMoneda (1 = boliviano)',
  fa_tipo_cambio         DECIMAL(12,5)   NOT NULL DEFAULT 1.00000 COMMENT 'tipoCambio',
  -- clasificacion fiscal
  fa_doc_sector          INT             NOT NULL DEFAULT 1 COMMENT 'codigoDocumentoSector (1 = factura compra venta)',
  fa_codigo_emision      INT             NOT NULL DEFAULT 1 COMMENT 'codigoEmision (1 = en linea, 2 = contingencia)',
  fa_tipo_factura        INT             NOT NULL DEFAULT 1 COMMENT 'tipoFacturaDocumento (1 = con derecho a credito fiscal)',
  fa_codigo_excepcion    INT             NULL COMMENT 'codigoExcepcion (solo casos especiales)',
  fa_cafc                VARCHAR(100)    NULL COMMENT 'CAFC (solo preimpreso)',
  -- datos fiscales del envio (duenos)
  fa_cuf                 VARCHAR(100)    NULL COMMENT 'CUF generado',
  fa_cufd                VARCHAR(255)    NULL COMMENT 'CUFD vigente usado en la emision',
  fa_leyenda             TEXT            NULL COMMENT 'leyenda usada (copia de auditoria)',
  -- estado interno del documento
  fa_estado              TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '0=pendiente SIN, 1=validada, 2=anulada, 3=rechazada',
  fa_creado_en           DATETIME        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (fa_id),
  UNIQUE KEY ux_fa_ve (ve_id),
  UNIQUE KEY ux_fa_su_anio_numero (su_id, fa_numero_siat),
  UNIQUE KEY ux_fa_cuf (fa_cuf),
  KEY ix_fa_cliente (cl_id),
  KEY ix_fa_usuario (us_id),
  KEY ix_fa_estado (fa_estado),
  KEY ix_fa_fecha (fa_fecha_emision),
  CONSTRAINT fk_fa_venta    FOREIGN KEY (ve_id) REFERENCES ventas (ve_id)    ON DELETE CASCADE,
  CONSTRAINT fk_fa_cliente  FOREIGN KEY (cl_id) REFERENCES clientes (cl_id)  ON DELETE SET NULL,
  CONSTRAINT fk_fa_usuario  FOREIGN KEY (us_id) REFERENCES usuarios (us_id)  ON DELETE CASCADE,
  CONSTRAINT fk_fa_sucursal FOREIGN KEY (su_id) REFERENCES sucursales (su_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Factura (documento fiscal con datos obligatorios del SIAT)';

-- ----------------------------------------------------------------------------
-- 3.2 factura_secuencia : correlativo del numeroFactura del SIN.
--     El SIN numera por sucursal + punto de venta + gestion, por eso el punto
--     de venta entra en la clave primaria (hoy no estaba).
-- ----------------------------------------------------------------------------
CREATE TABLE factura_secuencia (
  su_id            BIGINT UNSIGNED NOT NULL,
  fs_punto_venta   INT             NOT NULL DEFAULT 0,
  fs_anio          INT             NOT NULL,
  fs_ultimo_nro    INT             NOT NULL DEFAULT 0,
  PRIMARY KEY (su_id, fs_punto_venta, fs_anio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Correlativo de numeros de factura por sucursal/punto de venta/gestion';

-- ----------------------------------------------------------------------------
-- 3.3 siat_eventos : EVENTOS SIGNIFICATIVOS (Paso/Etapa 5).
--     Guarda la respuesta de registroEventoSignificativo. El campo
--     ev_codigo (codigoRecepcionEventoSignificativo) es OBLIGATORIO despues:
--     se envia como "codigoEvento" en recepcionPaqueteFactura.
--     Los nombres de campos siguen el WSDL verificado.
-- ----------------------------------------------------------------------------
CREATE TABLE siat_eventos (
  ev_id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  su_id                BIGINT UNSIGNED NOT NULL,
  ev_codigo            BIGINT          NOT NULL COMMENT 'codigoRecepcionEventoSignificativo (obligatorio en el paquete)',
  ev_codigo_evento     BIGINT          NULL COMMENT 'codigoEvento de listaCodigos (referencia del SIN)',
  ev_motivo            INT             NOT NULL COMMENT 'codigoMotivoEvento (1..7)',
  ev_descripcion       VARCHAR(500)    NOT NULL COMMENT 'descripcion enviada',
  ev_fecha_hora_inicio DATETIME        NOT NULL COMMENT 'fechaHoraInicioEvento (UTC)',
  ev_fecha_hora_fin    DATETIME        NOT NULL COMMENT 'fechaHoraFinEvento (UTC)',
  ev_cufd              VARCHAR(255)    NOT NULL COMMENT 'cufdEvento: CUFD vigente al inicio del evento',
  ev_cuis              VARCHAR(200)    NULL COMMENT 'CUIS usado',
  ev_transaccion       TINYINT(1)      NOT NULL DEFAULT 1 COMMENT 'transaccion devuelta por el SIN',
  ev_mensajes          TEXT            NULL COMMENT 'mensajesList del SIN en JSON',
  ev_estado            VARCHAR(30)     NOT NULL DEFAULT 'REGISTRADO' COMMENT 'REGISTRADO|USADO|ANULADO',
  ev_usado_en_envio    TINYINT(1)      NOT NULL DEFAULT 0,
  ev_creado_en         DATETIME        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (ev_id),
  UNIQUE KEY ux_ev_codigo (ev_codigo),
  KEY ix_ev_su_estado (su_id, ev_estado),
  CONSTRAINT fk_ev_sucursal FOREIGN KEY (su_id) REFERENCES sucursales (su_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Eventos significativos (contingencia) - Etapa 5';

-- ----------------------------------------------------------------------------
-- 3.4 siat_paquetes : paquetes de contingencia enviados.
--     recepcionPaqueteFactura exige: archivo(unico, base64), cantidadFacturas,
--     codigoEvento y hashArchivo. Aqui queda el registro del envio.
-- ----------------------------------------------------------------------------
CREATE TABLE siat_paquetes (
  pa_id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  su_id               BIGINT UNSIGNED NOT NULL,
  ev_id               BIGINT UNSIGNED NOT NULL COMMENT 'evento significativo usado como codigoEvento',
  pa_cantidad_facturas INT            NOT NULL DEFAULT 0,
  pa_codigo_recepcion VARCHAR(100)    NULL COMMENT 'codigoRecepcion del paquete',
  pa_hash_archivo     VARCHAR(128)    NULL COMMENT 'sha256 del archivo enviado',
  pa_estado           VARCHAR(30)     NOT NULL DEFAULT 'ENVIADO' COMMENT 'ENVIADO|VALIDADO|RECHAZADO',
  pa_mensajes         TEXT            NULL COMMENT 'mensajesList del SIN en JSON',
  pa_fecha_envio      DATETIME        NULL,
  pa_creado_en        DATETIME        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (pa_id),
  KEY ix_pa_evento (ev_id),
  CONSTRAINT fk_pa_evento   FOREIGN KEY (ev_id)   REFERENCES siat_eventos (ev_id) ON DELETE CASCADE,
  CONSTRAINT fk_pa_sucursal FOREIGN KEY (su_id)   REFERENCES sucursales (su_id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Paquetes de facturas de contingencia enviados al SIN';

-- ----------------------------------------------------------------------------
-- 3.5 facturacion_electronica : BITACORA de cada envio al SIN.
--     Una fila por factura (UNIQUE fa_id). Es el "log" del tramite fiscal:
--     NO guarda el CUF (ese vive en factura.fa_cuf), solo lo que ocurre al enviar.
--     Separa el estado del SIN (numerico) del estado interno (texto) porque hoy
--     estaban mezclados en una sola columna.
-- ----------------------------------------------------------------------------
CREATE TABLE facturacion_electronica (
  fe_id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  fa_id               BIGINT UNSIGNED NOT NULL,
  -- que se envio
  fe_codigo_emision   TINYINT         NOT NULL DEFAULT 1 COMMENT 'codigoEmision: 1 en linea, 2 contingencia',
  fe_modalidad        TINYINT         NOT NULL DEFAULT 2 COMMENT 'codigoModalidad usado en el envio',
  fe_doc_sector       TINYINT         NOT NULL DEFAULT 1 COMMENT 'codigoDocumentoSector usado',
  fe_numero_factura   INT             NULL COMMENT 'numeroFactura enviado (copia del envio)',
  fe_tipo_factura     TINYINT         NOT NULL DEFAULT 1 COMMENT 'tipoFacturaDocumento',
  fe_hash_archivo     VARCHAR(128)    NULL COMMENT 'sha256 del GZIP enviado (hashArchivo)',
  fe_fecha_envio      DATETIME        NULL COMMENT 'fechaEnvio enviada al SIN',
  -- que respondio el SIN
  fe_codigo_recepcion VARCHAR(100)    NULL COMMENT 'codigoRecepcion (ticket) devuelto',
  fe_estado_siat      INT             NULL COMMENT 'codigoEstado del SIN (908 = validada)',
  fe_mensajes         TEXT            NULL COMMENT 'mensajesList del SIN en JSON',
  fe_qr               TEXT            NULL COMMENT 'contenido del QR generado (URL)',
  -- estado interno del tramite (antes se mezclaba en fe_estado_siat)
  fe_estado_local     VARCHAR(30)     NOT NULL DEFAULT 'PENDIENTE'
                      COMMENT 'PENDIENTE|ENVIADO|PENDIENTE_VALIDACION|VALIDADA|RECHAZADA|CONTINGENCIA|ANULADA',
  -- contingencia
  fe_paquete_id       BIGINT UNSIGNED NULL COMMENT 'paquete de contingencia donde viajo',
  -- control
  fe_intentos         TINYINT UNSIGNED NOT NULL DEFAULT 1,
  fe_ultimo_intento   DATETIME        NULL,
  fe_anulado_motivo   INT             NULL COMMENT 'codigoMotivo de anulacion',
  fe_anulado_en       DATETIME        NULL,
  fe_payload          LONGTEXT        NULL COMMENT 'XML firmado del ultimo envio (auditoria)',
  fe_creado_en        DATETIME        NOT NULL DEFAULT current_timestamp(),
  fe_actualizado_en   DATETIME        NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (fe_id),
  UNIQUE KEY ux_fe_fa (fa_id),
  KEY ix_fe_estado_local (fe_estado_local),
  KEY ix_fe_estado_siat (fe_estado_siat),
  KEY ix_fe_paquete (fe_paquete_id),
  CONSTRAINT fk_fe_factura FOREIGN KEY (fa_id) REFERENCES factura (fa_id) ON DELETE CASCADE,
  CONSTRAINT fk_fe_paquete FOREIGN KEY (fe_paquete_id) REFERENCES siat_paquetes (pa_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Bitacora de envios al SIAT (una fila por factura)';

-- ============================================================================
--  SECCION 4. AJUSTES MINIMOS A TABLAS RELACIONADAS (no son tablas SIAT, pero
--             la factura electronica las necesita). Solo agregados/limpieza.
--  Son los UNICOS cambios sobre estas tablas: la seccion 7.1 no los repite.
-- ============================================================================

-- 4.1 configuracion_empresa : datos del EMISOR que exige el XML
--     (municipio y actividad economica estaban fijos en el codigo PHP)
ALTER TABLE configuracion_empresa
  ADD COLUMN ce_municipio        VARCHAR(120) NULL COMMENT 'municipio del emisor (campo municipio del XML)' AFTER ce_direccion,
  ADD COLUMN ce_codigo_actividad VARCHAR(20)  NULL COMMENT 'CAEB del emisor (campo actividadEconomica del XML)' AFTER ce_municipio;

-- 4.2 clientes : tipo de documento de identidad como CODIGO del SIN
--     (hoy el codigo lo adivina contando digitos del carnet)
ALTER TABLE clientes
  ADD COLUMN cl_tipo_documento INT NULL COMMENT 'codigoTipoDocumentoIdentidad del SIN' AFTER cl_carnet,
  ADD KEY ix_cl_carnet (cl_carnet);

-- 4.3 medicamento : vinculos al catalogo del SIN (estas columnas ya existen,
--     solo se documentan y se indexan para el mapeo)
--     med_codigo_sin -> siat_productos.pro_codigo (codigoProductoSin)
--     med_unidad_sin -> siat_parametricas (tipo = UNIDAD_MEDIDA)
ALTER TABLE medicamento
  ADD KEY ix_med_codigo_sin (med_codigo_sin);

-- 4.4 ventas : metodo de pago es la fuente del codigoMetodoPago del XML.
--     Convencion de valores (texto): efectivo | tarjeta_debito | tarjeta_credito
--     | transferencia | cheque | billetera_movil   -> se traduce a codigo del SIN
--     via siat_parametricas (tipo = METODO_PAGO).
--     ve_numero_control no se usa en ninguna parte del sistema.
ALTER TABLE ventas
  DROP COLUMN ve_numero_control;

-- 4.5 sucursales : datos que el XML debe usar para la sucursal emisora
--     (su_direccion y su_municipio ya existen; se usan en lugar de los del
--     emisor matriz y del texto 'La Paz' fijo)
--     NOTA: no requiere cambios estructurales.

-- ============================================================================
--  SECCION 5. VISTAS DE APOYO (evitan repetir JOINs en el codigo PHP)
--  OJO: estas vistas usan columnas del DISENO OPTIMO (secciones 2 y 3).
--       En la migracion MINIMA (7.1) todavia no existen; se crean recien
--       cuando se adopte el diseno completo (7.2).
-- ============================================================================

-- 5.1 Todos los datos que necesita la cabecera del XML de una factura,
--     tomando cada dato de su dueno (empresa, sucursal, configuracion SIAT).
CREATE OR REPLACE VIEW v_siat_factura_datos_xml AS
SELECT
  v.ve_id,
  f.fa_id,
  f.fa_numero,
  f.fa_numero_siat,
  f.fa_fecha_emision                       AS fecha_emision,
  f.fa_monto_total,
  f.fa_monto_sujeto_iva,
  f.fa_descuento_adicional,
  f.fa_codigo_metodo_pago,
  f.fa_numero_tarjeta,
  f.fa_codigo_moneda,
  f.fa_tipo_cambio,
  f.fa_doc_sector,
  f.fa_codigo_emision,
  f.fa_tipo_factura,
  f.fa_cuf,
  f.fa_cufd,
  f.fa_leyenda,
  ce.ce_nit,
  ce.ce_nombre                             AS razon_social,
  ce.ce_telefono,
  ce.ce_codigo_actividad,
  COALESCE(NULLIF(su.su_municipio, ''), ce.ce_municipio) AS municipio,
  COALESCE(NULLIF(su.su_direccion, ''), ce.ce_direccion) AS direccion,
  sc.sc_cuis,
  sc.sc_sucursal_codigo                    AS codigo_sucursal,
  sc.sc_punto_venta_codigo                 AS codigo_punto_venta,
  c.cl_id,
  c.cl_carnet                              AS numero_documento,
  c.cl_tipo_documento,
  c.cl_nombres,
  c.cl_apellido_paterno,
  u.us_username                            AS usuario,
  v.su_id
FROM factura f
JOIN ventas v                ON v.ve_id = f.ve_id
JOIN configuracion_empresa ce ON ce.ce_id = 1
JOIN siat_configuracion sc   ON sc.su_id = v.su_id
JOIN sucursales su           ON su.su_id = v.su_id
LEFT JOIN clientes c         ON c.cl_id = v.cl_id
JOIN usuarios u               ON u.us_id = v.us_id;

-- 5.2 Facturas ya enviadas que esperan la validacion del SIN (para el cron).
CREATE OR REPLACE VIEW v_siat_pendientes_validacion AS
SELECT fe.fe_id, fe.fa_id, f.fa_cuf, f.fa_numero_siat, f.fa_numero,
       v.su_id, fe.fe_codigo_recepcion, fe.fe_estado_local, fe.fe_intentos
FROM facturacion_electronica fe
JOIN factura f ON f.fa_id = fe.fa_id
JOIN ventas  v ON v.ve_id = f.ve_id
WHERE fe.fe_estado_local IN ('ENVIADO', 'PENDIENTE_VALIDACION');

-- 5.3 Facturas emitidas sin internet que aun no fueron al SIN (paquete).
CREATE OR REPLACE VIEW v_siat_contingencia_pendiente AS
SELECT fe.fe_id, fe.fa_id, f.fa_cuf, f.fa_numero_siat, f.fa_codigo_emision,
       v.su_id
FROM facturacion_electronica fe
JOIN factura f ON f.fa_id = fe.fa_id
JOIN ventas  v ON v.ve_id = f.ve_id
WHERE fe.fe_estado_local = 'CONTINGENCIA';

-- ============================================================================
--  SECCION 6. DATOS INICIALES (solo referencia)
-- ============================================================================

-- 6.0 ORDEN DE EJECUCION IMPORTANTE
--     Si se usa la migracion MINIMA (7.1), esta seccion 6 debe ejecutarse
--     DESPUES de la 7.1: la 7.1 empieza limpiando los catalogos (DELETE), por lo
--     que si se corre antes, borra la leyenda de respaldo recien insertada.
--     En el diseno optimo (secciones 2 y 3) no hay DELETE, asi que el orden da igual.

-- 6.1 Leyenda de respaldo. En produccion la leyenda correcta la trae la
--     sincronizacion del SIN (sincronizarListaLeyendasFactura). Esta fila es
--     solo para no emitir con leyenda vacia mientras se sincroniza.
--     OJO: este INSERT usa la columna ley_texto del DISENO OPTIMO. Si todavia
--     se esta en la migracion MINIMA (7.1), usar la columna actual "leyenda":
--     INSERT INTO siat_leyendas (codigo_actividad, leyenda) VALUES (NULL, '...');
INSERT INTO siat_leyendas (ley_codigo_actividad, ley_texto, ley_tipo)
VALUES (NULL,
        'Ley N 453: Tienes derecho a recibir informacion sobre las caracteristicas y contenidos de los servicios que utilices.',
        'LEYENDA');

-- 6.2 Datos del emisor (hoy ce_direccion = 'N/A' y no hay municipio/actividad).
--     Reemplazar por los datos reales del NIT 6006007013.
UPDATE configuracion_empresa
SET ce_direccion         = 'COMPLETAR DIRECCION REAL',
    ce_municipio         = 'COMPLETAR MUNICIPIO',
    ce_codigo_actividad  = 'COMPLETAR CAEB'         -- actividad economica del SIN
WHERE ce_id = 1;

-- 6.3 Credenciales SIAT por sucursal (el token deja de estar en el codigo).
--     Los valores reales se obtienen del Portal SIAT / de los servicios CUIS y CUFD.
--     OJO: sc_habilitado se crea en la seccion 7.1 paso (7). Ejecutar ese ALTER
--     ANTES de este UPDATE (o quitarlo y dejar solo sc_token).
UPDATE siat_configuracion
SET sc_token     = 'COMPLETAR TOKEN DELEGADO'
WHERE su_id = 1;

-- 6.4 NO INVENTAR CODIGOS.
--     Los catalogos (unidad de medida, moneda, metodo de pago, tipo de documento
--     de identidad, documento sector, tipo de emision, motivos de anulacion,
--     motivos de evento) se cargan con los servicios sincronizarParametrica* del
--     SIN y quedan en siat_parametricas. Los productos y actividades, con
--     sincronizarListaProductosServicios y sincronizarListaActividadesDocumentoSector.

-- ============================================================================
--  SECCION 7. MIGRACION
--   7.1 MINIMA  -> se puede aplicar HOY sobre samfarm_db (arregla lo que rompe)
--   7.2 COMPLETA-> solo cuando se adopte el diseno optimo (implica tocar el codigo)
--  Antes de cualquier cosa: RESPALDO completo de samfarm_db.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 7.1 MIGRACION MINIMA (segura, sin perder datos)
--     CAMINO ALTERNATIVO: aplicar esto SOLO si NO se adopta el diseno optimo
--     de las secciones 2 y 3 (que ya incluye todo lo de aqui). Las secciones
--     2-3 y la 7.1 son caminos excluyentes; la 7.1 mantiene el codigo actual
--     funcionando sin renombrar columnas.
--     Ademas incluye los ajustes de la seccion 4 (ver notas 6 y 8).
-- ----------------------------------------------------------------------------

-- (1) Tipo de emision: DECIDIDO que si se agrega
ALTER TABLE facturacion_electronica
  ADD COLUMN fe_tipo_emision TINYINT NOT NULL DEFAULT 1
  COMMENT '1=en linea, 2=contingencia' AFTER fe_payload;

-- (2) Numero de factura numerico + CUFD usado en la emision
ALTER TABLE factura
  ADD COLUMN fa_numero_siat INT NULL COMMENT 'numeroFactura numerico (XML/CUF)' AFTER fa_numero,
  ADD COLUMN fa_cufd       VARCHAR(255) NULL COMMENT 'CUFD usado al emitir' AFTER fa_cuf;

-- (3) Evento significativo (Etapa 5). DDL completo en la seccion 3.3.
-- CREATE TABLE siat_eventos ( ... );

-- (4) Catalogos: PK + AUTO_INCREMENT + columnas que faltan
--     (hoy estan vacias: 0 filas, por eso se pueden limpiar sin riesgo)
DELETE FROM siat_actividades;
DELETE FROM siat_leyendas;
DELETE FROM siat_productos;

ALTER TABLE siat_actividades
  MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY ux_act_codigo_sector (codigo, tipo_actividad);

ALTER TABLE siat_leyendas
  MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY (id);

ALTER TABLE siat_productos
  MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY (id),
  ADD COLUMN codigo_actividad VARCHAR(20) NULL,
  ADD COLUMN nandina VARCHAR(60) NULL;

-- (5) Unicidad: evita duplicados silenciosos
ALTER TABLE factura                 ADD UNIQUE KEY ux_fa_ve (ve_id);
ALTER TABLE facturacion_electronica ADD UNIQUE KEY ux_fe_fa (fa_id);

-- (6) Datos obligatorios del emisor  -> ver seccion 4.1 (no repetir aqui)
--     ALTER + UPDATE de ce_municipio / ce_codigo_actividad / ce_direccion

-- (7) Configuracion SIAT: vigencia del CUIS + interruptor por sucursal
ALTER TABLE siat_configuracion
  ADD COLUMN sc_cuis_vigente_hasta DATETIME NULL AFTER sc_cuis,
  ADD COLUMN sc_habilitado TINYINT(1) NOT NULL DEFAULT 1;

-- (8) Limpieza de columnas muertas -> ver seccion 4.4 (ventas.ve_numero_control)
--     y seccion 4.2 (clientes.cl_tipo_documento, por si se usa ahora)

-- (9) Actualizar el volcado del proyecto (samfarm_db.sql) con TODO lo anterior
--     y con el DDL de siat_eventos, para que una instalacion nueva no quede
--     desalineada (asi no hay migraciones pendientes mas adelante).

-- ----------------------------------------------------------------------------
-- 7.2 MIGRACION COMPLETA (adoptar el diseno optimo de las secciones 2 y 3)
--     Requiere cambiar el codigo PHP (seccion 8). Orden sugerido:
--       1. Respaldo de samfarm_db
--       2. Crear las tablas nuevas (parametricas, eventos, paquetes)
--       3. Renombrar/renovar factura y facturacion_electronica
--       4. Ajustar el codigo PHP y PROBAR en PILOTO
--       5. Recien despues eliminar tablas/columnas viejas
--     El renombrado de columnas de los catalogos (codigo -> act_codigo, etc.)
--     va junto con el cambio de codigo; hasta entonces se mantiene la migracion
--     MINIMA (7.1) que es compatible con el codigo actual.
-- ----------------------------------------------------------------------------

-- ============================================================================
--  SECCION 8. IMPACTO EN EL CODIGO PHP (para que la estructura y el codigo
--             trabajen en un solo rumbo)
-- ============================================================================
--  8.1 LEER EL NIT DESDE LA BD
--      models/siatModel.php usa SIAT_NIT (constante) en validarRecepcionFactura,
--      anularFactura y generarQR. Debe usar configuracion_empresa.ce_nit.
--
--  8.2 TOKEN / CREDENCIALES
--      clienteSOAP() toma SIAT_TOKEN del archivo de configuracion. Pasa a leer
--      siat_configuracion.sc_token de la sucursal (y el modo desde SIAT_MODO).
--
--  8.3 LEYENDA
--      obtener_leyenda_siat_model() hace "SELECT descripcion FROM siat_leyendas"
--      y esa columna no existe -> error SQL 1054. Con el diseno nuevo es
--      ley_texto (o, en la migracion minima, la columna actual "leyenda").
--
--  8.4 XML DE LA FACTURA (models/siatModel.php -> generarXML)
--      * numeroFactura : usar factura.fa_numero_siat (numerico)
--      * cufd          : usar factura.fa_cufd (el CUFD con el que se emitio)
--      * leyenda       : usar factura.fa_leyenda / siat_leyendas
--      * municipio     : usar sucursales.su_municipio o configuracion_empresa.ce_municipio
--      * actividadEconomica : usar configuracion_empresa.ce_codigo_actividad
--      * codigoMetodoPago   : traducir ventas.ve_metodo_pago con siat_parametricas
--      * unidadMedida       : usar medicamento.med_unidad_sin (hoy va fijo 1)
--      * codigoProductoSin  : usar medicamento.med_codigo_sin (hoy cae en 99900)
--      * usuario            : usar usuarios.us_username (hoy usa us_nombres)
--      * agregar los nodos obligatorios que faltan: codigoDocumentoSector,
--        numeroTarjeta, montoGiftCard, descuentoAdicional, codigoExcepcion, cafc,
--        numeroSerie, numeroImei
--      * dia/hora de emision SIN zona horaria (formato yyyy-MM-ddTHH:mm:ss.SSS)
--
--  8.5 FIRMA DIGITAL (firmarXML)
--      * addReference() con transforms = null genera SOLO el transform de
--        canonicalizacion: falta el transform enveloped-signature y el digest
--        queda calculado antes de que exista SignatureValue -> la firma NO valida.
--      * con force_uri = true sobre un DOMDocument el URI queda "" (vacio), por
--        lo que el atributo Id del nodo raiz no se usa (y no esta en el XSD).
--      * agregar autoverificacion (XMLSecurityDSig::verify) antes de enviar.
--
--  8.6 CONTINGENCIA (Etapa 5 y 6)
--      * los nombres reales de la solicitud son fechaHoraInicioEvento y
--        fechaHoraFinEvento (los scripts usan fechaInicioEvento/fechaFinEvento),
--        y NO existe el campo codigoEvento en la solicitud (si en la respuesta).
--      * la respuesta es RespuestaListaEventos (no RespuestaEventoSignificativo).
--      * guardar codigoRecepcionEventoSignificativo en siat_eventos.ev_codigo.
--      * recepcionPaqueteFactura pide: archivo (uno solo, base64),
--        cantidadFacturas, codigoEvento (= ev_codigo), hashArchivo, codigoEmision.
--      * CUF de contingencia con tipoEmision = 2.
--
--  8.7 CATALOGOS
--      * sincronizarParametrica* devuelve listaCodigos con codigoClasificador y
--        descripcion: hoy sincronizarUnidadesMedida lee listaUnidadesMedida.
--      * guardarActividades guarda tipoDocumentoSector dentro de descripcion.
--      * guardarProductos hace ALTER TABLE en tiempo de ejecucion (ya no hara
--        falta: la columna nandina queda creada aqui).

-- ============================================================================
--  SECCION 9. PENDIENTES A CONFIRMAR CON EL SIN (no inventar valores)
-- ============================================================================
--  * Lista oficial de codigos: unidad de medida, moneda, metodo de pago,
--    tipo de documento de identidad, documento sector, tipo de emision,
--    tipos de factura, motivos de anulacion y motivos de evento.
--    -> se obtienen con los servicios sincronizarParametrica* (siat_parametricas).
--  * XSD oficiales (facturaComputarizadaCompraVenta.xsd /
--    facturaElectronicaCompraVenta.xsd) para validar el XML antes de firmar.
--    -> NO existen en el repositorio (verificado: 0 archivos .xsd).
--  * Modalidad real contratada (codigoModalidad) y su nombre de documento asociado:
--        1 = factura electronica en linea      -> facturaElectronicaCompraVenta
--        2 = factura computarizada en linea    -> facturaComputarizadaCompraVenta
--    Hoy el codigo genera el XML de computarizada pero envia codigoModalidad = 1.
--  * Formato exacto de fechaEnvio / fechaHoraInicioEvento / fechaHoraFinEvento
--    (UTC sin offset) segun el anexo vigente.

-- ============================================================================
--  SECCION 10. RESUMEN DEL CAMBIO (que se agrega y que se elimina)
-- ============================================================================
--  TABLAS NUEVAS
--    siat_parametricas  (todos los catalogos parametricos en una tabla)
--    siat_eventos       (evento significativo + codigo de recepcion - Etapa 5)
--    siat_paquetes      (envios de contingencia)
--
--  TABLAS QUE DESAPARECEN (fusionadas)
--    siat_unidades_medida -> siat_parametricas (tipo = UNIDAD_MEDIDA)
--
--  COLUMNAS QUE SE AGREGAN
--    facturacion_electronica: fe_tipo_emision (DECIDIDO), fe_estado_local,
--      fe_mensajes, fe_hasArchivo -> fe_hash_archivo, fe_intentos, fe_paquete_id,
--      fe_anulado_motivo, fe_anulado_en
--    factura: fa_numero_siat, fa_cufd, fa_leyenda, fa_monto_sujeto_iva,
--      fa_descuento_adicional, fa_codigo_metodo_pago, fa_numero_tarjeta,
--      fa_codigo_moneda, fa_tipo_cambio, fa_doc_sector, fa_codigo_emision,
--      fa_tipo_factura
--    configuracion_empresa: ce_municipio, ce_codigo_actividad
--    clientes: cl_tipo_documento
--    siat_configuracion: sc_cuis_vigente_hasta, sc_habilitado
--    siat_productos: codigo_actividad, nandina
--    factura_secuencia: fs_punto_venta (entra en la PK)
--
--  COLUMNAS QUE SE ELIMINAN (redundantes o muertas)
--    facturacion_electronica.fe_cuf  (el CUF vive en factura.fa_cuf)
--    siat_configuracion.sc_modo      (el ambiente vive en SIAT_MODO)
--    ventas.ve_numero_control        (no se usa en ningun modulo)
--    factura.fa_codigo_control       (solo preimpreso; se deja si se usara)
--
--  CLAVES UNICAS QUE SE AGREGAN (evitan repetir informacion)
--    factura(ve_id)                        -> una factura por venta
--    factura(su_id, fa_numero_siat)        -> un numero SIAT por sucursal
--    factura(fa_cuf)                       -> un CUF = una factura
--    facturacion_electronica(fa_id)        -> un tramite por factura
--    siat_eventos(ev_codigo)               -> un evento por codigo de recepcion
--    siat_parametricas(par_tipo, par_codigo)
--    siat_actividades(act_codigo, act_doc_sector)
-- ============================================================================
--  FIN DEL ARCHIVO DE REFERENCIA
-- ============================================================================












