-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-12-2025 a las 01:53:29
-- Versión del servidor: 10.4.27-MariaDB
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `samfarm_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `balance_precios`
--

CREATE TABLE `balance_precios` (
  `bp_id` bigint(20) UNSIGNED NOT NULL,
  `lm_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID del lote de medicamento afectado',
  `us_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID del usuario administrador que realizó el cambio',
  `bp_precio_anterior` decimal(12,2) NOT NULL COMMENT 'Precio anterior en Bs',
  `bp_precio_nuevo` decimal(12,2) NOT NULL COMMENT 'Precio nuevo en Bs',
  `bp_creado_en` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora del cambio'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro histórico de cambios de precios de venta en el balance';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja`
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
-- Volcado de datos para la tabla `caja`
--

INSERT INTO `caja` (`caja_id`, `su_id`, `us_id`, `caja_nombre`, `caja_saldo_inicial`, `caja_saldo_final`, `caja_activa`, `caja_creado_en`, `caja_cerrado_en`, `caja_observacion`) VALUES
(15, 1, 1, 'Caja admin', '200.00', '800.00', 0, '2025-12-10 01:14:15', '2025-12-10 15:24:42', NULL),
(16, 1, 1, 'Caja admin', '2200.00', NULL, 1, '2025-12-10 19:18:35', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
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
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`cl_id`, `cl_nombres`, `cl_apellido_paterno`, `cl_apellido_materno`, `cl_telefono`, `cl_correo`, `cl_direccion`, `cl_carnet`, `cl_creado_en`, `cl_actualizado_en`, `cl_estado`) VALUES
(13, 'jose ramiro', 'baca', 'ortega', '', '', '', '1092384712', '2025-12-03 14:52:36', '2025-12-03 14:53:19', 1),
(14, 'raul garcia', 'maamni', 'casimiri', '58737838', 'asdfas@ffsdaf', 'por ahi que no recuerdo', '44857630', '2025-12-03 14:54:42', '2025-12-03 14:54:42', 1),
(15, 'edmon', 'farnoli', 'jumbo', '', '', '', '14236574756', '2025-12-06 14:12:27', '2025-12-06 14:27:00', 1),
(16, 'raul', 'ignacio', '', '', '', '', '837565631025', '2025-12-06 15:45:14', '2025-12-06 15:45:14', 1),
(17, 'oruga', 'comeloda', '', '', '', '', '475646276234', '2025-12-08 20:07:10', '2025-12-08 20:07:10', 1),
(18, 'hugo', 'samora', 'gomez', '6564563635', 'hugo@gmail.com', 'por ahir', '2453453534', '2025-12-10 01:15:19', '2025-12-10 01:15:19', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
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
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`co_id`, `co_numero`, `co_fecha`, `la_id`, `us_id`, `su_id`, `pr_id`, `co_subtotal`, `co_impuesto`, `co_total`, `co_numero_factura`, `co_fecha_factura`, `co_tipo_documento`, `co_nit_proveedor`, `co_razon_social`, `co_creado_en`, `co_actualizado_en`, `co_estado`) VALUES
(14, 'COMP-2025-0001', '2025-12-10 01:03:09', 4, 1, 1, 9, '3000.00', '390.00', '3390.00', '6546565465', '2025-12-19', 'compra', NULL, 'juan ramon chochu - NIT: 6516576465', '2025-12-10 01:03:09', '2025-12-10 01:03:09', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_empresa`
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
-- Volcado de datos para la tabla `configuracion_empresa`
--

INSERT INTO `configuracion_empresa` (`ce_id`, `ce_nombre`, `ce_nit`, `ce_direccion`, `ce_telefono`, `ce_correo`, `ce_logo`, `ce_creado_en`, `ce_actualizado_en`) VALUES
(1, 'SAMFARM - Sistema de Gestión Farmacéutica', '123456789dfg', 'por ahi no recuerdo', '75767565656', 'contacto@samfarm.com', 'http://localhost/samfarm-backend/views/assets/img/logo_empresa_2285f77ce9add384_1764983826.png', '2025-11-19 01:22:36', '2025-12-06 18:56:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compra`
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
-- Volcado de datos para la tabla `detalle_compra`
--

INSERT INTO `detalle_compra` (`dc_id`, `co_id`, `med_id`, `lm_id`, `dc_cantidad`, `dc_precio_unitario`, `dc_descuento`, `dc_subtotal`, `dc_estado`) VALUES
(8, 14, 5, 21, 1500, '60.00', '0.00', '3000.00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_peticion`
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
-- Estructura de tabla para la tabla `detalle_transferencia`
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
-- Volcado de datos para la tabla `detalle_transferencia`
--

INSERT INTO `detalle_transferencia` (`dt_id`, `tr_id`, `lm_origen_id`, `lm_destino_id`, `med_id`, `dt_numero_lote_origen`, `dt_cantidad_cajas`, `dt_cantidad_unidades`, `dt_precio_compra`, `dt_precio_venta`, `dt_subtotal_valorado`, `dt_estado`, `dt_creado_en`) VALUES
(4, 6, 21, 22, 5, 'MED-0001', 10, 300, '60.00', '1.00', '300.00', 1, '2025-12-10 02:00:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
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
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`dv_id`, `ve_id`, `med_id`, `lm_id`, `dv_cantidad`, `dv_unidad`, `dv_precio_unitario`, `dv_descuento`, `dv_subtotal`, `dv_estado`) VALUES
(9, 58, 5, 21, 100, 'unidad', '1.00', '0.00', '100.00', 1),
(10, 60, 5, 21, 100, 'unidad', '1.00', '0.00', '100.00', 1),
(11, 61, 5, 21, 100, 'unidad', '1.00', '0.00', '100.00', 1),
(12, 62, 5, 21, 300, 'unidad', '1.00', '0.00', '300.00', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devoluciones`
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
-- Volcado de datos para la tabla `devoluciones`
--

INSERT INTO `devoluciones` (`dev_id`, `ve_id`, `fa_id`, `su_id`, `us_id`, `dev_total`, `dev_cantidad`, `dev_motivo`, `dev_fecha`, `dev_estado`) VALUES
(4, 62, 59, 1, 1, '300.00', 300, 'se me cayo al agua', '2025-12-10 02:51:30', 'aceptada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
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
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`fa_id`, `ve_id`, `cl_id`, `us_id`, `su_id`, `fa_numero`, `fa_fecha_emision`, `fa_monto_total`, `fa_codigo_control`, `fa_cuf`, `fa_estado`, `fa_creado_en`) VALUES
(56, 58, NULL, 1, 1, 'F-1-20251210011531-231', '2025-12-10 01:15:31', '100.00', NULL, NULL, 1, '2025-12-10 01:15:31'),
(57, 60, NULL, 1, 1, 'F-1-20251210012756-545', '2025-12-10 01:27:56', '100.00', NULL, NULL, 1, '2025-12-10 01:27:56'),
(58, 61, NULL, 1, 1, 'F-1-20251210015609-451', '2025-12-10 01:56:09', '100.00', NULL, NULL, 1, '2025-12-10 01:56:09'),
(59, 62, NULL, 1, 1, 'F-1-20251210020745-753', '2025-12-10 02:07:45', '300.00', NULL, NULL, 1, '2025-12-10 02:07:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturacion_electronica`
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
-- Estructura de tabla para la tabla `forma_farmaceutica`
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
-- Volcado de datos para la tabla `forma_farmaceutica`
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
-- Estructura de tabla para la tabla `historial_lote`
--

CREATE TABLE `historial_lote` (
  `hl_id` bigint(20) UNSIGNED NOT NULL,
  `lm_id` bigint(20) UNSIGNED NOT NULL,
  `us_id` bigint(20) UNSIGNED DEFAULT NULL,
  `hl_accion` enum('creacion','activacion','ajuste','caducidad','terminacion','devolucion','bloqueo','desbloqueo','transferencia_salida','transferencia_entrada') NOT NULL,
  `hl_descripcion` text DEFAULT NULL,
  `hl_fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_lote`
--

INSERT INTO `historial_lote` (`hl_id`, `lm_id`, `us_id`, `hl_accion`, `hl_descripcion`, `hl_fecha`) VALUES
(74, 21, 1, 'creacion', 'Lote creado por compra #COMP-2025-0001 en estado \'activo\'.', '2025-12-10 01:03:09'),
(75, 21, 1, 'activacion', 'Lote activado automáticamente al registrar compra #COMP-2025-0001.', '2025-12-10 01:03:09'),
(76, 21, 1, '', 'Salida de 10 cajas por transferencia #TRANS-2025-0001', '2025-12-10 02:00:01'),
(77, 22, 3, '', 'Recepción de 10 cajas por transferencia #TRANS-2025-0001', '2025-12-10 02:02:07'),
(78, 21, 1, 'ajuste', 'Actualización de datos del lote (cantidades/precios/fecha de vencimiento)', '2025-12-10 15:17:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `informes`
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
-- Volcado de datos para la tabla `informes`
--

INSERT INTO `informes` (`inf_id`, `inf_nombre`, `inf_tipo`, `inf_usuario`, `inf_config`, `inf_creado_en`) VALUES
(89, 'Compra COMP-2025-0001 - juan ramon chochu - NIT: 6516576465', 'compra', 1, '{\"compra_id\":14,\"numero_compra\":\"COMP-2025-0001\",\"proveedor_id\":\"9\",\"laboratorio_id\":\"4\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-12-19\",\"numero_factura\":\"6546565465\",\"razon_social\":\"juan ramon chochu - NIT: 6516576465\",\"subtotal\":\"3000.00\",\"impuestos\":\"390.00\",\"total\":\"3390.00\",\"cantidad_lotes\":1,\"lotes\":[{\"medicamento_id\":\"5\",\"numero_lote\":\"MED-0001\",\"cantidad\":50,\"precio_compra\":60,\"precio_venta\":1,\"vencimiento\":\"2026-01-03\",\"activar_lote\":1}]}', '2025-12-10 01:03:09'),
(90, 'Nota Venta F-1-20251210011531-231', 'nota_venta', 1, '{\"ve_id\":58,\"fa_id\":56,\"ve_numero_documento\":\"SU1-1765343731\",\"fa_numero\":\"F-1-20251210011531-231\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"5\",\"lote_id\":\"21\",\"cantidad\":100,\"precio\":1,\"subtotal\":100}],\"subtotal\":100,\"total\":100,\"metodo_pago\":\"efectivo\"}', '2025-12-10 01:15:31'),
(91, 'Nota Venta F-1-20251210012756-545', 'nota_venta', 1, '{\"ve_id\":60,\"fa_id\":57,\"ve_numero_documento\":\"SU1-1765344476\",\"fa_numero\":\"F-1-20251210012756-545\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"5\",\"lote_id\":\"21\",\"cantidad\":100,\"precio\":1,\"subtotal\":100}],\"subtotal\":100,\"total\":100,\"metodo_pago\":\"efectivo\"}', '2025-12-10 01:27:56'),
(92, 'Nota Venta F-1-20251210015609-451', 'nota_venta', 1, '{\"ve_id\":61,\"fa_id\":58,\"ve_numero_documento\":\"SU1-1765346169\",\"fa_numero\":\"F-1-20251210015609-451\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"5\",\"lote_id\":\"21\",\"cantidad\":100,\"precio\":1,\"subtotal\":100}],\"subtotal\":100,\"total\":100,\"metodo_pago\":\"efectivo\"}', '2025-12-10 01:56:09'),
(93, 'Transferencia TRANS-2025-0001', 'transferencia', 1, '{\"tipo_informe\":\"transferencia_salida\",\"tr_id\":\"6\",\"tr_numero\":\"TRANS-2025-0001\",\"su_origen\":\"1\",\"us_emisor\":\"1\",\"total_items\":1,\"total_cajas\":10,\"total_unidades\":300,\"total_valorado\":300,\"tr_estado\":\"pendiente\"}', '2025-12-10 02:00:01'),
(94, 'Recepción de Transferencia TRANS-2025-0001', 'transferencia_recepcion', 3, '{\"tipo_informe\":\"transferencia_entrada\",\"tr_id\":6,\"tr_numero\":\"TRANS-2025-0001\",\"su_destino\":\"2\",\"us_receptor\":\"3\",\"total_items\":1,\"total_cajas\":\"10\",\"total_unidades\":\"300\",\"total_valorado\":\"300.00\",\"tr_estado\":\"aceptada\"}', '2025-12-10 02:02:07'),
(95, 'Nota Venta F-1-20251210020745-753', 'nota_venta', 1, '{\"ve_id\":62,\"fa_id\":59,\"ve_numero_documento\":\"SU1-1765346865\",\"fa_numero\":\"F-1-20251210020745-753\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"5\",\"lote_id\":\"21\",\"cantidad\":300,\"precio\":1,\"subtotal\":300}],\"subtotal\":300,\"total\":300,\"metodo_pago\":\"efectivo\"}', '2025-12-10 02:07:45'),
(96, 'Devolución #4 - Venta #62', 'devolucion', 1, '{\"dev_id\":4,\"ve_id\":62,\"fa_id\":59,\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"dv_id\":\"12\",\"med_id\":\"5\",\"lm_id\":\"21\",\"cantidad\":300,\"precio_unitario\":\"1.00\",\"motivo\":\"se me cayo al agua\",\"tipo\":\"cambio\"}],\"total_devolucion\":300,\"cantidad_items\":300,\"motivo\":\"se me cayo al agua\",\"fecha\":\"2025-12-10 02:51:30\"}', '2025-12-10 02:51:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `informes_compra`
--

CREATE TABLE `informes_compra` (
  `ic_id` int(11) NOT NULL,
  `co_id` int(11) NOT NULL,
  `pr_id` int(11) NOT NULL,
  `us_id` int(11) NOT NULL,
  `su_id` int(11) NOT NULL,
  `ic_numero_compra` varchar(100) NOT NULL,
  `ic_numero_factura` varchar(100) DEFAULT NULL,
  `ic_fecha_compra` datetime NOT NULL,
  `ic_subtotal` decimal(10,2) NOT NULL,
  `ic_impuestos` decimal(10,2) NOT NULL,
  `ic_total` decimal(10,2) NOT NULL,
  `ic_cantidad_lotes` int(11) NOT NULL,
  `ic_config_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ic_config_json`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `informes_compra`
--

INSERT INTO `informes_compra` (`ic_id`, `co_id`, `pr_id`, `us_id`, `su_id`, `ic_numero_compra`, `ic_numero_factura`, `ic_fecha_compra`, `ic_subtotal`, `ic_impuestos`, `ic_total`, `ic_cantidad_lotes`, `ic_config_json`) VALUES
(2, 14, 9, 1, 1, 'COMP-2025-0001', '6546565465', '2025-12-10 01:03:09', '3000.00', '390.00', '3390.00', 1, '{\"compra_id\":14,\"numero_compra\":\"COMP-2025-0001\",\"proveedor_id\":\"9\",\"laboratorio_id\":\"4\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-12-19\",\"numero_factura\":\"6546565465\",\"razon_social\":\"juan ramon chochu - NIT: 6516576465\",\"subtotal\":\"3000.00\",\"impuestos\":\"390.00\",\"total\":\"3390.00\",\"cantidad_lotes\":1,\"lotes\":[{\"medicamento_id\":\"5\",\"numero_lote\":\"MED-0001\",\"cantidad\":50,\"precio_compra\":60,\"precio_venta\":1,\"vencimiento\":\"2026-01-03\",\"activar_lote\":1}]}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventarios`
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
-- Volcado de datos para la tabla `inventarios`
--

INSERT INTO `inventarios` (`inv_id`, `med_id`, `su_id`, `inv_total_cajas`, `inv_total_unidades`, `inv_total_valorado`, `inv_minimo`, `inv_maximo`, `inv_codigo_barras`, `inv_stock_alerta`, `inv_actualizado_en`, `inv_creado_en`) VALUES
(23, 5, 1, 0, 0, '0.00', 0, NULL, NULL, 0, '2025-12-10 15:23:31', '2025-12-10 01:03:09'),
(24, 5, 2, 10, 300, '300.00', 300, NULL, NULL, 0, '2025-12-10 15:23:31', '2025-12-10 02:02:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `laboratorios`
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
-- Volcado de datos para la tabla `laboratorios`
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
-- Estructura de tabla para la tabla `lote_medicamento`
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
-- Volcado de datos para la tabla `lote_medicamento`
--

INSERT INTO `lote_medicamento` (`lm_id`, `med_id`, `su_id`, `pr_id`, `pr_id_compra`, `lm_numero_lote`, `lm_cant_caja`, `lm_cant_blister`, `lm_cant_unidad`, `lm_cant_actual_cajas`, `lm_cant_actual_unidades`, `lm_precio_compra`, `lm_precio_venta`, `lm_fecha_ingreso`, `lm_fecha_vencimiento`, `lm_estado`, `lm_creado_en`, `lm_actualizado_en`, `lm_origen_id`, `lm_tr_bloqueado`) VALUES
(21, 5, 1, 9, 14, 'MED-0001', 50, 5, 6, 10, 300, '60.00', '1.00', '2025-12-10 01:03:09', '2025-12-10', 'caducado', '2025-12-10 01:03:09', '2025-12-10 15:18:04', NULL, NULL),
(22, 5, 2, 9, 14, 'MED-0001', 10, 5, 6, 10, 300, '60.00', '1.00', '2025-12-10 02:02:07', '2026-01-03', 'activo', '2025-12-10 02:02:07', '2025-12-10 02:02:07', 21, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicamento`
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
-- Volcado de datos para la tabla `medicamento`
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
-- Estructura de tabla para la tabla `merma`
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
-- Volcado de datos para la tabla `merma`
--

INSERT INTO `merma` (`me_id`, `med_id`, `lm_id`, `su_id`, `us_id`, `me_cantidad`, `me_motivo`, `me_fecha`) VALUES
(3, 5, 21, 1, 1, 300, 'caducado', '2025-12-10 15:18:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_caja`
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
-- Volcado de datos para la tabla `movimiento_caja`
--

INSERT INTO `movimiento_caja` (`mc_id`, `caja_id`, `us_id`, `mc_tipo`, `mc_monto`, `mc_concepto`, `mc_referencia_tipo`, `mc_referencia_id`, `mc_fecha`) VALUES
(57, 15, 1, 'venta', '100.00', 'Venta SU1-1765343731', 'venta', 58, '2025-12-10 01:15:31'),
(58, 15, 1, 'venta', '100.00', 'Venta SU1-1765344476', 'venta', 60, '2025-12-10 01:27:56'),
(59, 15, 1, 'venta', '100.00', 'Venta SU1-1765346169', 'venta', 61, '2025-12-10 01:56:09'),
(60, 15, 1, 'venta', '300.00', 'Venta SU1-1765346865', 'venta', 62, '2025-12-10 02:07:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_inventario`
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
-- Volcado de datos para la tabla `movimiento_inventario`
--

INSERT INTO `movimiento_inventario` (`mi_id`, `lm_id`, `med_id`, `su_id`, `us_id`, `mi_tipo`, `mi_cantidad`, `mi_unidad`, `mi_referencia_tipo`, `mi_referencia_id`, `mi_motivo`, `mi_creado_en`, `mi_estado`) VALUES
(109, 21, 5, 1, 1, 'entrada', 1500, 'unidad', 'compra', 14, 'Ingreso por compra COMP-2025-0001', '2025-12-10 01:03:09', 1),
(110, 21, 5, 1, 1, 'salida', 100, 'unidad', 'venta', 0, 'Venta SU1-1765343731 (lm_id 21)', '2025-12-10 01:15:31', 1),
(111, 21, 5, 1, 1, 'salida', 100, 'unidad', 'venta', 0, 'Venta SU1-1765344476 (lm_id 21)', '2025-12-10 01:27:56', 1),
(112, 21, 5, 1, 1, 'salida', 100, 'unidad', 'venta', 0, 'Venta SU1-1765346169 (lm_id 21)', '2025-12-10 01:56:09', 1),
(113, 21, 5, 1, 1, 'salida', 300, 'unidad', 'transferencia_salida', 6, 'Transferencia #TRANS-2025-0001 hacia sucursal destino', '2025-12-10 02:00:01', 1),
(114, 22, 5, 2, 3, 'entrada', 300, 'unidad', 'transferencia_entrada', 6, 'Recepción de transferencia #TRANS-2025-0001 desde Sucursal Central', '2025-12-10 02:02:07', 1),
(115, 21, 5, 1, 1, 'salida', 300, 'unidad', 'venta', 0, 'Venta SU1-1765346865 (lm_id 21)', '2025-12-10 02:07:45', 1),
(116, 21, 5, 1, 1, 'baja', 300, 'unidad', 'devolucion', 0, 'Devolución: se me cayo al agua', '2025-12-10 02:51:30', 1),
(117, 21, 5, 1, 1, 'salida', 300, 'unidad', 'merma', 3, 'Merma: caducado', '2025-12-10 15:18:04', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `not_id` bigint(20) UNSIGNED NOT NULL,
  `not_tipo` enum('stock_bajo','proximo_caducar','ya_caducado','sin_stock','bajo_minimo','transferencia_pendiente') NOT NULL,
  `not_referencia_id` varchar(100) DEFAULT NULL COMMENT 'ID de referencia (med_id, lm_id, tr_id, etc)',
  `not_su_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Sucursal donde aplica la notificación',
  `not_titulo` varchar(100) NOT NULL,
  `not_mensaje` text NOT NULL,
  `not_icono` varchar(50) NOT NULL DEFAULT 'alert-circle-outline',
  `not_color` varchar(20) DEFAULT '#ff9800',
  `not_leida` tinyint(1) NOT NULL DEFAULT 0,
  `not_descartada` tinyint(1) NOT NULL DEFAULT 0,
  `not_fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `not_fecha_lectura` datetime DEFAULT NULL,
  `not_aplicable_rol_1` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Visible para Rol 1 (Admin)',
  `not_aplicable_rol_2` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Visible para Rol 2 (Gerente)',
  `not_aplicable_rol_3` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Visible para Rol 3 (Caja)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`not_id`, `not_tipo`, `not_referencia_id`, `not_su_id`, `not_titulo`, `not_mensaje`, `not_icono`, `not_color`, `not_leida`, `not_descartada`, `not_fecha_creacion`, `not_fecha_lectura`, `not_aplicable_rol_1`, `not_aplicable_rol_2`, `not_aplicable_rol_3`) VALUES
(27, 'proximo_caducar', '21', 1, 'Próximo a Caducar', 'Omeprazol Lote: MED-0001 caduca en 24 días', 'alert-circle-outline', '#ff5722', 0, 1, '2025-12-10 01:03:11', NULL, 1, 1, 0),
(28, 'transferencia_pendiente', '6', 2, 'Transferencia Pendiente', 'Transferencia #TRANS-2025-0001 de Sucursal Central pendiente de recepcionar', 'swap-horizontal-outline', '#2196f3', 0, 1, '2025-12-10 02:00:22', NULL, 1, 1, 0),
(29, 'proximo_caducar', '22', 2, 'Próximo a Caducar', 'Omeprazol Lote: MED-0001 caduca en 24 días', 'alert-circle-outline', '#ff5722', 0, 1, '2025-12-10 02:02:38', NULL, 1, 1, 0),
(30, 'proximo_caducar', '21', 1, 'Próximo a Caducar', 'Omeprazol Lote: MED-0001 caduca en 24 días', 'alert-circle-outline', '#ff5722', 0, 1, '2025-12-10 02:52:17', NULL, 1, 1, 0),
(31, 'sin_stock', '5_1', 1, 'Sin Stock', 'Omeprazol en Sucursal Central no tiene existencias', 'close-outline', '#f44336', 0, 1, '2025-12-10 15:18:06', NULL, 1, 1, 0),
(32, 'stock_bajo', '5_2', 2, 'Stock Bajo', 'Omeprazol - sucursal 2: 300 unidades', 'warning-outline', '#ff9800', 0, 1, '2025-12-10 15:19:52', NULL, 1, 1, 0),
(33, 'stock_bajo', '5_2', 2, 'Stock Bajo', 'Omeprazol - sucursal 2: 300 unidades', 'warning-outline', '#ff9800', 0, 1, '2025-12-10 20:34:46', NULL, 1, 1, 0),
(34, 'proximo_caducar', '22', 2, 'Próximo a Caducar', 'Omeprazol Lote: MED-0001 caduca en 24 días', 'alert-circle-outline', '#ff5722', 0, 1, '2025-12-10 20:34:47', NULL, 1, 1, 0),
(35, 'sin_stock', '5_1', 1, 'Sin Stock', 'Omeprazol en Sucursal Central no tiene existencias', 'close-outline', '#f44336', 0, 1, '2025-12-10 20:34:47', NULL, 1, 1, 0),
(36, 'proximo_caducar', '22', 2, 'Próximo a Caducar', 'Omeprazol Lote: MED-0001 caduca en 24 días', 'alert-circle-outline', '#ff5722', 0, 1, '2025-12-10 20:42:17', NULL, 1, 1, 0),
(37, 'stock_bajo', '5_2', 2, 'Stock Bajo', 'Omeprazol - sucursal 2: 300 unidades', 'warning-outline', '#ff9800', 0, 0, '2025-12-10 20:44:43', NULL, 1, 1, 0),
(38, 'proximo_caducar', '22', 2, 'Próximo a Caducar', 'Omeprazol Lote: MED-0001 caduca en 24 días', 'alert-circle-outline', '#ff5722', 0, 0, '2025-12-10 20:44:43', NULL, 1, 1, 0),
(39, 'sin_stock', '5_1', 1, 'Sin Stock', 'Omeprazol en Sucursal Central no tiene existencias', 'close-outline', '#f44336', 0, 0, '2025-12-10 20:44:43', NULL, 1, 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `peticiones`
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
-- Estructura de tabla para la tabla `proveedores`
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
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`pr_id`, `pr_nombres`, `pr_apellido_paterno`, `pr_apellido_materno`, `pr_telefono`, `pr_nit`, `pr_direccion`, `pr_creado_en`, `pr_actualizado_en`, `pr_estado`) VALUES
(9, 'juan ramon chochu', 'portillo', 'Sanahoria', '5646764', '6516576465', 'por ahi qu eno recuerdo', '2025-12-10 00:50:19', '2025-12-10 01:01:12', 1),
(10, 'ronso quiñones SRL', NULL, NULL, '', '654846465465', '', '2025-12-10 00:58:38', '2025-12-10 00:58:38', 1),
(11, 'medibol', NULL, NULL, '5432124423', '4235263643', 'afasdfasdff 232', '2025-12-10 01:00:12', '2025-12-10 01:00:12', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores_laboratorio`
--

CREATE TABLE `proveedores_laboratorio` (
  `pl_id` bigint(20) UNSIGNED NOT NULL,
  `pr_id` bigint(20) UNSIGNED NOT NULL,
  `la_id` bigint(20) UNSIGNED NOT NULL,
  `pl_fecha_creado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
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
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`ro_id`, `ro_nombre`, `ro_descripcion`, `ro_creado_en`, `ro_actualizado_en`, `ro_estado`) VALUES
(1, 'admin', 'Administrador del sistema con todos los permisos', '2025-11-06 10:17:03', '2025-11-06 10:17:03', 1),
(2, 'gerente', 'Gerente de sucursal', '2025-11-06 10:17:03', '2025-11-06 10:17:03', 1),
(3, 'vendedor', 'Usuario de caja / ventas', '2025-11-06 10:17:03', '2025-11-06 10:17:03', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sucursales`
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
-- Volcado de datos para la tabla `sucursales`
--

INSERT INTO `sucursales` (`su_id`, `su_nombre`, `su_direccion`, `su_telefono`, `su_creado_en`, `su_actualizado_en`, `su_estado`) VALUES
(1, 'Sucursal Central', 'Av. Principal 123, Ciudad', '+591-2-1234567', '2025-11-06 10:17:03', '2025-11-06 10:17:03', 1),
(2, 'sucursal 2', 'calle siempre viva', '123456789', '2025-11-20 21:18:42', '2025-12-06 14:55:33', 1),
(3, 'dasd', 'fsfsdfads', '21312', '2025-12-02 20:37:20', '2025-12-04 20:34:54', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transferencias`
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
-- Volcado de datos para la tabla `transferencias`
--

INSERT INTO `transferencias` (`tr_id`, `tr_numero`, `su_origen_id`, `su_destino_id`, `us_emisor_id`, `us_receptor_id`, `tr_total_items`, `tr_total_cajas`, `tr_total_unidades`, `tr_total_valorado`, `tr_estado`, `tr_observaciones`, `tr_motivo_rechazo`, `tr_fecha_envio`, `tr_fecha_respuesta`, `tr_creado_en`, `tr_actualizado_en`) VALUES
(6, 'TRANS-2025-0001', 1, 2, 1, 3, 1, 10, 300, '300.00', 'aceptada', 'ovserbacion de nose que', NULL, '2025-12-10 02:00:01', '2025-12-10 02:02:07', '2025-12-10 02:00:01', '2025-12-10 02:02:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uso_farmacologico`
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
-- Volcado de datos para la tabla `uso_farmacologico`
--

INSERT INTO `uso_farmacologico` (`uf_id`, `uf_nombre`, `uf_imagen`, `uf_creado_en`, `uf_actualizado_en`, `uf_estado`) VALUES
(1, 'Analgésico', NULL, '2025-11-06 11:06:03', '2025-12-05 20:55:49', 1),
(2, 'Antibiótico', 'antibiotico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(3, 'Antiinflamatorio', 'antiinflamatorio.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(4, 'Antihistamínico', 'antihistaminico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(5, 'Antipirético', 'antipiretico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(6, 'Antiséptico', 'antiseptico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(7, 'Cardiovascular', 'cardiovascular.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(8, 'Digestivo', 'digestivo.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(9, 'Dermatológico', 'dermatologico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(10, 'Respiratorio', 'respiratorio.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(11, 'aviar', '', '2025-11-29 14:17:38', '2025-11-29 14:17:38', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
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
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`us_id`, `us_nombres`, `us_apellido_paterno`, `us_apellido_materno`, `us_numero_carnet`, `us_telefono`, `us_correo`, `us_direccion`, `us_username`, `us_password_hash`, `us_token_recuperacion`, `us_token_expiracion`, `us_creado_en`, `us_actualizado_en`, `us_estado`, `su_id`, `ro_id`) VALUES
(1, 'admin', 'admin', 'admin', '000000000', '111111111', 'admin@admin.com', 'admin calle admin', 'admin', 'dlo5ZmZvbmRjME41dGlDY01tTGcrUT09', NULL, NULL, '2025-11-06 10:17:03', '2025-12-04 12:41:09', 1, 1, 1),
(2, 'usuario', 'usuario', 'usuario', '1235497866656', '122565165464', 'usuario@usuario.usuario', 'usuariousuario', 'usuario', 'Q0oxTTdMNktnMzhoQjBDOXFJWXI1Zz09', NULL, NULL, '2025-11-20 21:30:31', '2025-12-10 15:31:01', 1, 2, 3),
(3, 'gerente', 'gerente', 'gerente', '123321321', '', '', 'gerente', 'gerente', 'ZFA3UHhUdGwrVERjWjVCSmhWaFJpdz09', NULL, NULL, '2025-11-27 18:05:22', '2025-12-04 13:20:18', 1, 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
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
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`ve_id`, `ve_numero_documento`, `ve_fecha_emision`, `cl_id`, `us_id`, `su_id`, `caja_id`, `ve_subtotal`, `ve_impuesto`, `ve_total`, `ve_actualizado_en`, `ve_metodo_pago`, `ve_tipo_documento`, `ve_estado_documento`, `ve_numero_control`, `ve_estado`) VALUES
(58, 'SU1-1765343731', '2025-12-10 01:15:31', NULL, 1, 1, 15, '100.00', '0.00', '100.00', '2025-12-10 01:15:31', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(60, 'SU1-1765344476', '2025-12-10 01:27:56', NULL, 1, 1, 15, '100.00', '0.00', '100.00', '2025-12-10 01:27:56', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(61, 'SU1-1765346169', '2025-12-10 01:56:09', NULL, 1, 1, 15, '100.00', '0.00', '100.00', '2025-12-10 01:56:09', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(62, 'SU1-1765346865', '2025-12-10 02:07:45', NULL, 1, 1, 15, '300.00', '0.00', '300.00', '2025-12-10 02:51:30', 'efectivo', 'nota de venta', 'devuelto', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `via_de_administracion`
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
-- Volcado de datos para la tabla `via_de_administracion`
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
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `balance_precios`
--
ALTER TABLE `balance_precios`
  ADD PRIMARY KEY (`bp_id`),
  ADD KEY `idx_bp_lote` (`lm_id`),
  ADD KEY `idx_bp_usuario` (`us_id`),
  ADD KEY `idx_bp_fecha` (`bp_creado_en`);

--
-- Indices de la tabla `caja`
--
ALTER TABLE `caja`
  ADD PRIMARY KEY (`caja_id`),
  ADD KEY `fk_caja_sucursal` (`su_id`),
  ADD KEY `fk_caja_usuario` (`us_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`cl_id`),
  ADD KEY `ix_clientes_carnet` (`cl_carnet`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`co_id`),
  ADD KEY `fk_compras_laboratorios` (`la_id`),
  ADD KEY `fk_compras_proveedores` (`pr_id`),
  ADD KEY `fk_compras_sucursales` (`su_id`),
  ADD KEY `fk_compras_usuarios` (`us_id`);

--
-- Indices de la tabla `configuracion_empresa`
--
ALTER TABLE `configuracion_empresa`
  ADD PRIMARY KEY (`ce_id`);

--
-- Indices de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD PRIMARY KEY (`dc_id`),
  ADD KEY `ix_dc_co` (`co_id`),
  ADD KEY `ix_dc_med` (`med_id`),
  ADD KEY `ix_dc_lm` (`lm_id`);

--
-- Indices de la tabla `detalle_peticion`
--
ALTER TABLE `detalle_peticion`
  ADD PRIMARY KEY (`dp_id`),
  ADD KEY `idx_dp_peticion` (`pe_id`),
  ADD KEY `idx_dp_medicamento` (`med_id`);

--
-- Indices de la tabla `detalle_transferencia`
--
ALTER TABLE `detalle_transferencia`
  ADD PRIMARY KEY (`dt_id`),
  ADD KEY `idx_dt_tr` (`tr_id`),
  ADD KEY `idx_dt_lm_origen` (`lm_origen_id`),
  ADD KEY `idx_dt_lm_destino` (`lm_destino_id`),
  ADD KEY `idx_dt_medicamento` (`med_id`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`dv_id`),
  ADD KEY `ix_dv_ve` (`ve_id`),
  ADD KEY `ix_dv_med` (`med_id`),
  ADD KEY `ix_dv_lm` (`lm_id`);

--
-- Indices de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD PRIMARY KEY (`dev_id`),
  ADD KEY `fk_dev_ve` (`ve_id`),
  ADD KEY `fk_dev_fa` (`fa_id`),
  ADD KEY `fk_dev_su` (`su_id`),
  ADD KEY `fk_dev_us` (`us_id`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`fa_id`),
  ADD KEY `fk_factura_venta` (`ve_id`),
  ADD KEY `fk_factura_cliente` (`cl_id`),
  ADD KEY `fk_factura_usuario` (`us_id`),
  ADD KEY `fk_factura_sucursal` (`su_id`);

--
-- Indices de la tabla `facturacion_electronica`
--
ALTER TABLE `facturacion_electronica`
  ADD PRIMARY KEY (`fe_id`),
  ADD KEY `fk_fe_fa` (`fa_id`);

--
-- Indices de la tabla `forma_farmaceutica`
--
ALTER TABLE `forma_farmaceutica`
  ADD PRIMARY KEY (`ff_id`),
  ADD UNIQUE KEY `ux_forma_nombre` (`ff_nombre`);

--
-- Indices de la tabla `historial_lote`
--
ALTER TABLE `historial_lote`
  ADD PRIMARY KEY (`hl_id`),
  ADD KEY `fk_historial_lote_lm` (`lm_id`),
  ADD KEY `fk_historial_lote_us` (`us_id`);

--
-- Indices de la tabla `informes`
--
ALTER TABLE `informes`
  ADD PRIMARY KEY (`inf_id`),
  ADD KEY `fk_inf_usuario` (`inf_usuario`);

--
-- Indices de la tabla `informes_compra`
--
ALTER TABLE `informes_compra`
  ADD PRIMARY KEY (`ic_id`),
  ADD KEY `co_id` (`co_id`),
  ADD KEY `pr_id` (`pr_id`),
  ADD KEY `us_id` (`us_id`),
  ADD KEY `su_id` (`su_id`);

--
-- Indices de la tabla `inventarios`
--
ALTER TABLE `inventarios`
  ADD PRIMARY KEY (`inv_id`),
  ADD UNIQUE KEY `ux_inv_su_med` (`su_id`,`med_id`),
  ADD KEY `ix_inv_med` (`med_id`);

--
-- Indices de la tabla `laboratorios`
--
ALTER TABLE `laboratorios`
  ADD PRIMARY KEY (`la_id`),
  ADD UNIQUE KEY `ux_laboratorios_nombre` (`la_nombre_comercial`);

--
-- Indices de la tabla `lote_medicamento`
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
-- Indices de la tabla `medicamento`
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
-- Indices de la tabla `merma`
--
ALTER TABLE `merma`
  ADD PRIMARY KEY (`me_id`),
  ADD KEY `fk_me_med` (`med_id`),
  ADD KEY `fk_me_lm` (`lm_id`),
  ADD KEY `fk_me_su` (`su_id`),
  ADD KEY `fk_me_us` (`us_id`);

--
-- Indices de la tabla `movimiento_caja`
--
ALTER TABLE `movimiento_caja`
  ADD PRIMARY KEY (`mc_id`),
  ADD KEY `ix_mc_caja` (`caja_id`),
  ADD KEY `fk_mc_us` (`us_id`);

--
-- Indices de la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD PRIMARY KEY (`mi_id`),
  ADD KEY `ix_mi_lm` (`lm_id`),
  ADD KEY `ix_mi_med` (`med_id`),
  ADD KEY `ix_mi_su` (`su_id`),
  ADD KEY `ix_mi_us` (`us_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`not_id`),
  ADD KEY `not_su_id` (`not_su_id`),
  ADD KEY `not_tipo` (`not_tipo`),
  ADD KEY `not_leida` (`not_leida`),
  ADD KEY `not_descartada` (`not_descartada`),
  ADD KEY `not_fecha_creacion` (`not_fecha_creacion`),
  ADD KEY `idx_not_rol_lectura` (`not_aplicable_rol_1`,`not_aplicable_rol_2`,`not_leida`),
  ADD KEY `idx_not_su_id_tipo` (`not_su_id`,`not_tipo`);

--
-- Indices de la tabla `peticiones`
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
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`pr_id`),
  ADD KEY `ix_proveedores_nit` (`pr_nit`);

--
-- Indices de la tabla `proveedores_laboratorio`
--
ALTER TABLE `proveedores_laboratorio`
  ADD PRIMARY KEY (`pl_id`),
  ADD UNIQUE KEY `ux_pl_pr_la` (`pr_id`,`la_id`),
  ADD KEY `fk_pl_pr` (`pr_id`),
  ADD KEY `fk_pl_la` (`la_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`ro_id`),
  ADD UNIQUE KEY `ux_roles_nombre` (`ro_nombre`);

--
-- Indices de la tabla `sucursales`
--
ALTER TABLE `sucursales`
  ADD PRIMARY KEY (`su_id`),
  ADD UNIQUE KEY `ux_sucursales_nombre` (`su_nombre`);

--
-- Indices de la tabla `transferencias`
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
-- Indices de la tabla `uso_farmacologico`
--
ALTER TABLE `uso_farmacologico`
  ADD PRIMARY KEY (`uf_id`),
  ADD UNIQUE KEY `ux_uso_nombre` (`uf_nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`us_id`),
  ADD UNIQUE KEY `ux_usuarios_username` (`us_username`),
  ADD KEY `fk_usuarios_sucursales` (`su_id`),
  ADD KEY `fk_usuarios_roles` (`ro_id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`ve_id`),
  ADD KEY `fk_ventas_clientes` (`cl_id`),
  ADD KEY `fk_ventas_sucursales` (`su_id`),
  ADD KEY `fk_ventas_usuarios` (`us_id`),
  ADD KEY `fk_ventas_caja` (`caja_id`);

--
-- Indices de la tabla `via_de_administracion`
--
ALTER TABLE `via_de_administracion`
  ADD PRIMARY KEY (`vd_id`),
  ADD UNIQUE KEY `ux_via_nombre` (`vd_nombre`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `balance_precios`
--
ALTER TABLE `balance_precios`
  MODIFY `bp_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `caja`
--
ALTER TABLE `caja`
  MODIFY `caja_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `cl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `co_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `configuracion_empresa`
--
ALTER TABLE `configuracion_empresa`
  MODIFY `ce_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  MODIFY `dc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `detalle_peticion`
--
ALTER TABLE `detalle_peticion`
  MODIFY `dp_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_transferencia`
--
ALTER TABLE `detalle_transferencia`
  MODIFY `dt_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `dv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  MODIFY `dev_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `fa_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de la tabla `facturacion_electronica`
--
ALTER TABLE `facturacion_electronica`
  MODIFY `fe_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `forma_farmaceutica`
--
ALTER TABLE `forma_farmaceutica`
  MODIFY `ff_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `historial_lote`
--
ALTER TABLE `historial_lote`
  MODIFY `hl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `informes`
--
ALTER TABLE `informes`
  MODIFY `inf_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT de la tabla `informes_compra`
--
ALTER TABLE `informes_compra`
  MODIFY `ic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `inventarios`
--
ALTER TABLE `inventarios`
  MODIFY `inv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `laboratorios`
--
ALTER TABLE `laboratorios`
  MODIFY `la_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `lote_medicamento`
--
ALTER TABLE `lote_medicamento`
  MODIFY `lm_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `medicamento`
--
ALTER TABLE `medicamento`
  MODIFY `med_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `merma`
--
ALTER TABLE `merma`
  MODIFY `me_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `movimiento_caja`
--
ALTER TABLE `movimiento_caja`
  MODIFY `mc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  MODIFY `mi_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `not_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `peticiones`
--
ALTER TABLE `peticiones`
  MODIFY `pe_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `pr_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `proveedores_laboratorio`
--
ALTER TABLE `proveedores_laboratorio`
  MODIFY `pl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `ro_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sucursales`
--
ALTER TABLE `sucursales`
  MODIFY `su_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `transferencias`
--
ALTER TABLE `transferencias`
  MODIFY `tr_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `uso_farmacologico`
--
ALTER TABLE `uso_farmacologico`
  MODIFY `uf_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `us_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `ve_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `via_de_administracion`
--
ALTER TABLE `via_de_administracion`
  MODIFY `vd_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `balance_precios`
--
ALTER TABLE `balance_precios`
  ADD CONSTRAINT `fk_bp_lote` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bp_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `caja`
--
ALTER TABLE `caja`
  ADD CONSTRAINT `fk_caja_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_caja_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `fk_compras_laboratorio` FOREIGN KEY (`la_id`) REFERENCES `laboratorios` (`la_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_proveedor` FOREIGN KEY (`pr_id`) REFERENCES `proveedores` (`pr_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD CONSTRAINT `fk_dc_compras` FOREIGN KEY (`co_id`) REFERENCES `compras` (`co_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dc_lote` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dc_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_peticion`
--
ALTER TABLE `detalle_peticion`
  ADD CONSTRAINT `fk_dp_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dp_peticion` FOREIGN KEY (`pe_id`) REFERENCES `peticiones` (`pe_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_transferencia`
--
ALTER TABLE `detalle_transferencia`
  ADD CONSTRAINT `fk_dt_lm_destino` FOREIGN KEY (`lm_destino_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dt_lm_origen` FOREIGN KEY (`lm_origen_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dt_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dt_transferencia` FOREIGN KEY (`tr_id`) REFERENCES `transferencias` (`tr_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `fk_dv_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dv_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dv_venta` FOREIGN KEY (`ve_id`) REFERENCES `ventas` (`ve_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD CONSTRAINT `fk_dev_fa` FOREIGN KEY (`fa_id`) REFERENCES `factura` (`fa_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dev_su` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dev_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dev_ve` FOREIGN KEY (`ve_id`) REFERENCES `ventas` (`ve_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `fk_factura_cliente` FOREIGN KEY (`cl_id`) REFERENCES `clientes` (`cl_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_venta` FOREIGN KEY (`ve_id`) REFERENCES `ventas` (`ve_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `facturacion_electronica`
--
ALTER TABLE `facturacion_electronica`
  ADD CONSTRAINT `fk_fe_fa` FOREIGN KEY (`fa_id`) REFERENCES `factura` (`fa_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_lote`
--
ALTER TABLE `historial_lote`
  ADD CONSTRAINT `fk_historial_lote_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historial_lote_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `informes`
--
ALTER TABLE `informes`
  ADD CONSTRAINT `fk_inf_usuario` FOREIGN KEY (`inf_usuario`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `inventarios`
--
ALTER TABLE `inventarios`
  ADD CONSTRAINT `fk_inv_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inv_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `lote_medicamento`
--
ALTER TABLE `lote_medicamento`
  ADD CONSTRAINT `fk_lm_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_origen` FOREIGN KEY (`lm_origen_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_proveedor` FOREIGN KEY (`pr_id`) REFERENCES `proveedores` (`pr_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_tr_bloqueado` FOREIGN KEY (`lm_tr_bloqueado`) REFERENCES `transferencias` (`tr_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `medicamento`
--
ALTER TABLE `medicamento`
  ADD CONSTRAINT `fk_medicamento_forma` FOREIGN KEY (`ff_id`) REFERENCES `forma_farmaceutica` (`ff_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_laboratorio` FOREIGN KEY (`la_id`) REFERENCES `laboratorios` (`la_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_uso` FOREIGN KEY (`uf_id`) REFERENCES `uso_farmacologico` (`uf_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_via` FOREIGN KEY (`vd_id`) REFERENCES `via_de_administracion` (`vd_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `merma`
--
ALTER TABLE `merma`
  ADD CONSTRAINT `fk_me_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_me_med` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_me_su` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_me_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `movimiento_caja`
--
ALTER TABLE `movimiento_caja`
  ADD CONSTRAINT `fk_mc_caja` FOREIGN KEY (`caja_id`) REFERENCES `caja` (`caja_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mc_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD CONSTRAINT `fk_mi_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mi_med` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mi_su` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mi_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`not_su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `peticiones`
--
ALTER TABLE `peticiones`
  ADD CONSTRAINT `fk_pe_su_destino` FOREIGN KEY (`su_destino_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pe_su_solicitante` FOREIGN KEY (`su_solicitante_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pe_transferencia` FOREIGN KEY (`tr_id`) REFERENCES `transferencias` (`tr_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pe_us_respondedor` FOREIGN KEY (`us_respondedor_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pe_us_solicitante` FOREIGN KEY (`us_solicitante_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `proveedores_laboratorio`
--
ALTER TABLE `proveedores_laboratorio`
  ADD CONSTRAINT `fk_pl_la` FOREIGN KEY (`la_id`) REFERENCES `laboratorios` (`la_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pl_pr` FOREIGN KEY (`pr_id`) REFERENCES `proveedores` (`pr_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `transferencias`
--
ALTER TABLE `transferencias`
  ADD CONSTRAINT `fk_tr_su_destino` FOREIGN KEY (`su_destino_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tr_su_origen` FOREIGN KEY (`su_origen_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tr_us_emisor` FOREIGN KEY (`us_emisor_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tr_us_receptor` FOREIGN KEY (`us_receptor_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`ro_id`) REFERENCES `roles` (`ro_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_sucursales` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `ventas`
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
