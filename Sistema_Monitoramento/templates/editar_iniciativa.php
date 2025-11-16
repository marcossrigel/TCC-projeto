<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
date_default_timezone_set('America/Recife');

require_once __DIR__ . '/config.php';

if (empty($_SESSION['id_usuario'])) {
  header('Location: ../login.php');
  exit;
}

$id_usuario = (int)($_SESSION['id_usuario'] ?? 0);

$id_iniciativa = isset($_GET['id_iniciativa']) ? (int)$_GET['id_iniciativa'] : 0;
if ($id_iniciativa <= 0) {
  echo "ID de iniciativa inválido.";
  exit;
}

$stmt = $conexao->prepare("
  SELECT 
    iniciativa,
    data_vistoria,
    numero_contrato,
    ib_secretaria,
    ib_diretoria,
    ib_gestor_responsavel,
    ib_fiscal,
    objeto,
    informacoes_gerais,
    observacoes
  FROM iniciativas
  WHERE id = ?
");
$stmt->bind_param('i', $id_iniciativa);
$stmt->execute();
$result = $stmt->get_result();
$obra = $result->fetch_assoc();

if (!$obra) {
  echo "Iniciativa não encontrada.";
  exit;
}

$data_vistoria_value = '';
if (!empty($obra['data_vistoria'])) {
  $data_vistoria_value = $obra['data_vistoria'];
}

$erro = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data_vistoria        = $_POST['data_vistoria'] ?? null; // esperado Y-m-d
  $numero_contrato      = trim($_POST['numero_contrato'] ?? '');
  $ib_secretaria        = trim($_POST['ib_secretaria'] ?? '');
  $ib_diretoria         = trim($_POST['ib_diretoria'] ?? '');
  $ib_gestor_responsavel= trim($_POST['ib_gestor_responsavel'] ?? '');
  $ib_fiscal            = trim($_POST['ib_fiscal'] ?? '');
  $objeto               = trim($_POST['objeto'] ?? '');
  $informacoes_gerais   = trim($_POST['informacoes_gerais'] ?? '');
  $observacoes          = trim($_POST['observacoes'] ?? '');

  $sql = "
    UPDATE iniciativas
       SET data_vistoria        = ?,
           numero_contrato      = ?,
           ib_secretaria        = ?,
           ib_diretoria         = ?,
           ib_gestor_responsavel= ?,
           ib_fiscal            = ?,
           objeto               = ?,
           informacoes_gerais   = ?,
           observacoes          = ?
     WHERE id = ?
  ";
  $stmt2 = $conexao->prepare($sql);
  $stmt2->bind_param(
    'sssssssssi',
    $data_vistoria,
    $numero_contrato,
    $ib_secretaria,
    $ib_diretoria,
    $ib_gestor_responsavel,
    $ib_fiscal,
    $objeto,
    $informacoes_gerais,
    $observacoes,
    $id_iniciativa
  );

  if ($stmt2->execute()) {
    header("Location: index.php?page=home&msg=atualizado");
    exit;
  } else {
    $erro = "Erro ao atualizar a iniciativa. Tente novamente.";
  }

}

function e($v) {
  return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Editar Iniciativa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-100 min-h-screen" style="font-family: 'Inter', system-ui, sans-serif;">

  <header class="w-full border-b bg-white shadow-sm">
    <div class="mx-auto max-w-5xl px-4 py-3 flex items-center justify-between">
      <div>
        <h1 class="text-slate-800 text-lg sm:text-xl font-semibold">
          Editar Iniciativa
        </h1>
        <p class="text-xs text-slate-600">
          <?= e($obra['iniciativa']) ?>
        </p>
      </div>
      <a href="index.php?page=home"
        class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
        ← Voltar
      </a>


    </div>
  </header>

  <main class="mx-auto max-w-5xl px-4 py-8">
    <div class="rounded-2xl border border-slate-300 bg-white shadow-md p-6 space-y-6">

      <?php if ($erro): ?>
        <div class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">
          <?= e($erro) ?>
        </div>
      <?php endif; ?>

      <form method="post" class="space-y-6">

        <div class="grid md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              Data da Atualização
            </label>
            <input type="date" name="data_vistoria"
                   value="<?= e($data_vistoria_value) ?>"
                   class="w-full border rounded-lg px-3 py-2">
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">
              Nº do Contrato
            </label>
            <input type="text" name="numero_contrato"
                   value="<?= e($obra['numero_contrato']) ?>"
                   class="w-full border rounded-lg px-3 py-2">
          </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              Secretaria
            </label>
            <input type="text" name="ib_secretaria"
                   value="<?= e($obra['ib_secretaria']) ?>"
                   class="w-full border rounded-lg px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              Diretoria
            </label>
            <input type="text" name="ib_diretoria"
                   value="<?= e($obra['ib_diretoria']) ?>"
                   class="w-full border rounded-lg px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              Gestor Responsável
            </label>
            <input type="text" name="ib_gestor_responsavel"
                   value="<?= e($obra['ib_gestor_responsavel']) ?>"
                   class="w-full border rounded-lg px-3 py-2">
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              Fiscal Responsável
            </label>
            <input type="text" name="ib_fiscal"
                   value="<?= e($obra['ib_fiscal']) ?>"
                   class="w-full border rounded-lg px-3 py-2">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Objeto
          </label>
          <textarea name="objeto"
                    class="w-full border rounded-lg px-3 py-2 min-h-[90px]"><?= e($obra['objeto']) ?></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Informações Gerais
          </label>
          <textarea name="informacoes_gerais"
                    class="w-full border rounded-lg px-3 py-2 min-h-[90px]"><?= e($obra['informacoes_gerais']) ?></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Observações (Pontos Críticos)
          </label>
          <textarea name="observacoes"
                    class="w-full border rounded-lg px-3 py-2 min-h-[90px]"><?= e($obra['observacoes']) ?></textarea>
        </div>

        <div class="flex items-center justify-end gap-2">
          <a href="index.php?page=home"
            class="rounded-full px-4 py-2 border border-slate-300 text-slate-800 hover:bg-slate-50">
            Cancelar
          </a>

          <button type="submit"
                  class="rounded-full px-5 py-2 bg-blue-600 text-white font-semibold hover:bg-blue-700">
            Salvar alterações
          </button>
        </div>

      </form>
    </div>
  </main>

</body>

</html>
