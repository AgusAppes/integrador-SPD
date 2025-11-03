<?php
// Incluir archivo de configuración con la conexión a la base de datos
require_once 'config/database.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: ' . BASE_URL . 'index.php?page=login&error=' . urlencode('Debes iniciar sesión para acceder a tu perfil'));
    exit;
}

// Obtener ID del usuario desde la sesión
$usuario_id = $_SESSION['usuario_id'];

// Función para obtener datos completos del usuario
function obtener_datos_usuario($id) {
    try {
        $conexion = db_connection();
        
        $sql = "SELECT id, dni, nombre, apellido, fecha_nac, correo, id_rol 
                FROM usuarios 
                WHERE id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            return [
                'success' => true,
                'data' => $usuario
            ];
        } else {
            return [
                'success' => false,
                'data' => null
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error en obtener_datos_usuario: " . $e->getMessage());
        return [
            'success' => false,
            'data' => null
        ];
    }
}

// Función para obtener entradas del usuario
function obtener_entradas_usuario($id_usuario) {
    try {
        $conexion = db_connection();
        
        $sql = "SELECT 
            v.id as id_venta,
            v.fecha_venta,
            v.monto_total,
            e.id as evento_id,
            e.nombre as evento_nombre,
            e.fecha as evento_fecha,
            ent.id as entrada_id,
            ent.nro_serie,
            ent.precio as precio_entrada,
            est.id as estado_id,
            est.nombre as estado_nombre
        FROM ventas v
        INNER JOIN detalle_venta dv ON v.id = dv.id_venta
        INNER JOIN entradas ent ON dv.id_entrada = ent.id
        INNER JOIN eventos e ON ent.id_evento = e.id
        INNER JOIN estados est ON ent.id_estado = est.id
        WHERE v.id_usuario = ?
        ORDER BY v.fecha_venta DESC, e.fecha ASC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id_usuario]);
        
        $entradas_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Agrupar por venta para mostrar mejor
        $ventas_agrupadas = [];
        foreach ($entradas_raw as $entrada) {
            $venta_id = $entrada['id_venta'];
            
            if (!isset($ventas_agrupadas[$venta_id])) {
                $ventas_agrupadas[$venta_id] = [
                    'id_venta' => $venta_id,
                    'fecha_venta' => $entrada['fecha_venta'],
                    'monto_total' => $entrada['monto_total'],
                    'evento_nombre' => $entrada['evento_nombre'],
                    'evento_fecha' => $entrada['evento_fecha'],
                    'entradas' => []
                ];
            }
            
            $ventas_agrupadas[$venta_id]['entradas'][] = [
                'id' => $entrada['entrada_id'],
                'nro_serie' => $entrada['nro_serie'],
                'precio' => $entrada['precio_entrada'],
                'estado_id' => $entrada['estado_id'],
                'estado_nombre' => ucfirst($entrada['estado_nombre'])
            ];
        }
        
        return [
            'success' => true,
            'data' => array_values($ventas_agrupadas)
        ];
        
    } catch (PDOException $e) {
        error_log("Error en obtener_entradas_usuario: " . $e->getMessage());
        return [
            'success' => false,
            'data' => []
        ];
    }
}

// Obtener datos completos del usuario
$datos_usuario_resultado = obtener_datos_usuario($usuario_id);
$datos_usuario = $datos_usuario_resultado['success'] ? $datos_usuario_resultado['data'] : null;

// Si no se encontraron datos, redirigir al login
if (!$datos_usuario) {
    header('Location: ' . BASE_URL . 'index.php?page=login&error=' . urlencode('Error al obtener datos del usuario'));
    exit;
}

// Obtener entradas del usuario
$entradas_resultado = obtener_entradas_usuario($usuario_id);
$entradas = $entradas_resultado['success'] ? $entradas_resultado['data'] : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
    
    <!-- Archivos CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/toast.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/perfil.css">
</head>
<body>
    <!-- Container para notificaciones toast -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- include navbar -->
    <?php include 'navbar.php'; ?>
    
    <!-- Contenido principal -->
    <div class="main-content">
        <div class="perfil-container">
            <h1>Mi Perfil</h1>
            <p class="perfil-descripcion">Aquí puedes ver y editar tu información personal</p>
            
            <!-- Formulario de perfil -->
            <form action="<?php echo BASE_URL; ?>methods/users.php" method="post" class="perfil-form" id="perfilForm">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($datos_usuario['nombre']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="apellido">Apellido <span class="required">*</span></label>
                        <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($datos_usuario['apellido']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="dni">DNI <span class="required">*</span></label>
                        <input type="text" id="dni" name="dni" maxlength="8" value="<?php echo htmlspecialchars($datos_usuario['dni']); ?>" required>
                        <small class="form-text">Sin puntos ni espacios (7-8 dígitos)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_nac">Fecha de Nacimiento <span class="required">*</span></label>
                        <input type="date" id="fecha_nac" name="fecha_nac" value="<?php 
                            if (!empty($datos_usuario['fecha_nac'])) {
                                $fecha_nac = $datos_usuario['fecha_nac'];
                                if (strpos($fecha_nac, ' ') !== false) {
                                    $fecha_nac = substr($fecha_nac, 0, 10);
                                }
                                $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_nac);
                                if (!$fecha_obj) {
                                    $fecha_obj = DateTime::createFromFormat('Y-m-d H:i:s', $datos_usuario['fecha_nac']);
                                }
                                if ($fecha_obj) {
                                    echo $fecha_obj->format('Y-m-d');
                                } else {
                                    echo $fecha_nac;
                                }
                            }
                        ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo Electrónico <span class="required">*</span></label>
                    <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($datos_usuario['correo']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="contraseña">Nueva Contraseña</label>
                    <?php if ($datos_usuario): ?>
                        <div class="password-info">
                            <small class="form-text" style="color: #28a745; font-weight: 600;">
                                ✓ Contraseña actual establecida
                            </small>
                        </div>
                    <?php endif; ?>
                    <input type="password" id="contraseña" name="contraseña" placeholder="Dejar en blanco para mantener la actual" autocomplete="new-password">
                    <small class="form-text">Solo completa si deseas cambiar la contraseña. Mínimo 6 caracteres.</small>
                </div>
                
                <!-- Información de solo lectura: Rol -->
                <div class="form-group readonly-info">
                    <label>Rol</label>
                    <div class="readonly-field">
                        <?php
                        $rol_nombre = 'Cliente';
                        if (isset($_SESSION['usuario_rol'])) {
                            switch($_SESSION['usuario_rol']) {
                                case 1:
                                    $rol_nombre = 'Administrador';
                                    break;
                                case 3:
                                    $rol_nombre = 'Recepcionista';
                                    break;
                                default:
                                    $rol_nombre = 'Cliente';
                            }
                        }
                        echo htmlspecialchars($rol_nombre);
                        ?>
                    </div>
                    <small class="form-text">El rol no puede ser modificado desde aquí. Contacta a un administrador si necesitas cambiar tu rol.</small>
                </div>
                
                <div class="form-actions">
                    <a href="<?php echo BASE_URL; ?>index.php?page=home" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
            
            <!-- Sección de Entradas -->
            <div class="entradas-section">
                <h2>Mis Entradas</h2>
                
                <?php if (empty($entradas)): ?>
                    <div class="no-entradas">
                        <p>No has comprado entradas aún.</p>
                        <a href="<?php echo BASE_URL; ?>index.php?page=catalogo" class="btn btn-primary">Ver Eventos</a>
                    </div>
                <?php else: ?>
                    <div class="entradas-list">
                        <?php foreach ($entradas as $venta): ?>
                            <div class="entrada-card">
                                <div class="entrada-header">
                                    <div class="entrada-info">
                                        <h3><?php echo htmlspecialchars($venta['evento_nombre']); ?></h3>
                                        <p class="evento-fecha">
                                            <strong>Fecha del Evento:</strong> 
                                            <?php 
                                                $fecha_evento = new DateTime($venta['evento_fecha']);
                                                echo $fecha_evento->format('d/m/Y');
                                            ?>
                                        </p>
                                        <p class="venta-info">
                                            <strong>Fecha de Compra:</strong> 
                                            <?php 
                                                $fecha_venta = new DateTime($venta['fecha_venta']);
                                                echo $fecha_venta->format('d/m/Y H:i');
                                            ?>
                                        </p>
                                        <p class="monto-total">
                                            <strong>Total Pagado:</strong> $<?php echo number_format($venta['monto_total'], 2, ',', '.'); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="entradas-detalle">
                                    <h4>Entradas (<?php echo count($venta['entradas']); ?>)</h4>
                                    <div class="entradas-grid">
                                        <?php foreach ($venta['entradas'] as $entrada): ?>
                                            <div class="entrada-item">
                                                <div class="entrada-serie">
                                                    <span class="label">Nro. Serie:</span>
                                                    <span class="value"><?php echo htmlspecialchars($entrada['nro_serie']); ?></span>
                                                </div>
                                                <div class="entrada-precio">
                                                    <span class="label">Precio:</span>
                                                    <span class="value">$<?php echo number_format($entrada['precio'], 2, ',', '.'); ?></span>
                                                </div>
                                                <div class="entrada-estado">
                                                    <span class="label">Estado:</span>
                                                    <span class="estado-badge estado-<?php echo strtolower($entrada['estado_nombre']); ?>">
                                                        <?php echo htmlspecialchars($entrada['estado_nombre']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    <script src="<?php echo BASE_URL; ?>js/perfil.js"></script>
    
    <script>
        // Mostrar mensaje de éxito o error
        <?php if (isset($_GET['success'])): ?>
            showToast('<?php echo htmlspecialchars(urldecode($_GET['success']), ENT_QUOTES, 'UTF-8'); ?>', 'success');
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            showToast('<?php echo htmlspecialchars(urldecode($_GET['error']), ENT_QUOTES, 'UTF-8'); ?>', 'error');
        <?php endif; ?>
    </script>
</body>
</html>

