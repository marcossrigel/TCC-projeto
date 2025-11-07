<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
date_default_timezone_set('America/Recife');

require_once __DIR__ . '/config.php';

if (empty($_SESSION['id_usuario'])) {
  header('Location: ../login.php');
  exit;
}

$id_usuario = (int)($_SESSION['id_usuario'] ?? 0);

/**
 * Fonte do “setor”: tente primeiro diretoria da sessão (vinda do login),
 * depois $_SESSION['setor'], senão mostra “—”.
 * (Não faz SELECT em coluna inexistente.)
 */
$setorRaw = $_SESSION['diretoria'] ?? ($_SESSION['setor'] ?? '—');

$setoresMap = [
  'DAF'   => 'DAF - Diretoria de Administração e Finanças',
  'DOHDU' => 'DOHDU - Diretoria de Obras',
  'CELOE I' => 'CELOE I - Comissão de Licitação I',
  'CELOE II' => 'CELOE II - Comissão de Licitação II',
  'CELOSE' => 'CELOSE - Comissão de Licitação',
  'GCOMP' => 'GCOMP - Gerência de Compras',
  'GOP'   => 'GOP - Gerência de Orçamento e Planejamento',
  'GFIN'  => 'GFIN - Gerência Financeira',
  'GCONT' => 'GCONT - Gerência de Contabilidade',
  'DP'    => 'DP - Diretoria da Presidência',
  'GAD'   => 'GAD - Gerência Administrativa',
  'GAC'   => 'GAC - Gerência de Acompanhamento de Contratos',
  'CGAB'  => 'CGAB - Chefia de Gabinete',
  'DOE'   => 'DOE - Diretoria de Obras Estratégicas',
  'DSU'   => 'DSU - Diretoria de Obras de Saúde',
  'DSG'   => 'DSG - Diretoria de Obras de Segurança',
  'DED'   => 'DED - Diretoria de Obras de Educação',
  'SPO'   => 'SPO - Superintendência de Projetos de Obras',
  'SUAJ'  => 'SUAJ - Superintendência de Apoio Jurídico',
  'SUFIN' => 'SUFIN - Superintendência Financeira',
  'GAJ'   => 'GAJ - Gerência de Apoio Jurídico',
  'SUPLAN'=> 'SUPLAN - Superintendência de Planejamento',
  'DPH'   => 'DPH - Diretoria de Projetos Habitacionais',
];
if ($setorRaw !== '—' && strpos($setorRaw, ' - ') === false) {
  $setorRaw = $setoresMap[$setorRaw] ?? $setorRaw;
}
$_SESSION['setor'] = $setorRaw; // mantém padronizado
$setor = htmlspecialchars($setorRaw, ENT_QUOTES, 'UTF-8');

$nome  = htmlspecialchars($_SESSION['nome'] ?? 'Usuário', ENT_QUOTES, 'UTF-8');
$usuarioRede = $_SESSION['usuario_rede'] ?? null;

if ($usuarioRede === null) {
  $stmt = $conexao->prepare("SELECT usuario_rede FROM usuarios WHERE id_usuario = ?");
  $stmt->bind_param('i', $id_usuario);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $usuarioRede = $row['usuario_rede'] ?? '';
  $_SESSION['usuario_rede'] = $usuarioRede; // cache
}

$usuarioRedeEsc = htmlspecialchars($usuarioRede ?: '—', ENT_QUOTES, 'UTF-8');

/**
 * Iniciativas visíveis pelo usuário:
 * - criadas por ele OU
 * - compartilhadas com ele.
 */
$sql = "SELECT *
          FROM iniciativas
         WHERE id_usuario = $id_usuario
            OR EXISTS (
                 SELECT 1
                   FROM compartilhamentos c
                  WHERE c.id_iniciativa = iniciativas.id
                    AND c.id_compartilhado = $id_usuario
               )
         ORDER BY id DESC";
$iniciativas = $conexao->query($sql);
?>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<header class="w-full border-b bg-white shadow-sm">
  <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <img src="./img/logo.png" alt="CEHAB"
           class="h-8 w-auto object-contain select-none" draggable="false" />
      <div>
        <h1 class="text-slate-800 text-lg sm:text-xl font-semibold">Sistema de Monitoramento de Obras</h1>
        <p class="text-xs text-slate-600">
          Olá, <?= $nome ?>! <span class="text-slate-500">usuário: <?= $usuarioRedeEsc ?></span>
        </p>
      </div>
    </div>

    <nav class="flex items-center gap-2">
      <button type="button" data-action="criar"
        class="inline-flex items-center rounded-full bg-green-600 px-4 py-2 text-white text-sm font-semibold hover:bg-green-700 transition">
        Criar Iniciativa
      </button>

      <button type="button" data-action="compartilhar"
        class="inline-flex items-center rounded-full bg-blue-600 px-4 py-2 text-white text-sm font-semibold hover:bg-blue-700 transition">
        👥 Compartilhar
      </button>

      <!-- home.php está dentro de /templates, então o sair.php também -->
      <a href="templates/sair.php"
        class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-4 py-2 text-red-600 text-sm font-semibold hover:bg-red-100 transition">
          Sair
      </a>
    </nav>
  </div>
</header>

<main class="bg-slate-200 min-h-screen">
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-2xl border border-slate-300 bg-slate-50 shadow-md">

      <!-- Cabeçalho dentro da moldura -->
      <div class="p-6 border-b border-slate-200">
        <div class="text-sm text-slate-800 flex items-center gap-2">
          <span class="inline-flex items-center gap-2">
            <h6>Iniciativas Cadastradas</h6>
          </span>
        </div>
      </div>

      <!-- Cards de iniciativas -->
      <section class="p-6">
        <?php if ($iniciativas && $iniciativas->num_rows > 0): ?>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="cardsIniciativas">
            <?php while ($row = $iniciativas->fetch_assoc()): ?>
              <?php
                $status   = htmlspecialchars($row['ib_status'] ?? '', ENT_QUOTES, 'UTF-8');
                $execucao = htmlspecialchars($row['ib_execucao'] ?? '', ENT_QUOTES, 'UTF-8');
                $previsto = htmlspecialchars($row['ib_previsto'] ?? '', ENT_QUOTES, 'UTF-8');
                $variacao = htmlspecialchars($row['ib_variacao'] ?? '', ENT_QUOTES, 'UTF-8');
                $contrato = htmlspecialchars($row['numero_contrato'] ?? '', ENT_QUOTES, 'UTF-8');

                // >>> formata a data
                $dtRaw = $row['data_vistoria'] ?? '';
                $dtFmt = '';
                if ($dtRaw) {
                  $d = DateTime::createFromFormat('Y-m-d', $dtRaw);
                  if ($d) $dtFmt = $d->format('d/m/Y');
                }
                $dt = htmlspecialchars($dtFmt, ENT_QUOTES, 'UTF-8');

                $titulo   = htmlspecialchars($row['iniciativa'] ?? '', ENT_QUOTES, 'UTF-8');
                $id       = (int)$row['id'];
              ?>

              <article
                class="group cursor-pointer rounded-xl border border-slate-300 bg-slate-100 hover:border-blue-400 hover:shadow-md transition p-4"
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
                  <div class="mt-1 text-xs text-slate-700">
                    Nº Contrato: <span class="font-medium"><?= $contrato ?: '—' ?></span>
                  </div>
                </header>

                <div class="flex items-center gap-2 text-xs">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 border text-slate-800">
                    <?= $status ?: 'Sem status' ?>
                  </span>
                  <span class="text-slate-700">Exec:</span>
                  <span class="font-medium"><?= $execucao ?: '—' ?></span>
                  <span class="text-slate-700">Prev:</span>
                  <span class="font-medium"><?= $previsto ?: '—' ?></span>
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
            Nenhuma iniciativa cadastrada ainda.
          </div>
        <?php endif; ?>
      </section>
    </div>
  </div>
</main>

<!-- Modal: Criar Iniciativa -->
<div id="modalIniciativa" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" data-close-modal></div>
  <div class="absolute inset-0 flex items-start justify-center overflow-y-auto overflow-x-hidden p-2 sm:p-4">
    <div class="w-full sm:max-w-3xl md:max-w-4xl mt-8 bg-white rounded-2xl shadow-xl border overflow-hidden">
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-slate-800">Criar uma nova iniciativa</h3>
        <button type="button" class="rounded-lg px-3 py-1.5 text-slate-800 hover:bg-slate-100" data-close-modal>Fechar ×</button>
      </div>

      <form class="px-6 py-5 space-y-6" action="formulario.php" method="post" id="formIniciativa">
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
                <span class="self-center text-slate-700 select-none">/</span>
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
          <button type="button" class="rounded-full px-4 py-2 border border-slate-300 text-slate-800 hover:bg-slate-50" data-close-modal>Cancelar</button>
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
  <div class="absolute inset-0 bg-black/40" data-close-detalhes></div>
  <div class="absolute inset-0 flex items-start justify-center overflow-y-auto p-4">
    <div class="w-full sm:max-w-3xl bg-white rounded-2xl shadow-xl border overflow-hidden mt-10">
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-slate-800" id="det_titulo">Iniciativa</h3>
        <div class="flex items-center gap-2">
          <button type="button" id="btnEditarDetalhes"
                  class="rounded-lg px-3 py-1.5 text-blue-700 hover:bg-blue-50">Editar</button>
          <button type="button" class="rounded-lg px-3 py-1.5 text-slate-800 hover:bg-slate-100" data-close-detalhes>Fechar ×</button>
        </div>
      </div>

      <div class="px-6 py-5 space-y-4 text-sm">
        <div class="grid md:grid-cols-2 gap-4">
          <p><span class="text-slate-700">Data da Atualização:</span> <span class="font-medium" id="det_data"></span></p>
          <p><span class="text-slate-700">Nº do Contrato:</span> <span class="font-medium" id="det_contrato"></span></p>
          <p><span class="text-slate-700">Status:</span> <span class="font-medium" id="det_status"></span></p>
          <p><span class="text-slate-700">% Execução:</span> <span class="font-medium" id="det_execucao"></span></p>
          <p><span class="text-slate-700">% Previsto:</span> <span class="font-medium" id="det_previsto"></span></p>
          <p><span class="text-slate-700">% Variação:</span> <span class="font-medium" id="det_variacao"></span></p>
          <p><span class="text-slate-700">Valor Acumulado:</span> <span class="font-medium" id="det_valor"></span></p>
          <p><span class="text-slate-700">Secretaria:</span> <span class="font-medium" id="det_secretaria"></span></p>
          <p><span class="text-slate-700">Diretoria:</span> <span class="font-medium" id="det_diretoria"></span></p>
          <p><span class="text-slate-700">Gestor:</span> <span class="font-medium" id="det_gestor"></span></p>
          <p><span class="text-slate-700">Fiscal:</span> <span class="font-medium" id="det_fiscal"></span></p>
        </div>

        <div>
          <div class="text-slate-700 mb-1">Objeto</div>
          <div id="det_objeto" class="whitespace-pre-wrap"></div>
        </div>

        <div>
          <div class="text-slate-700 mb-1">Informações Gerais</div>
          <div id="det_info" class="whitespace-pre-wrap"></div>
        </div>

        <div>
          <div class="text-slate-700 mb-1">Observações (Pontos Críticos)</div>
          <div id="det_obs" class="whitespace-pre-wrap"></div>
        </div>
      </div>

      <div class="px-6 py-6 border-t">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <button id="btnPendencias"
            class="w-full rounded-xl border bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:bg-slate-100 flex items-center justify-center gap-2">
            <span>🛠</span> <span>Acompanhar Pendências</span>
          </button>
          <button id="btnProjeto"
            class="w-full rounded-xl border bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:bg-slate-100 flex items-center justify-center gap-2">
            <span>📋</span> <span>Projeto e Licitação</span>
          </button>
          <button id="btnContratuais"
            class="w-full rounded-xl border bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:bg-slate-100 flex items-center justify-center gap-2">
            <span>📄</span> <span>Informações Contratuais</span>
          </button>
          <button id="btnMedicoes"
            class="w-full rounded-xl border bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:bg-slate-100 flex items-center justify-center gap-2">
            <span>📊</span> <span>Acompanhamento de Medições</span>
          </button>
          <button id="btnCronograma"
            class="w-full rounded-xl border bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:bg-slate-100 flex items-center justify-center gap-2">
            <span>📆</span> <span>Cronograma</span>
          </button>
          <button id="btnConcluida"
            class="w-full rounded-xl border bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:bg-slate-100 flex items-center justify-center gap-2">
            <span>✔️</span> <span>Concluída</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Modal: Compartilhar Iniciativas -->
<div id="modalCompartilhar" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" data-close-compartilhar></div>
  <div class="absolute inset-0 flex items-start justify-center overflow-y-auto overflow-x-hidden p-2 sm:p-4">
    <div class="w-full sm:max-w-3xl md:max-w-4xl mt-8 bg-white rounded-2xl shadow-xl border overflow-hidden">
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-slate-800">Compartilhar iniciativas</h3>
        <button type="button" class="rounded-lg px-3 py-1.5 text-slate-800 hover:bg-slate-100" data-close-compartilhar>
          Fechar ×
        </button>
      </div>
      <div id="conteudoCompartilhar" class="px-6 py-5">
        <div class="text-slate-600">Carregando…</div>
      </div>
    </div>
  </div>
</div>

<script>
/* ===== Abertura dos modais do topo ===== */
document.querySelector('[data-action="criar"]')?.addEventListener('click', () => {
  document.getElementById('modalIniciativa')?.classList.remove('hidden');
});

document.querySelector('[data-action="compartilhar"]')?.addEventListener('click', async () => {
  const modal = document.getElementById('modalCompartilhar');
  const content = document.getElementById('conteudoCompartilhar');
  modal.classList.remove('hidden');
  content.innerHTML = '<div class="text-slate-600">Carregando…</div>';

  // carrega o HTML do modal
  const html = await (await fetch('templates/compartilhar.php', { cache: 'no-store' })).text();
  content.innerHTML = html;

  // ===== Autocomplete
  const input = document.getElementById('cmp_usuario');
  const sug = document.getElementById('cmp_sugestoes');
  let sugTimer = null;

  function hideSug(){ sug.classList.add('hidden'); }
  function showSug(){ sug.classList.remove('hidden'); }

  input.addEventListener('input', () => {
    clearTimeout(sugTimer);
    const termo = input.value.trim();
    if (termo.length < 2) { hideSug(); return; }
    sugTimer = setTimeout(async () => {
      const data = await (await fetch('templates/compartilhar_buscar_usuario.php?termo=' + encodeURIComponent(termo))).json();
      sug.innerHTML = '';
      if (!data.length) { hideSug(); return; }
      data.forEach(txt => {
        const row = document.createElement('div');
        row.className = 'px-3 py-2 hover:bg-slate-50 cursor-pointer';
        row.textContent = txt;
        row.onclick = () => { input.value = txt; hideSug(); };
        sug.appendChild(row);
      });
      showSug();
    }, 200);
  });
  document.addEventListener('click', (e) => { if (!input.contains(e.target)) hideSug(); }, { once:false });

  // ===== Selecionar todas
  const selAll = document.getElementById('cmp_todos');
  const cbs = [...content.querySelectorAll('input[name="iniciativas[]"]')];
  selAll?.addEventListener('change', () => cbs.forEach(cb => cb.checked = selAll.checked));
  cbs.forEach(cb => cb.addEventListener('change', () => {
    selAll.checked = cbs.length && cbs.every(x => x.checked);
  }));

  // ===== Submit compartilhar
  const form = document.getElementById('formCompartilhar');
  form?.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const fd = new FormData(form);
    const resp = await fetch('templates/salvar_compartilhamento.php', { method:'POST', body: fd });
    const txt = await resp.text();
    if (txt.trim() === 'OK') {
      toast('Compartilhado com sucesso!', 'ok');
      // recarrega a lista “Já compartilhado com”
      const html2 = await (await fetch('templates/compartilhar_modal.php', { cache: 'no-store' })).text();
      content.innerHTML = html2; // simples refresh do conteúdo
    } else {
      toast('Falha: ' + txt, 'err');
    }
  });

  // ===== Remover compartilhamento
  function wireRemove() {
    content.querySelectorAll('.cmp-remover').forEach(btn => {
      btn.onclick = async () => {
        if (!confirm('Deseja remover este compartilhamento?')) return;
        const id = btn.dataset.id;
        const resp = await fetch('templates/remover_compartilhamento.php', {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},
          body:'id_compartilhado=' + encodeURIComponent(id)
        });
        const t = await resp.text();
        if (t.trim() === 'OK') {
          toast('Removido!', 'ok');
          // atualiza painel
          const html3 = await (await fetch('templates/compartilhar_modal.php', { cache: 'no-store' })).text();
          content.innerHTML = html3;
          wireRemove();
        } else {
          toast('Erro ao remover', 'err');
        }
      };
    });
  }
  wireRemove();
});

// fecha modal por backdrop/botão
document.getElementById('modalCompartilhar')?.addEventListener('click', (ev) => {
  if (ev.target.hasAttribute('data-close-compartilhar')) ev.currentTarget.classList.add('hidden');
});

// helper de toast (já tem no seu arquivo; mantenha apenas uma versão)
function toast(msg, type='ok') {
  const t = document.createElement('div');
  t.textContent = msg;
  t.className = 'fixed top-4 right-4 z-[60] px-4 py-2 rounded-lg shadow ' +
                (type==='ok' ? 'bg-green-600 text-white' : 'bg-red-600 text-white');
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2200);
}

/* ===== Modal de Detalhes (com edição) ===== */
(function() {
  const modal = document.getElementById('modalDetalhes');

  const FIELD_MAP = {
    data_vistoria : { elId: 'det_data',        type: 'date' },
    numero_contrato: { elId: 'det_contrato',   type: 'text' },
    ib_status     : { elId: 'det_status',      type: 'select', options: ['Em Execução', 'Paralizado', 'Concluido'] },
    ib_execucao   : { elId: 'det_execucao',    type: 'text' },
    ib_previsto   : { elId: 'det_previsto',    type: 'text' },
    ib_variacao   : { elId: 'det_variacao',    type: 'text' },
    ib_valor_medio: { elId: 'det_valor',       type: 'text' },
    ib_secretaria : { elId: 'det_secretaria',  type: 'text' },
    ib_diretoria  : { elId: 'det_diretoria',   type: 'text' },
    ib_gestor_responsavel: { elId: 'det_gestor', type: 'text' },
    ib_fiscal     : { elId: 'det_fiscal',      type: 'text' },
    objeto        : { elId: 'det_objeto',      type: 'textarea' },
    informacoes_gerais: { elId: 'det_info',    type: 'textarea' },
    observacoes   : { elId: 'det_obs',         type: 'textarea' },
  };

  let originalValues = {};
  let isEditing = false;
  let currentId = null;

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

    currentId = el.dataset.id;
    leaveEditMode(true);
    modal.classList.remove('hidden');

    document.getElementById('btnPendencias').onclick = () =>
      window.location.href = 'index.php?page=acompanhamento&id_iniciativa=' + currentId;
    document.getElementById('btnProjeto').onclick = () =>
      window.location.href = 'index.php?page=projeto_licitacoes&id_iniciativa=' + currentId;
    document.getElementById('btnContratuais').onclick = () =>
      window.location.href = 'index.php?page=info_contratuais&id_iniciativa=' + currentId;
    document.getElementById('btnMedicoes').onclick = () =>
      window.location.href = 'index.php?page=medicoes&id_iniciativa=' + currentId;
    document.getElementById('btnCronograma').onclick = () =>
      window.location.href = 'index.php?page=cronogramamarcos&id_iniciativa=' + currentId;
    document.getElementById('btnConcluida').onclick = markDone;
  }

  function enterEditMode() {
    if (isEditing) return;
    isEditing = true;

    originalValues = {};
    for (const [name, cfg] of Object.entries(FIELD_MAP)) {
      const span = document.getElementById(cfg.elId);
      const raw = (span.textContent || '').trim();
      originalValues[name] = raw === '—' ? '' : raw;

      let input;
      if (cfg.type === 'textarea') {
        input = document.createElement('textarea');
        input.className = 'w-full min-h-[80px] border rounded-lg px-2 py-1';
        input.value = originalValues[name];
      } else if (cfg.type === 'select') {
        input = document.createElement('select');
        input.className = 'border rounded-lg px-2 py-1';
        (cfg.options || []).forEach(opt => {
          const o = document.createElement('option');
          o.value = opt; o.textContent = opt;
          if (opt === originalValues[name]) o.selected = true;
          input.appendChild(o);
        });
      } else if (cfg.type === 'date') {
        input = document.createElement('input');
        input.type = 'date';
        input.className = 'border rounded-lg px-2 py-1';
        const v = originalValues[name];
        const m = v.match(/^(\d{4})-(\d{2})-(\d{2})$/) || v.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (m) input.value = (m[3] ? `${m[3]}-${m[2]}-${m[1]}` : v);
        else input.value = v;
      } else {
        input = document.createElement('input');
        input.type = 'text';
        input.className = 'border rounded-lg px-2 py-1';
        input.value = originalValues[name];
      }
      input.dataset.bind = name;
      span.replaceWith(input);
      input.id = cfg.elId;
    }

    const btnEdit = document.getElementById('btnEditarDetalhes');
    btnEdit.textContent = 'Salvar';
    btnEdit.classList.remove('text-blue-700');
    btnEdit.classList.add('bg-blue-600','text-white','hover:bg-blue-700','px-4','rounded-full');
    btnEdit.onclick = saveChanges;

    let btnCancel = document.getElementById('btnCancelarEdicao');
    if (!btnCancel) {
      btnCancel = document.createElement('button');
      btnCancel.id = 'btnCancelarEdicao';
      btnCancel.type = 'button';
      btnCancel.className = 'rounded-full px-4 py-1.5 border border-slate-300 text-slate-700 hover:bg-slate-50 ml-2';
      btnCancel.textContent = 'Cancelar';
      document.querySelector('#modalDetalhes .border-b .flex.items-center.gap-2').insertBefore(
        btnCancel,
        document.querySelector('[data-close-detalhes]')
      );
    }
    btnCancel.onclick = () => leaveEditMode(false);
  }

  function leaveEditMode(resetSpans) {
    const btnEdit = document.getElementById('btnEditarDetalhes');
    btnEdit.textContent = 'Editar';
    btnEdit.className   = 'rounded-lg px-3 py-1.5 text-blue-700 hover:bg-blue-50';
    btnEdit.onclick     = enterEditMode;
    document.getElementById('btnCancelarEdicao')?.remove();

    if (!isEditing) return;
    isEditing = false;

    if (!resetSpans) {
      for (const [name, cfg] of Object.entries(FIELD_MAP)) {
        const input = document.getElementById(cfg.elId);
        const span  = document.createElement('span');
        span.id = cfg.elId;
        span.textContent = originalValues[name] || '—';
        input.replaceWith(span);
      }
    }
  }

  async function saveChanges() {
    const payload = { id_iniciativa: currentId };
    for (const [name, cfg] of Object.entries(FIELD_MAP)) {
      const el = document.getElementById(cfg.elId);
      let val = (el.value ?? '').trim();
      if (cfg.type === 'date' && val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/)) {
        const [,d,m,y] = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        val = `${y}-${m}-${d}`;
      }
      payload[name] = val;
    }

    try {
      const resp = await fetch('atualizar_iniciativa.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
      });
      const data = await resp.json();
      if (!resp.ok || !data.ok) throw new Error(data.error || 'Falha ao salvar');

      for (const [name, cfg] of Object.entries(FIELD_MAP)) {
        const el = document.getElementById(cfg.elId);
        const span = document.createElement('span');
        span.id = cfg.elId;
        span.textContent = payload[name] || '—';
        el.replaceWith(span);
      }
      leaveEditMode(true);
      toast('Alterações salvas!', 'ok');
    } catch (e) {
      toast('Não foi possível salvar. ' + e.message, 'err');
    }
  }

  async function markDone() {
    try {
      const resp = await fetch('marcar_concluida.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
        body: 'id_iniciativa=' + encodeURIComponent(currentId)
      });
      if (resp.ok) {
        const btn = document.getElementById('btnConcluida');
        btn.innerHTML = '<span>✅</span> <span>Concluído</span>';
      }
    } catch(e) {}
  }

  function toast(msg, type='ok') {
    const t = document.createElement('div');
    t.textContent = msg;
    t.className = 'fixed top-4 right-4 z-[60] px-4 py-2 rounded-lg shadow ' +
                  (type==='ok' ? 'bg-green-600 text-white' : 'bg-red-600 text-white');
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2500);
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

  document.getElementById('btnEditarDetalhes')?.addEventListener('click', enterEditMode);
})();

/* ===== Fechar modal: Criar Iniciativa ===== */
(() => {
  const modal = document.getElementById('modalIniciativa');
  const form  = document.getElementById('formIniciativa');

  // fecha ao clicar no backdrop OU em qualquer elemento com data-close-modal
  modal?.addEventListener('click', (ev) => {
    const isClose = ev.target.matches('[data-close-modal]') ||
                    ev.target.closest?.('[data-close-modal]');
    if (isClose) {
      modal.classList.add('hidden');
      form?.reset(); // opcional: limpa o formulário ao fechar
    }
  });

  // fecha com ESC
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') modal?.classList.add('hidden');
  });
})();


</script>
