<?php
// Incluir configuración de la base de datos
require_once '../config/database.php';

// Función para listar todos los eventos (directamente en la vista)
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
                            <div class="event-image">
                                <?php 
                                $imagen_src = !empty($evento['banner']) ? '../' . htmlspecialchars($evento['banner']) : '../img/malpa.png';
                                ?>
                                <img src="<?php echo $imagen_src; ?>" alt="<?php echo htmlspecialchars($evento['nombre']); ?>">
                            </div>
                            
                            <div class="event-content">
                                <h3 class="event-title"><?php echo htmlspecialchars($evento['nombre']); ?></h3>
                                <p class="event-description"><?php echo htmlspecialchars($evento['descripcion']); ?></p>
                                
                                <div class="event-details">
                                    <div class="event-date">
                                        <strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($evento['fecha'])); ?>
                                    </div>
                                </div>
                                
                                <div class="event-prices">
                                    <div class="price-item">
                                        <span class="price-label">Anticipadas:</span>
                                        <span class="price-value">$<?php echo number_format($evento['precio_anticipadas'], 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="price-item">
                                        <span class="price-label">En puerta:</span>
                                        <span class="price-value">$<?php echo number_format($evento['precio_en_puerta'], 0, ',', '.'); ?></span>
                                    </div>
                                </div>
                                
                                <div class="event-actions">
                                    <button class="btn-primary">Comprar Entrada</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-events">
                    <h2>No hay eventos disponibles</h2>
                    <p>Próximamente se agregarán nuevos eventos.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>