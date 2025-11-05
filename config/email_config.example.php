<?php
/**
 * Configuración de correo para envío de comprobantes
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como email_config.php
 * 2. Completa los datos de tu cuenta de Gmail
 * 3. Para usar Gmail, necesitas:
 *    - Habilitar "Verificación en 2 pasos" en tu cuenta de Google
 *    - Crear una "Contraseña de aplicación" en: https://myaccount.google.com/apppasswords
 *    - Usar esa contraseña en lugar de tu contraseña normal
 */

// Configuración SMTP de Gmail
define('EMAIL_SMTP_HOST', 'smtp.gmail.com');
define('EMAIL_SMTP_PORT', 587); // Puerto 587 para TLS, 465 para SSL
define('EMAIL_SMTP_SECURE', 'tls'); // 'tls' o 'ssl'
define('EMAIL_SMTP_USER', 'tu_correo@gmail.com'); // Tu correo de Gmail
define('EMAIL_SMTP_PASS', 'tu_contraseña_de_aplicacion'); // Contraseña de aplicación de Google
define('EMAIL_FROM_NAME', 'Malpa Eventos');
define('EMAIL_FROM_ADDRESS', 'noreply@malpaeventos.com');

// Si prefieres usar mail() estándar (requiere sendmail configurado)
define('USE_PHP_MAIL', false); // Cambiar a true para usar mail() estándar
?>

