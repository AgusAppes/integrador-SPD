# 📧 Configuración de Email para Tu Compañera

## ⚠️ IMPORTANTE: Seguridad y Mejores Prácticas

### Opción 1: Usar Sus Propias Credenciales (RECOMENDADO) ✅

**Es la mejor opción por seguridad:**

1. Tu compañera debe crear su propia cuenta de Gmail (o usar una existente)
2. Debe crear su propia contraseña de aplicación en Google
3. Configurar `email_config.php` con SUS propias credenciales

**Ventajas:**
- ✅ Mayor seguridad (cada uno tiene sus propias credenciales)
- ✅ Independencia (si alguien cambia algo, no afecta al otro)
- ✅ Mejor para auditoría (sabes quién envió qué correo)
- ✅ Si una cuenta se bloquea, la otra sigue funcionando

### Opción 2: Compartir Credenciales (NO RECOMENDADO) ⚠️

**Técnicamente funciona, pero tiene riesgos:**

Si decides compartir tus credenciales:
- ✅ Funcionará para ambos
- ❌ Menos seguro (más personas con acceso)
- ❌ Si alguien cambia la contraseña, ambos dejan de funcionar
- ❌ Más difícil de rastrear quién hizo qué
- ❌ Si hay problemas de seguridad, afecta a ambos

## 📋 Pasos para Tu Compañera

### Paso 1: Después de Hacer `git pull`

Cuando tu compañera descargue los cambios, debe:

1. **Copiar el archivo de ejemplo:**
   ```bash
   cp config/email_config.example.php config/email_config.php
   ```

2. **Editar el archivo `config/email_config.php`** con sus credenciales

### Paso 2: Crear Contraseña de Aplicación en Google

Si tu compañera usa **SU propia cuenta de Gmail**:

1. Ir a: https://myaccount.google.com/apppasswords
2. Seleccionar "Correo" y "Otro (nombre personalizado)"
3. Escribir: "MALPA CLUB Sistema"
4. Generar la contraseña
5. Copiar la contraseña (16 caracteres, con espacios)

### Paso 3: Configurar email_config.php

Abrir `config/email_config.php` y actualizar:

```php
<?php
// Configuración SMTP de Gmail
define('EMAIL_SMTP_HOST', 'smtp.gmail.com');
define('EMAIL_SMTP_PORT', 587);
define('EMAIL_SMTP_SECURE', 'tls');

// OPCIÓN A: Si usa SUS propias credenciales (RECOMENDADO)
define('EMAIL_SMTP_USER', 'su_correo@gmail.com'); // Su correo
define('EMAIL_SMTP_PASS', 'xxxx xxxx xxxx xxxx'); // Su contraseña de aplicación

// OPCIÓN B: Si compartes tus credenciales
// define('EMAIL_SMTP_USER', 'tiarasb689@gmail.com');
// define('EMAIL_SMTP_PASS', 'etjt xvft wkfy gblw');

define('EMAIL_FROM_NAME', 'Malpa Eventos');
define('EMAIL_FROM_ADDRESS', 'su_correo@gmail.com'); // Debe ser el mismo que EMAIL_SMTP_USER

define('USE_PHP_MAIL', false);
?>
```

## 🔐 Seguridad

**IMPORTANTE:**
- ❌ **NO** subir `email_config.php` a GitHub (ya está en `.gitignore`)
- ✅ Cada desarrollador debe tener su propio archivo local
- ✅ Si compartes credenciales, hazlo por un canal seguro (no por email/chat)
- ✅ Considera cambiar la contraseña de aplicación periódicamente

## 📝 Resumen

**Para tu compañera:**

1. ✅ Hacer `git pull` para descargar cambios
2. ✅ Copiar `email_config.example.php` a `email_config.php`
3. ✅ Configurar con SUS propias credenciales (o las compartidas si prefieres)
4. ✅ Probar enviando un correo de prueba

**Nota:** El archivo `email_config.php` NO está en GitHub, así que cada uno debe crearlo localmente.

