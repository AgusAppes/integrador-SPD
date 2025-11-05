/**
 * Scripts para el CRUD de Usuarios
 */

// Validación de DNI solo números
document.addEventListener('DOMContentLoaded', function() {
    const dniInput = document.getElementById('edit_dni');
    if (dniInput) {
        dniInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
    
    // Asegurar que el campo de contraseña vacío no se envíe en el formulario
    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(e) {
            const passwordInput = document.getElementById('edit_contraseña');
            if (passwordInput) {
                const passwordValue = passwordInput.value.trim();
                if (passwordValue === '') {
                    // Si el campo está vacío, eliminar el name para que no se envíe en el POST
                    passwordInput.removeAttribute('name');
                    // También establecer el valor como vacío explícitamente
                    passwordInput.value = '';
                } else {
                    // Si tiene valor, asegurarse de que tiene el atributo name
                    if (!passwordInput.hasAttribute('name')) {
                        passwordInput.setAttribute('name', 'contraseña');
                    }
                }
            }
        });
    }
});

