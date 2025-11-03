<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema Ticket</title>
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
        <div class="registro-container">
            <h1>Crear Cuenta</h1>
            <p class="registro-descripcion">
                Complete los siguientes datos para registrarse en el sistema
            </p>
            
            <!-- Formulario de registro -->
            <form action="<?php echo BASE_URL; ?>methods/users.php" method="post" class="registro-form">
                
                
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre" class="form-label">Nombre <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" placeholder="Juan" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="apellido" class="form-label">Apellido <span class="required">*</span></label>
                        <input type="text" id="apellido" name="apellido" placeholder="Pérez" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dni" class="form-label">DNI <span class="required">*</span></label>
                        <input type="text" id="dni" name="dni" maxlength="8" placeholder="12345678" required>
                        <small class="form-text">Sin puntos ni espacios</small>
                    </div>
                
                    <div class="form-group">
                        <label for="fecha_nac" class="form-label">Fecha de Nacimiento <span class="required">*</span></label>
                        <input type="date" id="fecha_nac" name="fecha_nac">
                    </div>
                    
                </div>
                
                <div class="form-group">
                    <label for="correo" class="form-label">Correo Electrónico <span class="required">*</span></label>
                    <input type="email" id="correo" name="correo" placeholder="ejemplo@gmail.com" required>
                </div>
                
                <div class="form-group">
                    <label for="contraseña" class="form-label">Contraseña <span class="required">*</span></label>
                    <input type="password" id="contraseña" name="contraseña" placeholder="Mínimo 6 caracteres" required>
                    <small class="form-text">Debe tener al menos 6 caracteres</small>
                </div>
                
                <div class="form-actions">
                    <a href="?page=home" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Registrarse</button>
                </div>
                
            </form>
            
            <div class="login-link">
                <p>¿Ya tienes una cuenta? <a href="?page=login">Iniciar sesión</a></p>
            </div>
            
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    
    <script>
        // Mostrar mensaje de error si existe
        <?php if (isset($_GET['error'])): ?>
            showToast('<?php echo htmlspecialchars($_GET['error']); ?>', 'error');
        <?php endif; ?>
        
        // Validación de DNI solo números
        document.getElementById('dni').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
    
</body>
</html>