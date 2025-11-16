<<<<<<< HEAD
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
=======
<?php
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

include("config.php");

if (!isset($_GET['id'])) {
    echo "ID não fornecido.";
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM iniciativas WHERE id = $id";
$resultado = $conexao->query($sql);

if ($resultado->num_rows == 0) {
    header("Location: visualizar.php");
    exit;
}

$row = $resultado->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $iniciativa = $_POST['iniciativa'];
    $ib_status = $_POST['ib_status'];
    $data_vistoria = $_POST['data_vistoria'];
    $numero_contrato = $_POST['numero_contrato'];
    
    $ib_secretaria = $_POST['ib_secretaria'];
    $ib_orgao = $_POST['ib_orgao'];
    $ib_diretoria = $_POST['ib_diretoria'];
    $ib_numero_processo_sei = $_POST['ib_numero_processo_sei'];
    $ib_gestor_responsavel = $_POST['ib_gestor_responsavel'];
    $ib_fiscal = $_POST['ib_fiscal'];

    $ib_execucao = $_POST['ib_execucao'];
    $ib_previsto = $_POST['ib_previsto'];
    $ib_variacao = $_POST['ib_variacao'];
    $ib_valor_medio = $_POST['ib_valor_medio'];
    $objeto = $_POST['objeto'];
    $informacoes_gerais = $_POST['informacoes_gerais'];
    $observacoes = $_POST['observacoes'];

    $update = "UPDATE iniciativas SET 
    iniciativa = '$iniciativa',
    numero_contrato = '$numero_contrato',
    ib_status = '$ib_status',
    data_vistoria = '$data_vistoria',
    ib_execucao = '$ib_execucao',
    ib_previsto = '$ib_previsto',
    ib_variacao = '$ib_variacao',
    ib_valor_medio = '$ib_valor_medio',
    ib_secretaria = '$ib_secretaria',
    ib_orgao = '$ib_orgao',
    ib_diretoria = '$ib_diretoria',
    ib_numero_processo_sei = '$ib_numero_processo_sei',
    ib_gestor_responsavel = '$ib_gestor_responsavel',
    ib_fiscal = '$ib_fiscal',
    objeto = '$objeto',
    informacoes_gerais = '$informacoes_gerais',
    observacoes = '$observacoes'
  WHERE id = $id";

    if ($conexao->query($update)) {
        header("Location: index.php?page=visualizar");
        exit;
    } else {
        echo "Erro ao atualizar: " . $conexao->error;
    }
}
?>

<div class="container">
  <h1>Editar Iniciativa</h1>
  <form method="post">
    <div class="linha">
      <div class="campo">
        <label>Iniciativa:</label>
        <input type="text" name="iniciativa" value="<?php echo htmlspecialchars($row['iniciativa']); ?>">
      </div>
      <div class="campo">
        <label>Status:</label>
        <select name="ib_status" required>
          <option value="Em Execução" <?php if ($row['ib_status'] == 'Em Execução') echo 'selected'; ?>>Em Execução</option>
          <option value="Paralizado" <?php if ($row['ib_status'] == 'Paralizado') echo 'selected'; ?>>Paralizado</option>
          <option value="Concluído" <?php if ($row['ib_status'] == 'Concluído') echo 'selected'; ?>>Concluído</option>
        </select>
      </div>
      
      <div class="campo">
        <label class="label">Nº do contrato</label>
        <div style="display: flex;">
          <?php
            $contrato_parts = explode('/', $row['numero_contrato']);
            $prefixo = $contrato_parts[0] ?? '';
            $ano = $contrato_parts[1] ?? '';
          ?>
          <input type="text" name="numero_contrato_prefixo" id="numero_contrato_prefixo" maxlength="3" placeholder="000" pattern="\d{3}" required style="flex: 0 0 60px; text-align: center;" value="<?php echo htmlspecialchars($prefixo); ?>">
          <span style="align-self: center; padding: 0 5px;">/</span>
          <input type="text" name="numero_contrato_ano" id="numero_contrato_ano" maxlength="4" placeholder="2025" pattern="\d{4}" required style="flex: 0 0 70px; text-align: center;" value="<?php echo htmlspecialchars($ano); ?>">
        </div>
      </div>

      <div class="campo">
        <label>Data da Atualização:</label>
        <input type="date" name="data_vistoria" value="<?php echo htmlspecialchars($row['data_vistoria']); ?>">
      </div>

      <div class="campo">
      <label>Diretoria:</label>
      <select name="ib_diretoria" required>
        <option value="">Selecione...</option>
        <option value="Educacao" <?php if ($row['ib_diretoria'] === 'Educacao') echo 'selected'; ?>>Educação</option>
        <option value="Saude" <?php if ($row['ib_orgao'] === 'Saude') echo 'selected'; ?>>Saúde</option>
        <option value="Seguranca" <?php if ($row['ib_orgao'] === 'Seguranca') echo 'selected'; ?>>Segurança</option>
        <option value="Infra Estrategicas" <?php if ($row['ib_orgao'] === 'Infra Estrategicas') echo 'selected'; ?>>Infra Estratégicas</option>
        <option value="Infra Grandes Obras" <?php if ($row['ib_orgao'] === 'Infra Grandes Obras') echo 'selected'; ?>>Infra Grandes Obras</option>
        <option value="Social" <?php if ($row['ib_orgao'] === 'Social') echo 'selected'; ?>>Social</option>
      </select>
    </div>
  </div>

    <div class="linha">
      <div class="campo">
        <label>Execução:</label>
        <input type="text" name="ib_execucao" value="<?php echo htmlspecialchars($row['ib_execucao']); ?>">
      </div>
      <div class="campo">
        <label>Previsto:</label>
        <input type="text" name="ib_previsto" value="<?php echo htmlspecialchars($row['ib_previsto']); ?>">
      </div>
      <div class="campo">
        <label>Variação:</label>
        <input type="text" name="ib_variacao" value="<?php echo htmlspecialchars($row['ib_variacao']); ?>">
      </div>
    </div>

    <div class="linha">
      <div class="campo">
        <label>Valor Medido Acumulado:</label>
        <input type="text" name="ib_valor_medio" value="<?php echo htmlspecialchars($row['ib_valor_medio']); ?>">
      </div>
      <div class="campo">
        <label>Secretaria:</label>
        <input type="text" name="ib_secretaria" value="<?php echo htmlspecialchars($row['ib_secretaria']); ?>">
      </div>
      <div class="campo">
        <label>Órgão:</label>
        <input type="text" name="ib_orgao" value="<?php echo htmlspecialchars($row['ib_orgao'] ?? ''); ?>">
      </div>
    </div>

    <div class="linha">
      <div class="campo">
        <label>Processo SEI:</label>
        <input type="text" name="ib_numero_processo_sei" value="<?php echo htmlspecialchars($row['ib_numero_processo_sei']); ?>" >
      </div>
      <div class="campo">
        <label>Gestor Responsável:</label>
        <input type="text" name="ib_gestor_responsavel" value="<?php echo htmlspecialchars($row['ib_gestor_responsavel']); ?>">
      </div>
      <div class="campo">
        <label>Fiscal Responsável:</label>
        <input type="text" name="ib_fiscal" value="<?php echo htmlspecialchars($row['ib_fiscal']); ?>">
      </div>
    </div>

    <div class="linha-atividade">
      <div class="campo">
        <label>OBJETO</label>
        <textarea name="objeto"><?php echo htmlspecialchars($row['objeto']); ?></textarea>
      </div>
      <div class="campo">
        <label>Informações Gerais</label>
        <textarea name="informacoes_gerais"><?php echo htmlspecialchars($row['informacoes_gerais']); ?></textarea>
      </div>
      <div class="campo">
        <label>OBSERVAÇÕES (PONTOS CRÍTICOS)</label>
        <textarea name="observacoes"><?php echo htmlspecialchars($row['observacoes']); ?></textarea>
      </div>
    </div>

    <button type="submit">Salvar Alterações</button>
    <?php if ($row['id_usuario'] == $_SESSION['id_usuario']): ?>
      <button type="button" onclick="abrirModal()" style="background-color: transparent; border: none; cursor: pointer; font-size: 18px; color: red; font-weight: bold;">delete</button>
    <?php endif; ?>
  </form>

  <div class="botao-voltar">
    <button class="btn-azul" onclick="window.location.href='index.php?page=visualizar';">&lt; Voltar</button>
  </div>
</div>

<div id="modalConfirmacao">
  <div>
    <p style="margin-bottom: 20px;">Tem certeza que deseja excluir esta iniciativa?</p>
    <button onclick="confirmarExclusao()">Sim</button>
    <button onclick="fecharModal()" style="background-color: #4da6ff; color: white;">Cancelar</button>
  </div>
</div>

<script>
  const idIniciativa = <?php echo $row['id']; ?>;
</script>
<script src="js/editar_iniciativa.js"></script>


>>>>>>> 7a6b3a60ed50304554a32283faa4a38b5b504435
