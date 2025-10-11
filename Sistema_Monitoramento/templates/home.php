<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
date_default_timezone_set('America/Recife');

require_once __DIR__ . '/config.php';

if (empty($_SESSION['id_usuario'])) {
  header('Location: ../login.php');
  exit;
}

$id_usuario   = (int)($_SESSION['id_usuario'] ?? 0);

// dono OU compartilhadas com ele
$sql = "SELECT * FROM iniciativas
        WHERE id_usuario = $id_usuario
           OR EXISTS (
              SELECT 1 FROM compartilhamentos c
              WHERE c.id_iniciativa = iniciativas.id
                AND c.id_compartilhado = $id_usuario
           )
        ORDER BY id DESC";
$iniciativas = $conexao->query($sql);

$nome  = htmlspecialchars($_SESSION['nome']  ?? 'Usuário', ENT_QUOTES, 'UTF-8');
$setor = htmlspecialchars($_SESSION['setor'] ?? '—',       ENT_QUOTES, 'UTF-8');
$msg   = $_GET['msg'] ?? '';
?>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">


<!-- Topbar -->
<header class="w-full border-b bg-white shadow-sm">
  <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <img src="assets/img/logo-cehab-azul.png" alt="CEHAB"
           class="h-8 w-auto object-contain select-none" draggable="false" />
      <h1 class="text-slate-800 text-lg sm:text-xl font-semibold">
        CEHAB - Sistema de Monitoramento
      </h1>
    </div>

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

<!-- Modal: Criar Iniciativa -->
<div id="modalIniciativa" class="fixed inset-0 z-50 hidden">
  <!-- backdrop -->
  <div class="absolute inset-0 bg-black/40" data-close-modal></div>

  <!-- content -->
  <div class="absolute inset-0 flex items-start justify-center overflow-y-auto overflow-x-hidden p-2 sm:p-4">
    <div class="w-full sm:max-w-3xl md:max-w-4xl mt-8 bg-white rounded-2xl shadow-xl border overflow-hidden">
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-slate-800">Criar uma nova iniciativa</h3>
        <button type="button" class="rounded-lg px-3 py-1.5 text-slate-600 hover:bg-slate-100" data-close-modal>Fechar ×</button>
      </div>

      <!-- FORM: mesmos names/ids do seu projeto -->
      <form class="px-6 py-5 space-y-6" action="templates/formulario.php"  method="post" id="formIniciativa">

        <div class="grid md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Nome da Iniciativa</label>
            <input list="lista-iniciativas" name="iniciativa" class="w-full border rounded-lg px-3 py-2"
                   required placeholder="Digite ou selecione" maxlength="255">
            <datalist id="lista-iniciativas">
              <option value="Creche - Lote 01 (Cabrobó)">
              <option value="Creche - Lote 01 (Granito)">
              <option value="Creche - Lote 01 (Lagoa Grande)">
              <option value="Creche - Lote 01 (Ouricuri)">
              <option value="Creche - Lote 02 (Mirandiba)">
              <option value="Creche - Lote 02 (Serra T 01)">
              <option value="Creche - Lote 02 (Serra T 02)">
              <option value="Creche - Lote 02 (Triunfo)">
              <option value="Creche - Lote 02 (Tuparetama)">
              <option value="Creche - Lote 03 (Arcoverde)">
              <option value="Creche - Lote 03 (Custódia)">
              <option value="Creche - Lote 03 (Ibimirim)">
              <option value="Creche - Lote 03 (Itíba)">
              <option value="Creche - Lote 03 (Pedra)">
              <option value="Creche - Lote 04 (Garanhuns Terreno 01)">
              <option value="Creche - Lote 04 (Garanhuns Terreno 02)">
              <option value="Creche - Lote 04 (Paranatama)">
              <option value="Creche - Lote 04 (São Bento do una)">
              <option value="Creche - Lote 05 (Belo Jardim)">
              <option value="Creche - Lote 05 (Brejo da Madre de Deus)">
              <option value="Creche - Lote 05 (Jataúba)">
              <option value="Creche - Lote 05 (Taquaritinga do Norte)">
              <option value="Creche - Lote 05 (São Bento do una)">
              <option value="Creche - Lote 05 (Vertentes)">
              <option value="Creche - Lote 06 (Belém de Maria)">
              <option value="Creche - Lote 06 (Bezerros)">
              <option value="Creche - Lote 06 (Caruaru 06 - Salgado)">
              <option value="Creche - Lote 06 (Caruaru 02 - Vila Cipó)">
              <option value="Creche - Lote 06 (Caruaru 03 - Rendeiras)">
              <option value="Creche - Lote 06 (Caruaru 04 - Xique Xique)">
              <option value="Creche - Lote 06 (Catende)">
              <option value="Creche - Lote 06 (São Joaquim do Monte)">
              <option value="Creche - Lote 07 (Vicência)">
              <option value="Creche - Lote 07 (Timbaúba)">
              <option value="Creche - Lote 07 (Camutanga)">
              <option value="Creche - Lote 07 (Bom Jardim)">
              <option value="Creche - Lote 07 (Araçoiaba)">
              <option value="Creche - Lote 08 (São José da Coroa Grande)">
              <option value="Creche - Lote 08 (Jaboatão Terreno 04 Muribeca)">
              <option value="Creche - Lote 08 (Cabo de Santo Agostinho)">
              <option value="Creche - Lote 08 (Jaboatão Terreno 01 Rio Dourado)">
              <option value="Creche - Lote 08 (Moreno)">
              <option value="Creche - Lote 08 (Jaboatão Terreno 02 Candeias)">
              <option value="Creche - Lote 08 (Ipojuca)">
              <option value="Creche - Lote 09 (Areias)">
              <option value="Creche - Lote 09 (Itamaraca)">
              <option value="Creche - Lote 09 (Camaragibe 01)">
              <option value="Creche - Lote 09 (Igarassu 01)">
              <option value="Creche - Lote 09 (Camaragibe 02)">
              <option value="Creche - Lote 09 (Igarassu 02)">
              <option value="Creche - Lote 09 (Olinda)">
            </datalist>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Data da Atualização</label>
            <input type="date" name="data_vistoria" class="w-full border rounded-lg px-3 py-2" required>
          </div>
        </div>

        <!-- Informações Básicas -->
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1">Informações Básicas</label>

          <div class="grid md:grid-cols-5 gap-4 [&>div]:min-w-0">
            <div>
              <label class="block text-sm text-slate-700 mb-1">Status</label>
              <select name="ib_status" class="w-full border rounded-lg px-3 py-2" required>
                <option value="">Selecione...</option>
                <option value="Em Execução">Em Execução</option>
                <option value="Paralizado">Paralizado</option>
                <option value="Concluido">Concluido</option>
              </select>
            </div>

            <div>
              <label class="block text-sm text-slate-700 mb-1">% Execução</label>
              <input type="text" name="ib_execucao" placeholder="visualização" readonly class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
              <label class="block text-sm text-slate-700 mb-1">% Previsto</label>
              <input type="text" name="ib_previsto" class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
              <label class="block text-sm text-slate-700 mb-1">% Variação</label>
              <input type="text" name="ib_variacao" id="ib_variacao" placeholder="visualização" readonly class="w-full border rounded-lg px-3 py-2">
            </div>

            <div class="min-w-0">
              <label class="block text-sm text-slate-700 mb-1">Nº do contrato</label>
              <div class="flex items-center gap-2">
                <input type="text" name="numero_contrato_prefixo" id="numero_contrato_prefixo"
                       maxlength="3" placeholder="000" pattern="\d{3}" required
                       class="border rounded-lg px-2 py-2 text-center w-[68px] shrink-0">
                <span class="self-center text-slate-500 select-none">/</span>
                <input type="text" name="numero_contrato_ano" id="numero_contrato_ano"
                       maxlength="4" placeholder="2025" pattern="\d{4}" required
                       class="border rounded-lg px-2 py-2 text-center w-[84px] shrink-0">
              </div>
              <input type="hidden" name="numero_contrato" id="numero_contrato">
            </div>
          </div>

          <div class="grid md:grid-cols-5 gap-4 mt-4 [&>div]:min-w-0">
            <div>
              <label class="block text-sm text-slate-700 mb-1">Valor Acumulado</label>
              <input type="text" name="ib_valor_medio" class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
              <label class="block text-sm text-slate-700 mb-1">Secretaria</label>
              <input type="text" name="ib_secretaria" class="w-full border rounded-lg px-3 py-2" placeholder="Digite a secretaria">
            </div>

            <div>
              <label class="block text-sm text-slate-700 mb-1">Diretoria</label>
              <select name="ib_diretoria" class="w-full border rounded-lg px-3 py-2" required>
                <option value="">Selecione...</option>
                <option value="Seguranca">Segurança</option>
                <option value="Educacao">Educação</option>
                <option value="Saude">Saúde</option>
                <option value="Infra Estrategicas">Infra Estratégicas</option>
                <option value="Infra Grandes Obras">Infra Grandes Obras</option>
                <option value="Social">Social</option>
              </select>
            </div>

            <div>
              <label class="block text-sm text-slate-700 mb-1">Gestor Responsável</label>
              <input type="text" name="ib_gestor_responsavel" class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
              <label class="block text-sm text-slate-700 mb-1">Fiscal Responsável</label>
              <input type="text" name="ib_fiscal" class="w-full border rounded-lg px-3 py-2">
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm text-slate-700 mb-1">OBJETO (opcional)</label>
          <textarea name="objeto" class="w-full border rounded-lg px-3 py-2 min-h-[90px]"></textarea>
        </div>

        <hr class="border-slate-200">

        <div>
          <label class="block text-sm text-slate-700 mb-1">Informações Gerais (opcional)</label>
          <textarea name="informacoes_gerais" class="w-full border rounded-lg px-3 py-2 min-h-[90px]"></textarea>
        </div>

        <div>
          <label class="block text-sm text-slate-700 mb-1">OBSERVAÇÕES (PONTOS CRÍTICOS) (opcional)</label>
          <textarea name="observacoes" class="w-full border rounded-lg px-3 py-2 min-h-[90px]"></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-1 pb-6">
          <button type="button" class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 hover:bg-slate-50" data-close-modal>Cancelar</button>
          <button type="submit" name="submit" id="submit" class="rounded-full px-5 py-2 bg-blue-600 text-white font-semibold hover:bg-blue-700">
            Criar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Detalhes da Iniciativa -->
<div id="modalDetalhes" class="fixed inset-0 z-50 hidden">
  <!-- backdrop -->
  <div class="absolute inset-0 bg-black/40" data-close-detalhes></div>

  <!-- content -->
  <div class="absolute inset-0 flex items-start justify-center overflow-y-auto p-4">
    <div class="w-full sm:max-w-3xl bg-white rounded-2xl shadow-xl border overflow-hidden mt-10">
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-slate-800" id="det_titulo">Iniciativa</h3>
        <button type="button" class="rounded-lg px-3 py-1.5 text-slate-600 hover:bg-slate-100" data-close-detalhes>Fechar ×</button>
      </div>

      <div class="px-6 py-5 space-y-4 text-sm">
        <div class="grid md:grid-cols-2 gap-4">
          <p><span class="text-slate-500">Data da Atualização:</span> <span class="font-medium" id="det_data"></span></p>
          <p><span class="text-slate-500">Nº do Contrato:</span> <span class="font-medium" id="det_contrato"></span></p>
          <p><span class="text-slate-500">Status:</span> <span class="font-medium" id="det_status"></span></p>
          <p><span class="text-slate-500">% Execução:</span> <span class="font-medium" id="det_execucao"></span></p>
          <p><span class="text-slate-500">% Previsto:</span> <span class="font-medium" id="det_previsto"></span></p>
          <p><span class="text-slate-500">% Variação:</span> <span class="font-medium" id="det_variacao"></span></p>
          <p><span class="text-slate-500">Valor Acumulado:</span> <span class="font-medium" id="det_valor"></span></p>
          <p><span class="text-slate-500">Secretaria:</span> <span class="font-medium" id="det_secretaria"></span></p>
          <p><span class="text-slate-500">Diretoria:</span> <span class="font-medium" id="det_diretoria"></span></p>
          <p><span class="text-slate-500">Gestor:</span> <span class="font-medium" id="det_gestor"></span></p>
          <p><span class="text-slate-500">Fiscal:</span> <span class="font-medium" id="det_fiscal"></span></p>
        </div>

        <div>
          <div class="text-slate-500 mb-1">Objeto</div>
          <div id="det_objeto" class="whitespace-pre-wrap"></div>
        </div>

        <div>
          <div class="text-slate-500 mb-1">Informações Gerais</div>
          <div id="det_info" class="whitespace-pre-wrap"></div>
        </div>

        <div>
          <div class="text-slate-500 mb-1">Observações (Pontos Críticos)</div>
          <div id="det_obs" class="whitespace-pre-wrap"></div>
        </div>
      </div>

      <!-- Ações (grade 2x3, centradas) -->
      <div class="px-6 py-6 border-t">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

          <button id="btnPendencias"
            class="w-full rounded-xl border bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:bg-slate-100
                  flex items-center justify-center gap-2">
            <span>🛠</span> <span>Acompanhar Pendências</span>
          </button>

          <button id="btnProjeto"
            class="w-full rounded-xl border bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:bg-slate-100
                  flex items-center justify-center gap-2">
            <span>📋</span> <span>Projeto e Licitação</span>
          </button>

          <button id="btnContratuais"
            class="w-full rounded-xl border bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:bg-slate-100
                  flex items-center justify-center gap-2">
            <span>📄</span> <span>Informações Contratuais</span>
          </button>

          <button id="btnMedicoes"
            class="w-full rounded-xl border bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:bg-slate-100
                  flex items-center justify-center gap-2">
            <span>📊</span> <span>Acompanhamento de Medições</span>
          </button>

          <button id="btnCronograma"
            class="w-full rounded-xl border bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:bg-slate-100
                  flex items-center justify-center gap-2">
            <span>📆</span> <span>Cronograma</span>
          </button>

          <button id="btnConcluida"
            class="w-full rounded-xl border bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:bg-slate-100
                  flex items-center justify-center gap-2">
            <span>✔️</span> <span>Concluída</span>
          </button>

        </div>
      </div>

    </div>
  </div>
</div>

<script>
/* ===== Modal Criar Iniciativa ===== */
(function () {
  const modalCriar = document.getElementById('modalIniciativa');

  // Abrir
  document.querySelector('[data-action="criar"]')?.addEventListener('click', () => {
    modalCriar?.classList.remove('hidden');
    setTimeout(() => {
      document.querySelector('#modalIniciativa input[name="iniciativa"]')?.focus();
    }, 0);
  });

  // Fechar (backdrop/botão)
  modalCriar?.addEventListener('click', (e) => {
    if (e.target.hasAttribute('data-close-modal') || e.target.closest('[data-close-modal]')) {
      modalCriar.classList.add('hidden');
    }
  });

  // Fechar com ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') modalCriar?.classList.add('hidden');
  });

  // Monta numero_contrato = "prefixo/ano"
  const prefixo = document.getElementById('numero_contrato_prefixo');
  const ano     = document.getElementById('numero_contrato_ano');
  const hidden  = document.getElementById('numero_contrato');
  function updateNC() {
    if (!hidden) return;
    const p = (prefixo?.value || '').replace(/\D/g, '');
    const a = (ano?.value || '').replace(/\D/g, '');
    hidden.value = (p && a) ? `${p}/${a}` : '';
  }
  prefixo?.addEventListener('input', updateNC);
  ano?.addEventListener('input', updateNC);
  updateNC();
})();

/* ===== Modal de Detalhes (se você mantiver) ===== */
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

    // Navegações
    document.getElementById('btnPendencias').onclick = () =>
      window.location.href = 'index.php?page=acompanhamento&id_iniciativa=' + id;

    document.getElementById('btnProjeto').onclick = () =>
      window.location.href = 'index.php?page=projeto_licitacoes&id_iniciativa=' + id;

    document.getElementById('btnContratuais').onclick = () =>
      window.location.href = 'index.php?page=info_contratuais&id_iniciativa=' + id;

    document.getElementById('btnMedicoes').onclick = () =>
      window.location.href = 'index.php?page=medicoes&id_iniciativa=' + id;

    document.getElementById('btnCronograma').onclick = () =>
      window.location.href = 'index.php?page=cronogramamarcos&id_iniciativa=' + id;

    // Concluída (ajuste o endpoint se já tiver outro)
    document.getElementById('btnConcluida').onclick = async () => {
      try {
        const resp = await fetch('templates/marcar_concluida.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
          body: 'id_iniciativa=' + encodeURIComponent(id)
        });
        if (resp.ok) {
          // feedback rápido no botão
          const btn = document.getElementById('btnConcluida');
          btn.innerHTML = '<span>✅</span> <span>Concluído</span>';
        }
      } catch(e) {}
    };

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

