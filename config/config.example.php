<?php
// Constantes para la conexión a la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'tu_base_de_datos');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');

// Constante para el nombre del sitio
define('SITE_NAME', 'Sistema Ticket');

// Constante para la URL base del sitio
define('BASE_URL', 'http://localhost/sistema-ticket/');

// Constante para la zona horaria
define('TIMEZONE', 'America/Argentina/Buenos_Aires');

// Configurar zona horaria
date_default_timezone_set(TIMEZONE);

// Iniciar sesión si no está iniciada
// session_status() es una función que devuelve el estado de la sesión
// Este bloque verifica si hay una sesión activa
// Si no hay una sesión activa, se inicia una nueva sesión con session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>