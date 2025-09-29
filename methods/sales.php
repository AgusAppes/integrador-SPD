<?php
//configuración de base de datos
require_once '../config/database.php';

// Función para procesar compra de entrada anticipada
function procesar_compra_anticipada($id_evento, $id_usuario = null) {
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
        
        return [
            'success' => true,
            'message' => 'Compra realizada exitosamente',
            'data' => [
                'nro_serie' => $nro_serie,
                'evento' => $evento['nombre'],
                'precio' => $evento['precio_anticipadas'],
                'id_venta' => $id_venta
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

// Procesar compra de entrada anticipada (GET request)
if ($_GET && isset($_GET['action']) && $_GET['action'] === 'comprar' && isset($_GET['id_evento'])) {
    $resultado = procesar_compra_anticipada($_GET['id_evento']);
    
    if ($resultado['success']) {
        header('Location: ../index.php?page=catalogo&compra_exitosa=1&nro_serie=' . urlencode($resultado['data']['nro_serie']) . '&success=' . urlencode($resultado['message']));
    } else {
        // Si el error es por falta de sesión, redirigir al login
        if (strpos($resultado['message'], 'iniciar sesión') !== false) {
            header('Location: ../index.php?page=login&error=' . urlencode($resultado['message']));
        } else {
            header('Location: ../index.php?page=catalogo&error=' . urlencode($resultado['message']));
        }
    }
    exit;
}