/**
 * Sistema de Modales
 * Vanilla JavaScript - Sin dependencias externas
 * 
 * Uso:
 * openModal('modalId');
 * closeModal('modalId');
 * closeAllModals();
 */

// ========== SISTEMA DE MODALES ==========


// Abrir modal
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    // Mostrar modal agregando la clase "show"
    modal.classList.add('show');
    
}

// Cerrar modal
function closeModal(modalId) {
    const modal = typeof modalId === 'string' 
        ? document.getElementById(modalId) 
        : modalId;
        
    
    // Ocultar modal removiendo la clase "show"
    modal.classList.remove('show');
    
    
    // Limpiar formularios dentro del modal
    const forms = modal.querySelectorAll('form');
    forms.forEach(form => {
        form.reset();
    });
    
}

// Cerrar todos los modales abiertos
function closeAllModals() {
    // openModals es un array que obtiene todos los elementos que tienen la clase"modal" y la clase "show"
    const openModals = document.querySelectorAll('.modal.show');
    // closeModal es una función que cierra el modal
    // modal es el elemento que se cierra
    // openModals.forEach(modal => closeModal(modal)) es un bucle que recorre el array openModals y cierra cada modal
    openModals.forEach(modal => closeModal(modal));
}


// Configuraciones para que el modal se cierre al hacer clic fuera o al presionar ESC
document.addEventListener('DOMContentLoaded', function() {
    // Cerrar modal al hacer clic fuera
    document.addEventListener('click', function(e) {
        // "e.target" es el elemento en el que se hizo clic
        // "classList.contains" es una función que verifica si el elemento tiene la clase "modal"
        if (e.target.classList.contains('modal')) {
            // Si el elemento en el que se hizo click tiene la clase "modal", se cierra el modal
            // Esto funciona asi porque solo el fondo transparente es el que tiene la clase "modal"
            // Los elementos que esten adentro del modal no tienen la clase "modal"
            // Entonces el modal se cierra al hace click en ese fondo transparente
            closeModal(e.target);
        }
    });

    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                closeModal(openModal);
            }
        }
    });
    
});
