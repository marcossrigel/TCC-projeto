<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/config.php';

if (empty($_SESSION['id_usuario'])) { http_response_code(401); exit('Sem sessão'); }

$id_usuario = (int) $_SESSION['id_usuario'];

/* iniciativas do dono */
$sql_iniciativas = "SELECT id, iniciativa FROM iniciativas WHERE id_usuario = $id_usuario ORDER BY iniciativa";
$res_iniciativas = $conexao->query($sql_iniciativas);

/* já compartilhado com (apenas usuários 'comum') */
$sql_compartilhados = "
  SELECT DISTINCT u.nome AS nome_usuario, u.id_usuario
  FROM compartilhamentos c
  JOIN iniciativas i ON i.id = c.id_iniciativa
  JOIN usuarios u    ON u.id_usuario = c.id_compartilhado
  WHERE i.id_usuario = $id_usuario
    AND u.tipo = 'comum'
  ORDER BY u.nome
";
$res_compartilhados = $conexao->query($sql_compartilhados);
?>

<!-- Cabeçalho do formulário -->
<div class="space-y-4">
  <form action="index.php?page=salvar_compartilhamento" method="post" id="formCompartilhar" class="space-y-5">

    <div>
      <label for="usuario" class="block text-sm font-medium text-slate-700 mb-1">
        Nome do Usuário (REDE)
      </label>
      <input type="text" name="usuario" id="usuario"
             placeholder="Digite o nome do usuário da rede"
             class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-200"
             required>
      <!-- container para autocomplete -->
      <div id="sugestoes"
           class="mt-1 hidden absolute z-50 w-[calc(100%-3rem)] bg-white border rounded-lg shadow-sm max-h-48 overflow-y-auto"></div>
    </div>

    <div class="pt-1">
      <div class="text-sm font-semibold text-slate-800 mb-2">Selecione as iniciativas a compartilhar</div>

      <!-- Selecionar todas -->
      <label class="inline-flex items-center gap-2 text-slate-700 mb-2">
        <input type="checkbox" id="selecionar_tudo" class="rounded border-slate-300">
        <span>Selecionar todas</span>
      </label>

      <div class="grid sm:grid-cols-2 gap-2">
        <?php if ($res_iniciativas && $res_iniciativas->num_rows): ?>
          <?php while ($linha = $res_iniciativas->fetch_assoc()): ?>
            <label class="flex items-center gap-2 p-2 border rounded-lg bg-slate-50 hover:bg-slate-100">
              <input type="checkbox" name="iniciativas[]"
                     value="<?= (int)$linha['id'] ?>"
                     class="rounded border-slate-300">
              <span class="text-sm text-slate-800 line-clamp-1">
                <?= htmlspecialchars($linha['iniciativa'], ENT_QUOTES, 'UTF-8') ?>
              </span>
            </label>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="text-slate-500 text-sm">Você não possui iniciativas para compartilhar.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="flex justify-end gap-2 pt-2">
      <button type="button" data-close-compartilhar
              class="rounded-full px-4 py-2 border border-slate-300 text-slate-800 hover:bg-slate-50">
        Cancelar
      </button>
      <button type="submit"
              class="rounded-full px-5 py-2 bg-blue-600 text-white font-semibold hover:bg-blue-700">
        Compartilhar
      </button>
    </div>
  </form>

  <hr class="border-slate-200">

  <div>
    <div class="text-sm font-semibold text-slate-800 mb-3">Já compartilhado com</div>

    <?php if ($res_compartilhados && $res_compartilhados->num_rows): ?>
      <ul class="space-y-2">
        <?php while ($linha = $res_compartilhados->fetch_assoc()): ?>
          <li class="flex items-center gap-3 p-2 border rounded-lg bg-slate-50">
            <img src="perfil.png" alt="" class="w-8 h-8 rounded-full object-cover">
            <span class="text-sm text-slate-800 flex-1">
              <?= htmlspecialchars($linha['nome_usuario'], ENT_QUOTES, 'UTF-8') ?>
            </span>
            <button class="btn-remover text-slate-600 hover:text-red-600"
                    title="Remover compartilhamento"
                    data-id="<?= (int)$linha['id_usuario'] ?>">
              🗑️
            </button>
          </li>
        <?php endwhile; ?>
      </ul>
    <?php else: ?>
      <div class="text-slate-500 text-sm">Nenhum usuário ainda.</div>
    <?php endif; ?>
  </div>
</div>

<script>
// ===== Autocomplete (busca por rede) =====
(function () {
  const input = document.getElementById('usuario');
  const box = document.getElementById('sugestoes');
  if (!input || !box) return;

  input.addEventListener('input', async function () {
    const termo = this.value.trim();
    if (termo.length < 2) { box.classList.add('hidden'); return; }

    try {
      const resp = await fetch('templates/compartilhar_buscar_usuario.php?termo=' + encodeURIComponent(termo));
      const data = await resp.json();
      box.innerHTML = '';
      data.forEach(nome => {
        const item = document.createElement('div');
        item.className = 'px-3 py-2 hover:bg-slate-100 cursor-pointer text-sm';
        item.textContent = nome;
        item.onclick = () => { input.value = nome; box.classList.add('hidden'); };
        box.appendChild(item);
      });
      box.classList.toggle('hidden', data.length === 0);
    } catch (e) { box.classList.add('hidden'); }
  });

  document.addEventListener('click', (e) => {
    if (!input.contains(e.target) && !box.contains(e.target)) box.classList.add('hidden');
  });
})();

// ===== Selecionar todas =====
(function () {
  const sel = document.getElementById('selecionar_tudo');
  const cbs = [...document.querySelectorAll('input[name="iniciativas[]"]')];
  if (!sel || cbs.length === 0) return;

  sel.addEventListener('change', () => cbs.forEach(cb => cb.checked = sel.checked));
  cbs.forEach(cb => cb.addEventListener('change', () => {
    sel.checked = cbs.every(x => x.checked);
  }));
})();

// ===== Remover compartilhamento =====
document.querySelectorAll('.btn-remover').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm('Deseja remover este compartilhamento?')) return;
    const id = btn.dataset.id;

    const resp = await fetch('templates/remover_compartilhamento.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id_compartilhado=' + encodeURIComponent(id)
    });
    const txt = await resp.text();
    if (txt.trim() === 'OK') {
      // remove visualmente o item
      btn.closest('li')?.remove();
    } else {
      alert('Erro ao remover compartilhamento.');
    }
  });
});
</script>
