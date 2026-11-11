// Melhoria progressiva: máscara 00000-000 no campo de CEP.
(function () {
  'use strict';

  var campo = document.getElementById('cep');
  if (!campo) return;

  campo.addEventListener('input', function () {
    var digitos = campo.value.replace(/\D/g, '').slice(0, 8);
    campo.value = digitos.length > 5
      ? digitos.slice(0, 5) + '-' + digitos.slice(5)
      : digitos;
  });
})();
