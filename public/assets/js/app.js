/* Sistema de Prontuários — App JS */

(function () {
  'use strict';

  // ---- Sidebar toggle (mobile) ----
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar       = document.getElementById('sidebar');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });

    // Fechar ao clicar fora (mobile)
    document.addEventListener('click', function (e) {
      if (
        window.innerWidth <= 768 &&
        sidebar.classList.contains('open') &&
        !sidebar.contains(e.target) &&
        e.target !== sidebarToggle &&
        !sidebarToggle.contains(e.target)
      ) {
        sidebar.classList.remove('open');
      }
    });
  }

  // ---- Auto-dismiss alerts after 6 s ----
  document.querySelectorAll('.alert.alert-success, .alert.alert-info, .alert.alert-warning').forEach(function (el) {
    setTimeout(function () {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
      bsAlert.close();
    }, 6000);
  });

  // ---- Confirm delete via data attribute (alternative) ----
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(this.dataset.confirm)) {
        e.preventDefault();
      }
    });
  });

})();

// ---- Auto-calculate patient age when data_atendimento changes ----
(function () {
  'use strict';

  /**
   * Calculates the age in whole years between dataNasc and dataRef.
   * Both parameters must be strings in YYYY-MM-DD format.
   * Returns a string like "45 anos" or "" if dates are invalid/missing.
   */
  function calcularIdadeEmAnos(dataNasc, dataRef) {
    if (!dataNasc || !dataRef) return '';
    var nasc = new Date(dataNasc + 'T00:00:00');
    var ref  = new Date(dataRef  + 'T00:00:00');
    if (isNaN(nasc.getTime()) || isNaN(ref.getTime())) return '';
    if (ref < nasc) return '';
    var anos = ref.getFullYear() - nasc.getFullYear();
    if (
      ref.getMonth() < nasc.getMonth() ||
      (ref.getMonth() === nasc.getMonth() && ref.getDate() < nasc.getDate())
    ) {
      anos--;
    }
    if (anos < 0) return '';
    return anos + ' ano' + (anos !== 1 ? 's' : '');
  }

  var fldDataNasc  = document.getElementById('data_nascimento');
  var fldDataAtend = document.getElementById('data_atendimento');
  var fldIdade     = document.getElementById('idade');

  // Single-atendimento form (novo.php / editar.php)
  function atualizarIdadeSimples() {
    if (!fldDataNasc || !fldDataAtend || !fldIdade) return;
    fldIdade.value = calcularIdadeEmAnos(fldDataNasc.value, fldDataAtend.value);
  }

  if (fldDataAtend && fldIdade) {
    fldDataAtend.addEventListener('change', atualizarIdadeSimples);
  }
  if (fldDataNasc && fldDataAtend && fldIdade) {
    fldDataNasc.addEventListener('change', atualizarIdadeSimples);
  }

  // Multi-atendimento form (atendimentosContainer)
  var multiContainer = document.getElementById('atendimentosContainer');
  if (multiContainer) {
    multiContainer.addEventListener('change', function (e) {
      if (!e.target.matches('[name*="[data_atendimento]"]')) return;
      var item = e.target.closest('.atendimento-item');
      if (!item) return;
      var idadeField = item.querySelector('[name*="[idade]"]');
      if (!idadeField) return;
      var dataNasc = fldDataNasc ? fldDataNasc.value : '';
      idadeField.value = calcularIdadeEmAnos(dataNasc, e.target.value);
    });

    if (fldDataNasc) {
      fldDataNasc.addEventListener('change', function () {
        multiContainer.querySelectorAll('.atendimento-item').forEach(function (item) {
          var dataAtend  = item.querySelector('[name*="[data_atendimento]"]');
          var idadeField = item.querySelector('[name*="[idade]"]');
          if (!dataAtend || !idadeField) return;
          idadeField.value = calcularIdadeEmAnos(fldDataNasc.value, dataAtend.value);
        });
      });
    }
  }

})();
