document.addEventListener("DOMContentLoaded", () => {
  const elementos = document.querySelectorAll(
    ".texto-eventos, .imagenes-eventos, .texto-ubicacion, .mapa-ubicacion"
  );

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("show");
      }
    });
  }, { threshold: 0.2 });

  elementos.forEach(el => observer.observe(el));
});