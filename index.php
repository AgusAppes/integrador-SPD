<?php
include 'config/config.php';

// Si no hay parámetro page, mostrar home
$page = $_GET['page'] ?? null;

if ($page === null) {
    // Página principal - mostrar eventos públicos
    include 'views/home.php';
} else {
    // Redirigir al archivo pasado por el parametro $page de la carpeta views
    $file = "views/{$page}.php";
    if (file_exists($file)) {
        include $file;
    } else {
        // Si no existe, mostrar home
        include 'views/home.php';
    }
}
?>