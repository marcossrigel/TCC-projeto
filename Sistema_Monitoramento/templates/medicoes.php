<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['id_usuario'])) { header('Location: login.php'); exit; }
include_once('config.php');
mysqli_set_charset($conexao, "utf8mb4");

$id_iniciativa     = isset($_GET['id_iniciativa']) ? (int)$_GET['id_iniciativa'] : 0;
$id_usuario_logado = (int)$_SESSION['id_usuario'];
$tipo_usuario      = $_SESSION['tipo_usuario'] ?? '';

// ---- Resolve DONO, diretoria e valida permissão ----
$stmt = $conexao->prepare("
  SELECT id_usuario AS id_dono, iniciativa, ib_diretoria
  FROM iniciativas
  WHERE id = ?
");
$stmt->bind_param("i", $id_iniciativa);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row) { die("Iniciativa não encontrada."); }

$id_dono          = (int)$row['id_dono'];
$nome_iniciativa  = $row['iniciativa'] ?? 'Iniciativa Desconhecida';
$diretoria        = trim($row['ib_diretoria'] ?? '');

$temAcesso = ($tipo_usuario === 'admin') || ($id_usuario_logado === $id_dono);
if (!$temAcesso) {
  $stmt = $conexao->prepare("
    SELECT 1 FROM compartilhamentos
    WHERE id_dono=? AND id_compartilhado=? AND id_iniciativa=? LIMIT 1
  ");
  $stmt->bind_param("iii", $id_dono, $id_usuario_logado, $id_iniciativa);
  $stmt->execute();
  $temAcesso = (bool)$stmt->get_result()->fetch_row();
}
if (!$temAcesso) { die("Sem permissão para acessar esta iniciativa."); }

// ---- Helpers null-safe ----
function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function money_br($v){ return ($v===null||$v==='') ? 'R$ ' : 'R$ '.number_format((float)$v,2,',','.'); }

// ---- Salvar (sempre no DONO) ----
if (isset($_POST['salvar'])) {
  $valor_orcamento = $_POST['valor_orcamento'] ?? [];
  $valor_bm        = $_POST['valor_bm'] ?? [];
  $saldo_obra      = $_POST['saldo_obra'] ?? [];
  $bm              = $_POST['bm'] ?? [];
  $numero_sei      = $_POST['numero_processo_sei'] ?? [];
  $data_inicio     = $_POST['data_inicio'] ?? [];
  $data_fim        = $_POST['data_fim'] ?? [];
  $ids             = $_POST['ids'] ?? [];

  $cleanMoney = function($v){
    $v = (string)$v;
    $v = preg_replace('/[^0-9,.-]/','',$v);
    $v = str_replace('.','',$v);
    $v = str_replace(',','.',$v);
    return is_numeric($v) ? $v : "0";
  };

  for ($i=0; $i<count($valor_orcamento); $i++){
    $id_existente = (int)($ids[$i] ?? 0);
    $orc  = $cleanMoney($valor_orcamento[$i] ?? '');
    $bmv  = $cleanMoney($valor_bm[$i] ?? '');
    $sld  = $cleanMoney($saldo_obra[$i] ?? '');
    $bm_s = mysqli_real_escape_string($conexao, $bm[$i] ?? '');
    $sei  = mysqli_real_escape_string($conexao, $numero_sei[$i] ?? '');
    $ini  = !empty($data_inicio[$i]) ? "'".mysqli_real_escape_string($conexao,$data_inicio[$i])."'" : "NULL";
    $fim  = !empty($data_fim[$i])    ? "'".mysqli_real_escape_string($conexao,$data_fim[$i])."'"    : "NULL";
    $agora= date('Y-m-d H:i:s');

    if ($id_existente > 0) {
      $sql = "UPDATE medicoes SET 
        valor_orcamento='$orc', valor_bm='$bmv', saldo_obra='$sld', bm='$bm_s',
        data_inicio=$ini, data_fim=$fim, numero_processo_sei='$sei'
      WHERE id=$id_existente AND id_usuario=$id_dono AND id_iniciativa=$id_iniciativa";
    } else {
      $sql = "INSERT INTO medicoes
        (id_usuario,id_iniciativa,valor_orcamento,valor_bm,saldo_obra,bm,data_inicio,data_fim,data_registro,numero_processo_sei)
        VALUES ($id_dono,$id_iniciativa,'$orc','$bmv','$sld','$bm_s',$ini,$fim,'$agora','$sei')";
    }
    mysqli_query($conexao,$sql);
  }

  header("Location: index.php?page=medicoes&id_iniciativa=$id_iniciativa");
  exit;
}

// ---- Exclusão (do DONO) ----
if (!empty($_POST['excluir_ids'])) {
  foreach ($_POST['excluir_ids'] as $id_excluir) {
    $id_excluir = (int)$id_excluir;
    mysqli_query($conexao, "DELETE FROM medicoes WHERE id=$id_excluir AND id_usuario=$id_dono AND id_iniciativa=$id_iniciativa");
  }
}

// ---- Carregar linhas do DONO ----
$dados = mysqli_query($conexao, "
  SELECT * FROM medicoes 
  WHERE id_usuario=$id_dono AND id_iniciativa=$id_iniciativa
  ORDER BY data_inicio, id
");

// ---- URL do botão Voltar ----
if ($tipo_usuario === 'admin') {
  $url_voltar = $diretoria
    ? 'index.php?page=visualizar&diretoria=' . rawurlencode($diretoria)
    : 'index.php?page=diretorias';
} else {
  $url_voltar = 'index.php?page=home';
}
?>

<div class="container">
  <h2><?php echo e($nome_iniciativa); ?> - Acompanhamento de Medições</h2>

  <form method="post" action="index.php?page=medicoes&id_iniciativa=<?php echo $id_iniciativa; ?>">
    <div class="table-wrapper">
      <table id="medicoes">
        <thead>
          <tr>
            <th>Valor Total da Obra</th>
            <th>Valor BM</th>
            <th>Saldo da Obra</th>
            <th>BM</th>
            <th>Nº Processo SEI</th>
            <th>Data Início</th>
            <th>Data Fim</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($linha = mysqli_fetch_assoc($dados)) : ?>
          <tr data-id="<?php echo (int)$linha['id']; ?>">
            <input type="hidden" name="ids[]" value="<?php echo (int)$linha['id']; ?>">
            <td><input type="text" name="valor_orcamento[]" value="<?php echo money_br($linha['valor_orcamento'] ?? null); ?>"></td>
            <td><input type="text" name="valor_bm[]"        value="<?php echo money_br($linha['valor_bm'] ?? null); ?>"></td>
            <td><input type="text" name="saldo_obra[]"      value="<?php echo money_br($linha['saldo_obra'] ?? null); ?>"></td>

            <td><input type="text" name="bm[]" value="<?php echo e($linha['bm'] ?? ''); ?>"></td>
            <td><input type="text" name="numero_processo_sei[]" value="<?php echo e($linha['numero_processo_sei'] ?? ''); ?>"></td>
            <td><input type="date" name="data_inicio[]" value="<?php echo e($linha['data_inicio'] ?? ''); ?>"></td>
            <td><input type="date" name="data_fim[]"    value="<?php echo e($linha['data_fim'] ?? ''); ?>"></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>

      <div class="buttons">
        <button type="button" onclick="adicionarLinha()">Adicionar Linha</button>
        <button type="button" onclick="removerLinha()">Excluir Linha</button>
        <button type="submit" name="salvar">Salvar</button>
        <button type="button" onclick="window.location.href='<?php echo htmlspecialchars($url_voltar, ENT_QUOTES, 'UTF-8'); ?>';">&lt; Voltar</button>
      </div>
    </div>
  </form>
</div>
<script src="js/medicoes.js"></script>
