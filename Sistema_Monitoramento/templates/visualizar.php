<<<<<<< HEAD
<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
  header('Location: login.php'); exit;
}

$permitidas = ['Educacao','Saude','Seguranca','Infra Estrategicas','Infra Grandes Obras','Social'];

$diretoria = $_GET['diretoria'] ?? '';
$diretoria = trim($diretoria);

$diretoria = preg_replace('/\s+/', ' ', $diretoria);
$diretoria = strip_tags($diretoria);

if (!in_array($diretoria, $permitidas, true)) {
  http_response_code(400);
  echo '<p style="padding:16px">Diretoria inválida.</p>';
  exit;
}

$stmt = $conexao->prepare(
  "SELECT id, iniciativa, data_vistoria, numero_contrato,
          ib_status, ib_execucao, ib_previsto, ib_variacao,
          ib_valor_medio, ib_secretaria, ib_diretoria,
          ib_gestor_responsavel, ib_fiscal, objeto,
          informacoes_gerais, observacoes
     FROM iniciativas
    WHERE ib_diretoria = ?
    ORDER BY id DESC"
);
$stmt->bind_param('s', $diretoria);
$stmt->execute();
$res = $stmt->get_result();
?>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  html, body { margin: 0; padding: 0; } 
</style>

<header class="sticky top-0 inset-x-0 z-50 bg-white border-b shadow-sm">
  <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <img src="./img/logo.png" class="h-12 md:h-14 w-auto object-contain" alt="CEHAB">
      <div>
        <h1 class="text-slate-800 text-lg sm:text-xl font-semibold">
          Sistema de Monitoramento de Obras
        </h1>
        <p class="text-xs text-slate-600">
          Visualizando: <strong><?= htmlspecialchars($diretoria, ENT_QUOTES, 'UTF-8') ?></strong>
        </p>
      </div>
    </div>

    <nav class="flex items-center gap-2">
      <a href="index.php?page=diretorias"
         class="inline-flex items-center rounded-full border px-4 py-2 text-slate-700 text-sm font-semibold hover:bg-slate-50">
        ← Voltar
      </a>
      <a href="templates/sair.php"
         class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-4 py-2 text-red-600 text-sm font-semibold hover:bg-red-100">
        Sair
      </a>
    </nav>
  </div>
</header>

<main class="bg-slate-200 min-h-screen">
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-2xl border border-slate-300 bg-slate-50 shadow-md">
      <div class="p-6 border-b border-slate-200">
        <div class="text-sm text-slate-800">
          <h6>Iniciativas da diretoria “<?= htmlspecialchars($diretoria, ENT_QUOTES, 'UTF-8') ?>”</h6>
        </div>
      </div>

<div id="modalDetalhes" class="fixed inset-0 z-50 hidden">
  <!-- overlay -->
  <button class="absolute inset-0 bg-black/40" data-close-modal aria-label="Fechar"></button>

  <!-- wrapper do modal -->
  <div class="relative mx-auto my-6 w-[96%] max-w-5xl">
    <!-- cartão do modal com altura limitada e layout em coluna -->
    <div class="flex max-h-[85vh] flex-col rounded-2xl border border-slate-200 bg-white shadow-2xl">

      <!-- header fixo -->
      <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 border-b bg-white">
        <h3 class="text-lg font-semibold text-slate-800" data-f="title">—</h3>
        <div class="flex items-center gap-6">
          <button type="button" class="text-slate-600 hover:underline" data-btn-close>Fechar ×</button>
        </div>
      </div>

      <!-- CONTEÚDO ROLÁVEL -->
      <div class="flex-1 overflow-y-auto px-6 py-5 text-sm text-slate-800">
        <div class="grid sm:grid-cols-2 gap-x-10 gap-y-3">
          <div><span class="text-slate-500">Data da Atualização:</span> <span class="font-medium" data-f="data_vistoria">—</span></div>
          <div><span class="text-slate-500">Nº do Contrato:</span> <span class="font-medium" data-f="contrato">—</span></div>

          <div><span class="text-slate-500">Status:</span> <span class="font-medium" data-f="status">—</span></div>
          <div><span class="text-slate-500">% Execução:</span> <span class="font-medium" data-f="execucao">—</span></div>

          <div><span class="text-slate-500">% Previsto:</span> <span class="font-medium" data-f="previsto">—</span></div>
          <div><span class="text-slate-500">% Variação:</span> <span class="font-medium" data-f="variacao">—</span></div>

          <div><span class="text-slate-500">Valor Acumulado:</span> <span class="font-medium" data-f="valor_medio">—</span></div>
          <div><span class="text-slate-500">Secretaria:</span> <span class="font-medium" data-f="secretaria">—</span></div>

          <div><span class="text-slate-500">Diretoria:</span> <span class="font-medium" data-f="diretoria">—</span></div>
          <div><span class="text-slate-500">Gestor:</span> <span class="font-medium" data-f="gestor">—</span></div>

          <div><span class="text-slate-500">Fiscal:</span> <span class="font-medium" data-f="fiscal">—</span></div>
        </div>

        <div class="mt-6">
          <div class="text-slate-500 mb-1">Objeto</div>
          <div class="rounded-xl border bg-slate-50 p-3 min-h-[36px]" data-f="objeto">—</div>
        </div>

        <div class="mt-4">
          <div class="text-slate-500 mb-1">Informações Gerais</div>
          <div class="rounded-xl border bg-slate-50 p-3 min-h-[36px]" data-f="info">—</div>
        </div>

        <div class="mt-4">
          <div class="text-slate-500 mb-1">Observações (Pontos Críticos)</div>
          <div class="rounded-xl border bg-slate-50 p-3 min-h-[36px]" data-f="obs">—</div>
        </div>
      </div>

      <!-- footer fixo -->
      <div class="sticky bottom-0 z-10 px-6 pb-6 pt-4 bg-white border-t">
        <div class="grid sm:grid-cols-2 gap-4">
          <a data-link="pendencias" class="flex items-center justify-center rounded-2xl border px-4 py-3 bg-slate-50 hover:bg-slate-100 transition">
            🛠️ <span class="ml-2 font-medium text-slate-800">Acompanhar Pendências</span>
          </a>
          <a data-link="projeto_licitacao" class="flex items-center justify-center rounded-2xl border px-4 py-3 bg-slate-50 hover:bg-slate-100 transition">
            🧾 <span class="ml-2 font-medium text-slate-800">Projeto e Licitação</span>
          </a>
          <a data-link="info_contratuais" class="flex items-center justify-center rounded-2xl border px-4 py-3 bg-slate-50 hover:bg-slate-100 transition">
            📄 <span class="ml-2 font-medium text-slate-800">Informações Contratuais</span>
          </a>
          <a data-link="medicoes" class="flex items-center justify-center rounded-2xl border px-4 py-3 bg-slate-50 hover:bg-slate-100 transition">
            📊 <span class="ml-2 font-medium text-slate-800">Acompanhamento de Medições</span>
          </a>
          <a data-link="cronograma" class="flex items-center justify-center rounded-2xl border px-4 py-3 bg-slate-50 hover:bg-slate-100 transition">
            📅 <span class="ml-2 font-medium text-slate-800">Cronograma</span>
          </a>
          <button type="button" data-btn-concluir class="flex items-center justify-center rounded-2xl border px-4 py-3 bg-slate-50 hover:bg-slate-100 transition">
            ✔️ <span class="ml-2 font-medium text-slate-800">Concluída</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>


      <section class="p-6">
        <?php if ($res && $res->num_rows): ?>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="cardsIniciativas">
            <?php while ($row = $res->fetch_assoc()): ?>
              <?php
              $id       = (int)$row['id'];
              $titulo   = htmlspecialchars($row['iniciativa'] ?? '', ENT_QUOTES, 'UTF-8');
              $contrato = htmlspecialchars($row['numero_contrato'] ?? '', ENT_QUOTES, 'UTF-8');
              $dtRaw = $row['data_vistoria'] ?? '';
              $dtFmt = '';
              if ($dtRaw) {
                $d = DateTime::createFromFormat('Y-m-d', $dtRaw);
                if ($d) $dtFmt = $d->format('d/m/Y');
              }
              $dt = htmlspecialchars($dtFmt, ENT_QUOTES, 'UTF-8');

              $status = htmlspecialchars($row['ib_status'] ?? '', ENT_QUOTES, 'UTF-8');
              $exec   = htmlspecialchars($row['ib_execucao'] ?? '', ENT_QUOTES, 'UTF-8');
              $prev   = htmlspecialchars($row['ib_previsto'] ?? '', ENT_QUOTES, 'UTF-8');
              $var    = htmlspecialchars($row['ib_variacao'] ?? '', ENT_QUOTES, 'UTF-8');
              ?>

              <article
                class="group cursor-pointer rounded-xl border border-slate-300 bg-slate-100 hover:border-blue-400 hover:shadow-md transition p-4"
                data-id="<?= $id ?>"
                data-iniciativa="<?= $titulo ?>"
                data-data_vistoria="<?= $dt ?>"
                data-status="<?= $status ?>"
                data-execucao="<?= $exec ?>"
                data-previsto="<?= $prev ?>"
                data-variacao="<?= $var ?>"
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
                  <div class="mt-1 text-xs text-slate-700">
                    Nº Contrato: <span class="font-medium"><?= $contrato ?: '—' ?></span>
                  </div>
                </header>

                <div class="flex items-center gap-2 text-xs">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 border text-slate-800">
                    <?= $status ?: 'Sem status' ?>
                  </span>
                  <span class="text-slate-700">Exec:</span>
                  <span class="font-medium"><?= $exec ?: '—' ?></span>
                  <span class="text-slate-700">Prev:</span>
                  <span class="font-medium"><?= $prev ?: '—' ?></span>
                </div>

                <footer class="mt-3 flex items-center justify-between">
                  <span class="text-xs text-slate-700">Atualização: <?= $dt ?: '—' ?></span>
                  <button type="button" class="text-blue-700 text-sm font-medium hover:underline" data-open-detalhes>
                    Detalhes
                  </button>
                </footer>
              </article>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div class="rounded-lg border border-dashed p-8 text-center text-slate-400">
            Nenhuma iniciativa nessa diretoria.
          </div>
        <?php endif; ?>
      </section>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('cardsIniciativas');
  if (!container) return;

  const tryOpen = (card) => {
    if (!card) return;
    if (typeof window.openWith === 'function') {
      window.openWith(card);
    } else {
      console.error('window.openWith não disponível');
      alert('Não foi possível abrir os detalhes agora.');
    }
  };

  container.addEventListener('click', (ev) => {
    const btn = ev.target.closest('[data-open-detalhes]');
    const card = btn ? btn.closest('article[data-id]')
                     : ev.target.closest('article[data-id]');
    if (!card) return;
    ev.preventDefault();
    tryOpen(card);
  });

  container.addEventListener('keydown', (ev) => {
    const card = ev.target.closest('article[data-id]');
    if (!card) return;
    if (ev.key === 'Enter' || ev.key === ' ') {
      ev.preventDefault();
      tryOpen(card);
    }
  });
});

(function () {
  const modal   = document.getElementById('modalDetalhes');
  const closeEl = modal.querySelector('[data-close-modal]');
  const btnX    = modal.querySelector('[data-btn-close]');

  const links = {
    pendencias: modal.querySelector('[data-link="pendencias"]'),
    projeto:    modal.querySelector('[data-link="projeto_licitacao"]'),
    info:       modal.querySelector('[data-link="info_contratuais"]'),
    medicoes:   modal.querySelector('[data-link="medicoes"]'),
    cronograma: modal.querySelector('[data-link="cronograma"]'),
  };

  let currentId = null;

  function set(el, value) {
    el.textContent = value && String(value).trim() ? value : '—';
  }

  function fillFrom(card) {
    const d = card.dataset;
    currentId = d.id;

    set(modal.querySelector('[data-f="title"]'),         d.iniciativa);
    set(modal.querySelector('[data-f="contrato"]'),      d.contrato);
    set(modal.querySelector('[data-f="data_vistoria"]'), d.data_vistoria);
    set(modal.querySelector('[data-f="status"]'),        d.status);
    set(modal.querySelector('[data-f="execucao"]'),      d.execucao);
    set(modal.querySelector('[data-f="previsto"]'),      d.previsto);
    set(modal.querySelector('[data-f="variacao"]'),      d.variacao);
    set(modal.querySelector('[data-f="valor_medio"]'),   d.valor_medio);
    set(modal.querySelector('[data-f="secretaria"]'),    d.secretaria);
    set(modal.querySelector('[data-f="diretoria"]'),     d.diretoria);
    set(modal.querySelector('[data-f="gestor"]'),        d.gestor);
    set(modal.querySelector('[data-f="fiscal"]'),        d.fiscal);
    set(modal.querySelector('[data-f="objeto"]'),        d.objeto);
    set(modal.querySelector('[data-f="info"]'),          d.info);
    set(modal.querySelector('[data-f="obs"]'),           d.obs);

    if (links.pendencias) links.pendencias.href = `index.php?page=acompanhamento&id_iniciativa=${encodeURIComponent(currentId)}`;
    if (links.projeto)    links.projeto.href    = `index.php?page=projeto_licitacoes&id_iniciativa=${encodeURIComponent(currentId)}`;
    if (links.info)       links.info.href       = `index.php?page=info_contratuais&id_iniciativa=${encodeURIComponent(currentId)}`;
    if (links.medicoes)   links.medicoes.href   = `index.php?page=medicoes&id_iniciativa=${encodeURIComponent(currentId)}`;
    if (links.cronograma)
  links.cronograma.href = `index.php?page=cronogramamarcos&id_iniciativa=${encodeURIComponent(currentId)}`;

  }

  function open(card) {
    fillFrom(card);
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  function close() {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }

  closeEl.addEventListener('click', close);
  btnX.addEventListener('click', close);
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
  });

  window.openWith = open;
})();
</script>

=======
<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
  header('Location: login.php'); exit;
}

$permitidas = ['Educacao','Saude','Seguranca','Infra Estrategicas','Infra Grandes Obras','Social'];

$diretoria = $_GET['diretoria'] ?? '';
$diretoria = trim($diretoria);

$diretoria = preg_replace('/\s+/', ' ', $diretoria);
$diretoria = strip_tags($diretoria);

if (!in_array($diretoria, $permitidas, true)) {
  http_response_code(400);
  echo '<p style="padding:16px">Diretoria inválida.</p>';
  exit;
}

$stmt = $conexao->prepare(
  "SELECT id, iniciativa, data_vistoria, numero_contrato,
          ib_status, ib_execucao, ib_previsto, ib_variacao,
          ib_valor_medio, ib_secretaria, ib_diretoria,
          ib_gestor_responsavel, ib_fiscal, objeto,
          informacoes_gerais, observacoes
     FROM iniciativas
    WHERE ib_diretoria = ?
    ORDER BY id DESC"
);
$stmt->bind_param('s', $diretoria);
$stmt->execute();
$res = $stmt->get_result();
?>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  html, body { margin: 0; padding: 0; } 
</style>

<header class="sticky top-0 inset-x-0 z-50 bg-white border-b shadow-sm">
  <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <img src="./img/logo.png" class="h-12 md:h-14 w-auto object-contain" alt="CEHAB">
      <div>
        <h1 class="text-slate-800 text-lg sm:text-xl font-semibold">
          Sistema de Monitoramento de Obras
        </h1>
        <p class="text-xs text-slate-600">
          Visualizando: <strong><?= htmlspecialchars($diretoria, ENT_QUOTES, 'UTF-8') ?></strong>
        </p>
      </div>
    </div>

    <nav class="flex items-center gap-2">
      <a href="index.php?page=diretorias"
         class="inline-flex items-center rounded-full border px-4 py-2 text-slate-700 text-sm font-semibold hover:bg-slate-50">
        ← Voltar
      </a>
      <a href="templates/sair.php"
         class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-4 py-2 text-red-600 text-sm font-semibold hover:bg-red-100">
        Sair
      </a>
    </nav>
  </div>
</header>

<main class="bg-slate-200 min-h-screen">
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-2xl border border-slate-300 bg-slate-50 shadow-md">
      <div class="p-6 border-b border-slate-200">
        <div class="text-sm text-slate-800">
          <h6>Iniciativas da diretoria “<?= htmlspecialchars($diretoria, ENT_QUOTES, 'UTF-8') ?>”</h6>
        </div>
      </div>

<div id="modalDetalhes" class="fixed inset-0 z-50 hidden">
  <!-- overlay -->
  <button class="absolute inset-0 bg-black/40" data-close-modal aria-label="Fechar"></button>

  <!-- wrapper do modal -->
  <div class="relative mx-auto my-6 w-[96%] max-w-5xl">
    <!-- cartão do modal com altura limitada e layout em coluna -->
    <div class="flex max-h-[85vh] flex-col rounded-2xl border border-slate-200 bg-white shadow-2xl">

      <!-- header fixo -->
      <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 border-b bg-white">
        <h3 class="text-lg font-semibold text-slate-800" data-f="title">—</h3>
        <div class="flex items-center gap-6">
          <button type="button" class="text-slate-600 hover:underline" data-btn-close>Fechar ×</button>
        </div>
      </div>

      <!-- CONTEÚDO ROLÁVEL -->
      <div class="flex-1 overflow-y-auto px-6 py-5 text-sm text-slate-800">
        <div class="grid sm:grid-cols-2 gap-x-10 gap-y-3">
          <div><span class="text-slate-500">Data da Atualização:</span> <span class="font-medium" data-f="data_vistoria">—</span></div>
          <div><span class="text-slate-500">Nº do Contrato:</span> <span class="font-medium" data-f="contrato">—</span></div>

          <div><span class="text-slate-500">Status:</span> <span class="font-medium" data-f="status">—</span></div>
          <div><span class="text-slate-500">% Execução:</span> <span class="font-medium" data-f="execucao">—</span></div>

          <div><span class="text-slate-500">% Previsto:</span> <span class="font-medium" data-f="previsto">—</span></div>
          <div><span class="text-slate-500">% Variação:</span> <span class="font-medium" data-f="variacao">—</span></div>

          <div><span class="text-slate-500">Valor Acumulado:</span> <span class="font-medium" data-f="valor_medio">—</span></div>
          <div><span class="text-slate-500">Secretaria:</span> <span class="font-medium" data-f="secretaria">—</span></div>

          <div><span class="text-slate-500">Diretoria:</span> <span class="font-medium" data-f="diretoria">—</span></div>
          <div><span class="text-slate-500">Gestor:</span> <span class="font-medium" data-f="gestor">—</span></div>

          <div><span class="text-slate-500">Fiscal:</span> <span class="font-medium" data-f="fiscal">—</span></div>
        </div>

        <div class="mt-6">
          <div class="text-slate-500 mb-1">Objeto</div>
          <div class="rounded-xl border bg-slate-50 p-3 min-h-[36px]" data-f="objeto">—</div>
        </div>

        <div class="mt-4">
          <div class="text-slate-500 mb-1">Informações Gerais</div>
          <div class="rounded-xl border bg-slate-50 p-3 min-h-[36px]" data-f="info">—</div>
        </div>

        <div class="mt-4">
          <div class="text-slate-500 mb-1">Observações (Pontos Críticos)</div>
          <div class="rounded-xl border bg-slate-50 p-3 min-h-[36px]" data-f="obs">—</div>
        </div>
      </div>

      <!-- footer fixo -->
      <div class="sticky bottom-0 z-10 px-6 pb-6 pt-4 bg-white border-t">
        <div class="grid sm:grid-cols-2 gap-4">
          <a data-link="pendencias" class="flex items-center justify-center rounded-2xl border px-4 py-3 bg-slate-50 hover:bg-slate-100 transition">
            🛠️ <span class="ml-2 font-medium text-slate-800">Acompanhar Pendências</span>
          </a>
          <a data-link="projeto_licitacao" class="flex items-center justify-center rounded-2xl border px-4 py-3 bg-slate-50 hover:bg-slate-100 transition">
            🧾 <span class="ml-2 font-medium text-slate-800">Projeto e Licitação</span>
          </a>
          <a data-link="info_contratuais" class="flex items-center justify-center rounded-2xl border px-4 py-3 bg-slate-50 hover:bg-slate-100 transition">
            📄 <span class="ml-2 font-medium text-slate-800">Informações Contratuais</span>
          </a>
          <a data-link="medicoes" class="flex items-center justify-center rounded-2xl border px-4 py-3 bg-slate-50 hover:bg-slate-100 transition">
            📊 <span class="ml-2 font-medium text-slate-800">Acompanhamento de Medições</span>
          </a>
          <a data-link="cronograma" class="flex items-center justify-center rounded-2xl border px-4 py-3 bg-slate-50 hover:bg-slate-100 transition">
            📅 <span class="ml-2 font-medium text-slate-800">Cronograma</span>
          </a>
          <button type="button" data-btn-concluir class="flex items-center justify-center rounded-2xl border px-4 py-3 bg-slate-50 hover:bg-slate-100 transition">
            ✔️ <span class="ml-2 font-medium text-slate-800">Concluída</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>


      <section class="p-6">
        <?php if ($res && $res->num_rows): ?>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="cardsIniciativas">
            <?php while ($row = $res->fetch_assoc()): ?>
              <?php
              $id       = (int)$row['id'];
              $titulo   = htmlspecialchars($row['iniciativa'] ?? '', ENT_QUOTES, 'UTF-8');
              $contrato = htmlspecialchars($row['numero_contrato'] ?? '', ENT_QUOTES, 'UTF-8');
              $dtRaw = $row['data_vistoria'] ?? '';
              $dtFmt = '';
              if ($dtRaw) {
                $d = DateTime::createFromFormat('Y-m-d', $dtRaw);
                if ($d) $dtFmt = $d->format('d/m/Y');
              }
              $dt = htmlspecialchars($dtFmt, ENT_QUOTES, 'UTF-8');

              $status = htmlspecialchars($row['ib_status'] ?? '', ENT_QUOTES, 'UTF-8');
              $exec   = htmlspecialchars($row['ib_execucao'] ?? '', ENT_QUOTES, 'UTF-8');
              $prev   = htmlspecialchars($row['ib_previsto'] ?? '', ENT_QUOTES, 'UTF-8');
              $var    = htmlspecialchars($row['ib_variacao'] ?? '', ENT_QUOTES, 'UTF-8');
              ?>

              <article
                class="group cursor-pointer rounded-xl border border-slate-300 bg-slate-100 hover:border-blue-400 hover:shadow-md transition p-4"
                data-id="<?= $id ?>"
                data-iniciativa="<?= $titulo ?>"
                data-data_vistoria="<?= $dt ?>"
                data-status="<?= $status ?>"
                data-execucao="<?= $exec ?>"
                data-previsto="<?= $prev ?>"
                data-variacao="<?= $var ?>"
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
                  <div class="mt-1 text-xs text-slate-700">
                    Nº Contrato: <span class="font-medium"><?= $contrato ?: '—' ?></span>
                  </div>
                </header>

                <div class="flex items-center gap-2 text-xs">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 border text-slate-800">
                    <?= $status ?: 'Sem status' ?>
                  </span>
                  <span class="text-slate-700">Exec:</span>
                  <span class="font-medium"><?= $exec ?: '—' ?></span>
                  <span class="text-slate-700">Prev:</span>
                  <span class="font-medium"><?= $prev ?: '—' ?></span>
                </div>

                <footer class="mt-3 flex items-center justify-between">
                  <span class="text-xs text-slate-700">Atualização: <?= $dt ?: '—' ?></span>
                  <button type="button" class="text-blue-700 text-sm font-medium hover:underline" data-open-detalhes>
                    Detalhes
                  </button>
                </footer>
              </article>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div class="rounded-lg border border-dashed p-8 text-center text-slate-400">
            Nenhuma iniciativa nessa diretoria.
          </div>
        <?php endif; ?>
      </section>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('cardsIniciativas');
  if (!container) return;

  const tryOpen = (card) => {
    if (!card) return;
    if (typeof window.openWith === 'function') {
      window.openWith(card);
    } else {
      console.error('window.openWith não disponível');
      alert('Não foi possível abrir os detalhes agora.');
    }
  };

  container.addEventListener('click', (ev) => {
    const btn = ev.target.closest('[data-open-detalhes]');
    const card = btn ? btn.closest('article[data-id]')
                     : ev.target.closest('article[data-id]');
    if (!card) return;
    ev.preventDefault();
    tryOpen(card);
  });

  container.addEventListener('keydown', (ev) => {
    const card = ev.target.closest('article[data-id]');
    if (!card) return;
    if (ev.key === 'Enter' || ev.key === ' ') {
      ev.preventDefault();
      tryOpen(card);
    }
  });
});

(function () {
  const modal   = document.getElementById('modalDetalhes');
  const closeEl = modal.querySelector('[data-close-modal]');
  const btnX    = modal.querySelector('[data-btn-close]');

  const links = {
    pendencias: modal.querySelector('[data-link="pendencias"]'),
    projeto:    modal.querySelector('[data-link="projeto_licitacao"]'),
    info:       modal.querySelector('[data-link="info_contratuais"]'),
    medicoes:   modal.querySelector('[data-link="medicoes"]'),
    cronograma: modal.querySelector('[data-link="cronograma"]'),
  };

  let currentId = null;

  function set(el, value) {
    el.textContent = value && String(value).trim() ? value : '—';
  }

  function fillFrom(card) {
    const d = card.dataset;
    currentId = d.id;

    set(modal.querySelector('[data-f="title"]'),         d.iniciativa);
    set(modal.querySelector('[data-f="contrato"]'),      d.contrato);
    set(modal.querySelector('[data-f="data_vistoria"]'), d.data_vistoria);
    set(modal.querySelector('[data-f="status"]'),        d.status);
    set(modal.querySelector('[data-f="execucao"]'),      d.execucao);
    set(modal.querySelector('[data-f="previsto"]'),      d.previsto);
    set(modal.querySelector('[data-f="variacao"]'),      d.variacao);
    set(modal.querySelector('[data-f="valor_medio"]'),   d.valor_medio);
    set(modal.querySelector('[data-f="secretaria"]'),    d.secretaria);
    set(modal.querySelector('[data-f="diretoria"]'),     d.diretoria);
    set(modal.querySelector('[data-f="gestor"]'),        d.gestor);
    set(modal.querySelector('[data-f="fiscal"]'),        d.fiscal);
    set(modal.querySelector('[data-f="objeto"]'),        d.objeto);
    set(modal.querySelector('[data-f="info"]'),          d.info);
    set(modal.querySelector('[data-f="obs"]'),           d.obs);

    if (links.pendencias) links.pendencias.href = `index.php?page=acompanhamento&id_iniciativa=${encodeURIComponent(currentId)}`;
    if (links.projeto)    links.projeto.href    = `index.php?page=projeto_licitacoes&id_iniciativa=${encodeURIComponent(currentId)}`;
    if (links.info)       links.info.href       = `index.php?page=info_contratuais&id_iniciativa=${encodeURIComponent(currentId)}`;
    if (links.medicoes)   links.medicoes.href   = `index.php?page=medicoes&id_iniciativa=${encodeURIComponent(currentId)}`;
    if (links.cronograma)
  links.cronograma.href = `index.php?page=cronogramamarcos&id_iniciativa=${encodeURIComponent(currentId)}`;

  }

  function open(card) {
    fillFrom(card);
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  function close() {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }

  closeEl.addEventListener('click', close);
  btnX.addEventListener('click', close);
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
  });

  window.openWith = open;
})();
</script>

>>>>>>> 7a6b3a60ed50304554a32283faa4a38b5b504435
