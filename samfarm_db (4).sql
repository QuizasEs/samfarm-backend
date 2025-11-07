

CREATE TABLE `caja` (
  `caja_id` bigint(20) UNSIGNED NOT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `caja_nombre` varchar(120) DEFAULT 'Principal',
  `caja_saldo_inicial` decimal(14,2) DEFAULT 0.00,
  `caja_activa` tinyint(1) NOT NULL DEFAULT 1,
  `caja_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE `clientes` (
  `cl_id` bigint(20) UNSIGNED NOT NULL,
  `cl_nombres` varchar(100) NOT NULL,
  `cl_apellido_paterno` varchar(80) DEFAULT NULL,
  `cl_apellido_materno` varchar(80) DEFAULT NULL,
  `cl_telefono` varchar(30) DEFAULT NULL,
  `cl_correo` varchar(200) DEFAULT NULL,
  `cl_direccion` varchar(250) DEFAULT NULL,
  `cl_carnet` varchar(40) DEFAULT NULL,
  `cl_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `cl_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cl_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `codigo_barras` (
  `cb_id` bigint(30) NOT NULL,
  `cb_codigo` varchar(255) NOT NULL,
  `cb_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `compras` (
  `co_id` bigint(20) UNSIGNED NOT NULL,
  `co_numero` varchar(80) NOT NULL,
  `co_fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `la_id` bigint(20) UNSIGNED DEFAULT NULL,
  `us_id` bigint(20) UNSIGNED NOT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `pr_id` bigint(20) UNSIGNED DEFAULT NULL,
  `co_subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `co_impuesto` decimal(14,2) NOT NULL DEFAULT 0.00,
  `co_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `co_numero_factura` varchar(100) DEFAULT NULL,
  `co_fecha_factura` date DEFAULT NULL,
  `co_tipo_documento` varchar(20) DEFAULT 'compra',
  `co_nit_proveedor` varchar(50) DEFAULT NULL,
  `co_razon_social` varchar(200) DEFAULT NULL,
  `co_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `co_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `co_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `detalle_compra` (
  `dc_id` bigint(20) UNSIGNED NOT NULL,
  `co_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `lm_id` bigint(20) UNSIGNED DEFAULT NULL,
  `dc_cantidad` int(11) NOT NULL,
  `dc_precio_unitario` decimal(12,2) NOT NULL,
  `dc_descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `dc_subtotal` decimal(14,2) NOT NULL,
  `dc_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `detalle_venta` (
  `dv_id` bigint(20) UNSIGNED NOT NULL,
  `ve_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `lm_id` bigint(20) UNSIGNED DEFAULT NULL,
  `dv_cantidad` int(11) NOT NULL,
  `dv_precio_unitario` decimal(12,2) NOT NULL,
  `dv_descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `dv_subtotal` decimal(14,2) NOT NULL,
  `dv_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `factura` (
  `fa_id` bigint(20) UNSIGNED NOT NULL,
  `ve_id` bigint(20) UNSIGNED NOT NULL,
  `cl_id` bigint(20) UNSIGNED DEFAULT NULL,
  `us_id` bigint(20) UNSIGNED NOT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `fa_numero` varchar(100) NOT NULL,
  `fa_fecha_emision` datetime NOT NULL DEFAULT current_timestamp(),
  `fa_monto_total` decimal(14,2) NOT NULL,
  `fa_codigo_control` varchar(100) DEFAULT NULL,
  `fa_cuf` varchar(255) DEFAULT NULL,
  `fa_estado` tinyint(1) NOT NULL DEFAULT 1,
  `fa_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `forma_farmaceutica` (
  `ff_id` bigint(20) UNSIGNED NOT NULL,
  `ff_nombre` varchar(250) NOT NULL,
  `ff_imagen` varchar(255) DEFAULT NULL,
  `ff_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `ff_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ff_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `forma_farmaceutica` (`ff_id`, `ff_nombre`, `ff_imagen`, `ff_creado_en`, `ff_actualizado_en`, `ff_estado`) VALUES
(1, 'Tableta', 'tableta.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(2, 'Cápsula', 'capsula.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(3, 'Jarabe', 'jarabe.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(4, 'Crema', 'crema.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(5, 'Inyectable', 'inyectable.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(6, 'Suspensión', 'suspension.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(7, 'Ungüento', 'unguento.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(8, 'Supositorio', 'supositorio.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(9, 'Spray', 'spray.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(10, 'Polvo', 'polvo.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1);


CREATE TABLE `informes` (
  `inf_id` bigint(20) UNSIGNED NOT NULL,
  `inf_nombre` varchar(150) NOT NULL,
  `inf_tipo` varchar(80) NOT NULL,
  `inf_usuario` bigint(20) UNSIGNED DEFAULT NULL,
  `inf_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `inf_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `inventarios` (
  `inv_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `lm_id` bigint(20) UNSIGNED DEFAULT NULL,
  `inv_cantidad` int(11) NOT NULL DEFAULT 0,
  `inv_reservado` int(11) NOT NULL DEFAULT 0,
  `inv_minimo` int(11) DEFAULT 0,
  `inv_maximo` int(11) DEFAULT NULL,
  `inv_ultimo_precio` decimal(12,2) DEFAULT NULL,
  `inv_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `inv_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `inventarios` (`inv_id`, `med_id`, `su_id`, `lm_id`, `inv_cantidad`, `inv_reservado`, `inv_minimo`, `inv_maximo`, `inv_ultimo_precio`, `inv_actualizado_en`, `inv_creado_en`) VALUES
(1, 1, 1, 1, 85, 0, 20, 200, 2.50, '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(2, 1, 1, 2, 150, 0, 20, 200, 2.50, '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(3, 2, 1, 3, 60, 0, 15, 150, 3.00, '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(4, 3, 1, 4, 35, 0, 10, 100, 15.00, '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(5, 4, 1, 5, 95, 0, 25, 200, 4.50, '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(6, 5, 1, 6, 42, 0, 10, 100, 12.00, '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(7, 6, 1, 7, 90, 0, 20, 150, 8.50, '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(8, 7, 1, 8, 25, 0, 5, 80, 18.00, '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(9, 8, 1, 9, 18, 0, 5, 50, 35.00, '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(10, 9, 1, 10, 55, 0, 15, 120, 22.00, '2025-11-06 11:06:06', '2025-11-06 11:06:06');



CREATE TABLE `laboratorios` (
  `la_id` bigint(20) UNSIGNED NOT NULL,
  `la_nombre_comercial` varchar(250) NOT NULL,
  `la_logo` varchar(255) DEFAULT NULL,
  `la_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `la_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `la_estado` tinyint(1) NOT NULL DEFAULT 1,
  `pr_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `laboratorios` (`la_id`, `la_nombre_comercial`, `la_logo`, `la_creado_en`, `la_actualizado_en`, `la_estado`, `pr_id`) VALUES
(1, 'Bayer', 'bayer.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 1),
(2, 'Pfizer', 'pfizer.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 2),
(3, 'Roche', 'roche.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 3),
(4, 'Novartis', 'novartis.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 4),
(5, 'GSK', 'gsk.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 5),
(6, 'Sanofi', 'sanofi.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 1),
(7, 'Merck', 'merck.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 2),
(8, 'AstraZeneca', 'astrazeneca.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 3);



CREATE TABLE `lote_medicamento` (
  `lm_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `pc_id` bigint(20) UNSIGNED DEFAULT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `pr_id` bigint(20) UNSIGNED DEFAULT NULL,
  `la_id` bigint(20) UNSIGNED DEFAULT NULL,
  `lm_numero_lote` varchar(200) DEFAULT NULL,
  `lm_cantidad_inicial` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `lm_cantidad_actual` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `lm_precio_compra` decimal(12,2) DEFAULT NULL,
  `lm_precio_venta` decimal(12,2) DEFAULT NULL,
  `lm_fecha_ingreso` datetime NOT NULL DEFAULT current_timestamp(),
  `lm_fecha_vencimiento` date DEFAULT NULL,
  `lm_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `lm_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `lm_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE `medicamento` (
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `med_nombre_quimico` varchar(200) NOT NULL,
  `med_principio_activo` varchar(200) NOT NULL,
  `med_accion_farmacologica` varchar(255) DEFAULT NULL,
  `med_presentacion` varchar(150) DEFAULT NULL,
  `med_descripcion` text DEFAULT NULL,
  `med_precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `med_precio_caja` decimal(12,2) DEFAULT NULL,
  `cb_id` bigint(30) DEFAULT NULL,
  `med_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `med_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `uf_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vd_id` bigint(20) UNSIGNED DEFAULT NULL,
  `la_id` bigint(20) UNSIGNED DEFAULT NULL,
  `su_id` bigint(20) UNSIGNED DEFAULT NULL,
  `us_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE `merma` (
  `me_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `lm_id` bigint(20) UNSIGNED DEFAULT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `us_id` bigint(20) UNSIGNED DEFAULT NULL,
  `me_cantidad` int(11) NOT NULL,
  `me_motivo` text DEFAULT NULL,
  `me_fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE `movimiento_caja` (
  `mc_id` bigint(20) UNSIGNED NOT NULL,
  `caja_id` bigint(20) UNSIGNED NOT NULL,
  `us_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mc_tipo` varchar(30) NOT NULL,
  `mc_monto` decimal(14,2) NOT NULL,
  `mc_concepto` varchar(255) DEFAULT NULL,
  `mc_referencia_tipo` varchar(50) DEFAULT NULL,
  `mc_referencia_id` bigint(20) DEFAULT NULL,
  `mc_fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `movimiento_caja` (`mc_id`, `caja_id`, `us_id`, `mc_tipo`, `mc_monto`, `mc_concepto`, `mc_referencia_tipo`, `mc_referencia_id`, `mc_fecha`) VALUES
(1, 1, 1, 'ingreso', 51.87, 'Venta VENT-001-2024', 'venta', 1, '2025-11-06 11:06:09'),
(2, 1, 1, 'ingreso', 31.92, 'Venta VENT-002-2024', 'venta', 2, '2025-11-06 11:06:09'),
(3, 1, 1, 'ingreso', 71.25, 'Venta VENT-003-2024', 'venta', 3, '2025-11-06 11:06:09');


CREATE TABLE `movimiento_inventario` (
  `mi_id` bigint(20) UNSIGNED NOT NULL,
  `lm_id` bigint(20) UNSIGNED DEFAULT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `us_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mi_tipo` varchar(30) NOT NULL,
  `mi_cantidad` int(11) NOT NULL,
  `mi_unidad` varchar(30) DEFAULT 'unidad',
  `mi_fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `mi_referencia_tipo` varchar(30) DEFAULT NULL,
  `mi_referencia_id` bigint(20) DEFAULT NULL,
  `mi_motivo` text DEFAULT NULL,
  `mi_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `mi_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE `presentacion_cantidad` (
  `pc_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `ff_id` bigint(20) UNSIGNED NOT NULL,
  `pc_unidades_por_presentacion` int(11) NOT NULL DEFAULT 1,
  `pc_unidad_medida` varchar(80) DEFAULT NULL,
  `pc_equivalencia` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `pc_estado` tinyint(1) NOT NULL DEFAULT 1,
  `pc_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `proveedores` (
  `pr_id` bigint(20) UNSIGNED NOT NULL,
  `pr_nombres` varchar(120) NOT NULL,
  `pr_apellido_paterno` varchar(80) DEFAULT NULL,
  `pr_apellido_materno` varchar(80) DEFAULT NULL,
  `pr_telefono` varchar(30) DEFAULT NULL,
  `pr_nit` varchar(50) DEFAULT NULL,
  `pr_direccion` varchar(250) DEFAULT NULL,
  `pr_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `pr_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pr_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `roles` (
  `ro_id` bigint(20) UNSIGNED NOT NULL,
  `ro_nombre` varchar(50) NOT NULL,
  `ro_descripcion` text DEFAULT NULL,
  `ro_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `ro_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ro_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE `sucursales` (
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `su_nombre` varchar(120) NOT NULL,
  `su_direccion` varchar(250) DEFAULT NULL,
  `su_telefono` varchar(30) DEFAULT NULL,
  `su_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `su_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `su_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `uso_farmacologico` (
  `uf_id` bigint(20) UNSIGNED NOT NULL,
  `uf_nombre` varchar(250) NOT NULL,
  `uf_imagen` varchar(255) DEFAULT NULL,
  `uf_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `uf_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `uf_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `usuarios` (
  `us_id` bigint(20) UNSIGNED NOT NULL,
  `us_nombres` varchar(120) NOT NULL,
  `us_apellido_paterno` varchar(80) DEFAULT NULL,
  `us_apellido_materno` varchar(80) DEFAULT NULL,
  `us_numero_carnet` varchar(60) DEFAULT NULL,
  `us_telefono` varchar(30) DEFAULT NULL,
  `us_correo` varchar(200) DEFAULT NULL,
  `us_direccion` text DEFAULT NULL,
  `us_username` varchar(80) NOT NULL,
  `us_password_hash` varchar(255) NOT NULL,
  `us_token_recuperacion` varchar(255) DEFAULT NULL,
  `us_token_expiracion` datetime DEFAULT NULL,
  `us_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `us_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `us_estado` tinyint(1) NOT NULL DEFAULT 1,
  `su_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ro_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `ventas` (
  `ve_id` bigint(20) UNSIGNED NOT NULL,
  `ve_numero_documento` varchar(80) NOT NULL,
  `ve_fecha_emision` datetime NOT NULL DEFAULT current_timestamp(),
  `cl_id` bigint(20) UNSIGNED DEFAULT NULL,
  `us_id` bigint(20) UNSIGNED NOT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `ve_subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ve_impuesto` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ve_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ve_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `ve_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ve_estado` tinyint(1) NOT NULL DEFAULT 1,
  `ve_tipo_documento` varchar(20) DEFAULT 'venta',
  `ve_estado_documento` varchar(20) DEFAULT 'emitida',
  `ve_numero_control` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `via_de_administracion` (
  `vd_id` bigint(20) UNSIGNED NOT NULL,
  `vd_nombre` varchar(250) NOT NULL,
  `vd_imagen` varchar(255) DEFAULT NULL,
  `vd_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `vd_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `vd_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `caja`
  ADD PRIMARY KEY (`caja_id`),
  ADD KEY `fk_caja_sucursal` (`su_id`);



ALTER TABLE `clientes`
  ADD PRIMARY KEY (`cl_id`),
  ADD KEY `ix_clientes_carnet` (`cl_carnet`);



ALTER TABLE `codigo_barras`
  ADD PRIMARY KEY (`cb_id`);


ALTER TABLE `compras`
  ADD PRIMARY KEY (`co_id`),
  ADD KEY `fk_compras_laboratorios` (`la_id`),
  ADD KEY `fk_compras_proveedores` (`pr_id`),
  ADD KEY `fk_compras_sucursales` (`su_id`),
  ADD KEY `fk_compras_usuarios` (`us_id`);

ALTER TABLE `detalle_compra`
  ADD PRIMARY KEY (`dc_id`),
  ADD KEY `ix_dc_co` (`co_id`),
  ADD KEY `ix_dc_med` (`med_id`),
  ADD KEY `ix_dc_lm` (`lm_id`);


ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`dv_id`),
  ADD KEY `ix_dv_ve` (`ve_id`),
  ADD KEY `ix_dv_med` (`med_id`),
  ADD KEY `ix_dv_lm` (`lm_id`);

ALTER TABLE `factura`
  ADD PRIMARY KEY (`fa_id`),
  ADD KEY `fk_factura_venta` (`ve_id`),
  ADD KEY `fk_factura_cliente` (`cl_id`),
  ADD KEY `fk_factura_usuario` (`us_id`),
  ADD KEY `fk_factura_sucursal` (`su_id`);

ALTER TABLE `forma_farmaceutica`
  ADD PRIMARY KEY (`ff_id`),
  ADD UNIQUE KEY `ux_forma_nombre` (`ff_nombre`);


ALTER TABLE `informes`
  ADD PRIMARY KEY (`inf_id`),
  ADD KEY `fk_inf_usuario` (`inf_usuario`);

ALTER TABLE `inventarios`
  ADD PRIMARY KEY (`inv_id`),
  ADD UNIQUE KEY `ux_inv_su_med_lm` (`su_id`,`med_id`,`lm_id`),
  ADD KEY `ix_inv_med` (`med_id`),
  ADD KEY `fk_inv_lm` (`lm_id`);

ALTER TABLE `laboratorios`
  ADD PRIMARY KEY (`la_id`),
  ADD UNIQUE KEY `ux_laboratorios_nombre` (`la_nombre_comercial`),
  ADD KEY `fk_laboratorios_pr` (`pr_id`);

ALTER TABLE `lote_medicamento`
  ADD PRIMARY KEY (`lm_id`),
  ADD KEY `ix_lm_med` (`med_id`),
  ADD KEY `ix_lm_su` (`su_id`),
  ADD KEY `ix_lm_pr` (`pr_id`),
  ADD KEY `ix_lm_numero` (`lm_numero_lote`),
  ADD KEY `ix_lm_pc` (`pc_id`),
  ADD KEY `ix_lm_la` (`la_id`);

ALTER TABLE `medicamento`
  ADD PRIMARY KEY (`med_id`),
  ADD KEY `fk_med_uf` (`uf_id`),
  ADD KEY `fk_med_ff` (`ff_id`),
  ADD KEY `fk_med_vd` (`vd_id`),
  ADD KEY `fk_med_la` (`la_id`),
  ADD KEY `fk_med_su` (`su_id`),
  ADD KEY `fk_med_us` (`us_id`),
  ADD KEY `cb_id` (`cb_id`);

ALTER TABLE `merma`
  ADD PRIMARY KEY (`me_id`),
  ADD KEY `fk_me_med` (`med_id`),
  ADD KEY `fk_me_lm` (`lm_id`),
  ADD KEY `fk_me_su` (`su_id`),
  ADD KEY `fk_me_us` (`us_id`);


ALTER TABLE `movimiento_caja`
  ADD PRIMARY KEY (`mc_id`),
  ADD KEY `ix_mc_caja` (`caja_id`),
  ADD KEY `fk_mc_us` (`us_id`);


ALTER TABLE `movimiento_inventario`
  ADD PRIMARY KEY (`mi_id`),
  ADD KEY `ix_mi_lm` (`lm_id`),
  ADD KEY `ix_mi_med` (`med_id`),
  ADD KEY `ix_mi_su` (`su_id`),
  ADD KEY `ix_mi_us` (`us_id`);
ndexes for table `presentacion_cantidad`

ALTER TABLE `presentacion_cantidad`
  ADD PRIMARY KEY (`pc_id`),
  ADD KEY `ix_pc_med` (`med_id`),
  ADD KEY `ix_pc_ff` (`ff_id`);


ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`pr_id`),
  ADD KEY `ix_proveedores_nit` (`pr_nit`);


ALTER TABLE `roles`
  ADD PRIMARY KEY (`ro_id`),
  ADD UNIQUE KEY `ux_roles_nombre` (`ro_nombre`);

ALTER TABLE `sucursales`
  ADD PRIMARY KEY (`su_id`),
  ADD UNIQUE KEY `ux_sucursales_nombre` (`su_nombre`);

ALTER TABLE `uso_farmacologico`
  ADD PRIMARY KEY (`uf_id`),
  ADD UNIQUE KEY `ux_uso_nombre` (`uf_nombre`);


ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`us_id`),
  ADD UNIQUE KEY `ux_usuarios_username` (`us_username`),
  ADD UNIQUE KEY `ux_usuarios_correo` (`us_correo`),
  ADD KEY `fk_usuarios_sucursales` (`su_id`),
  ADD KEY `fk_usuarios_roles` (`ro_id`);

ALTER TABLE `ventas`
  ADD PRIMARY KEY (`ve_id`),
  ADD KEY `fk_ventas_clientes` (`cl_id`),
  ADD KEY `fk_ventas_sucursales` (`su_id`),
  ADD KEY `fk_ventas_usuarios` (`us_id`);


ALTER TABLE `via_de_administracion`
  ADD PRIMARY KEY (`vd_id`),
  ADD UNIQUE KEY `ux_via_nombre` (`vd_nombre`);

ALTER TABLE `caja`
  MODIFY `caja_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `clientes`
  MODIFY `cl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;


ALTER TABLE `codigo_barras`
  MODIFY `cb_id` bigint(30) NOT NULL AUTO_INCREMENT;


ALTER TABLE `compras`
  MODIFY `co_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `detalle_compra`
  MODIFY `dc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `detalle_venta`
  MODIFY `dv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;


ALTER TABLE `factura`
  MODIFY `fa_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `forma_farmaceutica`
  MODIFY `ff_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `informes`
  MODIFY `inf_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `inventarios`
  MODIFY `inv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `laboratorios`
  MODIFY `la_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `lote_medicamento`
  MODIFY `lm_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;


ALTER TABLE `medicamento`
  MODIFY `med_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;


ALTER TABLE `merma`
  MODIFY `me_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


ALTER TABLE `movimiento_caja`
  MODIFY `mc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;



ALTER TABLE `movimiento_inventario`
  MODIFY `mi_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;


ALTER TABLE `presentacion_cantidad`
  MODIFY `pc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `proveedores`
  MODIFY `pr_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;


ALTER TABLE `roles`
  MODIFY `ro_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `sucursales`
  MODIFY `su_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `uso_farmacologico`
  MODIFY `uf_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;


ALTER TABLE `usuarios`
  MODIFY `us_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `ventas`
  MODIFY `ve_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `via_de_administracion`
  MODIFY `vd_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;


ALTER TABLE `caja`
  ADD CONSTRAINT `fk_caja_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE;



ALTER TABLE `compras`
  ADD CONSTRAINT `fk_compras_laboratorio` FOREIGN KEY (`la_id`) REFERENCES `laboratorios` (`la_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_proveedor` FOREIGN KEY (`pr_id`) REFERENCES `proveedores` (`pr_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `detalle_compra`
  ADD CONSTRAINT `fk_dc_compras` FOREIGN KEY (`co_id`) REFERENCES `compras` (`co_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dc_lote` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dc_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `fk_dv_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dv_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dv_venta` FOREIGN KEY (`ve_id`) REFERENCES `ventas` (`ve_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `factura`
  ADD CONSTRAINT `fk_factura_cliente` FOREIGN KEY (`cl_id`) REFERENCES `clientes` (`cl_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_venta` FOREIGN KEY (`ve_id`) REFERENCES `ventas` (`ve_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `informes`
  ADD CONSTRAINT `fk_inf_usuario` FOREIGN KEY (`inf_usuario`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE `inventarios`
  ADD CONSTRAINT `fk_inv_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inv_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inv_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `laboratorios`
  ADD CONSTRAINT `fk_laboratorios_proveedores` FOREIGN KEY (`pr_id`) REFERENCES `proveedores` (`pr_id`) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE `lote_medicamento`
  ADD CONSTRAINT `fk_lm_la` FOREIGN KEY (`la_id`) REFERENCES `laboratorios` (`la_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_pc` FOREIGN KEY (`pc_id`) REFERENCES `presentacion_cantidad` (`pc_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_proveedor` FOREIGN KEY (`pr_id`) REFERENCES `proveedores` (`pr_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `medicamento`
  ADD CONSTRAINT `fk_medicamento_forma` FOREIGN KEY (`ff_id`) REFERENCES `forma_farmaceutica` (`ff_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_laboratorio` FOREIGN KEY (`la_id`) REFERENCES `laboratorios` (`la_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_uso` FOREIGN KEY (`uf_id`) REFERENCES `uso_farmacologico` (`uf_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_via` FOREIGN KEY (`vd_id`) REFERENCES `via_de_administracion` (`vd_id`) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE `merma`
  ADD CONSTRAINT `fk_me_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_me_med` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_me_su` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_me_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE `movimiento_inventario`
  ADD CONSTRAINT `fk_mi_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mi_med` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mi_su` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mi_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `presentacion_cantidad`
  ADD CONSTRAINT `fk_pc_ff` FOREIGN KEY (`ff_id`) REFERENCES `forma_farmaceutica` (`ff_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pc_med` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON UPDATE CASCADE;


ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`ro_id`) REFERENCES `roles` (`ro_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_sucursales` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE SET NULL ON UPDATE CASCADE;



ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_ventas_clientes` FOREIGN KEY (`cl_id`) REFERENCES `clientes` (`cl_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ventas_sucursales` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ventas_usuarios` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
