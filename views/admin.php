<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
</head>
<body>
    <h1>Crear Evento</h1>
    
    <?php
    // Mostrar mensaje de éxito
    if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'evento_creado') {
        echo '<p style="color: green; font-weight: bold;">¡Evento creado exitosamente!</p>';
    }
    
    // Mostrar mensaje de error
    if (isset($_GET['error'])) {
        echo '<p style="color: red; font-weight: bold;">Error: ' . htmlspecialchars($_GET['error']) . '</p>';
    }
    ?>
    
    <!-- formulario para crear evento -->
    <form action="../methods/events.php" method="POST">
        <div>
            <label for="nombre">Nombre del Evento:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        
        <div>
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
        </div>
        
        <div>
            <label for="fecha">Fecha del Evento:</label>
            <input type="date" id="fecha" name="fecha" required>
        </div>
        
        <div>
            <label for="cupo_total">Cupo Total:</label>
            <input type="number" id="cupo_total" name="cupo_total" min="1" required>
        </div>
        
        <div>
            <label for="cantidad_anticipadas">Cantidad de Entradas Anticipadas:</label>
            <input type="number" id="cantidad_anticipadas" name="cantidad_anticipadas" min="0" required>
        </div>
        
        <div>
            <label for="precio_anticipadas">Precio Entradas Anticipadas:</label>
            <input type="number" id="precio_anticipadas" name="precio_anticipadas" min="0" step="0.01" required>
        </div>
        
        <div>
            <label for="precio_en_puerta">Precio en Puerta:</label>
            <input type="number" id="precio_en_puerta" name="precio_en_puerta" min="0" step="0.01" required>
        </div>
        
        <div>
            <label for="id_usuario">ID Usuario:</label>
            <input type="number" id="id_usuario" name="id_usuario" value="1" min="1" required>
        </div>
        
        <div>
            <button type="submit">Crear Evento</button>
            <button type="reset">Limpiar Formulario</button>
        </div>
    </form>
</body>
</html>
