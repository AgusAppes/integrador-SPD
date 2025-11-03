<?php
// Incluir configuraciones
require_once 'config.php';

// Función para conectar a la base de datos utilizando PDO
function db_connection() {
    static $pdo = null;
    
    // Solo crear conexión si no existe
    if ($pdo === null) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            error_log("Error de conexión DB: " . $e->getMessage());
            die("Error de conexión a la base de datos. Contacte al administrador.");
        }
    }
    
    return $pdo;
}
