<?php
// Incluir archivo de configuración con la conexión a la base de datos
require_once 'config/database.php';

// ==========================
// Función: contar_usuarios($filtros = [])
// ==========================
function contar_usuarios($filtros = []) {
    try {
        $conexion = db_connection();
        
        $sql = "SELECT COUNT(*) as total
                FROM usuarios u
                LEFT JOIN roles r ON u.id_rol = r.id";
        
        $where_conditions = [];
        $params = [];
        
        // Aplicar los mismos filtros que en listar_usuarios
        if (!empty($filtros['id_rol']) && is_numeric($filtros['id_rol'])) {
            $where_conditions[] = "u.id_rol = ?";
            $params[] = intval($filtros['id_rol']);
        }
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(" AND ", $where_conditions);
        }
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return intval($result['total']);
    } catch (PDOException $e) {
        error_log("Error en contar_usuarios: " . $e->getMessage());
        return 0;
    }
}

// ==========================
// Función: listar_usuarios($filtros = [], $limit = null, $offset = 0)
// ==========================
function listar_usuarios($filtros = [], $limit = null, $offset = 0) {
    try {
        $conexion = db_connection();
        
        $sql = "SELECT u.id, u.dni, u.nombre, u.apellido, u.fecha_nac, u.correo, u.id_rol, r.nombre as rol_nombre
                FROM usuarios u
                LEFT JOIN roles r ON u.id_rol = r.id";
        
        // Array para almacenar las condiciones WHERE
        $where_conditions = [];
        $params = [];
        
        // Filtro por rol
        if (!empty($filtros['id_rol']) && is_numeric($filtros['id_rol'])) {
            $where_conditions[] = "u.id_rol = ?";
            $params[] = intval($filtros['id_rol']);
        }
        
        // Agregar condiciones WHERE si existen
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(" AND ", $where_conditions);
        }
        
        // Agregar ORDER BY
        $sql .= " ORDER BY u.nombre ASC, u.apellido ASC";
        
        // Agregar LIMIT y OFFSET si se especifican
        if ($limit !== null && $limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = intval($limit);
            $params[] = intval($offset);
        }
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);
        
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
                WHERE id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        
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

// ==============================
// Función: obtener_roles()
// ==============================
function obtener_roles() {
    try {
        $conexion = db_connection();
        
        $sql = "SELECT id, nombre FROM roles ORDER BY nombre ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $roles
        ];
    } catch (PDOException $e) {
        error_log("Error en obtener_roles: " . $e->getMessage());
        return [
            'success' => false,
            'data' => []
        ];
    }
}

// ==============================
// Ejecución del listado de datos
// ==============================

// Obtener filtros del GET
$filtros = [
    'id_rol' => isset($_GET['filtro_rol']) && !empty($_GET['filtro_rol']) ? trim($_GET['filtro_rol']) : ''
];

// Obtener roles para el dropdown
$roles_resultado = obtener_roles();
$roles = $roles_resultado['success'] ? $roles_resultado['data'] : [];

// Configuración de paginación
$registros_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && $_GET['pagina'] > 0 ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Contar total de usuarios (antes de paginar)
$total_usuarios = contar_usuarios($filtros);
$total_paginas = ceil($total_usuarios / $registros_por_pagina);

// Obtener usuarios con filtros y paginación aplicados
$usuarios_resultado = listar_usuarios($filtros, $registros_por_pagina, $offset);
// Si la consulta fue exitosa, asignar los datos, si no, un arreglo vacío
$usuarios = $usuarios_resultado['success'] ? $usuarios_resultado['data'] : [];

// ==============================
// Modo edición de un usuario
// ==============================

// Variables de control
$usuario_editando = null;
$mostrar_modal = false;
$modo_modal = 'crear'; // Por defecto, modo crear

// Verificar si se pasa el parámetro GET "edit" (modo edición)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    // Buscar el usuario a editar
    $usuario_resultado = obtener_usuario($_GET['edit']);
    if ($usuario_resultado['success'] && $usuario_resultado['data']) {
        // Asignar datos del usuario y mostrar modal de edición
        $usuario_editando = $usuario_resultado['data'];
        $mostrar_modal = true;
        $modo_modal = 'editar';
    }
}

// Verificar si se pasa el parámetro GET "crear" (modo crear)
if (isset($_GET['crear'])) {
    $mostrar_modal = true;
    $modo_modal = 'crear';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestión de Usuarios</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>img/favicon.png">

    <!-- Archivos CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/admin.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/toast.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/modal.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/crud-usuarios.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/ventas-styles.css">
</head>
<body>
    <!-- Contenedor de notificaciones -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- Navbar común -->
    <?php include 'navbar.php'; ?>
    
    <!-- Contenido principal -->
    <div class="main-content">
        <div class="admin-container">
            <!-- Barra lateral -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Contenido central -->
            <div class="admin-main-content">
                <div class="admin-panel">
                    <h1>Gestión de Usuarios</h1>

            <!-- Filtros de búsqueda -->
            <div class="filtros-container">
                <h3>Filtros de Búsqueda</h3>
                <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="filtros-form">
                    <!-- Preservar parámetro page -->
                    <input type="hidden" name="page" value="admin-usuarios">
                    <!-- Resetear a página 1 cuando se aplican filtros -->
                    <input type="hidden" name="pagina" value="1">
                    <?php if (isset($_GET['edit'])): ?>
                        <input type="hidden" name="edit" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
                    <?php endif; ?>
                    
                    <!-- Filtro por Rol -->
                    <div class="form-group">
                        <label for="filtro_rol">Rol</label>
                        <select id="filtro_rol" name="filtro_rol">
                            <option value="">Todos los roles</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol['id']; ?>" 
                                    <?php echo (isset($filtros['id_rol']) && $filtros['id_rol'] == $rol['id']) ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(htmlspecialchars($rol['nombre'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="form-group filtros-actions">
                        <button type="submit" class="btn btn-primary">
                            Filtrar
                        </button>
                        <a href="<?php echo BASE_URL; ?>index.php?page=admin-usuarios<?php echo isset($_GET['edit']) ? '&edit=' . htmlspecialchars($_GET['edit']) : ''; ?>" 
                           class="btn btn-secondary">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Tabla de usuarios -->
            <div class="users-list-container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2>Usuarios Registrados</h2>
                    <a href="<?php echo BASE_URL; ?>index.php?page=admin-usuarios&crear=1" class="btn btn-primary">
                        + Crear Usuario
                    </a>
                </div>
                <div class="users-list">
                    <?php if (empty($usuarios)): ?>
                        <!-- Mensaje si no hay usuarios -->
                        <div class="no-users">
                            <?php if (!empty(array_filter($filtros))): ?>
                                No se encontraron resultados relacionados con la búsqueda.
                            <?php else: ?>
                                No hay usuarios registrados
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Tabla de datos -->
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>DNI</th>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Correo</th>
                                    <th>Fecha Nacimiento</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <?php
                                    // Formatear la fecha de nacimiento
                                    $fecha_nac = new DateTime($usuario['fecha_nac']);
                                    $fechaFormateada = $fecha_nac->format('d-m-Y');
                                    
                                    // Obtener nombre del rol
                                    $rol_nombre = isset($usuario['rol_nombre']) ? ucfirst($usuario['rol_nombre']) : 'Sin rol';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['dni']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['apellido']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                        <td><?php echo $fechaFormateada; ?></td>
                                        <td>
                                            <span class="rol-badge rol-badge-<?php echo strtolower($rol_nombre); ?>">
                                                <?php echo $rol_nombre; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- Botón de editar -->
                                            <a href="<?php echo BASE_URL; ?>index.php?page=admin-usuarios&edit=<?php echo $usuario['id']; ?>" class="btn btn-primary btn-sm">
                                                Editar
                                            </a>
                                            <!-- Botón de eliminar -->
                                            <a href="<?php echo BASE_URL; ?>methods/users.php?action=delete&id=<?php echo $usuario['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Estás seguro de que quieres eliminar este usuario? Esta acción no se puede deshacer.')">
                                                Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <!-- Controles de paginación -->
                        <?php if ($total_paginas > 1): ?>
                            <div class="pagination-container">
                                <div class="pagination-info">
                                    <span>
                                        Mostrando <?php echo $usuarios ? count($usuarios) : 0; ?> de <?php echo $total_usuarios; ?> usuarios
                                        (Página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?>)
                                    </span>
                                </div>
                                <div class="pagination-controls">
                                    <?php
                                    // Función auxiliar para construir URL con filtros y página
                                    function construir_url_paginacion_usuarios($pagina, $filtros_actuales) {
                                        $params = ['page' => 'admin-usuarios', 'pagina' => $pagina];
                                        
                                        if (!empty($filtros_actuales['id_rol'])) {
                                            $params['filtro_rol'] = $filtros_actuales['id_rol'];
                                        }
                                        
                                        return BASE_URL . 'index.php?' . http_build_query($params);
                                    }
                                    
                                    // Botón Anterior
                                    if ($pagina_actual > 1): 
                                        $url_anterior = construir_url_paginacion_usuarios($pagina_actual - 1, $filtros);
                                    ?>
                                        <a href="<?php echo $url_anterior; ?>" class="pagination-btn pagination-btn-prev">
                                            ‹ Anterior
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-btn-disabled">‹ Anterior</span>
                                    <?php endif; ?>
                                    
                                    <!-- Números de página -->
                                    <div class="pagination-numbers">
                                        <?php
                                        // Mostrar páginas alrededor de la página actual
                                        $paginas_a_mostrar = [];
                                        $rango = 2; // Número de páginas a mostrar a cada lado
                                        
                                        $inicio = max(1, $pagina_actual - $rango);
                                        $fin = min($total_paginas, $pagina_actual + $rango);
                                        
                                        // Agregar primera página si no está en el rango
                                        if ($inicio > 1) {
                                            $paginas_a_mostrar[] = 1;
                                            if ($inicio > 2) {
                                                $paginas_a_mostrar[] = '...';
                                            }
                                        }
                                        
                                        // Agregar páginas del rango
                                        for ($i = $inicio; $i <= $fin; $i++) {
                                            $paginas_a_mostrar[] = $i;
                                        }
                                        
                                        // Agregar última página si no está en el rango
                                        if ($fin < $total_paginas) {
                                            if ($fin < $total_paginas - 1) {
                                                $paginas_a_mostrar[] = '...';
                                            }
                                            $paginas_a_mostrar[] = $total_paginas;
                                        }
                                        
                                        foreach ($paginas_a_mostrar as $num_pagina):
                                            if ($num_pagina === '...'):
                                        ?>
                                            <span class="pagination-dots">...</span>
                                        <?php
                                            else:
                                                $url_pagina = construir_url_paginacion_usuarios($num_pagina, $filtros);
                                                $clase_activa = ($num_pagina == $pagina_actual) ? 'pagination-btn-active' : '';
                                        ?>
                                            <a href="<?php echo $url_pagina; ?>" 
                                               class="pagination-btn pagination-btn-number <?php echo $clase_activa; ?>">
                                                <?php echo $num_pagina; ?>
                                            </a>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </div>
                                    
                                    <!-- Botón Siguiente -->
                                    <?php if ($pagina_actual < $total_paginas): 
                                        $url_siguiente = construir_url_paginacion_usuarios($pagina_actual + 1, $filtros);
                                    ?>
                                        <a href="<?php echo $url_siguiente; ?>" class="pagination-btn pagination-btn-next">
                                            Siguiente ›
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-btn-disabled">Siguiente ›</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php elseif ($total_usuarios > 0): ?>
                            <div class="pagination-info">
                                <span>Total: <?php echo $total_usuarios; ?> usuario(s)</span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de usuario (crear/editar) -->
    <div id="editUserModal" class="modal <?php echo $mostrar_modal ? 'show' : ''; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $modo_modal === 'editar' ? 'Editar Usuario' : 'Crear Usuario'; ?></h2>
                <!-- Botón para cerrar modal -->
                <button class="close" onclick="closeModal('editUserModal')">&times;</button>
            </div>
            
            <!-- Formulario de usuario -->
            <form action="<?php echo BASE_URL; ?>methods/users.php" method="post" id="editUserForm" class="modal-body modal-usuarios">
                <!-- Campos ocultos -->
                <?php if ($modo_modal === 'editar'): ?>
                    <input type="hidden" name="usuario_id" value="<?php echo $usuario_editando ? $usuario_editando['id'] : ''; ?>">
                    <input type="hidden" name="action" value="update">
                <?php else: ?>
                    <input type="hidden" name="action" value="create">
                <?php endif; ?>
                
                <!-- Campos de texto -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_nombre">Nombre <span class="required">*</span></label>
                        <input type="text" id="edit_nombre" name="nombre" value="<?php echo $usuario_editando ? htmlspecialchars($usuario_editando['nombre']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_apellido">Apellido <span class="required">*</span></label>
                        <input type="text" id="edit_apellido" name="apellido" value="<?php echo $usuario_editando ? htmlspecialchars($usuario_editando['apellido']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_dni">DNI <span class="required">*</span></label>
                        <input type="text" id="edit_dni" name="dni" maxlength="8" value="<?php echo $usuario_editando ? htmlspecialchars($usuario_editando['dni']) : ''; ?>" required>
                        <small class="form-text">Sin puntos ni espacios (7-8 dígitos)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_fecha_nac">Fecha de Nacimiento <span class="required">*</span></label>
                        <input type="date" id="edit_fecha_nac" name="fecha_nac" value="<?php 
                            if ($usuario_editando && !empty($usuario_editando['fecha_nac'])) {
                                // Formatear fecha para input type="date" (YYYY-MM-DD)
                                $fecha_nac = $usuario_editando['fecha_nac'];
                                // Si viene como datetime, extraer solo la fecha
                                if (strpos($fecha_nac, ' ') !== false) {
                                    $fecha_nac = substr($fecha_nac, 0, 10);
                                }
                                // Crear objeto DateTime para asegurar formato correcto
                                $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_nac);
                                if (!$fecha_obj) {
                                    // Intentar otro formato común
                                    $fecha_obj = DateTime::createFromFormat('Y-m-d H:i:s', $usuario_editando['fecha_nac']);
                                }
                                if ($fecha_obj) {
                                    echo $fecha_obj->format('Y-m-d');
                                } else {
                                    echo $fecha_nac;
                                }
                            } else {
                                echo '';
                            }
                        ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_correo">Correo Electrónico <span class="required">*</span></label>
                    <input type="email" id="edit_correo" name="correo" value="<?php echo $usuario_editando ? htmlspecialchars($usuario_editando['correo']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="edit_contraseña"><?php echo $modo_modal === 'editar' ? 'Nueva Contraseña' : 'Contraseña'; ?> <?php echo $modo_modal === 'crear' ? '<span class="required">*</span>' : ''; ?></label>
                    <?php if ($modo_modal === 'editar' && $usuario_editando): ?>
                        <div class="password-info">
                            <small class="form-text" style="color: #28a745; font-weight: 600;">
                                ✓ Contraseña actual establecida
                            </small>
                        </div>
                    <?php endif; ?>
                    <input type="password" id="edit_contraseña" name="contraseña" 
                           placeholder="<?php echo $modo_modal === 'editar' ? 'Dejar en blanco para mantener la actual' : 'Mínimo 6 caracteres'; ?>" 
                           autocomplete="new-password"
                           <?php echo $modo_modal === 'crear' ? 'required' : ''; ?>>
                    <small class="form-text">
                        <?php if ($modo_modal === 'editar'): ?>
                            Solo completa si deseas cambiar la contraseña. Mínimo 6 caracteres. Si dejas este campo vacío, se mantendrá la contraseña actual.
                        <?php else: ?>
                            Ingresa una contraseña segura. Mínimo 6 caracteres.
                        <?php endif; ?>
                    </small>
                </div>

                <div class="form-group">
                    <label for="edit_rol">Rol <span class="required">*</span></label>
                    <select id="edit_rol" name="id_rol" required>
                        <option value="2" <?php echo ($usuario_editando && $usuario_editando['id_rol'] == 2) ? 'selected' : ''; ?>>Cliente</option>
                        <option value="3" <?php echo ($usuario_editando && $usuario_editando['id_rol'] == 3) ? 'selected' : ''; ?>>Recepcionista</option>
                        <option value="1" <?php echo ($usuario_editando && $usuario_editando['id_rol'] == 1) ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                    <small class="form-text">Selecciona el rol que tendrá el usuario en el sistema</small>
                </div>
                
                <!-- Botones -->
                <div class="modal-footer">
                    <a href="?page=admin-usuarios" class="btn btn-danger">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $modo_modal === 'editar' ? 'Guardar Cambios' : 'Crear Usuario'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Scripts JS -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    <script src="<?php echo BASE_URL; ?>js/modal.js"></script>
    <script src="<?php echo BASE_URL; ?>js/crud-usuarios.js"></script>
    
    <script>
        // Mostrar mensaje de éxito o error
        <?php if (isset($_GET['success'])): ?>
            showToast('<?php echo htmlspecialchars(urldecode($_GET['success']), ENT_QUOTES, 'UTF-8'); ?>', 'success');
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            showToast('<?php echo htmlspecialchars(urldecode($_GET['error']), ENT_QUOTES, 'UTF-8'); ?>', 'error');
        <?php endif; ?>
        
        // Validación de edad mínima (18 años)
        function validarEdadMinima(fechaNacimiento) {
            if (!fechaNacimiento) return false;
            
            const fechaNac = new Date(fechaNacimiento);
            const hoy = new Date();
            const edad = hoy.getFullYear() - fechaNac.getFullYear();
            const mes = hoy.getMonth() - fechaNac.getMonth();
            
            if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
                return edad - 1 >= 18;
            }
            
            return edad >= 18;
        }
        
        // Validar edad al enviar el formulario de usuario
        const editUserForm = document.getElementById('editUserForm');
        if (editUserForm) {
            editUserForm.addEventListener('submit', function(e) {
                const fechaNacInput = document.getElementById('edit_fecha_nac');
                if (fechaNacInput) {
                    const fechaNac = fechaNacInput.value;
                    
                    if (fechaNac && !validarEdadMinima(fechaNac)) {
                        e.preventDefault();
                        showToast('Debe ser mayor de 18 años para registrarse', 'error');
                        fechaNacInput.focus();
                        return false;
                    }
                }
            });
            
            // Validar edad al cambiar la fecha
            const fechaNacInput = document.getElementById('edit_fecha_nac');
            if (fechaNacInput) {
                fechaNacInput.addEventListener('change', function(e) {
                    const fechaNac = this.value;
                    if (fechaNac && !validarEdadMinima(fechaNac)) {
                        showToast('Debe ser mayor de 18 años para registrarse', 'error');
                        this.setCustomValidity('Debe ser mayor de 18 años');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        }
    </script>
</body>
</html>

