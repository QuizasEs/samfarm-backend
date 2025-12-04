-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2025 at 08:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `samfarm_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `caja`
--

CREATE TABLE `caja` (
  `caja_id` bigint(20) UNSIGNED NOT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `us_id` bigint(20) UNSIGNED DEFAULT NULL,
  `caja_nombre` varchar(120) DEFAULT 'Principal',
  `caja_saldo_inicial` decimal(14,2) DEFAULT 0.00,
  `caja_saldo_final` decimal(14,2) DEFAULT NULL,
  `caja_activa` tinyint(1) NOT NULL DEFAULT 1,
  `caja_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `caja_cerrado_en` datetime DEFAULT NULL,
  `caja_observacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caja`
--

INSERT INTO `caja` (`caja_id`, `su_id`, `us_id`, `caja_nombre`, `caja_saldo_inicial`, `caja_saldo_final`, `caja_activa`, `caja_creado_en`, `caja_cerrado_en`, `caja_observacion`) VALUES
(10, 1, 1, 'Caja admin', 200.00, 260.00, 0, '2025-12-03 12:50:01', '2025-12-03 12:50:53', NULL),
(11, 1, 1, 'Caja admin', 200.00, NULL, 1, '2025-12-03 14:52:49', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

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

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`cl_id`, `cl_nombres`, `cl_apellido_paterno`, `cl_apellido_materno`, `cl_telefono`, `cl_correo`, `cl_direccion`, `cl_carnet`, `cl_creado_en`, `cl_actualizado_en`, `cl_estado`) VALUES
(13, 'jose ramiro', 'baca', 'ortega', '', '', '', '1092384712', '2025-12-03 14:52:36', '2025-12-03 14:53:19', 1),
(14, 'raul garcia', 'maamni', 'casimiri', '58737838', 'asdfas@ffsdaf', 'por ahi que no recuerdo', '44857630', '2025-12-03 14:54:42', '2025-12-03 14:54:42', 1);

-- --------------------------------------------------------

--
-- Table structure for table `compras`
--

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

--
-- Dumping data for table `compras`
--

INSERT INTO `compras` (`co_id`, `co_numero`, `co_fecha`, `la_id`, `us_id`, `su_id`, `pr_id`, `co_subtotal`, `co_impuesto`, `co_total`, `co_numero_factura`, `co_fecha_factura`, `co_tipo_documento`, `co_nit_proveedor`, `co_razon_social`, `co_creado_en`, `co_actualizado_en`, `co_estado`) VALUES
(9, 'COMP-2025-0001', '2025-12-03 02:10:58', 8, 1, 1, 3, 37600.00, 4888.00, 42488.00, '31254234232', '2025-12-03', 'compra', NULL, 'Droguería Inti - NIT: 123456790', '2025-12-03 02:10:58', '2025-12-03 02:10:58', 1),
(10, 'COMP-2025-0002', '2025-12-03 02:17:15', 4, 1, 1, 6, 100.00, 13.00, 113.00, '131245243345', '2025-12-21', 'compra', NULL, 'MediBol - NIT: 123456793', '2025-12-03 02:17:15', '2025-12-03 02:17:15', 1),
(11, 'COMP-2025-0003', '2025-12-04 13:03:59', 8, 1, 1, 4, 3500.00, 455.00, 3955.00, '00000000000', '2025-12-04', 'compra', NULL, 'Laboratorios Bolivia - NIT: 123456791', '2025-12-04 13:03:59', '2025-12-04 13:03:59', 1);

-- --------------------------------------------------------

--
-- Table structure for table `configuracion_empresa`
--

CREATE TABLE `configuracion_empresa` (
  `ce_id` bigint(20) UNSIGNED NOT NULL,
  `ce_nombre` varchar(150) NOT NULL,
  `ce_nit` varchar(50) DEFAULT NULL,
  `ce_direccion` varchar(250) DEFAULT NULL,
  `ce_telefono` varchar(50) DEFAULT NULL,
  `ce_correo` varchar(120) DEFAULT NULL,
  `ce_logo` varchar(200) DEFAULT NULL,
  `ce_creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `ce_actualizado_en` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `configuracion_empresa`
--

INSERT INTO `configuracion_empresa` (`ce_id`, `ce_nombre`, `ce_nit`, `ce_direccion`, `ce_telefono`, `ce_correo`, `ce_logo`, `ce_creado_en`, `ce_actualizado_en`) VALUES
(1, 'SAMFARM - Sistema de Gestión Farmacéutica', '123456789', 'por ahi no recuerdo', '75767565656', 'contacto@samfarm.com', NULL, '2025-11-19 01:22:36', '2025-12-04 16:45:12');

-- --------------------------------------------------------

--
-- Table structure for table `detalle_compra`
--

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

--
-- Dumping data for table `detalle_compra`
--

INSERT INTO `detalle_compra` (`dc_id`, `co_id`, `med_id`, `lm_id`, `dc_cantidad`, `dc_precio_unitario`, `dc_descuento`, `dc_subtotal`, `dc_estado`) VALUES
(1, 9, 1, 11, 600, 50.00, 0.00, 30000.00, 1),
(2, 9, 5, 12, 2000, 80.00, 0.00, 4000.00, 1),
(3, 9, 2, 13, 1440, 60.00, 0.00, 3600.00, 1),
(4, 10, 3, 14, 10, 10.00, 0.00, 100.00, 1),
(5, 11, 5, 15, 2240, 50.00, 0.00, 3500.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `detalle_peticion`
--

CREATE TABLE `detalle_peticion` (
  `dp_id` bigint(20) UNSIGNED NOT NULL,
  `pe_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `dp_cantidad_solicitada` int(11) NOT NULL COMMENT 'Cantidad en unidades',
  `dp_observaciones` varchar(255) DEFAULT NULL,
  `dp_estado` tinyint(1) NOT NULL DEFAULT 1,
  `dp_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detalle de medicamentos solicitados en cada petición';

-- --------------------------------------------------------

--
-- Table structure for table `detalle_transferencia`
--

CREATE TABLE `detalle_transferencia` (
  `dt_id` bigint(20) UNSIGNED NOT NULL,
  `tr_id` bigint(20) UNSIGNED NOT NULL,
  `lm_origen_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Lote original que se transfiere',
  `lm_destino_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Lote creado en destino al aceptar',
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `dt_numero_lote_origen` varchar(200) NOT NULL COMMENT 'Respaldo del número de lote',
  `dt_cantidad_cajas` int(11) NOT NULL,
  `dt_cantidad_unidades` bigint(20) NOT NULL,
  `dt_precio_compra` decimal(12,2) NOT NULL,
  `dt_precio_venta` decimal(12,2) NOT NULL,
  `dt_subtotal_valorado` decimal(14,2) NOT NULL,
  `dt_estado` tinyint(1) NOT NULL DEFAULT 1,
  `dt_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detalle de items en cada transferencia';

--
-- Dumping data for table `detalle_transferencia`
--

INSERT INTO `detalle_transferencia` (`dt_id`, `tr_id`, `lm_origen_id`, `lm_destino_id`, `med_id`, `dt_numero_lote_origen`, `dt_cantidad_cajas`, `dt_cantidad_unidades`, `dt_precio_compra`, `dt_precio_venta`, `dt_subtotal_valorado`, `dt_estado`, `dt_creado_en`) VALUES
(1, 3, 15, 16, 5, 'MED-0005', 10, 320, 50.00, 1.00, 500.00, 1, '2025-12-04 13:19:22');

-- --------------------------------------------------------

--
-- Table structure for table `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `dv_id` bigint(20) UNSIGNED NOT NULL,
  `ve_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `lm_id` bigint(20) UNSIGNED DEFAULT NULL,
  `dv_cantidad` int(11) NOT NULL,
  `dv_unidad` varchar(30) DEFAULT 'unidad',
  `dv_precio_unitario` decimal(12,2) NOT NULL,
  `dv_descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `dv_subtotal` decimal(14,2) NOT NULL,
  `dv_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detalle_venta`
--

INSERT INTO `detalle_venta` (`dv_id`, `ve_id`, `med_id`, `lm_id`, `dv_cantidad`, `dv_unidad`, `dv_precio_unitario`, `dv_descuento`, `dv_subtotal`, `dv_estado`) VALUES
(1, 50, 1, 11, 1, 'unidad', 60.00, 0.00, 60.00, 0),
(2, 51, 5, 15, 40, 'unidad', 1.00, 0.00, 40.00, 1),
(3, 52, 5, 12, 50, 'unidad', 2.00, 0.00, 100.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `devoluciones`
--

CREATE TABLE `devoluciones` (
  `dev_id` bigint(20) UNSIGNED NOT NULL,
  `ve_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fa_id` bigint(20) UNSIGNED DEFAULT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `us_id` bigint(20) UNSIGNED DEFAULT NULL,
  `dev_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `dev_cantidad` int(11) NOT NULL DEFAULT 0,
  `dev_motivo` varchar(255) DEFAULT NULL,
  `dev_fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `dev_estado` enum('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devoluciones`
--

INSERT INTO `devoluciones` (`dev_id`, `ve_id`, `fa_id`, `su_id`, `us_id`, `dev_total`, `dev_cantidad`, `dev_motivo`, `dev_fecha`, `dev_estado`) VALUES
(1, 50, 48, 1, 1, 60.00, 1, 'fasdfasdfadsfasdfasdfasd', '2025-12-04 12:18:04', 'aceptada');

-- --------------------------------------------------------

--
-- Table structure for table `factura`
--

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

--
-- Dumping data for table `factura`
--

INSERT INTO `factura` (`fa_id`, `ve_id`, `cl_id`, `us_id`, `su_id`, `fa_numero`, `fa_fecha_emision`, `fa_monto_total`, `fa_codigo_control`, `fa_cuf`, `fa_estado`, `fa_creado_en`) VALUES
(48, 50, NULL, 1, 1, 'F-1-20251203125046-601', '2025-12-03 12:50:46', 60.00, NULL, NULL, 1, '2025-12-03 12:50:46'),
(49, 51, NULL, 1, 1, 'F-1-20251204130738-610', '2025-12-04 13:07:38', 40.00, NULL, NULL, 1, '2025-12-04 13:07:38'),
(50, 52, NULL, 1, 1, 'F-1-20251204131455-658', '2025-12-04 13:14:55', 100.00, NULL, NULL, 1, '2025-12-04 13:14:55');

-- --------------------------------------------------------

--
-- Table structure for table `facturacion_electronica`
--

CREATE TABLE `facturacion_electronica` (
  `fe_id` bigint(20) UNSIGNED NOT NULL,
  `fa_id` bigint(20) UNSIGNED NOT NULL,
  `fe_cuf` varchar(255) DEFAULT NULL,
  `fe_qr` text DEFAULT NULL,
  `fe_estado_siat` varchar(50) DEFAULT NULL,
  `fe_ticket` varchar(255) DEFAULT NULL,
  `fe_fecha_envio` datetime DEFAULT NULL,
  `fe_payload` longtext DEFAULT NULL,
  `fe_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forma_farmaceutica`
--

CREATE TABLE `forma_farmaceutica` (
  `ff_id` bigint(20) UNSIGNED NOT NULL,
  `ff_nombre` varchar(250) NOT NULL,
  `ff_imagen` varchar(255) DEFAULT NULL,
  `ff_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `ff_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ff_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forma_farmaceutica`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `historial_lote`
--

CREATE TABLE `historial_lote` (
  `hl_id` bigint(20) UNSIGNED NOT NULL,
  `lm_id` bigint(20) UNSIGNED NOT NULL,
  `us_id` bigint(20) UNSIGNED DEFAULT NULL,
  `hl_accion` enum('creacion','activacion','ajuste','caducidad','terminacion','devolucion','bloqueo','desbloqueo') NOT NULL,
  `hl_descripcion` text DEFAULT NULL,
  `hl_fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `historial_lote`
--

INSERT INTO `historial_lote` (`hl_id`, `lm_id`, `us_id`, `hl_accion`, `hl_descripcion`, `hl_fecha`) VALUES
(53, 11, 1, 'creacion', 'Lote creado por compra #COMP-2025-0001 en estado \'activo\'.', '2025-12-03 02:10:58'),
(54, 11, 1, 'activacion', 'Lote activado automáticamente al registrar compra #COMP-2025-0001.', '2025-12-03 02:10:58'),
(55, 12, 1, 'creacion', 'Lote creado por compra #COMP-2025-0001 en estado \'activo\'.', '2025-12-03 02:10:58'),
(56, 12, 1, 'activacion', 'Lote activado automáticamente al registrar compra #COMP-2025-0001.', '2025-12-03 02:10:58'),
(57, 13, 1, 'creacion', 'Lote creado por compra #COMP-2025-0001 en estado \'activo\'.', '2025-12-03 02:10:58'),
(58, 13, 1, 'activacion', 'Lote activado automáticamente al registrar compra #COMP-2025-0001.', '2025-12-03 02:10:58'),
(59, 14, 1, 'creacion', 'Lote creado por compra #COMP-2025-0002 en estado \'activo\'.', '2025-12-03 02:17:15'),
(60, 14, 1, 'activacion', 'Lote activado automáticamente al registrar compra #COMP-2025-0002.', '2025-12-03 02:17:15'),
(61, 15, 1, 'creacion', 'Lote creado por compra #COMP-2025-0003 en estado \'activo\'.', '2025-12-04 13:03:59'),
(62, 15, 1, 'activacion', 'Lote activado automáticamente al registrar compra #COMP-2025-0003.', '2025-12-04 13:03:59'),
(63, 15, 1, '', 'Salida de 10 cajas por transferencia #TRANS-2025-0001', '2025-12-04 13:19:22'),
(64, 16, 3, '', 'Recepción de 10 cajas por transferencia #TRANS-2025-0001', '2025-12-04 13:20:52'),
(65, 15, 1, 'ajuste', 'Actualización de datos del lote (cantidades/precios/fecha de vencimiento)', '2025-12-04 13:35:32');

-- --------------------------------------------------------

--
-- Table structure for table `informes`
--

CREATE TABLE `informes` (
  `inf_id` bigint(20) UNSIGNED NOT NULL,
  `inf_nombre` varchar(150) NOT NULL,
  `inf_tipo` varchar(80) NOT NULL,
  `inf_usuario` bigint(20) UNSIGNED DEFAULT NULL,
  `inf_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `inf_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `informes`
--

INSERT INTO `informes` (`inf_id`, `inf_nombre`, `inf_tipo`, `inf_usuario`, `inf_config`, `inf_creado_en`) VALUES
(65, 'Compra COMP-2025-0001 - Droguería Inti - NIT: 123456790', 'compra', 1, '{\"compra_id\":9,\"numero_compra\":\"COMP-2025-0001\",\"proveedor_id\":\"3\",\"laboratorio_id\":\"8\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-12-03\",\"numero_factura\":\"31254234232\",\"razon_social\":\"Droguería Inti - NIT: 123456790\",\"subtotal\":\"37600.00\",\"impuestos\":\"4888.00\",\"total\":\"42488.00\",\"cantidad_lotes\":3,\"lotes\":[{\"medicamento_id\":\"1\",\"numero_lote\":\"MED-0001\",\"cantidad\":600,\"precio_compra\":50,\"precio_venta\":60,\"vencimiento\":\"2026-01-01\",\"activar_lote\":1},{\"medicamento_id\":\"5\",\"numero_lote\":\"MED-0002\",\"cantidad\":50,\"precio_compra\":80,\"precio_venta\":2,\"vencimiento\":\"2026-01-11\",\"activar_lote\":1},{\"medicamento_id\":\"2\",\"numero_lote\":\"MED-0003\",\"cantidad\":60,\"precio_compra\":60,\"precio_venta\":3,\"vencimiento\":\"2026-01-11\",\"activar_lote\":1}]}', '2025-12-03 02:10:58'),
(66, 'Compra COMP-2025-0002 - MediBol - NIT: 123456793', 'compra', 1, '{\"compra_id\":10,\"numero_compra\":\"COMP-2025-0002\",\"proveedor_id\":\"6\",\"laboratorio_id\":\"4\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-12-21\",\"numero_factura\":\"131245243345\",\"razon_social\":\"MediBol - NIT: 123456793\",\"subtotal\":\"100.00\",\"impuestos\":\"13.00\",\"total\":\"113.00\",\"cantidad_lotes\":1,\"lotes\":[{\"medicamento_id\":\"3\",\"numero_lote\":\"MED-0004\",\"cantidad\":10,\"precio_compra\":10,\"precio_venta\":12,\"vencimiento\":\"2025-12-04\",\"activar_lote\":1}]}', '2025-12-03 02:17:15'),
(67, 'Nota Venta F-1-20251203125046-601', 'nota_venta', 1, '{\"ve_id\":50,\"fa_id\":48,\"ve_numero_documento\":\"SU1-1764780646\",\"fa_numero\":\"F-1-20251203125046-601\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"1\",\"lote_id\":\"11\",\"cantidad\":1,\"precio\":60,\"subtotal\":60}],\"subtotal\":60,\"total\":60,\"metodo_pago\":\"efectivo\"}', '2025-12-03 12:50:46'),
(68, 'Devolución #1 - Venta #50', 'devolucion', 1, '{\"dev_id\":1,\"ve_id\":50,\"fa_id\":48,\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"dv_id\":\"1\",\"med_id\":\"1\",\"lm_id\":\"11\",\"cantidad\":1,\"precio_unitario\":\"60.00\",\"motivo\":\"fasdfasdfadsfasdfasdfasd\",\"tipo\":\"cambio\"}],\"total_devolucion\":60,\"cantidad_items\":1,\"motivo\":\"fasdfasdfadsfasdfasdfasd\",\"fecha\":\"2025-12-04 12:18:04\"}', '2025-12-04 12:18:04'),
(69, 'Compra COMP-2025-0003 - Laboratorios Bolivia - NIT: 123456791', 'compra', 1, '{\"compra_id\":11,\"numero_compra\":\"COMP-2025-0003\",\"proveedor_id\":\"4\",\"laboratorio_id\":\"8\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-12-04\",\"numero_factura\":\"00000000000\",\"razon_social\":\"Laboratorios Bolivia - NIT: 123456791\",\"subtotal\":\"3500.00\",\"impuestos\":\"455.00\",\"total\":\"3955.00\",\"cantidad_lotes\":1,\"lotes\":[{\"medicamento_id\":\"5\",\"numero_lote\":\"MED-0005\",\"cantidad\":70,\"precio_compra\":50,\"precio_venta\":1,\"vencimiento\":\"2025-12-26\",\"activar_lote\":1}]}', '2025-12-04 13:03:59'),
(70, 'Nota Venta F-1-20251204130738-610', 'nota_venta', 1, '{\"ve_id\":51,\"fa_id\":49,\"ve_numero_documento\":\"SU1-1764868058\",\"fa_numero\":\"F-1-20251204130738-610\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"5\",\"lote_id\":\"15\",\"cantidad\":40,\"precio\":1,\"subtotal\":40}],\"subtotal\":40,\"total\":40,\"metodo_pago\":\"efectivo\"}', '2025-12-04 13:07:38'),
(71, 'Nota Venta F-1-20251204131455-658', 'nota_venta', 1, '{\"ve_id\":52,\"fa_id\":50,\"ve_numero_documento\":\"SU1-1764868495\",\"fa_numero\":\"F-1-20251204131455-658\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"5\",\"lote_id\":\"12\",\"cantidad\":50,\"precio\":2,\"subtotal\":100}],\"subtotal\":100,\"total\":100,\"metodo_pago\":\"efectivo\"}', '2025-12-04 13:14:55'),
(72, 'Transferencia TRANS-2025-0001', 'transferencia', 1, '{\"tipo_informe\":\"transferencia_salida\",\"tr_id\":\"3\",\"tr_numero\":\"TRANS-2025-0001\",\"su_origen\":\"1\",\"us_emisor\":\"1\",\"total_items\":1,\"total_cajas\":10,\"total_unidades\":320,\"total_valorado\":500,\"tr_estado\":\"pendiente\"}', '2025-12-04 13:19:22'),
(73, 'Recepción de Transferencia TRANS-2025-0001', 'transferencia_recepcion', 3, '{\"tipo_informe\":\"transferencia_entrada\",\"tr_id\":3,\"tr_numero\":\"TRANS-2025-0001\",\"su_destino\":\"2\",\"us_receptor\":\"3\",\"total_items\":1,\"total_cajas\":\"10\",\"total_unidades\":\"320\",\"total_valorado\":\"500.00\",\"tr_estado\":\"aceptada\"}', '2025-12-04 13:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `inventarios`
--

CREATE TABLE `inventarios` (
  `inv_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `inv_total_cajas` int(11) NOT NULL DEFAULT 0,
  `inv_total_unidades` bigint(20) NOT NULL DEFAULT 0,
  `inv_total_valorado` decimal(14,2) DEFAULT 0.00,
  `inv_minimo` int(11) DEFAULT 0,
  `inv_maximo` int(11) DEFAULT NULL,
  `inv_codigo_barras` varchar(255) DEFAULT NULL,
  `inv_stock_alerta` tinyint(1) NOT NULL DEFAULT 0,
  `inv_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `inv_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventarios`
--

INSERT INTO `inventarios` (`inv_id`, `med_id`, `su_id`, `inv_total_cajas`, `inv_total_unidades`, `inv_total_valorado`, `inv_minimo`, `inv_maximo`, `inv_codigo_barras`, `inv_stock_alerta`, `inv_actualizado_en`, `inv_creado_en`) VALUES
(14, 1, 1, 598, 598, 35820.00, 80, NULL, NULL, 0, '2025-12-04 15:20:03', '2025-12-03 02:10:58'),
(15, 5, 1, 34, 1950, 3900.00, 40, NULL, NULL, 0, '2025-12-04 15:20:03', '2025-12-03 02:10:58'),
(16, 2, 1, 60, 1440, 4320.00, 20, 100, NULL, 0, '2025-12-04 15:20:03', '2025-12-03 02:10:58'),
(17, 3, 1, 0, 0, 0.00, 20, NULL, NULL, 0, '2025-12-04 15:20:03', '2025-12-03 02:17:15'),
(19, 5, 2, 10, 320, 320.00, 0, NULL, NULL, 0, '2025-12-04 15:20:03', '2025-12-04 13:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `laboratorios`
--

CREATE TABLE `laboratorios` (
  `la_id` bigint(20) UNSIGNED NOT NULL,
  `la_nombre_comercial` varchar(250) NOT NULL,
  `la_logo` varchar(255) DEFAULT NULL,
  `la_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `la_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `la_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laboratorios`
--

INSERT INTO `laboratorios` (`la_id`, `la_nombre_comercial`, `la_logo`, `la_creado_en`, `la_actualizado_en`, `la_estado`) VALUES
(1, 'Bayer', 'bayer.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1),
(2, 'Pfizer', 'pfizer.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1),
(3, 'Roche', 'roche.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1),
(4, 'Novartis', 'novartis.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1),
(5, 'GSK', 'gsk.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1),
(6, 'Sanofi', 'sanofi.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1),
(7, 'Merck', 'merck.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1),
(8, 'AstraZeneca', 'astrazeneca.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lote_medicamento`
--

CREATE TABLE `lote_medicamento` (
  `lm_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `pr_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pr_id_compra` bigint(20) UNSIGNED DEFAULT NULL,
  `lm_numero_lote` varchar(200) DEFAULT NULL,
  `lm_cant_caja` int(11) NOT NULL DEFAULT 0 COMMENT 'cajas ingresadas en este registro',
  `lm_cant_blister` int(11) NOT NULL DEFAULT 1 COMMENT 'blisters por caja (1 si no aplica)',
  `lm_cant_unidad` int(11) NOT NULL DEFAULT 1 COMMENT 'unidades por blister (1 si no aplica)',
  `lm_total_unidades` bigint(20) GENERATED ALWAYS AS (`lm_cant_caja` * `lm_cant_blister` * `lm_cant_unidad`) STORED,
  `lm_cant_actual_cajas` int(11) NOT NULL DEFAULT 0,
  `lm_cant_actual_unidades` bigint(20) NOT NULL DEFAULT 0,
  `lm_precio_compra` decimal(12,2) DEFAULT NULL,
  `lm_precio_venta` decimal(12,2) DEFAULT NULL,
  `lm_fecha_ingreso` datetime NOT NULL DEFAULT current_timestamp(),
  `lm_fecha_vencimiento` date DEFAULT NULL,
  `lm_estado` enum('en_espera','activo','terminado','caducado','devuelto','bloqueado') NOT NULL DEFAULT 'en_espera',
  `lm_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `lm_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `lm_origen_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'si este registro proviene de la división/transferencia de otro lm_id',
  `lm_tr_bloqueado` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID de transferencia que tiene bloqueado este stock'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lote_medicamento`
--

INSERT INTO `lote_medicamento` (`lm_id`, `med_id`, `su_id`, `pr_id`, `pr_id_compra`, `lm_numero_lote`, `lm_cant_caja`, `lm_cant_blister`, `lm_cant_unidad`, `lm_cant_actual_cajas`, `lm_cant_actual_unidades`, `lm_precio_compra`, `lm_precio_venta`, `lm_fecha_ingreso`, `lm_fecha_vencimiento`, `lm_estado`, `lm_creado_en`, `lm_actualizado_en`, `lm_origen_id`, `lm_tr_bloqueado`) VALUES
(11, 1, 1, 3, 9, 'MED-0001', 600, 1, 1, 597, 597, 50.00, 60.00, '2025-12-03 02:10:58', '2026-01-01', 'activo', '2025-12-03 02:10:58', '2025-12-04 12:18:04', NULL, NULL),
(12, 5, 1, 3, 9, 'MED-0002', 50, 4, 10, 48, 1950, 80.00, 2.00, '2025-12-03 02:10:58', '2026-01-11', 'activo', '2025-12-03 02:10:58', '2025-12-04 13:14:55', NULL, NULL),
(13, 2, 1, 3, 9, 'MED-0003', 60, 6, 4, 60, 1440, 60.00, 3.00, '2025-12-03 02:10:58', '2026-01-11', 'activo', '2025-12-03 02:10:58', '2025-12-03 02:10:58', NULL, NULL),
(14, 3, 1, 6, 10, 'MED-0004', 10, 1, 1, 10, 10, 10.00, 12.00, '2025-12-03 02:17:15', '2025-12-04', 'caducado', '2025-12-03 02:17:15', '2025-12-03 12:10:32', NULL, NULL),
(15, 5, 1, 4, 11, 'MED-0005', 70, 4, 8, 58, 1880, 50.00, 1.00, '2025-12-04 13:03:59', '2025-12-04', 'caducado', '2025-12-04 13:03:59', '2025-12-04 14:41:31', NULL, NULL),
(16, 5, 2, 4, 11, 'MED-0005', 10, 4, 8, 10, 320, 50.00, 1.00, '2025-12-04 13:20:52', '2025-12-26', 'activo', '2025-12-04 13:20:52', '2025-12-04 13:20:52', 15, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medicamento`
--

CREATE TABLE `medicamento` (
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `med_nombre_quimico` varchar(200) NOT NULL,
  `med_principio_activo` varchar(200) NOT NULL,
  `med_accion_farmacologica` varchar(255) DEFAULT NULL,
  `med_presentacion` varchar(150) DEFAULT NULL,
  `med_descripcion` text DEFAULT NULL,
  `med_codigo_barras` varchar(255) DEFAULT NULL,
  `med_version_comercial` varchar(100) DEFAULT NULL,
  `med_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `med_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `uf_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vd_id` bigint(20) UNSIGNED DEFAULT NULL,
  `la_id` bigint(20) UNSIGNED DEFAULT NULL,
  `su_id` bigint(20) UNSIGNED DEFAULT NULL,
  `us_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicamento`
--

INSERT INTO `medicamento` (`med_id`, `med_nombre_quimico`, `med_principio_activo`, `med_accion_farmacologica`, `med_presentacion`, `med_descripcion`, `med_codigo_barras`, `med_version_comercial`, `med_creado_en`, `med_actualizado_en`, `uf_id`, `ff_id`, `vd_id`, `la_id`, `su_id`, `us_id`) VALUES
(1, 'Paracetamol', 'Paracetamol', 'Analgésico y antipirético', 'Tabletas 500mg x 10', 'Analgésico para dolor leve a moderado', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 1, 1, 1, 1, 1, 1),
(2, 'Ibuprofeno', 'Ibuprofeno', 'Antiinflamatorio no esteroideo', 'Tabletas 400mg x 20', 'Antiinflamatorio y analgésico', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 3, 1, 1, 2, 1, 1),
(3, 'Amoxicilina', 'Amoxicilina', 'Antibiótico de amplio espectro', 'Cápsulas 500mg x 12', 'Antibiótico para infecciones bacterianas', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 2, 2, 1, 3, 1, 1),
(4, 'Loratadina', 'Loratadina', 'Antihistamínico', 'Tabletas 10mg x 10', 'Para alergias y rinitis', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 4, 1, 1, 4, 1, 1),
(5, 'Omeprazol', 'Omeprazol', 'Inhibidor de bomba de protones', 'Cápsulas 20mg x 14', 'Para úlceras y reflujo gastroesofágico', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 8, 2, 1, 5, 1, 1),
(6, 'Metformina', 'Metformina', 'Hipoglucemiante oral', 'Tabletas 850mg x 30', 'Para diabetes tipo 2', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 7, 1, 1, 6, 1, 1),
(7, 'Atorvastatina', 'Atorvastatina', 'Hipolipemiante', 'Tabletas 20mg x 30', 'Para reducir colesterol', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 7, 1, 1, 7, 1, 1),
(8, 'Salbutamol', 'Salbutamol', 'Broncodilatador', 'Spray 100mcg x 200 dosis', 'Para asma y broncoespasmo', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 10, 9, 10, 8, 1, 1),
(9, 'Losartán', 'Losartán', 'Antihipertensivo', 'Tabletas 50mg x 30', 'Para hipertensión arterial', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 7, 1, 1, 1, 1, 1),
(10, 'Diazepam', 'Diazepam', 'Ansiolítico y relajante muscular', 'Tabletas 5mg x 20', 'Para ansiedad y espasmos musculares', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 9, 1, 1, 2, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `merma`
--

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

--
-- Dumping data for table `merma`
--

INSERT INTO `merma` (`me_id`, `med_id`, `lm_id`, `su_id`, `us_id`, `me_cantidad`, `me_motivo`, `me_fecha`) VALUES
(1, 3, 14, 1, 1, 10, 'caducado', '2025-12-03 12:10:32'),
(2, 5, 15, 1, 1, 1880, 'caducado', '2025-12-04 14:41:31');

-- --------------------------------------------------------

--
-- Table structure for table `movimiento_caja`
--

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

--
-- Dumping data for table `movimiento_caja`
--

INSERT INTO `movimiento_caja` (`mc_id`, `caja_id`, `us_id`, `mc_tipo`, `mc_monto`, `mc_concepto`, `mc_referencia_tipo`, `mc_referencia_id`, `mc_fecha`) VALUES
(49, 10, 1, 'venta', 60.00, 'Venta SU1-1764780646', 'venta', 50, '2025-12-03 12:50:46'),
(50, 11, 1, 'venta', 40.00, 'Venta SU1-1764868058', 'venta', 51, '2025-12-04 13:07:38'),
(51, 11, 1, 'venta', 100.00, 'Venta SU1-1764868495', 'venta', 52, '2025-12-04 13:14:55');

-- --------------------------------------------------------

--
-- Table structure for table `movimiento_inventario`
--

CREATE TABLE `movimiento_inventario` (
  `mi_id` bigint(20) UNSIGNED NOT NULL,
  `lm_id` bigint(20) UNSIGNED DEFAULT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `us_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mi_tipo` varchar(30) NOT NULL,
  `mi_cantidad` int(11) NOT NULL,
  `mi_unidad` varchar(30) DEFAULT 'unidad',
  `mi_referencia_tipo` varchar(30) DEFAULT NULL,
  `mi_referencia_id` bigint(20) DEFAULT NULL,
  `mi_motivo` text DEFAULT NULL,
  `mi_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `mi_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movimiento_inventario`
--

INSERT INTO `movimiento_inventario` (`mi_id`, `lm_id`, `med_id`, `su_id`, `us_id`, `mi_tipo`, `mi_cantidad`, `mi_unidad`, `mi_referencia_tipo`, `mi_referencia_id`, `mi_motivo`, `mi_creado_en`, `mi_estado`) VALUES
(80, 11, 1, 1, 1, 'entrada', 600, 'unidad', 'compra', 9, 'Ingreso por compra COMP-2025-0001', '2025-12-03 02:10:58', 1),
(81, 12, 5, 1, 1, 'entrada', 2000, 'unidad', 'compra', 9, 'Ingreso por compra COMP-2025-0001', '2025-12-03 02:10:58', 1),
(82, 13, 2, 1, 1, 'entrada', 1440, 'unidad', 'compra', 9, 'Ingreso por compra COMP-2025-0001', '2025-12-03 02:10:58', 1),
(83, 14, 3, 1, 1, 'entrada', 10, 'unidad', 'compra', 10, 'Ingreso por compra COMP-2025-0002', '2025-12-03 02:17:15', 1),
(84, 14, 3, 1, 1, 'salida', 10, 'unidad', 'merma', 1, 'Merma: caducado', '2025-12-03 12:10:33', 1),
(85, 11, 1, 1, 1, 'salida', 1, 'unidad', 'venta', 50, 'Venta SU1-1764780646 (lm_id 11)', '2025-12-03 12:50:46', 1),
(86, 11, 1, 1, 1, 'baja', 1, 'unidad', 'devolucion', 1, 'Devolución: fasdfasdfadsfasdfasdfasd', '2025-12-04 12:18:04', 1),
(87, 11, 1, 1, 1, 'salida', 1, 'unidad', 'cambio', 1, 'Cambio por devolución: fasdfasdfadsfasdfasdfasd', '2025-12-04 12:18:04', 1),
(88, 15, 5, 1, 1, 'entrada', 2240, 'unidad', 'compra', 11, 'Ingreso por compra COMP-2025-0003', '2025-12-04 13:03:59', 1),
(89, 15, 5, 1, 1, 'salida', 40, 'unidad', 'venta', 51, 'Venta SU1-1764868058 (lm_id 15)', '2025-12-04 13:07:38', 1),
(90, 12, 5, 1, 1, 'salida', 50, 'unidad', 'venta', 52, 'Venta SU1-1764868495 (lm_id 12)', '2025-12-04 13:14:55', 1),
(91, 15, 5, 1, 1, 'salida', 320, 'unidad', 'transferencia_salida', 3, 'Transferencia #TRANS-2025-0001 hacia sucursal destino', '2025-12-04 13:19:22', 1),
(92, 16, 5, 2, 3, 'entrada', 320, 'unidad', 'transferencia_entrada', 3, 'Recepción de transferencia #TRANS-2025-0001 desde Sucursal Central', '2025-12-04 13:20:52', 1),
(93, 15, 5, 1, 1, 'salida', 1880, 'unidad', 'merma', 2, 'Merma: caducado', '2025-12-04 14:41:31', 1);

-- --------------------------------------------------------

--
-- Table structure for table `peticiones`
--

CREATE TABLE `peticiones` (
  `pe_id` bigint(20) UNSIGNED NOT NULL,
  `pe_numero` varchar(100) NOT NULL COMMENT 'Formato: PET-YYYY-0001',
  `su_solicitante_id` bigint(20) UNSIGNED NOT NULL,
  `su_destino_id` bigint(20) UNSIGNED NOT NULL,
  `us_solicitante_id` bigint(20) UNSIGNED NOT NULL,
  `us_respondedor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pe_total_items` int(11) NOT NULL DEFAULT 0,
  `pe_estado` enum('pendiente','aceptada','rechazada','cancelada') NOT NULL DEFAULT 'pendiente',
  `pe_observaciones` text DEFAULT NULL,
  `pe_motivo_rechazo` text DEFAULT NULL,
  `pe_fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `pe_fecha_respuesta` datetime DEFAULT NULL,
  `pe_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `pe_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tr_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Transferencia generada si se acepta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Peticiones de medicamentos entre sucursales';

-- --------------------------------------------------------

--
-- Table structure for table `proveedores`
--

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

--
-- Dumping data for table `proveedores`
--

INSERT INTO `proveedores` (`pr_id`, `pr_nombres`, `pr_apellido_paterno`, `pr_apellido_materno`, `pr_telefono`, `pr_nit`, `pr_direccion`, `pr_creado_en`, `pr_actualizado_en`, `pr_estado`) VALUES
(1, 'javier', 'javier', 'javier', '1231234312', '312123234234', 'javierjavier', '2025-11-06 10:45:32', '2025-11-06 10:45:32', 1),
(2, 'Farmacorp S.A.', NULL, NULL, '23456789', '123456789', 'Av. Industrial 456, Zona Industrial', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(3, 'Droguería Inti', NULL, NULL, '23456790', '123456790', 'Calle Comercio 789, Centro', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(4, 'Laboratorios Bolivia', NULL, NULL, '23456791', '123456791', 'Av. Petrolera 321, Zona Sur', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(5, 'Distribuidora Salud', NULL, NULL, '23456792', '123456792', 'Calle Mercado 654, Zona Norte', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(6, 'MediBol', NULL, NULL, '23456793', '123456793', 'Av. Circunvalación 987, Zona Este', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(7, 'jonas', 'raul', 'de la mar', '', '31348612316542310', '', '2025-11-26 21:22:41', '2025-11-26 21:22:41', 1),
(8, 'hugo', 'suares', 'maldonado', '02113216845', '000000000000', '', '2025-11-26 21:23:27', '2025-11-26 21:24:20', 1);

-- --------------------------------------------------------

--
-- Table structure for table `proveedores_laboratorio`
--

CREATE TABLE `proveedores_laboratorio` (
  `pl_id` bigint(20) UNSIGNED NOT NULL,
  `pr_id` bigint(20) UNSIGNED NOT NULL,
  `la_id` bigint(20) UNSIGNED NOT NULL,
  `pl_fecha_creado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `ro_id` bigint(20) UNSIGNED NOT NULL,
  `ro_nombre` varchar(50) NOT NULL,
  `ro_descripcion` text DEFAULT NULL,
  `ro_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `ro_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ro_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`ro_id`, `ro_nombre`, `ro_descripcion`, `ro_creado_en`, `ro_actualizado_en`, `ro_estado`) VALUES
(1, 'admin', 'Administrador del sistema con todos los permisos', '2025-11-06 10:17:03', '2025-11-06 10:17:03', 1),
(2, 'gerente', 'Gerente de sucursal', '2025-11-06 10:17:03', '2025-11-06 10:17:03', 1),
(3, 'vendedor', 'Usuario de caja / ventas', '2025-11-06 10:17:03', '2025-11-06 10:17:03', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sucursales`
--

CREATE TABLE `sucursales` (
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `su_nombre` varchar(120) NOT NULL,
  `su_direccion` varchar(250) DEFAULT NULL,
  `su_telefono` varchar(30) DEFAULT NULL,
  `su_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `su_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `su_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sucursales`
--

INSERT INTO `sucursales` (`su_id`, `su_nombre`, `su_direccion`, `su_telefono`, `su_creado_en`, `su_actualizado_en`, `su_estado`) VALUES
(1, 'Sucursal Central', 'Av. Principal 123, Ciudad', '+591-2-1234567', '2025-11-06 10:17:03', '2025-11-06 10:17:03', 1),
(2, 'sucursal 2', 'calle siempre viva', '123456789', '2025-11-20 21:18:42', '2025-11-20 21:18:42', 1),
(3, 'dasd', 'fsfsdfads', '21312', '2025-12-02 20:37:20', '2025-12-04 12:42:36', 1);

-- --------------------------------------------------------

--
-- Table structure for table `transferencias`
--

CREATE TABLE `transferencias` (
  `tr_id` bigint(20) UNSIGNED NOT NULL,
  `tr_numero` varchar(100) NOT NULL COMMENT 'Formato: TRANS-YYYY-0001',
  `su_origen_id` bigint(20) UNSIGNED NOT NULL,
  `su_destino_id` bigint(20) UNSIGNED NOT NULL,
  `us_emisor_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Usuario que envía',
  `us_receptor_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Usuario que recibe',
  `tr_total_items` int(11) NOT NULL DEFAULT 0,
  `tr_total_cajas` int(11) NOT NULL DEFAULT 0,
  `tr_total_unidades` bigint(20) NOT NULL DEFAULT 0,
  `tr_total_valorado` decimal(14,2) DEFAULT 0.00,
  `tr_estado` enum('pendiente','aceptada','rechazada','cancelada') NOT NULL DEFAULT 'pendiente',
  `tr_observaciones` text DEFAULT NULL,
  `tr_motivo_rechazo` text DEFAULT NULL,
  `tr_fecha_envio` datetime NOT NULL DEFAULT current_timestamp(),
  `tr_fecha_respuesta` datetime DEFAULT NULL,
  `tr_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `tr_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro padre de transferencias entre sucursales';

--
-- Dumping data for table `transferencias`
--

INSERT INTO `transferencias` (`tr_id`, `tr_numero`, `su_origen_id`, `su_destino_id`, `us_emisor_id`, `us_receptor_id`, `tr_total_items`, `tr_total_cajas`, `tr_total_unidades`, `tr_total_valorado`, `tr_estado`, `tr_observaciones`, `tr_motivo_rechazo`, `tr_fecha_envio`, `tr_fecha_respuesta`, `tr_creado_en`, `tr_actualizado_en`) VALUES
(3, 'TRANS-2025-0001', 1, 2, 1, 3, 1, 10, 320, 500.00, 'aceptada', '', NULL, '2025-12-04 13:19:22', '2025-12-04 13:20:52', '2025-12-04 13:19:22', '2025-12-04 13:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `uso_farmacologico`
--

CREATE TABLE `uso_farmacologico` (
  `uf_id` bigint(20) UNSIGNED NOT NULL,
  `uf_nombre` varchar(250) NOT NULL,
  `uf_imagen` longtext DEFAULT NULL,
  `uf_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `uf_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `uf_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uso_farmacologico`
--

INSERT INTO `uso_farmacologico` (`uf_id`, `uf_nombre`, `uf_imagen`, `uf_creado_en`, `uf_actualizado_en`, `uf_estado`) VALUES
(1, 'Analgésico', NULL, '2025-11-06 11:06:03', '2025-11-29 15:49:21', 0),
(2, 'Antibiótico', 'antibiotico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(3, 'Antiinflamatorio', 'antiinflamatorio.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(4, 'Antihistamínico', 'antihistaminico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(5, 'Antipirético', 'antipiretico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(6, 'Antiséptico', 'antiseptico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(7, 'Cardiovascular', 'cardiovascular.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(8, 'Digestivo', 'digestivo.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(9, 'Dermatológico', 'dermatologico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(10, 'Respiratorio', 'respiratorio.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(11, 'aviar', '', '2025-11-29 14:17:38', '2025-11-29 14:17:38', 1),
(12, 'garganta', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABUAAAAMACAIAAABq7Fo6AAAAAXNSR0IB2cksfwAAAARnQU1BAACxjwv8YQUAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB+kLEQ8VOHYB+LYAACAASURBVHjaZL3bgtxIjixoBjgjMqWq6ulz2', '2025-11-29 14:30:00', '2025-11-29 14:30:00', 1),
(13, 'dasads', 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wgARCAIyA+gDASIAAhEB', '2025-11-29 14:33:53', '2025-11-29 14:34:25', 0),
(14, 'parada', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABUAAAAMACAIAAABq7Fo6AAAAAXNSR0IB2cksfwAAAARnQU1BAACxjwv8YQUAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB+kLEQ8VOHYB+LYAACAASURBVHjaZL3bgtxIjixoBjgjMqWq6ulz2', '2025-11-29 15:08:52', '2025-11-29 15:08:52', 1);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

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

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`us_id`, `us_nombres`, `us_apellido_paterno`, `us_apellido_materno`, `us_numero_carnet`, `us_telefono`, `us_correo`, `us_direccion`, `us_username`, `us_password_hash`, `us_token_recuperacion`, `us_token_expiracion`, `us_creado_en`, `us_actualizado_en`, `us_estado`, `su_id`, `ro_id`) VALUES
(1, 'admin', 'admin', 'admin', '000000000', '111111111', 'admin@admin.com', 'admin calle admin', 'admin', 'dlo5ZmZvbmRjME41dGlDY01tTGcrUT09', NULL, NULL, '2025-11-06 10:17:03', '2025-12-04 12:41:09', 1, 1, 1),
(2, 'usuario', 'usuario', 'usuario', '1235497866656', '122565165464', 'usuario@usuario.usuario', 'usuariousuario', 'usuario', 'Q0oxTTdMNktnMzhoQjBDOXFJWXI1Zz09', NULL, NULL, '2025-11-20 21:30:31', '2025-11-27 18:04:46', 0, 2, 3),
(3, 'gerente', 'gerente', 'gerente', '123321321', '', '', 'gerente', 'gerente', 'ZFA3UHhUdGwrVERjWjVCSmhWaFJpdz09', NULL, NULL, '2025-11-27 18:05:22', '2025-12-04 13:20:18', 1, 2, 2),
(5, 'caja', 'caja', 'caja', '51325346452634', '', '', '', 'caja', 'M29oVHhvbnExOFQ5TS9ha1hmRG11QT09', NULL, NULL, '2025-12-02 20:47:20', '2025-12-02 20:47:50', 1, 2, 3),
(6, 'caja caja', 'caja', 'caja', '3455345234645645645', '', '', '', 'roverto', 'VXhSUktmRUhJK3JubDR5N1o3VUd1QT09', NULL, NULL, '2025-12-04 12:42:19', '2025-12-04 12:42:19', 1, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `ventas`
--

CREATE TABLE `ventas` (
  `ve_id` bigint(20) UNSIGNED NOT NULL,
  `ve_numero_documento` varchar(80) NOT NULL,
  `ve_fecha_emision` datetime NOT NULL DEFAULT current_timestamp(),
  `cl_id` bigint(20) UNSIGNED DEFAULT NULL,
  `us_id` bigint(20) UNSIGNED NOT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `caja_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ve_subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ve_impuesto` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ve_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ve_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ve_metodo_pago` varchar(20) NOT NULL DEFAULT '''efectivo''',
  `ve_tipo_documento` varchar(20) DEFAULT '''nota de venta''',
  `ve_estado_documento` varchar(20) DEFAULT '''emitida''',
  `ve_numero_control` varchar(100) DEFAULT NULL,
  `ve_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ventas`
--

INSERT INTO `ventas` (`ve_id`, `ve_numero_documento`, `ve_fecha_emision`, `cl_id`, `us_id`, `su_id`, `caja_id`, `ve_subtotal`, `ve_impuesto`, `ve_total`, `ve_actualizado_en`, `ve_metodo_pago`, `ve_tipo_documento`, `ve_estado_documento`, `ve_numero_control`, `ve_estado`) VALUES
(50, 'SU1-1764780646', '2025-12-03 12:50:46', NULL, 1, 1, 10, 60.00, 0.00, 60.00, '2025-12-04 12:18:04', 'efectivo', 'nota de venta', 'devuelto', NULL, 1),
(51, 'SU1-1764868058', '2025-12-04 13:07:38', NULL, 1, 1, 11, 40.00, 0.00, 40.00, '2025-12-04 13:07:38', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(52, 'SU1-1764868495', '2025-12-04 13:14:55', NULL, 1, 1, 11, 100.00, 0.00, 100.00, '2025-12-04 13:14:55', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `via_de_administracion`
--

CREATE TABLE `via_de_administracion` (
  `vd_id` bigint(20) UNSIGNED NOT NULL,
  `vd_nombre` varchar(250) NOT NULL,
  `vd_imagen` varchar(255) DEFAULT NULL,
  `vd_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `vd_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `vd_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `via_de_administracion`
--

INSERT INTO `via_de_administracion` (`vd_id`, `vd_nombre`, `vd_imagen`, `vd_creado_en`, `vd_actualizado_en`, `vd_estado`) VALUES
(1, 'Oral', 'oral.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(2, 'Tópica', 'topica.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(3, 'Intramuscular', 'intramuscular.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(4, 'Intravenosa', 'intravenosa.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(5, 'Subcutánea', 'subcutanea.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(6, 'Rectal', 'rectal.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(7, 'Vaginal', 'vaginal.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(8, 'Oftálmica', 'oftalmica.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(9, 'Ótica', 'otica.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(10, 'Nasal', 'nasal.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `caja`
--
ALTER TABLE `caja`
  ADD PRIMARY KEY (`caja_id`),
  ADD KEY `fk_caja_sucursal` (`su_id`),
  ADD KEY `fk_caja_usuario` (`us_id`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`cl_id`),
  ADD KEY `ix_clientes_carnet` (`cl_carnet`);

--
-- Indexes for table `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`co_id`),
  ADD KEY `fk_compras_laboratorios` (`la_id`),
  ADD KEY `fk_compras_proveedores` (`pr_id`),
  ADD KEY `fk_compras_sucursales` (`su_id`),
  ADD KEY `fk_compras_usuarios` (`us_id`);

--
-- Indexes for table `configuracion_empresa`
--
ALTER TABLE `configuracion_empresa`
  ADD PRIMARY KEY (`ce_id`);

--
-- Indexes for table `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD PRIMARY KEY (`dc_id`),
  ADD KEY `ix_dc_co` (`co_id`),
  ADD KEY `ix_dc_med` (`med_id`),
  ADD KEY `ix_dc_lm` (`lm_id`);

--
-- Indexes for table `detalle_peticion`
--
ALTER TABLE `detalle_peticion`
  ADD PRIMARY KEY (`dp_id`),
  ADD KEY `idx_dp_peticion` (`pe_id`),
  ADD KEY `idx_dp_medicamento` (`med_id`);

--
-- Indexes for table `detalle_transferencia`
--
ALTER TABLE `detalle_transferencia`
  ADD PRIMARY KEY (`dt_id`),
  ADD KEY `idx_dt_tr` (`tr_id`),
  ADD KEY `idx_dt_lm_origen` (`lm_origen_id`),
  ADD KEY `idx_dt_lm_destino` (`lm_destino_id`),
  ADD KEY `idx_dt_medicamento` (`med_id`);

--
-- Indexes for table `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`dv_id`),
  ADD KEY `ix_dv_ve` (`ve_id`),
  ADD KEY `ix_dv_med` (`med_id`),
  ADD KEY `ix_dv_lm` (`lm_id`);

--
-- Indexes for table `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD PRIMARY KEY (`dev_id`),
  ADD KEY `fk_dev_ve` (`ve_id`),
  ADD KEY `fk_dev_fa` (`fa_id`),
  ADD KEY `fk_dev_su` (`su_id`),
  ADD KEY `fk_dev_us` (`us_id`);

--
-- Indexes for table `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`fa_id`),
  ADD KEY `fk_factura_venta` (`ve_id`),
  ADD KEY `fk_factura_cliente` (`cl_id`),
  ADD KEY `fk_factura_usuario` (`us_id`),
  ADD KEY `fk_factura_sucursal` (`su_id`);

--
-- Indexes for table `facturacion_electronica`
--
ALTER TABLE `facturacion_electronica`
  ADD PRIMARY KEY (`fe_id`),
  ADD KEY `fk_fe_fa` (`fa_id`);

--
-- Indexes for table `forma_farmaceutica`
--
ALTER TABLE `forma_farmaceutica`
  ADD PRIMARY KEY (`ff_id`),
  ADD UNIQUE KEY `ux_forma_nombre` (`ff_nombre`);

--
-- Indexes for table `historial_lote`
--
ALTER TABLE `historial_lote`
  ADD PRIMARY KEY (`hl_id`),
  ADD KEY `fk_historial_lote_lm` (`lm_id`),
  ADD KEY `fk_historial_lote_us` (`us_id`);

--
-- Indexes for table `informes`
--
ALTER TABLE `informes`
  ADD PRIMARY KEY (`inf_id`),
  ADD KEY `fk_inf_usuario` (`inf_usuario`);

--
-- Indexes for table `inventarios`
--
ALTER TABLE `inventarios`
  ADD PRIMARY KEY (`inv_id`),
  ADD UNIQUE KEY `ux_inv_su_med` (`su_id`,`med_id`),
  ADD KEY `ix_inv_med` (`med_id`);

--
-- Indexes for table `laboratorios`
--
ALTER TABLE `laboratorios`
  ADD PRIMARY KEY (`la_id`),
  ADD UNIQUE KEY `ux_laboratorios_nombre` (`la_nombre_comercial`);

--
-- Indexes for table `lote_medicamento`
--
ALTER TABLE `lote_medicamento`
  ADD PRIMARY KEY (`lm_id`),
  ADD KEY `ix_lm_med` (`med_id`),
  ADD KEY `ix_lm_su` (`su_id`),
  ADD KEY `ix_lm_pr` (`pr_id`),
  ADD KEY `ix_lm_numero` (`lm_numero_lote`),
  ADD KEY `fk_lm_origen` (`lm_origen_id`),
  ADD KEY `fk_lm_tr_bloqueado` (`lm_tr_bloqueado`);

--
-- Indexes for table `medicamento`
--
ALTER TABLE `medicamento`
  ADD PRIMARY KEY (`med_id`),
  ADD KEY `fk_med_uf` (`uf_id`),
  ADD KEY `fk_med_ff` (`ff_id`),
  ADD KEY `fk_med_vd` (`vd_id`),
  ADD KEY `fk_med_la` (`la_id`),
  ADD KEY `fk_med_su` (`su_id`),
  ADD KEY `fk_med_us` (`us_id`);

--
-- Indexes for table `merma`
--
ALTER TABLE `merma`
  ADD PRIMARY KEY (`me_id`),
  ADD KEY `fk_me_med` (`med_id`),
  ADD KEY `fk_me_lm` (`lm_id`),
  ADD KEY `fk_me_su` (`su_id`),
  ADD KEY `fk_me_us` (`us_id`);

--
-- Indexes for table `movimiento_caja`
--
ALTER TABLE `movimiento_caja`
  ADD PRIMARY KEY (`mc_id`),
  ADD KEY `ix_mc_caja` (`caja_id`),
  ADD KEY `fk_mc_us` (`us_id`);

--
-- Indexes for table `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD PRIMARY KEY (`mi_id`),
  ADD KEY `ix_mi_lm` (`lm_id`),
  ADD KEY `ix_mi_med` (`med_id`),
  ADD KEY `ix_mi_su` (`su_id`),
  ADD KEY `ix_mi_us` (`us_id`);

--
-- Indexes for table `peticiones`
--
ALTER TABLE `peticiones`
  ADD PRIMARY KEY (`pe_id`),
  ADD UNIQUE KEY `ux_pe_numero` (`pe_numero`),
  ADD KEY `idx_pe_estado` (`pe_estado`),
  ADD KEY `idx_pe_solicitante` (`su_solicitante_id`),
  ADD KEY `idx_pe_destino` (`su_destino_id`),
  ADD KEY `fk_pe_us_solicitante` (`us_solicitante_id`),
  ADD KEY `fk_pe_us_respondedor` (`us_respondedor_id`),
  ADD KEY `fk_pe_transferencia` (`tr_id`),
  ADD KEY `idx_pe_estado_destino` (`pe_estado`,`su_destino_id`);

--
-- Indexes for table `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`pr_id`),
  ADD KEY `ix_proveedores_nit` (`pr_nit`);

--
-- Indexes for table `proveedores_laboratorio`
--
ALTER TABLE `proveedores_laboratorio`
  ADD PRIMARY KEY (`pl_id`),
  ADD UNIQUE KEY `ux_pl_pr_la` (`pr_id`,`la_id`),
  ADD KEY `fk_pl_pr` (`pr_id`),
  ADD KEY `fk_pl_la` (`la_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`ro_id`),
  ADD UNIQUE KEY `ux_roles_nombre` (`ro_nombre`);

--
-- Indexes for table `sucursales`
--
ALTER TABLE `sucursales`
  ADD PRIMARY KEY (`su_id`),
  ADD UNIQUE KEY `ux_sucursales_nombre` (`su_nombre`);

--
-- Indexes for table `transferencias`
--
ALTER TABLE `transferencias`
  ADD PRIMARY KEY (`tr_id`),
  ADD UNIQUE KEY `ux_tr_numero` (`tr_numero`),
  ADD KEY `idx_tr_estado` (`tr_estado`),
  ADD KEY `idx_tr_origen` (`su_origen_id`),
  ADD KEY `idx_tr_destino` (`su_destino_id`),
  ADD KEY `idx_tr_fecha` (`tr_fecha_envio`),
  ADD KEY `fk_tr_us_emisor` (`us_emisor_id`),
  ADD KEY `fk_tr_us_receptor` (`us_receptor_id`),
  ADD KEY `idx_tr_estado_destino` (`tr_estado`,`su_destino_id`);

--
-- Indexes for table `uso_farmacologico`
--
ALTER TABLE `uso_farmacologico`
  ADD PRIMARY KEY (`uf_id`),
  ADD UNIQUE KEY `ux_uso_nombre` (`uf_nombre`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`us_id`),
  ADD UNIQUE KEY `ux_usuarios_username` (`us_username`),
  ADD KEY `fk_usuarios_sucursales` (`su_id`),
  ADD KEY `fk_usuarios_roles` (`ro_id`);

--
-- Indexes for table `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`ve_id`),
  ADD KEY `fk_ventas_clientes` (`cl_id`),
  ADD KEY `fk_ventas_sucursales` (`su_id`),
  ADD KEY `fk_ventas_usuarios` (`us_id`),
  ADD KEY `fk_ventas_caja` (`caja_id`);

--
-- Indexes for table `via_de_administracion`
--
ALTER TABLE `via_de_administracion`
  ADD PRIMARY KEY (`vd_id`),
  ADD UNIQUE KEY `ux_via_nombre` (`vd_nombre`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `caja`
--
ALTER TABLE `caja`
  MODIFY `caja_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `cl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `compras`
--
ALTER TABLE `compras`
  MODIFY `co_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `configuracion_empresa`
--
ALTER TABLE `configuracion_empresa`
  MODIFY `ce_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detalle_compra`
--
ALTER TABLE `detalle_compra`
  MODIFY `dc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `detalle_peticion`
--
ALTER TABLE `detalle_peticion`
  MODIFY `dp_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detalle_transferencia`
--
ALTER TABLE `detalle_transferencia`
  MODIFY `dt_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `dv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `devoluciones`
--
ALTER TABLE `devoluciones`
  MODIFY `dev_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `factura`
--
ALTER TABLE `factura`
  MODIFY `fa_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `facturacion_electronica`
--
ALTER TABLE `facturacion_electronica`
  MODIFY `fe_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forma_farmaceutica`
--
ALTER TABLE `forma_farmaceutica`
  MODIFY `ff_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `historial_lote`
--
ALTER TABLE `historial_lote`
  MODIFY `hl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `informes`
--
ALTER TABLE `informes`
  MODIFY `inf_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `inventarios`
--
ALTER TABLE `inventarios`
  MODIFY `inv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `laboratorios`
--
ALTER TABLE `laboratorios`
  MODIFY `la_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `lote_medicamento`
--
ALTER TABLE `lote_medicamento`
  MODIFY `lm_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `medicamento`
--
ALTER TABLE `medicamento`
  MODIFY `med_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `merma`
--
ALTER TABLE `merma`
  MODIFY `me_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `movimiento_caja`
--
ALTER TABLE `movimiento_caja`
  MODIFY `mc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  MODIFY `mi_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `peticiones`
--
ALTER TABLE `peticiones`
  MODIFY `pe_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `pr_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `proveedores_laboratorio`
--
ALTER TABLE `proveedores_laboratorio`
  MODIFY `pl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `ro_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sucursales`
--
ALTER TABLE `sucursales`
  MODIFY `su_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transferencias`
--
ALTER TABLE `transferencias`
  MODIFY `tr_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `uso_farmacologico`
--
ALTER TABLE `uso_farmacologico`
  MODIFY `uf_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `us_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ventas`
--
ALTER TABLE `ventas`
  MODIFY `ve_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `via_de_administracion`
--
ALTER TABLE `via_de_administracion`
  MODIFY `vd_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `caja`
--
ALTER TABLE `caja`
  ADD CONSTRAINT `fk_caja_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_caja_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `fk_compras_laboratorio` FOREIGN KEY (`la_id`) REFERENCES `laboratorios` (`la_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_proveedor` FOREIGN KEY (`pr_id`) REFERENCES `proveedores` (`pr_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD CONSTRAINT `fk_dc_compras` FOREIGN KEY (`co_id`) REFERENCES `compras` (`co_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dc_lote` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dc_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detalle_peticion`
--
ALTER TABLE `detalle_peticion`
  ADD CONSTRAINT `fk_dp_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dp_peticion` FOREIGN KEY (`pe_id`) REFERENCES `peticiones` (`pe_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detalle_transferencia`
--
ALTER TABLE `detalle_transferencia`
  ADD CONSTRAINT `fk_dt_lm_destino` FOREIGN KEY (`lm_destino_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dt_lm_origen` FOREIGN KEY (`lm_origen_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dt_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dt_transferencia` FOREIGN KEY (`tr_id`) REFERENCES `transferencias` (`tr_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `fk_dv_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dv_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dv_venta` FOREIGN KEY (`ve_id`) REFERENCES `ventas` (`ve_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD CONSTRAINT `fk_dev_fa` FOREIGN KEY (`fa_id`) REFERENCES `factura` (`fa_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dev_su` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dev_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dev_ve` FOREIGN KEY (`ve_id`) REFERENCES `ventas` (`ve_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `fk_factura_cliente` FOREIGN KEY (`cl_id`) REFERENCES `clientes` (`cl_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_venta` FOREIGN KEY (`ve_id`) REFERENCES `ventas` (`ve_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `facturacion_electronica`
--
ALTER TABLE `facturacion_electronica`
  ADD CONSTRAINT `fk_fe_fa` FOREIGN KEY (`fa_id`) REFERENCES `factura` (`fa_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `historial_lote`
--
ALTER TABLE `historial_lote`
  ADD CONSTRAINT `fk_historial_lote_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historial_lote_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `informes`
--
ALTER TABLE `informes`
  ADD CONSTRAINT `fk_inf_usuario` FOREIGN KEY (`inf_usuario`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `inventarios`
--
ALTER TABLE `inventarios`
  ADD CONSTRAINT `fk_inv_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inv_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lote_medicamento`
--
ALTER TABLE `lote_medicamento`
  ADD CONSTRAINT `fk_lm_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_origen` FOREIGN KEY (`lm_origen_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_proveedor` FOREIGN KEY (`pr_id`) REFERENCES `proveedores` (`pr_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_tr_bloqueado` FOREIGN KEY (`lm_tr_bloqueado`) REFERENCES `transferencias` (`tr_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `medicamento`
--
ALTER TABLE `medicamento`
  ADD CONSTRAINT `fk_medicamento_forma` FOREIGN KEY (`ff_id`) REFERENCES `forma_farmaceutica` (`ff_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_laboratorio` FOREIGN KEY (`la_id`) REFERENCES `laboratorios` (`la_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_uso` FOREIGN KEY (`uf_id`) REFERENCES `uso_farmacologico` (`uf_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_via` FOREIGN KEY (`vd_id`) REFERENCES `via_de_administracion` (`vd_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `merma`
--
ALTER TABLE `merma`
  ADD CONSTRAINT `fk_me_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_me_med` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_me_su` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_me_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `movimiento_caja`
--
ALTER TABLE `movimiento_caja`
  ADD CONSTRAINT `fk_mc_caja` FOREIGN KEY (`caja_id`) REFERENCES `caja` (`caja_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mc_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD CONSTRAINT `fk_mi_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mi_med` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mi_su` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mi_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `peticiones`
--
ALTER TABLE `peticiones`
  ADD CONSTRAINT `fk_pe_su_destino` FOREIGN KEY (`su_destino_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pe_su_solicitante` FOREIGN KEY (`su_solicitante_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pe_transferencia` FOREIGN KEY (`tr_id`) REFERENCES `transferencias` (`tr_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pe_us_respondedor` FOREIGN KEY (`us_respondedor_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pe_us_solicitante` FOREIGN KEY (`us_solicitante_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `proveedores_laboratorio`
--
ALTER TABLE `proveedores_laboratorio`
  ADD CONSTRAINT `fk_pl_la` FOREIGN KEY (`la_id`) REFERENCES `laboratorios` (`la_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pl_pr` FOREIGN KEY (`pr_id`) REFERENCES `proveedores` (`pr_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transferencias`
--
ALTER TABLE `transferencias`
  ADD CONSTRAINT `fk_tr_su_destino` FOREIGN KEY (`su_destino_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tr_su_origen` FOREIGN KEY (`su_origen_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tr_us_emisor` FOREIGN KEY (`us_emisor_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tr_us_receptor` FOREIGN KEY (`us_receptor_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`ro_id`) REFERENCES `roles` (`ro_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_sucursales` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_ventas_caja` FOREIGN KEY (`caja_id`) REFERENCES `caja` (`caja_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ventas_clientes` FOREIGN KEY (`cl_id`) REFERENCES `clientes` (`cl_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ventas_sucursales` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ventas_usuarios` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
