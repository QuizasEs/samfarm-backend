-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2025 at 08:10 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================
-- DATABASE: `samfarm_db`
-- ============================================================

-- --------------------------------------------------------
-- Table structure for table `clientes`
-- --------------------------------------------------------
CREATE TABLE `clientes` (
  `cl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cl_nombre` varchar(200) NOT NULL,
  `cl_tipo_documento` varchar(30) DEFAULT NULL,
  `cl_documento` varchar(60) DEFAULT NULL,
  `cl_telefono` varchar(30) DEFAULT NULL,
  `cl_correo` varchar(120) DEFAULT NULL,
  `cl_direccion` text DEFAULT NULL,
  `cl_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `cl_estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`cl_id`),
  KEY `ix_clientes_documento` (`cl_documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `clientes` VALUES
(1, 'Juan Pérez', 'CI', '1234567', '+591-7-7000000', 'juan.perez@mail.test', NULL, '2025-10-12 23:00:54', 1),
(2, 'Empresa Salud SRL', 'RUC', '800100200', '+591-7-7000001', 'compras@salud.test', NULL, '2025-10-12 23:00:54', 1);

-- --------------------------------------------------------
-- Table structure for table `sucursales`
-- --------------------------------------------------------
CREATE TABLE `sucursales` (
  `su_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `su_nombre` varchar(120) NOT NULL,
  `su_direccion` text DEFAULT NULL,
  `su_telefono` varchar(30) DEFAULT NULL,
  `su_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `su_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `su_estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`su_id`),
  UNIQUE KEY `ux_sucursales_nombre` (`su_nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sucursales` VALUES
(1, 'Sucursal Central', 'Av. Principal 123, Ciudad', '+591-2-1234567', '2025-10-12 23:00:54', '2025-10-12 23:00:54', 1),
(2, 'Sucursal Norte', 'Calle 10 #45', '+591-2-7654321', '2025-10-12 23:00:54', '2025-10-12 23:00:54', 1);

-- --------------------------------------------------------
-- Table structure for table `roles`
-- --------------------------------------------------------
CREATE TABLE `roles` (
  `ro_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ro_nombre` varchar(50) NOT NULL,
  `ro_descripcion` text DEFAULT NULL,
  `ro_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `ro_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ro_estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`ro_id`),
  UNIQUE KEY `ro_nombre` (`ro_nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` VALUES
(1, 'admin', 'Administrador del sistema con todos los permisos', '2025-10-12 23:00:54', '2025-10-12 23:00:54', 1),
(2, 'gerente', 'Gerente de sucursal', '2025-10-12 23:00:54', '2025-10-12 23:00:54', 1),
(3, 'vendedor', 'Usuario de caja / ventas', '2025-10-12 23:00:54', '2025-10-12 23:00:54', 1);

-- --------------------------------------------------------
-- Table structure for table `usuarios`
-- --------------------------------------------------------
CREATE TABLE `usuarios` (
  `us_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `us_nombres` varchar(120) NOT NULL,
  `us_apellido_paterno` varchar(80) DEFAULT NULL,
  `us_apellido_materno` varchar(80) DEFAULT NULL,
  `us_numero_carnet` varchar(60) DEFAULT NULL,
  `us_telefono` varchar(30) DEFAULT NULL,
  `us_correo` varchar(120) DEFAULT NULL,
  `us_direccion` text DEFAULT NULL,
  `us_username` varchar(80) NOT NULL,
  `us_password_hash` varchar(255) NOT NULL,
  `us_token_recuperacion` varchar(255) DEFAULT NULL,
  `us_token_expiracion` datetime DEFAULT NULL,
  `us_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `us_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `us_estado` tinyint(1) NOT NULL DEFAULT 1,
  `su_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ro_id` bigint(20) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`us_id`),
  UNIQUE KEY `ux_usuarios_username` (`us_username`),
  UNIQUE KEY `ux_usuarios_correo` (`us_correo`),
  KEY `fk_usuarios_sucursales` (`su_id`),
  KEY `fk_usuarios_roles` (`ro_id`),
  CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`ro_id`) REFERENCES `roles` (`ro_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_usuarios_sucursales` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `laboratorios`
-- --------------------------------------------------------
CREATE TABLE `laboratorios` (
  `la_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `la_nombre_contacto` varchar(120) DEFAULT NULL,
  `la_telefono` varchar(30) DEFAULT NULL,
  `la_nombre_comercial` varchar(150) NOT NULL,
  `la_logo` varchar(255) DEFAULT NULL,
  `la_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `la_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `la_estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`la_id`),
  UNIQUE KEY `ux_laboratorios_nombre` (`la_nombre_comercial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `laboratorios` VALUES
(1, 'Contacto Lab A', '+591-4-1111111', 'Laboratorios A', NULL, '2025-10-12 23:00:54', '2025-10-12 23:00:54', 1),
(2, 'Contacto Lab B', '+591-4-2222222', 'Laboratorios B', NULL, '2025-10-12 23:00:54', '2025-10-12 23:00:54', 1);

-- --------------------------------------------------------
-- NUEVAS TABLAS FARMACOLÓGICAS
-- --------------------------------------------------------

CREATE TABLE `via_de_administracion` (
  `VD_COD` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `VD_NOMBRE` varchar(150) NOT NULL,
  `VD_IMAGEN` varchar(255) DEFAULT NULL,
  `VD_CREADO_EN` datetime NOT NULL DEFAULT current_timestamp(),
  `VD_ACTUALIZADO_EN` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `VD_ESTADO` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`VD_COD`),
  UNIQUE KEY `ux_via_nombre` (`VD_NOMBRE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `uso_farmacologico` (
  `UF_COD` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `UF_NOMBRE` varchar(150) NOT NULL,
  `UF_IMAGEN` varchar(255) DEFAULT NULL,
  `UF_CREADO_EN` datetime NOT NULL DEFAULT current_timestamp(),
  `UF_ACTUALIZADO_EN` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `UF_ESTADO` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`UF_COD`),
  UNIQUE KEY `ux_uso_nombre` (`UF_NOMBRE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `forma_farmaceutica` (
  `FF_COD` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `FF_NOMBRE` varchar(150) NOT NULL,
  `FF_IMAGEN` varchar(255) DEFAULT NULL,
  `FF_CREADO_EN` datetime NOT NULL DEFAULT current_timestamp(),
  `FF_ACTUALIZADO_EN` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `FF_ESTADO` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`FF_COD`),
  UNIQUE KEY `ux_forma_nombre` (`FF_NOMBRE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `medicamento` (
  `MED_COD` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `MED_NOMBRE_QUIMICO` varchar(200) NOT NULL,
  `MED_PRINCIPIO_ACTIVO` varchar(200) NOT NULL,
  `MED_ACCION_FARMACOLOGICA` varchar(255) DEFAULT NULL,
  `MED_PRESENTACION` varchar(150) DEFAULT NULL,
  `UF_COD` bigint(20) UNSIGNED DEFAULT NULL,
  `FF_COD` bigint(20) UNSIGNED DEFAULT NULL,
  `VD_COD` bigint(20) UNSIGNED DEFAULT NULL,
  `LA_ID` bigint(20) UNSIGNED DEFAULT NULL,
  `MED_IMAUNO` varchar(255) DEFAULT NULL,
  `MED_IMADOS` varchar(255) DEFAULT NULL,
  `MED_IMAQR` varchar(255) DEFAULT NULL,
  `MED_DESCRIPCION` text DEFAULT NULL,
  `MED_PRECIO_UNITARIO` decimal(12,2) NOT NULL DEFAULT 0.00,
  `MED_PRECIO_CAJA` decimal(12,2) DEFAULT NULL,
  `MED_CREADO_EN` datetime NOT NULL DEFAULT current_timestamp(),
  `MED_ACTUALIZADO_EN` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `MED_ESTADO` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`MED_COD`),
  KEY `fk_medicamento_laboratorio` (`LA_ID`),
  KEY `fk_medicamento_uso` (`UF_COD`),
  KEY `fk_medicamento_forma` (`FF_COD`),
  KEY `fk_medicamento_via` (`VD_COD`),
  CONSTRAINT `fk_medicamento_laboratorio` FOREIGN KEY (`LA_ID`) REFERENCES `laboratorios` (`la_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_medicamento_uso` FOREIGN KEY (`UF_COD`) REFERENCES `uso_farmacologico` (`UF_COD`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_medicamento_forma` FOREIGN KEY (`FF_COD`) REFERENCES `forma_farmaceutica` (`FF_COD`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_medicamento_via` FOREIGN KEY (`VD_COD`) REFERENCES `via_de_administracion` (`VD_COD`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `facturas`
-- --------------------------------------------------------
CREATE TABLE `facturas` (
  `fa_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fa_numero_factura` varchar(80) NOT NULL,
  `fa_fecha_emision` datetime NOT NULL DEFAULT current_timestamp(),
  `cl_id` bigint(20) UNSIGNED DEFAULT NULL,
  `us_id` bigint(20) UNSIGNED NOT NULL,
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `fa_subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `fa_impuesto` decimal(14,2) NOT NULL DEFAULT 0.00,
  `fa_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `fa_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `fa_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fa_estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`fa_id`),
  CONSTRAINT `fk_facturas_clientes` FOREIGN KEY (`cl_id`) REFERENCES `clientes` (`cl_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_facturas_usuarios` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_facturas_sucursales` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `detalle_venta`
-- --------------------------------------------------------
CREATE TABLE `detalle_venta` (
  `dv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fa_id` bigint(20) UNSIGNED NOT NULL,
  `med_cod` bigint(20) UNSIGNED NOT NULL,
  `dv_cantidad` int(11) NOT NULL,
  `dv_precio_unitario` decimal(12,2) NOT NULL,
  `dv_descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `dv_subtotal` decimal(14,2) NOT NULL,
  `dv_estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`dv_id`),
  KEY `ix_dv_fa` (`fa_id`),
  KEY `ix_dv_med` (`med_cod`),
  CONSTRAINT `fk_dv_facturas` FOREIGN KEY (`fa_id`) REFERENCES `facturas` (`fa_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dv_medicamento` FOREIGN KEY (`med_cod`) REFERENCES `medicamento` (`MED_COD`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;