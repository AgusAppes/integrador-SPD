<?php
/**
 * MÉTODOS PARA PANEL DE RECEPCIÓN
 * Maneja verificación de entradas, registro de asistencias y ventas directas
 */

require_once '../config/database.php';

// Función para verificar si existe una entrada válida para un DNI y evento
function verificar_entrada($dni, $evento_id) {
    try {
        $conexion = db_connection();
        
        // PASO 1: Buscar usuario por DNI en la tabla usuarios
        $sql_usuario = "SELECT id, nombre, apellido, dni, correo 
                       FROM usuarios 
                       WHERE dni = :dni";
        
        $stmt_usuario = $conexion->prepare($sql_usuario);
        $stmt_usuario->execute([':dni' => $dni]);
        $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            return [
                'success' => false,
                'message' => 'Usuario no registrado en el sistema',
                'suggestion' => 'El DNI no está registrado. Considerar registro manual.',
                'user_data' => null
            ];
        }
        
        // PASO 2: Buscar entrada válida usando el ID del usuario
        $sql = "SELECT e.*, ev.nombre as evento_nombre, te.nombre as tipo_entrada, es.nombre as estado_nombre,
                       u.nombre as usuario_nombre, u.apellido as usuario_apellido
                FROM entradas e
                INNER JOIN eventos ev ON e.id_evento = ev.id
                INNER JOIN tipo_entrada te ON e.id_tipo_entrada = te.id  
                INNER JOIN estados es ON e.id_estado = es.id
                INNER JOIN usuarios u ON e.id_usuario = u.id
                WHERE e.id_usuario = :usuario_id AND e.id_evento = :evento_id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuario['id'],
            ':evento_id' => $evento_id
        ]);
        
        $entrada = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($entrada) {
            // Verificar estado de la entrada
            switch ($entrada['id_estado']) {
                case 1: // Activa/Válida
                    return [
                        'success' => true,
                        'message' => 'Entrada válida - Puede ingresar',
                        'user_data' => [
                            'nombre' => $usuario['nombre'],
                            'apellido' => $usuario['apellido'],
                            'dni' => $usuario['dni'],
                            'nombre_completo' => $usuario['nombre'] . ' ' . $usuario['apellido']
                        ],
                        'ticket_info' => [
                            'evento' => $entrada['evento_nombre'],
                            'tipo' => $entrada['tipo_entrada'],
                            'estado' => $entrada['estado_nombre'],
                            'precio' => $entrada['precio']
                        ],
                        'entrada_id' => $entrada['id']
                    ];
                    
                case 2: // Ya utilizada
                    // Verificar si ya está registrada la asistencia
                    $sql_asistencia = "SELECT hora_ingreso FROM detalle_asistencias WHERE id_entrada = :entrada_id";
                    $stmt_asist = $conexion->prepare($sql_asistencia);
                    $stmt_asist->execute([':entrada_id' => $entrada['id']]);
                    $asistencia = $stmt_asist->fetch();
                    
                    if ($asistencia) {
                        return [
                            'success' => false,
                            'message' => 'Esta entrada ya fue utilizada para ingresar',
                            'user_data' => [
                                'nombre' => $usuario['nombre'],
                                'apellido' => $usuario['apellido'],
                                'dni' => $usuario['dni'],
                                'nombre_completo' => $usuario['nombre'] . ' ' . $usuario['apellido']
                            ],
                            'ticket_info' => [
                                'evento' => $entrada['evento_nombre'],
                                'tipo' => $entrada['tipo_entrada'],
                                'estado' => 'YA UTILIZADA',
                                'ingreso' => $asistencia['hora_ingreso']
                            ]
                        ];
                    } else {
                        // Entrada marcada como utilizada pero sin registro de asistencia
                        return [
                            'success' => true,
                            'message' => 'Entrada válida - Puede ingresar',
                            'user_data' => [
                                'nombre' => $usuario['nombre'],
                                'apellido' => $usuario['apellido'],
                                'dni' => $usuario['dni'],
                                'nombre_completo' => $usuario['nombre'] . ' ' . $usuario['apellido']
                            ],
                            'ticket_info' => [
                                'evento' => $entrada['evento_nombre'],
                                'tipo' => $entrada['tipo_entrada'],
                                'estado' => $entrada['estado_nombre']
                            ],
                            'entrada_id' => $entrada['id']
                        ];
                    }
                    
                case 3: // Cancelada
                    return [
                        'success' => false,
                        'message' => 'Esta entrada ha sido cancelada',
                        'ticket_info' => [
                            'evento' => $entrada['evento_nombre'],
                            'tipo' => $entrada['tipo_entrada'],
                            'estado' => 'CANCELADA'
                        ]
                    ];
                    
                default:
                    return [
                        'success' => false,
                        'message' => 'Estado de entrada no válido',
                        'ticket_info' => [
                            'evento' => $entrada['evento_nombre'],
                            'estado' => $entrada['estado_nombre']
                        ]
                    ];
            }
        } else {
            // Usuario existe pero no tiene entrada para este evento
            return [
                'success' => false,
                'message' => 'No tiene entrada para este evento',
                'user_data' => [
                    'nombre' => $usuario['nombre'],
                    'apellido' => $usuario['apellido'],
                    'dni' => $usuario['dni'],
                    'nombre_completo' => $usuario['nombre'] . ' ' . $usuario['apellido']
                ],
                'suggestion' => 'Usuario registrado pero sin entrada. Considerar venta directa.'
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error en verificar_entrada: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del sistema',
            'error' => $e->getMessage()
        ];
    }
}

// Función para registrar ingreso de un asistente
function registrar_ingreso($entrada_id, $dni) {
    try {
        $conexion = db_connection();
        $conexion->beginTransaction();
        
        // Verificar que la entrada exista y esté válida
        $sql_entrada = "SELECT id, id_estado FROM entradas WHERE id = :entrada_id";
        $stmt_entrada = $conexion->prepare($sql_entrada);
        $stmt_entrada->execute([':entrada_id' => $entrada_id]);
        $entrada = $stmt_entrada->fetch();
        
        if (!$entrada || $entrada['id_estado'] != 1) {
            $conexion->rollback();
            return [
                'success' => false,
                'message' => 'Entrada no válida para registro de ingreso'
            ];
        }
        
        // Verificar si ya existe registro de asistencia
        $sql_check = "SELECT id FROM detalle_asistencias WHERE id_entrada = :entrada_id";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->execute([':entrada_id' => $entrada_id]);
        
        if ($stmt_check->fetch()) {
            $conexion->rollback();
            return [
                'success' => false,
                'message' => 'El ingreso ya fue registrado anteriormente'
            ];
        }
        
        // Registrar asistencia
        $sql_asistencia = "INSERT INTO detalle_asistencias (id_entrada, hora_ingreso) VALUES (:entrada_id, NOW())";
        $stmt_asistencia = $conexion->prepare($sql_asistencia);
        $stmt_asistencia->execute([':entrada_id' => $entrada_id]);
        
        // Actualizar estado de entrada a "utilizada" (id = 2)
        $sql_update = "UPDATE entradas SET id_estado = 2 WHERE id = :entrada_id";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->execute([':entrada_id' => $entrada_id]);
        
        $conexion->commit();
        
        return [
            'success' => true,
            'message' => 'Ingreso registrado exitosamente',
            'timestamp' => date('H:i:s')
        ];
        
    } catch (PDOException $e) {
        $conexion->rollback();
        error_log("Error en registrar_ingreso: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al registrar ingreso'
        ];
    }
}

// Procesar solicitudes AJAX
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['action'])) {
        echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
        exit;
    }
    
    switch ($input['action']) {
        case 'verify_entry':
            if (!isset($input['dni']) || !isset($input['event_id'])) {
                echo json_encode(['success' => false, 'message' => 'Parámetros faltantes']);
                exit;
            }
            
            $dni = trim($input['dni']);
            $event_id = intval($input['event_id']);
            
            if (empty($dni) || $event_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
                exit;
            }
            
            $resultado = verificar_entrada($dni, $event_id);
            echo json_encode($resultado);
            break;
            
        case 'register_entry':
            if (!isset($input['entrada_id']) || !isset($input['dni'])) {
                echo json_encode(['success' => false, 'message' => 'Parámetros faltantes']);
                exit;
            }
            
            $entrada_id = intval($input['entrada_id']);
            $dni = trim($input['dni']);
            
            if ($entrada_id <= 0 || empty($dni)) {
                echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
                exit;
            }
            
            $resultado = registrar_ingreso($entrada_id, $dni);
            echo json_encode($resultado);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
