-- Tabla para almacenar tokens de recuperación de contraseña
CREATE TABLE IF NOT EXISTS `password_resets` (
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expira` int(11) NOT NULL,
  PRIMARY KEY (`usuario_id`),
  KEY `idx_expira` (`expira`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

