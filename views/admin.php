<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Crear Evento</title>
    <!-- Estilos base (incluye navbar) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
    <!-- Estilos del panel de administración -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/admin.css">
    <!-- Estilos de las notificaciones toast -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/toast.css">
    <!-- Estilos de modales -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/modal.css">
</head>
<body>
    <!-- Container para notificaciones toast -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- Barra de navegación -->
    <?php include 'navbar.php'; ?>
    
    <!-- Contenido principal -->
    <div class="main-content">
        <!-- Panel de Administración -->
        <div class="admin-panel">
        <h1>Panel de Administración</h1>
        
        
        <!-- Sección de Eventos -->
        <div class="admin-section">
            <h2>Gestión de Eventos</h2>
            <div class="admin-actions">
                <button class="btn btn-primary" onclick="openModal('createEventModal')">
                    <i class="icon">+</i> Crear Evento
                </button>
                <button class="btn btn-secondary" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?page=admin-eventos'">
                    Gestionar Eventos
                </button>
            </div>
        </div>
        
        <!-- Seleción de ventas -->
        <div class="admin-section">
            <h2>Gestión de Ventas</h2>
            <div class="admin-actions">
                <button class="btn btn-secondary" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?page=admin-ventas'">
                    Gestionar Ventas 
                </button>
            </div>
        </div>
        <!-- Selección de usuarios -->
        <div class="admin-section">
            <h2>Gestión de Usuarios</h2>
            <div class="admin-actions">
                <button class="btn btn-primary" onclick="openModal('createUserModal')">
                    <i class="icon">+</i> Crear Usuarios
                </button>
                <button class="btn btn-secondary" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?page=admin-usuarios'">
                     Gestionar Usuarios
                </button>
            </div>
        </div>
    </div>
<!--  ///////////////////////////////////////////// Modal //////////////////////////////////////////////  -->
    <!-- Modal para Crear Evento -->
    <div id="createEventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nuevo Evento</h2>
                <button class="close" onclick="closeModal('createEventModal')">&times;</button>
            </div>
            
            <form action="<?php echo BASE_URL; ?>methods/events.php" method="post" enctype="multipart/form-data" id="eventoForm" class="modal-body modal-eventos">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre del Evento <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha del Evento <span class="required">*</span></label>
                        <input type="date" id="fecha" name="fecha" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción <span class="required">*</span></label>
                    <textarea id="descripcion" name="descripcion" placeholder="Descripción del evento..." required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="cupo_total">Cupo Total <span class="required">*</span></label>
                        <input type="number" id="cupo_total" name="cupo_total" min="1" placeholder="Ej: 100" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cantidad_anticipadas">Cantidad de Anticipadas <span class="required">*</span></label>
                        <input type="number" id="cantidad_anticipadas" name="cantidad_anticipadas" min="1" placeholder="Ej: 50" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="precio_anticipadas">Precio Anticipadas ($) <span class="required">*</span></label>
                        <input type="number" id="precio_anticipadas" name="precio_anticipadas" min="0" step="0.01" placeholder="Ej: 1500.00" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="precio_en_puerta">Precio en Puerta ($) <span class="required">*</span></label>
                        <input type="number" id="precio_en_puerta" name="precio_en_puerta" min="0" step="0.01" placeholder="Ej: 2000.00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="banner">Banner</label>
                    <input type="file" id="banner" name="banner" accept="image/*">
                    <small class="form-text">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="closeModal('createEventModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Evento</button>
                </div>
            </form>
        </div>
        </div>
    </div>
    <!--  ///////////////////////////////////////////// Modal //////////////////////////////////////////////  -->
    <!-- Modal para Crear Usuarios -->
    <div id="createUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nuevo Usuario</h2>
                <button class="close" onclick="closeModal('createUserModal')">&times;</button>
            </div>
            
            <form action="<?php echo BASE_URL; ?>methods/users.php" method="post" id="usuarioForm" class="modal-body modal-eventos">
                <input type="hidden" name="action" value="create">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="usuario_nombre">Nombre <span class="required">*</span></label>
                        <input type="text" id="usuario_nombre" name="nombre" placeholder="Juan" required>
                    </div>

                    <div class="form-group">
                        <label for="usuario_apellido">Apellido <span class="required">*</span></label>
                        <input type="text" id="usuario_apellido" name="apellido" placeholder="Pérez" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="usuario_dni">DNI <span class="required">*</span></label>
                        <input type="text" id="usuario_dni" name="dni" maxlength="8" placeholder="12345678" required>
                        <small class="form-text">Sin puntos ni espacios (7-8 dígitos)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="usuario_fecha_nac">Fecha de Nacimiento <span class="required">*</span></label>
                        <input type="date" id="usuario_fecha_nac" name="fecha_nac" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="usuario_correo">Correo Electrónico <span class="required">*</span></label>
                    <input type="email" id="usuario_correo" name="correo" placeholder="ejemplo@gmail.com" required>
                </div>
                
                <div class="form-group">
                    <label for="usuario_contraseña">Contraseña <span class="required">*</span></label>
                    <input type="password" id="usuario_contraseña" name="contraseña" placeholder="Mínimo 6 caracteres" required>
                    <small class="form-text">Debe tener al menos 6 caracteres</small>
                </div>

                <div class="form-group">
                    <label for="usuario_rol">Rol <span class="required">*</span></label>
                    <select id="usuario_rol" name="id_rol" required>
                        <option value="2" selected>Cliente</option>
                        <option value="3">Recepcionista</option>
                        <option value="1">Administrador</option>
                    </select>
                    <small class="form-text">Selecciona el rol que tendrá el usuario en el sistema</small>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="closeModal('createUserModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Animaciones y funciones de las notificaciones toast -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    <!-- Animaciones y funciones de los modales -->
    <script src="<?php echo BASE_URL; ?>js/modal.js"></script>
    
    <script>
        // Mostrar mensaje de éxito de login si existe y si es admin
        <?php if (isset($_GET['login_exitoso']) && $_GET['login_exitoso'] == '1' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 1): ?>
            showToast('¡Bienvenido Admin! Has iniciado sesión correctamente.', 'success');
        <?php endif; ?>
        <?php if (isset($_GET['login_exitoso']) && $_GET['login_exitoso'] == '1' && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 2): ?>
            showToast('¡Bienvenido! Has iniciado sesión correctamente.', 'success');
        <?php endif; ?>
        
        // Mostrar mensaje de éxito o error al crear usuario
        <?php if (isset($_GET['success']) && $_GET['success'] == 'true' && isset($_GET['message'])): ?>
            showToast('<?php echo htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8'); ?>', 'success');
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'true' && isset($_GET['message'])): ?>
            showToast('<?php echo htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8'); ?>', 'error');
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] != 'true' && !isset($_GET['message'])): ?>
            showToast('<?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?>', 'error');
        <?php endif; ?>
        
        // Validación de DNI solo números
        const dniInput = document.getElementById('usuario_dni');
        if (dniInput) {
            dniInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    </script>
    
</body>
</html>