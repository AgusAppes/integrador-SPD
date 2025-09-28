<?php
include 'config/config.php';

// Definir paginas permitidas
$paginas_permitidas = ['home', 'login', 'register', 'catalogo', 'admin', 'admin-eventos'];

// Si no hay parámetro page, redirigir al home
if (!isset($_GET['page'])) {
    include 'views/home.php';
    exit;
}

// Si el parámetro page no está en las paginas permitidas, redirigir al home
if (!in_array($_GET['page'], $paginas_permitidas)) {
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
