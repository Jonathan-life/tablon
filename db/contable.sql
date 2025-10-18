-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 18-10-2025 a las 00:05:06
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `contable`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

DROP TABLE IF EXISTS `administradores`;
CREATE TABLE IF NOT EXISTS `administradores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `clave` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id`, `usuario`, `clave`) VALUES
(1, 'admin01', '123456');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deudas`
--

DROP TABLE IF EXISTS `deudas`;
CREATE TABLE IF NOT EXISTS `deudas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `periodo_tributario` varchar(20) DEFAULT NULL,
  `formulario` varchar(50) DEFAULT NULL,
  `numero_orden` varchar(20) DEFAULT NULL,
  `tributo_multa` varchar(100) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `ruc` varchar(11) NOT NULL,
  `fecha_emision` date DEFAULT NULL,
  `fecha_notificacion` date DEFAULT NULL,
  `fecha_pagos` date DEFAULT NULL,
  `fecha_calculos` date DEFAULT NULL,
  `etapa_basica` varchar(50) DEFAULT NULL,
  `importe_deudas` decimal(10,2) DEFAULT NULL,
  `importe_tributaria` decimal(10,2) DEFAULT NULL,
  `interes_capitalizado` decimal(10,2) DEFAULT NULL,
  `interes_moratorio` decimal(10,2) DEFAULT NULL,
  `pagos` decimal(10,2) DEFAULT NULL,
  `saldo_total` decimal(10,2) DEFAULT NULL,
  `interes_diario` decimal(5,2) DEFAULT '0.00',
  `interes_acumulado` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `ruc` (`ruc`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `deudas`
--

INSERT INTO `deudas` (`id`, `periodo_tributario`, `formulario`, `numero_orden`, `tributo_multa`, `tipo`, `ruc`, `fecha_emision`, `fecha_notificacion`, `fecha_pagos`, `fecha_calculos`, `etapa_basica`, `importe_deudas`, `importe_tributaria`, `interes_capitalizado`, `interes_moratorio`, `pagos`, `saldo_total`, `interes_diario`, `interes_acumulado`) VALUES
(4, '201701', '0601', '122510810', '5310', 'Tributaria', '10456789123', '2027-07-27', '2027-07-31', '0000-00-00', '0000-00-00', 'En cobranza coactiva', NULL, 373.00, 0.00, 344.00, 0.00, 717.00, 0.39, 344.00),
(5, '201701', '0601', '122510810', '5310', 'Tributaria', '', '2027-07-27', '2027-07-31', '0000-00-00', '2025-10-18', 'En cobranza coactiva', NULL, 373.00, 0.00, 378.00, 500.00, 251.00, 0.39, 378.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ruc` varchar(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `clave` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `ruc` (`ruc`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `ruc`, `usuario`, `clave`) VALUES
(1, '10456789123', 'usuario01', '123456');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
