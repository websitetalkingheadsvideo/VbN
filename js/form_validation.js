// Shared Bootstrap 5 client-side validation helper
// Attaches to all forms with .needs-validation and toggles .was-validated
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form.needs-validation').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
});

