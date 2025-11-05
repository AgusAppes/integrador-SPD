<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Sistema Ticket</title>
    <link rel="icon" href="<?php echo BASE_URL; ?>img/malpa-logo.png" type="image/png">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>img/malpa-logo.png" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>img/malpa-logo.png">
    <!-- Estilos base -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
    <!-- Estilos de admin para formularios -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/admin.css">
    <!-- Estilos de toast para notificaciones -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/toast.css">
    <!-- Estilos de usuarios -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/users.css">
</head>
<body>
    <!-- Container para notificaciones toast -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- Barra de navegación -->
    <?php include 'navbar.php'; ?>
    
    <!-- Contenido principal -->
    <div class="main-content">
        <div class="login-container">
            <h1>Recuperar Contraseña</h1>
            <p class="login-descripcion">
                Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña
            </p>
            
            <!-- Formulario de recuperación -->
            <form action="<?php echo BASE_URL; ?>methods/users.php" method="post" class="login-form">
                <input type="hidden" name="action" value="forgot_password">
                
                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" placeholder="ejemplo@gmail.com" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-full">Enviar enlace de recuperación</button>
                    <button type="button" class="btn btn-primary btn-full" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?page=login'">Volver al login</button>
                </div>
            </form>
            
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    
    <script>
        // Mostrar mensaje de éxito si existe
        <?php if (isset($_GET['success'])): ?>
            showToast('<?php echo htmlspecialchars($_GET['success']); ?>', 'success');
        <?php endif; ?>
        
        // Mostrar mensaje de error si existe
        <?php if (isset($_GET['error'])): ?>
            showToast('<?php echo htmlspecialchars($_GET['error']); ?>', 'error');
        <?php endif; ?>
    </script>
    
</body>
</html>

