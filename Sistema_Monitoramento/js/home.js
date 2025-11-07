// ===== Modal Criar Iniciativa =====
const modal = document.getElementById('modalIniciativa');

document.addEventListener('click', (e) => {
  const actionEl = e.target.closest('[data-action]');
  const action = actionEl?.dataset.action;

  // abrir modal "Criar Iniciativa"
  if (action === 'criar') {
    modal?.classList.remove('hidden');
    // foco inicial no campo
    setTimeout(() => {
      document.querySelector('#modalIniciativa input[name="iniciativa"]')?.focus();
    }, 0);
    return;
  }

  // futuras rotas
  if (action === 'vistorias') {
    // window.location.href = 'index.php?page=visualizar';
    return;
  }

  // só precisa deste bloco se o "Sair" continuar como <button data-action="sair">
  // Se você trocou por <a href="sair.php">, pode remover este if.
  if (action === 'sair') {
    window.location.href = 'sair.php';
    return;
  }

  // fechar o modal ao clicar no backdrop ou no botão com data-close-modal
  if (e.target.matches('[data-close-modal]')) {
    modal?.classList.add('hidden');
  }
});

// fechar com ESC
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') modal?.classList.add('hidden');
});

// monta o hidden numero_contrato = "prefixo/ano"
(() => {
  const prefixo = document.getElementById('numero_contrato_prefixo');
  const ano     = document.getElementById('numero_contrato_ano');
  const hidden  = document.getElementById('numero_contrato');

  function updateNC() {
    if (!hidden) return;
    const p = (prefixo?.value || '').replace(/\D/g, '');
    const a = (ano?.value || '').replace(/\D/g, '');
    hidden.value = `${p}/${a}`;
  }
  prefixo?.addEventListener('input', updateNC);
  ano?.addEventListener('input', updateNC);
  updateNC();
})();
