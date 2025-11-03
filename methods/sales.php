<?php
//configuración de base de datos
require_once '../config/database.php';

// Cargar configuración de correo si existe
$email_config_path = __DIR__ . '/../config/email_config.php';
if (file_exists($email_config_path)) {
    require_once $email_config_path;
} else {
    // Configuración por defecto
    if (!defined('USE_PHP_MAIL')) {
        define('USE_PHP_MAIL', true);
    }
}

// Función para enviar correo usando SMTP directo (Gmail)
function enviar_correo_smtp($correo_destino, $asunto, $mensaje_html) {
    // Verificar si hay configuración SMTP
    if (!defined('EMAIL_SMTP_HOST') || !defined('EMAIL_SMTP_USER') || !defined('EMAIL_SMTP_PASS')) {
        error_log("SMTP no configurado: Falta email_config.php con credenciales");
        return false;
    }
    
    try {
        // Crear conexión SMTP
        $smtp_host = EMAIL_SMTP_HOST;
        $smtp_port = defined('EMAIL_SMTP_PORT') ? EMAIL_SMTP_PORT : 587;
        $smtp_secure = defined('EMAIL_SMTP_SECURE') ? EMAIL_SMTP_SECURE : 'tls';
        
        // Abrir conexión
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        $socket = stream_socket_client(
            ($smtp_secure === 'ssl' ? 'ssl://' : '') . $smtp_host . ':' . $smtp_port,
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );
        
        if (!$socket) {
            error_log("Error conectando a SMTP: $errstr ($errno)");
            return false;
        }
        
        // Leer respuesta inicial
        fgets($socket, 515);
        
        // EHLO
        fputs($socket, "EHLO " . EMAIL_SMTP_HOST . "\r\n");
        fgets($socket, 515);
        
        // STARTTLS si es necesario
        if ($smtp_secure === 'tls') {
            fputs($socket, "STARTTLS\r\n");
            fgets($socket, 515);
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fputs($socket, "EHLO " . EMAIL_SMTP_HOST . "\r\n");
            fgets($socket, 515);
        }
        
        // Autenticación
        fputs($socket, "AUTH LOGIN\r\n");
        fgets($socket, 515);
        
        fputs($socket, base64_encode(EMAIL_SMTP_USER) . "\r\n");
        fgets($socket, 515);
        
        fputs($socket, base64_encode(EMAIL_SMTP_PASS) . "\r\n");
        $response = fgets($socket, 515);
        
        if (strpos($response, '235') === false) {
            error_log("Error de autenticación SMTP");
            fclose($socket);
            return false;
        }
        
        // FROM
        $from_email = defined('EMAIL_FROM_ADDRESS') ? EMAIL_FROM_ADDRESS : EMAIL_SMTP_USER;
        $from_name = defined('EMAIL_FROM_NAME') ? EMAIL_FROM_NAME : 'Malpa Eventos';
        fputs($socket, "MAIL FROM: <" . $from_email . ">\r\n");
        fgets($socket, 515);
        
        // TO
        fputs($socket, "RCPT TO: <" . $correo_destino . ">\r\n");
        fgets($socket, 515);
        
        // DATA
        fputs($socket, "DATA\r\n");
        fgets($socket, 515);
        
        // Headers
        $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
        $headers .= "To: <" . $correo_destino . ">\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($asunto) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n";
        
        fputs($socket, $headers . "\r\n");
        fputs($socket, chunk_split(base64_encode($mensaje_html)) . "\r\n");
        fputs($socket, ".\r\n");
        
        $response = fgets($socket, 515);
        
        // QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        if (strpos($response, '250') !== false) {
            error_log("✓ Correo enviado exitosamente vía SMTP a: " . $correo_destino);
            return true;
        } else {
            error_log("✗ Error enviando correo vía SMTP: " . $response);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Excepción en enviar_correo_smtp: " . $e->getMessage());
        return false;
    }
}

// Función para enviar comprobante de compra por correo
function enviar_comprobante_compra($usuario, $evento, $nro_serie, $precio) {
    try {
        $correo_destino = $usuario['correo'];
        $nombre_completo = trim($usuario['nombre'] . ' ' . $usuario['apellido']);
        
        // Formatear fecha del evento
        $dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $timestamp = strtotime($evento['fecha']);
        $dia_semana = $dias_semana[date('w', $timestamp)];
        $dia = date('j', $timestamp);
        $mes = $meses[date('n', $timestamp)];
        $año = date('Y', $timestamp);
        $fecha_formateada = $dia_semana . ' ' . $dia . ' de ' . $mes . ' de ' . $año;
        
        // Fecha de compra
        $fecha_compra = date('d/m/Y H:i');
        
        // Asunto del correo
        $asunto = 'Comprobante de Compra - ' . htmlspecialchars($evento['nombre']);
        
        // Contenido HTML del correo
        $mensaje_html = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #1a0033 0%, #2d0052 50%, #1a0033 100%);
                    color: #ffffff;
                    padding: 30px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                    text-transform: uppercase;
                }
                .content {
                    padding: 30px;
                }
                .greeting {
                    font-size: 16px;
                    color: #333;
                    margin-bottom: 20px;
                }
                .comprobante-box {
                    background-color: #f9f9f9;
                    border: 2px solid #e0e0e0;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                }
                .comprobante-item {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 15px;
                    padding-bottom: 15px;
                    border-bottom: 1px solid #e0e0e0;
                }
                .comprobante-item:last-child {
                    border-bottom: none;
                    margin-bottom: 0;
                    padding-bottom: 0;
                }
                .comprobante-label {
                    font-weight: 600;
                    color: #666;
                }
                .comprobante-value {
                    color: #333;
                    text-align: right;
                }
                .serie-number {
                    font-size: 20px;
                    font-weight: bold;
                    color: #FF00FF;
                    letter-spacing: 2px;
                }
                .total-box {
                    background: linear-gradient(135deg, rgba(255, 0, 255, 0.1), rgba(157, 78, 221, 0.1));
                    border: 2px solid #FF00FF;
                    border-radius: 8px;
                    padding: 20px;
                    margin-top: 20px;
                    text-align: center;
                }
                .total-label {
                    font-size: 14px;
                    color: #666;
                    margin-bottom: 10px;
                }
                .total-amount {
                    font-size: 32px;
                    font-weight: bold;
                    color: #FF00FF;
                }
                .footer {
                    background-color: #f9f9f9;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                }
                .warning-box {
                    background-color: #fff3cd;
                    border: 1px solid #ffc107;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                    color: #856404;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎫 Comprobante de Compra</h1>
                </div>
                <div class="content">
                    <div class="greeting">
                        Hola <strong>' . htmlspecialchars($nombre_completo) . '</strong>,
                    </div>
                    
                    <p>Gracias por tu compra. Aquí está tu comprobante de compra:</p>
                    
                    <div class="comprobante-box">
                        <div class="comprobante-item">
                            <span class="comprobante-label">Evento:</span>
                            <span class="comprobante-value"><strong>' . htmlspecialchars($evento['nombre']) . '</strong></span>
                        </div>
                        <div class="comprobante-item">
                            <span class="comprobante-label">Fecha del Evento:</span>
                            <span class="comprobante-value">' . htmlspecialchars($fecha_formateada) . '</span>
                        </div>
                        <div class="comprobante-item">
                            <span class="comprobante-label">Número de Serie:</span>
                            <span class="comprobante-value serie-number">' . htmlspecialchars($nro_serie) . '</span>
                        </div>
                        <div class="comprobante-item">
                            <span class="comprobante-label">Fecha de Compra:</span>
                            <span class="comprobante-value">' . htmlspecialchars($fecha_compra) . '</span>
                        </div>
                    </div>
                    
                    <div class="total-box">
                        <div class="total-label">Total Pagado</div>
                        <div class="total-amount">$' . number_format($precio, 2, ',', '.') . '</div>
                    </div>
                    
                    <div class="warning-box">
                        <strong>⚠️ Importante:</strong> No olvides llevar tu DNI el día del evento. Guarda este correo como comprobante.
                    </div>
                    
                    <p style="margin-top: 30px; color: #666; font-size: 14px;">
                        ¡Nos vemos en el evento!<br>
                        <strong>Malpa Eventos</strong>
                    </p>
                </div>
                <div class="footer">
                    <p>Este es un correo automático, por favor no respondas.</p>
                    <p>Si tienes alguna consulta, contacta con nosotros.</p>
                </div>
            </div>
        </body>
        </html>';
        
        // Validar que el correo sea válido
        if (!filter_var($correo_destino, FILTER_VALIDATE_EMAIL)) {
            error_log("Error: Correo inválido para enviar comprobante: " . $correo_destino);
            return false;
        }
        
        // Headers para correo HTML (mejorados para mejor compatibilidad)
        $boundary = md5(uniqid(time()));
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Malpa Eventos <noreply@malpaeventos.com>\r\n";
        $headers .= "Reply-To: noreply@malpaeventos.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "X-Priority: 3\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        
        // Codificar asunto para UTF-8
        $asunto_encoded = '=?UTF-8?B?' . base64_encode($asunto) . '?=';
        
        // Intentar enviar correo con mail()
        $envio_exitoso = false;
        try {
            // Usar ini_set para mejorar configuración de mail en XAMPP
            $smtp_original = ini_get('SMTP');
            $smtp_port_original = ini_get('smtp_port');
            
            // Intentar con mail() estándar
            $envio_exitoso = @mail($correo_destino, $asunto_encoded, $mensaje_html, $headers);
            
            if ($envio_exitoso) {
                error_log("✓ Correo de comprobante enviado exitosamente a: " . $correo_destino);
            } else {
                $error = error_get_last();
                $error_msg = $error ? $error['message'] : 'Desconocido';
                error_log("✗ Error al enviar correo con mail() a: " . $correo_destino . " - Error: " . $error_msg);
                
                // Crear archivo de log como respaldo
                $log_file = __DIR__ . '/../logs/comprobantes_' . date('Y-m-d') . '.log';
                $log_dir = dirname($log_file);
                if (!file_exists($log_dir)) {
                    @mkdir($log_dir, 0755, true);
                }
                
                $log_content = "[" . date('Y-m-d H:i:s') . "] COMPROBANTE NO ENVIADO (mail() falló)\n";
                $log_content .= "Email destino: " . $correo_destino . "\n";
                $log_content .= "Evento: " . $evento['nombre'] . "\n";
                $log_content .= "Serie: " . $nro_serie . "\n";
                $log_content .= "Total: $" . number_format($precio, 2, ',', '.') . "\n";
                $log_content .= "Error: " . $error_msg . "\n";
                $log_content .= "---\n";
                
                @file_put_contents($log_file, $log_content, FILE_APPEND);
                
                // Guardar también un archivo HTML del comprobante para envío manual
                $html_file = __DIR__ . '/../logs/comprobantes_html/comprobante_' . $nro_serie . '.html';
                $html_dir = dirname($html_file);
                if (!file_exists($html_dir)) {
                    @mkdir($html_dir, 0755, true);
                }
                @file_put_contents($html_file, $mensaje_html);
                
                // Intentar método alternativo: SMTP directo si está configurado
                if (!defined('USE_PHP_MAIL') || !USE_PHP_MAIL) {
                    $envio_exitoso = enviar_correo_smtp($correo_destino, $asunto, $mensaje_html);
                    if ($envio_exitoso) {
                        error_log("✓ Correo enviado exitosamente usando SMTP alternativo");
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Excepción al enviar correo: " . $e->getMessage());
            $envio_exitoso = false;
        }
        
        return $envio_exitoso;
        
    } catch (Exception $e) {
        error_log("Error en enviar_comprobante_compra: " . $e->getMessage());
        return false;
    }
}

// Función para procesar compra de entrada anticipada
function procesar_compra_anticipada($id_evento, $id_usuario = null)
{
    try {
        $conexion = db_connection();

        // Verificar que hay un usuario logueado
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!$id_usuario) {
            if (!isset($_SESSION['usuario_id']) || !$_SESSION['usuario_id']) {
                return [
                    'success' => false,
                    'message' => 'Debes iniciar sesión para realizar una compra',
                    'data' => null
                ];
            }
            $id_usuario = $_SESSION['usuario_id'];
        }

        // Iniciar transacción
        $conexion->beginTransaction();

        // 1. Verificar que el evento existe y tiene anticipadas disponibles
        $sql_evento = "SELECT * FROM eventos WHERE id = :id_evento";
        $stmt_evento = $conexion->prepare($sql_evento);
        $stmt_evento->execute([':id_evento' => $id_evento]);
        $evento = $stmt_evento->fetch(PDO::FETCH_ASSOC);

        if (!$evento) {
            $conexion->rollBack();
            return [
                'success' => false,
                'message' => 'Evento no encontrado',
                'data' => null
            ];
        }

        // Verificar que el evento no haya pasado (fecha >= fecha actual)
        $fecha_evento = new DateTime($evento['fecha']);
        $fecha_evento->setTime(0, 0, 0); // Resetear hora a medianoche para comparar solo fechas
        $fecha_actual = new DateTime();
        $fecha_actual->setTime(0, 0, 0); // Resetear hora a medianoche para comparar solo fechas
        
        if ($fecha_evento < $fecha_actual) {
            $conexion->rollBack();
            return [
                'success' => false,
                'message' => 'No se pueden comprar entradas para eventos pasados',
                'data' => null
            ];
        }

        // 2. Verificar disponibilidad de anticipadas
        // Se obtiene el total de entradas anticipadas vendidas para el evento
        // Si el total de entradas anticipadas vendidas es mayor o igual a la cantidad de entradas anticipadas disponibles 
        // se retorna un mensaje de error indicando que no hay más entradas anticipadas disponibles
        $sql_count = "SELECT COUNT(*) as vendidas FROM entradas WHERE id_evento = :id_evento AND id_tipo_entrada = 1";
        $stmt_count = $conexion->prepare($sql_count);
        $stmt_count->execute([':id_evento' => $id_evento]);
        $vendidas = $stmt_count->fetch(PDO::FETCH_ASSOC)['vendidas'];

        if ($vendidas >= $evento['cantidad_anticipadas']) {
            $conexion->rollBack();
            return [
                'success' => false,
                'message' => 'No hay más entradas anticipadas disponibles',
                'data' => null
            ];
        }

        // 3. Generar número de serie simple: ID_evento + número aleatorio
        $nro_serie = intval($id_evento . rand(10000, 99999));
        $id_usuario = $_SESSION['usuario_id'];

        // 4. Crear registro en tabla entradas
        $sql_entrada = "INSERT INTO entradas (nro_serie, id_usuario, id_evento, id_estado, id_tipo_entrada, precio) 
                       VALUES (:nro_serie, :id_usuario, :id_evento, 1, 1, :precio)";
        $stmt_entrada = $conexion->prepare($sql_entrada);
        $stmt_entrada->execute([
            ':nro_serie' => $nro_serie,
            ':id_usuario' => $id_usuario,
            ':id_evento' => $id_evento,
            ':precio' => $evento['precio_anticipadas']
        ]);
        $id_entrada = $conexion->lastInsertId();

        // 5. Crear registro en tabla ventas
        $sql_venta = "INSERT INTO ventas (fecha_venta, cantidad_entradas, monto_total, id_usuario) 
                     VALUES (NOW(), 1, :monto_total, :id_usuario)";
        $stmt_venta = $conexion->prepare($sql_venta);
        $stmt_venta->execute([
            ':monto_total' => $evento['precio_anticipadas'],
            ':id_usuario' => $id_usuario
        ]);
        $id_venta = $conexion->lastInsertId();

        // 6. Crear registro en tabla detalle_venta
        $sql_detalle = "INSERT INTO detalle_venta (id_venta, id_entrada) 
                       VALUES (:id_venta, :id_entrada)";
        $stmt_detalle = $conexion->prepare($sql_detalle);
        $stmt_detalle->execute([
            ':id_venta' => $id_venta,
            ':id_entrada' => $id_entrada
        ]);

        // Confirmar transacción
        $conexion->commit();

        // Obtener datos del usuario para el correo
        $sql_usuario = "SELECT nombre, apellido, correo FROM usuarios WHERE id = ?";
        $stmt_usuario = $conexion->prepare($sql_usuario);
        $stmt_usuario->execute([$id_usuario]);
        $usuario_datos = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

        // Enviar correo de confirmación
        $correo_enviado = false;
        if ($usuario_datos && !empty($usuario_datos['correo'])) {
            $correo_enviado = enviar_comprobante_compra($usuario_datos, $evento, $nro_serie, $evento['precio_anticipadas']);
            if (!$correo_enviado) {
                error_log("ADVERTENCIA: No se pudo enviar el correo de comprobante al usuario ID: " . $id_usuario . " Email: " . $usuario_datos['correo']);
            }
        } else {
            error_log("ADVERTENCIA: No se encontró correo para el usuario ID: " . $id_usuario);
        }

        // Mensaje de éxito (incluir información sobre el correo)
        $mensaje_exito = 'Compra realizada exitosamente';
        if (!$correo_enviado && $usuario_datos && !empty($usuario_datos['correo'])) {
            $mensaje_exito .= '. Nota: El comprobante por correo no pudo ser enviado, pero tu compra está confirmada.';
        }

        return [
            'success' => true,
            'message' => $mensaje_exito,
            'data' => [
                'nro_serie' => $nro_serie,
                'evento' => $evento['nombre'],
                'precio' => $evento['precio_anticipadas'],
                'id_venta' => $id_venta,
                'correo_enviado' => $correo_enviado
            ]
        ];
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("Error en procesar_compra_anticipada: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        $conexion->rollBack();
        error_log("Error general en procesar_compra_anticipada: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// Procesar compra de entrada anticipada (POST request desde formulario de pago)
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'comprar' && isset($_POST['id_evento'])) {
    $resultado = procesar_compra_anticipada($_POST['id_evento']);

    if ($resultado['success']) {
        $url_redirect = BASE_URL . 'index.php?page=catalogo&compra_exitosa=1&nro_serie=' . urlencode($resultado['data']['nro_serie']) . '&success=' . urlencode($resultado['message']);
        if (isset($resultado['data']['correo_enviado'])) {
            $url_redirect .= '&correo_enviado=' . ($resultado['data']['correo_enviado'] ? '1' : '0');
        }
        header('Location: ' . $url_redirect);
    } else {
        // Si el error es por falta de sesión, redirigir al login
        if (strpos($resultado['message'], 'iniciar sesión') !== false) {
            header('Location: ' . BASE_URL . 'index.php?page=login&error=' . urlencode($resultado['message']));
        } else {
            header('Location: ' . BASE_URL . 'index.php?page=pago&evento_id=' . intval($_POST['id_evento']) . '&error=' . urlencode($resultado['message']));
        }
    }
    exit;
}

// Procesar compra de entrada anticipada (GET request - método antiguo, mantener por compatibilidad)
if ($_GET && isset($_GET['action']) && $_GET['action'] === 'comprar' && isset($_GET['id_evento'])) {
    $resultado = procesar_compra_anticipada($_GET['id_evento']);

    if ($resultado['success']) {
        header('Location: ' . BASE_URL . 'index.php?page=catalogo&compra_exitosa=1&nro_serie=' . urlencode($resultado['data']['nro_serie']) . '&success=' . urlencode($resultado['message']));
    } else {
        // Si el error es por falta de sesión, redirigir al login
        if (strpos($resultado['message'], 'iniciar sesión') !== false) {
            header('Location: ' . BASE_URL . 'index.php?page=login&error=' . urlencode($resultado['message']));
        } else {
            header('Location: ' . BASE_URL . 'index.php?page=catalogo&error=' . urlencode($resultado['message']));
        }
    }
    exit;
}

// Función para actualizar una venta (monto_total y estados de entradas)
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update' && isset($_POST['venta_id'])) {
    try {
        $conexion = db_connection();
        
        // Validar datos requeridos
        if (!isset($_POST['monto_total'])) {
            header('Location: ' . BASE_URL . 'index.php?page=admin-ventas&error=true&message=' . urlencode('El monto total es requerido'));
            exit;
        }
        
        $venta_id = intval($_POST['venta_id']);
        $monto_total = floatval($_POST['monto_total']);
        
        // Validar que el monto sea mayor a 0
        if ($monto_total <= 0) {
            header('Location: ' . BASE_URL . 'index.php?page=admin-ventas&error=true&message=' . urlencode('El monto total debe ser mayor a 0'));
            exit;
        }
        
        // Verificar que la venta existe
        $sql_check = "SELECT id FROM ventas WHERE id = ?";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->execute([$venta_id]);
        
        if (!$stmt_check->fetch()) {
            header('Location: ' . BASE_URL . 'index.php?page=admin-ventas&error=true&message=' . urlencode('La venta no existe'));
            exit;
        }
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // Actualizar el monto_total de la venta
        $sql_update = "UPDATE ventas SET monto_total = ? WHERE id = ?";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->execute([$monto_total, $venta_id]);
        
        // Actualizar estados de las entradas si se enviaron
        if (isset($_POST['estados_entradas']) && is_array($_POST['estados_entradas'])) {
            $sql_update_entrada = "UPDATE entradas SET id_estado = ? WHERE id = ?";
            $stmt_update_entrada = $conexion->prepare($sql_update_entrada);
            
            foreach ($_POST['estados_entradas'] as $entrada_id => $nuevo_estado) {
                $entrada_id = intval($entrada_id);
                $nuevo_estado = intval($nuevo_estado);
                
                // Validar que el estado sea válido (1=vendida, 2=cancelada, 3=consumida)
                if ($nuevo_estado >= 1 && $nuevo_estado <= 3) {
                    // Verificar que la entrada pertenece a esta venta
                    $sql_verificar = "SELECT ent.id 
                                       FROM entradas ent 
                                       INNER JOIN detalle_venta dv ON ent.id = dv.id_entrada 
                                       WHERE ent.id = ? AND dv.id_venta = ?";
                    $stmt_verificar = $conexion->prepare($sql_verificar);
                    $stmt_verificar->execute([$entrada_id, $venta_id]);
                    
                    if ($stmt_verificar->fetch()) {
                        // Actualizar el estado de la entrada
                        $stmt_update_entrada->execute([$nuevo_estado, $entrada_id]);
                    }
                }
            }
        }
        
        // Confirmar transacción
        $conexion->commit();
        
        // Redirigir con mensaje de éxito
        header('Location: ' . BASE_URL . 'index.php?page=admin-ventas&success=true&message=' . urlencode('Venta y estados de entradas actualizados correctamente'));
        exit;
        
    } catch (PDOException $e) {
        if (isset($conexion)) {
            $conexion->rollBack();
        }
        error_log("Error en actualizar venta: " . $e->getMessage());
        header('Location: ' . BASE_URL . 'index.php?page=admin-ventas&error=true&message=' . urlencode('Error al actualizar la venta: ' . $e->getMessage()));
        exit;
    } catch (Exception $e) {
        if (isset($conexion)) {
            $conexion->rollBack();
        }
        error_log("Error general en actualizar venta: " . $e->getMessage());
        header('Location: ' . BASE_URL . 'index.php?page=admin-ventas&error=true&message=' . urlencode('Error al actualizar la venta'));
        exit;
    }
}

// Función para exportar informe de ventas (solo admin)
if ($_GET && isset($_GET['action']) && $_GET['action'] === 'exportar_informe') {
    // Verificar que el usuario esté logueado y sea admin
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 1) {
        header('Location: ' . BASE_URL . 'index.php?page=admin-ventas&error=true&message=' . urlencode('No tienes permisos para acceder a esta función'));
        exit;
    }
    
    try {
        $conexion = db_connection();
        
        // Consulta SQL para obtener todas las ventas con sus detalles
        $sql = "SELECT 
            v.id as id_venta,
            v.fecha_venta,
            v.cantidad_entradas,
            v.monto_total,
            u.dni as usuario_dni,
            u.nombre as usuario_nombre,
            u.apellido as usuario_apellido,
            u.correo as usuario_correo,
            e.nombre as evento_nombre,
            e.fecha as evento_fecha,
            ent.nro_serie,
            ent.precio as precio_entrada,
            est.nombre as estado_nombre
        FROM ventas v
        INNER JOIN usuarios u ON v.id_usuario = u.id
        INNER JOIN detalle_venta dv ON v.id = dv.id_venta
        INNER JOIN entradas ent ON dv.id_entrada = ent.id
        INNER JOIN eventos e ON ent.id_evento = e.id
        INNER JOIN estados est ON ent.id_estado = est.id
        ORDER BY v.fecha_venta DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $ventas_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Agrupar ventas y calcular estados
        $ventas_agrupadas = [];
        foreach ($ventas_raw as $venta) {
            $venta_id = $venta['id_venta'];
            
            if (!isset($ventas_agrupadas[$venta_id])) {
                $ventas_agrupadas[$venta_id] = $venta;
                $ventas_agrupadas[$venta_id]['entradas'] = [];
            }
            
            $ventas_agrupadas[$venta_id]['entradas'][] = [
                'nro_serie' => $venta['nro_serie'],
                'precio' => $venta['precio_entrada'],
                'estado' => ucfirst($venta['estado_nombre'])
            ];
        }
        
        // Preparar datos para CSV
        $datos_csv = [];
        foreach ($ventas_agrupadas as $venta) {
            // Calcular estado principal
            $estados = array_column($venta['entradas'], 'estado');
            $estado_principal = (count(array_unique($estados)) == 1) ? $estados[0] : 'Mixto';
            
            // Formatear números de serie
            $numeros_serie = array_column($venta['entradas'], 'nro_serie');
            $nro_serie_str = implode(', ', $numeros_serie);
            
            $datos_csv[] = [
                'ID Venta' => $venta['id_venta'],
                'Fecha Venta' => date('d/m/Y H:i', strtotime($venta['fecha_venta'])),
                'Cliente' => $venta['usuario_nombre'] . ' ' . $venta['usuario_apellido'],
                'DNI' => $venta['usuario_dni'],
                'Correo' => $venta['usuario_correo'],
                'Evento' => $venta['evento_nombre'],
                'Fecha Evento' => date('d/m/Y', strtotime($venta['evento_fecha'])),
                'Cantidad Entradas' => $venta['cantidad_entradas'],
                'Números de Serie' => $nro_serie_str,
                'Estado' => $estado_principal,
                'Monto Total' => number_format($venta['monto_total'], 2, ',', '.')
            ];
        }
        
        // Generar nombre del archivo con fecha
        $nombre_archivo = 'informe_ventas_' . date('Y-m-d_His') . '.csv';
        
        // Limpiar cualquier output anterior
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Configurar headers para descarga compatible con Excel
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');
        
        // Abrir output stream
        $output = fopen('php://output', 'w');
        
        // Agregar BOM para UTF-8 (para Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escribir encabezados
        if (!empty($datos_csv)) {
            // Escribir encabezados manualmente para mejor control
            $encabezados = array_keys($datos_csv[0]);
            fputcsv($output, $encabezados, ';', '"');
            
            // Escribir datos
            foreach ($datos_csv as $fila) {
                // Escapar campos que puedan tener problemas
                $fila_escapada = array_map(function($campo) {
                    // Si el campo contiene punto y coma o comillas, escapar correctamente
                    return $campo;
                }, $fila);
                fputcsv($output, $fila_escapada, ';', '"');
            }
        } else {
            // Si no hay datos, escribir al menos los encabezados
            $encabezados = ['ID Venta', 'Fecha Venta', 'Cliente', 'DNI', 'Correo', 'Evento', 'Fecha Evento', 'Cantidad Entradas', 'Números de Serie', 'Estado', 'Monto Total'];
            fputcsv($output, $encabezados, ';', '"');
        }
        
        fclose($output);
        exit;
        
    } catch (PDOException $e) {
        error_log("Error en exportar_informe: " . $e->getMessage());
        header('Location: ' . BASE_URL . 'index.php?page=admin-ventas&error=true&message=' . urlencode('Error al generar el informe'));
        exit;
    } catch (Exception $e) {
        error_log("Error general en exportar_informe: " . $e->getMessage());
        header('Location: ' . BASE_URL . 'index.php?page=admin-ventas&error=true&message=' . urlencode('Error al generar el informe'));
        exit;
    }
}

// Función para eliminar una venta
if ($_GET && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $conexion = db_connection();
        $id_venta = $_GET['id'];

        // Iniciar transacción
        $conexion->beginTransaction();

        // Primero eliminar los registros en detalle_venta
        $sql_detalle = "DELETE FROM detalle_venta WHERE id_venta = ?";
        $stmt = $conexion->prepare($sql_detalle);
        $stmt->execute([$id_venta]);

        // Luego eliminar la venta
        $sql_venta = "DELETE FROM ventas WHERE id = ?";
        $stmt = $conexion->prepare($sql_venta);
        $stmt->execute([$id_venta]);

        // Confirmar transacción
        $conexion->commit();

        // Redirigir con mensaje de éxito
        header('Location: ' . BASE_URL . 'index.php?page=admin-ventas&success=true&message=' . urlencode('Venta eliminada correctamente'));
        exit;
    } catch (Exception $e) {
        if (isset($conexion)) {
            $conexion->rollBack();
        }
        // Redirigir con mensaje de error
        header('Location: ' . BASE_URL . 'index.php?page=admin-ventas&error=true&message=' . urlencode($e->getMessage()));
        exit;
    }
}
