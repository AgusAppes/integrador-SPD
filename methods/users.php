<?php
//configuración de base de datos
require_once '../config/database.php';

// Header para respuesta JSON (solo para probar en postman - ignorar)
header('Content-Type: application/json; charset=utf-8');

// Función para crear un nuevo usuario
function crear_usuario($datos) {
    try {
        $conexion = db_connection();
        
        // Validar datos requeridos
        $campos_requeridos = ['dni', 'nombre', 'apellido', 'correo', 'contraseña', 'fecha_nac'];
        foreach ($campos_requeridos as $campo) {
            if (!isset($datos[$campo]) || empty(trim($datos[$campo]))) {
                return [
                    'success' => false,
                    'message' => "El campo '$campo' es requerido",
                    'data' => null
                ];
            }
        }
        
        // Validar formato de DNI (solo números, 7-8 dígitos)
        if (!preg_match('/^\d{7,8}$/', $datos['dni'])) {
            return [
                'success' => false,
                'message' => 'El DNI debe contener 8 dígitos',
                'data' => null
            ];
        }
        
        // Validar formato de email
        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'El formato del correo electrónico no es válido',
                'data' => null
            ];
        }
        
        // Validar longitud de contraseña
        if (strlen($datos['contraseña']) < 6) {
            return [
                'success' => false,
                'message' => 'La contraseña debe tener al menos 6 caracteres',
                'data' => null
            ];
        }
        
        // Verificar si el DNI ya existe
        $sql_dni = "SELECT id FROM usuarios WHERE dni = :dni";
        $stmt_dni = $conexion->prepare($sql_dni);
        $stmt_dni->execute([':dni' => $datos['dni']]);
        if ($stmt_dni->fetch()) {
            return [
                'success' => false,
                'message' => 'Ya existe un usuario con ese DNI',
                'data' => null
            ];
        }
        
        // Verificar si el correo ya existe
        $sql_correo = "SELECT id FROM usuarios WHERE correo = :correo";
        $stmt_correo = $conexion->prepare($sql_correo);
        $stmt_correo->execute([':correo' => $datos['correo']]);
        if ($stmt_correo->fetch()) {
            return [
                'success' => false,
                'message' => 'Ya existe un usuario con ese correo electrónico',
                'data' => null
            ];
        }
        
        // Encriptar contraseña
        $contraseña_hash = password_hash($datos['contraseña'], PASSWORD_DEFAULT);
        
        // Preparar consulta SQL
        $sql = "INSERT INTO usuarios (dni, nombre, apellido, fecha_nac, correo, contraseña, id_rol) 
                VALUES (:dni, :nombre, :apellido, :fecha_nac, :correo, :password, 2)";
        
        $stmt = $conexion->prepare($sql);
        
        // Ejecutar consulta
        $resultado = $stmt->execute([
            ':dni' => $datos['dni'],
            ':nombre' => trim($datos['nombre']),
            ':apellido' => trim($datos['apellido']),
            ':fecha_nac' => $datos['fecha_nac'] ?? null,
            ':correo' => trim($datos['correo']),
            ':password' => $contraseña_hash
        ]);
        
        if ($resultado) {
            $usuario_id = $conexion->lastInsertId();
            return [
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'id' => $usuario_id,
                    'dni' => $datos['dni'],
                    'nombre' => trim($datos['nombre']),
                    'apellido' => trim($datos['apellido']),
                    'correo' => trim($datos['correo'])
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al registrar el usuario',
                'data' => null
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error en crear_usuario: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        error_log("Error general en crear_usuario: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// Función para iniciar sesión
function iniciar_sesion($datos) {
    try {
        $conexion = db_connection();
        
        // Validar datos requeridos
        if (!isset($datos['correo']) || empty(trim($datos['correo']))) {
            return [
                'success' => false,
                'message' => 'El correo electrónico es requerido',
                'data' => null
            ];
        }
        
        if (!isset($datos['contraseña']) || empty(trim($datos['contraseña']))) {
            return [
                'success' => false,
                'message' => 'La contraseña es requerida',
                'data' => null
            ];
        }
        
        // Buscar usuario por correo
        $sql = "SELECT id, dni, nombre, apellido, correo, contraseña, id_rol FROM usuarios WHERE correo = :correo";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':correo' => trim($datos['correo'])]);
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            return [
                'success' => false,
                'message' => 'Correo electrónico o contraseña incorrectos',
                'data' => null
            ];
        }
        
        // Verificar contraseña
        if (!password_verify($datos['contraseña'], $usuario['contraseña'])) {
            return [
                'success' => false,
                'message' => 'Correo electrónico o contraseña incorrectos',
                'data' => null
            ];
        }
        
        // Iniciar sesión
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_apellido'] = $usuario['apellido'];
        $_SESSION['usuario_correo'] = $usuario['correo'];
        $_SESSION['usuario_dni'] = $usuario['dni'];
        $_SESSION['usuario_rol'] = $usuario['id_rol'];
        $_SESSION['loggedin'] = true;
        
        // Si marcó "recordar sesión", extender la vida de la sesión
        if (isset($datos['recordar']) && $datos['recordar']) {
            // Extender por 24 horas
            ini_set('session.cookie_lifetime', 60 * 60 * 24);
        }
        
        return [
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'data' => [
                'usuario_id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'correo' => $usuario['correo'],
                'rol' => $usuario['id_rol']
            ]
        ];
        
    } catch (PDOException $e) {
        error_log("Error en iniciar_sesion: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos',
            'data' => null
        ];
    } catch (Exception $e) {
        error_log("Error general en iniciar_sesion: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// Función para cerrar sesión
function cerrar_sesion() {
    try {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Limpiar todas las variables de sesión
        $_SESSION = array();
        
        // Destruir la cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
            'data' => null
        ];
        
    } catch (Exception $e) {
        error_log("Error en cerrar_sesion: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al cerrar sesión',
            'data' => null
        ];
    }
}

// Procesar formularios
if ($_POST) {
    // Determinar qué acción realizar
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        // Procesar login
        $resultado = iniciar_sesion($_POST);
        
        if ($resultado['success']) {
            // Redirigir según el rol del usuario
            if ($resultado['data']['rol'] == 1) {
                // Admin - redirigir al panel de administración
                header('Location: ../index.php?page=admin&login_exitoso=1');
            } else {
                // Cliente - redirigir al catálogo
                header('Location: ../index.php?page=catalogo&login_exitoso=1');
            }
        } else {
            header('Location: ../index.php?page=login&error=' . urlencode($resultado['message']));
        }
        exit;
    } else {
        // Procesar registro (comportamiento anterior)
        $resultado = crear_usuario($_POST);
        
        if ($resultado['success']) {
            header('Location: ../index.php?page=login&registro_exitoso=1&message=' . urlencode($resultado['message']));
        } else {
            header('Location: ../index.php?page=register&error=' . urlencode($resultado['message']));
        }
        exit;
    }
}

// Procesar logout (GET request)
if ($_GET && isset($_GET['action']) && $_GET['action'] === 'logout') {
    $resultado = cerrar_sesion();
    
    if ($resultado['success']) {
        header('Location: ../index.php?page=home&logout_exitoso=1&message=' . urlencode($resultado['message']));
    } else {
        header('Location: ../index.php?page=home&error=' . urlencode($resultado['message']));
    }
    exit;
}

