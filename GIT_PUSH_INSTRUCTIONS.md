# 📤 Instrucciones para Subir Cambios a GitHub

## ✅ Estado Actual

Los cambios han sido commiteados localmente. El commit incluye:
- ✅ Nuevos archivos: reportes, configuración de email, vistas de recuperación de contraseña
- ✅ Archivos modificados: estilos CSS, funciones de email, validación de tokens

## 📋 Pasos para Subir a GitHub

### Opción 1: Usando HTTPS (requiere autenticación)

1. **Si tienes un Personal Access Token de GitHub:**
   ```bash
   git push origin master
   ```
   Cuando te pida credenciales:
   - Username: tu_usuario_de_github
   - Password: tu_personal_access_token (no tu contraseña normal)

2. **Si no tienes un token, puedes crear uno:**
   - Ve a: https://github.com/settings/tokens
   - Click en "Generate new token (classic)"
   - Selecciona los permisos: `repo` (todos los permisos)
   - Copia el token y úsalo como contraseña

### Opción 2: Configurar SSH (recomendado para el futuro)

1. **Generar clave SSH (si no tienes una):**
   ```bash
   ssh-keygen -t ed25519 -C "tu_email@example.com"
   ```

2. **Agregar la clave a GitHub:**
   - Copia el contenido de `~/.ssh/id_ed25519.pub`
   - Ve a: https://github.com/settings/keys
   - Click en "New SSH key" y pega la clave

3. **Cambiar el remote a SSH:**
   ```bash
   git remote set-url origin git@github.com:AgusAppes/integrador-SPD.git
   ```

4. **Hacer push:**
   ```bash
   git push origin master
   ```

### Opción 3: Usar GitHub Desktop o IDE

Si prefieres usar una interfaz gráfica:
- GitHub Desktop
- VS Code con extensión de Git
- Cualquier IDE con soporte Git

## 🔍 Verificar el Estado

Para ver qué commits están listos para subir:
```bash
git log origin/master..master
```

## ⚠️ Nota Importante

**NO subir `config/email_config.php` a GitHub** si contiene credenciales reales.

Recomendación: Agregar a `.gitignore`:
```
config/email_config.php
```

Y asegurarse de que solo `email_config.example.php` esté en el repositorio.

## 📝 Resumen de Cambios Commiteados

- Mejoras en UI (fuentes, estilos)
- Corrección de sistema de correos SMTP
- Corrección de recuperación de contraseña
- Nuevos reportes y documentación
- Vistas de recuperación de contraseña

