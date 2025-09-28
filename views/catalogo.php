<?php
// Incluir configuración de la base de datos
require_once 'config/database.php';

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

// Obtener los eventos
$eventos_resultado = listar_eventos();
$eventos = $eventos_resultado['success'] ? $eventos_resultado['data'] : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Eventos</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/catalogo.css">
</head>
<body>
    <!-- include navbar -->
    <?php include 'navbar.php'; ?>
    
    <div class="main-content">
        <div class="catalog-container">
            <?php if (!empty($eventos)): ?>
                <div class="events-grid">
                    <?php foreach ($eventos as $evento): ?>
                        <div class="event-card">
                            <a href="#" class="event-image-link" onclick="comprarEntrada(<?php echo $evento['id']; ?>)">
                                <div class="event-image">
                                    <?php 
                                    $imagen_src = !empty($evento['banner']) ? '../' . htmlspecialchars($evento['banner']) : '../img/malpa.png';
                                    ?>
                                    <img src="<?php echo $imagen_src; ?>" alt="<?php echo htmlspecialchars($evento['nombre']); ?>">
                                    <div class="event-title-overlay">
                                        <h3 class="event-title"><?php echo htmlspecialchars($evento['nombre']); ?></h3>
                                    </div>
                                    <div class="event-price-overlay">
                                        <span class="price-tag">$<?php echo number_format($evento['precio_anticipadas'], 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="event-hover-overlay">
                                        <span class="hover-message">Comprá tu anticipada</span>
                                    </div>
                                </div>
                            </a>
                            
                            <div class="event-content">
                                <p class="event-description"><?php echo htmlspecialchars($evento['descripcion']); ?></p>
                                
                                <div class="event-details">
                                    <div class="event-date">📆
                                        <?php 
                                        // Configurar local en español
                                        setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
                                        
                                        // Array de días de la semana en español
                                        $dias_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                        
                                        // Array de meses en español
                                        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                                                 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                        
                                        // Obtener información de la fecha
                                        $timestamp = strtotime($evento['fecha']);
                                        $dia_semana = $dias_semana[date('w', $timestamp)];
                                        $dia = date('j', $timestamp);
                                        $mes = $meses[date('n', $timestamp)];
                                        
                                        echo $dia_semana . ' ' . $dia . ' de ' . $mes;
                                        ?>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-events-container">
                    <div class="event-card no-events-card">
                        <div class="event-image">
                            <img src="../img/malpa.png" alt="No hay eventos">
                            <div class="event-title-overlay">
                                <h3 class="event-title">No hay eventos próximos</h3>
                            </div>
                        </div>
                        <div class="event-content">
                            <p class="event-description">Próximamente se agregarán nuevos eventos. ¡Mantente atento!</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Función placeholder para comprar entrada
        function comprarEntrada(eventoId) {
            alert(`Funcionalidad de compra para evento ${eventoId} - Por implementar`);
            return false; // Prevenir navegación del enlace
        }
    </script>
</body>
</html>