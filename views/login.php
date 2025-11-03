<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema Ticket</title>
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
            <h1>Iniciar Sesión</h1>
            <p class="login-descripcion">
                Accede a tu cuenta para comprar tus entradas
            </p>
            
            <!-- Formulario de login -->
            <form action="<?php echo BASE_URL; ?>methods/users.php" method="post" class="login-form">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="correo" placeholder="ejemplo@gmail.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="contraseña" placeholder="Tu contraseña" required>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="recordar">
                        <span class="checkmark"></span>
                        Recordar sesión
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-full">Iniciar Sesión</button>
                </div>
                
            </form>
            
            <div class="login-links">
                <div class="forgot-password">
                    <a href="#" class="link-secundario">¿Olvidaste tu contraseña?</a>
                </div>
                <div class="register-link">
                    <p>¿No tienes cuenta? <a href="?page=register">Crear cuenta</a></p>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    
    <script>
        // Mostrar mensaje de éxito de registro si existe
        <?php if (isset($_GET['registro_exitoso']) && $_GET['registro_exitoso'] == '1'): ?>
            showToast('<?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Registro exitoso. Ya puedes iniciar sesión.'; ?>', 'success');
        <?php endif; ?>
        
        // Mostrar mensaje de error si existe
        <?php if (isset($_GET['error'])): ?>
            showToast('<?php echo htmlspecialchars($_GET['error']); ?>', 'error');
        <?php endif; ?>
    </script>
    
</body>
</html>