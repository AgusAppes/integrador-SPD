/**
 * Scripts para la página de pago
 */

document.addEventListener('DOMContentLoaded', function() {
    // Formatear número de tarjeta (agregar espacios cada 4 dígitos)
    const numeroTarjeta = document.getElementById('numero_tarjeta');
    if (numeroTarjeta) {
        numeroTarjeta.addEventListener('input', function(e) {
            // Eliminar todo excepto números
            let value = this.value.replace(/\D/g, '');
            
            // Agregar espacios cada 4 dígitos
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            
            // Limitar a 16 dígitos
            if (value.length > 16) {
                value = value.slice(0, 16);
                formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            }
            
            this.value = formattedValue;
        });
    }
    
    // Formatear fecha de vencimiento (MM/AA)
    const fechaVencimiento = document.getElementById('fecha_vencimiento');
    if (fechaVencimiento) {
        fechaVencimiento.addEventListener('input', function(e) {
            // Eliminar todo excepto números
            let value = this.value.replace(/\D/g, '');
            
            // Agregar barra después de 2 dígitos
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            
            // Limitar a 5 caracteres (MM/AA)
            if (value.length > 5) {
                value = value.slice(0, 5);
            }
            
            this.value = value;
        });
        
        // Validar mes (01-12)
        fechaVencimiento.addEventListener('blur', function() {
            const value = this.value;
            if (value.length >= 2) {
                const mes = parseInt(value.substring(0, 2));
                if (mes < 1 || mes > 12) {
                    this.setCustomValidity('El mes debe ser entre 01 y 12');
                } else {
                    this.setCustomValidity('');
                }
            }
        });
    }
    
    // Validar CVV (solo números, 3 o 4 dígitos)
    const cvv = document.getElementById('cvv');
    if (cvv) {
        cvv.addEventListener('input', function(e) {
            // Eliminar todo excepto números
            this.value = this.value.replace(/\D/g, '');
            
            // Limitar a 4 dígitos
            if (this.value.length > 4) {
                this.value = this.value.slice(0, 4);
            }
        });
    }
    
    // Validar nombre del titular (solo letras y espacios)
    const nombreTitular = document.getElementById('nombre_titular');
    if (nombreTitular) {
        nombreTitular.addEventListener('input', function(e) {
            // Permitir solo letras, espacios y algunos caracteres especiales comunes en nombres
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s'-]/g, '');
        });
    }
    
    // Validar formulario antes de enviar
    const pagoForm = document.getElementById('pagoForm');
    if (pagoForm) {
        pagoForm.addEventListener('submit', function(e) {
            // Validar número de tarjeta (debe tener 16 dígitos)
            const numeroTarjetaValue = numeroTarjeta.value.replace(/\D/g, '');
            if (numeroTarjetaValue.length !== 16) {
                e.preventDefault();
                showToast('El número de tarjeta debe tener 16 dígitos', 'error');
                numeroTarjeta.focus();
                return false;
            }
            
            // Validar fecha de vencimiento
            const fechaValue = fechaVencimiento.value;
            if (fechaValue.length !== 5) {
                e.preventDefault();
                showToast('La fecha de vencimiento debe tener el formato MM/AA', 'error');
                fechaVencimiento.focus();
                return false;
            }
            
            // Validar CVV (debe tener 3 o 4 dígitos)
            const cvvValue = cvv.value;
            if (cvvValue.length < 3 || cvvValue.length > 4) {
                e.preventDefault();
                showToast('El CVV debe tener 3 o 4 dígitos', 'error');
                cvv.focus();
                return false;
            }
            
            // Si todo está bien, el formulario se enviará normalmente
        });
    }
});

