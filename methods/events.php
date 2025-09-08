<?php
//configuración de base de datos
require_once '../config/database.php';

// Header para respuesta JSON (solo para probar en postman - ignorar)
header('Content-Type: application/json; charset=utf-8');


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
        $sql = "INSERT INTO eventos (nombre, descripcion, fecha, cupo_total, cantidad_anticipadas, precio_anticipadas, precio_en_puerta, id_usuario) 
                VALUES (:nombre, :descripcion, :fecha, :cupo_total, :cantidad_anticipadas, :precio_anticipadas, :precio_en_puerta, :id_usuario)";
        
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


// Manejo para formularios PHP
if ($_POST) {
    // Crear nuevo evento con datos del formulario
    $resultado = crear_evento($_POST);
    
    if ($resultado['success']) {
        // Redirigir de vuelta al formulario con mensaje de éxito
        header('Location: ../views/admin.php?mensaje=evento_creado');
        exit;
    } else {
        // Redirigir con mensaje de error
        header('Location: ../views/admin.php?error=' . urlencode($resultado['message']));
        exit;
    }
}
?>