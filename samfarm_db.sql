-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-11-2025 a las 21:45:40
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
(7, 1, 1, 'Caja admin', '200.00', NULL, 1, '2025-11-26 14:44:32', NULL, NULL),
(8, 2, 3, 'Caja gerente', '200.00', NULL, 1, '2025-11-27 20:27:35', NULL, NULL),
(9, 1, 3, 'Caja gerente', '200.00', NULL, 1, '2025-11-27 20:28:45', NULL, NULL);

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
(1, 'dadasda', 'asd', 'dasdas', '423423', '', 'dfsdfsdfsd', '234234', '2025-11-16 18:31:40', '2025-11-16 18:31:40', 1),
(2, 'dadasda', 'asd', 'dasdas', '423423', '', 'dfsdfsdfsd', '234234', '2025-11-16 18:31:51', '2025-11-16 18:31:51', 1),
(3, 'juan', 'quiroga', 'Mamani', '', '', '', '1123123', '2025-11-16 18:35:17', '2025-11-26 21:59:35', 1),
(4, 'juan', 'quiroga', '', '', '', '', '', '2025-11-16 18:36:47', '2025-11-26 19:01:52', 0),
(5, 'teodoro', 'jasinto', '', '', '', '', '', '2025-11-16 18:37:19', '2025-11-16 18:37:19', 1),
(6, 'teodoro', 'gusman', '', '', '', '', '', '2025-11-16 18:37:45', '2025-11-16 18:37:45', 1),
(7, 'jorge', 'gusman', '', '', '', '', '', '2025-11-16 18:38:13', '2025-11-25 21:42:13', 0),
(8, 'Fabricio', 'romero', '', '', '', '', '0000000000', '2025-11-18 10:31:15', '2025-11-18 10:31:15', 1),
(9, 'asdas', 'dasd', '', '', '', '', '', '2025-11-26 20:54:51', '2025-11-26 20:54:51', 1),
(10, 'sads', 'dasdsada', '', '', '', '', '', '2025-11-26 20:59:55', '2025-11-26 20:59:55', 1),
(11, 'fssd', 'sfsd', '', '', '', '', '21324651', '2025-11-26 21:32:15', '2025-11-26 21:32:15', 1),
(12, 'fadfad', 'fasdfasdf', '', '', '', '', '353453453', '2025-11-26 21:34:30', '2025-11-26 21:34:30', 1);

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
(1, 'COMP-2025-0001', '2025-11-13 13:55:49', 7, 1, 1, 1, '42.00', '5.46', '47.46', '242342323423', '2025-11-30', 'compra', NULL, 'jose', '2025-11-13 13:55:49', '2025-11-13 13:55:49', 1),
(2, 'COMP-2025-0002', '2025-11-16 13:30:17', 2, 1, 1, 3, '54756.00', '7118.28', '61874.28', '2342', '2025-11-22', 'compra', NULL, 'fsdfsdf', '2025-11-16 13:30:17', '2025-11-16 13:30:17', 1),
(3, 'COMP-2025-0003', '2025-11-18 10:26:29', 4, 1, 1, 1, '100.00', '13.00', '113.00', '12312312', '2025-11-28', 'compra', NULL, 'jose', '2025-11-18 10:26:29', '2025-11-18 10:26:29', 1),
(4, 'COMP-2025-0004', '2025-11-19 18:13:28', 3, 1, 1, 3, '4500.00', '585.00', '5085.00', '123123', '2025-11-30', 'compra', NULL, 'dadasda', '2025-11-19 18:13:28', '2025-11-19 18:13:28', 1),
(5, 'COMP-2025-0005', '2025-11-19 18:18:05', 4, 1, 1, 2, '200.00', '26.00', '226.00', '3213', '2025-11-14', 'compra', NULL, 'asaasdas', '2025-11-19 18:18:05', '2025-11-19 18:18:05', 1),
(6, 'COMP-2025-0006', '2025-11-19 19:16:53', 6, 1, 1, 4, '900.00', '117.00', '1017.00', '423423', '2025-11-22', 'compra', NULL, '423423423', '2025-11-19 19:16:53', '2025-11-19 19:16:53', 1),
(7, 'COMP-2025-0007', '2025-11-19 20:31:32', 6, 1, 1, 3, '3200.00', '416.00', '3616.00', '65484', '2025-11-28', 'compra', NULL, 'jjbhjg', '2025-11-19 20:31:32', '2025-11-19 20:31:32', 1),
(8, 'COMP-2025-0008', '2025-11-22 16:37:12', 6, 1, 1, 6, '4500.00', '585.00', '5085.00', '132132154541', '2025-11-22', 'compra', NULL, 'ugo dabila', '2025-11-22 16:37:12', '2025-11-22 16:37:12', 1);

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
(1, 'SAMFARM - Sistema de Gestión Farmacéutica', '123456789', 'Av. Principal #123, La Paz, Bolivia', '591-2-1234567', 'contacto@samfarm.com', NULL, '2025-11-19 01:22:36', NULL);

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
(1, 1, 5, 1, 6, '7.00', '0.00', '42.00', 1),
(2, 2, 4, 2, 234, '234.00', '0.00', '54756.00', 1),
(3, 3, 4, 3, 400, '10.00', '0.00', '100.00', 1),
(4, 4, 8, 4, 50, '10.00', '0.00', '500.00', 1),
(5, 4, 9, 5, 3840, '50.00', '0.00', '4000.00', 1),
(6, 5, 6, 6, 40, '5.00', '0.00', '200.00', 1),
(7, 6, 1, 7, 10, '50.00', '0.00', '500.00', 1),
(8, 6, 1, 8, 10, '40.00', '0.00', '400.00', 1),
(9, 7, 1, 9, 4800, '40.00', '0.00', '3200.00', 1),
(10, 8, 3, 10, 2700, '50.00', '0.00', '4500.00', 1);

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
(1, 1, 4, NULL, 10, 'unidad', '1.00', '0.00', '10.00', 1),
(14, 14, 5, 1, 1, 'unidad', '4.00', '0.00', '4.00', 0),
(15, 15, 4, 2, 10, 'unidad', '1.00', '0.00', '10.00', 1),
(16, 16, 4, 2, 10, 'unidad', '1.00', '0.00', '10.00', 1),
(17, 17, 4, 2, 1, 'unidad', '1.00', '0.00', '1.00', 1),
(18, 18, 5, 1, 5, 'unidad', '4.00', '0.00', '20.00', 1),
(19, 20, 4, 2, 213, 'unidad', '1.00', '0.00', '213.00', 1),
(20, 20, 4, 3, 400, 'unidad', '1.00', '0.00', '400.00', 1),
(21, 21, 1, 7, 4, 'unidad', '55.00', '0.00', '220.00', 1),
(22, 22, 9, 5, 2, 'unidad', '4.00', '0.00', '8.00', 1),
(23, 23, 8, 4, 5, 'unidad', '12.00', '0.00', '60.00', 1),
(24, 24, 6, 6, 3, 'unidad', '6.00', '0.00', '18.00', 1),
(25, 25, 1, 7, 1, 'unidad', '50.00', '0.00', '50.00', 1),
(26, 26, 1, 7, 3, 'unidad', '50.00', '0.00', '150.00', 1),
(27, 27, 1, 7, 2, 'unidad', '50.00', '0.00', '100.00', 1),
(28, 27, 1, 8, 4, 'unidad', '50.00', '0.00', '200.00', 1),
(29, 28, 6, 6, 1, 'unidad', '6.00', '0.00', '6.00', 1),
(30, 29, 6, 6, 36, 'unidad', '6.00', '0.00', '216.00', 1),
(37, 36, 1, 8, 1, 'unidad', '2.00', '0.00', '2.00', 1),
(38, 37, 9, 5, 1500, 'unidad', '4.00', '0.00', '6000.00', 1),
(39, 38, 8, 4, 1, 'unidad', '12.00', '0.00', '12.00', 1),
(40, 38, 1, 8, 1, 'unidad', '2.00', '0.00', '2.00', 1),
(41, 38, 9, 5, 1, 'unidad', '4.00', '0.00', '4.00', 1),
(42, 39, 1, 8, 4, 'unidad', '2.00', '0.00', '8.00', 1),
(43, 40, 1, 9, 1, 'unidad', '2.00', '0.00', '2.00', 1),
(44, 41, 1, 9, 9, 'unidad', '2.00', '0.00', '18.00', 1),
(45, 42, 1, 9, 1, 'unidad', '2.00', '0.00', '2.00', 1),
(46, 42, 9, 5, 1, 'unidad', '4.00', '0.00', '4.00', 1),
(47, 43, 9, 5, 6, 'unidad', '4.00', '0.00', '24.00', 1),
(48, 44, 9, 5, 1, 'unidad', '4.00', '0.00', '4.00', 1),
(49, 45, 3, 10, 1, 'unidad', '1.00', '0.00', '1.00', 0),
(50, 46, 9, 5, 4, 'unidad', '4.00', '0.00', '16.00', 1),
(51, 47, 9, 5, 3, 'unidad', '4.00', '0.00', '12.00', 1),
(52, 47, 1, 9, 5, 'unidad', '2.00', '0.00', '10.00', 1),
(53, 47, 8, 4, 2, 'unidad', '12.00', '0.00', '24.00', 1),
(54, 47, 3, 10, 3, 'unidad', '1.00', '0.00', '3.00', 1),
(55, 47, 1, 9, 3, 'unidad', '2.00', '0.00', '6.00', 1),
(56, 48, 9, 5, 3, 'unidad', '4.00', '0.00', '12.00', 1),
(57, 48, 9, 5, 3, 'unidad', '4.00', '0.00', '12.00', 1),
(58, 49, 9, 5, 3, 'unidad', '4.00', '0.00', '12.00', 1);

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
(1, 14, 13, 1, 1, '4.00', 1, 'por fecha vencida', '2025-11-26 18:58:12', 'aceptada'),
(2, 45, 43, 1, 1, '1.00', 1, 'fdsgsgsd', '2025-11-26 22:10:35', 'aceptada');

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
(13, 14, 3, 1, 1, 'F-1-20251118212524-321', '2025-11-18 21:25:24', '4.00', NULL, NULL, 1, '2025-11-18 21:25:24'),
(14, 15, 7, 1, 1, 'F-1-20251118213518-763', '2025-11-18 21:35:18', '10.00', NULL, NULL, 1, '2025-11-18 21:35:18'),
(15, 16, NULL, 1, 1, 'F-1-20251118213900-238', '2025-11-18 21:39:00', '10.00', NULL, NULL, 1, '2025-11-18 21:39:00'),
(16, 17, NULL, 1, 1, 'F-1-20251118213932-712', '2025-11-18 21:39:32', '1.00', NULL, NULL, 1, '2025-11-18 21:39:32'),
(17, 18, 4, 1, 1, 'F-1-20251119182056-490', '2025-11-19 18:20:56', '20.00', NULL, NULL, 1, '2025-11-19 18:20:56'),
(18, 20, NULL, 1, 1, 'F-1-20251119191058-905', '2025-11-19 19:10:58', '613.00', NULL, NULL, 1, '2025-11-19 19:10:58'),
(19, 21, NULL, 1, 1, 'F-1-20251119195235-435', '2025-11-19 19:52:35', '220.00', NULL, NULL, 1, '2025-11-19 19:52:35'),
(20, 22, NULL, 1, 1, 'F-1-20251119200054-901', '2025-11-19 20:00:54', '8.00', NULL, NULL, 1, '2025-11-19 20:00:54'),
(21, 23, NULL, 1, 1, 'F-1-20251119201401-536', '2025-11-19 20:14:01', '60.00', NULL, NULL, 1, '2025-11-19 20:14:01'),
(22, 24, NULL, 1, 1, 'F-1-20251119201536-832', '2025-11-19 20:15:36', '18.00', NULL, NULL, 1, '2025-11-19 20:15:36'),
(23, 25, NULL, 1, 1, 'F-1-20251119201750-601', '2025-11-19 20:17:50', '50.00', NULL, NULL, 1, '2025-11-19 20:17:50'),
(24, 26, NULL, 1, 1, 'F-1-20251119202507-888', '2025-11-19 20:25:07', '150.00', NULL, NULL, 1, '2025-11-19 20:25:07'),
(25, 27, NULL, 1, 1, 'F-1-20251119202638-787', '2025-11-19 20:26:38', '300.00', NULL, NULL, 1, '2025-11-19 20:26:38'),
(26, 28, NULL, 1, 1, 'F-1-20251119203410-148', '2025-11-19 20:34:10', '6.00', NULL, NULL, 1, '2025-11-19 20:34:10'),
(27, 29, NULL, 1, 1, 'F-1-20251121181757-418', '2025-11-21 18:17:57', '216.00', NULL, NULL, 1, '2025-11-21 18:17:57'),
(34, 36, NULL, 1, 1, 'F-1-20251121191419-689', '2025-11-21 19:14:19', '2.00', NULL, NULL, 1, '2025-11-21 19:14:19'),
(35, 37, NULL, 1, 1, 'F-1-20251121191505-329', '2025-11-21 19:15:05', '6000.00', NULL, NULL, 1, '2025-11-21 19:15:05'),
(36, 38, NULL, 1, 1, 'F-1-20251121191554-518', '2025-11-21 19:15:54', '18.00', NULL, NULL, 1, '2025-11-21 19:15:54'),
(37, 39, NULL, 1, 1, 'F-1-20251123143046-670', '2025-11-23 14:30:46', '8.00', NULL, NULL, 1, '2025-11-23 14:30:46'),
(38, 40, NULL, 1, 1, 'F-1-20251124004759-890', '2025-11-24 00:47:59', '2.00', NULL, NULL, 1, '2025-11-24 00:47:59'),
(39, 41, NULL, 1, 1, 'F-1-20251124185328-130', '2025-11-24 18:53:28', '18.00', NULL, NULL, 1, '2025-11-24 18:53:28'),
(40, 42, NULL, 1, 1, 'F-1-20251124185352-661', '2025-11-24 18:53:52', '6.00', NULL, NULL, 1, '2025-11-24 18:53:52'),
(41, 43, NULL, 1, 1, 'F-1-20251126142949-627', '2025-11-26 14:29:49', '24.00', NULL, NULL, 1, '2025-11-26 14:29:49'),
(42, 44, 3, 1, 1, 'F-1-20251126190218-219', '2025-11-26 19:02:18', '4.00', NULL, NULL, 1, '2025-11-26 19:02:18'),
(43, 45, NULL, 1, 1, 'F-1-20251126220551-630', '2025-11-26 22:05:51', '1.00', NULL, NULL, 1, '2025-11-26 22:05:51'),
(44, 46, NULL, 3, 1, 'F-1-20251127202924-593', '2025-11-27 20:29:24', '16.00', NULL, NULL, 1, '2025-11-27 20:29:24'),
(45, 47, 3, 3, 1, 'F-1-20251127202957-736', '2025-11-27 20:29:57', '55.00', NULL, NULL, 1, '2025-11-27 20:29:57'),
(46, 48, 3, 3, 1, 'F-1-20251127203019-650', '2025-11-27 20:30:19', '24.00', NULL, NULL, 1, '2025-11-27 20:30:19'),
(47, 49, NULL, 1, 1, 'F-1-20251128185730-511', '2025-11-28 18:57:30', '12.00', NULL, NULL, 1, '2025-11-28 18:57:30');

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
  `hl_accion` enum('creacion','activacion','ajuste','caducidad','terminacion','devolucion','bloqueo','desbloqueo') NOT NULL,
  `hl_descripcion` text DEFAULT NULL,
  `hl_fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_lote`
--

INSERT INTO `historial_lote` (`hl_id`, `lm_id`, `us_id`, `hl_accion`, `hl_descripcion`, `hl_fecha`) VALUES
(1, 1, 1, 'creacion', 'Lote creado automáticamente por compra #COMP-2025-0001 en estado \'en_espera\'.', '2025-11-13 13:55:49'),
(2, 2, 1, 'creacion', 'Lote creado automáticamente por compra #COMP-2025-0002 en estado \'en_espera\'.', '2025-11-16 13:30:17'),
(3, 1, 1, 'activacion', 'Lote activado por admin', '2025-11-17 14:35:47'),
(4, 2, 1, 'activacion', 'Lote activado por admin', '2025-11-17 14:57:06'),
(5, 3, 1, 'creacion', 'Lote creado automáticamente por compra #COMP-2025-0003 en estado \'en_espera\'.', '2025-11-18 10:26:29'),
(6, 2, 1, 'ajuste', 'Actualización de datos del lote (cantidades/precios/fecha de vencimiento)', '2025-11-18 10:27:23'),
(7, 3, 1, 'activacion', 'Lote activado por admin', '2025-11-18 10:27:56'),
(8, 4, 1, 'creacion', 'Lote creado por compra #COMP-2025-0004 en estado \'en_espera\'.', '2025-11-19 18:13:28'),
(9, 5, 1, 'creacion', 'Lote creado por compra #COMP-2025-0004 en estado \'activo\'.', '2025-11-19 18:13:28'),
(10, 5, 1, 'activacion', 'Lote activado automáticamente al registrar compra #COMP-2025-0004.', '2025-11-19 18:13:28'),
(11, 4, 1, 'activacion', 'Lote activado por admin', '2025-11-19 18:13:59'),
(12, 6, 1, 'creacion', 'Lote creado por compra #COMP-2025-0005 en estado \'en_espera\'.', '2025-11-19 18:18:05'),
(13, 6, 1, 'activacion', 'Lote activado por admin', '2025-11-19 18:18:14'),
(14, 7, 1, 'creacion', 'Lote creado por compra #COMP-2025-0006 en estado \'activo\'.', '2025-11-19 19:16:53'),
(15, 7, 1, 'activacion', 'Lote activado automáticamente al registrar compra #COMP-2025-0006.', '2025-11-19 19:16:53'),
(16, 8, 1, 'creacion', 'Lote creado por compra #COMP-2025-0006 en estado \'activo\'.', '2025-11-19 19:16:53'),
(17, 8, 1, 'activacion', 'Lote activado automáticamente al registrar compra #COMP-2025-0006.', '2025-11-19 19:16:53'),
(18, 9, 1, 'creacion', 'Lote creado por compra #COMP-2025-0007 en estado \'en_espera\'.', '2025-11-19 20:31:32'),
(19, 9, 1, 'activacion', 'Lote activado por admin', '2025-11-19 20:32:18'),
(20, 6, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-21 18:17:57'),
(27, 8, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-21 19:14:19'),
(28, 5, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-21 19:15:05'),
(29, 4, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-21 19:15:54'),
(30, 8, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-21 19:15:54'),
(31, 5, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-21 19:15:54'),
(32, 10, 1, 'creacion', 'Lote creado por compra #COMP-2025-0008 en estado \'activo\'.', '2025-11-22 16:37:12'),
(33, 10, 1, 'activacion', 'Lote activado automáticamente al registrar compra #COMP-2025-0008.', '2025-11-22 16:37:12'),
(34, 8, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-23 14:30:46'),
(35, 9, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-24 00:47:59'),
(36, 9, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-24 18:53:28'),
(37, 9, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-24 18:53:52'),
(38, 5, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-24 18:53:52'),
(39, 5, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-26 14:29:49'),
(40, 5, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-26 19:02:18'),
(41, 10, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-26 22:05:51'),
(42, 5, 3, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-27 20:29:24'),
(43, 5, 3, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-27 20:29:57'),
(44, 9, 3, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-27 20:29:57'),
(45, 4, 3, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-27 20:29:57'),
(46, 10, 3, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-27 20:29:57'),
(47, 9, 3, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-27 20:29:57'),
(48, 5, 3, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-27 20:30:19'),
(49, 5, 3, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-27 20:30:19'),
(50, 5, 1, 'terminacion', 'Lote agotado por ventas, cambiado a estado \'terminado\' automáticamente', '2025-11-28 18:57:30');

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
(1, 'Compra COMP-2025-0001 - jose', 'compra', 1, '{\"compra_id\":1,\"numero_compra\":\"COMP-2025-0001\",\"proveedor_id\":\"1\",\"laboratorio_id\":\"7\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-11-30\",\"numero_factura\":\"242342323423\",\"razon_social\":\"jose\",\"subtotal\":\"42.00\",\"impuestos\":\"5.46\",\"total\":\"47.46\",\"cantidad_lotes\":1,\"lotes\":[{\"medicamento_id\":\"5\",\"numero_lote\":\"MED-0001\",\"cantidad\":6,\"precio_compra\":7,\"precio_venta\":4,\"vencimiento\":\"2025-11-28\",\"activar_lote\":0}]}', '2025-11-13 13:55:49'),
(2, 'Compra COMP-2025-0002 - fsdfsdf', 'compra', 1, '{\"compra_id\":2,\"numero_compra\":\"COMP-2025-0002\",\"proveedor_id\":\"3\",\"laboratorio_id\":\"2\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-11-22\",\"numero_factura\":\"2342\",\"razon_social\":\"fsdfsdf\",\"subtotal\":\"54756.00\",\"impuestos\":\"7118.28\",\"total\":\"61874.28\",\"cantidad_lotes\":1,\"lotes\":[{\"medicamento_id\":\"4\",\"numero_lote\":\"MED-0002\",\"cantidad\":234,\"precio_compra\":234,\"precio_venta\":523,\"vencimiento\":\"2025-12-03\",\"activar_lote\":0}]}', '2025-11-16 13:30:17'),
(3, 'Activación de Lote #MED-0001 (Omeprazol)', 'Activacion', 1, '{\"tipo_informe\":\"activacion_lote\",\"lote_id\":\"1\",\"numero_lote\":\"MED-0001\",\"medicamento_id\":5,\"medicamento_nombre\":\"Omeprazol\",\"sucursal_id\":1,\"usuario_id\":\"1\",\"usuario_nombre\":\"admin\",\"fecha_activacion\":\"2025-11-17 14:35:47\",\"precio_compra\":7,\"precio_venta\":4,\"cantidad_cajas\":6,\"cantidad_unidades\":36,\"subtotal_lote\":42,\"observaciones\":\"Activación inicial del lote e ingreso a inventario.\"}', '2025-11-17 14:35:47'),
(4, 'Activación de Lote #MED-0002 (Loratadina)', 'Activacion', 1, '{\"tipo_informe\":\"activacion_lote\",\"lote_id\":\"2\",\"numero_lote\":\"MED-0002\",\"medicamento_id\":4,\"medicamento_nombre\":\"Loratadina\",\"sucursal_id\":1,\"usuario_id\":\"1\",\"usuario_nombre\":\"admin\",\"fecha_activacion\":\"2025-11-17 14:57:06\",\"precio_compra\":234,\"precio_venta\":523,\"cantidad_cajas\":234,\"cantidad_unidades\":54756,\"subtotal_lote\":54756,\"observaciones\":\"Activación inicial del lote e ingreso a inventario.\"}', '2025-11-17 14:57:06'),
(5, 'Compra COMP-2025-0003 - jose', 'compra', 1, '{\"compra_id\":3,\"numero_compra\":\"COMP-2025-0003\",\"proveedor_id\":\"1\",\"laboratorio_id\":\"4\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-11-28\",\"numero_factura\":\"12312312\",\"razon_social\":\"jose\",\"subtotal\":\"100.00\",\"impuestos\":\"13.00\",\"total\":\"113.00\",\"cantidad_lotes\":1,\"lotes\":[{\"medicamento_id\":\"4\",\"numero_lote\":\"MED-0003\",\"cantidad\":10,\"precio_compra\":10,\"precio_venta\":1,\"vencimiento\":\"2025-12-07\",\"activar_lote\":0}]}', '2025-11-18 10:26:29'),
(6, 'Activación de Lote #MED-0003 (Loratadina)', 'Activacion', 1, '{\"tipo_informe\":\"activacion_lote\",\"lote_id\":\"3\",\"numero_lote\":\"MED-0003\",\"medicamento_id\":4,\"medicamento_nombre\":\"Loratadina\",\"sucursal_id\":1,\"usuario_id\":\"1\",\"usuario_nombre\":\"admin\",\"fecha_activacion\":\"2025-11-18 10:27:56\",\"precio_compra\":10,\"precio_venta\":1,\"cantidad_cajas\":10,\"cantidad_unidades\":16000,\"subtotal_lote\":4000,\"observaciones\":\"Activación inicial del lote e ingreso a inventario.\"}', '2025-11-18 10:27:56'),
(7, 'Nota Venta SU1-1763476327', 'nota_venta', 1, '{\"ve_id\":1,\"ve_numero_documento\":\"SU1-1763476327\",\"usuario_id\":1,\"sucursal_id\":1,\"cliente_id\":8,\"items\":[{\"med_id\":\"4\",\"cantidad\":10,\"precio\":1,\"subtotal\":10}],\"subtotal\":10,\"total\":10,\"metodo_pago\":\"efectivo\"}', '2025-11-18 10:32:07'),
(20, 'Nota Venta F-1-20251118212524-321', 'nota_venta', 1, '{\"ve_id\":14,\"fa_id\":13,\"ve_numero_documento\":\"SU1-1763515524\",\"fa_numero\":\"F-1-20251118212524-321\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"5\",\"cantidad\":1,\"precio\":4,\"subtotal\":4}],\"subtotal\":4,\"total\":4,\"metodo_pago\":\"efectivo\"}', '2025-11-18 21:25:24'),
(21, 'Nota Venta F-1-20251118213518-763', 'nota_venta', 1, '{\"ve_id\":15,\"fa_id\":14,\"ve_numero_documento\":\"SU1-1763516118\",\"fa_numero\":\"F-1-20251118213518-763\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"4\",\"cantidad\":10,\"precio\":1,\"subtotal\":10}],\"subtotal\":10,\"total\":10,\"metodo_pago\":\"efectivo\"}', '2025-11-18 21:35:18'),
(22, 'Nota Venta F-1-20251118213900-238', 'nota_venta', 1, '{\"ve_id\":16,\"fa_id\":15,\"ve_numero_documento\":\"SU1-1763516340\",\"fa_numero\":\"F-1-20251118213900-238\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"4\",\"cantidad\":10,\"precio\":1,\"subtotal\":10}],\"subtotal\":10,\"total\":10,\"metodo_pago\":\"\"}', '2025-11-18 21:39:00'),
(23, 'Nota Venta F-1-20251118213932-712', 'nota_venta', 1, '{\"ve_id\":17,\"fa_id\":16,\"ve_numero_documento\":\"SU1-1763516372\",\"fa_numero\":\"F-1-20251118213932-712\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"4\",\"cantidad\":1,\"precio\":1,\"subtotal\":1}],\"subtotal\":1,\"total\":1,\"metodo_pago\":\"\"}', '2025-11-18 21:39:32'),
(24, 'Compra COMP-2025-0004 - dadasda', 'compra', 1, '{\"compra_id\":4,\"numero_compra\":\"COMP-2025-0004\",\"proveedor_id\":\"3\",\"laboratorio_id\":\"3\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-11-30\",\"numero_factura\":\"123123\",\"razon_social\":\"dadasda\",\"subtotal\":\"4500.00\",\"impuestos\":\"585.00\",\"total\":\"5085.00\",\"cantidad_lotes\":2,\"lotes\":[{\"medicamento_id\":\"8\",\"numero_lote\":\"MED-0004\",\"cantidad\":50,\"precio_compra\":10,\"precio_venta\":12,\"vencimiento\":\"2025-11-30\",\"activar_lote\":0},{\"medicamento_id\":\"9\",\"numero_lote\":\"MED-0005\",\"cantidad\":80,\"precio_compra\":50,\"precio_venta\":4,\"vencimiento\":\"2025-11-30\",\"activar_lote\":1}]}', '2025-11-19 18:13:29'),
(25, 'Activación de Lote #MED-0004 (Salbutamol)', 'Activacion', 1, '{\"tipo_informe\":\"activacion_lote\",\"lote_id\":\"4\",\"numero_lote\":\"MED-0004\",\"medicamento_id\":8,\"medicamento_nombre\":\"Salbutamol\",\"sucursal_id\":1,\"usuario_id\":\"1\",\"usuario_nombre\":\"admin\",\"fecha_activacion\":\"2025-11-19 18:13:59\",\"precio_compra\":10,\"precio_venta\":12,\"cantidad_cajas\":50,\"cantidad_unidades\":2500,\"subtotal_lote\":500,\"observaciones\":\"Activación inicial del lote e ingreso a inventario.\"}', '2025-11-19 18:13:59'),
(26, 'Compra COMP-2025-0005 - asaasdas', 'compra', 1, '{\"compra_id\":5,\"numero_compra\":\"COMP-2025-0005\",\"proveedor_id\":\"2\",\"laboratorio_id\":\"4\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-11-14\",\"numero_factura\":\"3213\",\"razon_social\":\"asaasdas\",\"subtotal\":\"200.00\",\"impuestos\":\"26.00\",\"total\":\"226.00\",\"cantidad_lotes\":1,\"lotes\":[{\"medicamento_id\":\"6\",\"numero_lote\":\"MED-0006\",\"cantidad\":40,\"precio_compra\":5,\"precio_venta\":6,\"vencimiento\":\"2025-11-30\",\"activar_lote\":0}]}', '2025-11-19 18:18:05'),
(27, 'Activación de Lote #MED-0006 (Metformina)', 'Activacion', 1, '{\"tipo_informe\":\"activacion_lote\",\"lote_id\":\"6\",\"numero_lote\":\"MED-0006\",\"medicamento_id\":6,\"medicamento_nombre\":\"Metformina\",\"sucursal_id\":1,\"usuario_id\":\"1\",\"usuario_nombre\":\"admin\",\"fecha_activacion\":\"2025-11-19 18:18:14\",\"precio_compra\":5,\"precio_venta\":6,\"cantidad_cajas\":40,\"cantidad_unidades\":1600,\"subtotal_lote\":200,\"observaciones\":\"Activación inicial del lote e ingreso a inventario.\"}', '2025-11-19 18:18:14'),
(28, 'Nota Venta F-1-20251119182056-490', 'nota_venta', 1, '{\"ve_id\":18,\"fa_id\":17,\"ve_numero_documento\":\"SU1-1763590856\",\"fa_numero\":\"F-1-20251119182056-490\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"5\",\"cantidad\":5,\"precio\":4,\"subtotal\":20}],\"subtotal\":20,\"total\":20,\"metodo_pago\":\"\"}', '2025-11-19 18:20:56'),
(29, 'Nota Venta F-1-20251119191058-905', 'nota_venta', 1, '{\"ve_id\":20,\"fa_id\":18,\"ve_numero_documento\":\"SU1-1763593857\",\"fa_numero\":\"F-1-20251119191058-905\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"4\",\"cantidad\":613,\"precio\":1,\"subtotal\":613}],\"subtotal\":613,\"total\":613,\"metodo_pago\":\"\"}', '2025-11-19 19:10:58'),
(30, 'Compra COMP-2025-0006 - 423423423', 'compra', 1, '{\"compra_id\":6,\"numero_compra\":\"COMP-2025-0006\",\"proveedor_id\":\"4\",\"laboratorio_id\":\"6\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-11-22\",\"numero_factura\":\"423423\",\"razon_social\":\"423423423\",\"subtotal\":\"900.00\",\"impuestos\":\"117.00\",\"total\":\"1017.00\",\"cantidad_lotes\":2,\"lotes\":[{\"medicamento_id\":\"1\",\"numero_lote\":\"MED-0007\",\"cantidad\":10,\"precio_compra\":50,\"precio_venta\":55,\"vencimiento\":\"2025-11-30\",\"activar_lote\":1},{\"medicamento_id\":\"1\",\"numero_lote\":\"MED-0008\",\"cantidad\":10,\"precio_compra\":40,\"precio_venta\":50,\"vencimiento\":\"2025-11-30\",\"activar_lote\":1}]}', '2025-11-19 19:16:53'),
(31, 'Nota Venta F-1-20251119195235-435', 'nota_venta', 1, '{\"ve_id\":21,\"fa_id\":19,\"ve_numero_documento\":\"SU1-1763596355\",\"fa_numero\":\"F-1-20251119195235-435\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"1\",\"lote_id\":\"7\",\"cantidad\":4,\"precio\":55,\"subtotal\":220}],\"subtotal\":220,\"total\":220,\"metodo_pago\":\"\"}', '2025-11-19 19:52:35'),
(32, 'Nota Venta F-1-20251119200054-901', 'nota_venta', 1, '{\"ve_id\":22,\"fa_id\":20,\"ve_numero_documento\":\"SU1-1763596854\",\"fa_numero\":\"F-1-20251119200054-901\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"9\",\"lote_id\":\"5\",\"cantidad\":2,\"precio\":4,\"subtotal\":8}],\"subtotal\":8,\"total\":8,\"metodo_pago\":\"QR\"}', '2025-11-19 20:00:54'),
(33, 'Nota Venta F-1-20251119201401-536', 'nota_venta', 1, '{\"ve_id\":23,\"fa_id\":21,\"ve_numero_documento\":\"SU1-1763597641\",\"fa_numero\":\"F-1-20251119201401-536\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"8\",\"lote_id\":\"4\",\"cantidad\":5,\"precio\":12,\"subtotal\":60}],\"subtotal\":60,\"total\":60,\"metodo_pago\":\"QR\"}', '2025-11-19 20:14:01'),
(34, 'Nota Venta F-1-20251119201536-832', 'nota_venta', 1, '{\"ve_id\":24,\"fa_id\":22,\"ve_numero_documento\":\"SU1-1763597736\",\"fa_numero\":\"F-1-20251119201536-832\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"6\",\"lote_id\":\"6\",\"cantidad\":3,\"precio\":6,\"subtotal\":18}],\"subtotal\":18,\"total\":18,\"metodo_pago\":\"targeta\"}', '2025-11-19 20:15:36'),
(35, 'Nota Venta F-1-20251119201750-601', 'nota_venta', 1, '{\"ve_id\":25,\"fa_id\":23,\"ve_numero_documento\":\"SU1-1763597870\",\"fa_numero\":\"F-1-20251119201750-601\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"1\",\"lote_id\":\"8\",\"cantidad\":1,\"precio\":50,\"subtotal\":50}],\"subtotal\":50,\"total\":50,\"metodo_pago\":\"\"}', '2025-11-19 20:17:50'),
(36, 'Nota Venta F-1-20251119202507-888', 'nota_venta', 1, '{\"ve_id\":26,\"fa_id\":24,\"ve_numero_documento\":\"SU1-1763598307\",\"fa_numero\":\"F-1-20251119202507-888\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"1\",\"lote_id\":\"8\",\"cantidad\":3,\"precio\":50,\"subtotal\":150}],\"subtotal\":150,\"total\":150,\"metodo_pago\":\"efectivo\"}', '2025-11-19 20:25:07'),
(37, 'Nota Venta F-1-20251119202638-787', 'nota_venta', 1, '{\"ve_id\":27,\"fa_id\":25,\"ve_numero_documento\":\"SU1-1763598398\",\"fa_numero\":\"F-1-20251119202638-787\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"1\",\"lote_id\":\"8\",\"cantidad\":6,\"precio\":50,\"subtotal\":300}],\"subtotal\":300,\"total\":300,\"metodo_pago\":\"efectivo\"}', '2025-11-19 20:26:38'),
(38, 'Compra COMP-2025-0007 - jjbhjg', 'compra', 1, '{\"compra_id\":7,\"numero_compra\":\"COMP-2025-0007\",\"proveedor_id\":\"3\",\"laboratorio_id\":\"6\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-11-28\",\"numero_factura\":\"65484\",\"razon_social\":\"jjbhjg\",\"subtotal\":\"3200.00\",\"impuestos\":\"416.00\",\"total\":\"3616.00\",\"cantidad_lotes\":1,\"lotes\":[{\"medicamento_id\":\"1\",\"numero_lote\":\"MED-0009\",\"cantidad\":80,\"precio_compra\":40,\"precio_venta\":2,\"vencimiento\":\"2025-11-30\",\"activar_lote\":0}]}', '2025-11-19 20:31:32'),
(39, 'Activación de Lote #MED-0009 (Paracetamol)', 'Activacion', 1, '{\"tipo_informe\":\"activacion_lote\",\"lote_id\":\"9\",\"numero_lote\":\"MED-0009\",\"medicamento_id\":1,\"medicamento_nombre\":\"Paracetamol\",\"sucursal_id\":1,\"usuario_id\":\"1\",\"usuario_nombre\":\"admin\",\"fecha_activacion\":\"2025-11-19 20:32:18\",\"precio_compra\":40,\"precio_venta\":2,\"cantidad_cajas\":80,\"cantidad_unidades\":2304000,\"subtotal_lote\":192000,\"observaciones\":\"Activación inicial del lote e ingreso a inventario.\"}', '2025-11-19 20:32:18'),
(40, 'Nota Venta F-1-20251119203410-148', 'nota_venta', 1, '{\"ve_id\":28,\"fa_id\":26,\"ve_numero_documento\":\"SU1-1763598850\",\"fa_numero\":\"F-1-20251119203410-148\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"6\",\"lote_id\":null,\"cantidad\":1,\"precio\":6,\"subtotal\":6}],\"subtotal\":6,\"total\":6,\"metodo_pago\":\"efectivo\"}', '2025-11-19 20:34:10'),
(41, 'Nota Venta F-1-20251121181757-418', 'nota_venta', 1, '{\"ve_id\":29,\"fa_id\":27,\"ve_numero_documento\":\"SU1-1763763477\",\"fa_numero\":\"F-1-20251121181757-418\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"6\",\"lote_id\":\"6\",\"cantidad\":36,\"precio\":6,\"subtotal\":216}],\"subtotal\":216,\"total\":216,\"metodo_pago\":\"efectivo\"}', '2025-11-21 18:17:57'),
(48, 'Nota Venta F-1-20251121191419-689', 'nota_venta', 1, '{\"ve_id\":36,\"fa_id\":34,\"ve_numero_documento\":\"SU1-1763766859\",\"fa_numero\":\"F-1-20251121191419-689\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"1\",\"lote_id\":null,\"cantidad\":1,\"precio\":2,\"subtotal\":2}],\"subtotal\":2,\"total\":2,\"metodo_pago\":\"efectivo\"}', '2025-11-21 19:14:19'),
(49, 'Nota Venta F-1-20251121191505-329', 'nota_venta', 1, '{\"ve_id\":37,\"fa_id\":35,\"ve_numero_documento\":\"SU1-1763766905\",\"fa_numero\":\"F-1-20251121191505-329\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"9\",\"lote_id\":null,\"cantidad\":1500,\"precio\":4,\"subtotal\":6000}],\"subtotal\":6000,\"total\":6000,\"metodo_pago\":\"efectivo\"}', '2025-11-21 19:15:05'),
(50, 'Nota Venta F-1-20251121191554-518', 'nota_venta', 1, '{\"ve_id\":38,\"fa_id\":36,\"ve_numero_documento\":\"SU1-1763766954\",\"fa_numero\":\"F-1-20251121191554-518\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"8\",\"lote_id\":null,\"cantidad\":1,\"precio\":12,\"subtotal\":12},{\"med_id\":\"1\",\"lote_id\":null,\"cantidad\":1,\"precio\":2,\"subtotal\":2},{\"med_id\":\"9\",\"lote_id\":null,\"cantidad\":1,\"precio\":4,\"subtotal\":4}],\"subtotal\":18,\"total\":18,\"metodo_pago\":\"efectivo\"}', '2025-11-21 19:15:54'),
(51, 'Compra COMP-2025-0008 - ugo dabila', 'compra', 1, '{\"compra_id\":8,\"numero_compra\":\"COMP-2025-0008\",\"proveedor_id\":\"6\",\"laboratorio_id\":\"6\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-11-22\",\"numero_factura\":\"132132154541\",\"razon_social\":\"ugo dabila\",\"subtotal\":\"4500.00\",\"impuestos\":\"585.00\",\"total\":\"5085.00\",\"cantidad_lotes\":1,\"lotes\":[{\"medicamento_id\":\"3\",\"numero_lote\":\"MED-0010\",\"cantidad\":90,\"precio_compra\":50,\"precio_venta\":1,\"vencimiento\":\"2026-01-25\",\"activar_lote\":1}]}', '2025-11-22 16:37:12'),
(52, 'Nota Venta F-1-20251123143046-670', 'nota_venta', 1, '{\"ve_id\":39,\"fa_id\":37,\"ve_numero_documento\":\"SU1-1763922646\",\"fa_numero\":\"F-1-20251123143046-670\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"1\",\"lote_id\":\"9\",\"cantidad\":4,\"precio\":2,\"subtotal\":8}],\"subtotal\":8,\"total\":8,\"metodo_pago\":\"efectivo\"}', '2025-11-23 14:30:46'),
(53, 'Nota Venta F-1-20251124004759-890', 'nota_venta', 1, '{\"ve_id\":40,\"fa_id\":38,\"ve_numero_documento\":\"SU1-1763959679\",\"fa_numero\":\"F-1-20251124004759-890\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"1\",\"lote_id\":null,\"cantidad\":1,\"precio\":2,\"subtotal\":2}],\"subtotal\":2,\"total\":2,\"metodo_pago\":\"efectivo\"}', '2025-11-24 00:47:59'),
(54, 'Nota Venta F-1-20251124185328-130', 'nota_venta', 1, '{\"ve_id\":41,\"fa_id\":39,\"ve_numero_documento\":\"SU1-1764024808\",\"fa_numero\":\"F-1-20251124185328-130\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"1\",\"lote_id\":\"9\",\"cantidad\":9,\"precio\":2,\"subtotal\":18}],\"subtotal\":18,\"total\":18,\"metodo_pago\":\"efectivo\"}', '2025-11-24 18:53:28'),
(55, 'Nota Venta F-1-20251124185352-661', 'nota_venta', 1, '{\"ve_id\":42,\"fa_id\":40,\"ve_numero_documento\":\"SU1-1764024832\",\"fa_numero\":\"F-1-20251124185352-661\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"1\",\"lote_id\":\"9\",\"cantidad\":1,\"precio\":2,\"subtotal\":2},{\"med_id\":\"9\",\"lote_id\":\"5\",\"cantidad\":1,\"precio\":4,\"subtotal\":4}],\"subtotal\":6,\"total\":6,\"metodo_pago\":\"efectivo\"}', '2025-11-24 18:53:52'),
(56, 'Nota Venta F-1-20251126142949-627', 'nota_venta', 1, '{\"ve_id\":43,\"fa_id\":41,\"ve_numero_documento\":\"SU1-1764181789\",\"fa_numero\":\"F-1-20251126142949-627\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"9\",\"lote_id\":null,\"cantidad\":6,\"precio\":4,\"subtotal\":24}],\"subtotal\":24,\"total\":24,\"metodo_pago\":\"efectivo\"}', '2025-11-26 14:29:49'),
(57, 'Devolución #1 - Venta #14', 'devolucion', 1, '{\"dev_id\":1,\"ve_id\":14,\"fa_id\":13,\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"dv_id\":\"14\",\"med_id\":\"5\",\"lm_id\":\"1\",\"cantidad\":1,\"precio_unitario\":\"4.00\",\"motivo\":\"por fecha vencida\",\"tipo\":\"devolucion\"}],\"total_devolucion\":4,\"cantidad_items\":1,\"motivo\":\"por fecha vencida\",\"fecha\":\"2025-11-26 18:58:12\"}', '2025-11-26 18:58:12'),
(58, 'Nota Venta F-1-20251126190218-219', 'nota_venta', 1, '{\"ve_id\":44,\"fa_id\":42,\"ve_numero_documento\":\"SU1-1764198138\",\"fa_numero\":\"F-1-20251126190218-219\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"9\",\"lote_id\":null,\"cantidad\":1,\"precio\":4,\"subtotal\":4}],\"subtotal\":4,\"total\":4,\"metodo_pago\":\"efectivo\"}', '2025-11-26 19:02:18'),
(59, 'Nota Venta F-1-20251126220551-630', 'nota_venta', 1, '{\"ve_id\":45,\"fa_id\":43,\"ve_numero_documento\":\"SU1-1764209151\",\"fa_numero\":\"F-1-20251126220551-630\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"3\",\"lote_id\":\"10\",\"cantidad\":1,\"precio\":1,\"subtotal\":1}],\"subtotal\":1,\"total\":1,\"metodo_pago\":\"efectivo\"}', '2025-11-26 22:05:51'),
(60, 'Devolución #2 - Venta #45', 'devolucion', 1, '{\"dev_id\":2,\"ve_id\":45,\"fa_id\":43,\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"dv_id\":\"49\",\"med_id\":\"3\",\"lm_id\":\"10\",\"cantidad\":1,\"precio_unitario\":\"1.00\",\"motivo\":\"fdsgsgsd\",\"tipo\":\"cambio\"}],\"total_devolucion\":1,\"cantidad_items\":1,\"motivo\":\"fdsgsgsd\",\"fecha\":\"2025-11-26 22:10:35\"}', '2025-11-26 22:10:35'),
(61, 'Nota Venta F-1-20251127202924-593', 'nota_venta', 3, '{\"ve_id\":46,\"fa_id\":44,\"ve_numero_documento\":\"SU1-1764289764\",\"fa_numero\":\"F-1-20251127202924-593\",\"usuario_id\":3,\"sucursal_id\":1,\"items\":[{\"med_id\":\"9\",\"lote_id\":null,\"cantidad\":4,\"precio\":4,\"subtotal\":16}],\"subtotal\":16,\"total\":16,\"metodo_pago\":\"efectivo\"}', '2025-11-27 20:29:24'),
(62, 'Nota Venta F-1-20251127202957-736', 'nota_venta', 3, '{\"ve_id\":47,\"fa_id\":45,\"ve_numero_documento\":\"SU1-1764289797\",\"fa_numero\":\"F-1-20251127202957-736\",\"usuario_id\":3,\"sucursal_id\":1,\"items\":[{\"med_id\":\"9\",\"lote_id\":null,\"cantidad\":3,\"precio\":4,\"subtotal\":12},{\"med_id\":\"1\",\"lote_id\":null,\"cantidad\":5,\"precio\":2,\"subtotal\":10},{\"med_id\":\"8\",\"lote_id\":null,\"cantidad\":2,\"precio\":12,\"subtotal\":24},{\"med_id\":\"3\",\"lote_id\":null,\"cantidad\":3,\"precio\":1,\"subtotal\":3},{\"med_id\":\"1\",\"lote_id\":\"9\",\"cantidad\":3,\"precio\":2,\"subtotal\":6}],\"subtotal\":55,\"total\":55,\"metodo_pago\":\"efectivo\"}', '2025-11-27 20:29:57'),
(63, 'Nota Venta F-1-20251127203019-650', 'nota_venta', 3, '{\"ve_id\":48,\"fa_id\":46,\"ve_numero_documento\":\"SU1-1764289819\",\"fa_numero\":\"F-1-20251127203019-650\",\"usuario_id\":3,\"sucursal_id\":1,\"items\":[{\"med_id\":\"9\",\"lote_id\":null,\"cantidad\":3,\"precio\":4,\"subtotal\":12},{\"med_id\":\"9\",\"lote_id\":\"5\",\"cantidad\":3,\"precio\":4,\"subtotal\":12}],\"subtotal\":24,\"total\":24,\"metodo_pago\":\"efectivo\"}', '2025-11-27 20:30:19'),
(64, 'Nota Venta F-1-20251128185730-511', 'nota_venta', 1, '{\"ve_id\":49,\"fa_id\":47,\"ve_numero_documento\":\"SU1-1764370650\",\"fa_numero\":\"F-1-20251128185730-511\",\"usuario_id\":1,\"sucursal_id\":1,\"items\":[{\"med_id\":\"9\",\"lote_id\":\"5\",\"cantidad\":3,\"precio\":4,\"subtotal\":12}],\"subtotal\":12,\"total\":12,\"metodo_pago\":\"efectivo\"}', '2025-11-28 18:57:30');

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
(1, 5, 1, 0, 0, '42.00', 0, NULL, NULL, 0, '2025-11-19 18:20:56', '2025-11-17 14:35:47'),
(2, 4, 1, 0, 0, '58756.00', 0, NULL, NULL, 0, '2025-11-19 19:10:58', '2025-11-17 14:57:06'),
(4, 9, 1, 48, 2313, '4000.00', 0, NULL, NULL, 0, '2025-11-28 18:57:30', '2025-11-19 18:13:28'),
(5, 8, 1, 2542, 2542, '1000.00', 0, NULL, NULL, 0, '2025-11-27 20:29:57', '2025-11-19 18:13:59'),
(7, 6, 1, 1600, 1600, '400.00', 0, NULL, NULL, 0, '2025-11-21 18:17:57', '2025-11-19 18:18:14'),
(9, 1, 1, 38479, 2308781, '196100.00', 0, NULL, NULL, 0, '2025-11-27 20:29:57', '2025-11-19 19:16:53'),
(13, 3, 1, 89, 2695, '4500.00', 0, NULL, NULL, 0, '2025-11-27 20:29:57', '2025-11-22 16:37:12');

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
(1, 5, 1, 1, 1, 'MED-0001', 6, 1, 1, 0, 0, '7.00', '4.00', '2025-11-13 13:55:49', '2025-11-28', 'terminado', '2025-11-13 13:55:49', '2025-11-21 18:20:12', NULL, NULL),
(2, 4, 1, 3, 2, 'MED-0002', 234, 1, 1, 0, 0, '234.00', '523.00', '2025-11-16 13:30:17', '2025-12-03', 'terminado', '2025-11-16 13:30:17', '2025-11-21 18:20:12', NULL, NULL),
(3, 4, 1, 1, 3, 'MED-0003', 10, 4, 10, 0, 0, '10.00', '1.00', '2025-11-18 10:26:29', '2025-12-07', 'terminado', '2025-11-18 10:26:29', '2025-11-21 18:20:12', NULL, NULL),
(4, 8, 1, 3, 4, 'MED-0004', 50, 1, 1, 42, 42, '10.00', '12.00', '2025-11-19 18:13:28', '2025-11-30', 'activo', '2025-11-19 18:13:28', '2025-11-27 20:29:57', NULL, NULL),
(5, 9, 1, 3, 4, 'MED-0005', 80, 6, 8, 48, 2313, '50.00', '4.00', '2025-11-19 18:13:28', '2025-11-30', 'activo', '2025-11-19 18:13:28', '2025-11-28 18:57:30', NULL, NULL),
(6, 6, 1, 2, 5, 'MED-0006', 40, 1, 1, 0, 0, '5.00', '6.00', '2025-11-19 18:18:05', '2025-11-30', 'terminado', '2025-11-19 18:18:05', '2025-11-21 18:17:57', NULL, NULL),
(7, 1, 1, 4, 6, 'MED-0007', 10, 1, 1, 0, 0, '50.00', '55.00', '2025-11-19 19:16:53', '2025-11-30', 'terminado', '2025-11-19 19:16:53', '2025-11-21 18:20:12', NULL, NULL),
(8, 1, 1, 4, 6, 'MED-0008', 10, 1, 1, 0, 0, '40.00', '50.00', '2025-11-19 19:16:53', '2025-11-30', 'terminado', '2025-11-19 19:16:53', '2025-11-23 14:30:46', NULL, NULL),
(9, 1, 1, 3, 7, 'MED-0009', 80, 6, 10, 79, 4781, '40.00', '2.00', '2025-11-19 20:31:32', '2025-11-30', 'activo', '2025-11-19 20:31:32', '2025-11-27 20:29:57', NULL, NULL),
(10, 3, 1, 6, 8, 'MED-0010', 90, 5, 6, 89, 2695, '50.00', '1.00', '2025-11-22 16:37:12', '2026-01-25', 'activo', '2025-11-22 16:37:12', '2025-11-27 20:29:57', NULL, NULL);

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
  `med_precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `med_precio_caja` decimal(12,2) DEFAULT NULL,
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

INSERT INTO `medicamento` (`med_id`, `med_nombre_quimico`, `med_principio_activo`, `med_accion_farmacologica`, `med_presentacion`, `med_descripcion`, `med_precio_unitario`, `med_precio_caja`, `med_codigo_barras`, `med_version_comercial`, `med_creado_en`, `med_actualizado_en`, `uf_id`, `ff_id`, `vd_id`, `la_id`, `su_id`, `us_id`) VALUES
(1, 'Paracetamol', 'Paracetamol', 'Analgésico y antipirético', 'Tabletas 500mg x 10', 'Analgésico para dolor leve a moderado', '2.50', '25.00', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 1, 1, 1, 1, 1, 1),
(2, 'Ibuprofeno', 'Ibuprofeno', 'Antiinflamatorio no esteroideo', 'Tabletas 400mg x 20', 'Antiinflamatorio y analgésico', '3.00', '60.00', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 3, 1, 1, 2, 1, 1),
(3, 'Amoxicilina', 'Amoxicilina', 'Antibiótico de amplio espectro', 'Cápsulas 500mg x 12', 'Antibiótico para infecciones bacterianas', '15.00', '180.00', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 2, 2, 1, 3, 1, 1),
(4, 'Loratadina', 'Loratadina', 'Antihistamínico', 'Tabletas 10mg x 10', 'Para alergias y rinitis', '4.50', '45.00', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 4, 1, 1, 4, 1, 1),
(5, 'Omeprazol', 'Omeprazol', 'Inhibidor de bomba de protones', 'Cápsulas 20mg x 14', 'Para úlceras y reflujo gastroesofágico', '12.00', '168.00', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 8, 2, 1, 5, 1, 1),
(6, 'Metformina', 'Metformina', 'Hipoglucemiante oral', 'Tabletas 850mg x 30', 'Para diabetes tipo 2', '8.50', '255.00', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 7, 1, 1, 6, 1, 1),
(7, 'Atorvastatina', 'Atorvastatina', 'Hipolipemiante', 'Tabletas 20mg x 30', 'Para reducir colesterol', '18.00', '540.00', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 7, 1, 1, 7, 1, 1),
(8, 'Salbutamol', 'Salbutamol', 'Broncodilatador', 'Spray 100mcg x 200 dosis', 'Para asma y broncoespasmo', '35.00', '35.00', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 10, 9, 10, 8, 1, 1),
(9, 'Losartán', 'Losartán', 'Antihipertensivo', 'Tabletas 50mg x 30', 'Para hipertensión arterial', '22.00', '660.00', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 7, 1, 1, 1, 1, 1),
(10, 'Diazepam', 'Diazepam', 'Ansiolítico y relajante muscular', 'Tabletas 5mg x 20', 'Para ansiedad y espasmos musculares', '6.50', '130.00', NULL, NULL, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 9, 1, 1, 2, 1, 1);

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
(43, 7, 1, 'venta', '4.00', 'Venta SU1-1764198138', 'venta', 44, '2025-11-26 19:02:18'),
(44, 7, 1, 'venta', '1.00', 'Venta SU1-1764209151', 'venta', 45, '2025-11-26 22:05:51'),
(45, 9, 3, 'venta', '16.00', 'Venta SU1-1764289764', 'venta', 46, '2025-11-27 20:29:24'),
(46, 9, 3, 'venta', '55.00', 'Venta SU1-1764289797', 'venta', 47, '2025-11-27 20:29:57'),
(47, 9, 3, 'venta', '24.00', 'Venta SU1-1764289819', 'venta', 48, '2025-11-27 20:30:19'),
(48, 7, 1, 'venta', '12.00', 'Venta SU1-1764370650', 'venta', 49, '2025-11-28 18:57:30');

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
(1, 1, 5, 1, 1, 'entrada', 36, 'unidad', 'activacion', 1, 'Ingreso por Activacion de lote MED-0001', '2025-11-17 14:35:47', 1),
(2, 2, 4, 1, 1, 'entrada', 54756, 'unidad', 'activacion', 2, 'Ingreso por Activacion de lote MED-0002', '2025-11-17 14:57:06', 1),
(3, 3, 4, 1, 1, 'entrada', 16000, 'unidad', 'activacion', 3, 'Ingreso por Activacion de lote MED-0003', '2025-11-18 10:27:56', 1),
(4, NULL, 4, 1, 1, 'salida', 10, 'unidad', 'venta', 1, 'Venta #SU1-1763476327', '2025-11-18 10:32:07', 1),
(17, 1, 5, 1, 1, 'salida', 1, 'unidad', 'venta', 14, 'Venta SU1-1763515524 (lm_id 1)', '2025-11-18 21:25:24', 1),
(18, 2, 4, 1, 1, 'salida', 10, 'unidad', 'venta', 15, 'Venta SU1-1763516118 (lm_id 2)', '2025-11-18 21:35:18', 1),
(19, 2, 4, 1, 1, 'salida', 10, 'unidad', 'venta', 16, 'Venta SU1-1763516340 (lm_id 2)', '2025-11-18 21:39:00', 1),
(20, 2, 4, 1, 1, 'salida', 1, 'unidad', 'venta', 17, 'Venta SU1-1763516372 (lm_id 2)', '2025-11-18 21:39:32', 1),
(21, 5, 9, 1, 1, 'entrada', 3840, 'unidad', 'compra', 4, 'Ingreso por compra COMP-2025-0004', '2025-11-19 18:13:28', 1),
(22, 4, 8, 1, NULL, 'entrada', 50, 'unidad', 'activacion_lote', 4, 'Activación manual de lote MED-0004', '2025-11-19 18:13:59', 1),
(23, 4, 8, 1, 1, 'entrada', 2500, 'unidad', 'activacion', 4, 'Ingreso por Activacion de lote MED-0004', '2025-11-19 18:13:59', 1),
(24, 6, 6, 1, 1, 'entrada', 40, 'unidad', 'activacion_lote', 6, 'Activación manual de lote MED-0006', '2025-11-19 18:18:14', 1),
(25, 6, 6, 1, 1, 'entrada', 1600, 'unidad', 'activacion', 6, 'Ingreso por Activacion de lote MED-0006', '2025-11-19 18:18:14', 1),
(26, 1, 5, 1, 1, 'salida', 5, 'unidad', 'venta', 18, 'Venta SU1-1763590856 (lm_id 1)', '2025-11-19 18:20:56', 1),
(27, 2, 4, 1, 1, 'salida', 213, 'unidad', 'venta', 20, 'Venta SU1-1763593857 (lm_id 2)', '2025-11-19 19:10:57', 1),
(28, 3, 4, 1, 1, 'salida', 400, 'unidad', 'venta', 20, 'Venta SU1-1763593857 (lm_id 3)', '2025-11-19 19:10:57', 1),
(29, 7, 1, 1, 1, 'entrada', 10, 'unidad', 'compra', 6, 'Ingreso por compra COMP-2025-0006', '2025-11-19 19:16:53', 1),
(30, 8, 1, 1, 1, 'entrada', 10, 'unidad', 'compra', 6, 'Ingreso por compra COMP-2025-0006', '2025-11-19 19:16:53', 1),
(31, 7, 1, 1, 1, 'salida', 4, 'unidad', 'venta', 21, 'Venta SU1-1763596355 (lm_id 7)', '2025-11-19 19:52:35', 1),
(32, 5, 9, 1, 1, 'salida', 2, 'unidad', 'venta', 22, 'Venta SU1-1763596854 (lm_id 5)', '2025-11-19 20:00:54', 1),
(33, 4, 8, 1, 1, 'salida', 5, 'unidad', 'venta', 23, 'Venta SU1-1763597641 (lm_id 4)', '2025-11-19 20:14:01', 1),
(34, 6, 6, 1, 1, 'salida', 3, 'unidad', 'venta', 24, 'Venta SU1-1763597736 (lm_id 6)', '2025-11-19 20:15:36', 1),
(35, 7, 1, 1, 1, 'salida', 1, 'unidad', 'venta', 25, 'Venta SU1-1763597870 (lm_id 7)', '2025-11-19 20:17:50', 1),
(36, 7, 1, 1, 1, 'salida', 3, 'unidad', 'venta', 26, 'Venta SU1-1763598307 (lm_id 7)', '2025-11-19 20:25:07', 1),
(37, 7, 1, 1, 1, 'salida', 2, 'unidad', 'venta', 27, 'Venta SU1-1763598398 (lm_id 7)', '2025-11-19 20:26:38', 1),
(38, 8, 1, 1, 1, 'salida', 4, 'unidad', 'venta', 27, 'Venta SU1-1763598398 (lm_id 8)', '2025-11-19 20:26:38', 1),
(39, 9, 1, 1, 1, 'entrada', 4800, 'unidad', 'activacion_lote', 9, 'Activación manual de lote MED-0009', '2025-11-19 20:32:18', 1),
(40, 9, 1, 1, 1, 'entrada', 2304000, 'unidad', 'activacion', 9, 'Ingreso por Activacion de lote MED-0009', '2025-11-19 20:32:18', 1),
(41, 6, 6, 1, 1, 'salida', 1, 'unidad', 'venta', 28, 'Venta SU1-1763598850 (lm_id 6)', '2025-11-19 20:34:10', 1),
(42, 6, 6, 1, 1, 'salida', 36, 'unidad', 'venta', 29, 'Venta SU1-1763763477 (lm_id 6)', '2025-11-21 18:17:57', 1),
(49, 8, 1, 1, 1, 'salida', 1, 'unidad', 'venta', 36, 'Venta SU1-1763766859 (lm_id 8)', '2025-11-21 19:14:19', 1),
(50, 5, 9, 1, 1, 'salida', 1500, 'unidad', 'venta', 37, 'Venta SU1-1763766905 (lm_id 5)', '2025-11-21 19:15:05', 1),
(51, 4, 8, 1, 1, 'salida', 1, 'unidad', 'venta', 38, 'Venta SU1-1763766954 (lm_id 4)', '2025-11-21 19:15:54', 1),
(52, 8, 1, 1, 1, 'salida', 1, 'unidad', 'venta', 38, 'Venta SU1-1763766954 (lm_id 8)', '2025-11-21 19:15:54', 1),
(53, 5, 9, 1, 1, 'salida', 1, 'unidad', 'venta', 38, 'Venta SU1-1763766954 (lm_id 5)', '2025-11-21 19:15:54', 1),
(54, 10, 3, 1, 1, 'entrada', 2700, 'unidad', 'compra', 8, 'Ingreso por compra COMP-2025-0008', '2025-11-22 16:37:12', 1),
(55, 8, 1, 1, 1, 'salida', 4, 'unidad', 'venta', 39, 'Venta SU1-1763922646 (lm_id 8)', '2025-11-23 14:30:46', 1),
(56, 9, 1, 1, 1, 'salida', 1, 'unidad', 'venta', 40, 'Venta SU1-1763959679 (lm_id 9)', '2025-11-24 00:47:59', 1),
(57, 9, 1, 1, 1, 'salida', 9, 'unidad', 'venta', 41, 'Venta SU1-1764024808 (lm_id 9)', '2025-11-24 18:53:28', 1),
(58, 9, 1, 1, 1, 'salida', 1, 'unidad', 'venta', 42, 'Venta SU1-1764024832 (lm_id 9)', '2025-11-24 18:53:52', 1),
(59, 5, 9, 1, 1, 'salida', 1, 'unidad', 'venta', 42, 'Venta SU1-1764024832 (lm_id 5)', '2025-11-24 18:53:52', 1),
(60, 5, 9, 1, 1, 'salida', 6, 'unidad', 'venta', 43, 'Venta SU1-1764181789 (lm_id 5)', '2025-11-26 14:29:49', 1),
(61, 1, 5, 1, 1, 'baja', 1, 'unidad', 'devolucion', 1, 'Devolución: por fecha vencida', '2025-11-26 18:58:12', 1),
(62, 5, 9, 1, 1, 'salida', 1, 'unidad', 'venta', 44, 'Venta SU1-1764198138 (lm_id 5)', '2025-11-26 19:02:18', 1),
(63, 10, 3, 1, 1, 'salida', 1, 'unidad', 'venta', 45, 'Venta SU1-1764209151 (lm_id 10)', '2025-11-26 22:05:51', 1),
(64, 10, 3, 1, 1, 'baja', 1, 'unidad', 'devolucion', 2, 'Devolución: fdsgsgsd', '2025-11-26 22:10:35', 1),
(65, 10, 3, 1, 1, 'salida', 1, 'unidad', 'cambio', 2, 'Cambio por devolución: fdsgsgsd', '2025-11-26 22:10:35', 1),
(66, 5, 9, 1, 3, 'salida', 4, 'unidad', 'venta', 46, 'Venta SU1-1764289764 (lm_id 5)', '2025-11-27 20:29:24', 1),
(67, 5, 9, 1, 3, 'salida', 3, 'unidad', 'venta', 47, 'Venta SU1-1764289797 (lm_id 5)', '2025-11-27 20:29:57', 1),
(68, 9, 1, 1, 3, 'salida', 5, 'unidad', 'venta', 47, 'Venta SU1-1764289797 (lm_id 9)', '2025-11-27 20:29:57', 1),
(69, 4, 8, 1, 3, 'salida', 2, 'unidad', 'venta', 47, 'Venta SU1-1764289797 (lm_id 4)', '2025-11-27 20:29:57', 1),
(70, 10, 3, 1, 3, 'salida', 3, 'unidad', 'venta', 47, 'Venta SU1-1764289797 (lm_id 10)', '2025-11-27 20:29:57', 1),
(71, 9, 1, 1, 3, 'salida', 3, 'unidad', 'venta', 47, 'Venta SU1-1764289797 (lm_id 9)', '2025-11-27 20:29:57', 1),
(72, 5, 9, 1, 3, 'salida', 3, 'unidad', 'venta', 48, 'Venta SU1-1764289819 (lm_id 5)', '2025-11-27 20:30:19', 1),
(73, 5, 9, 1, 3, 'salida', 3, 'unidad', 'venta', 48, 'Venta SU1-1764289819 (lm_id 5)', '2025-11-27 20:30:19', 1),
(74, 5, 9, 1, 1, 'salida', 3, 'unidad', 'venta', 49, 'Venta SU1-1764370650 (lm_id 5)', '2025-11-28 18:57:30', 1);

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
(2, 'sucursal 2', 'calle siempre viva', '123456789', '2025-11-20 21:18:42', '2025-11-20 21:18:42', 1);

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
(1, 'admin', 'admin', 'admin', '000000000', '000000000', 'admin@admin.com', 'admin calle admin', 'admin', 'dlo5ZmZvbmRjME41dGlDY01tTGcrUT09', NULL, NULL, '2025-11-06 10:17:03', '2025-11-06 10:17:03', 1, 1, 1),
(2, 'usuario', 'usuario', 'usuario', '1235497866656', '122565165464', 'usuario@usuario.usuario', 'usuariousuario', 'usuario', 'Q0oxTTdMNktnMzhoQjBDOXFJWXI1Zz09', NULL, NULL, '2025-11-20 21:30:31', '2025-11-27 18:04:46', 0, 2, 3),
(3, 'gerente', 'gerente', 'gerente', '123321321', '', '', 'gerente', 'gerente', 'ZFA3UHhUdGwrVERjWjVCSmhWaFJpdz09', NULL, NULL, '2025-11-27 18:05:22', '2025-11-27 20:54:02', 1, 1, 2);

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
(1, 'SU1-1763476327', '2025-11-18 10:32:07', 8, 1, 1, NULL, '10.00', '0.00', '10.00', '2025-11-18 10:32:07', 'efectivo', 'nota de venta', 'emitida', NULL, 1),
(14, 'SU1-1763515524', '2025-11-18 21:25:24', 3, 1, 1, NULL, '4.00', '0.00', '4.00', '2025-11-26 18:58:12', 'efectivo', 'nota de venta', 'devuelto', NULL, 1),
(15, 'SU1-1763516118', '2025-11-18 21:35:18', 7, 1, 1, NULL, '10.00', '0.00', '10.00', '2025-11-18 21:35:18', 'efectivo', 'nota de venta', 'emitida', NULL, 1),
(16, 'SU1-1763516340', '2025-11-18 21:39:00', NULL, 1, 1, NULL, '10.00', '0.00', '10.00', '2025-11-18 21:39:00', 'efectivo', '', 'emitida', NULL, 1),
(17, 'SU1-1763516372', '2025-11-18 21:39:32', NULL, 1, 1, NULL, '1.00', '0.00', '1.00', '2025-11-18 21:39:32', 'efectivo', '', 'emitida', NULL, 1),
(18, 'SU1-1763590856', '2025-11-19 18:20:56', 4, 1, 1, NULL, '20.00', '0.00', '20.00', '2025-11-19 18:20:56', 'efectivo', '', 'emitida', NULL, 1),
(20, 'SU1-1763593857', '2025-11-19 19:10:57', NULL, 1, 1, NULL, '613.00', '0.00', '613.00', '2025-11-19 19:10:57', 'efectivo', '', 'emitida', NULL, 1),
(21, 'SU1-1763596355', '2025-11-19 19:52:35', NULL, 1, 1, NULL, '220.00', '0.00', '220.00', '2025-11-19 19:52:35', 'efectivo', '', 'emitida', NULL, 1),
(22, 'SU1-1763596854', '2025-11-19 20:00:54', NULL, 1, 1, NULL, '8.00', '0.00', '8.00', '2025-11-19 20:00:54', 'efectivo', 'nota de venta', 'emitida', NULL, 1),
(23, 'SU1-1763597641', '2025-11-19 20:14:01', NULL, 1, 1, NULL, '60.00', '0.00', '60.00', '2025-11-19 20:14:01', 'QR', 'factura', 'emitida', NULL, 1),
(24, 'SU1-1763597736', '2025-11-19 20:15:36', NULL, 1, 1, NULL, '18.00', '0.00', '18.00', '2025-11-19 20:15:36', 'targeta', '', '\'emitida\'', NULL, 1),
(25, 'SU1-1763597870', '2025-11-19 20:17:50', NULL, 1, 1, NULL, '50.00', '0.00', '50.00', '2025-11-19 20:17:50', '', '', '\'emitida\'', NULL, 1),
(26, 'SU1-1763598307', '2025-11-19 20:25:07', NULL, 1, 1, NULL, '150.00', '0.00', '150.00', '2025-11-19 20:25:07', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(27, 'SU1-1763598398', '2025-11-19 20:26:38', NULL, 1, 1, NULL, '300.00', '0.00', '300.00', '2025-11-19 20:26:38', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(28, 'SU1-1763598850', '2025-11-19 20:34:10', NULL, 1, 1, NULL, '6.00', '0.00', '6.00', '2025-11-19 20:34:10', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(29, 'SU1-1763763477', '2025-11-21 18:17:57', NULL, 1, 1, NULL, '216.00', '0.00', '216.00', '2025-11-21 18:17:57', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(36, 'SU1-1763766859', '2025-11-21 19:14:19', NULL, 1, 1, NULL, '2.00', '0.00', '2.00', '2025-11-21 19:14:19', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(37, 'SU1-1763766905', '2025-11-21 19:15:05', NULL, 1, 1, NULL, '6000.00', '0.00', '6000.00', '2025-11-21 19:15:05', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(38, 'SU1-1763766954', '2025-11-21 19:15:54', NULL, 1, 1, NULL, '18.00', '0.00', '18.00', '2025-11-21 19:15:54', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(39, 'SU1-1763922646', '2025-11-23 14:30:46', NULL, 1, 1, NULL, '8.00', '0.00', '8.00', '2025-11-23 14:30:46', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(40, 'SU1-1763959679', '2025-11-24 00:47:59', NULL, 1, 1, NULL, '2.00', '0.00', '2.00', '2025-11-24 00:47:59', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(41, 'SU1-1764024808', '2025-11-24 18:53:28', NULL, 1, 1, NULL, '18.00', '0.00', '18.00', '2025-11-24 18:53:28', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(42, 'SU1-1764024832', '2025-11-24 18:53:52', NULL, 1, 1, NULL, '6.00', '0.00', '6.00', '2025-11-24 18:53:52', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(43, 'SU1-1764181789', '2025-11-26 14:29:49', NULL, 1, 1, NULL, '24.00', '0.00', '24.00', '2025-11-26 14:29:49', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(44, 'SU1-1764198138', '2025-11-26 19:02:18', 3, 1, 1, 7, '4.00', '0.00', '4.00', '2025-11-26 19:02:18', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(45, 'SU1-1764209151', '2025-11-26 22:05:51', NULL, 1, 1, 7, '1.00', '0.00', '1.00', '2025-11-26 22:10:35', 'efectivo', 'nota de venta', 'devuelto', NULL, 1),
(46, 'SU1-1764289764', '2025-11-27 20:29:24', NULL, 3, 1, 9, '16.00', '0.00', '16.00', '2025-11-27 20:29:24', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(47, 'SU1-1764289797', '2025-11-27 20:29:57', 3, 3, 1, 9, '55.00', '0.00', '55.00', '2025-11-27 20:29:57', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(48, 'SU1-1764289819', '2025-11-27 20:30:19', 3, 3, 1, 9, '24.00', '0.00', '24.00', '2025-11-27 20:30:19', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1),
(49, 'SU1-1764370650', '2025-11-28 18:57:30', NULL, 1, 1, 7, '12.00', '0.00', '12.00', '2025-11-28 18:57:30', 'efectivo', 'nota de venta', '\'emitida\'', NULL, 1);

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
  ADD UNIQUE KEY `ux_usuarios_correo` (`us_correo`),
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
-- AUTO_INCREMENT de la tabla `caja`
--
ALTER TABLE `caja`
  MODIFY `caja_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `cl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `co_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `configuracion_empresa`
--
ALTER TABLE `configuracion_empresa`
  MODIFY `ce_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  MODIFY `dc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `detalle_peticion`
--
ALTER TABLE `detalle_peticion`
  MODIFY `dp_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_transferencia`
--
ALTER TABLE `detalle_transferencia`
  MODIFY `dt_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `dv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  MODIFY `dev_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `fa_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

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
  MODIFY `hl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `informes`
--
ALTER TABLE `informes`
  MODIFY `inf_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de la tabla `inventarios`
--
ALTER TABLE `inventarios`
  MODIFY `inv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `laboratorios`
--
ALTER TABLE `laboratorios`
  MODIFY `la_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `lote_medicamento`
--
ALTER TABLE `lote_medicamento`
  MODIFY `lm_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `medicamento`
--
ALTER TABLE `medicamento`
  MODIFY `med_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `merma`
--
ALTER TABLE `merma`
  MODIFY `me_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimiento_caja`
--
ALTER TABLE `movimiento_caja`
  MODIFY `mc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  MODIFY `mi_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `peticiones`
--
ALTER TABLE `peticiones`
  MODIFY `pe_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `pr_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `su_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `transferencias`
--
ALTER TABLE `transferencias`
  MODIFY `tr_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `uso_farmacologico`
--
ALTER TABLE `uso_farmacologico`
  MODIFY `uf_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `us_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `ve_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `via_de_administracion`
--
ALTER TABLE `via_de_administracion`
  MODIFY `vd_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

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




/* Breve descripción de cada tabla (para usar como prompt o documentación)
Tabla	Descripción breve


sucursales	Registra las sucursales físicas donde se manejan inventarios y ventas.
proveedores	Contiene los proveedores de medicamentos, sin vinculación directa a laboratorios.
laboratorios	Clasifica los laboratorios productores de medicamentos.
forma_farmaceutica	Define la forma farmacéutica de cada medicamento (jarabe, cápsula, inyectable, etc.).
uso_farmacologico	Clasifica medicamentos según su uso (analgésico, antibiótico, etc.).
via_de_administracion	Indica la vía de aplicación del medicamento (oral, tópica, etc.).
roles	Define los roles del sistema (administrador, gerente, usuario).
usuarios	Registra los usuarios del sistema y sus credenciales.
clientes	Guarda los datos de clientes frecuentes para emitir notas y facturas.
medicamento	Almacena la información base de los medicamentos (composición, código de barras, laboratorio, presentación, precios).
lote_medicamento	Registra los lotes físicos de medicamentos, cantidades por caja/blíster/unidad, fechas y sucursal donde se encuentran. Fuente principal de stock.
inventarios	Tabla resumen del stock total por medicamento y sucursal; consolida datos de los lotes.
compras	Encabezado de cada compra o reabastecimiento de medicamentos a la farmacia.
detalle_compra	Detalla los medicamentos comprados en cada orden de compra.
ventas	Encabezado de las ventas realizadas en las sucursales.
detalle_venta	Detalle de cada producto vendido (por unidad o por caja), con referencia opcional a lote.
factura	Registro de facturas generadas en ventas, con posibilidad de integrarse a SIAT.
facturacion_electronica	Tabla preparada para la integración con el sistema SIAT de Bolivia (CUF, QR, estado, etc.).
movimiento_inventario	Registro histórico de movimientos de stock (entradas, salidas, transferencias, ajustes).
historial_lote	Historial de acciones sobre lotes (creación, activación, caducidad, baja, etc.).
merma	Registra pérdidas de inventario por deterioro, vencimiento u otros motivos.
caja	Define las cajas de punto de venta asociadas a cada sucursal.
movimiento_caja	Registra entradas y salidas de efectivo de cada caja.
informes	Guarda configuraciones o reportes generados por los usuarios.
devoluciones	Registra las devoluciones de productos por los clientes, sin reingreso automático a stock. */