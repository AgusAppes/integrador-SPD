<?php
// Verificar si el usuario está logueado
$usuario_logueado = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$es_admin = $usuario_logueado && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 1;
$es_reception = $usuario_logueado && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 3;
$nombre_usuario = $usuario_logueado ? ($_SESSION['usuario_nombre'] ?? '') : '';
$apellido_usuario = $usuario_logueado && isset($_SESSION['usuario_apellido']) && !empty($_SESSION['usuario_apellido']) ? $_SESSION['usuario_apellido'] : '';
$nombre_completo = '';
if ($usuario_logueado) {
    if (!empty($nombre_usuario) && !empty($apellido_usuario)) {
        $nombre_completo = trim($nombre_usuario . ' ' . $apellido_usuario);
    } elseif (!empty($nombre_usuario)) {
        $nombre_completo = trim($nombre_usuario);
    }
}
?>

<!-- Barra de navegación -->
<header>
    <a href="<?php echo BASE_URL; ?>index.php?page=home">
        <img src="<?php echo BASE_URL; ?>img/malpa-logo.png" alt="malpa eventos">
    </a>
    <nav>
        <ul>
            <li><a href="<?php echo BASE_URL; ?>index.php?page=home">Inicio</a></li>
            
            
            <?php if (!$usuario_logueado): ?>
                <!-- Opciones para usuarios no logueados -->
                <li><a href="<?php echo BASE_URL; ?>index.php?page=home#container-ubicacion">Ubicación</a></li>
                <li><a href="<?php echo BASE_URL; ?>index.php?page=home#container-contacto">Contacto</a></li>
            <?php else: ?>
                <!-- Opciones para usuarios logueados -->
                <?php if ($es_admin): ?>
                    <li><a href="<?php echo BASE_URL; ?>index.php?page=admin">Panel Admin</a></li>
                <?php endif; ?>
                
                <?php if ($es_reception || $es_admin): ?>
                    <!-- Opciones para recepcionista -->
                    <li><a href="<?php echo BASE_URL; ?>index.php?page=reception">Panel Recepción</a></li>
                <?php endif; ?>
                <li><a href="<?php echo BASE_URL; ?>index.php?page=home#container-ubicacion">Ubicación</a></li>
                <li><a href="<?php echo BASE_URL; ?>index.php?page=home#container-contacto">Contacto</a></li>
                <li><a href="<?php echo BASE_URL; ?>index.php?page=catalogo">Eventos</a></li>
                <li class="user-dropdown">
                    <a href="#" class="user-profile-link" id="username-nav-link">
                        <?php echo htmlspecialchars($nombre_completo ?: $nombre_usuario); ?>
                        <span class="dropdown-arrow">▼</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo BASE_URL; ?>index.php?page=perfil">Mi Perfil</a></li>
                        <li><a href="<?php echo BASE_URL; ?>index.php?page=mis-entradas">Mis Entradas</a></li>
                        <li><a href="<?php echo BASE_URL; ?>methods/users.php?action=logout" class="logout-link">Cerrar Sesión</a></li>
                    </ul>
                </li>
            <?php endif; ?>
            
        </ul>
    </nav>
</header>

