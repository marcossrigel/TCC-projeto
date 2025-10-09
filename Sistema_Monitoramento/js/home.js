// ===== Modal Criar Iniciativa =====
const modal = document.getElementById('modalIniciativa');

// abre ao clicar no botão do topo
document.addEventListener('click', (ev) => {
  const btn = ev.target.closest('button[data-action="criar"]');
  if (btn) {
    modal?.classList.remove('hidden');
    // foco inicial
    setTimeout(() => document.querySelector('#modalIniciativa input[name="iniciativa"]')?.focus(), 0);
  }
});

// fecha no X, no backdrop ou com ESC
document.addEventListener('click', (ev) => {
  if (ev.target.matches('[data-close-modal]')) modal?.classList.add('hidden');
});
document.addEventListener('keydown', (ev) => {
  if (ev.key === 'Escape') modal?.classList.add('hidden');
});

// monta o hidden numero_contrato = prefixo/ano
(function bindNumeroContratoComposer(){
  const prefixo = document.getElementById('numero_contrato_prefixo');
  const ano     = document.getElementById('numero_contrato_ano');
  const hidden  = document.getElementById('numero_contrato');

  function updateNC(){
    if (hidden) hidden.value = (prefixo?.value || '').replace(/\D/g,'') + '/' + (ano?.value || '').replace(/\D/g,'');
  }
  prefixo?.addEventListener('input', updateNC);
  ano?.addEventListener('input', updateNC);
  updateNC();
})();
