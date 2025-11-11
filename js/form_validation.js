// Shared Bootstrap 5 client-side validation helper
// Attaches to all forms with .needs-validation and toggles .was-validated
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form.needs-validation').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        // Focus the first invalid field for better accessibility
        const firstInvalid = form.querySelector(':invalid');
        if (firstInvalid && typeof firstInvalid.focus === 'function') {
          try { firstInvalid.focus({ preventScroll: true }); } catch (_) { firstInvalid.focus(); }
          try { firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (_) {}
        }
      }
      form.classList.add('was-validated');
    }, false);
  });
});
