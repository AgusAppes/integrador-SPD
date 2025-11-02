<!-- Barra lateral del panel de administración -->
<div class="admin-sidebar">
    <h2>Panel de Administración</h2>
    
    <nav class="sidebar-nav">
        <ul class="sidebar-menu">
            <!-- Sección de Eventos -->
            <li class="sidebar-menu-item" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?page=admin-eventos'">
                <span class="menu-text">Gestion de ventos</span>
            </li>
            
            <!-- Futuras secciones -->
            <li class="sidebar-menu-item disabled">
                <span class="menu-text">Ventas</span>
            </li>
            <li class="sidebar-menu-item disabled">
                <span class="menu-text">Usuarios</span>
            </li>
        </ul>
    </nav>
</div>

