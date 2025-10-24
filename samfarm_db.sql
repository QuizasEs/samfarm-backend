-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2025 at 10:42 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

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
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `cl_id` bigint(20) UNSIGNED NOT NULL,
  `cl_nombre` varchar(200) NOT NULL,
  `cl_tipo_documento` varchar(30) DEFAULT NULL,
  `cl_documento` varchar(60) DEFAULT NULL,
  `cl_telefono` varchar(30) DEFAULT NULL,
  `cl_correo` varchar(120) DEFAULT NULL,
  `cl_direccion` text DEFAULT NULL,
  `cl_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `cl_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`cl_id`, `cl_nombre`, `cl_tipo_documento`, `cl_documento`, `cl_telefono`, `cl_correo`, `cl_direccion`, `cl_creado_en`, `cl_estado`) VALUES
(1, 'Juan Pérez', 'CI', '1234567', '+591-7-7000000', 'juan.perez@mail.test', NULL, '2025-10-12 23:00:54', 1),
(2, 'Empresa Salud SRL', 'RUC', '800100200', '+591-7-7000001', 'compras@salud.test', NULL, '2025-10-12 23:00:54', 1);

-- --------------------------------------------------------

--
-- Table structure for table `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `dv_id` bigint(20) UNSIGNED NOT NULL,
  `fa_id` bigint(20) UNSIGNED NOT NULL,
  `med_id` bigint(20) UNSIGNED NOT NULL,
  `dv_cantidad` int(11) NOT NULL,
  `dv_precio_unitario` decimal(12,2) NOT NULL,
  `dv_descuento` decimal(12,2) NOT NULL DEFAULT 0.00,
  `dv_subtotal` decimal(14,2) NOT NULL,
  `dv_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facturas`
--

CREATE TABLE `facturas` (
  `fa_id` bigint(20) UNSIGNED NOT NULL,
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
  `fa_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forma_farmaceutica`
--

CREATE TABLE `forma_farmaceutica` (
  `ff_id` bigint(20) UNSIGNED NOT NULL,
  `ff_nombre` varchar(150) NOT NULL,
  `ff_imagen` varchar(255) DEFAULT NULL,
  `ff_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `ff_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ff_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forma_farmaceutica`
--

INSERT INTO `forma_farmaceutica` (`ff_id`, `ff_nombre`, `ff_imagen`, `ff_creado_en`, `ff_actualizado_en`, `ff_estado`) VALUES
(1, 'tableta', NULL, '2025-10-23 05:50:28', '2025-10-23 05:50:28', 1),
(2, 'cápsula', NULL, '2025-10-23 05:50:28', '2025-10-23 05:50:28', 1);

-- --------------------------------------------------------

--
-- Table structure for table `laboratorios`
--

CREATE TABLE `laboratorios` (
  `la_id` bigint(20) UNSIGNED NOT NULL,
  `la_nombre_contacto` varchar(120) DEFAULT NULL,
  `la_telefono` varchar(30) DEFAULT NULL,
  `la_nombre_comercial` varchar(150) NOT NULL,
  `la_logo` varchar(255) DEFAULT NULL,
  `la_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `la_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `la_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laboratorios`
--

INSERT INTO `laboratorios` (`la_id`, `la_nombre_contacto`, `la_telefono`, `la_nombre_comercial`, `la_logo`, `la_creado_en`, `la_actualizado_en`, `la_estado`) VALUES
(1, 'Contacto Lab A', '+591-4-1111111', 'Laboratorios A', NULL, '2025-10-23 05:50:28', '2025-10-23 05:50:28', 1),
(2, 'Contacto Lab B', '+591-4-2222222', 'Laboratorios B', NULL, '2025-10-23 05:50:28', '2025-10-23 05:50:28', 1);

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
  `uf_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vd_id` bigint(20) UNSIGNED DEFAULT NULL,
  `la_id` bigint(20) UNSIGNED DEFAULT NULL,
  `su_id` bigint(20) UNSIGNED DEFAULT NULL,
  `med_descripcion` text DEFAULT NULL,
  `med_precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `med_precio_caja` decimal(12,2) DEFAULT NULL,
  `med_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `med_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `us_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicamento`
--

INSERT INTO `medicamento` (`med_id`, `med_nombre_quimico`, `med_principio_activo`, `med_accion_farmacologica`, `med_presentacion`, `uf_id`, `ff_id`, `vd_id`, `la_id`, `su_id`, `med_descripcion`, `med_precio_unitario`, `med_precio_caja`, `med_creado_en`, `med_actualizado_en`, `us_id`) VALUES
(1, 'paracetamol', 'acetaminofén', 'analgésico y antipirético', 'tabletas 500mg x20', 1, 1, 1, 1, NULL, 'Alivia el dolor y la fiebre.', '5.50', '50.00', '2025-10-23 05:50:28', '2025-10-23 05:50:28', 0),
(2, 'multivitamínico', 'complejo B + zinc', 'suplemento vitamínico', 'cápsulas x30', 2, 2, 1, 2, NULL, 'Aumenta la energía y fortalece defensas.', '12.00', '120.00', '2025-10-23 05:50:28', '2025-10-23 05:50:28', 0),
(3, 'tapsin', 'tapsinico', 'analgesico', '10gm', 2, 1, 2, 1, 1, 'asd', '5.00', '60.00', '2025-10-23 14:33:54', '2025-10-24 04:08:51', 0),
(4, 'resfrianex', 'antigripal resfrianex', 'analgesico', 'sobre 10mg', 1, 2, 1, 1, 2, 'para la fiebre', '5.00', '120.00', '2025-10-24 03:22:13', '2025-10-24 03:22:13', 0),
(5, 'multivitamínico', 'complejo B + zinc', 'suplemento vitamínico', 'cápsulas x30', 2, 2, 2, 2, 2, 'Aumenta la energía y fortalece defensas.', '12.00', '120.00', '2025-10-24 03:46:15', '2025-10-24 03:46:15', 0),
(6, 'multivitamínico', 'complejo B + zinc', 'suplemento vitamínico', 'cápsulas x30', 2, 2, 2, 2, 2, 'Aumenta la energía y fortalece defensas.', '12.00', '120.00', '2025-10-24 03:46:40', '2025-10-24 03:46:40', 0);

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
(1, 'admin', 'Administrador del sistema con todos los permisos', '2025-10-12 23:00:54', '2025-10-12 23:00:54', 1),
(2, 'gerente', 'Gerente de sucursal', '2025-10-12 23:00:54', '2025-10-12 23:00:54', 1),
(3, 'vendedor', 'Usuario de caja / ventas', '2025-10-12 23:00:54', '2025-10-12 23:00:54', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sucursales`
--

CREATE TABLE `sucursales` (
  `su_id` bigint(20) UNSIGNED NOT NULL,
  `su_nombre` varchar(120) NOT NULL,
  `su_direccion` text DEFAULT NULL,
  `su_telefono` varchar(30) DEFAULT NULL,
  `su_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `su_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `su_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sucursales`
--

INSERT INTO `sucursales` (`su_id`, `su_nombre`, `su_direccion`, `su_telefono`, `su_creado_en`, `su_actualizado_en`, `su_estado`) VALUES
(1, 'Sucursal Central', 'Av. Principal 123, Ciudad', '+591-2-1234567', '2025-10-12 23:00:54', '2025-10-12 23:00:54', 1),
(2, 'Sucursal Norte', 'Calle 10 #45', '+591-2-7654321', '2025-10-12 23:00:54', '2025-10-12 23:00:54', 1);

-- --------------------------------------------------------

--
-- Table structure for table `uso_farmacologico`
--

CREATE TABLE `uso_farmacologico` (
  `uf_id` bigint(20) UNSIGNED NOT NULL,
  `uf_nombre` varchar(150) NOT NULL,
  `uf_imagen` varchar(255) DEFAULT NULL,
  `uf_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `uf_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `uf_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uso_farmacologico`
--

INSERT INTO `uso_farmacologico` (`uf_id`, `uf_nombre`, `uf_imagen`, `uf_creado_en`, `uf_actualizado_en`, `uf_estado`) VALUES
(1, 'analgésico', NULL, '2025-10-23 05:50:28', '2025-10-23 05:50:28', 1),
(2, 'vitamina', NULL, '2025-10-23 05:50:28', '2025-10-23 05:50:28', 1);

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
  `ro_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`us_id`, `us_nombres`, `us_apellido_paterno`, `us_apellido_materno`, `us_numero_carnet`, `us_telefono`, `us_correo`, `us_direccion`, `us_username`, `us_password_hash`, `us_token_recuperacion`, `us_token_expiracion`, `us_creado_en`, `us_actualizado_en`, `us_estado`, `su_id`, `ro_id`) VALUES
(1, 'admin', 'admin', 'admin', '000000000', '000000000', 'admin@admin.com', 'admin calle admin', 'admin', 'dlo5ZmZvbmRjME41dGlDY01tTGcrUT09', NULL, NULL, '2025-10-14 03:10:57', '2025-10-22 01:03:05', 1, 2, 1),
(2, 'Ana', 'Martínez', 'Quispe', '234234234', '756734231231', 'ana.m@farmacia.test', 'Avenida Siempre Viva 5', 'ana', 'QzErdU1pVjZPSFpaQU1oNy9GWXhqUT09', NULL, NULL, '2025-10-12 23:00:54', '2025-10-24 03:17:48', 0, 2, 2),
(3, 'fghfg', 'gfhfgh', 'mnbnm', '123123', '1234123', 'mnbq@jklqw.q', 'qwe', 'qwe', 'VVRCRW5qNC8wUThFUjQ5ZEdINmJ4QT09', NULL, NULL, '2025-10-14 00:51:30', '2025-10-17 02:53:48', 0, 1, 3),
(4, 'QWE', 'QWE', 'QWE', '123123123', '123123', '123@EQW.QWE', 'qwe asd as sad', 'asdasd', 'aHFnUmZKV3hlaThKck5jMitaNzlOdz09', NULL, NULL, '2025-10-14 00:55:22', '2025-10-24 03:18:14', 1, 1, 2),
(10, 'ines', 'ines', 'ines', '324234234234', '235234523452', 'ines@ds.s', 'ines', 'ines', 'L2V6c0g3aC9PSGR0TUlsK2xJS2ZkQT09', NULL, NULL, '2025-10-18 15:46:49', '2025-10-22 01:00:12', 1, 2, 2),
(11, 'juan', 'juan', 'juan', '79790867345', '45734787', 'dasf@asd.s', 'fasdf', 'juan', 'dFhJNW9mdXZ0dVJON2NTOU83Y3A5UT09', NULL, NULL, '2025-10-18 16:14:00', '2025-10-22 01:02:10', 0, 1, 3),
(12, 'juan', 'juan', 'juan', '534345345', '2345345345', 'juan@jua.juan', 'juanjuanjuan', 'juanjuan', 'dFhJNW9mdXZ0dVJON2NTOU83Y3A5UT09', NULL, NULL, '2025-10-22 01:11:04', '2025-10-22 01:11:04', 1, 1, 2),
(13, 'godines', 'godines', 'godines', '645634534521235', '245714623463', 'godines@g.godines', 'godinesgodines', 'godines', 'cEtnaGJNcWpPUjNKWE5CNStrSkg0UT09', NULL, NULL, '2025-10-23 01:06:45', '2025-10-23 01:06:45', 1, 1, 1),
(14, 'gandi', 'gandi', 'gandi', '23563645', '77534523', 'gandi@gandi.v', 'gandigandi', 'gandi', 'aVE4OXlrdHA0N2VkcWdQQldDNmkxQT09', NULL, NULL, '2025-10-23 01:54:45', '2025-10-23 01:54:45', 1, 1, 1),
(15, 'MARIO', 'MARIO', 'MARIO', '134563564', '64468257', 'MARIO@MARIO.C', 'MARIO', 'MARIO', 'c3FZOUdQTmlRQUh1L3lURFpkVktiZz09', NULL, NULL, '2025-10-23 03:16:34', '2025-10-23 03:16:34', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `via_de_administracion`
--

CREATE TABLE `via_de_administracion` (
  `vd_id` bigint(20) UNSIGNED NOT NULL,
  `vd_nombre` varchar(150) NOT NULL,
  `vd_imagen` varchar(255) DEFAULT NULL,
  `vd_creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `vd_actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `vd_estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `via_de_administracion`
--

INSERT INTO `via_de_administracion` (`vd_id`, `vd_nombre`, `vd_imagen`, `vd_creado_en`, `vd_actualizado_en`, `vd_estado`) VALUES
(1, 'oral', NULL, '2025-10-23 05:50:28', '2025-10-23 05:50:28', 1),
(2, 'intravenosa', NULL, '2025-10-23 05:50:28', '2025-10-23 05:50:28', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`cl_id`),
  ADD KEY `ix_clientes_documento` (`cl_documento`);

--
-- Indexes for table `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`dv_id`),
  ADD KEY `ix_dv_fa` (`fa_id`),
  ADD KEY `ix_dv_med` (`med_id`);

--
-- Indexes for table `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`fa_id`),
  ADD KEY `fk_facturas_clientes` (`cl_id`),
  ADD KEY `fk_facturas_usuarios` (`us_id`),
  ADD KEY `fk_facturas_sucursales` (`su_id`);

--
-- Indexes for table `forma_farmaceutica`
--
ALTER TABLE `forma_farmaceutica`
  ADD PRIMARY KEY (`ff_id`),
  ADD UNIQUE KEY `ux_forma_nombre` (`ff_nombre`);

--
-- Indexes for table `laboratorios`
--
ALTER TABLE `laboratorios`
  ADD PRIMARY KEY (`la_id`),
  ADD UNIQUE KEY `ux_laboratorios_nombre` (`la_nombre_comercial`);

--
-- Indexes for table `medicamento`
--
ALTER TABLE `medicamento`
  ADD PRIMARY KEY (`med_id`),
  ADD KEY `fk_medicamento_laboratorio` (`la_id`),
  ADD KEY `fk_medicamento_uso` (`uf_id`),
  ADD KEY `fk_medicamento_forma` (`ff_id`),
  ADD KEY `fk_medicamento_via` (`vd_id`),
  ADD KEY `fk_medicamento_sucursal` (`su_id`);

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
-- Indexes for table `via_de_administracion`
--
ALTER TABLE `via_de_administracion`
  ADD PRIMARY KEY (`vd_id`),
  ADD UNIQUE KEY `ux_via_nombre` (`vd_nombre`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `cl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `dv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `facturas`
--
ALTER TABLE `facturas`
  MODIFY `fa_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `forma_farmaceutica`
--
ALTER TABLE `forma_farmaceutica`
  MODIFY `ff_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `laboratorios`
--
ALTER TABLE `laboratorios`
  MODIFY `la_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `medicamento`
--
ALTER TABLE `medicamento`
  MODIFY `med_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `ro_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sucursales`
--
ALTER TABLE `sucursales`
  MODIFY `su_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `uso_farmacologico`
--
ALTER TABLE `uso_farmacologico`
  MODIFY `uf_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `us_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `via_de_administracion`
--
ALTER TABLE `via_de_administracion`
  MODIFY `vd_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `fk_dv_facturas` FOREIGN KEY (`fa_id`) REFERENCES `facturas` (`fa_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dv_medicamento` FOREIGN KEY (`med_id`) REFERENCES `medicamento` (`med_id`) ON UPDATE CASCADE;

--
-- Constraints for table `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `fk_facturas_clientes` FOREIGN KEY (`cl_id`) REFERENCES `clientes` (`cl_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_facturas_sucursales` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_facturas_usuarios` FOREIGN KEY (`us_id`) REFERENCES `usuarios` (`us_id`) ON UPDATE CASCADE;

--
-- Constraints for table `medicamento`
--
ALTER TABLE `medicamento`
  ADD CONSTRAINT `fk_medicamento_forma` FOREIGN KEY (`ff_id`) REFERENCES `forma_farmaceutica` (`ff_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_laboratorio` FOREIGN KEY (`la_id`) REFERENCES `laboratorios` (`la_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_sucursal` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_uso` FOREIGN KEY (`uf_id`) REFERENCES `uso_farmacologico` (`uf_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicamento_via` FOREIGN KEY (`vd_id`) REFERENCES `via_de_administracion` (`vd_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`ro_id`) REFERENCES `roles` (`ro_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_sucursales` FOREIGN KEY (`su_id`) REFERENCES `sucursales` (`su_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
