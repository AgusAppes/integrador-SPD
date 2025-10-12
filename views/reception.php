<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Recepción - MALPA CLUB</title>
    <!-- Estilos base -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/reception.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/toast.css">
</head>
<body>
    <!-- Container para notificaciones toast -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- Barra de navegación -->
    <?php include 'navbar.php'; ?>

    <!-- Panel principal de recepción -->
    <main class="reception-main">
        <div class="reception-container">
            
            <!-- Selector de eventos (se muestra al inicio) -->
            <section class="event-selector" id="event-selector">
                <div class="selector-card">
                    <h1>Panel de Recepción</h1>
                    <h2>Seleccionar Evento</h2>
                    <div class="selector-form">
                        <select id="event-select" class="event-dropdown">
                            <option value="">Cargando eventos...</option>
                        </select>
                        <button type="button" id="start-reception" class="btn-start" disabled>
                            INICIAR RECEPCIÓN
                        </button>
                    </div>
                </div>
            </section>

            <!-- Header con información del evento (oculto inicialmente) -->
            <header class="event-header" id="event-header" style="display: none;">
                <h1>Panel de Recepción</h1>
                <div class="event-info">
                    <span class="event-title" id="selected-event-title">Evento Seleccionado</span>
                    <button type="button" id="change-event" class="btn-change-event">Cambiar Evento</button>
                </div>
            </header>

            <!-- Paneles principales (ocultos hasta seleccionar evento) -->
            <div class="main-panels" id="main-panels" style="display: none;">
                
                <!-- Panel de capacidad -->
                <section class="capacity-panel">
                    <div class="capacity-card">
                        <h2>📊 Capacidad del Local</h2>
                        <div class="capacity-display">
                            <div class="capacity-current">
                                <span class="capacity-number" id="current-capacity">0</span>
                                <span class="capacity-label">Personas Dentro</span>
                            </div>
                            <div class="capacity-max">
                                <span class="capacity-number" id="max-capacity">200</span>
                                <span class="capacity-label">Capacidad Máxima</span>
                            </div>
                        </div>
                        <div class="capacity-status" id="capacity-status">
                            <span class="status-indicator available"></span>
                            <span>DISPONIBLE</span>
                        </div>
                    </div>
                </section>

                <!-- Panel de escáner DNI -->
                <section class="scanner-panel">
                    <div class="scanner-card">
                        <h2>📱 Escáner de DNI</h2>
                        <!-- Campo para capturar escáner -->
                        <input 
                            type="text" 
                            id="scanner-input" 
                            class="scanner-hidden-input"
                            placeholder="🔍 Esperando código de barras..."
                            autocomplete="off"
                        >
                        
                        <!-- Resultado de la verificación -->
                        <div class="verification-result" id="verification-result" style="display: none;">
                            <!-- Se llena dinámicamente con JavaScript -->
                        </div>
                    </div>
                </section>
                
            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    <script>
        // URL base para peticiones fetch
        const BASE_URL = '<?php echo BASE_URL; ?>';
        
        // Variables globales
        let currentEvent = null;
        let events = [];

        // Cargar eventos en el selector al inicializar la página
        document.addEventListener('DOMContentLoaded', function() {
            loadEvents();
        });

        // Cargar eventos disponibles
        async function loadEvents() {
            try {
                const response = await fetch(BASE_URL + 'methods/events.php?action=get_active_events');
                const result = await response.json();
                
                if (result.success) {
                    events = result.events;
                    populateEventSelector();
                    // Después de cargar eventos, verificar si hay uno guardado
                    checkSavedEvent();
                } else {
                    showToast('Error al cargar eventos: ' + result.message, 'error');
                }
            } catch (error) {
                showToast('Error de conexión al cargar eventos', 'error');
                console.error('Error:', error);
            }
        }

        // Poblar el selector de eventos
        function populateEventSelector() {
            const eventSelect = document.getElementById('event-select');
            eventSelect.innerHTML = '<option value="">Seleccione un evento...</option>';
            
            if (events.length === 0) {
                eventSelect.innerHTML = '<option value="">No hay eventos disponibles</option>';
                return;
            }

            events.forEach(event => {
                const option = document.createElement('option');
                option.value = event.id;
                option.textContent = `${event.nombre} - ${event.fecha_formateada || event.fecha}`;
                eventSelect.appendChild(option);
            });
        }

        // Manejar selección de evento
        document.getElementById('event-select').addEventListener('change', function() {
            const selectedEventId = this.value;
            const startButton = document.getElementById('start-reception');
            
            if (selectedEventId) {
                currentEvent = events.find(event => event.id == selectedEventId);
                startButton.disabled = false;
                startButton.style.opacity = '1';
            } else {
                currentEvent = null;
                startButton.disabled = true;
                startButton.style.opacity = '0.5';
            }
        });

        // Iniciar recepción
        document.getElementById('start-reception').addEventListener('click', function() {
            if (currentEvent) {
                initializeReceptionPanel();
                focusScannerInput();
            }
        });

        // Cambiar evento - agregar evento después de que el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            const changeEventBtn = document.getElementById('change-event');
            if (changeEventBtn) {
                changeEventBtn.addEventListener('click', function() {
                    // Limpiar localStorage
                    localStorage.removeItem('selectedEvent');
                    // Volver al selector
                    document.getElementById('event-selector').style.display = 'block';
                    document.getElementById('event-header').style.display = 'none';
                    document.getElementById('main-panels').style.display = 'none';
                    // Limpiar selección
                    document.getElementById('event-select').value = '';
                    currentEvent = null;
                });
            }
        });

        // Inicializar panel de recepción
        function initializeReceptionPanel() {
            // Ocultar selector
            document.getElementById('event-selector').style.display = 'none';
            
            // Mostrar header y paneles principales
            document.getElementById('event-header').style.display = 'block';
            document.getElementById('main-panels').style.display = 'contents';
            
            // Actualizar información del evento
            document.getElementById('selected-event-title').textContent = currentEvent.nombre;
            document.getElementById('max-capacity').textContent = currentEvent.capacidad || 0;
            
            // Cargar capacidad inicial del evento
            refreshCapacity();
            
            // Guardar el evento seleccionado en localStorage
            localStorage.setItem('selectedEvent', JSON.stringify(currentEvent));
        }

        // Verificar si hay un evento guardado en localStorage
        function checkSavedEvent() {
            const savedEvent = localStorage.getItem('selectedEvent');
            
            if (savedEvent) {
                try {
                    const eventData = JSON.parse(savedEvent);
                    
                    // Verificar que el evento aún existe en la lista de eventos activos
                    if (events.length > 0) {
                        const eventExists = events.find(event => event.id == eventData.id);
                        
                        if (eventExists) {
                            currentEvent = eventData;
                            
                            // Restaurar la interfaz
                            document.getElementById('event-selector').style.display = 'none';
                            document.getElementById('event-header').style.display = 'block';
                            document.getElementById('main-panels').style.display = 'contents';
                            document.getElementById('selected-event-title').textContent = currentEvent.nombre;
                            document.getElementById('max-capacity').textContent = currentEvent.capacidad || 0;
                            // Seleccionar el evento en el dropdown
                            document.getElementById('event-select').value = currentEvent.id;
                            return;
                        }
                    }
                    // Si el evento ya no existe, limpiar localStorage
                    localStorage.removeItem('selectedEvent');
                } catch (error) {
                    console.error('Error al cargar evento guardado:', error);
                    localStorage.removeItem('selectedEvent');
                }
            }
        }

        // Función para parsear el DNI desde el código de barras
        function parseDNIBarcode(barcodeData) {
            if (!barcodeData || typeof barcodeData !== 'string') return null;

            // Normalizar comillas: reemplaza comillas curvas por comilla recta
            const normalized = barcodeData.replace(/[\u201C\u201D\u201E\u201F\u2018\u2019\u2032\u2033]/g, '"').trim();

            // Dividir por comillas y filtrar vacíos / espacios
            const parts = normalized.split('"').map(p => p.trim()).filter(p => p !== '');

            const genders = ['M', 'F', 'O'];
            let dni = null;
            let gender = null;  
            let name = null;
            let surname = null;
            let raw = barcodeData;

            // Primer intento: usar el split por comillas
            for (let i = 0; i < parts.length; i++) {
                const p = parts[i].toUpperCase();
                if (p.length === 1 && genders.includes(p)) {
                    gender = p;
                    // siguiente elemento (si existe) suele ser el DNI
                    if (i + 1 < parts.length) {
                        const possibleDni = parts[i + 1].replace(/\D/g, ''); // sólo dígitos
                        if (possibleDni.length >= 6 && possibleDni.length <= 9) {
                            dni = possibleDni;
                        }
                    }
                    // intentar extraer nombre y apellido (por convención: el anterior al genero suele ser nombre completo)
                    if (i - 1 >= 0) {
                        name = parts[i - 1];
                    }
                    // apellido o código al principio (opcional)
                    if (parts.length > 2) {
                        surname = parts[1] || null;
                    }
                    break;
                }
            }

            // Fallback 1: si no encontramos por comillas, usar regex para buscar "LETRA"+"NUMEROS"
            if (!dni) {
                // Busca una letra de genero seguida de no dígitos y luego una secuencia de 6-9 dígitos
                const regex = /([MFObmfO])[^0-9]{0,4}([0-9]{6,9})/;
                const m = normalized.match(regex);
                if (m) {
                    gender = (m[1] || gender || null).toUpperCase();
                    dni = m[2];
                }
            }

            // Fallback 2: si aún no hay DNI, intentar extraer el número de mayor longitud en la cadena
            if (!dni) {
                const allNums = normalized.match(/\d{6,9}/g);
                if (allNums && allNums.length) {
                    // elegir el que tenga mayor longitud o el último (ajusta según tus datos)
                    dni = allNums.reduce((a,b) => a.length>=b.length ? a : b);
                }
            }

            if (!dni) return null;

            return {
                raw,
                dni,
                gender,
                name,
                surname,
                parts
            };
        }

        // --- Integración con el input scanner ---
        const scannerInput = document.getElementById('scanner-input');
        const verificationResult = document.getElementById('verification-result');

        // Opcional: enfocar el input al iniciar la recepción
        function focusScannerInput() {
            setTimeout(() => {
                try { scannerInput.focus(); } catch (e) {}
            }, 100);
        }


        // Manejo cuando el scanner envía Enter (lo normal)
        scannerInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const value = this.value.trim();
                if (!value) return;
                const parsed = parseDNIBarcode(value);
                displayParsedResult(parsed);
                this.value = ''; // limpiar para el siguiente escaneo
            }
        });

        // Manejo de pegado (por si el scanner pega o si pruebas manualmente)
        scannerInput.addEventListener('paste', function(e) {
            setTimeout(() => {
                const value = this.value.trim();
                const parsed = parseDNIBarcode(value);
                displayParsedResult(parsed);
                this.value = '';
            }, 50);
        });

        // Función para mostrar el resultado en la UI
        function displayParsedResult(parsed) {
            if (!parsed) {
                verificationResult.style.display = 'block';
                verificationResult.innerHTML = `<div class="verification-error">Código no reconocido o no contiene DNI.</div>`;
                return;
            }

            // Mostrar datos parseados temporalmente
            verificationResult.style.display = 'block';
            verificationResult.innerHTML = `
                <div class="verification-card">
                    <div><strong>DNI:</strong> ${parsed.dni}</div>
                    <div><strong>Género:</strong> ${parsed.gender || 'N/A'}</div>
                    <div><strong>Nombre:</strong> ${parsed.name || 'N/A'}</div>
                    <div><strong>Apellido:</strong> ${parsed.surname || 'N/A'}</div>
                    <div class="processing">Procesando...</div>
                </div>
            `;

            // Enviar datos al backend para procesamiento
            processDNIWithBackend(parsed.raw);
        }

        // Función para procesar DNI con el backend
        async function processDNIWithBackend(dniData) {
            try {
                console.log('Enviando datos al backend:', { 
                    action: 'scan_dni', 
                    dni_data: dniData,
                    event_id: currentEvent ? currentEvent.id : null
                });
                
                const response = await fetch(BASE_URL + 'methods/reception.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'scan_dni',
                        dni_data: dniData,
                        event_id: currentEvent ? currentEvent.id : null
                    })
                });

                console.log('Respuesta del servidor:', response.status, response.statusText);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                console.log('Resultado del backend:', result);

                if (result.success) {
                    displayBackendResult(result);
                } else {
                    displayError(result.message);
                }
            } catch (error) {
                console.error('Error completo:', error);
                displayError(`Error de conexión: ${error.message}`);
            }
        }

        // Función para mostrar resultado del backend
        function displayBackendResult(result) {
            const user = result.user;
            const action = result.action;
            const ticket = result.ticket;
            
            let statusClass = action === 'found' ? 'success' : 'created';
            let statusText = action === 'found' ? 'Usuario Encontrado' : 'Usuario Creado';
            
            // Determinar el estado de la entrada y botones a mostrar
            let ticketStatus = '';
            let ticketClass = '';
            let showRegisterButton = false;
            let showSellButton = false;
            let showExitButton = false;
            let ticketState = ticket ? ticket.ticket_state : null;
            
            if (ticket) {
                if (ticket.has_ticket) {
                    // Estado 1: Vendida - puede ingresar
                    ticketStatus = '✅ ENTRADA VÁLIDA';
                    ticketClass = 'ticket-valid';
                    showRegisterButton = true;
                } else {
                    // Otros estados
                    if (ticketState === 2) {
                        // Estado 2: Cancelada - puede vender
                        ticketStatus = '❌ ENTRADA CANCELADA';
                        ticketClass = 'ticket-invalid';
                        showSellButton = true;
                    } else if (ticketState === 3) {
                        // Estado 3: Consumida - está dentro, puede salir
                        ticketStatus = '🟢 CLIENTE DENTRO';
                        ticketClass = 'ticket-consumed';
                        showExitButton = true;
                    } else {
                        // Otros estados
                        ticketStatus = '❌ ' + ticket.message;
                        ticketClass = 'ticket-invalid';
                        showSellButton = true; // Puede vender entrada si tiene estado inválido
                    }
                }
            } else {
                ticketStatus = '⚠️ NO SE VERIFICÓ ENTRADA';
                ticketClass = 'ticket-unchecked';
                showSellButton = true; // Puede vender entrada si no tiene ninguna
            }
            
            verificationResult.innerHTML = `
                <div class="verification-card ${statusClass}">
                    <div class="ticket-status ${ticketClass}">
                        <strong>${ticketStatus}</strong>
                    </div>
                    <div class="verification-content">
                        <div class="verification-column">
                            <div class="info-section">
                                <div><strong>DNI:</strong> ${user.dni}</div>
                                <div><strong>Nombre:</strong> ${user.nombre} ${user.apellido}</div>
                            </div>
                        </div>
                        <div class="verification-column">
                            <div class="info-section">
                                ${ticket && ticket.ticket ? `
                                    <div class="ticket-details">
                                        <div><strong>Serie:</strong> ${ticket.ticket.nro_serie}</div>
                                        <div><strong>Precio:</strong> $${ticket.ticket.precio}</div>
                                        <div><strong>Tipo:</strong> ${ticket.ticket.tipo_entrada_nombre}</div>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    ${showRegisterButton || showSellButton || showExitButton ? `
                        <div class="action-buttons">
                            ${showRegisterButton ? `
                                <button type="button" class="btn-register-ingress" onclick="registerIngress('${user.id}', '${ticket && ticket.ticket ? ticket.ticket.id : ''}')">
                                    ➡️ REGISTRAR INGRESO
                                </button>
                            ` : ''}
                            ${showSellButton ? `
                                <button type="button" class="btn-sell-ticket" onclick="sellTicket('${user.id}', '${ticket && ticket.ticket ? ticket.ticket.id : ''}')">
                                    VENDER ENTRADA
                                </button>
                            ` : ''}
                            ${showExitButton ? `
                                <button type="button" class="btn-register-exit" onclick="registerExit('${user.id}', '${ticket && ticket.ticket ? ticket.ticket.id : ''}')">
                                    ⬅️ REGISTRAR SALIDA
                                </button>
                            ` : ''}
                        </div>
                    ` : ''}
                </div>
            `;
        }

        // Función para mostrar errores
        function displayError(message) {
            verificationResult.innerHTML = `
                <div class="verification-error">
                    <strong>Error:</strong> ${message}
                </div>
            `;
        }

        // Función para registrar ingreso
        async function registerIngress(userId, ticketId) {
            console.log('Registrando ingreso para usuario:', userId, 'entrada:', ticketId);
            
            if (!currentEvent) {
                showToast('No hay evento seleccionado', 'error');
                return;
            }
            
            try {
                const response = await fetch(BASE_URL + 'methods/reception.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'register_entry',
                        user_id: userId,
                        ticket_id: ticketId,
                        event_id: currentEvent.id
                    })
                });

                const result = await response.json();
                console.log('Resultado del registro de ingreso:', result);

                if (result.success) {
                    showToast(result.message, 'success');
                    // Actualizar capacidad en tiempo real
                    if (result.capacity) {
                        updateCapacityDisplay(result.capacity);
                    }
                    // Limpiar resultado de verificación para permitir nuevo escaneo
                    document.getElementById('verification-result').style.display = 'none';
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error al registrar ingreso:', error);
                showToast('Error de conexión al registrar ingreso', 'error');
            }
        }

        // Función para vender entrada
        function sellTicket(userId, ticketId) {
            console.log('Vendiendo entrada para usuario:', userId, 'entrada:', ticketId);
            
            if (ticketId && ticketId !== '') {
                // Tiene entrada existente - cambiar estado a vendida
                showToast('Reactivando entrada existente - Pendiente de implementar', 'info');
                // TODO: Implementar lógica para cambiar estado de entrada a "vendida" (1)
            } else {
                // No tiene entrada - crear nueva venta
                showToast('Vendiendo nueva entrada - Pendiente de implementar', 'info');
                // TODO: Implementar lógica para crear nueva entrada con estado "vendida" (1)
            }
        }

        // Función para registrar salida
        async function registerExit(userId, ticketId) {
            console.log('Registrando salida para usuario:', userId, 'entrada:', ticketId);
            
            if (!currentEvent) {
                showToast('No hay evento seleccionado', 'error');
                return;
            }
            
            try {
                const response = await fetch(BASE_URL + 'methods/reception.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'register_exit',
                        user_id: userId,
                        ticket_id: ticketId,
                        event_id: currentEvent.id
                    })
                });

                const result = await response.json();
                console.log('Resultado del registro de salida:', result);

                if (result.success) {
                    showToast(result.message, 'success');
                    // Actualizar capacidad en tiempo real
                    if (result.capacity) {
                        updateCapacityDisplay(result.capacity);
                    }
                    // Limpiar resultado de verificación para permitir nuevo escaneo
                    document.getElementById('verification-result').style.display = 'none';
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error al registrar salida:', error);
                showToast('Error de conexión al registrar salida', 'error');
            }
        }

        // Función para actualizar la visualización de capacidad
        function updateCapacityDisplay(capacityData) {
            if (capacityData && capacityData.success) {
                document.getElementById('current-capacity').textContent = capacityData.current_capacity;
                document.getElementById('max-capacity').textContent = capacityData.max_capacity;
                
                // Actualizar indicador de estado
                const statusIndicator = document.querySelector('.status-indicator');
                const statusText = document.querySelector('#capacity-status span:last-child');
                
                if (capacityData.current_capacity >= capacityData.max_capacity) {
                    statusIndicator.className = 'status-indicator full';
                    statusText.textContent = 'COMPLETO';
                } else if (capacityData.current_capacity >= capacityData.max_capacity * 0.8) {
                    statusIndicator.className = 'status-indicator warning';
                    statusText.textContent = 'PRÓXIMO A COMPLETO';
                } else {
                    statusIndicator.className = 'status-indicator available';
                    statusText.textContent = 'DISPONIBLE';
                }
            }
        }

        // Función para obtener capacidad actualizada
        async function refreshCapacity() {
            if (!currentEvent) return;
            
            try {
                const response = await fetch(BASE_URL + 'methods/reception.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'check_capacity',
                        event_id: currentEvent.id
                    })
                });

                const result = await response.json();
                if (result.success) {
                    updateCapacityDisplay(result);
                }
            } catch (error) {
                console.error('Error al actualizar capacidad:', error);
            }
        }

        // Helper para evitar XSS si muestras raw
        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // Enfocar automáticamente el campo para que el scanner "escriba" ahí
        document.addEventListener('DOMContentLoaded', () => {
            // si ya se inició recepción, enfocamos; si no, enfocamos cuando el panel esté visible
            focusScannerInput();
            
            // Botón de prueba de conexión
            const testBtn = document.getElementById('test-connection');
            if (testBtn) {
                testBtn.addEventListener('click', async function() {
                    try {
                        const response = await fetch(BASE_URL + 'methods/test_connection.php');
                        const result = await response.json();
                        
                        if (result.success) {
                            displayError('✅ Conexión exitosa: ' + result.message);
                        } else {
                            displayError('❌ Error de conexión: ' + result.message);
                        }
                    } catch (error) {
                        displayError('❌ Error de conexión: ' + error.message);
                    }
                });
            }
        });

    </script>
</body>
</html>