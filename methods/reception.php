<?php
// Incluir configuraciones de base de datos
require_once __DIR__ . '/../config/database.php';

// Función para procesar el escaneo de DNI
function processDNIScan($dniData, $eventId = null) {
    try {
        $pdo = db_connection();
        
        // Extraer el DNI del código de barras
        $dni = extractDNIFromBarcode($dniData);
        
        if (!$dni) {
            return [
                'success' => false,
                'message' => 'No se pudo extraer el DNI del código de barras'
            ];
        }
        
        // Buscar si el usuario ya existe
        $existingUser = findUserByDNI($dni);
        
        if ($existingUser) {
            // Usuario existe, verificar entrada si se proporciona eventId
            $ticketInfo = null;
            if ($eventId) {
                $ticketInfo = checkUserTicketForEvent($existingUser['id'], $eventId);
            }
            
            return [
                'success' => true,
                'message' => 'Usuario encontrado',
                'user' => $existingUser,
                'action' => 'found',
                'ticket' => $ticketInfo
            ];
        } else {
            // Usuario no existe, crear uno nuevo
            $newUser = createUserFromDNI($dni, $dniData);
            
            if ($newUser) {
                // Verificar entrada para el nuevo usuario si se proporciona eventId
                $ticketInfo = null;
                if ($eventId) {
                    $ticketInfo = checkUserTicketForEvent($newUser['id'], $eventId);
                }
                
                return [
                    'success' => true,
                    'message' => 'Usuario creado exitosamente',
                    'user' => $newUser,
                    'action' => 'created',
                    'ticket' => $ticketInfo
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el usuario'
                ];
            }
        }
        
    } catch (Exception $e) {
        error_log("Error en processDNIScan: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor'
        ];
    }
}

// Función para extraer DNI del código de barras
function extractDNIFromBarcode($barcodeData) {
    if (!$barcodeData || !is_string($barcodeData)) {
        return null;
    }
    
    // Normalizar comillas
    $quotes = array(
        "\u201C", "\u201D", "\u201E", "\u201F", "\u2018", "\u2019"
    );
    $normalized = str_replace($quotes, '"', $barcodeData);
    $normalized = trim($normalized);
    
    // Dividir por comillas y filtrar vacíos
    $parts = array_filter(array_map('trim', explode('"', $normalized)), function($part) {
        return !empty($part);
    });
    
    // Buscar DNI en los segmentos
    $genders = ['M', 'F', 'O'];
    $dni = null;
    
    // Buscar por patrón: género seguido de números
    for ($i = 0; $i < count($parts); $i++) {
        $part = strtoupper($parts[$i]);
        if (in_array($part, $genders) && $i + 1 < count($parts)) {
            $possibleDni = preg_replace('/\D/', '', $parts[$i + 1]);
            if (strlen($possibleDni) >= 6 && strlen($possibleDni) <= 9) {
                $dni = $possibleDni;
                break;
            }
        }
    }
    
    // Fallback: buscar cualquier secuencia de 6-9 dígitos
    if (!$dni) {
        preg_match_all('/\d{6,9}/', $normalized, $matches);
        if (!empty($matches[0])) {
            $dni = $matches[0][0];
        }
    }
    
    return $dni;
}

// Función para buscar usuario por DNI
function findUserByDNI($dni) {
    try {
        $pdo = db_connection();
        
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE dni = ?");
        $stmt->execute([$dni]);
        
        return $stmt->fetch();
        
    } catch (Exception $e) {
        error_log("Error en findUserByDNI: " . $e->getMessage());
        return false;
    }
}

// Función para crear usuario desde datos del DNI
function createUserFromDNI($dni, $barcodeData) {
    try {
        $pdo = db_connection();
        
        // Extraer datos adicionales del código de barras
        $userData = extractUserDataFromBarcode($barcodeData);
        
        // Preparar datos para inserción
        $nombre = $userData['nombre'] ?? 'Usuario';
        $apellido = $userData['apellido'] ?? 'Sin Apellido';
        $fechaNac = $userData['fecha_nac'] ?? '1990-01-01';
        $correo = 'sinconfigurar@mail.com';
        $contraseña = password_hash('123456', PASSWORD_DEFAULT); // Contraseña por defecto
        $idRol = 2; // Rol por defecto
        
        // Insertar nuevo usuario
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (dni, nombre, apellido, fecha_nac, correo, contraseña, id_rol) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $dni,
            $nombre,
            $apellido,
            $fechaNac,
            $correo,
            $contraseña,
            $idRol
        ]);
        
        if ($result) {
            // Obtener el usuario recién creado
            $userId = $pdo->lastInsertId();
            return findUserByDNI($dni);
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Error en createUserFromDNI: " . $e->getMessage());
        return false;
    }
}

// Función para extraer datos del usuario del código de barras
function extractUserDataFromBarcode($barcodeData) {
    $quotes = array(
        "\u201C", "\u201D", "\u201E", "\u201F", "\u2018", "\u2019"
    );
    $normalized = str_replace($quotes, '"', $barcodeData);
    $parts = array_filter(array_map('trim', explode('"', $normalized)), function($part) {
        return !empty($part);
    });
    
    $userData = [];
    $genders = ['M', 'F', 'O'];
    
    // Buscar nombre y apellido
    for ($i = 0; $i < count($parts); $i++) {
        $part = strtoupper($parts[$i]);
        if (in_array($part, $genders)) {
            // El nombre suele estar antes del género
            if ($i > 0) {
                $userData['nombre'] = $parts[$i - 1];
            }
            // El apellido suele estar en la segunda posición
            if (count($parts) > 1) {
                $userData['apellido'] = $parts[1];
            }
            break;
        }
    }
    
    // Buscar fecha de nacimiento (formato DD-MM-YYYY)
    foreach ($parts as $part) {
        if (preg_match('/\d{2}-\d{2}-\d{4}/', $part)) {
            $dateParts = explode('-', $part);
            $userData['fecha_nac'] = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
            break;
        }
    }
    
    return $userData;
}

// Función para verificar si el usuario tiene entrada válida para el evento
function checkUserTicketForEvent($userId, $eventId) {
    try {
        $pdo = db_connection();
        
        // Buscar cualquier entrada del usuario para el evento (cualquier estado)
        $stmt = $pdo->prepare("
            SELECT 
                e.id,
                e.nro_serie,
                e.precio,
                e.id_estado,
                es.nombre as estado_nombre,
                te.nombre as tipo_entrada_nombre,
                ev.nombre as evento_nombre
            FROM entradas e
            INNER JOIN estados es ON e.id_estado = es.id
            INNER JOIN tipo_entrada te ON e.id_tipo_entrada = te.id
            INNER JOIN eventos ev ON e.id_evento = ev.id
            WHERE e.id_usuario = ? 
            AND e.id_evento = ?
            ORDER BY e.id DESC
            LIMIT 1
        ");
        
        $stmt->execute([$userId, $eventId]);
        $ticket = $stmt->fetch();
        
        if ($ticket) {
            // Determinar si la entrada es válida según el estado
            $isValid = ($ticket['id_estado'] == 1); // Solo vendida (1) es válida para ingreso
            
            return [
                'has_ticket' => $isValid,
                'ticket' => $ticket,
                'message' => $isValid ? 'Entrada válida encontrada' : 'Entrada encontrada (Estado: ' . $ticket['estado_nombre'] . ')',
                'ticket_state' => $ticket['id_estado']
            ];
        } else {
            return [
                'has_ticket' => false,
                'ticket' => null,
                'message' => 'No tiene entrada para este evento',
                'ticket_state' => null
            ];
        }
        
    } catch (Exception $e) {
        error_log("Error en checkUserTicketForEvent: " . $e->getMessage());
        return [
            'has_ticket' => false,
            'ticket' => null,
            'message' => 'Error al verificar entrada'
        ];
    }
}

// Función para registrar ingreso
function registerEntry($userId, $ticketId, $eventId) {
    try {
        $pdo = db_connection();
        
        // Verificar si la persona ya está dentro (tiene hora_ingreso pero no hora_egreso)
        $stmt = $pdo->prepare("
            SELECT da.id 
            FROM detalle_asistencias da
            INNER JOIN entradas e ON da.id_entrada = e.id
            WHERE e.id_usuario = ? 
            AND e.id_evento = ? 
            AND da.hora_ingreso IS NOT NULL 
            AND da.hora_egreso IS NULL
        ");
        
        $stmt->execute([$userId, $eventId]);
        $existingEntry = $stmt->fetch();
        
        if ($existingEntry) {
            return [
                'success' => false,
                'message' => 'La persona ya está dentro del local'
            ];
        }
        
        // Verificar capacidad del evento
        $capacityResult = checkEventCapacity($eventId);
        if (!$capacityResult['success']) {
            return $capacityResult;
        }
        
        if ($capacityResult['current_capacity'] >= $capacityResult['max_capacity']) {
            return [
                'success' => false,
                'message' => 'El local está al máximo de su capacidad'
            ];
        }
        
        // Registrar el ingreso
        $stmt = $pdo->prepare("
            INSERT INTO detalle_asistencias (id_entrada, hora_ingreso) 
            VALUES (?, NOW())
        ");
        
        $result = $stmt->execute([$ticketId]);
        
        if ($result) {
            // Actualizar estado de la entrada a "consumida" (estado 3)
            $stmt2 = $pdo->prepare("UPDATE entradas SET id_estado = 3 WHERE id = ?");
            $stmt2->execute([$ticketId]);
            
            // Obtener capacidad actualizada
            $updatedCapacity = checkEventCapacity($eventId);
            
            return [
                'success' => true,
                'message' => 'Ingreso registrado exitosamente',
                'capacity' => $updatedCapacity
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al registrar el ingreso'
        ];
        
    } catch (Exception $e) {
        error_log("Error en registerEntry: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor'
        ];
    }
}

// Función para registrar egreso
function registerExit($userId, $ticketId, $eventId) {
    try {
        $pdo = db_connection();
        
        // Buscar el registro de ingreso activo (con hora_ingreso pero sin hora_egreso)
        $stmt = $pdo->prepare("
            SELECT da.id 
            FROM detalle_asistencias da
            INNER JOIN entradas e ON da.id_entrada = e.id
            WHERE e.id_usuario = ? 
            AND e.id_evento = ? 
            AND da.hora_ingreso IS NOT NULL 
            AND da.hora_egreso IS NULL
        ");
        
        $stmt->execute([$userId, $eventId]);
        $activeEntry = $stmt->fetch();
        
        if (!$activeEntry) {
            return [
                'success' => false,
                'message' => 'La persona no está registrada como dentro del local'
            ];
        }
        
        // Registrar el egreso
        $stmt = $pdo->prepare("
            UPDATE detalle_asistencias 
            SET hora_egreso = NOW() 
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$activeEntry['id']]);
        
        if ($result) {
            // Actualizar estado de la entrada a "vendida" (estado 1) para permitir reingreso
            $stmt2 = $pdo->prepare("UPDATE entradas SET id_estado = 1 WHERE id = ?");
            $stmt2->execute([$ticketId]);
            
            // Obtener capacidad actualizada
            $updatedCapacity = checkEventCapacity($eventId);
            
            return [
                'success' => true,
                'message' => 'Egreso registrado exitosamente',
                'capacity' => $updatedCapacity
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al registrar el egreso'
        ];
        
    } catch (Exception $e) {
        error_log("Error en registerExit: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor'
        ];
    }
}

// Función para verificar capacidad del evento
function checkEventCapacity($eventId) {
    try {
        $pdo = db_connection();
        
        // Obtener capacidad máxima del evento
        $stmt = $pdo->prepare("SELECT cupo_total FROM eventos WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch();
        
        if (!$event) {
            return [
                'success' => false,
                'message' => 'Evento no encontrado'
            ];
        }
        
        // Contar personas actualmente dentro (con hora_ingreso pero sin hora_egreso)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as current_capacity
            FROM detalle_asistencias da
            INNER JOIN entradas e ON da.id_entrada = e.id
            WHERE e.id_evento = ? 
            AND da.hora_ingreso IS NOT NULL 
            AND da.hora_egreso IS NULL
        ");
        
        $stmt->execute([$eventId]);
        $capacity = $stmt->fetch();
        
        return [
            'success' => true,
            'current_capacity' => (int)$capacity['current_capacity'],
            'max_capacity' => (int)$event['cupo_total'],
            'available_capacity' => (int)$event['cupo_total'] - (int)$capacity['current_capacity']
        ];
        
    } catch (Exception $e) {
        error_log("Error en checkEventCapacity: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al verificar capacidad'
        ];
    }
}

// Manejar peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'scan_dni':
                    $dniData = $input['dni_data'] ?? '';
                    $eventId = $input['event_id'] ?? null;
                    $result = processDNIScan($dniData, $eventId);
                    echo json_encode($result);
                    exit;
                    
                case 'register_entry':
                    $userId = $input['user_id'] ?? null;
                    $ticketId = $input['ticket_id'] ?? null;
                    $eventId = $input['event_id'] ?? null;
                    $result = registerEntry($userId, $ticketId, $eventId);
                    echo json_encode($result);
                    exit;
                    
                case 'register_exit':
                    $userId = $input['user_id'] ?? null;
                    $ticketId = $input['ticket_id'] ?? null;
                    $eventId = $input['event_id'] ?? null;
                    $result = registerExit($userId, $ticketId, $eventId);
                    echo json_encode($result);
                    exit;
                    
                case 'check_capacity':
                    $eventId = $input['event_id'] ?? null;
                    $result = checkEventCapacity($eventId);
                    echo json_encode($result);
                    exit;
            }
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
        exit;
        
    } catch (Exception $e) {
        error_log("Error en reception.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error interno: ' . $e->getMessage()
        ]);
        exit;
    }
}

// Si no es POST, devolver error
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Método no permitido'
]);
?>