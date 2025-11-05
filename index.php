<?php
/*
================================================================================
SISTEMA DE ENRUTAMIENTO SIMPLE - INTEGRADOR SPD
================================================================================

Este archivo controla la navegación/redirección del sistema.
Todo el tráfico pasa por aquí mediante el parámetro GET "page".

FUNCIONAMIENTO:
- Las URLs siguen el patrón: index.php?page=NOMBRE_VISTA
- Ejemplo: ?page=catalogo carga views/catalogo.php
- Sin parámetro page: carga views/home.php por defecto
- Páginas no permitidas: redirige al home automáticamente
- Cada vez que se agrega una nueva vista, se debe agregar el nombre de la vista en el array $paginas_permitidas

SEGURIDAD:
- lista de páginas permitidas ($paginas_permitidas)
- Solo se pueden cargar vistas que estén explícitamente autorizadas
- Previene inclusión de archivos no deseados

ESTRUCTURA:
1. Configuración inicial (config.php)
2. Definición de páginas permitidas
3. Validación del parámetro page
4. Redirigir a la vista correspondiente

================================================================================
*/

include 'config/config.php';

// Definir paginas permitidas
$paginas_permitidas = ['home', 'login', 'register', 'catalogo', 'admin', 'admin-eventos', 'reception', 'admin-ventas', 'admin-usuarios', 'perfil', 'pago', 'mis-entradas', 'forgot-password', 'reset-password'];

// Si no hay parámetro page, redirigir al home
if (!isset($_GET['page'])) {
    include 'views/home.php';
    exit;
}

// Si el parámetro page no está en las paginas permitidas, redirigir al home
if (!in_array($_GET['page'], $paginas_permitidas)) {
    //include sirve para cargar el archivo de la vista
    include 'views/home.php';
    exit;
}

// Si hay parámetro page, y está en las paginas permitidas, cargar la vista correspondiente
$page = $_GET['page'];

if (in_array($page, $paginas_permitidas)) {
    $file = "views/{$page}.php";

    if (file_exists($file)) {
        include $file;
} else {
        include 'views/home.php';
    }
}


