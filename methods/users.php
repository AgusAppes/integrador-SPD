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
        
        // Determinar el rol: si viene desde admin (id_rol en datos), usarlo; si no, usar 2 (cliente) por defecto
        $id_rol = 2; // Por defecto cliente
        if (isset($datos['id_rol']) && is_numeric($datos['id_rol'])) {
            $id_rol_valido = intval($datos['id_rol']);
            // Validar que el rol sea válido (1=admin, 2=cliente, 3=recepcionista)
            if (in_array($id_rol_valido, [1, 2, 3])) {
                $id_rol = $id_rol_valido;
            }
        }
        
        // Preparar consulta SQL
        $sql = "INSERT INTO usuarios (dni, nombre, apellido, fecha_nac, correo, contraseña, id_rol) 
                VALUES (:dni, :nombre, :apellido, :fecha_nac, :correo, :password, :id_rol)";
        
        $stmt = $conexion->prepare($sql);
        
        // Ejecutar consulta
        $resultado = $stmt->execute([
            ':dni' => $datos['dni'],
            ':nombre' => trim($datos['nombre']),
            ':apellido' => trim($datos['apellido']),
            ':fecha_nac' => $datos['fecha_nac'] ?? null,
            ':correo' => trim($datos['correo']),
            ':password' => $contraseña_hash,
            ':id_rol' => $id_rol
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
        
        // Buscar usuario por correo (sin case sensitivity)
        $correo_trimmed = trim($datos['correo']);
        $sql = "SELECT id, dni, nombre, apellido, correo, contraseña, id_rol FROM usuarios WHERE LOWER(correo) = LOWER(:correo)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':correo' => $correo_trimmed]);
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            return [
                'success' => false,
                'message' => 'Correo electrónico o contraseña incorrectos',
                'data' => null
            ];                                                                                                                                                                                                                                                                                                                               
        }
        
        // Verificar contraseña (trim para eliminar espacios)
        $contraseña_input = trim($datos['contraseña']);
        
        // Verificar si la contraseña está hasheada correctamente
        if (empty($usuario['contraseña'])) {
            return [
                'success' => false,
                'message' => 'Error: la contraseña del usuario no está configurada correctamente',
                'data' => null
            ];
        }
        
        // Intentar verificar la contraseña
        $password_verificado = password_verify($contraseña_input, $usuario['contraseña']);
        
        // Si falla password_verify, puede ser que la contraseña esté guardada en texto plano (migración)
        // Solo para debugging - esto no debería ser necesario en producción
        if (!$password_verificado && $usuario['contraseña'] === $contraseña_input) {
            // La contraseña está en texto plano, debemos actualizarla a hash
            $nuevo_hash = password_hash($contraseña_input, PASSWORD_DEFAULT);
            $sql_update = "UPDATE usuarios SET contraseña = :password WHERE id = :id";
            $stmt_update = $conexion->prepare($sql_update);
            $stmt_update->execute([':password' => $nuevo_hash, ':id' => $usuario['id']]);
            $password_verificado = true; // Permitir el login después de actualizar el hash
        }
        
        if (!$password_verificado) {
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
                header('Location: ' . BASE_URL . 'index.php?page=admin&login_exitoso=1');
            } else {
                // Cliente - redirigir al catálogo
                header('Location: ' . BASE_URL . 'index.php?page=catalogo&login_exitoso=1');
            }
        } else {
            header('Location: ' . BASE_URL . 'index.php?page=login&error=' . urlencode($resultado['message']));
        }
        exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
        // Procesar actualización de usuario desde admin
        // Verificar que el usuario esté autenticado y sea admin
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 1) {
            header('Location: ' . BASE_URL . 'index.php?page=admin-usuarios&error=' . urlencode('No tienes permisos para realizar esta acción'));
            exit;
        }
        
        $resultado = actualizar_usuario($_POST);
        
        if ($resultado['success']) {
            header('Location: ' . BASE_URL . 'index.php?page=admin-usuarios&success=' . urlencode($resultado['message']));
        } else {
            header('Location: ' . BASE_URL . 'index.php?page=admin-usuarios&error=' . urlencode($resultado['message']));
        }
        exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        // Procesar actualización de perfil propio del usuario
        // Verificar que el usuario esté autenticado
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
            header('Location: ' . BASE_URL . 'index.php?page=login&error=' . urlencode('Debes iniciar sesión para acceder a tu perfil'));
            exit;
        }
        
        // Verificar que el usuario esté editando su propio perfil
        if (!isset($_POST['usuario_id']) || $_POST['usuario_id'] != $_SESSION['usuario_id']) {
            header('Location: ' . BASE_URL . 'index.php?page=perfil&error=' . urlencode('No tienes permisos para editar este perfil'));
            exit;
        }
        
        $resultado = actualizar_perfil_propio($_POST);
        
        if ($resultado['success']) {
            // Actualizar datos de sesión con los nuevos valores
            $_SESSION['usuario_nombre'] = trim($_POST['nombre']);
            $_SESSION['usuario_apellido'] = trim($_POST['apellido']);
            $_SESSION['usuario_dni'] = $_POST['dni'];
            $_SESSION['usuario_correo'] = trim($_POST['correo']);
            
            header('Location: ' . BASE_URL . 'index.php?page=perfil&success=' . urlencode($resultado['message']));
        } else {
            header('Location: ' . BASE_URL . 'index.php?page=perfil&error=' . urlencode($resultado['message']));
        }
        exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'create') {
        // Procesar creación de usuario desde admin
        // Verificar que el usuario esté autenticado y sea admin
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 1) {
            header('Location: ' . BASE_URL . 'index.php?page=admin-usuarios&error=' . urlencode('No tienes permisos para realizar esta acción'));
            exit;
        }
        
        $resultado = crear_usuario($_POST);
        
        if ($resultado['success']) {
            header('Location: ' . BASE_URL . 'index.php?page=admin-usuarios&success=' . urlencode($resultado['message']));
        } else {
            header('Location: ' . BASE_URL . 'index.php?page=admin-usuarios&error=' . urlencode($resultado['message']));
        }
        exit;
    } else {
        // Procesar registro (comportamiento anterior - registro público)
        $resultado = crear_usuario($_POST);
        
        if ($resultado['success']) {
            header('Location: ' . BASE_URL . 'index.php?page=login&registro_exitoso=1&message=' . urlencode($resultado['message']));
        } else {
            header('Location: ' . BASE_URL . 'index.php?page=register&error=' . urlencode($resultado['message']));
        }
        exit;
    }
}

// ==========================
// Función: listar_usuarios()
// ==========================
function listar_usuarios() {
    try {
        $conexion = db_connection();
        
        $sql = "SELECT u.id, u.dni, u.nombre, u.apellido, u.fecha_nac, u.correo, u.id_rol, r.nombre as rol_nombre
                FROM usuarios u
                LEFT JOIN roles r ON u.id_rol = r.id
                ORDER BY u.nombre ASC, u.apellido ASC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'message' => 'Usuarios obtenidos exitosamente',
            'data' => $usuarios
        ];
        
    } catch (PDOException $e) {
        error_log("Error en listar_usuarios: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        error_log("Error general en listar_usuarios: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// ============================
// Función: obtener_usuario($id)
// ============================
function obtener_usuario($id) {
    try {
        $conexion = db_connection();
        
        $sql = "SELECT id, dni, nombre, apellido, fecha_nac, correo, id_rol 
                FROM usuarios 
                WHERE id = :id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            return [
                'success' => true,
                'message' => 'Usuario obtenido exitosamente',
                'data' => $usuario
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado',
                'data' => null
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error en obtener_usuario: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        error_log("Error general en obtener_usuario: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// =============================
// Función: actualizar_usuario($datos)
// =============================
function actualizar_usuario($datos) {
    try {
        $conexion = db_connection();
        
        // Validar datos requeridos
        $campos_requeridos = ['usuario_id', 'dni', 'nombre', 'apellido', 'correo', 'fecha_nac'];
        foreach ($campos_requeridos as $campo) {
            if (!isset($datos[$campo]) || (is_string($datos[$campo]) && trim($datos[$campo]) === '')) {
                return [
                    'success' => false,
                    'message' => "El campo '$campo' es requerido",
                    'data' => null
                ];
            }
        }
        
        // Validar que el usuario exista
        $usuario_existente = obtener_usuario($datos['usuario_id']);
        if (!$usuario_existente['success']) {
            return $usuario_existente;
        }
        
        // Validar formato de DNI
        if (!preg_match('/^\d{7,8}$/', $datos['dni'])) {
            return [
                'success' => false,
                'message' => 'El DNI debe contener 7-8 dígitos',
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
        
        // Verificar si el DNI ya existe en otro usuario
        $sql_dni = "SELECT id FROM usuarios WHERE dni = :dni AND id != :id";
        $stmt_dni = $conexion->prepare($sql_dni);
        $stmt_dni->execute([':dni' => $datos['dni'], ':id' => $datos['usuario_id']]);
        if ($stmt_dni->fetch()) {
            return [
                'success' => false,
                'message' => 'Ya existe otro usuario con ese DNI',
                'data' => null
            ];
        }
        
        // Verificar si el correo ya existe en otro usuario
        $sql_correo = "SELECT id FROM usuarios WHERE correo = :correo AND id != :id";
        $stmt_correo = $conexion->prepare($sql_correo);
        $stmt_correo->execute([':correo' => $datos['correo'], ':id' => $datos['usuario_id']]);
        if ($stmt_correo->fetch()) {
            return [
                'success' => false,
                'message' => 'Ya existe otro usuario con ese correo electrónico',
                'data' => null
            ];
        }
        
        // Determinar el rol
        $id_rol = $datos['id_rol'] ?? 2;
        if (isset($datos['id_rol']) && is_numeric($datos['id_rol'])) {
            $id_rol_valido = intval($datos['id_rol']);
            // Validar que el rol sea válido (1=admin, 2=cliente, 3=recepcionista)
            if (in_array($id_rol_valido, [1, 2, 3])) {
                $id_rol = $id_rol_valido;
            }
        }
        
        // Preparar consulta SQL de actualización
        $sql = "UPDATE usuarios SET 
                dni = :dni, 
                nombre = :nombre, 
                apellido = :apellido, 
                fecha_nac = :fecha_nac, 
                correo = :correo, 
                id_rol = :id_rol";
        
        $params = [
            ':id' => $datos['usuario_id'],
            ':dni' => $datos['dni'],
            ':nombre' => trim($datos['nombre']),
            ':apellido' => trim($datos['apellido']),
            ':fecha_nac' => $datos['fecha_nac'],
            ':correo' => trim($datos['correo']),
            ':id_rol' => $id_rol
        ];
        
        // Si se proporciona una nueva contraseña, actualizarla
        // IMPORTANTE: La contraseña es completamente opcional en actualización
        // Solo se actualiza si viene explícitamente y no está vacía
        if (isset($datos['contraseña']) && $datos['contraseña'] !== null && $datos['contraseña'] !== '') {
            $contraseña_trimmed = trim($datos['contraseña']);
            if ($contraseña_trimmed !== '') {
                if (strlen($contraseña_trimmed) < 6) {
                    return [
                        'success' => false,
                        'message' => 'La contraseña debe tener al menos 6 caracteres',
                        'data' => null
                    ];
                }
                $sql .= ", contraseña = :password";
                $params[':password'] = password_hash($contraseña_trimmed, PASSWORD_DEFAULT);
            }
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $conexion->prepare($sql);
        $resultado = $stmt->execute($params);
        
        if ($resultado) {
            return [
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => [
                    'id' => $datos['usuario_id'],
                    'nombre' => trim($datos['nombre'])
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar el usuario',
                'data' => null
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error en actualizar_usuario: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        error_log("Error general en actualizar_usuario: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// =============================
// Función: actualizar_perfil_propio($datos)
// =============================
function actualizar_perfil_propio($datos) {
    try {
        $conexion = db_connection();
        
        // Validar datos requeridos
        $campos_requeridos = ['usuario_id', 'dni', 'nombre', 'apellido', 'correo', 'fecha_nac'];
        foreach ($campos_requeridos as $campo) {
            if (!isset($datos[$campo]) || (is_string($datos[$campo]) && trim($datos[$campo]) === '')) {
                return [
                    'success' => false,
                    'message' => "El campo '$campo' es requerido",
                    'data' => null
                ];
            }
        }
        
        // Validar que el usuario exista
        $usuario_existente = obtener_usuario($datos['usuario_id']);
        if (!$usuario_existente['success']) {
            return $usuario_existente;
        }
        
        // Obtener el rol actual del usuario (no se puede cambiar desde el perfil)
        $rol_actual = $usuario_existente['data']['id_rol'];
        
        // Validar formato de DNI
        if (!preg_match('/^\d{7,8}$/', $datos['dni'])) {
            return [
                'success' => false,
                'message' => 'El DNI debe contener 7-8 dígitos',
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
        
        // Verificar si el DNI ya existe en otro usuario
        $sql_dni = "SELECT id FROM usuarios WHERE dni = :dni AND id != :id";
        $stmt_dni = $conexion->prepare($sql_dni);
        $stmt_dni->execute([':dni' => $datos['dni'], ':id' => $datos['usuario_id']]);
        if ($stmt_dni->fetch()) {
            return [
                'success' => false,
                'message' => 'Ya existe otro usuario con ese DNI',
                'data' => null
            ];
        }
        
        // Verificar si el correo ya existe en otro usuario
        $sql_correo = "SELECT id FROM usuarios WHERE correo = :correo AND id != :id";
        $stmt_correo = $conexion->prepare($sql_correo);
        $stmt_correo->execute([':correo' => $datos['correo'], ':id' => $datos['usuario_id']]);
        if ($stmt_correo->fetch()) {
            return [
                'success' => false,
                'message' => 'Ya existe otro usuario con ese correo electrónico',
                'data' => null
            ];
        }
        
        // Preparar consulta SQL de actualización (sin cambiar el rol)
        $sql = "UPDATE usuarios SET 
                dni = :dni, 
                nombre = :nombre, 
                apellido = :apellido, 
                fecha_nac = :fecha_nac, 
                correo = :correo";
        
        $params = [
            ':id' => $datos['usuario_id'],
            ':dni' => $datos['dni'],
            ':nombre' => trim($datos['nombre']),
            ':apellido' => trim($datos['apellido']),
            ':fecha_nac' => $datos['fecha_nac'],
            ':correo' => trim($datos['correo'])
        ];
        
        // Si se proporciona una nueva contraseña, actualizarla
        // IMPORTANTE: La contraseña es completamente opcional
        // Solo se actualiza si viene explícitamente y no está vacía
        if (isset($datos['contraseña']) && $datos['contraseña'] !== null && $datos['contraseña'] !== '') {
            $contraseña_trimmed = trim($datos['contraseña']);
            if ($contraseña_trimmed !== '') {
                if (strlen($contraseña_trimmed) < 6) {
                    return [
                        'success' => false,
                        'message' => 'La contraseña debe tener al menos 6 caracteres',
                        'data' => null
                    ];
                }
                $sql .= ", contraseña = :password";
                $params[':password'] = password_hash($contraseña_trimmed, PASSWORD_DEFAULT);
            }
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $conexion->prepare($sql);
        $resultado = $stmt->execute($params);
        
        if ($resultado) {
            return [
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'data' => [
                    'id' => $datos['usuario_id'],
                    'nombre' => trim($datos['nombre'])
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar el perfil',
                'data' => null
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error en actualizar_perfil_propio: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        error_log("Error general en actualizar_perfil_propio: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// =============================
// Función: eliminar_usuario($id)
// =============================
function eliminar_usuario($id) {
    try {
        $conexion = db_connection();
        
        // Verificar que el usuario exista
        $usuario = obtener_usuario($id);
        if (!$usuario['success']) {
            return $usuario;
        }
        
        $usuario_data = $usuario['data'];
        
        // No permitir eliminar el último admin
        if ($usuario_data['id_rol'] == 1) {
            $sql_count_admin = "SELECT COUNT(*) as total FROM usuarios WHERE id_rol = 1";
            $stmt_count = $conexion->prepare($sql_count_admin);
            $stmt_count->execute();
            $result = $stmt_count->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] <= 1) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar el último administrador del sistema',
                    'data' => null
                ];
            }
        }
        
        // Eliminar el usuario de la base de datos
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $resultado = $stmt->execute([':id' => $id]);
        
        if ($resultado) {
            return [
                'success' => true,
                'message' => 'Usuario eliminado exitosamente',
                'data' => [
                    'id' => $id,
                    'nombre' => $usuario_data['nombre'] . ' ' . $usuario_data['apellido']
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al eliminar el usuario',
                'data' => null
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error en eliminar_usuario: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage(),
            'data' => null
        ];
    } catch (Exception $e) {
        error_log("Error general en eliminar_usuario: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'data' => null
        ];
    }
}

// Procesar logout (GET request)
if ($_GET && isset($_GET['action']) && $_GET['action'] === 'logout') {
    $resultado = cerrar_sesion();
    
    if ($resultado['success']) {
        header('Location: ' . BASE_URL . 'index.php?page=home&logout_exitoso=1&message=' . urlencode($resultado['message']));
    } else {
        header('Location: ' . BASE_URL . 'index.php?page=home&error=' . urlencode($resultado['message']));
    }
    exit;
}

// Procesar eliminación de usuario (GET)
if ($_GET && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Verificar permisos de admin
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 1) {
        header('Location: ' . BASE_URL . 'index.php?page=admin-usuarios&error=' . urlencode('No tienes permisos para realizar esta acción'));
        exit;
    }
    
    $resultado = eliminar_usuario($_GET['id']);
    
    if ($resultado['success']) {
        header('Location: ' . BASE_URL . 'index.php?page=admin-usuarios&success=' . urlencode($resultado['message']));
    } else {
        header('Location: ' . BASE_URL . 'index.php?page=admin-usuarios&error=' . urlencode($resultado['message']));
    }
    exit;
}

