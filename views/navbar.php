<?php
// Verificar si el usuario está logueado
$usuario_logueado = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$es_admin = $usuario_logueado && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 1;
$nombre_usuario = $usuario_logueado ? $_SESSION['usuario_nombre'] : '';
?>

<!-- Barra de navegación -->
<header>
    <a href="../index.php?page=home">
        <img src="../img/malpa-logo.png" alt="malpa eventos">
    </a>
    <nav>
        <ul>
            <li><a href="../index.php?page=home">Inicio</a></li>
            <li><a href="../index.php?page=catalogo">Eventos</a></li>
            
            <?php if (!$usuario_logueado): ?>
                <!-- Opciones para usuarios no logueados -->
                <li><a href="../index.php?page=home#container-ubicacion">Ubicación</a></li>
                <li><a href="../index.php?page=home#container-contacto">Contacto</a></li>
            <?php else: ?>
                <!-- Opciones para usuarios logueados -->
                <?php if ($es_admin): ?>
                    <li><a href="../index.php?page=admin">Panel Admin</a></li>
                    <li><a href="../index.php?page=admin-eventos">Gestionar Eventos</a></li>
                <?php endif; ?>
                <li><a href="../index.php?page=home#container-ubicacion">Ubicación</a></li>
                <li><a href="../index.php?page=home#container-contacto">Contacto</a></li>
                <li><a href="../methods/users.php?action=logout" class="logout-link">Cerrar Sesión</a></li>
            <?php endif; ?>
            
        </ul>
    </nav>
</header>

