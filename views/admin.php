<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Crear Evento</title>
    <!-- Estilos base (incluye navbar) -->
    <link rel="stylesheet" href="../css/styles.css">
    <!-- Estilos del panel de administración -->
    <link rel="stylesheet" href="../css/admin.css">
    <!-- Estilos de las notificaciones toast -->
    <link rel="stylesheet" href="../css/toast.css">
    <!-- Estilos de modales -->
    <link rel="stylesheet" href="../css/modal.css">
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
                <button class="btn btn-secondary">
                    Ver Eventos
                </button>
            </div>
        </div>
        
        <!-- futuras secciones -->
        <div class="admin-section">
            <h2>Gestión de Ventas</h2>
            <div class="admin-actions">
                <button class="btn btn-secondary" disabled>
                    Ver Ventas (Próximamente)
                </button>
            </div>
        </div>
        
        <div class="admin-section">
            <h2>Gestión de Usuarios</h2>
            <div class="admin-actions">
                <button class="btn btn-secondary" disabled>
                    Ver Usuarios (Próximamente)
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal para Crear Evento -->
    <div id="createEventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nuevo Evento</h2>
                <button class="close" onclick="closeModal('createEventModal')">&times;</button>
            </div>
            
            <form action="../methods/events.php" method="post" id="eventoForm" class="modal-body">
                <div class="form-group">
                    <label for="nombre">Nombre del Evento <span class="required">*</span></label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción <span class="required">*</span></label>
                    <textarea id="descripcion" name="descripcion" placeholder="Describe el evento..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="fecha">Fecha del Evento <span class="required">*</span></label>
                    <input type="date" id="fecha" name="fecha" required>
                </div>
                
                <div class="form-group">
                    <label for="cupo_total">Cupo Total <span class="required">*</span></label>
                    <input type="number" id="cupo_total" name="cupo_total" min="1" placeholder="Ej: 100" required>
                </div>
                
                <div class="form-group">
                    <label for="cantidad_anticipadas">Cantidad de Entradas Anticipadas <span class="required">*</span></label>
                    <input type="number" id="cantidad_anticipadas" name="cantidad_anticipadas" min="1" placeholder="Ej: 50" required>
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
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createEventModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Evento</button>
                </div>
            </form>
        </div>
        </div>
    </div>
    
    <!-- Animaciones y funciones de las notificaciones toast -->
    <script src="../js/toast.js"></script>
    <!-- Animaciones y funciones de los modales -->
    <script src="../js/modal.js"></script>
    
</body>
</html>