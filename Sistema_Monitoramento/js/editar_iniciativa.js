  function abrirModal() {
    document.getElementById('modalConfirmacao').style.display = 'flex';
  }
  function fecharModal() {
    document.getElementById('modalConfirmacao').style.display = 'none';
  }
  function confirmarExclusao() {
    window.location.href = 'templates/excluir_iniciativa.php?id=' + idIniciativa;
  }