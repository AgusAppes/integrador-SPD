<?php
//configuración de base de datos
require_once '../config/database.php';


// Función para procesar la imagen
function procesar_imagen($archivo) {
    // Crear directorio si no existe
    $directorio = '../images/eventos/';
    if (!file_exists($directorio)) {
        mkdir($directorio, 0755, true);
    }
    
    // Validar tipo de archivo
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($archivo['type'], $tipos_permitidos)) {
        return false;
    }
    
    // Validar tamaño (5MB máximo)
    $tamaño_maximo = 5 * 1024 * 1024; // 5MB en bytes
    if ($archivo['size'] > $tamaño_maximo) {
        return false;
    }
    
    // Generar nombre único para el archivo
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
    $ruta_completa = $directorio . $nombre_archivo;
    
    // Mover archivo al directorio
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        return 'images/eventos/' . $nombre_archivo; // Ruta relativa para la BD
    }
    
    return false;
}


// Función para crear un nuevo evento
function crear_evento($datos) {
    try {
        $conexion = db_connection();
        
        // Validar datos requeridos
        $campos_requeridos = ['nombre', 'descripcion', 'fecha', 'cupo_total', 'cantidad_anticipadas', 'precio_anticipadas', 'precio_en_puerta'];
        foreach ($campos_requeridos as $campo) {
            if (!isset($datos[$campo]) || empty(trim($datos[$campo]))) {
                return [
                    'success' => false,
                    'message' => "El campo '$campo' es requerido",
                    'data' => null
                ];
            }
        }
        
        // Validar y procesar imagen
        $banner_path = null;
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $banner_path = procesar_imagen($_FILES['banner']);
            if (!$banner_path) {
                return [
                    'success' => false,
                    'message' => 'Error al procesar la imagen',
                    'data' => null
                ];
            }
        }
        
        // Validar que el cupo sea un número positivo
        if (!is_numeric($datos['cupo_total']) || $datos['cupo_total'] <= 0) {
            return [
                'success' => false,
                'message' => 'El cupo total debe ser un número positivo',
                'data' => null
            ];
        }

        // validar que la cantidad de anticipadas sea un número positivo y sea menor o igual al cupo total
        if (!is_numeric($datos['cantidad_anticipadas']) || $datos['cantidad_anticipadas'] <= 0 || $datos['cantidad_anticipadas'] > $datos['cupo_total']) {
            return [
                'success' => false,
                'message' => 'La cantidad de anticipadas debe ser un número positivo y menor o igual al cupo total',
                'data' => null
            ];
        }
        
        // Validar que los precios sean números positivos
        if (!is_numeric($datos['precio_anticipadas']) || $datos['precio_anticipadas'] < 0) {
            return [
                'success' => false,
                'message' => 'El precio de entradas anticipadas debe ser un número válido',
                'data' => null
            ];
        }
        
        if (!is_numeric($datos['precio_en_puerta']) || $datos['precio_en_puerta'] < 0) {
            return [
                'success' => false,
                'message' => 'El precio en puerta debe ser un número válido',
                'data' => null
            ];
        }
        
        // Validar formato de fecha
        $fecha = DateTime::createFromFormat('Y-m-d', $datos['fecha']);
        if (!$fecha) {
            return [
                'success' => false,
                'message' => 'La fecha debe tener el formato YYYY-MM-DD',
                'data' => null
            ];
        }
        
        // Preparar consulta SQL
        $sql = "INSERT INTO eventos (nombre, descripcion, fecha, cupo_total, cantidad_anticipadas, precio_anticipadas, precio_en_puerta, banner, id_usuario) 
                VALUES (:nombre, :descripcion, :fecha, :cupo_total, :cantidad_anticipadas, :precio_anticipadas, :precio_en_puerta, :banner, :id_usuario)";
        
        $stmt = $conexion->prepare($sql);
        
        // Ejecutar consulta
        $resultado = $stmt->execute([
            ':nombre' => trim($datos['nombre']),
            ':descripcion' => trim($datos['descripcion']),
            ':fecha' => $datos['fecha'],
            ':cupo_total' => (int)$datos['cupo_total'],
            ':cantidad_anticipadas' => (int)$datos['cantidad_anticipadas'],
            ':precio_anticipadas' => (float)$datos['precio_anticipadas'],
            ':precio_en_puerta' => (float)$datos['precio_en_puerta'],
            ':banner' => $banner_path,
            ':id_usuario' => isset($datos['id_usuario']) ? (int)$datos['id_usuario'] : 1 // Por defecto usuario 1
        ]);
        
        if ($resultado) {
            // obtener el id del evento creado
            $evento_id = $conexion->lastInsertId();
            return [
                'success' => true,
                'message' => 'Evento creado exitosamente',
                'data' => [
                    'id' => $evento_id,
                    'nombre' => trim($datos['nombre']),
                    'fecha' => $datos['fecha']
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al crear el evento',
                'data' => null
            ];
        }
    // manejo de errores (no dar bolilla por ahora)
    } catch (PDOException $e) {
        error_log("Error en crear_evento: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        error_log("Error general en crear_evento: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// Función para listar todos los eventos
function listar_eventos() {
    try {
        $conexion = db_connection();
        
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


// Función para obtener un evento por ID
function obtener_evento($id) {
    try {
        $conexion = db_connection();
        
        $sql = "SELECT id, nombre, descripcion, fecha, cupo_total, cantidad_anticipadas, precio_anticipadas, precio_en_puerta, banner 
                FROM eventos 
                WHERE id = :id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($evento) {
            return [
                'success' => true,
                'message' => 'Evento obtenido exitosamente',
                'data' => $evento
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Evento no encontrado',
                'data' => null
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error en obtener_evento: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    }
}

// Función para actualizar un evento
function actualizar_evento($datos) {
    try {
        $conexion = db_connection();
        
        // Validar datos requeridos
        $campos_requeridos = ['evento_id', 'nombre', 'descripcion', 'fecha', 'cupo_total', 'cantidad_anticipadas', 'precio_anticipadas', 'precio_en_puerta'];
        foreach ($campos_requeridos as $campo) {
            if (!isset($datos[$campo]) || empty(trim($datos[$campo]))) {
                return [
                    'success' => false,
                    'message' => "El campo '$campo' es requerido",
                    'data' => null
                ];
            }
        }
        
        // Validar que el evento exista
        $evento_existente = obtener_evento($datos['evento_id']);
        if (!$evento_existente['success']) {
            return $evento_existente;
        }
        
        // Obtener datos actuales del evento
        $evento_actual = $evento_existente['data'];
        if ($evento_actual) {
            $banner_path = $evento_actual['banner'];
        }
        
        // Procesar nueva imagen si se subió
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $nueva_imagen = procesar_imagen($_FILES['banner']);
            if ($nueva_imagen) {
                // Eliminar imagen anterior si existe
                if ($banner_path && file_exists('../' . $banner_path)) {
                    unlink('../' . $banner_path);
                }
                $banner_path = $nueva_imagen;
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al procesar la nueva imagen',
                    'data' => null
                ];
            }
        }
        
        // Validaciones de datos
        if (!is_numeric($datos['cupo_total']) || $datos['cupo_total'] <= 0) {
            return [
                'success' => false,
                'message' => 'El cupo total debe ser un número positivo',
                'data' => null
            ];
        }

        if (!is_numeric($datos['cantidad_anticipadas']) || $datos['cantidad_anticipadas'] <= 0 || $datos['cantidad_anticipadas'] > $datos['cupo_total']) {
            return [
                'success' => false,
                'message' => 'La cantidad de anticipadas debe ser un número positivo y menor o igual al cupo total',
                'data' => null
            ];
        }
        
        if (!is_numeric($datos['precio_anticipadas']) || $datos['precio_anticipadas'] < 0) {
            return [
                'success' => false,
                'message' => 'El precio de entradas anticipadas debe ser un número válido',
                'data' => null
            ];
        }
        
        if (!is_numeric($datos['precio_en_puerta']) || $datos['precio_en_puerta'] < 0) {
            return [
                'success' => false,
                'message' => 'El precio en puerta debe ser un número válido',
                'data' => null
            ];
        }
        
        $fecha = DateTime::createFromFormat('Y-m-d', $datos['fecha']);
        if (!$fecha) {
            return [
                'success' => false,
                'message' => 'La fecha debe tener el formato YYYY-MM-DD',
                'data' => null
            ];
        }
        
        // Preparar consulta SQL de actualización
        $sql = "UPDATE eventos SET 
                nombre = :nombre, 
                descripcion = :descripcion, 
                fecha = :fecha, 
                cupo_total = :cupo_total, 
                cantidad_anticipadas = :cantidad_anticipadas, 
                precio_anticipadas = :precio_anticipadas, 
                precio_en_puerta = :precio_en_puerta, 
                banner = :banner 
                WHERE id = :id";
        
        $stmt = $conexion->prepare($sql);
        
        $resultado = $stmt->execute([
            ':id' => $datos['evento_id'],
            ':nombre' => trim($datos['nombre']),
            ':descripcion' => trim($datos['descripcion']),
            ':fecha' => $datos['fecha'],
            ':cupo_total' => (int)$datos['cupo_total'],
            ':cantidad_anticipadas' => (int)$datos['cantidad_anticipadas'],
            ':precio_anticipadas' => (float)$datos['precio_anticipadas'],
            ':precio_en_puerta' => (float)$datos['precio_en_puerta'],
            ':banner' => $banner_path
        ]);
        
        if ($resultado) {
            return [
                'success' => true,
                'message' => 'Evento actualizado exitosamente',
                'data' => [
                    'id' => $datos['evento_id'],
                    'nombre' => trim($datos['nombre'])
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar el evento',
                'data' => null
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error en actualizar_evento: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        error_log("Error general en actualizar_evento: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// Función para eliminar un evento
function eliminar_evento($id) {
    try {
        $conexion = db_connection();
        
        // Obtener los datos del evento para eliminar la imagen
        $evento = obtener_evento($id);
        if (!$evento['success']) {
            return $evento;
        }
        
        $evento_data = $evento['data'];
        
        // Eliminar el evento de la base de datos
        $sql = "DELETE FROM eventos WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $resultado = $stmt->execute([':id' => $id]);
        
        if ($resultado) {
            // Eliminar la imagen del servidor si existe
            if ($evento_data['banner'] && file_exists('../' . $evento_data['banner'])) {
                unlink('../' . $evento_data['banner']);
            }
            
            return [
                'success' => true,
                'message' => 'Evento eliminado exitosamente',
                'data' => [
                    'id' => $id,
                    'nombre' => $evento_data['nombre']
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al eliminar el evento',
                'data' => null
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error en eliminar_evento: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        error_log("Error general en eliminar_evento: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// Función para procesar compra de entrada anticipada


// Procesar formulario de crear evento
if ($_POST && !isset($_POST['action'])) {
    $resultado = crear_evento($_POST);
    
    if ($resultado['success']) {
        header('Location: ' . BASE_URL . 'index.php?page=admin&success=' . urlencode($resultado['message']));
    } else {
        header('Location: ' . BASE_URL . 'index.php?page=admin&error=' . urlencode($resultado['message']));
    }
    exit;
}

// Procesar formulario de actualizar evento
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update') {
    $resultado = actualizar_evento($_POST);
    
    if ($resultado['success']) {
        header('Location: ' . BASE_URL . 'index.php?page=admin-eventos&success=' . urlencode($resultado['message']));
    } else {
        header('Location: ' . BASE_URL . 'index.php?page=admin-eventos&error=' . urlencode($resultado['message']));
    }
    exit;
}

// Función para obtener eventos activos (para recepción)
function obtener_eventos_activos() {
    try {
        $conexion = db_connection();
        
        // Obtener eventos de hoy y futuros
        $sql = "SELECT id, nombre, descripcion, fecha, cupo_total, cantidad_anticipadas, precio_anticipadas, precio_en_puerta 
                FROM eventos 
                WHERE fecha >= CURDATE() 
                ORDER BY fecha ASC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear fechas para mejor legibilidad
        foreach ($eventos as &$evento) {
            $evento['fecha_formateada'] = date('d/m/Y', strtotime($evento['fecha']));
            $evento['capacidad'] = $evento['cupo_total'];
        }
        
        return [
            'success' => true,
            'message' => 'Eventos activos obtenidos exitosamente',
            'events' => $eventos
        ];
        
    } catch (PDOException $e) {
        error_log("Error en obtener_eventos_activos: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al obtener eventos activos',
            'events' => []
        ];
    }
}

// Procesar solicitud de eventos activos (AJAX)
if ($_GET && isset($_GET['action']) && $_GET['action'] === 'get_active_events') {
    header('Content-Type: application/json');
    echo json_encode(obtener_eventos_activos());
    exit;
}

// Procesar eliminación de evento (GET request)
if ($_GET && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $resultado = eliminar_evento($_GET['id']);
    
    if ($resultado['success']) {
        header('Location: ' . BASE_URL . 'index.php?page=admin-eventos&success=' . urlencode($resultado['message']));
    } else {
        header('Location: ' . BASE_URL . 'index.php?page=admin-eventos&error=' . urlencode($resultado['message']));
    }
    exit;
}
