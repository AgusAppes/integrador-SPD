<?php
// Incluir archivo de configuración con la conexión a la base de datos
require_once 'config/database.php';

// ==========================
// Función: listar_ventas($filtros = [])
// ==========================
function listar_ventas($filtros = []) {
    try {
        // Crear conexión a la base de datos
        $conexion = db_connection();

        // Construir la consulta SQL base
        $sql = "SELECT 
            v.id as id_venta,
            v.fecha_venta,
            v.cantidad_entradas,
            v.monto_total,
            u.id as usuario_id,
            u.nombre as usuario_nombre,
            u.apellido as usuario_apellido,
            e.id as evento_id,
            e.nombre as evento_nombre,
            ent.nro_serie,
            ent.precio as precio_entrada,
            ent.id_estado,
            est.nombre as estado_nombre
        FROM ventas v
        INNER JOIN usuarios u ON v.id_usuario = u.id
        INNER JOIN detalle_venta dv ON v.id = dv.id_venta
        INNER JOIN entradas ent ON dv.id_entrada = ent.id
        INNER JOIN eventos e ON ent.id_evento = e.id
        INNER JOIN estados est ON ent.id_estado = est.id";
        
        // Array para almacenar las condiciones WHERE
        $where_conditions = [];
        $params = [];
        
        // Filtro por ID de venta
        if (!empty($filtros['id_venta'])) {
            $where_conditions[] = "v.id = ?";
            $params[] = intval($filtros['id_venta']);
        }
        
        // Filtro por nombre de usuario
        if (!empty($filtros['nombre_usuario'])) {
            $where_conditions[] = "(u.nombre LIKE ? OR u.apellido LIKE ? OR CONCAT(u.nombre, ' ', u.apellido) LIKE ?)";
            $nombre_busqueda = '%' . $filtros['nombre_usuario'] . '%';
            $params[] = $nombre_busqueda;
            $params[] = $nombre_busqueda;
            $params[] = $nombre_busqueda;
        }
        
        // Filtro por fecha
        if (!empty($filtros['fecha_desde'])) {
            $where_conditions[] = "DATE(v.fecha_venta) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where_conditions[] = "DATE(v.fecha_venta) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        
        // Filtro por estado
        if (!empty($filtros['estado']) && $filtros['estado'] != 'todos') {
            $where_conditions[] = "ent.id_estado = ?";
            $params[] = intval($filtros['estado']);
        }
        
        // Agregar condiciones WHERE si existen
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(" AND ", $where_conditions);
        }
        
        // Agregar ORDER BY
        $sql .= " ORDER BY v.fecha_venta DESC";

        // Preparar y ejecutar la consulta
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);

        // Obtener todos los resultados en un arreglo asociativo
        $ventas_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Agrupar ventas y calcular estados
        $ventas_agrupadas = [];
        foreach ($ventas_raw as $venta) {
            $venta_id = $venta['id_venta'];
            
            if (!isset($ventas_agrupadas[$venta_id])) {
                $ventas_agrupadas[$venta_id] = $venta;
                $ventas_agrupadas[$venta_id]['estados'] = [];
            }
            
            // Agregar estado a la lista de estados de esta venta
            $ventas_agrupadas[$venta_id]['estados'][] = [
                'id' => $venta['id_estado'],
                'nombre' => ucfirst($venta['estado_nombre'])
            ];
        }
        
        // Procesar estados para cada venta (calcular el estado principal)
        $ventas = [];
        foreach ($ventas_agrupadas as $venta) {
            // Contar estados
            $conteo_estados = [];
            foreach ($venta['estados'] as $estado) {
                $nombre = $estado['nombre'];
                if (!isset($conteo_estados[$nombre])) {
                    $conteo_estados[$nombre] = 0;
                }
                $conteo_estados[$nombre]++;
            }
            
            // Determinar el estado principal
            if (count($conteo_estados) == 1) {
                // Todas las entradas tienen el mismo estado
                $venta['estado_principal'] = array_key_first($conteo_estados);
                $venta['estado_tipo'] = 'unico';
            } else {
                // Hay múltiples estados
                $venta['estado_principal'] = 'Mixto';
                $venta['estado_tipo'] = 'mixto';
                $venta['estados_detalle'] = $conteo_estados;
            }
            
            // Determinar el ID de estado para colores (usar el más común)
            arsort($conteo_estados);
            $estado_mas_comun = array_key_first($conteo_estados);
            $venta['id_estado_principal'] = $venta['estados'][0]['id']; // Usar el primer estado como referencia
            
            $ventas[] = $venta;
        }

        // Devolver respuesta exitosa
        return [
            'success' => true,
            'message' => 'Ventas obtenidas exitosamente',
            'data' => $ventas
        ];
    } catch (PDOException $e) {
        // Capturar errores de base de datos
        error_log("Error en listar_ventas: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        // Capturar errores generales
        error_log("Error general en listar_ventas: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// ============================
// Función: obtener_ventas($id)
// ============================
function obtener_ventas($id) {
    try {
        // Conectar a la base de datos
        $conexion = db_connection();

        // Consulta SQL para obtener una venta específica por su ID con información básica
        $sql = "SELECT 
                v.id,
                v.fecha_venta,
                v.cantidad_entradas,
                v.monto_total,
                v.id_usuario,
                u.nombre as usuario_nombre,
                u.apellido as usuario_apellido,
                u.dni as usuario_dni,
                u.correo as usuario_correo
            FROM ventas v
            INNER JOIN usuarios u ON v.id_usuario = u.id
            WHERE v.id = ?";

        // Preparar y ejecutar la consulta con parámetro
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);

        // Obtener resultado único
        $venta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$venta) {
            return [
                'success' => false,
                'message' => 'Venta no encontrada',
                'data' => null
            ];
        }

        // Consulta SQL para obtener todas las entradas relacionadas con esta venta
        $sql_entradas = "SELECT 
                ent.id as entrada_id,
                ent.nro_serie,
                ent.precio as precio_entrada,
                ent.id_estado,
                est.nombre as estado_nombre,
                e.id as evento_id,
                e.nombre as evento_nombre,
                e.fecha as evento_fecha,
                te.nombre as tipo_entrada_nombre
            FROM detalle_venta dv
            INNER JOIN entradas ent ON dv.id_entrada = ent.id
            INNER JOIN estados est ON ent.id_estado = est.id
            INNER JOIN eventos e ON ent.id_evento = e.id
            INNER JOIN tipo_entrada te ON ent.id_tipo_entrada = te.id
            WHERE dv.id_venta = ?
            ORDER BY ent.id ASC";

        $stmt_entradas = $conexion->prepare($sql_entradas);
        $stmt_entradas->execute([$id]);

        // Obtener todas las entradas
        $entradas = $stmt_entradas->fetchAll(PDO::FETCH_ASSOC);

        // Agregar información de entradas al resultado
        $venta['entradas'] = $entradas;
        
        // Agregar información del evento (tomamos el primero ya que todas las entradas son del mismo evento)
        if (!empty($entradas)) {
            $venta['evento_nombre'] = $entradas[0]['evento_nombre'];
            $venta['evento_fecha'] = $entradas[0]['evento_fecha'];
            $venta['evento_id'] = $entradas[0]['evento_id'];
        }

        // Calcular precio promedio por entrada
        if ($venta['cantidad_entradas'] > 0) {
            $venta['precio_promedio'] = $venta['monto_total'] / $venta['cantidad_entradas'];
        } else {
            $venta['precio_promedio'] = 0;
        }

        // Devolver venta
        return [
            'success' => true,
            'message' => 'Venta obtenida exitosamente',
            'data' => $venta
        ];
    } catch (PDOException $e) {
        // Error en la base de datos
        error_log("Error en obtener_venta: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        // Error general
        error_log("Error general en obtener_venta: " . $e->getMessage());
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

// Verificar si el usuario es admin
$es_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 1;

// Obtener filtros del GET
$filtros = [
    'id_venta' => isset($_GET['filtro_id']) ? trim($_GET['filtro_id']) : '',
    'nombre_usuario' => isset($_GET['filtro_usuario']) ? trim($_GET['filtro_usuario']) : '',
    'fecha_desde' => isset($_GET['filtro_fecha_desde']) ? trim($_GET['filtro_fecha_desde']) : '',
    'fecha_hasta' => isset($_GET['filtro_fecha_hasta']) ? trim($_GET['filtro_fecha_hasta']) : '',
    'estado' => isset($_GET['filtro_estado']) ? trim($_GET['filtro_estado']) : ''
];

// Obtener ventas con filtros aplicados
$ventas_resultado = listar_ventas($filtros);
$ventas = $ventas_resultado['success'] ? $ventas_resultado['data'] : [];

// ==============================
// Modo edición de una venta
// ==============================

// Variables de control
$venta_editando = null;
$mostrar_modal = false;

// Verificar si se pasa el parámetro GET "edit"
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    // Buscar la venta a editar
    $venta_resultado = obtener_ventas($_GET['edit']);
    if ($venta_resultado['success'] && $venta_resultado['data']) {
        // Asignar datos de la venta y mostrar modal de edición
        $venta_editando = $venta_resultado['data'];
        $mostrar_modal = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestión de Ventas</title>

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
            <h1>Gestión de Ventas</h1>

            <!-- Filtros de búsqueda -->
            <div class="filtros-container">
                <h3>Filtros de Búsqueda</h3>
                <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="filtros-form">
                    <!-- Preservar parámetro page -->
                    <input type="hidden" name="page" value="admin-ventas">
                    <?php if (isset($_GET['edit'])): ?>
                        <input type="hidden" name="edit" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
                    <?php endif; ?>
                    
                    <!-- Filtro por ID de Venta -->
                    <div class="form-group">
                        <label for="filtro_id">ID Venta</label>
                        <input type="number" 
                               id="filtro_id" 
                               name="filtro_id" 
                               value="<?php echo htmlspecialchars($filtros['id_venta']); ?>" 
                               placeholder="Ej: 11">
                    </div>
                    
                    <!-- Filtro por Nombre de Usuario -->
                    <div class="form-group">
                        <label for="filtro_usuario">Nombre Usuario</label>
                        <input type="text" 
                               id="filtro_usuario" 
                               name="filtro_usuario" 
                               value="<?php echo htmlspecialchars($filtros['nombre_usuario']); ?>" 
                               placeholder="Ej: Carla">
                    </div>
                    
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
                    
                    <!-- Filtro por Estado -->
                    <div class="form-group">
                        <label for="filtro_estado">Estado</label>
                        <select id="filtro_estado" 
                                name="filtro_estado">
                            <option value="todos" <?php echo $filtros['estado'] == '' || $filtros['estado'] == 'todos' ? 'selected' : ''; ?>>Todos</option>
                            <option value="1" <?php echo $filtros['estado'] == '1' ? 'selected' : ''; ?>>Vendida</option>
                            <option value="2" <?php echo $filtros['estado'] == '2' ? 'selected' : ''; ?>>Cancelada</option>
                            <option value="3" <?php echo $filtros['estado'] == '3' ? 'selected' : ''; ?>>Consumida</option>
                        </select>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="form-group filtros-actions">
                        <button type="submit" class="btn btn-primary">
                            Filtrar
                        </button>
                        <a href="<?php echo BASE_URL; ?>index.php?page=admin-ventas<?php echo isset($_GET['edit']) ? '&edit=' . htmlspecialchars($_GET['edit']) : ''; ?>" 
                           class="btn btn-secondary">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabla de ventas -->
            <div class="sales-list-container">
                <div class="sales-header">
                    <h2>Ventas Registradas</h2>
                    <?php if ($es_admin): ?>
                        <a href="<?php echo BASE_URL; ?>methods/sales.php?action=exportar_informe" 
                           class="btn-download-report">
                            📥 Descargar Informe
                        </a>
                    <?php endif; ?>
                </div>
                <div class="sales-list">
                    <?php if (empty($ventas)): ?>
                        <!-- Mensaje si no hay ventas -->
                        <div class="no-events">
                            <?php if (!empty(array_filter($filtros))): ?>
                                No se encontraron resultados relacionados con la búsqueda.
                            <?php else: ?>
                                No hay ventas registradas
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Tabla de datos -->
                        <table class="events-table">
                            <thead>
                                <tr>
                                    <th>ID Venta</th>
                                    <th>Fecha Venta</th>
                                    <th>Usuario</th>
                                    <th>Evento</th>
                                    <th>Nro Serie</th>
                                    <th>Precio Entrada</th>
                                    <th>Cantidad</th>
                                    <th>Estado</th>
                                    <th>Monto Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventas as $venta): ?>
                                    <?php
                                    // Formatear la fecha
                                    $fecha = new DateTime($venta['fecha_venta']);
                                    $fechaFormateada = $fecha->format('d-m-Y H:i');
                                    // Nombre de usuario
                                    $usuarioNombre = $venta['usuario_nombre'];
                                    
                                    // Colores según estado
                                    $estado_colors = [
                                        'Vendida' => '#4caf50',
                                        'Cancelada' => '#f44336',
                                        'Consumida' => '#2196f3',
                                        'Mixto' => '#ff9800'
                                    ];
                                    
                                    $estado_nombre = isset($venta['estado_principal']) ? $venta['estado_principal'] : 'N/A';
                                    $estado_color = isset($estado_colors[$estado_nombre]) ? $estado_colors[$estado_nombre] : '#757575';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($venta['id_venta']); ?></td>
                                        <td><?php echo $fechaFormateada; ?></td>
                                        <td><?php echo htmlspecialchars($usuarioNombre); ?></td>
                                        <td><?php echo htmlspecialchars($venta['evento_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($venta['nro_serie']); ?></td>
                                        <td>$<?php echo number_format($venta['precio_entrada'], 0, ',', '.'); ?></td>
                                        <td><?php echo htmlspecialchars($venta['cantidad_entradas']); ?></td>
                                        <td>
                                            <span class="estado-badge" style="background-color: <?php echo $estado_color; ?>;">
                                                <?php echo htmlspecialchars($estado_nombre); ?>
                                                <?php if (isset($venta['estado_tipo']) && $venta['estado_tipo'] === 'mixto' && isset($venta['estados_detalle'])): ?>
                                                    <span class="estado-badge-detail">
                                                        <?php 
                                                        $detalles = [];
                                                        foreach ($venta['estados_detalle'] as $estado_nom => $cantidad) {
                                                            $detalles[] = "$estado_nom: $cantidad";
                                                        }
                                                        echo '(' . implode(', ', $detalles) . ')';
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($venta['monto_total'], 0, ',', '.'); ?></td>
                                        <td>
                                            <!-- Botón de editar -->
                                            <a href="<?php echo BASE_URL; ?>index.php?page=admin-ventas&edit=<?php echo $venta['id_venta']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                Editar
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
    <div id="editVentaModal" class="modal <?php echo $mostrar_modal ? 'show' : ''; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Venta</h2>
                <button class="close" onclick="closeModal('editVentaModal')">&times;</button>
            </div>
            
            <!-- Formulario de edición -->
            <form action="<?php echo BASE_URL; ?>methods/sales.php" method="post" id="editVentaForm" class="modal-body">
                <!-- Campos ocultos -->
                <input type="hidden" name="venta_id" value="<?php echo $venta_editando ? $venta_editando['id'] : ''; ?>">
                <input type="hidden" name="action" value="update">
                
                <!-- Resumen de la venta (solo lectura) -->
                <?php if ($venta_editando): ?>
                    <div class="venta-resumen">
                        <h3>Resumen de la Venta</h3>
                        
                        <div class="resumen-grid">
                            <div class="resumen-item">
                                <strong>ID Venta:</strong> #<?php echo htmlspecialchars($venta_editando['id']); ?>
                            </div>
                            <div class="resumen-item">
                                <strong>Fecha de Venta:</strong> 
                                <?php 
                                    $fecha = new DateTime($venta_editando['fecha_venta']);
                                    echo $fecha->format('d/m/Y H:i');
                                ?>
                            </div>
                            <div class="resumen-item">
                                <strong>Cliente:</strong> 
                                <?php echo htmlspecialchars($venta_editando['usuario_nombre'] . ' ' . $venta_editando['usuario_apellido']); ?>
                            </div>
                            <div class="resumen-item">
                                <strong>DNI:</strong> <?php echo htmlspecialchars($venta_editando['usuario_dni']); ?>
                            </div>
                            <div class="resumen-item">
                                <strong>Evento:</strong> <?php echo isset($venta_editando['evento_nombre']) ? htmlspecialchars($venta_editando['evento_nombre']) : 'N/A'; ?>
                            </div>
                            <div class="resumen-item">
                                <strong>Fecha Evento:</strong> 
                                <?php 
                                    if (isset($venta_editando['evento_fecha'])) {
                                        $fechaEvento = new DateTime($venta_editando['evento_fecha']);
                                        echo $fechaEvento->format('d/m/Y');
                                    } else {
                                        echo 'N/A';
                                    }
                                ?>
                            </div>
                            <div class="resumen-item">
                                <strong>Cantidad de Entradas:</strong> <?php echo htmlspecialchars($venta_editando['cantidad_entradas']); ?>
                            </div>
                            <div class="resumen-item">
                                <strong>Precio por Entrada:</strong> 
                                $<?php echo number_format($venta_editando['precio_promedio'], 2, ',', '.'); ?>
                            </div>
                            <div class="resumen-item resumen-item-full">
                                <strong>Monto Total:</strong> 
                                <span class="monto-total-display">
                                    $<?php echo number_format($venta_editando['monto_total'], 2, ',', '.'); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Resumen de estados (solo visualización) -->
                        <?php if (!empty($venta_editando['entradas'])): ?>
                            <div class="entradas-info">
                                <h4>Resumen de Estados</h4>
                                <div class="estados-grid">
                                    <?php 
                                    $estados_count = [];
                                    foreach ($venta_editando['entradas'] as $entrada) {
                                        $estado_nombre = ucfirst($entrada['estado_nombre']);
                                        if (!isset($estados_count[$estado_nombre])) {
                                            $estados_count[$estado_nombre] = 0;
                                        }
                                        $estados_count[$estado_nombre]++;
                                    }
                                    
                                    // Colores según estado
                                    $estado_colors = [
                                        'Vendida' => '#4caf50',
                                        'Cancelada' => '#f44336',
                                        'Consumida' => '#2196f3'
                                    ];
                                    
                                    foreach ($estados_count as $estado => $cantidad): 
                                        $clase_estado = 'estado-card';
                                        if ($estado === 'Vendida') {
                                            $clase_estado .= ' estado-card-vendida';
                                        } elseif ($estado === 'Cancelada') {
                                            $clase_estado .= ' estado-card-cancelada';
                                        } elseif ($estado === 'Consumida') {
                                            $clase_estado .= ' estado-card-consumida';
                                        }
                                    ?>
                                        <div class="<?php echo $clase_estado; ?>">
                                            <strong><?php echo htmlspecialchars($estado); ?>:</strong> 
                                            <span><?php echo $cantidad; ?> entrada(s)</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Advertencia de inconsistencia si existe -->
                <?php 
                if ($venta_editando && !empty($venta_editando['entradas'])) {
                    $entradas_reales = count($venta_editando['entradas']);
                    $cantidad_registrada = $venta_editando['cantidad_entradas'];
                    
                    if ($entradas_reales != $cantidad_registrada) {
                ?>
                    <div class="warning-box">
                        <strong>⚠️ Advertencia de Inconsistencia</strong>
                        <p>
                            La cantidad registrada (<?php echo $cantidad_registrada; ?>) no coincide con las entradas reales (<?php echo $entradas_reales; ?>).
                            Esta información es solo de referencia y no puede ser editada desde aquí.
                        </p>
                    </div>
                <?php 
                    }
                }
                ?>
                
                <div class="edit-form-section">
                    <h3>Campo Editable</h3>
                    <p>
                        Solo puedes editar el <strong>Monto Total</strong> de la venta. Los demás datos son históricos y no pueden modificarse.
                    </p>
                    
                    <!-- Campo editable: Monto Total -->
                    <div class="form-group monto-total-group">
                        <label for="edit_monto">
                            Monto Total <span class="required">*</span>
                        </label>
                        <input type="number" 
                               id="edit_monto" 
                               name="monto_total" 
                               class="monto-total-input"
                               min="0" 
                               step="0.01"
                               value="<?php echo $venta_editando ? number_format($venta_editando['monto_total'], 2, '.', '') : '0.00'; ?>" 
                               required
                               autocomplete="off">
                        <small>
                            Precio promedio por entrada: <strong>$<?php echo $venta_editando ? number_format($venta_editando['precio_promedio'], 2, ',', '.') : '0.00'; ?></strong>
                        </small>
                    </div>
                </div>
                
                <!-- Edición de Estados de Entradas -->
                <?php if ($venta_editando && !empty($venta_editando['entradas'])): ?>
                    <div class="form-group estados-entradas-section">
                        <h4>Editar Estados de las Entradas</h4>
                        <div class="entradas-editable-list">
                            <?php 
                            foreach ($venta_editando['entradas'] as $index => $entrada): 
                                $estado_actual = strtolower($entrada['estado_nombre']);
                                $clase_entrada = 'entrada-editable-item';
                                if ($estado_actual === 'vendida') {
                                    $clase_entrada .= ' entrada-editable-item-vendida';
                                } elseif ($estado_actual === 'cancelada') {
                                    $clase_entrada .= ' entrada-editable-item-cancelada';
                                } elseif ($estado_actual === 'consumida') {
                                    $clase_entrada .= ' entrada-editable-item-consumida';
                                }
                            ?>
                                <div class="<?php echo $clase_entrada; ?>">
                                    <div class="entrada-editable-header">
                                        <div class="entrada-editable-info">
                                            <strong>Entrada #<?php echo htmlspecialchars($entrada['nro_serie']); ?></strong>
                                            <div class="entrada-editable-precio">
                                                Precio: $<?php echo number_format($entrada['precio_entrada'], 0, ',', '.'); ?>
                                            </div>
                                        </div>
                                        <div class="entrada-editable-select-group">
                                            <label for="estado_entrada_<?php echo $entrada['entrada_id']; ?>">
                                                Estado:
                                            </label>
                                            <select 
                                                id="estado_entrada_<?php echo $entrada['entrada_id']; ?>"
                                                name="estados_entradas[<?php echo $entrada['entrada_id']; ?>]"
                                                class="entrada-editable-select"
                                                required>
                                                <option value="1" <?php echo $entrada['id_estado'] == 1 ? 'selected' : ''; ?>>Vendida</option>
                                                <option value="2" <?php echo $entrada['id_estado'] == 2 ? 'selected' : ''; ?>>Cancelada</option>
                                                <option value="3" <?php echo $entrada['id_estado'] == 3 ? 'selected' : ''; ?>>Consumida</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Información de solo lectura: Cantidad de Entradas -->
                <div class="form-group cantidad-entradas-readonly">
                    <label>Cantidad de Entradas (Solo lectura)</label>
                    <div class="cantidad-display">
                        <strong><?php echo $venta_editando ? $venta_editando['cantidad_entradas'] : '0'; ?> entrada(s)</strong>
                        <span class="cantidad-info">
                            (Entradas reales en sistema: <?php echo $venta_editando && !empty($venta_editando['entradas']) ? count($venta_editando['entradas']) : 0; ?>)
                        </span>
                    </div>
                    <small>
                        Este campo no puede editarse porque está vinculado a las entradas físicas en el sistema.
                    </small>
                </div>
                
                <!-- Botones -->
                <div class="modal-footer">
                    <a href="<?php echo BASE_URL; ?>index.php?page=admin-ventas" class="btn btn-danger">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts JS -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    <script src="<?php echo BASE_URL; ?>js/modal.js"></script>
    
    <script>
        // Mostrar mensajes de éxito o error si existen
        <?php if (isset($_GET['success']) && $_GET['success'] == 'true' && isset($_GET['message'])): ?>
            showToast('<?php echo htmlspecialchars(urldecode($_GET['message']), ENT_QUOTES, 'UTF-8'); ?>', 'success');
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] == 'true' && isset($_GET['message'])): ?>
            showToast('<?php echo htmlspecialchars(urldecode($_GET['message']), ENT_QUOTES, 'UTF-8'); ?>', 'error');
        <?php endif; ?>
    </script>
</body>
</html>