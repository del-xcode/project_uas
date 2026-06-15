document.addEventListener('DOMContentLoaded', () => {
  const navLinks = document.querySelectorAll('.navbar .nav-link');
  const currentPath = window.location.pathname;

  navLinks.forEach((link) => {
    if (link.getAttribute('href') === currentPath) {
      link.classList.add('active');
    }
  });
});