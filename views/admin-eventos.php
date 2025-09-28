<?php
// Incluir configuración de la base de datos
require_once 'config/database.php';

// Función para listar todos los eventos con conteo de entradas vendidas
function listar_eventos() {
    try {
        $conexion = db_connection();
        
        $sql = "SELECT 
                    e.id, 
                    e.nombre, 
                    e.descripcion, 
                    e.fecha, 
                    e.cupo_total, 
                    e.cantidad_anticipadas, 
                    e.precio_anticipadas, 
                    e.precio_en_puerta, 
                    e.banner,
                    COALESCE(COUNT(CASE WHEN ent.id_estado <> 2 THEN 1 END), 0) as vendidas
                FROM eventos e
                LEFT JOIN entradas ent ON e.id = ent.id_evento
                GROUP BY e.id, e.nombre, e.descripcion, e.fecha, e.cupo_total, e.cantidad_anticipadas, e.precio_anticipadas, e.precio_en_puerta, e.banner
                ORDER BY e.fecha ASC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'message' => 'Eventos obtenidos exitosamente',
            'data' => $eventos
        ];
        
    } catch (PDOException $e) {
        error_log("Error en listar_eventos: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        error_log("Error general en listar_eventos: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// Función para obtener un evento por ID
function obtener_evento($id) {
    try {
        $conexion = db_connection();
        
        $sql = "SELECT id, nombre, descripcion, fecha, cupo_total, cantidad_anticipadas, precio_anticipadas, precio_en_puerta, banner 
                FROM eventos 
                WHERE id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'message' => 'Evento obtenido exitosamente',
            'data' => $evento
        ];
        
    } catch (PDOException $e) {
        error_log("Error en obtener_evento: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        error_log("Error general en obtener_evento: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// Obtener los eventos
$eventos_resultado = listar_eventos();
$eventos = $eventos_resultado['success'] ? $eventos_resultado['data'] : [];

// Verificar si se está editando un evento
$evento_editando = null;
$mostrar_modal = false;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $evento_resultado = obtener_evento($_GET['edit']);
    if ($evento_resultado['success'] && $evento_resultado['data']) {
        $evento_editando = $evento_resultado['data'];
        $mostrar_modal = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestión de Eventos</title>
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
            
            <h1>Gestión de Eventos</h1>
            
            <!-- Lista de eventos -->
            <div class="events-list-container">
                <h2>Eventos Creados</h2>
                <div class="events-list">
                    <?php if (empty($eventos)): ?>
                        <div class="no-events">No hay eventos creados</div>
                    <?php else: ?>
                        <table class="events-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Fecha Evento</th>
                                    <th>Precio Anticipadas</th>
                                    <th>Precio en Puerta</th>
                                    <th>Cupo Total</th>
                                    <th>Anticipadas</th>
                                    <th>Vendidas</th>
                                    <th>Cupo Disponible</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eventos as $evento): ?>
                                    <?php
                                    $fecha = new DateTime($evento['fecha']);
                                    $fechaFormateada = $fecha->format('d-m-Y');
                                    $cupoDisponible = $evento['cupo_total'] - $evento['vendidas'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($evento['nombre']); ?></td>
                                        <td><?php echo $fechaFormateada; ?></td>
                                        <td>$<?php echo number_format($evento['precio_anticipadas'], 0, ',', '.'); ?></td>
                                        <td>$<?php echo number_format($evento['precio_en_puerta'], 0, ',', '.'); ?></td>
                                        <td><?php echo $evento['cupo_total']; ?></td>
                                        <td><?php echo $evento['cantidad_anticipadas']; ?></td>
                                        <td><?php echo $evento['vendidas']; ?></td>
                                        <td><?php echo $cupoDisponible; ?></td>
                                        <td>
                                            <a href="../index.php?page=admin-eventos&edit=<?php echo $evento['id']; ?>" class="btn btn-primary btn-sm">
                                                Editar
                                            </a>
                                            <a href="../methods/events.php?action=delete&id=<?php echo $evento['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Estás seguro de que quieres eliminar este evento?')">
                                                Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Evento -->
    <div id="editEventModal" class="modal <?php echo $mostrar_modal ? 'show' : ''; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Evento</h2>
                <button class="close" onclick="closeModal('editEventModal')">&times;</button>
            </div>
            
            <form action="../methods/events.php" method="post" enctype="multipart/form-data" id="editEventForm" class="modal-body modal-eventos">
                <input type="hidden" name="evento_id" value="<?php echo $evento_editando ? $evento_editando['id'] : ''; ?>">
                <input type="hidden" name="action" value="update">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_nombre">Nombre del Evento <span class="required">*</span></label>
                        <input type="text" id="edit_nombre" name="nombre" value="<?php echo $evento_editando ? htmlspecialchars($evento_editando['nombre']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_fecha">Fecha del Evento <span class="required">*</span></label>
                        <input type="date" id="edit_fecha" name="fecha" value="<?php echo $evento_editando ? $evento_editando['fecha'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_descripcion">Descripción <span class="required">*</span></label>
                    <textarea id="edit_descripcion" name="descripcion" placeholder="Descripción del evento..." required><?php echo $evento_editando ? htmlspecialchars($evento_editando['descripcion']) : ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_cupo_total">Cupo Total <span class="required">*</span></label>
                        <input type="number" id="edit_cupo_total" name="cupo_total" min="1" placeholder="Ej: 100" value="<?php echo $evento_editando ? $evento_editando['cupo_total'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_cantidad_anticipadas">Cantidad de Anticipadas <span class="required">*</span></label>
                        <input type="number" id="edit_cantidad_anticipadas" name="cantidad_anticipadas" min="1" placeholder="Ej: 50" value="<?php echo $evento_editando ? $evento_editando['cantidad_anticipadas'] : ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_precio_anticipadas">Precio Anticipadas ($) <span class="required">*</span></label>
                        <input type="number" id="edit_precio_anticipadas" name="precio_anticipadas" min="0" step="0.01" placeholder="Ej: 1500.00" value="<?php echo $evento_editando ? $evento_editando['precio_anticipadas'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_precio_en_puerta">Precio en Puerta ($) <span class="required">*</span></label>
                        <input type="number" id="edit_precio_en_puerta" name="precio_en_puerta" min="0" step="0.01" placeholder="Ej: 2000.00" value="<?php echo $evento_editando ? $evento_editando['precio_en_puerta'] : ''; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_banner">Banner</label>
                    <input type="file" id="edit_banner" name="banner" accept="image/*">
                    <small class="form-text">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                    <?php if ($evento_editando && $evento_editando['banner']): ?>
                        <div class="current-banner-preview">
                            <small>Imagen actual:</small>
                            <img src="../<?php echo $evento_editando['banner']; ?>" alt="Banner actual" style="max-width: 100px; max-height: 60px; margin-top: 5px;">
                        </div>
                    <?php else: ?>
                        <div class="current-banner-preview">
                            <small>Sin imagen actual</small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="modal-footer">
                    <a href="?page=admin-eventos" class="btn btn-danger">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="../js/toast.js"></script>
    <script src="../js/modal.js"></script>
</body>
</html>