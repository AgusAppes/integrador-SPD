# 🚀 Cómo Subir Cambios a GitHub

## ✅ Estado Actual

Tu commit está listo y contiene:
- ✅ Mejoras en UI (fuentes Poppins)
- ✅ Corrección de inputs azules en admin
- ✅ Favicon de Malpa Club
- ✅ Corrección de protocolo SMTP
- ✅ Corrección de tokens de recuperación
- ✅ Reportes y documentación
- ✅ Archivos protegidos (email_config.php NO está incluido)

## 📤 Para Hacer Push a GitHub

### Opción 1: Desde la Terminal (Recomendado)

**Paso 1:** Abre una terminal y ejecuta:
```bash
cd /opt/lampp/htdocs/integrador-SPD-main
git push origin master
```

**Paso 2:** Cuando te pida credenciales:
- **Username:** Tu usuario de GitHub (ej: `AgusAppes` o `tiarabenitez`)
- **Password:** Tu **Personal Access Token** (NO tu contraseña normal)

### Opción 2: Crear un Personal Access Token

Si no tienes un token:

1. Ve a: https://github.com/settings/tokens
2. Click en **"Generate new token (classic)"**
3. Nombre: `integrador-SPD`
4. Expiración: 90 días (o el tiempo que prefieras)
5. Permisos: Marca **`repo`** (todos los permisos)
6. Click en **"Generate token"**
7. **COPIA EL TOKEN** (solo se muestra una vez)
8. Úsalo como contraseña cuando hagas `git push`

### Opción 3: Usar GitHub Desktop

1. Abre GitHub Desktop
2. Ve al repositorio `integrador-SPD-main`
3. Verás el commit pendiente
4. Click en **"Push origin"**

## 🔒 Seguridad

**IMPORTANTE:** El archivo `config/email_config.php` con tus credenciales **NO está incluido** en el commit gracias a `.gitignore`. Esto protege tu información sensible.

## 📋 Resumen del Commit

```
Commit: Mejoras en UI, corrección de emails y sistema de recuperación de contraseña

Archivos incluidos:
- DIAGNOSTICO_CORREOS.md
- REPORTE_SISTEMA_CORREOS.md
- GIT_PUSH_INSTRUCTIONS.md
- database/password_resets.sql
- views/forgot-password.php
- views/reset-password.php
- .gitignore (actualizado)
```

## ⚠️ Si Tienes Problemas

Si el push falla, verifica:
1. ✅ Tienes acceso al repositorio
2. ✅ El token tiene permisos `repo`
3. ✅ Estás en la rama correcta (`master`)

## 🎯 Comando Final

```bash
git push origin master
```

¡Listo! 🎉

