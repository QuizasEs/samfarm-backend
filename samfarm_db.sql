-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2025 at 03:10 PM
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
  `caja_nombre` varchar(120) DEFAULT 'Principal',
  `caja_saldo_inicial` decimal(14,2) DEFAULT 0.00,
  `caja_activa` tinyint(1) NOT NULL DEFAULT 1,
  `caja_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caja`
--

INSERT INTO `caja` (`caja_id`, `su_id`, `caja_nombre`, `caja_saldo_inicial`, `caja_activa`, `caja_creado_en`) VALUES
(1, 1, 'Caja Principal', 1000.00, 1, '2025-11-06 11:06:09');

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
(1, 'Juan', 'Pérez', 'García', '71234567', 'juan.perez@email.com', 'Av. Siempre Viva 123', '1234567', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1),
(2, 'María', 'López', 'Martínez', '71234568', 'maria.lopez@email.com', 'Calle Falsa 456', '1234568', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1),
(3, 'Carlos', 'Rodríguez', 'Silva', '71234569', 'carlos.rodriguez@email.com', 'Av. Libertador 789', '1234569', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1),
(4, 'Ana', 'Gómez', 'Vargas', '71234570', 'ana.gomez@email.com', 'Calle Real 321', '1234570', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1),
(5, 'Luis', 'Fernández', 'Castro', '71234571', 'luis.fernandez@email.com', 'Av. Bolívar 654', '1234571', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1);

-- --------------------------------------------------------

--
-- Table structure for table `codigo_barras`
--

CREATE TABLE `codigo_barras` (
  `cb_id` bigint(20) NOT NULL,
  `cb_codigo` varchar(255) NOT NULL,
  `la_id` bigint(20) UNSIGNED NOT NULL,
  `cb_creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'COMP-001-2024', '2024-01-15 08:00:00', 1, 1, 1, 1, 180.00, 25.20, 205.20, 'FAC-001-2024', '2024-01-15', 'compra', '123456789', 'Farmacorp S.A.', '2025-11-06 11:06:07', '2025-11-06 11:06:07', 1),
(2, 'COMP-002-2024', '2024-01-10 10:15:00', 2, 1, 1, 2, 176.00, 24.64, 200.64, 'FAC-002-2024', '2024-01-10', 'compra', '123456790', 'Droguería Inti', '2025-11-06 11:06:07', '2025-11-06 11:06:07', 1),
(3, 'COMP-003-2024', '2024-03-01 14:20:00', 3, 1, 1, 3, 600.00, 84.00, 684.00, 'FAC-003-2024', '2024-03-01', 'compra', '123456791', 'Laboratorios Bolivia', '2025-11-06 11:06:07', '2025-11-06 11:06:07', 1),
(4, 'COMP-2025-0001', '2025-11-07 20:36:36', 3, 1, 1, 2, 536.00, 69.68, 605.68, '13123123', '2025-11-22', 'compra', NULL, 'sofir', '2025-11-07 20:36:36', '2025-11-07 20:36:36', 1);

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
(1, 1, 1, 1, 100, 1.80, 0.00, 180.00, 1),
(2, 2, 2, 3, 80, 2.20, 0.00, 176.00, 1),
(3, 3, 3, 4, 50, 12.00, 0.00, 600.00, 1),
(4, 4, 10, 11, 14, 16.00, 0.00, 224.00, 1),
(5, 4, 1, 12, 12, 26.00, 0.00, 312.00, 1);

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
  `dv_precio_unitario` decimal(12,2) NOT NULL,
  `dv_descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `dv_subtotal` decimal(14,2) NOT NULL,
  `dv_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detalle_venta`
--

INSERT INTO `detalle_venta` (`dv_id`, `ve_id`, `med_id`, `lm_id`, `dv_cantidad`, `dv_precio_unitario`, `dv_descuento`, `dv_subtotal`, `dv_estado`) VALUES
(1, 1, 1, 1, 10, 2.50, 0.00, 25.00, 1),
(2, 1, 2, 3, 5, 3.00, 0.50, 14.50, 1),
(3, 1, 4, 5, 2, 4.50, 0.00, 9.00, 1),
(4, 2, 1, 1, 8, 2.50, 0.00, 20.00, 1),
(5, 2, 5, 6, 1, 12.00, 4.00, 8.00, 1),
(6, 3, 3, 4, 3, 15.00, 0.00, 45.00, 1),
(7, 3, 6, 7, 2, 8.50, 0.00, 17.00, 1);

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
(1, 1, 1, 1, 1, 'FAC-001-2024', '2024-03-20 09:15:00', 51.87, 'ABC123XYZ', 'CUF001234567890', 1, '2025-11-06 11:06:09'),
(2, 2, 2, 1, 1, 'FAC-002-2024', '2024-03-21 10:30:00', 31.92, 'DEF456UVW', 'CUF001234567891', 1, '2025-11-06 11:06:09'),
(3, 3, 3, 1, 1, 'FAC-003-2024', '2024-03-22 14:45:00', 71.25, 'GHI789RST', 'CUF001234567892', 1, '2025-11-06 11:06:09');

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
(1, 11, 1, 'creacion', 'Lote creado automáticamente por compra #COMP-2025-0001 en estado \'en_espera\'.', '2025-11-07 20:36:36'),
(2, 12, 1, 'creacion', 'Lote creado automáticamente por compra #COMP-2025-0001 en estado \'en_espera\'.', '2025-11-07 20:36:36');

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
(1, 'Stock Mínimo', 'stock', 1, '{\"filtro_stock\": \"minimo\", \"sucursal_id\": 1}', '2025-11-06 11:06:10'),
(2, 'Ventas Mensuales', 'ventas', 1, '{\"mes\": 3, \"anio\": 2024, \"sucursal_id\": 1}', '2025-11-06 11:06:10'),
(3, 'Productos Más Vendidos', 'ventas', 1, '{\"orden\": \"cantidad\", \"limite\": 10, \"periodo\": \"mes\"}', '2025-11-06 11:06:10'),
(4, 'Compra COMP-2025-0001 - sofir', 'compra', 1, '{\"compra_id\":4,\"numero_compra\":\"COMP-2025-0001\",\"proveedor_id\":\"2\",\"laboratorio_id\":\"3\",\"sucursal_id\":\"1\",\"fecha_factura\":\"2025-11-22\",\"numero_factura\":\"13123123\",\"razon_social\":\"sofir\",\"subtotal\":\"536.00\",\"impuestos\":\"69.68\",\"total\":\"605.68\",\"cantidad_lotes\":2,\"lotes\":[{\"medicamento_id\":\"10\",\"numero_lote\":\"MED-11\",\"cantidad\":14,\"precio_compra\":16,\"precio_venta\":167,\"vencimiento\":\"2025-11-12\"},{\"medicamento_id\":\"1\",\"numero_lote\":\"MED-12\",\"cantidad\":12,\"precio_compra\":26,\"precio_venta\":37,\"vencimiento\":\"2025-11-27\"}]}', '2025-11-07 20:36:36');

-- --------------------------------------------------------

--
-- Table structure for table `inventarios`
--

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

--
-- Dumping data for table `inventarios`
--

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
  `la_estado` tinyint(1) NOT NULL DEFAULT 1,
  `pr_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laboratorios`
--

INSERT INTO `laboratorios` (`la_id`, `la_nombre_comercial`, `la_logo`, `la_creado_en`, `la_actualizado_en`, `la_estado`, `pr_id`) VALUES
(1, 'Bayer', 'bayer.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 1),
(2, 'Pfizer', 'pfizer.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 2),
(3, 'Roche', 'roche.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 3),
(4, 'Novartis', 'novartis.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 4),
(5, 'GSK', 'gsk.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 5),
(6, 'Sanofi', 'sanofi.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 1),
(7, 'Merck', 'merck.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 2),
(8, 'AstraZeneca', 'astrazeneca.png', '2025-11-06 11:06:04', '2025-11-06 11:06:04', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `lote_medicamento`
--

CREATE TABLE `lote_medicamento` (
  `lm_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `pc_id` bigint(20) UNSIGNED DEFAULT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `pr_id` bigint(20) UNSIGNED DEFAULT NULL,
  `lm_numero_lote` varchar(200) DEFAULT NULL,
  `lm_cantidad_inicial` int(11) NOT NULL DEFAULT 0,
  `lm_cantidad_actual` int(11) NOT NULL DEFAULT 0,
  `lm_precio_compra` decimal(12,2) DEFAULT NULL,
  `lm_precio_venta` decimal(12,2) DEFAULT NULL,
  `lm_fecha_ingreso` datetime NOT NULL DEFAULT current_timestamp(),
  `lm_fecha_vencimiento` date DEFAULT NULL,
  `lm_estado` enum('en_espera','activo','terminado','caducado','devuelto','bloqueado') NOT NULL DEFAULT 'en_espera',
  `lm_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `lm_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lote_medicamento`
--

INSERT INTO `lote_medicamento` (`lm_id`, `med_id`, `pc_id`, `su_id`, `pr_id`, `lm_numero_lote`, `lm_cantidad_inicial`, `lm_cantidad_actual`, `lm_precio_compra`, `lm_precio_venta`, `lm_fecha_ingreso`, `lm_fecha_vencimiento`, `lm_estado`, `lm_creado_en`, `lm_actualizado_en`) VALUES
(1, 1, NULL, 1, 1, 'LOTE-001-2024', 100, 85, 1.80, 2.50, '2024-01-15 08:00:00', '2025-12-31', 'en_espera', '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(2, 1, NULL, 1, 1, 'LOTE-002-2024', 150, 150, 1.75, 2.50, '2024-02-20 09:30:00', '2026-01-31', 'en_espera', '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(3, 2, NULL, 1, 2, 'LOTE-003-2024', 80, 60, 2.20, 3.00, '2024-01-10 10:15:00', '2025-11-30', 'en_espera', '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(4, 3, NULL, 1, 3, 'LOTE-004-2024', 50, 35, 12.00, 15.00, '2024-03-01 14:20:00', '2025-09-30', 'en_espera', '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(5, 4, NULL, 1, 4, 'LOTE-005-2024', 120, 95, 3.20, 4.50, '2024-02-15 11:45:00', '2026-02-28', 'en_espera', '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(6, 5, NULL, 1, 5, 'LOTE-006-2024', 60, 42, 9.50, 12.00, '2024-01-25 16:30:00', '2025-10-31', 'en_espera', '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(7, 6, NULL, 1, 1, 'LOTE-007-2024', 90, 90, 6.80, 8.50, '2024-03-10 08:45:00', '2026-03-31', 'en_espera', '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(8, 7, NULL, 1, 2, 'LOTE-008-2024', 40, 25, 14.50, 18.00, '2024-02-05 13:15:00', '2025-12-15', 'activo', '2025-11-06 11:06:06', '2025-11-07 21:09:48'),
(9, 8, NULL, 1, 3, 'LOTE-009-2024', 30, 18, 28.00, 35.00, '2024-01-30 15:20:00', '2025-11-30', 'en_espera', '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(10, 9, NULL, 1, 4, 'LOTE-010-2024', 70, 55, 17.50, 22.00, '2024-03-05 10:00:00', '2026-04-30', 'en_espera', '2025-11-06 11:06:06', '2025-11-06 11:06:06'),
(11, 10, NULL, 1, 2, 'MED-11', 14, 14, 16.00, 167.00, '2025-11-07 20:36:36', '2025-11-12', 'en_espera', '2025-11-07 20:36:36', '2025-11-07 20:36:36'),
(12, 1, NULL, 1, 2, 'MED-12', 12, 12, 26.00, 37.00, '2025-11-07 20:36:36', '2025-11-27', 'en_espera', '2025-11-07 20:36:36', '2025-11-07 20:36:36');

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
  `med_precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `med_precio_caja` decimal(12,2) DEFAULT NULL,
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

INSERT INTO `medicamento` (`med_id`, `med_nombre_quimico`, `med_principio_activo`, `med_accion_farmacologica`, `med_presentacion`, `med_descripcion`, `med_precio_unitario`, `med_precio_caja`, `med_creado_en`, `med_actualizado_en`, `uf_id`, `ff_id`, `vd_id`, `la_id`, `su_id`, `us_id`) VALUES
(1, 'Paracetamol', 'Paracetamol', 'Analgésico y antipirético', 'Tabletas 500mg x 10', 'Analgésico para dolor leve a moderado', 2.50, 25.00, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 1, 1, 1, 1, 1, 1),
(2, 'Ibuprofeno', 'Ibuprofeno', 'Antiinflamatorio no esteroideo', 'Tabletas 400mg x 20', 'Antiinflamatorio y analgésico', 3.00, 60.00, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 3, 1, 1, 2, 1, 1),
(3, 'Amoxicilina', 'Amoxicilina', 'Antibiótico de amplio espectro', 'Cápsulas 500mg x 12', 'Antibiótico para infecciones bacterianas', 15.00, 180.00, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 2, 2, 1, 3, 1, 1),
(4, 'Loratadina', 'Loratadina', 'Antihistamínico', 'Tabletas 10mg x 10', 'Para alergias y rinitis', 4.50, 45.00, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 4, 1, 1, 4, 1, 1),
(5, 'Omeprazol', 'Omeprazol', 'Inhibidor de bomba de protones', 'Cápsulas 20mg x 14', 'Para úlceras y reflujo gastroesofágico', 12.00, 168.00, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 8, 2, 1, 5, 1, 1),
(6, 'Metformina', 'Metformina', 'Hipoglucemiante oral', 'Tabletas 850mg x 30', 'Para diabetes tipo 2', 8.50, 255.00, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 7, 1, 1, 6, 1, 1),
(7, 'Atorvastatina', 'Atorvastatina', 'Hipolipemiante', 'Tabletas 20mg x 30', 'Para reducir colesterol', 18.00, 540.00, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 7, 1, 1, 7, 1, 1),
(8, 'Salbutamol', 'Salbutamol', 'Broncodilatador', 'Spray 100mcg x 200 dosis', 'Para asma y broncoespasmo', 35.00, 35.00, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 10, 9, 10, 8, 1, 1),
(9, 'Losartán', 'Losartán', 'Antihipertensivo', 'Tabletas 50mg x 30', 'Para hipertensión arterial', 22.00, 660.00, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 7, 1, 1, 1, 1, 1),
(10, 'Diazepam', 'Diazepam', 'Ansiolítico y relajante muscular', 'Tabletas 5mg x 20', 'Para ansiedad y espasmos musculares', 6.50, 130.00, '2025-11-06 11:06:05', '2025-11-06 11:06:05', 9, 1, 1, 2, 1, 1);

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
(1, 1, 1, 1, 1, 2, 'Producto dañado en almacén', '2025-11-06 11:06:10'),
(2, 2, 3, 1, 1, 1, 'Caducidad próxima', '2025-11-06 11:06:10');

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
(1, 1, 1, 'ingreso', 51.87, 'Venta VENT-001-2024', 'venta', 1, '2025-11-06 11:06:09'),
(2, 1, 1, 'ingreso', 31.92, 'Venta VENT-002-2024', 'venta', 2, '2025-11-06 11:06:09'),
(3, 1, 1, 'ingreso', 71.25, 'Venta VENT-003-2024', 'venta', 3, '2025-11-06 11:06:09');

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
  `mi_fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `mi_referencia_tipo` varchar(30) DEFAULT NULL,
  `mi_referencia_id` bigint(20) DEFAULT NULL,
  `mi_motivo` text DEFAULT NULL,
  `mi_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `mi_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movimiento_inventario`
--

INSERT INTO `movimiento_inventario` (`mi_id`, `lm_id`, `med_id`, `su_id`, `us_id`, `mi_tipo`, `mi_cantidad`, `mi_unidad`, `mi_fecha`, `mi_referencia_tipo`, `mi_referencia_id`, `mi_motivo`, `mi_creado_en`, `mi_estado`) VALUES
(1, 1, 1, 1, 1, 'ingreso', 100, 'unidad', '2025-11-06 11:06:09', 'compra', 1, 'Compra inicial', '2025-11-06 11:06:09', 1),
(2, 1, 1, 1, 1, 'salida', 15, 'unidad', '2025-11-06 11:06:09', 'venta', 1, 'Venta al público', '2025-11-06 11:06:09', 1),
(3, 3, 2, 1, 1, 'ingreso', 80, 'unidad', '2025-11-06 11:06:09', 'compra', 2, 'Compra inicial', '2025-11-06 11:06:09', 1),
(4, 3, 2, 1, 1, 'salida', 20, 'unidad', '2025-11-06 11:06:09', 'venta', 1, 'Venta al público', '2025-11-06 11:06:09', 1),
(5, 4, 3, 1, 1, 'ingreso', 50, 'unidad', '2025-11-06 11:06:09', 'compra', 3, 'Compra inicial', '2025-11-06 11:06:09', 1),
(6, 4, 3, 1, 1, 'salida', 15, 'unidad', '2025-11-06 11:06:09', 'venta', 3, 'Venta al público', '2025-11-06 11:06:09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `presentacion_cantidad`
--

CREATE TABLE `presentacion_cantidad` (
  `pc_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `pc_nombre` varchar(100) NOT NULL,
  `pc_cantidad` int(11) NOT NULL,
  `pc_unidad_base` varchar(50) DEFAULT 'unidad',
  `pc_estado` enum('activo','inactivo') DEFAULT 'activo',
  `pc_fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(6, 'MediBol', NULL, NULL, '23456793', '123456793', 'Av. Circunvalación 987, Zona Este', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1);

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
(1, 'Sucursal Central', 'Av. Principal 123, Ciudad', '+591-2-1234567', '2025-11-06 10:17:03', '2025-11-06 10:17:03', 1);

-- --------------------------------------------------------

--
-- Table structure for table `uso_farmacologico`
--

CREATE TABLE `uso_farmacologico` (
  `uf_id` bigint(20) UNSIGNED NOT NULL,
  `uf_nombre` varchar(250) NOT NULL,
  `uf_imagen` varchar(255) DEFAULT NULL,
  `uf_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `uf_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `uf_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uso_farmacologico`
--

INSERT INTO `uso_farmacologico` (`uf_id`, `uf_nombre`, `uf_imagen`, `uf_creado_en`, `uf_actualizado_en`, `uf_estado`) VALUES
(1, 'Analgésico', 'analgesico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(2, 'Antibiótico', 'antibiotico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(3, 'Antiinflamatorio', 'antiinflamatorio.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(4, 'Antihistamínico', 'antihistaminico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(5, 'Antipirético', 'antipiretico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(6, 'Antiséptico', 'antiseptico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(7, 'Cardiovascular', 'cardiovascular.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(8, 'Digestivo', 'digestivo.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(9, 'Dermatológico', 'dermatologico.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1),
(10, 'Respiratorio', 'respiratorio.png', '2025-11-06 11:06:03', '2025-11-06 11:06:03', 1);

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
(1, 'admin', 'admin', 'admin', '000000000', '000000000', 'admin@admin.com', 'admin calle admin', 'admin', 'dlo5ZmZvbmRjME41dGlDY01tTGcrUT09', NULL, NULL, '2025-11-06 10:17:03', '2025-11-06 10:17:03', 1, 1, 1);

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

--
-- Dumping data for table `ventas`
--

INSERT INTO `ventas` (`ve_id`, `ve_numero_documento`, `ve_fecha_emision`, `cl_id`, `us_id`, `su_id`, `ve_subtotal`, `ve_impuesto`, `ve_total`, `ve_creado_en`, `ve_actualizado_en`, `ve_estado`, `ve_tipo_documento`, `ve_estado_documento`, `ve_numero_control`) VALUES
(1, 'VENT-001-2024', '2024-03-20 09:15:00', 1, 1, 1, 45.50, 6.37, 51.87, '2025-11-06 11:06:08', '2025-11-06 11:06:08', 1, 'venta', 'emitida', NULL),
(2, 'VENT-002-2024', '2024-03-21 10:30:00', 2, 1, 1, 28.00, 3.92, 31.92, '2025-11-06 11:06:08', '2025-11-06 11:06:08', 1, 'venta', 'emitida', NULL),
(3, 'VENT-003-2024', '2024-03-22 14:45:00', 3, 1, 1, 62.50, 8.75, 71.25, '2025-11-06 11:06:08', '2025-11-06 11:06:08', 1, 'venta', 'emitida', NULL);

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
  ADD KEY `fk_caja_sucursal` (`su_id`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`cl_id`),
  ADD KEY `ix_clientes_carnet` (`cl_carnet`);

--
-- Indexes for table `codigo_barras`
--
ALTER TABLE `codigo_barras`
  ADD PRIMARY KEY (`cb_id`),
  ADD KEY `la_id` (`la_id`);

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
-- Indexes for table `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD PRIMARY KEY (`dc_id`),
  ADD KEY `ix_dc_co` (`co_id`),
  ADD KEY `ix_dc_med` (`med_id`),
  ADD KEY `ix_dc_lm` (`lm_id`);

--
-- Indexes for table `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`dv_id`),
  ADD KEY `ix_dv_ve` (`ve_id`),
  ADD KEY `ix_dv_med` (`med_id`),
  ADD KEY `ix_dv_lm` (`lm_id`);

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
  ADD UNIQUE KEY `ux_inv_su_med_lm` (`su_id`,`med_id`,`lm_id`),
  ADD KEY `ix_inv_med` (`med_id`),
  ADD KEY `fk_inv_lm` (`lm_id`);

--
-- Indexes for table `laboratorios`
--
ALTER TABLE `laboratorios`
  ADD PRIMARY KEY (`la_id`),
  ADD UNIQUE KEY `ux_laboratorios_nombre` (`la_nombre_comercial`),
  ADD KEY `fk_laboratorios_pr` (`pr_id`);

--
-- Indexes for table `lote_medicamento`
--
ALTER TABLE `lote_medicamento`
  ADD PRIMARY KEY (`lm_id`),
  ADD KEY `ix_lm_med` (`med_id`),
  ADD KEY `ix_lm_su` (`su_id`),
  ADD KEY `ix_lm_pr` (`pr_id`),
  ADD KEY `ix_lm_numero` (`lm_numero_lote`),
  ADD KEY `fk_lote_presentacion` (`pc_id`);

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
-- Indexes for table `presentacion_cantidad`
--
ALTER TABLE `presentacion_cantidad`
  ADD PRIMARY KEY (`pc_id`),
  ADD KEY `fk_presentacion_cantidad_medicamento` (`med_id`);

--
-- Indexes for table `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`pr_id`),
  ADD KEY `ix_proveedores_nit` (`pr_nit`);

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
  ADD UNIQUE KEY `ux_usuarios_correo` (`us_correo`),
  ADD KEY `fk_usuarios_sucursales` (`su_id`),
  ADD KEY `fk_usuarios_roles` (`ro_id`);

--
-- Indexes for table `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`ve_id`),
  ADD KEY `fk_ventas_clientes` (`cl_id`),
  ADD KEY `fk_ventas_sucursales` (`su_id`),
  ADD KEY `fk_ventas_usuarios` (`us_id`);

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
  MODIFY `caja_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `cl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `codigo_barras`
--
ALTER TABLE `codigo_barras`
  MODIFY `cb_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compras`
--
ALTER TABLE `compras`
  MODIFY `co_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `detalle_compra`
--
ALTER TABLE `detalle_compra`
  MODIFY `dc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `dv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `factura`
--
ALTER TABLE `factura`
  MODIFY `fa_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `forma_farmaceutica`
--
ALTER TABLE `forma_farmaceutica`
  MODIFY `ff_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `historial_lote`
--
ALTER TABLE `historial_lote`
  MODIFY `hl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `informes`
--
ALTER TABLE `informes`
  MODIFY `inf_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventarios`
--
ALTER TABLE `inventarios`
  MODIFY `inv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `laboratorios`
--
ALTER TABLE `laboratorios`
  MODIFY `la_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `lote_medicamento`
--
ALTER TABLE `lote_medicamento`
  MODIFY `lm_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `mc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  MODIFY `mi_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `presentacion_cantidad`
--
ALTER TABLE `presentacion_cantidad`
  MODIFY `pc_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `pr_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `ro_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sucursales`
--
ALTER TABLE `sucursales`
  MODIFY `su_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `uso_farmacologico`
--
ALTER TABLE `uso_farmacologico`
  MODIFY `uf_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `us_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ventas`
--
ALTER TABLE `ventas`
  MODIFY `ve_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `fk_caja_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `codigo_barras`
--
ALTER TABLE `codigo_barras`
  ADD CONSTRAINT `fk_codigo_barras_laboratorio` FOREIGN KEY (`la_id`) REFERENCES `laboratorios` (`la_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `fk_dv_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dv_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dv_venta` FOREIGN KEY (`ve_id`) REFERENCES `ventas` (`ve_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `fk_factura_cliente` FOREIGN KEY (`cl_id`) REFERENCES `clientes` (`cl_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_usuario` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_venta` FOREIGN KEY (`ve_id`) REFERENCES `ventas` (`ve_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_inv_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inv_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inv_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `laboratorios`
--
ALTER TABLE `laboratorios`
  ADD CONSTRAINT `fk_laboratorios_proveedores` FOREIGN KEY (`pr_id`) REFERENCES `proveedores` (`pr_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `lote_medicamento`
--
ALTER TABLE `lote_medicamento`
  ADD CONSTRAINT `fk_lm_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_proveedor` FOREIGN KEY (`pr_id`) REFERENCES `proveedores` (`pr_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lm_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lote_presentacion` FOREIGN KEY (`pc_id`) REFERENCES `presentacion_cantidad` (`pc_id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
-- Constraints for table `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD CONSTRAINT `fk_mi_lm` FOREIGN KEY (`lm_id`) REFERENCES `lote_medicamento` (`lm_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mi_med` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mi_su` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mi_us` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `presentacion_cantidad`
--
ALTER TABLE `presentacion_cantidad`
  ADD CONSTRAINT `fk_presentacion_cantidad_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_ventas_clientes` FOREIGN KEY (`cl_id`) REFERENCES `clientes` (`cl_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ventas_sucursales` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ventas_usuarios` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
