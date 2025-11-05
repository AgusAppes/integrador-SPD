# 🔧 DIAGNÓSTICO: Problemas con Envío de Correos

## ❌ PROBLEMAS IDENTIFICADOS

### 1. **Error en Protocolo SMTP - STARTTLS**
**Error en logs:**
```
Error en STARTTLS: 250-SIZE 35882577
```

**Causa:**
- Gmail responde con múltiples líneas cuando se envía `EHLO`
- El código estaba interpretando mal la respuesta de `STARTTLS`
- No estaba leyendo todas las líneas de respuesta SMTP

**Solución aplicada:**
- ✅ Función `leer_respuesta_smtp()` para leer respuestas multilínea
- ✅ Corrección del protocolo: EHLO → STARTTLS → EHLO
- ✅ Mejor manejo de respuestas del servidor

### 2. **Error de Autenticación**
**Posibles causas:**
- Contraseña de aplicación incorrecta o expirada
- Verificación en 2 pasos no habilitada
- Cuenta bloqueada por seguridad

### 3. **Errores Reportados por Usuario**

#### Compras de Entradas:
- ❌ "El correo no pudo enviarse, pero tu compra está confirmada"
- ❌ Los correos NO llegan a la bandeja de entrada

#### Recuperación de Contraseña:
- ⚠️ Muestra "El correo ha sido enviado"
- ❌ Pero NO llega ningún correo

## 🔍 PASOS PARA DIAGNOSTICAR

### 1. Verificar Logs de PHP
```bash
tail -f /opt/lampp/logs/php_error_log
```

### 2. Verificar Configuración SMTP
- ✅ Archivo `config/email_config.php` existe
- ✅ Credenciales correctas
- ✅ Contraseña de aplicación válida

### 3. Probar Envío Manual
Crear un script de prueba simple para verificar la conexión SMTP.

## 🛠️ CORRECCIONES APLICADAS

1. **Función `leer_respuesta_smtp()`:**
   - Lee todas las líneas de respuesta SMTP
   - Maneja respuestas multilínea correctamente

2. **Protocolo SMTP Corregido:**
   - EHLO inicial (sin TLS)
   - STARTTLS
   - EHLO después de TLS
   - Autenticación LOGIN
   - Envío del correo

3. **Mejor Logging:**
   - Logs más detallados en cada paso
   - Identificación clara de errores

## ⚠️ PRÓXIMOS PASOS

1. **Verificar Contraseña de Aplicación:**
   - Ir a: https://myaccount.google.com/apppasswords
   - Verificar que la contraseña sigue siendo válida
   - Generar una nueva si es necesario

2. **Verificar Configuración de Gmail:**
   - Verificación en 2 pasos habilitada
   - "Permitir aplicaciones menos seguras" (si aplica)
   - No hay restricciones de seguridad

3. **Probar con Script de Diagnóstico:**
   - Crear script independiente para probar conexión SMTP
   - Verificar que la conexión funciona antes de usar en producción

## 📝 NOTAS IMPORTANTES

- El sistema está configurado para NO fallar si el correo no se envía
- Las compras se confirman aunque el correo falle
- Los comprobantes se guardan en `logs/comprobantes_html/` como respaldo
- Los logs de errores están en los logs de PHP

