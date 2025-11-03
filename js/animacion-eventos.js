document.addEventListener("DOMContentLoaded", () => {
  const texto = document.querySelector(".texto-eventos");
  const imagenes = document.querySelector(".imagenes-eventos");

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("show");
      }
    });
  }, { threshold: 0.2 });

  observer.observe(texto);
  observer.observe(imagenes);
});