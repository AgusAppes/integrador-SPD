<?php
// Incluir configuración de la base de datos
require_once 'config/database.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: ' . BASE_URL . 'index.php?page=login&error=' . urlencode('Debes iniciar sesión para realizar una compra'));
    exit;
}

// Verificar que se haya proporcionado un evento_id
if (!isset($_GET['evento_id']) || !is_numeric($_GET['evento_id'])) {
    header('Location: ' . BASE_URL . 'index.php?page=catalogo&error=' . urlencode('Evento no válido'));
    exit;
}

$evento_id = intval($_GET['evento_id']);

// Función para obtener datos del evento
function obtener_evento($id) {
    try {
        $conexion = db_connection();
        
        $sql = "SELECT id, nombre, descripcion, fecha, precio_anticipadas, banner 
                FROM eventos 
                WHERE id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($evento) {
            return [
                'success' => true,
                'data' => $evento
            ];
        } else {
            return [
                'success' => false,
                'data' => null
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error en obtener_evento: " . $e->getMessage());
        return [
            'success' => false,
            'data' => null
        ];
    }
}

// Obtener datos del evento
$evento_resultado = obtener_evento($evento_id);

if (!$evento_resultado['success'] || !$evento_resultado['data']) {
    header('Location: ' . BASE_URL . 'index.php?page=catalogo&error=' . urlencode('Evento no encontrado'));
    exit;
}

$evento = $evento_resultado['data'];

// Verificar que el evento no haya pasado
$fecha_evento = new DateTime($evento['fecha']);
$fecha_evento->setTime(0, 0, 0);
$fecha_actual = new DateTime();
$fecha_actual->setTime(0, 0, 0);

if ($fecha_evento < $fecha_actual) {
    header('Location: ' . BASE_URL . 'index.php?page=catalogo&error=' . urlencode('No se pueden comprar entradas para eventos pasados'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago - <?php echo htmlspecialchars($evento['nombre']); ?></title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>img/favicon.png">
    
    <!-- Archivos CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/toast.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/pago.css">
</head>
<body>
    <!-- Container para notificaciones toast -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- include navbar -->
    <?php include 'navbar.php'; ?>
    
    <!-- Contenido principal -->
    <div class="main-content">
        <div class="pago-container">
            <h1>Procesar Pago</h1>
            <p class="pago-descripcion">Completa los datos de tu tarjeta para finalizar la compra</p>
            
            <div class="pago-content">
                <!-- Resumen de compra -->
                <div class="resumen-compra">
                    <h2>Resumen de Compra</h2>
                    <div class="resumen-item">
                        <span class="resumen-label">Evento:</span>
                        <span class="resumen-value"><?php echo htmlspecialchars($evento['nombre']); ?></span>
                    </div>
                    <div class="resumen-item">
                        <span class="resumen-label">Fecha del Evento:</span>
                        <span class="resumen-value">
                            <?php 
                            $dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                            $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                                     'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                            $timestamp = strtotime($evento['fecha']);
                            $dia_semana = $dias_semana[date('w', $timestamp)];
                            $dia = date('j', $timestamp);
                            $mes = $meses[date('n', $timestamp)];
                            echo $dia_semana . ' ' . $dia . ' de ' . $mes;
                            ?>
                        </span>
                    </div>
                    <div class="resumen-item total">
                        <span class="resumen-label">Total a Pagar:</span>
                        <span class="resumen-value">$<?php echo number_format($evento['precio_anticipadas'], 0, ',', '.'); ?></span>
                    </div>
                </div>
                
                <!-- Formulario de pago -->
                <div class="formulario-pago">
                    <h2>Datos de la Tarjeta</h2>
                    <form action="<?php echo BASE_URL; ?>methods/sales.php" method="post" id="pagoForm">
                        <input type="hidden" name="action" value="comprar">
                        <input type="hidden" name="id_evento" value="<?php echo $evento_id; ?>">
                        
                        <div class="form-group">
                            <label for="nombre_titular">Nombre del Titular <span class="required">*</span></label>
                            <input type="text" id="nombre_titular" name="nombre_titular" placeholder="Como aparece en la tarjeta" required autocomplete="cc-name">
                        </div>
                        
                        <div class="form-group">
                            <label for="numero_tarjeta">Número de Tarjeta <span class="required">*</span></label>
                            <input type="text" id="numero_tarjeta" name="numero_tarjeta" placeholder="1234 5678 9012 3456" maxlength="19" required autocomplete="cc-number">
                            <small class="form-text">Sin espacios ni guiones</small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha_vencimiento">Fecha de Vencimiento <span class="required">*</span></label>
                                <input type="text" id="fecha_vencimiento" name="fecha_vencimiento" placeholder="MM/AA" maxlength="5" required autocomplete="cc-exp">
                                <small class="form-text">Formato: MM/AA</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="cvv">CVV <span class="required">*</span></label>
                                <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="4" required autocomplete="cc-csc">
                                <small class="form-text">3 o 4 dígitos</small>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="<?php echo BASE_URL; ?>index.php?page=catalogo" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Pagar</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Información de seguridad -->
            <div class="seguridad-info">
                <p>🔒 Tu información está protegida. Te enviaremos el comprobante de pago a tu correo electrónico.</p>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    <script src="<?php echo BASE_URL; ?>js/pago.js"></script>
    
    <script>
        // Mostrar mensaje de error si existe
        <?php if (isset($_GET['error'])): ?>
            showToast('<?php echo htmlspecialchars(urldecode($_GET['error']), ENT_QUOTES, 'UTF-8'); ?>', 'error');
        <?php endif; ?>
    </script>
</body>
</html>

