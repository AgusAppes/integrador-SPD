-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 02-11-2025 a las 22:34:50
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dni_pass`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_asistencias`
--

CREATE TABLE `detalle_asistencias` (
  `id` int(11) NOT NULL,
  `hora_ingreso` datetime NOT NULL,
  `hora_egreso` datetime DEFAULT NULL,
  `id_entrada` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `detalle_asistencias`
--

INSERT INTO `detalle_asistencias` (`id`, `hora_ingreso`, `hora_egreso`, `id_entrada`) VALUES
(1, '2025-10-03 17:02:06', NULL, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `id` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_entrada` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`id`, `id_venta`, `id_entrada`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 4, 4),
(5, 5, 5),
(6, 6, 6),
(7, 7, 7),
(8, 8, 8),
(9, 9, 9),
(10, 10, 10),
(11, 11, 11);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entradas`
--

CREATE TABLE `entradas` (
  `id` int(11) NOT NULL,
  `nro_serie` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL,
  `id_estado` int(11) NOT NULL,
  `id_tipo_entrada` int(11) NOT NULL,
  `precio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `entradas`
--

INSERT INTO `entradas` (`id`, `nro_serie`, `id_usuario`, `id_evento`, `id_estado`, `id_tipo_entrada`, `precio`) VALUES
(1, 195406, 1, 1, 1, 1, 150),
(2, 195659, 1, 1, 1, 1, 150),
(3, 199075, 1, 1, 1, 1, 150),
(4, 153625, 1, 1, 1, 1, 150),
(5, 130272, 1, 1, 1, 1, 150),
(6, 161440, 1, 1, 1, 1, 150),
(7, 191707, 1, 1, 1, 1, 150),
(8, 185262, 1, 1, 1, 1, 150),
(9, 136069, 5, 1, 3, 1, 150),
(10, 231109, 2, 2, 1, 1, 100),
(11, 271901, 2, 2, 1, 1, 100),
(12, 254109, 5, 2, 1, 1, 100),
(13, 211895, 2, 2, 1, 1, 100);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados`
--

CREATE TABLE `estados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `estados`
--

INSERT INTO `estados` (`id`, `nombre`) VALUES
(1, 'vendida'),
(2, 'cancelada'),
(3, 'consumida');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  `cupo_total` int(11) NOT NULL,
  `cantidad_anticipadas` int(11) NOT NULL,
  `precio_anticipadas` int(11) NOT NULL,
  `precio_en_puerta` int(11) NOT NULL,
  `banner` varchar(100) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `nombre`, `fecha`, `descripcion`, `cupo_total`, `cantidad_anticipadas`, `precio_anticipadas`, `precio_en_puerta`, `banner`, `id_usuario`) VALUES
(1, 'Turing Fest', '2025-10-12', 'dfghjkl', 100, 50, 150, 200, 'images/eventos/68d9998306da7_1759091075.png', 1),
(2, 'neon party', '2025-10-02', 'hgfd', 150, 30, 100, 200, NULL, 1),
(3, 'Navidad fest', '2025-12-24', 'Viene papa Noel', 500, 100, 500, 1500, NULL, 1),
(4, 'putas show', '2025-10-30', 'solo putas', 300, 150, 3000, 6500, 'images/eventos/68e5ad4d83e5f_1759882573.png', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos_por_roles`
--

CREATE TABLE `permisos_por_roles` (
  `id_rol` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'admin'),
(2, 'cliente'),
(3, 'recepcionista');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_entrada`
--

CREATE TABLE `tipo_entrada` (
  `id` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tipo_entrada`
--

INSERT INTO `tipo_entrada` (`id`, `nombre`) VALUES
(1, 'anticipada'),
(2, 'en puerta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `dni` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `apellido` varchar(45) NOT NULL,
  `fecha_nac` datetime NOT NULL,
  `correo` varchar(45) NOT NULL,
  `contraseña` varchar(100) NOT NULL,
  `id_rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `dni`, `nombre`, `apellido`, `fecha_nac`, `correo`, `contraseña`, `id_rol`) VALUES
(1, 456378, 'test', 'tester', '1990-06-30 00:00:00', 'ejemplo@ej.com', '12345678', 1),
(2, 12345678, 'Carla', 'Dominguez', '2025-02-05 00:00:00', 'agustinaappes@gmail.com', '$2y$10$3gxlMTUCuFUaHOCyKmYg8.v0TdDzS5rE2UKwSGe7xFNy.9gfU5Vba', 3),
(3, 33445566, 'Karen', 'Veron', '2025-09-11 00:00:00', 'karen@recepcionista.com', '$2y$10$pblh6TYKZk3uFLzCqUn6KOHNH9f7aeG.jXMYGr23li7PElN8xeMmq', 3),
(5, 41273341, 'AGUSTINA FATIMA', 'APPES', '1997-08-06 00:00:00', 'sinconfigurar@mail.com', '$2y$10$KJwcB9II/0V9SNiP0SJUsuTSBXElO6tNK0LuJ.iVCE4vd7Wbd4W2O', 2),
(6, 4455879, 'MARIA LAURA', 'COLAPINTO', '2025-10-03 00:00:00', 'marialaura@colapinto.com', '$2y$10$AwF7jTI1DhHYl0yFp1Qjs.vJ8/okwQ/tcFuv31tDkDAB7oECmXSLG', 2),
(7, 4852794, 'Tiara', 'Benitez', '2025-10-08 02:09:50', 'tiarasb689@gmail.com', '$2y$10$3gxlMTUCuFUaHOCyKmYg8.v0TdDzS5rE2UKwSGe7xFNy.9gfU5Vba', 1),
(8, 87654321, 'admin', 'istrador', '1965-01-22 00:00:00', 'admin@gmail.com', '$2y$10$cDuHwpsOUrKM.OrT7Kjbp.$2y$10$3gxlMTUCuFUaHOCyKmYg8.v0TdDzS5rE2UKwSGe7xFNy.9gfU5Vba', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `fecha_venta` datetime NOT NULL,
  `cantidad_entradas` int(11) NOT NULL,
  `monto_total` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `fecha_venta`, `cantidad_entradas`, `monto_total`, `id_usuario`) VALUES
(1, '2025-09-28 17:24:48', 1, 150, 1),
(2, '2025-09-28 17:28:09', 1, 150, 1),
(3, '2025-09-28 17:41:09', 1, 150, 1),
(4, '2025-09-28 19:20:56', 1, 150, 1),
(5, '2025-09-28 19:27:24', 1, 150, 1),
(6, '2025-09-28 20:46:48', 1, 150, 1),
(7, '2025-09-29 06:45:52', 1, 150, 1),
(8, '2025-09-29 06:54:43', 1, 150, 1),
(9, '2025-09-29 07:10:16', 1, 150, 2),
(10, '2025-09-29 07:29:54', 1, 100, 2),
(11, '2025-09-29 07:32:14', 1, 200, 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `detalle_asistencias`
--
ALTER TABLE `detalle_asistencias`
  ADD PRIMARY KEY (`id`,`id_entrada`),
  ADD KEY `fk_detalle_asistencias_entradas1_idx` (`id_entrada`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`id`,`id_venta`,`id_entrada`),
  ADD KEY `fk_detalle_venta_ventas1_idx` (`id_venta`),
  ADD KEY `fk_detalle_venta_entradas1_idx` (`id_entrada`);

--
-- Indices de la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD PRIMARY KEY (`id`,`id_usuario`,`id_evento`,`id_estado`,`id_tipo_entrada`),
  ADD KEY `fk_entradas_usuarios1_idx` (`id_usuario`),
  ADD KEY `fk_entradas_eventos1_idx` (`id_evento`),
  ADD KEY `fk_entradas_estados1_idx` (`id_estado`),
  ADD KEY `fk_entradas_tipo_entrada1_idx` (`id_tipo_entrada`);

--
-- Indices de la tabla `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`,`id_usuario`),
  ADD KEY `fk_eventos_usuarios1_idx` (`id_usuario`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `permisos_por_roles`
--
ALTER TABLE `permisos_por_roles`
  ADD PRIMARY KEY (`id_rol`,`id_permiso`),
  ADD KEY `fk_permisos_por_roles_permisos1_idx` (`id_permiso`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_entrada`
--
ALTER TABLE `tipo_entrada`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`,`id_rol`),
  ADD KEY `fk_usuarios_roles1_idx` (`id_rol`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`,`id_usuario`),
  ADD KEY `fk_ventas_usuarios1_idx` (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `detalle_asistencias`
--
ALTER TABLE `detalle_asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `entradas`
--
ALTER TABLE `entradas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `estados`
--
ALTER TABLE `estados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos_por_roles`
--
ALTER TABLE `permisos_por_roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tipo_entrada`
--
ALTER TABLE `tipo_entrada`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_asistencias`
--
ALTER TABLE `detalle_asistencias`
  ADD CONSTRAINT `fk_detalle_asistencias_entradas1` FOREIGN KEY (`id_entrada`) REFERENCES `entradas` (`id`);

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `fk_detalle_venta_entradas1` FOREIGN KEY (`id_entrada`) REFERENCES `entradas` (`id`),
  ADD CONSTRAINT `fk_detalle_venta_ventas1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`);

--
-- Filtros para la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD CONSTRAINT `fk_entradas_estados1` FOREIGN KEY (`id_estado`) REFERENCES `estados` (`id`),
  ADD CONSTRAINT `fk_entradas_eventos1` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id`),
  ADD CONSTRAINT `fk_entradas_tipo_entrada1` FOREIGN KEY (`id_tipo_entrada`) REFERENCES `tipo_entrada` (`id`),
  ADD CONSTRAINT `fk_entradas_usuarios1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `fk_eventos_usuarios1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `permisos_por_roles`
--
ALTER TABLE `permisos_por_roles`
  ADD CONSTRAINT `fk_permisos_por_roles_permisos1` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id`),
  ADD CONSTRAINT `fk_permisos_por_roles_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_roles1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_ventas_usuarios1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
