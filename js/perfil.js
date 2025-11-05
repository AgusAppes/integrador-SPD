/**
 * Scripts para la página de perfil
 */

document.addEventListener('DOMContentLoaded', function() {
    // Validación de DNI solo números
    const dniInput = document.getElementById('dni');
    if (dniInput) {
        dniInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
    
    // Manejo del formulario de perfil
    const perfilForm = document.getElementById('perfilForm');
    if (perfilForm) {
        perfilForm.addEventListener('submit', function(e) {
            const passwordInput = document.getElementById('contraseña');
            if (passwordInput) {
                const passwordValue = passwordInput.value.trim();
                if (passwordValue === '') {
                    // Si el campo está vacío, eliminar el name para que no se envíe en el POST
                    passwordInput.removeAttribute('name');
                    passwordInput.value = '';
                } else {
                    // Validar longitud mínima de contraseña
                    if (passwordValue.length < 6) {
                        e.preventDefault();
                        showToast('La contraseña debe tener al menos 6 caracteres', 'error');
                        return false;
                    }
                    // Si tiene valor, asegurarse de que tiene el atributo name
                    if (!passwordInput.hasAttribute('name')) {
                        passwordInput.setAttribute('name', 'contraseña');
                    }
                }
            }
        });
    }
});

