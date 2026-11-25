(function () {
  var b = document.getElementById('burger'), n = document.getElementById('nav');
  if (b) b.addEventListener('click', function () { var o = n.classList.toggle('open'); b.setAttribute('aria-expanded', o); });
  document.querySelectorAll('form[data-validate]').forEach(function (f) {
    f.addEventListener('submit', function (ev) {
      if (!f.checkValidity()) { ev.preventDefault(); f.reportValidity(); return; }
      var p = f.querySelector('[name=password]'), p2 = f.querySelector('[name=password2]');
      if (p && p2 && p.value !== p2.value) { ev.preventDefault(); p2.setCustomValidity('Passwords do not match.'); p2.reportValidity(); p2.setCustomValidity(''); }
    });
  });
})();
