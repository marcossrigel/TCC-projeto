<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
date_default_timezone_set('America/Recife');

require_once __DIR__ . '/config.php'; // ← mantém assim

if (empty($_SESSION['id_usuario'])) {
  header('Location: login.php');
  exit;
}

$id_usuario   = (int)($_SESSION['id_usuario'] ?? 0);
$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'comum';

$sql = "SELECT * FROM iniciativas WHERE id_usuario = $id_usuario ORDER BY id DESC";
$iniciativas = $conexao->query($sql);

$nome  = htmlspecialchars($_SESSION['nome']  ?? 'Usuário', ENT_QUOTES, 'UTF-8');
$setor = htmlspecialchars($_SESSION['setor'] ?? '—',       ENT_QUOTES, 'UTF-8');

// Caminho relativo à raiz do index.php
$LOGO_PATH = 'assets/img/logo-cehab-azul.png';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>CEHAB - Sistema de Monitoramento</title>

  <!-- ✅ Tailwind (via CDN funciona mesmo, mantenha) -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- ✅ Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- ✅ Caminho corrigido para o CSS -->
  <link rel="stylesheet" href="assets/css/home.css">
</head>


<!-- Topbar -->
<header class="w-full border-b bg-white shadow-sm">
  <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
    <!-- Logo + Título -->
    <div class="flex items-center gap-3">
      <img src="assets/img/logo-cehab-azul.png"
           alt="CEHAB"
           class="h-8 w-auto object-contain select-none" draggable="false" />

      <h1 class="text-slate-800 text-lg sm:text-xl font-semibold">
        CEHAB - Sistema de Monitoramento
      </h1>
    </div>

    <!-- Botões -->
    <nav class="flex items-center gap-2">
      <button type="button" data-action="criar"
        class="inline-flex items-center rounded-full bg-green-600 px-4 py-2 text-white text-sm font-semibold hover:bg-green-700 transition">
        Criar Iniciativa
      </button>

      <a href="sair.php"
        class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-4 py-2 text-red-600 text-sm font-semibold hover:bg-red-100 transition">
        Sair
      </a>
    </nav>
  </div>
</header>


<!-- Conteúdo -->
<main class="mx-auto max-w-7xl px-4 py-6">
  <div class="mb-4">
    <div class="text-sm text-slate-600 flex items-center gap-2">
      <span class="inline-flex items-center gap-2">
        <span class="text-slate-500">Setor do usuário:</span>
        <span class="chip"><?= $setor ?></span>
      </span>
    </div>
  </div>

  <!-- Cards de iniciativas -->
  <section class="mt-6">
    <?php if ($iniciativas && $iniciativas->num_rows > 0): ?>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="cardsIniciativas">
        <?php while ($row = $iniciativas->fetch_assoc()): ?>
          <?php
            $status   = htmlspecialchars($row['ib_status'] ?? '', ENT_QUOTES, 'UTF-8');
            $execucao = htmlspecialchars($row['ib_execucao'] ?? '', ENT_QUOTES, 'UTF-8');
            $previsto = htmlspecialchars($row['ib_previsto'] ?? '', ENT_QUOTES, 'UTF-8');
            $variacao = htmlspecialchars($row['ib_variacao'] ?? '', ENT_QUOTES, 'UTF-8');
            $contrato = htmlspecialchars($row['numero_contrato'] ?? '', ENT_QUOTES, 'UTF-8');
            $dt       = htmlspecialchars($row['data_vistoria'] ?? '', ENT_QUOTES, 'UTF-8');
            $titulo   = htmlspecialchars($row['iniciativa'] ?? '', ENT_QUOTES, 'UTF-8');
            $id       = (int)$row['id'];
          ?>
          <article
            class="group cursor-pointer rounded-xl border hover:border-blue-300 hover:shadow-md transition p-4 bg-white"
            data-id="<?= $id ?>"
            data-iniciativa="<?= $titulo ?>"
            data-data_vistoria="<?= $dt ?>"
            data-status="<?= $status ?>"
            data-execucao="<?= $execucao ?>"
            data-previsto="<?= $previsto ?>"
            data-variacao="<?= $variacao ?>"
            data-contrato="<?= $contrato ?>"
            data-valor_medio="<?= htmlspecialchars($row['ib_valor_medio'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            data-secretaria="<?= htmlspecialchars($row['ib_secretaria'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            data-diretoria="<?= htmlspecialchars($row['ib_diretoria'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            data-gestor="<?= htmlspecialchars($row['ib_gestor_responsavel'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            data-fiscal="<?= htmlspecialchars($row['ib_fiscal'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            data-objeto="<?= htmlspecialchars($row['objeto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            data-info="<?= htmlspecialchars($row['informacoes_gerais'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            data-obs="<?= htmlspecialchars($row['observacoes'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          >
            <header class="mb-2">
              <h3 class="line-clamp-2 font-semibold text-slate-800 group-hover:text-blue-700"><?= $titulo ?></h3>
              <div class="mt-1 text-xs text-slate-500">
                Nº Contrato: <span class="font-medium"><?= $contrato ?: '—' ?></span>
              </div>
            </header>

            <div class="flex items-center gap-2 text-xs">
              <span class="inline-flex items-center rounded-full px-2 py-0.5 border text-slate-600">
                <?= $status ?: 'Sem status' ?>
              </span>
              <span class="text-slate-500">Exec:</span>
              <span class="font-medium"><?= $execucao ?: '—' ?></span>
              <span class="text-slate-500">Prev:</span>
              <span class="font-medium"><?= $previsto ?: '—' ?></span>
            </div>

            <footer class="mt-3 flex items-center justify-between">
              <span class="text-xs text-slate-500">Atualização: <?= $dt ?: '—' ?></span>
              <button type="button" class="text-blue-700 text-sm font-medium hover:underline" data-open-detalhes>
                Detalhes
              </button>
            </footer>
          </article>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="rounded-lg border border-dashed p-8 text-center text-slate-400">
        Nenhuma iniciativa cadastrada ainda.
      </div>
    <?php endif; ?>
  </section>
</main>

<!-- Modal: Criar Iniciativa (mantém) -->
<?php /* ... seu mesmo HTML do modalIniciativa ... */ ?>

<!-- Modal: Detalhes da Iniciativa (mantém) -->
<?php /* ... seu mesmo HTML do modalDetalhes ... */ ?>

<!-- Scripts específicos desta tela -->
<script>
// montar numero_contrato antes de enviar o formulário
document.getElementById('formIniciativa')?.addEventListener('submit', function() {
  const p = document.getElementById('numero_contrato_prefixo')?.value?.trim() || '';
  const a = document.getElementById('numero_contrato_ano')?.value?.trim() || '';
  document.getElementById('numero_contrato').value = (p && a) ? `${p}/${a}` : '';
});

// modal de detalhes
(function() {
  const modal = document.getElementById('modalDetalhes');
  function openWith(el) {
    const get = (k) => el.dataset[k] || '—';
    det_titulo.textContent     = get('iniciativa');
    det_data.textContent       = get('data_vistoria');
    det_contrato.textContent   = get('contrato');
    det_status.textContent     = get('status');
    det_execucao.textContent   = get('execucao');
    det_previsto.textContent   = get('previsto');
    det_variacao.textContent   = get('variacao');
    det_valor.textContent      = get('valor_medio');
    det_secretaria.textContent = get('secretaria');
    det_diretoria.textContent  = get('diretoria');
    det_gestor.textContent     = get('gestor');
    det_fiscal.textContent     = get('fiscal');
    det_objeto.textContent     = get('objeto');
    det_info.textContent       = get('info');
    det_obs.textContent        = get('obs');

    const id = el.dataset.id;
    document.getElementById('btnAcompanhar').onclick = () =>
      window.location.href = 'index.php?page=acompanhamento&id_iniciativa=' + id;
    document.getElementById('btnMedicoes').onclick = () =>
      window.location.href = 'index.php?page=medicoes&id_iniciativa=' + id;

    modal.classList.remove('hidden');
  }

  document.getElementById('cardsIniciativas')?.addEventListener('click', (ev) => {
    const card = ev.target.closest('article[data-id]');
    if (card) openWith(card);
  });

  modal?.addEventListener('click', (ev) => {
    if (ev.target.hasAttribute('data-close-detalhes') || ev.target.closest('[data-close-detalhes]')) {
      modal.classList.add('hidden');
    }
  });
})();
</script>
