<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Crear Evento</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>img/favicon.png">
    <!-- Estilos base (incluye navbar) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
    <!-- Estilos del panel de administración -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/admin.css">
    <!-- Estilos de las notificaciones toast -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/toast.css">
    <!-- Estilos de modales -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/modal.css">
</head>
<body class="admin-page">
    <!-- Container para notificaciones toast -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- Barra de navegación -->
    <?php include 'navbar.php'; ?>
    
    <!-- Contenido principal -->
    <div class="main-content">
        <div class="admin-container">
            <!-- Barra lateral -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Contenido central -->
            <div class="admin-main-content">
                <div class="construction-message">
                    <div class="construction-icon">🚧</div>
                    <h1>En Construcción</h1>
                    <p>Esta sección está siendo desarrollada. Utiliza las opciones del menú lateral para gestionar el sistema.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Animaciones y funciones de las notificaciones toast -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    <!-- Animaciones y funciones de los modales -->
    <script src="<?php echo BASE_URL; ?>js/modal.js"></script>
    
    <script>
        // Mostrar mensaje de éxito de login si existe y si es admin
        <?php if (isset($_GET['login_exitoso']) && $_GET['login_exitoso'] == '1' && $_SESSION['rol'] == 1): ?>
            showToast('¡Bienvenido Admin! Has iniciado sesión correctamente.', 'success');
        <?php endif; ?>
        <?php if (isset($_GET['login_exitoso']) && $_GET['login_exitoso'] == '1' && $_SESSION['rol'] == 2): ?>
            showToast('¡Bienvenido! Has iniciado sesión correctamente.', 'success');
        <?php endif; ?>
    </script>
    
</body>
</html>