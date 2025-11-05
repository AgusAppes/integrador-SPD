# 📧 REPORTE: Sistema de Envío de Correos - Comprobantes de Compra

## 📋 Resumen Ejecutivo

El sistema envía automáticamente un correo electrónico con un comprobante de compra cuando un usuario completa la compra de una entrada. El comprobante incluye toda la información relevante de la transacción y se envía inmediatamente después de confirmar la compra en la base de datos.

---

## 🔄 Flujo del Proceso

### 1. **Proceso de Compra** (`methods/sales.php`)

Cuando un usuario realiza una compra, el sistema ejecuta la función `procesar_compra_anticipada()`:

1. **Validaciones:**
   - Verifica que el usuario esté autenticado
   - Valida que el evento existe
   - Verifica que el evento no haya pasado
   - Confirma que hay entradas anticipadas disponibles

2. **Transacción de Base de Datos:**
   - Genera un número de serie único (ID_evento + número aleatorio)
   - Crea registro en tabla `entradas`
   - Crea registro en tabla `ventas`
   - Crea registro en tabla `detalle_venta`
   - Confirma la transacción (commit)

3. **Envío de Correo:**
   - Obtiene los datos del usuario (nombre, apellido, correo)
   - Llama a la función `enviar_comprobante_compra()`
   - Si el correo no se envía, registra una advertencia pero NO cancela la compra

---

## 📨 Función de Envío de Correo

### `enviar_comprobante_compra($usuario, $evento, $nro_serie, $precio)`

**Ubicación:** `methods/sales.php` (líneas 183-447)

**Parámetros:**
- `$usuario`: Array con datos del usuario (nombre, apellido, correo)
- `$evento`: Array con datos del evento (nombre, fecha)
- `$nro_serie`: Número de serie de la entrada
- `$precio`: Precio pagado

**Proceso:**

1. **Preparación de Datos:**
   - Formatea la fecha del evento en español (ej: "Viernes 15 de Marzo de 2024")
   - Genera la fecha de compra (formato: dd/mm/yyyy HH:mm)
   - Crea el asunto del correo: "Comprobante de Compra - [Nombre del Evento]"

2. **Generación del HTML:**
   - Crea un correo HTML con estilos CSS embebidos
   - Diseño responsive y profesional
   - Colores corporativos (púrpura/violeta #FF00FF)

3. **Contenido del Comprobante:**
   - ✅ **Evento:** Nombre del evento
   - ✅ **Fecha del Evento:** Fecha formateada en español
   - ✅ **Número de Serie:** Número único de la entrada (destacado)
   - ✅ **Fecha de Compra:** Fecha y hora de la transacción
   - ✅ **Total Pagado:** Monto en formato monetario
   - ⚠️ **Advertencia:** Recordatorio de llevar DNI al evento

4. **Métodos de Envío (con fallback):**

   **Método 1: PHP `mail()` (Intento Principal)**
   - Usa la función nativa `mail()` de PHP
   - Headers configurados para HTML y UTF-8
   - Si falla, intenta método alternativo

   **Método 2: SMTP Directo (Fallback)**
   - Si `mail()` falla Y SMTP está configurado, usa `enviar_correo_smtp()`
   - Conecta directamente al servidor SMTP de Gmail
   - Usa autenticación LOGIN con TLS

---

## 🔐 Configuración SMTP

### Archivo: `config/email_config.php`

**Credenciales Configuradas:**
```php
EMAIL_SMTP_HOST = 'smtp.gmail.com'
EMAIL_SMTP_PORT = 587
EMAIL_SMTP_SECURE = 'tls'
EMAIL_SMTP_USER = 'tiarasb689@gmail.com'
EMAIL_SMTP_PASS = 'etjt xvft wkfy gblw' (Contraseña de aplicación)
EMAIL_FROM_ADDRESS = 'tiarasb689@gmail.com'
EMAIL_FROM_NAME = 'Malpa Eventos'
```

**Nota:** Se usa una **contraseña de aplicación de Google**, no la contraseña normal de Gmail.

---

## 🔧 Función SMTP Directa

### `enviar_correo_smtp($correo_destino, $asunto, $mensaje_html)`

**Ubicación:** `methods/sales.php` (líneas 17-180)

**Proceso de Conexión:**

1. **Conexión al Servidor:**
   - Conecta a `smtp.gmail.com:587`
   - Usa TLS (Transport Layer Security)
   - Crea un socket SSL con contexto de verificación relajada

2. **Protocolo SMTP:**
   ```
   → EHLO (saludo inicial)
   → STARTTLS (iniciar cifrado)
   → EHLO (después de TLS)
   → AUTH LOGIN (autenticación)
   → [Usuario en Base64]
   → [Contraseña en Base64]
   → MAIL FROM (dirección remitente)
   → RCPT TO (dirección destinatario)
   → DATA (inicio del mensaje)
   → [Headers + Contenido HTML en Base64]
   → . (fin del mensaje)
   → QUIT (cerrar conexión)
   ```

3. **Validaciones:**
   - Verifica cada respuesta del servidor SMTP
   - Registra errores en logs si algo falla
   - Retorna `true` si el correo se envió exitosamente

---

## 📊 Manejo de Errores

### Sistema de Logs

**Logs de Éxito:**
```
✓ Correo enviado exitosamente vía SMTP a: usuario@email.com
✓ Correo de comprobante enviado exitosamente a: usuario@email.com
```

**Logs de Error:**
```
✗ Error al enviar correo con mail() a: usuario@email.com - Error: [detalle]
✗ Error enviando correo vía SMTP. Respuesta: [respuesta del servidor]
```

### Archivos de Respaldo

Si el correo falla, el sistema:

1. **Crea Log de Texto:**
   - Ubicación: `logs/comprobantes_YYYY-MM-DD.log`
   - Contiene: Email, Evento, Serie, Total, Error

2. **Guarda HTML del Comprobante:**
   - Ubicación: `logs/comprobantes_html/comprobante_[nro_serie].html`
   - Permite envío manual posterior si es necesario

### Comportamiento en Caso de Fallo

**⚠️ IMPORTANTE:** Si el correo NO se puede enviar:
- ✅ **La compra SÍ se confirma** (no se revierte)
- ✅ **El usuario recibe un mensaje** indicando que la compra fue exitosa
- ⚠️ **Se muestra una nota** que el comprobante no pudo enviarse por correo
- 📝 **Se registra en logs** para seguimiento

---

## 📧 Plantilla del Correo

### Estructura HTML

```html
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    /* Estilos CSS embebidos */
    - Diseño responsive
    - Colores corporativos (#FF00FF, gradientes púrpura)
    - Tipografía Arial
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🎫 Comprobante de Compra</h1>
    </div>
    <div class="content">
      - Saludo personalizado
      - Caja de comprobante con detalles
      - Caja de total pagado
      - Advertencia sobre DNI
    </div>
    <div class="footer">
      - Mensaje de no responder
      - Información de contacto
    </div>
  </div>
</body>
</html>
```

### Información Incluida

1. **Datos del Usuario:**
   - Nombre completo (nombre + apellido)

2. **Datos del Evento:**
   - Nombre del evento
   - Fecha formateada en español

3. **Datos de la Compra:**
   - Número de serie (destacado en color #FF00FF)
   - Fecha y hora de compra
   - Precio total pagado

4. **Instrucciones:**
   - Recordatorio de llevar DNI
   - Información de contacto

---

## 🔄 Integración con el Sistema

### Llamada desde `procesar_compra_anticipada()`

**Ubicación:** `methods/sales.php` (líneas 560-575)

```php
// Después de confirmar la transacción en BD
$correo_enviado = enviar_comprobante_compra(
    $usuario_datos,      // Datos del usuario
    $evento,            // Datos del evento
    $nro_serie,         // Número de serie generado
    $evento['precio_anticipadas']  // Precio pagado
);

if (!$correo_enviado) {
    error_log("ADVERTENCIA: No se pudo enviar el correo...");
}

// Mensaje de éxito incluye información sobre el correo
$mensaje_exito = 'Compra realizada exitosamente';
if (!$correo_enviado) {
    $mensaje_exito .= '. Nota: El comprobante por correo no pudo ser enviado...';
}
```

---

## ✅ Estado Actual del Sistema

### Funcionamiento Confirmado

✅ **Configuración SMTP:** Configurada correctamente con Gmail
✅ **Contraseña de Aplicación:** Configurada (no expuesta en logs)
✅ **Función SMTP:** Implementada con manejo de errores robusto
✅ **Función PHP mail():** Implementada como método primario
✅ **Sistema de Fallback:** Funciona automáticamente si un método falla
✅ **Logs:** Registran todos los intentos y resultados
✅ **Respaldo:** Guarda comprobantes HTML si falla el envío

### Posibles Mejoras

1. **Cola de Correos:**
   - Implementar sistema de cola para reintentos automáticos
   - Procesar correos pendientes en segundo plano

2. **Plantillas Externas:**
   - Separar plantilla HTML en archivo externo
   - Facilitar personalización del diseño

3. **Múltiples Formatos:**
   - Enviar también PDF adjunto
   - Opción de texto plano para clientes antiguos

4. **Notificaciones:**
   - Alertar al administrador si hay muchos fallos
   - Dashboard de estadísticas de envío

---

## 📝 Resumen Técnico

**Archivos Involucrados:**
- `methods/sales.php` - Lógica principal de compra y envío
- `config/email_config.php` - Configuración SMTP
- `config/database.php` - Conexión a base de datos

**Tablas de BD Utilizadas:**
- `usuarios` - Datos del comprador
- `eventos` - Información del evento
- `entradas` - Registro de la entrada comprada
- `ventas` - Registro de la venta
- `detalle_venta` - Relación venta-entrada

**Funciones Principales:**
1. `procesar_compra_anticipada()` - Procesa la compra
2. `enviar_comprobante_compra()` - Genera y envía el correo
3. `enviar_correo_smtp()` - Envía vía SMTP directo

**Métodos de Envío:**
1. PHP `mail()` (primario)
2. SMTP directo con Gmail (fallback)

---

## 🎯 Conclusión

El sistema de envío de correos está **completamente funcional** y configurado para:
- ✅ Enviar comprobantes automáticamente tras cada compra
- ✅ Manejar errores de forma elegante sin afectar la compra
- ✅ Registrar todos los intentos para auditoría
- ✅ Guardar respaldos si el envío falla
- ✅ Usar múltiples métodos de envío con fallback automático

El comprobante incluye toda la información necesaria para que el usuario tenga un registro completo de su compra y pueda presentarlo el día del evento.

