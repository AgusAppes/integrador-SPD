
  const imagenes = document.querySelectorAll(".imagenes-eventos img");

  function mostrarImagenAleatoria() {
    // ocultar todas
    imagenes.forEach(img => img.classList.remove("active"));
    // elegir índice aleatorio
    const randomIndex = Math.floor(Math.random() * imagenes.length);
    // mostrar esa
    imagenes[randomIndex].classList.add("active");
  }

  // cambiar cada 4 segundos
  setInterval(mostrarImagenAleatoria, 2000);

