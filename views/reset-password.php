<?php
// Verificar que se proporcionen token e id
if (!isset($_GET['token']) || !isset($_GET['id']) || empty($_GET['token']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'index.php?page=forgot-password&error=' . urlencode('Enlace inválido. Por favor solicita un nuevo enlace de recuperación.'));
    exit;
}

$token = $_GET['token'];
$usuario_id = $_GET['id'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Sistema Ticket</title>
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
            <h1>Restablecer Contraseña</h1>
            <p class="login-descripcion">
                Ingresa tu nueva contraseña
            </p>
            
            <!-- Formulario de restablecimiento -->
            <form action="<?php echo BASE_URL; ?>methods/users.php" method="post" class="login-form" id="resetForm">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="hidden" name="usuario_id" value="<?php echo htmlspecialchars($usuario_id); ?>">
                <input type="hidden" name="confirmar_contraseña" id="confirmar_contraseña_hidden">
                
                <div class="form-group">
                    <label for="nueva_contraseña">Nueva Contraseña</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="nueva_contraseña" name="nueva_contraseña" placeholder="Mínimo 6 caracteres" required minlength="6">
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('nueva_contraseña', this)" aria-label="Mostrar contraseña">
                            <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_contraseña">Confirmar Contraseña</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="confirmar_contraseña" name="confirmar_contraseña" placeholder="Repite tu contraseña" required minlength="6">
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirmar_contraseña', this)" aria-label="Mostrar contraseña">
                            <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-full">Restablecer Contraseña</button>
                    <button type="button" class="btn btn-primary btn-full" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?page=login'">Cancelar</button>
                </div>
            </form>
            
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    
    <script>
        // Función para mostrar/ocultar contraseña
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const eyeIcon = button.querySelector('.eye-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
                button.setAttribute('aria-label', 'Ocultar contraseña');
            } else {
                input.type = 'password';
                eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                button.setAttribute('aria-label', 'Mostrar contraseña');
            }
        }
        
        // Validar que las contraseñas coincidan
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const nueva = document.getElementById('nueva_contraseña').value;
            const confirmar = document.getElementById('confirmar_contraseña').value;
            
            // Copiar el valor al campo hidden para enviarlo
            document.getElementById('confirmar_contraseña_hidden').value = confirmar;
            
            if (nueva !== confirmar) {
                e.preventDefault();
                showToast('Las contraseñas no coinciden', 'error');
                return false;
            }
            
            if (nueva.length < 6) {
                e.preventDefault();
                showToast('La contraseña debe tener al menos 6 caracteres', 'error');
                return false;
            }
        });
        
        // Mostrar mensaje de error si existe
        <?php if (isset($_GET['error'])): ?>
            showToast('<?php echo htmlspecialchars($_GET['error']); ?>', 'error');
        <?php endif; ?>
    </script>
    
</body>
</html>

