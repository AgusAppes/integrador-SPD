<?php
// Incluir archivo de configuración con la conexión a la base de datos
require_once 'config/database.php';

// ==========================
// Función: listar_eventos($filtros = [])
// ==========================
function listar_eventos($filtros = []) {
    try {
        // Crear conexión a la base de datos
        $conexion = db_connection();
        
        // Consulta SQL que obtiene todos los eventos junto con el conteo de entradas vendidas
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
                LEFT JOIN entradas ent ON e.id = ent.id_evento";
        
        // Array para almacenar las condiciones WHERE
        $where_conditions = [];
        $params = [];
        
        // Filtro por fecha desde
        if (!empty($filtros['fecha_desde'])) {
            $where_conditions[] = "DATE(e.fecha) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        
        // Filtro por fecha hasta
        if (!empty($filtros['fecha_hasta'])) {
            $where_conditions[] = "DATE(e.fecha) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        
        // Agregar condiciones WHERE si existen
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(" AND ", $where_conditions);
        }
        
        // Agregar GROUP BY y ORDER BY
        $sql .= " GROUP BY e.id, e.nombre, e.descripcion, e.fecha, e.cupo_total, e.cantidad_anticipadas, e.precio_anticipadas, e.precio_en_puerta, e.banner
                ORDER BY e.fecha ASC";
        
        // Preparar y ejecutar la consulta
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);
        
        // Obtener todos los resultados en un arreglo asociativo
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Devolver respuesta exitosa
        return [
            'success' => true,
            'message' => 'Eventos obtenidos exitosamente',
            'data' => $eventos
        ];
        
    } catch (PDOException $e) {
        // Capturar errores de base de datos
        error_log("Error en listar_eventos: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        // Capturar errores generales
        error_log("Error general en listar_eventos: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// ============================
// Función: obtener_evento($id)
// ============================
function obtener_evento($id) {
    try {
        // Conectar a la base de datos
        $conexion = db_connection();
        
        // Consulta SQL para obtener un evento específico por su ID
        $sql = "SELECT id, nombre, descripcion, fecha, cupo_total, cantidad_anticipadas, precio_anticipadas, precio_en_puerta, banner 
                FROM eventos 
                WHERE id = ?";
        
        // Preparar y ejecutar la consulta con parámetro
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        
        // Obtener resultado único
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Devolver evento
        return [
            'success' => true,
            'message' => 'Evento obtenido exitosamente',
            'data' => $evento
        ];
        
    } catch (PDOException $e) {
        // Error en la base de datos
        error_log("Error en obtener_evento: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        // Error general
        error_log("Error general en obtener_evento: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// ==============================
// Ejecución del listado de datos
// ==============================

// Obtener filtros del GET
$filtros = [
    'fecha_desde' => isset($_GET['filtro_fecha_desde']) ? trim($_GET['filtro_fecha_desde']) : '',
    'fecha_hasta' => isset($_GET['filtro_fecha_hasta']) ? trim($_GET['filtro_fecha_hasta']) : ''
];

// Obtener eventos con filtros aplicados
$eventos_resultado = listar_eventos($filtros);
// Si la consulta fue exitosa, asignar los datos, si no, un arreglo vacío
$eventos = $eventos_resultado['success'] ? $eventos_resultado['data'] : [];

// ==============================
// Modo edición de un evento
// ==============================

// Variables de control
$evento_editando = null;
$mostrar_modal = false;

// Verificar si se pasa el parámetro GET "edit"
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    // Buscar el evento a editar
    $evento_resultado = obtener_evento($_GET['edit']);
    if ($evento_resultado['success'] && $evento_resultado['data']) {
        // Asignar datos del evento y mostrar modal de edición
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

    <!-- Archivos CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/admin.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/toast.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/modal.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/ventas-styles.css">
</head>
<body>
    <!-- Contenedor de notificaciones -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- Navbar común -->
    <?php include 'navbar.php'; ?>
    
    <!-- Contenido principal -->
    <div class="main-content">
        <div class="admin-panel">
            <h1>Gestión de Eventos</h1>

            <!-- Filtros de búsqueda -->
            <div class="filtros-container">
                <h3>Filtros de Búsqueda</h3>
                <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="filtros-form">
                    <!-- Preservar parámetro page -->
                    <input type="hidden" name="page" value="admin-eventos">
                    <?php if (isset($_GET['edit'])): ?>
                        <input type="hidden" name="edit" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
                    <?php endif; ?>
                    
                    <!-- Filtro por Fecha Desde -->
                    <div class="form-group">
                        <label for="filtro_fecha_desde">Fecha Desde</label>
                        <input type="date" 
                               id="filtro_fecha_desde" 
                               name="filtro_fecha_desde" 
                               value="<?php echo htmlspecialchars($filtros['fecha_desde']); ?>">
                    </div>
                    
                    <!-- Filtro por Fecha Hasta -->
                    <div class="form-group">
                        <label for="filtro_fecha_hasta">Fecha Hasta</label>
                        <input type="date" 
                               id="filtro_fecha_hasta" 
                               name="filtro_fecha_hasta" 
                               value="<?php echo htmlspecialchars($filtros['fecha_hasta']); ?>">
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="form-group filtros-actions">
                        <button type="submit" class="btn btn-primary">
                            Filtrar
                        </button>
                        <a href="<?php echo BASE_URL; ?>index.php?page=admin-eventos<?php echo isset($_GET['edit']) ? '&edit=' . htmlspecialchars($_GET['edit']) : ''; ?>" 
                           class="btn btn-secondary">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Tabla de eventos -->
            <div class="events-list-container">
                <h2>Eventos Creados</h2>
                <div class="events-list">
                    <?php if (empty($eventos)): ?>
                        <!-- Mensaje si no hay eventos -->
                        <div class="no-events">
                            <?php if (!empty(array_filter($filtros))): ?>
                                No se encontraron resultados relacionados con la búsqueda.
                            <?php else: ?>
                                No hay eventos creados
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Tabla de datos -->
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
                                    // Formatear la fecha y calcular cupo disponible
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
                                            <!-- Botón de editar -->
                                            <a href="<?php echo BASE_URL; ?>index.php?page=admin-eventos&edit=<?php echo $evento['id']; ?>" class="btn btn-primary btn-sm">
                                                Editar
                                            </a>
                                            <!-- Botón de eliminar -->
                                            <a href="<?php echo BASE_URL; ?>methods/events.php?action=delete&id=<?php echo $evento['id']; ?>" 
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

    <!-- Modal de edición -->
    <div id="editEventModal" class="modal <?php echo $mostrar_modal ? 'show' : ''; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Evento</h2>
                <!-- Botón para cerrar modal -->
                <button class="close" onclick="closeModal('editEventModal')">&times;</button>
            </div>
            
            <!-- Formulario de edición -->
            <form action="<?php echo BASE_URL; ?>methods/events.php" method="post" enctype="multipart/form-data" id="editEventForm" class="modal-body modal-eventos">
                <!-- Campos ocultos -->
                <input type="hidden" name="evento_id" value="<?php echo $evento_editando ? $evento_editando['id'] : ''; ?>">
                <input type="hidden" name="action" value="update">
                
                <!-- Campos de texto -->
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
                    <textarea id="edit_descripcion" name="descripcion" required><?php echo $evento_editando ? htmlspecialchars($evento_editando['descripcion']) : ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_cupo_total">Cupo Total <span class="required">*</span></label>
                        <input type="number" id="edit_cupo_total" name="cupo_total" min="1" value="<?php echo $evento_editando ? $evento_editando['cupo_total'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_cantidad_anticipadas">Cantidad de Anticipadas <span class="required">*</span></label>
                        <input type="number" id="edit_cantidad_anticipadas" name="cantidad_anticipadas" min="1" value="<?php echo $evento_editando ? $evento_editando['cantidad_anticipadas'] : ''; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_precio_anticipadas">Precio Anticipadas ($) <span class="required">*</span></label>
                        <input type="number" id="edit_precio_anticipadas" name="precio_anticipadas" min="0" step="0.01" value="<?php echo $evento_editando ? $evento_editando['precio_anticipadas'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_precio_en_puerta">Precio en Puerta ($) <span class="required">*</span></label>
                        <input type="number" id="edit_precio_en_puerta" name="precio_en_puerta" min="0" step="0.01" value="<?php echo $evento_editando ? $evento_editando['precio_en_puerta'] : ''; ?>" required>
                    </div>
                </div>

                <!-- Carga de banner -->
                <div class="form-group">
                    <label for="edit_banner">Banner</label>
                    <input type="file" id="edit_banner" name="banner" accept="image/*">
                    <small>Formatos: JPG, PNG, GIF (máx. 5MB)</small>
                    <?php if ($evento_editando && $evento_editando['banner']): ?>
                        <div class="current-banner-preview">
                            <small>Imagen actual:</small>
                            <img src="<?php echo BASE_URL . htmlspecialchars($evento_editando['banner']); ?>" alt="Banner actual" style="max-width:100px;max-height:60px;margin-top:5px;">
                        </div>
                    <?php else: ?>
                        <div class="current-banner-preview">
                            <small>Sin imagen actual</small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Botones -->
                <div class="modal-footer">
                    <a href="?page=admin-eventos" class="btn btn-danger">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Scripts JS -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    <script src="<?php echo BASE_URL; ?>js/modal.js"></script>
</body>
</html>
