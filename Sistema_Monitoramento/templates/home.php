<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
date_default_timezone_set('America/Recife');

$nome  = htmlspecialchars($_SESSION['nome']  ?? 'Usuário', ENT_QUOTES, 'UTF-8');
$setor = htmlspecialchars($_SESSION['setor'] ?? '—',       ENT_QUOTES, 'UTF-8');

// Caminho da logo CEHAB (a que você enviou)
$LOGO_PATH = 'assets/img/logo-cehab-azul.png'; // coloque aqui o arquivo da logo que você enviou
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>CEHAB - Sistema de Monitoramento</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/home.css">
</head>
<body class="bg-slate-100 min-h-screen font-[Inter]">
  <!-- Topbar -->
  <header class="w-full border-b bg-white shadow-sm">
    <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <!-- Logo CEHAB -->
        <img src="<?= htmlspecialchars($LOGO_PATH, ENT_QUOTES, 'UTF-8') ?>"
             alt="CEHAB"
             class="h-8 w-auto object-contain select-none" draggable="false"/>

        <h1 class="text-slate-800 text-lg sm:text-xl font-semibold">
          CEHAB - Sistema de Monitoramento
        </h1>
      </div>

      <nav class="flex items-center gap-2">
        <button type="button" data-action="criar"
          class="inline-flex items-center rounded-full bg-green-600 px-4 py-2 text-white text-sm font-semibold hover:bg-green-700 transition">
          Criar Iniciativa
        </button>

        <button type="button" data-action="vistorias"
          class="inline-flex items-center rounded-full bg-slate-600 px-4 py-2 text-white text-sm font-semibold hover:bg-slate-700 transition">
          Minhas Vistorias
        </button>

        <button type="button" data-action="sair"
          class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-4 py-2 text-red-600 text-sm font-semibold hover:bg-red-100 transition">
          Sair
        </button>
      </nav>
    </div>
  </header>

  <!-- Conteúdo -->
  <main class="mx-auto max-w-7xl px-4 py-6">
    <!-- Faixa de setor -->
    <div class="mb-4">
      <div class="text-sm text-slate-600 flex items-center gap-2">
        <span class="inline-flex items-center gap-2">
          <span class="text-slate-500">Setor do usuário:</span>
          <span class="chip"><?= $setor ?></span>
        </span>
      </div>
    </div>

    <!-- Card principal -->
    <section class="bg-white rounded-xl shadow-sm border p-6">
      <div class="flex flex-col gap-2">
        <p class="text-slate-700">Olá, <span class="font-semibold"><?= $nome ?></span>!</p>
      </div>

      <div class="mt-6 rounded-lg border border-dashed p-8 text-center text-slate-400">
        Área de conteúdo (cards, buscas, listas) — deixe em branco por enquanto.
      </div>
    </section>
  </main>

  <!-- Modal: Criar Iniciativa -->
<div id="modalIniciativa" class="fixed inset-0 z-50 hidden">
  <!-- backdrop -->
  <div class="absolute inset-0 bg-black/40" data-close-modal></div>

  <!-- content -->
  <div class="absolute inset-0 flex items-start justify-center overflow-y-auto p-4">
    <div class="w-full max-w-4xl mt-8 bg-white rounded-2xl shadow-xl border">
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-slate-800">Criar uma nova iniciativa</h3>
        <button type="button" class="rounded-lg px-3 py-1.5 text-slate-600 hover:bg-slate-100" data-close-modal>Fechar ×</button>
      </div>

      <!-- FORM: mantém os MESMOS names/ids do seu formulario.php -->
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

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nº do contrato</label>
            <div class="flex gap-2">
              <input type="text" name="numero_contrato_prefixo" id="numero_contrato_prefixo"
                     maxlength="3" placeholder="000" pattern="\d{3}" required
                     class="w-20 text-center border rounded-lg px-2 py-2">
              <span class="self-center">/</span>
              <input type="text" name="numero_contrato_ano" id="numero_contrato_ano"
                     maxlength="4" placeholder="2025" pattern="\d{4}" required
                     class="w-24 text-center border rounded-lg px-2 py-2">
            </div>
            <input type="hidden" name="numero_contrato" id="numero_contrato">
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1">Informações Básicas</label>
          <div class="grid md:grid-cols-5 gap-4">
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
            <div class="md:col-span-1 md:col-start-5">
              <label class="block text-sm text-slate-700 mb-1">Valor Medido Acumulado</label>
              <input type="text" name="ib_valor_medio" class="w-full border rounded-lg px-3 py-2">
            </div>
          </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
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
          <div class="md:col-span-3">
            <label class="block text-sm text-slate-700 mb-1">Nº Processo SEI</label>
            <input type="text" name="ib_numero_processo_sei" class="w-full border rounded-lg px-3 py-2">
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
          <button type="button" class="rounded-full px-4 py-2 border border-slate-300 text-slate-600 hover:bg-slate-50"
                  data-close-modal>Cancelar</button>
          <button type="submit" name="submit" id="submit" class="rounded-full px-5 py-2 bg-blue-600 text-white font-semibold hover:bg-blue-700">
            Criar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

</body>

  <script src="js/home.js"></script>

</html>
