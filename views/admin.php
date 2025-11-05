<?php
// Incluir configuración de la base de datos
require_once 'config/database.php';

// Función para obtener todos los eventos con sus fechas
function obtenerEventosCalendario() {
    try {
        $conexion = db_connection();
        
        $sql = "SELECT 
                    e.id, 
                    e.nombre, 
                    e.fecha,
                    e.cupo_total,
                    COALESCE(COUNT(CASE WHEN ent.id_estado <> 2 THEN 1 END), 0) as vendidas
                FROM eventos e
                LEFT JOIN entradas ent ON e.id = ent.id_evento
                GROUP BY e.id, e.nombre, e.fecha, e.cupo_total
                ORDER BY e.fecha ASC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error en obtenerEventosCalendario: " . $e->getMessage());
        return [];
    }
}

// Obtener eventos
$eventos = obtenerEventosCalendario();

// Convertir eventos a JSON para JavaScript
$eventosJSON = json_encode($eventos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Panel Principal</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>img/favicon.png">
    <!-- Estilos base (incluye navbar) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
    <!-- Estilos del panel de administración -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/admin.css">
    <!-- Estilos de las notificaciones toast -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/toast.css">
    <!-- Estilos de modales -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/modal.css">
</head>
<body class="admin-page">
    <!-- Container para notificaciones toast -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- Barra de navegación -->
    <?php include 'navbar.php'; ?>
    
    <!-- Contenido principal -->
    <div class="main-content">
        <div class="admin-container">
            <!-- Barra lateral -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Contenido central -->
            <div class="admin-main-content">
                <div class="calendar-container">
                    <div class="calendar-header">
                        <button class="calendar-nav-btn" id="prevMonth">
                            <span>‹</span>
                        </button>
                        <h2 class="calendar-title" id="calendarTitle">Enero 2024</h2>
                        <button class="calendar-nav-btn" id="nextMonth">
                            <span>›</span>
                        </button>
                    </div>

                    <div class="calendar-weekdays">
                        <div class="calendar-weekday">Dom</div>
                        <div class="calendar-weekday">Lun</div>
                        <div class="calendar-weekday">Mar</div>
                        <div class="calendar-weekday">Mié</div>
                        <div class="calendar-weekday">Jue</div>
                        <div class="calendar-weekday">Vie</div>
                        <div class="calendar-weekday">Sáb</div>
                </div>
                
                    <div class="calendar-days" id="calendarDays">
                        <!-- Los días se generarán con JavaScript -->
                    </div>
                    
                    <div class="calendar-legend">
                        <div class="legend-item">
                            <span class="legend-dot past"></span>
                            <span>Evento pasado</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot upcoming"></span>
                            <span>Evento próximo</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot today"></span>
                            <span>Hoy</span>
                        </div>
                    </div>
                </div>

                <!-- Modal de detalles del evento -->
                <div id="eventDetailsModal" class="modal">
                    <div class="modal-content modal-sm">
                        <div class="modal-header">
                            <h2 id="eventModalTitle">Detalles del Evento</h2>
                            <button class="close" onclick="closeEventModal()">&times;</button>
                        </div>
                        <div class="modal-body" id="eventModalBody">
                            <!-- Se llenará con JavaScript -->
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" onclick="closeEventModal()">Cerrar</button>
                    </div>
                    </div>
                </div>
        </div>
        </div>
    </div>
    
    <!-- Animaciones y funciones de las notificaciones toast -->
    <script src="<?php echo BASE_URL; ?>js/toast.js"></script>
    <!-- Animaciones y funciones de los modales -->
    <script src="<?php echo BASE_URL; ?>js/modal.js"></script>
    
    <script>
        // Datos de eventos desde PHP
        const eventos = <?php echo $eventosJSON; ?>;
        const BASE_URL = '<?php echo BASE_URL; ?>';
        
        // Variables globales del calendario
        let currentDate = new Date();
        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        
        // Función para renderizar el calendario
        function renderCalendar(month, year) {
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const calendarDays = document.getElementById('calendarDays');
            const calendarTitle = document.getElementById('calendarTitle');
            
            calendarTitle.textContent = `${meses[month]} ${year}`;
            calendarDays.innerHTML = '';
            
            // Obtener fecha actual para comparación
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // Crear mapa de eventos por fecha
            const eventsByDate = {};
            eventos.forEach(evento => {
                const eventDate = new Date(evento.fecha + 'T00:00:00');
                const dateKey = `${eventDate.getFullYear()}-${eventDate.getMonth()}-${eventDate.getDate()}`;
                if (!eventsByDate[dateKey]) {
                    eventsByDate[dateKey] = [];
                }
                eventsByDate[dateKey].push(evento);
            });
            
            // Días vacíos al inicio
            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day empty';
                calendarDays.appendChild(emptyDay);
            }
            
            // Días del mes
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                
                const currentDayDate = new Date(year, month, day);
                currentDayDate.setHours(0, 0, 0, 0);
                const dateKey = `${year}-${month}-${day}`;
                
                // Verificar si es hoy
                if (currentDayDate.getTime() === today.getTime()) {
                    dayElement.classList.add('today');
                }
                
                // Verificar si hay eventos en este día
                const dayEvents = eventsByDate[dateKey] || [];
                
                dayElement.innerHTML = `
                    <div class="day-number">${day}</div>
                    ${dayEvents.length > 0 ? `<div class="day-events-indicator">${dayEvents.length}</div>` : ''}
                `;
                
                // Agregar clase según el tipo de evento
                dayEvents.forEach(evento => {
                    const eventDate = new Date(evento.fecha + 'T00:00:00');
                    const nextDay = new Date(today);
                    nextDay.setDate(today.getDate() + 1);
                    
                    if (eventDate < today) {
                        dayElement.classList.add('has-event-past');
                    } else {
                        dayElement.classList.add('has-event-upcoming');
                    }
                });
                
                // Click handler para mostrar eventos
                if (dayEvents.length > 0) {
                    dayElement.style.cursor = 'pointer';
                    dayElement.addEventListener('click', () => {
                        showEventDetails(dayEvents, currentDayDate);
                    });
                }
                
                calendarDays.appendChild(dayElement);
            }
        }
        
        // Función para mostrar detalles de eventos
        function showEventDetails(events, date) {
            const modal = document.getElementById('eventDetailsModal');
            const modalBody = document.getElementById('eventModalBody');
            const modalTitle = document.getElementById('eventModalTitle');
            
            const dateStr = date.toLocaleDateString('es-ES', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            modalTitle.textContent = `Eventos - ${dateStr}`;
            
            let html = '<div class="events-list-modal">';
            events.forEach(evento => {
                const porcentaje = Math.round((evento.vendidas / evento.cupo_total) * 100);
                const eventDate = new Date(evento.fecha + 'T00:00:00');
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const isPast = eventDate < today;
                
                html += `
                    <div class="event-item-modal ${isPast ? 'past-event' : 'upcoming-event'}">
                        <div class="event-item-header">
                            <h3>${evento.nombre}</h3>
                            <span class="event-badge ${isPast ? 'badge-past' : 'badge-upcoming'}">
                                ${isPast ? 'Finalizado' : 'Próximo'}
                            </span>
                        </div>
                        <div class="event-item-stats">
                            <div class="stat-item">
                                <span class="stat-label">Entradas vendidas:</span>
                                <span class="stat-value">${evento.vendidas} / ${evento.cupo_total}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            modalBody.innerHTML = html;
            modal.classList.add('show');
        }
        
        // Función para cerrar modal
        function closeEventModal() {
            const modal = document.getElementById('eventDetailsModal');
            modal.classList.remove('show');
        }
        
        // Navegación de meses
        document.getElementById('prevMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar(currentDate.getMonth(), currentDate.getFullYear());
        });
        
        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar(currentDate.getMonth(), currentDate.getFullYear());
        });
        
        // Cerrar modal al hacer click fuera
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('eventDetailsModal');
            if (e.target === modal) {
                closeEventModal();
            }
        });
        
        // Renderizar calendario inicial
        document.addEventListener('DOMContentLoaded', () => {
            renderCalendar(currentDate.getMonth(), currentDate.getFullYear());
        });
        
        // Mostrar mensaje de éxito de login si existe y si es admin
        <?php if (isset($_GET['login_exitoso']) && $_GET['login_exitoso'] == '1' && $_SESSION['rol'] == 1): ?>
            showToast('¡Bienvenido Admin! Has iniciado sesión correctamente.', 'success');
        <?php endif; ?>
        <?php if (isset($_GET['login_exitoso']) && $_GET['login_exitoso'] == '1' && $_SESSION['rol'] == 2): ?>
            showToast('¡Bienvenido! Has iniciado sesión correctamente.', 'success');
        <?php endif; ?>
    </script>
    
</body>
</html>