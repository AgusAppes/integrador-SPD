
## 🚀 Instalación

### 1. Clonar o Descargar el Proyecto

```bash
# Si usas Git
repo: https://github.com/AgusAppes/integrador-SPD

# O descomprime el archivo ZIP en tu directorio
```

### 2. Configurar Servidor Local

**XAMPP/WAMP/LARAGON**
- Coloca el proyecto en la carpeta `htdocs` (XAMPP) o `www` (WAMP/Laragon)
- Ruta recomendada: `C:\laragon\www\integrador-SPD\`

## 🗄️ Configuración de Base de Datos

### 3. Crear Base de Datos

Importar la base de datos desde el backup proporcionado


## ⚙️ Configuración del Proyecto

### 1. Crear Archivo de Configuración

**En Windows:**
```cmd
copy config\config.example.php config\config.php
```

**En Linux/Mac:**
```bash
cp config/config.example.php config/config.php
```

### 2. Editar Configuración

Abre `config/config.php` y modifica los valores según tu entorno:

```php
<?php
// Constantes para la conexión a la base de datos
define('DB_HOST', 'localhost');           // Tu servidor MySQL
define('DB_NAME', 'dni_pass');           // Nombre de tu base de datos
define('DB_USER', 'root');               // Tu usuario MySQL
define('DB_PASS', '');                   // Tu contraseña MySQL

// Constante para el nombre del sitio
define('SITE_NAME', 'Sistema Ticket');

// Constante para la URL base del sitio - ¡MUY IMPORTANTE!
define('BASE_URL', 'http://localhost/integrador-SPD/');  // Ajusta según tu configuración

// Zona horaria
define('TIMEZONE', 'America/Argentina/Buenos_Aires');
?>
```

### 3. Configurar Permisos de Directorio

Asegúrate de que el directorio `images/eventos/` tenga permisos de escritura:

**En Linux/Mac:**
```bash
chmod 755 images/eventos/
```

**En Windows:** Click derecho en la carpeta → Propiedades → Seguridad → Editar permisos




## 🔧 Resolución de Problemas

### Error "Cannot connect to database"
1. Verifica que MySQL esté ejecutándose
2. Confirma los datos en `config/config.php`
3. Asegúrate de que la base de datos `dni_pass` exista

### Error "Page not found" o estilos no cargan
1. Verifica que `BASE_URL` en `config/config.php` sea correcto
2. Debe terminar con `/` (slash)
3. Ejemplo: `http://localhost/integrador-SPD/`

### Error al subir imágenes
1. Verifica permisos en `images/eventos/`
2. Confirma que la extensión GD esté instalada: `php -m | grep -i gd`

