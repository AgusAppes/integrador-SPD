/**
 * Sistema de Notificaciones Toast
 * Vanilla JavaScript - Sin dependencias externas
 * 
 * Uso:
 * showSuccess('Mensaje de éxito');
 * showError('Mensaje de error');
 * showWarning('Mensaje de advertencia');
 * showInfo('Mensaje informativo');
 */

// ========== SISTEMA DE NOTIFICACIONES TOAST ==========

// Iconos y títulos para cada tipo
const TOAST_ICONS = {
    success: '✓',
    error: '✕',
    warning: '⚠',
    info: 'ℹ'
};

const TOAST_TITLES = {
    success: 'Éxito',
    error: 'Error',
    warning: 'Advertencia',
    info: 'Información'
};

// Crear notificación toast
function showToast(message, type = 'info', title = '') {
    // Buscar o crear container
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    // Crear elemento toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Usar título por defecto si no se especifica
    const finalTitle = title || TOAST_TITLES[type];
    
    toast.innerHTML = `
        <div class="toast-icon">${TOAST_ICONS[type]}</div>
        <div class="toast-content">
            <div class="toast-title">${finalTitle}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="closeToast(this.parentElement)">&times;</button>
        <div class="toast-progress"></div>
    `;
    
    // Agregar al container
    container.appendChild(toast);
    
    // Mostrar con animación
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Configurar barra de progreso
    const progressBar = toast.querySelector('.toast-progress');
    progressBar.style.width = '100%';
    
    // Animar barra de progreso (5 segundos)
    setTimeout(() => {
        progressBar.style.transition = 'width 5000ms linear';
        progressBar.style.width = '0%';
    }, 200);
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => closeToast(toast), 5000);
    
    return toast;
}

// Cerrar notificación toast
function closeToast(toast) {
    if (!toast || !toast.parentElement) return;
    // remueve la clase "show" del toast
    toast.classList.remove('show');
    
    // agrega la clase "hide" al toast
    toast.classList.add('hide');
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.parentElement.removeChild(toast);
        }
    }, 300);
}

// Funciones específicas para cada tipo

// message es el mensaje que se mostrará en el toast
// title es el título que se mostrará en el toast

// showSuccess es una función que muestra un toast de éxito
function showSuccess(message, title = '') {
    return showToast(message, 'success', title);
}

// showError es una función que muestra un toast de error
function showError(message, title = '') {
    return showToast(message, 'error', title);
}

// showWarning es una función que muestra un toast de advertencia
function showWarning(message, title = '') {
    return showToast(message, 'warning', title);
}

// showInfo es una función que muestra un toast de información
function showInfo(message, title = '') {
    return showToast(message, 'info', title);
}

// ========== PROCESAMIENTO AUTOMÁTICO DE MENSAJES URL ==========
// Este código se ejecuta cuando la página termina de cargar completamente
// Su función es detectar mensajes que vienen desde PHP a través de parámetros en la URL y convertirlos en notificaciones toast
document.addEventListener('DOMContentLoaded', function() {
    
    // URLSearchParams es una API de JavaScript que permite leer parámetros de la URL
    const urlParams = new URLSearchParams(window.location.search);
    
    // CASO 1: Detectar mensajes de éxito
    // PHP redirige con: header('Location: admin.php?success=mensaje_de_exito');
    // JavaScript detecta el parámetro y muestra toast verde
    if (urlParams.get('success')) {
        // Mostrar notificación de éxito con icono verde ✓
        // El mensaje viene directamente desde PHP
        showSuccess(urlParams.get('success'));
        
        // Limpiar la URL para que quede sin parámetros
        // Cambia de: "admin.php?success=Evento creado correctamente" a: "admin.php"
        // replaceState modifica la URL sin recargar la página
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    // CASO 2: Detectar mensajes de error
    // PHP redirige con: header('Location: admin.php?error=El campo nombre es requerido');
    // JavaScript detecta el parámetro y muestra toast rojo con el mensaje específico
    if (urlParams.get('error')) {
        // urlParams.get('error') obtiene el mensaje de error específico que envió PHP
        // Mostrar notificación de error con icono rojo ✕
        showError(urlParams.get('error'));
        
        // Limpiar la URL para que quede sin parámetros
        // Cambia de: "admin.php?error=El%20campo%20nombre%20es%20requerido" a: "admin.php"
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
