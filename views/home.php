<?php
// Verificar si el usuario está logueado
$usuario_logueado = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Tickets</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <link rel="stylesheet" href="../css/toast.css">
</head>
<body>
  <!-- Container para notificaciones toast -->
  <div id="toast-container" class="toast-container"></div>
  
  <!-- include navbar -->
  <?php include 'navbar.php'; ?>

  <!-- Hero section con imagen de fondo -->
  <section class="herosection">
    <div class="container"> 
      <h1 class="h1">Sistema de Tickets</h1> 
      <?php if (!$usuario_logueado): ?>
        <!-- Botones solo para usuarios no logueados -->
        <a class="button1" href="../index.php?page=register">Registrarse</a>
        <a class="button1" href="../index.php?page=login">Iniciar Sesión</a>
      <?php else: ?>
        <!-- Mensaje para usuarios logueados -->
        <p class="bienvenida">¡Bienvenido! Aquí podrás comprar tus entradas para los eventos de MALPA CLUB.</p>
        <a class="button1" href="../index.php?page=catalogo">Ver eventos</a>
      <?php endif; ?>
    </div>
  </section>

  <!-- SECTION EVENTS -->
  <main id="container-eventos">
    <div class="container-eventos">
      <div class="texto-eventos">
        <p class="parrafo-eventos">¡En MALPA CLUB tenemos eventos de alto nivel!<br><br>
          Los mejores DJ's, bandas y artistas en vivo <br>
          La mejor música y el mejor ambiente durante TODA LA NOCHE<br>
        </p>
      </div>
      <div class="imagenes-eventos">
        <img src="../img/FLYER1.jpg" alt="" class="active">
        <img src="../img/flyer2.jpg" alt="" class="img2">
        <img src="../img/flyer3.jpg" alt="" class="img3">
        <img src="../img/flyer4.jpg" alt="" class="img4">
       <img src="../img/flyer5.jpg" alt="" class="img5">
      </div>
    </div>
  </main>
  <script src="../js/carrusel.js"></script>
  <script src="../js/animacion-eventos.js"></script>
  <script src="../js/toast.js"></script>
  
  <script>
    // Mostrar mensaje de logout exitoso si existe
    <?php if (isset($_GET['logout_exitoso']) && $_GET['logout_exitoso'] == '1'): ?>
        showToast('<?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Sesión cerrada correctamente'; ?>', 'success');
    <?php endif; ?>
    
    // Mostrar mensaje de error si existe
    <?php if (isset($_GET['error'])): ?>
        showToast('<?php echo htmlspecialchars($_GET['error']); ?>', 'error');
    <?php endif; ?>
  </script>
  <!-- SECTION UBICACION -->
<div id="container-ubicacion" class="container-ubicacion">
  <div class="texto-ubicacion">
    <p class="parrafo-ubicacion">
      Encuéntranos en:<br><br>
      Dirección: Av. República Oriental del Uruguay 3035 N3301AYS, N3301AYS Posadas, Misiones<br>
      Teléfono: +54 9 1234 5678<br>
      Instagram: @malpaclub<br>
    </p>
  </div>
  <div class="mapa-ubicacion">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3542.921824161279!2d-55.90119002584077!3d-27.378156776372816!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9457be4edae713c3%3A0xf5f7acfc5e251d40!2sMalparida!5e0!3m2!1ses-419!2sar!4v1757352937968!5m2!1ses-419!2sar"
      width="500" height="300" style="border:0;" allowfullscreen="" loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"></iframe>
  </div>
</div>
<script src="../js/animacion-ubicacion.js"></script>
  
  
  <footer id="container-contacto" class="footer" >
    <p>&copy; 2025 MALPA CLUB.  Todos los derechos reservados.</p>

  </footer>

</body>
</html>
