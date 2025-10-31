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
<body class="admin-page">
    <!-- Container para notificaciones toast -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- Barra de navegación -->
    <?php include 'navbar.php'; ?>
    
    <!-- Contenido principal -->
    <div class="main-content">
        <div class="admin-container">
            <!-- Barra lateral -->
            <div class="admin-sidebar">
                <h2>Panel de Administración</h2>
                
                <!-- Sección de Eventos -->
                <div class="sidebar-section">
                    <h3>Gestión de Eventos</h3>
                    <div class="sidebar-actions">
                        <button class="btn btn-secondary sidebar-btn" onclick="openModal('createEventModal')">
                            Crear Evento
                        </button>
                        <button class="btn btn-secondary sidebar-btn" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?page=admin-eventos'">
                            Gestionar Eventos
                        </button>
                    </div>
                </div>
                
                <!-- Futuras secciones -->
                <div class="sidebar-section">
                    <h3>Gestión de Ventas</h3>
                    <div class="sidebar-actions">
                        <button class="btn btn-secondary sidebar-btn" disabled>
                            💰 Ver Ventas (Próximamente)
                        </button>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <h3>Gestión de Usuarios</h3>
                    <div class="sidebar-actions">
                        <button class="btn btn-secondary sidebar-btn" disabled>
                            👥 Ver Usuarios (Próximamente)
                        </button>
                    </div>
                </div>
            </div>
            
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