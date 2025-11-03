<?php
// Incluir configuración de la base de datos
require_once 'config/database.php';

// Función para listar todos los eventos
function listar_eventos() {
    try {
        $conexion = db_connection();
        
        // Obtener todos los eventos, ordenados por fecha
        $sql = "SELECT id, nombre, descripcion, fecha, cupo_total, cantidad_anticipadas, precio_anticipadas, precio_en_puerta, banner 
                FROM eventos 
                ORDER BY fecha ASC";
        
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

// Obtener los eventos
$eventos_resultado = listar_eventos();
$todos_eventos = $eventos_resultado['success'] ? $eventos_resultado['data'] : [];

// Separar eventos futuros y pasados
$eventos_futuros = [];
$eventos_pasados = [];
$fecha_actual = new DateTime();
$fecha_actual->setTime(0, 0, 0);

foreach ($todos_eventos as $evento) {
    $fecha_evento = new DateTime($evento['fecha']);
    $fecha_evento->setTime(0, 0, 0);
    
    if ($fecha_evento >= $fecha_actual) {
        $eventos_futuros[] = $evento;
    } else {
        $eventos_pasados[] = $evento;
    }
}

// Para el modal de compra, usar todos los eventos
$eventos = $todos_eventos;

// Verificar si se debe mostrar el modal de compra
$evento_compra = null;
$mostrar_modal_compra = false;
if (isset($_GET['comprar']) && is_numeric($_GET['comprar'])) {
    foreach ($eventos as $evento) {
        if ($evento['id'] == $_GET['comprar']) {
            // Verificar que el evento no haya pasado
            $fecha_evento = new DateTime($evento['fecha']);
            $fecha_evento->setTime(0, 0, 0); // Resetear hora a medianoche para comparar solo fechas
            $fecha_actual = new DateTime();
            $fecha_actual->setTime(0, 0, 0); // Resetear hora a medianoche para comparar solo fechas
            
            if ($fecha_evento >= $fecha_actual) {
                $evento_compra = $evento;
                $mostrar_modal_compra = true;
            }
            break;
        }
    }
}

// Verificar si se debe mostrar el modal de éxito
$mostrar_modal_exito = isset($_GET['compra_exitosa']) && $_GET['compra_exitosa'] == '1';
$nro_serie_compra = isset($_GET['nro_serie']) ? $_GET['nro_serie'] : '';
$mensaje_exito = isset($_GET['success']) ? urldecode($_GET['success']) : 'Entrada comprada exitosamente';
?>

<!DOCTYPE html>
<html lang="es"></html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Eventos</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/catalogo.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/modal.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/toast.css">
</head>
<body>
    <!-- Container para notificaciones toast -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- include navbar -->
    <?php include 'navbar.php'; ?>
    
    <div class="main-content">
        <div class="catalog-container">
            <?php if (!empty($eventos_futuros) || !empty($eventos_pasados)): ?>
                <!-- Eventos Futuros -->
                <?php if (!empty($eventos_futuros)): ?>
                    <div class="events-grid">
                        <?php foreach ($eventos_futuros as $evento): ?>
                            <div class="event-card">
                                <a href="<?php echo BASE_URL; ?>index.php?page=catalogo&comprar=<?php echo $evento['id']; ?>" class="event-image-link">
                                    <div class="event-image">
                                        <?php 
                                        $imagen_src = !empty($evento['banner']) ? BASE_URL . htmlspecialchars($evento['banner']) : BASE_URL . 'img/malpa.png';
                                        ?>
                                        <img src="<?php echo $imagen_src; ?>" alt="<?php echo htmlspecialchars($evento['nombre']); ?>">
                                        <div class="event-title-overlay">
                                            <h3 class="event-title"><?php echo htmlspecialchars($evento['nombre']); ?></h3>
                                        </div>
                                        <div class="event-price-overlay">
                                            <span class="price-tag">$<?php echo number_format($evento['precio_anticipadas'], 0, ',', '.'); ?></span>
                                        </div>
                                        <div class="event-hover-overlay">
                                            <span class="hover-message">Comprá tu anticipada</span>
                                        </div>
                                    </div>
                                </a>
                                
                                <div class="event-content">
                                    <p class="event-description"><?php echo htmlspecialchars($evento['descripcion']); ?></p>
                                    
                                    <div class="event-details">
                                        <div class="event-date">📆
                                            <?php 
                                            // Configurar local en español
                                            setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
                                            
                                            // Array de días de la semana en español
                                            $dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                            
                                            // Array de meses en español
                                            $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                                                     'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                            
                                            // Obtener información de la fecha
                                            $timestamp = strtotime($evento['fecha']);
                                            $dia_semana = $dias_semana[date('w', $timestamp)];
                                            $dia = date('j', $timestamp);
                                            $mes = $meses[date('n', $timestamp)];
                                            
                                            echo $dia_semana . ' ' . $dia . ' de ' . $mes;
                                            ?>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Eventos Pasados -->
                <?php if (!empty($eventos_pasados)): ?>
                    <div class="past-events-section">
                        <h2 class="past-events-title">Eventos Pasados</h2>
                        <div class="events-grid past-events-grid">
                            <?php foreach ($eventos_pasados as $evento): ?>
                                <div class="event-card past-event-card">
                                    <div class="event-image-link past-event-link">
                                        <div class="event-image">
                                            <?php 
                                            $imagen_src = !empty($evento['banner']) ? BASE_URL . htmlspecialchars($evento['banner']) : BASE_URL . 'img/malpa.png';
                                            ?>
                                            <img src="<?php echo $imagen_src; ?>" alt="<?php echo htmlspecialchars($evento['nombre']); ?>">
                                            <div class="event-title-overlay">
                                                <h3 class="event-title"><?php echo htmlspecialchars($evento['nombre']); ?></h3>
                                            </div>
                                            <div class="event-price-overlay">
                                                <span class="price-tag">$<?php echo number_format($evento['precio_anticipadas'], 0, ',', '.'); ?></span>
                                            </div>
                                            <div class="event-hover-overlay">
                                                <span class="hover-message">Ya no hay ventas</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="event-content">
                                        <p class="event-description"><?php echo htmlspecialchars($evento['descripcion']); ?></p>
                                        
                                        <div class="event-details">
                                            <div class="event-date">📆
                                                <?php 
                                                // Configurar local en español
                                                setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
                                                
                                                // Array de días de la semana en español
                                                $dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                                
                                                // Array de meses en español
                                                $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                                                         'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                                
                                                // Obtener información de la fecha
                                                $timestamp = strtotime($evento['fecha']);
                                                $dia_semana = $dias_semana[date('w', $timestamp)];
                                                $dia = date('j', $timestamp);
                                                $mes = $meses[date('n', $timestamp)];
                                                
                                                echo $dia_semana . ' ' . $dia . ' de ' . $mes;
                                                ?>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-events-container">
                    <div class="event-card no-events-card">
                        <div class="event-image">
                            <img src="<?php echo BASE_URL; ?>img/malpa.png" alt="No hay eventos">
                            <div class="event-title-overlay">
                                <h3 class="event-title">No hay eventos próximos</h3>
                            </div>
                        </div>
                        <div class="event-content">
                            <p class="event-description">Próximamente se agregarán nuevos eventos. ¡Mantente atento!</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para Gestionar Compra -->
    <div id="compraModal" class="modal <?php echo $mostrar_modal_compra ? 'show' : ''; ?>">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2>Gestionar Compra</h2>
                <button class="close" onclick="closeModal('compraModal')">&times;</button>
            </div>
            
            <div class="modal-body">
                <div class="compra-detalle">
                    <p><strong>Evento:</strong> <?php echo $evento_compra ? htmlspecialchars($evento_compra['nombre']) : ''; ?></p>
                    <p><strong>Total:</strong> $<?php echo $evento_compra ? number_format($evento_compra['precio_anticipadas'], 0, ',', '.') : ''; ?></p>
                </div>
            </div>
            
            <div class="modal-footer">
                <a href="?page=catalogo" class="btn btn-danger">Cancelar</a>
                <a href="<?php echo BASE_URL; ?>index.php?page=pago&evento_id=<?php echo $evento_compra ? $evento_compra['id'] : ''; ?>" class="btn btn-primary">Continuar</a>
            </div>
        </div>
    </div>

    <!-- Modal de Compra Exitosa -->
    <div id="exitoModal" class="modal modal-exito <?php echo $mostrar_modal_exito ? 'show' : ''; ?>">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h2>¡Compra Exitosa!</h2>
            </div>
            
            <div class="modal-body">
                <div class="exito-detalle">
                    <div class="exito-icon">✅</div>
                    <p class="exito-mensaje">
                        <strong><?php echo $mensaje_exito; ?></strong>
                    </p>
                    <?php if ($nro_serie_compra): ?>
                    <p class="exito-serie">
                        <strong>Número de serie:</strong> <?php echo htmlspecialchars($nro_serie_compra); ?>
                    </p>
                    <?php endif; ?>
                    <p class="exito-texto">
                        ¡Nos vemos en el evento!
                    </p>
                    <p class="exito-aviso">¡No olvides llevar tu DNI!</p>
                    <?php 
                    // Mostrar mensaje sobre el correo si está disponible
                    if (isset($_GET['correo_enviado']) && $_GET['correo_enviado'] == '0'):
                    ?>
                    <div style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px; margin-top: 15px; color: #856404; font-size: 0.9rem;">
                        <strong>ℹ️ Nota:</strong> El comprobante por correo no pudo enviarse automáticamente. Tu compra está confirmada y puedes ver tus entradas en tu perfil.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="modal-footer">
                <a href="?page=catalogo" class="btn btn-primary">Continuar</a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo BASE_URL; ?>js/modal.js"></script>
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    
    <script>
        // Mostrar mensaje de éxito de login si existe
        <?php if (isset($_GET['login_exitoso']) && $_GET['login_exitoso'] == '1'): ?>
            showToast('¡Bienvenido! Has iniciado sesión correctamente.', 'success');
        <?php endif; ?>
        
        // Mostrar mensaje de error si existe
        <?php if (isset($_GET['error'])): ?>
            showToast('<?php echo htmlspecialchars($_GET['error']); ?>', 'error');
        <?php endif; ?>
    </script>
</body>
</html>