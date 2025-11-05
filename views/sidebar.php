<!-- Barra lateral del panel de administración -->
<div class="admin-sidebar">
    <h2>Panel de Administración</h2>
    
    <nav class="sidebar-nav">
        <ul class="sidebar-menu">
            <!-- Sección de Eventos -->
            <li class="sidebar-menu-item" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?page=admin-eventos'">
                <span class="menu-text">Gestión de Eventos</span>
            </li>
            
            <!-- Sección de Ventas -->
            <li class="sidebar-menu-item" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?page=admin-ventas'">
                <span class="menu-text">Gestión de Ventas</span>
            </li>
            
            <!-- Sección de Usuarios -->
            <li class="sidebar-menu-item" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?page=admin-usuarios'">
                <span class="menu-text">Gestión de Usuarios</span>
            </li>
        </ul>
    </nav>
</div>

