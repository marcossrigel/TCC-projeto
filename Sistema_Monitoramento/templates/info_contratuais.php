<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

include_once('config.php');
mysqli_set_charset($conexao, "utf8mb4");
if (!$conexao) {
    die("Erro na conexão com o banco: " . mysqli_connect_error());
}

$id_usuario_logado = (int)$_SESSION['id_usuario'];
$id_iniciativa = isset($_POST['id_iniciativa'])
  ? (int)$_POST['id_iniciativa']
  : (isset($_GET['id_iniciativa']) ? (int)$_GET['id_iniciativa'] : 0);

// --- Resolve DONO e valida permissão ---
// --- Resolve DONO e valida permissão ---
$stmt = $conexao->prepare("
  SELECT id_usuario AS id_dono, iniciativa, ib_diretoria
  FROM iniciativas
  WHERE id = ?
");
$stmt->bind_param("i", $id_iniciativa);
$stmt->execute();
$res = $stmt->get_result();
$ini = $res->fetch_assoc();
if (!$ini) { die("Iniciativa não encontrada."); }

$id_dono          = (int)$ini['id_dono'];
$nome_iniciativa  = $ini['iniciativa'] ?? 'Iniciativa Desconhecida';
$diretoria        = trim($ini['ib_diretoria'] ?? '');

$tipo_usuario = $_SESSION['tipo_usuario'] ?? '';

// >>> BYPASS PARA ADMIN <<<
$temAcesso = ($tipo_usuario === 'admin') || ($id_usuario_logado === $id_dono);

if (!$temAcesso) {
  $stmt = $conexao->prepare("
    SELECT 1 FROM compartilhamentos
    WHERE id_dono = ? AND id_compartilhado = ? AND id_iniciativa = ?
    LIMIT 1
  ");
  $stmt->bind_param("iii", $id_dono, $id_usuario_logado, $id_iniciativa);
  $stmt->execute();
  $temAcesso = (bool)$stmt->get_result()->fetch_row();
}
if (!$temAcesso) { die("Você não tem permissão para acessar esta iniciativa."); }


// --- Salvar sempre no DONO ---
if (!function_exists('formatar_moeda')) {
    function formatar_moeda($valor) {
        if ($valor === null || $valor === '') return 'R$ ';
        return 'R$ ' . number_format((float)$valor, 2, ',', '.');
    }
}

if (isset($_POST['salvar'])) {
    $processo_licitatorio    = mysqli_real_escape_string($conexao, $_POST['processo_licitatorio'] ?? '');
    $empresa                 = mysqli_real_escape_string($conexao, $_POST['empresa'] ?? '');
    $data_assinatura_contrato= $_POST['data_assinatura_contrato'] ?? null;
    $data_os                 = $_POST['data_os'] ?? null;
    $prazo_execucao_original = $_POST['prazo_execucao_original'] ?? '';
    $prazo_execucao_atual    = $_POST['prazo_execucao_atual'] ?? '';

    function limpar_valor_decimal($valor) {
      $valor = trim((string)$valor);
      if ($valor === '' || strtolower($valor) === 'r$') return "NULL";
      $valor = str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $valor);
      return is_numeric($valor) ? $valor : "NULL";
    }

    function soma_valores($v1, $v2) {
      // v1 e v2 saem de limpar_valor_decimal
      if ($v1 === "NULL" && $v2 === "NULL") {
          return "NULL";
      }
      $n1 = ($v1 === "NULL") ? 0 : (float)$v1;
      $n2 = ($v2 === "NULL") ? 0 : (float)$v2;
      $soma = $n1 + $n2;
      return is_numeric($soma) ? $soma : "NULL";
    }   

    $valor_inicial_obra      = limpar_valor_decimal($_POST['valor_inicial_obra'] ?? '');
    $valor_aditivo_obra      = limpar_valor_decimal($_POST['valor_aditivo_obra'] ?? '');
    $valor_total_obra        = soma_valores($valor_inicial_obra, $valor_aditivo_obra);

    $valor_inicial_contrato  = limpar_valor_decimal($_POST['valor_inicial_contrato'] ?? '');
    $valor_aditivo           = limpar_valor_decimal($_POST['valor_aditivo'] ?? '');
    $valor_contrato          = soma_valores($valor_inicial_contrato, $valor_aditivo);

    $cod_subtracao           = mysqli_real_escape_string($conexao, $_POST['cod_subtracao'] ?? '');
    $secretaria_demandante   = mysqli_real_escape_string($conexao, $_POST['secretaria_demandante'] ?? '');

    // Existe registro de contratuais do DONO?
    $q = mysqli_query($conexao, "SELECT id FROM contratuais WHERE id_usuario = $id_dono AND id_iniciativa = $id_iniciativa LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        $query_update = "
          UPDATE contratuais SET 
            processo_licitatorio='$processo_licitatorio',
            empresa='$empresa',
            data_assinatura_contrato=" . ($data_assinatura_contrato ? "'$data_assinatura_contrato'" : "NULL") . ",
            data_os=" . ($data_os ? "'$data_os'" : "NULL") . ",
            prazo_execucao_original='$prazo_execucao_original',
            prazo_execucao_atual='$prazo_execucao_atual',
            valor_inicial_obra=$valor_inicial_obra,
            valor_aditivo_obra=$valor_aditivo_obra,
            valor_total_obra=$valor_total_obra,
            valor_inicial_contrato=$valor_inicial_contrato,
            valor_aditivo=$valor_aditivo,
            valor_contrato=$valor_contrato,
            cod_subtracao='$cod_subtracao',
            secretaria_demandante='$secretaria_demandante'
          WHERE id_usuario=$id_dono AND id_iniciativa=$id_iniciativa
        ";
        mysqli_query($conexao, $query_update);
    } else {
        $query_insert = "
          INSERT INTO contratuais (
            id_usuario, id_iniciativa, processo_licitatorio, empresa, data_assinatura_contrato, data_os, 
            prazo_execucao_original, prazo_execucao_atual, 
            valor_inicial_obra, valor_aditivo_obra, valor_total_obra, 
            valor_inicial_contrato, valor_aditivo, valor_contrato, 
            cod_subtracao, secretaria_demandante
          ) VALUES (
            $id_dono, $id_iniciativa, '$processo_licitatorio', '$empresa', " . 
            ($data_assinatura_contrato ? "'$data_assinatura_contrato'" : "NULL") . ", " .
            ($data_os ? "'$data_os'" : "NULL") . ", 
            '$prazo_execucao_original', '$prazo_execucao_atual', 
            $valor_inicial_obra, $valor_aditivo_obra, $valor_total_obra, 
            $valor_inicial_contrato, $valor_aditivo, $valor_contrato, 
            '$cod_subtracao', '$secretaria_demandante'
          )
        ";
        mysqli_query($conexao, $query_insert);
    }

    $valor_total_para_medicoes = $valor_total_obra; 
    $valor_bm_para_medicoes    = $valor_aditivo; 

    $qMed = mysqli_query($conexao, "
      SELECT COUNT(*) AS total 
      FROM medicoes 
      WHERE id_usuario = $id_dono AND id_iniciativa = $id_iniciativa
    ");
    $temMedicoes = $qMed ? ((int)mysqli_fetch_assoc($qMed)['total'] > 0) : false;

    if ($temMedicoes) {
        mysqli_query($conexao, "
          UPDATE medicoes 
          SET valor_orcamento = $valor_total_para_medicoes, 
              valor_bm       = $valor_bm_para_medicoes
          WHERE id_usuario = $id_dono AND id_iniciativa = $id_iniciativa
        ");
    } else {
        mysqli_query($conexao, "
          INSERT INTO medicoes (id_usuario, id_iniciativa, valor_orcamento, valor_bm, data_registro)
          VALUES ($id_dono, $id_iniciativa, $valor_total_para_medicoes, $valor_bm_para_medicoes, NOW())
        ");
    }


    header("Location: index.php?page=info_contratuais&id_iniciativa=$id_iniciativa");
    exit;
}

// --- Carrega dados SEMPRE do DONO ---
$qContr = mysqli_query($conexao, "
  SELECT * FROM contratuais 
  WHERE id_usuario = $id_dono AND id_iniciativa = $id_iniciativa
  LIMIT 1
");
$dados = mysqli_fetch_assoc($qContr);

// --- URL do botão Voltar ---
if ($tipo_usuario === 'admin') {
  $url_voltar = $diretoria
    ? 'index.php?page=visualizar&diretoria=' . rawurlencode($diretoria)
    : 'index.php?page=diretorias';
} else {
  $url_voltar = 'index.php?page=home';
}

?>

  <div class="container">
    <form method="post" action="index.php?page=info_contratuais">

      <input type="hidden" name="id_iniciativa" value="<?php echo $id_iniciativa; ?>">
      <div class="main-title"><?php echo htmlspecialchars($nome_iniciativa); ?> - Informações Contratuais</div>
      <table>
        <tr><th class="hide-mobile">Campo</th><th class="hide-mobile">Valor</th></tr>
        <tr><td>Processo Licitatório</td><td><input type="text" name="processo_licitatorio" value="<?php echo $dados['processo_licitatorio'] ?? ''; ?>"></td></tr>
        <tr><td>Empresa</td><td><input type="text" name="empresa" value="<?php echo $dados['empresa'] ?? ''; ?>"></td></tr>
        <tr><td>Data Assinatura do Contrato</td><td><input type="date" name="data_assinatura_contrato" value="<?php echo $dados['data_assinatura_contrato'] ?? ''; ?>" required></td></tr>
        <tr><td>Data da O.S.</td><td><input type="date"  name="data_os" value="<?php echo $dados['data_os'] ?? ''; ?>" required></td></tr>
        <tr><td>Prazo de Execução Original</td><td><input type="text" name="prazo_execucao_original" value="<?php echo $dados['prazo_execucao_original'] ?? ''; ?>"></td></tr>
        <tr><td>Prazo de Execução Atual</td><td><input type="text" name="prazo_execucao_atual" value="<?php echo $dados['prazo_execucao_atual'] ?? ''; ?>"></td></tr>
        
        <tr><td>Valor Inicial da Obra</td>
            <td><input type="text" class="dinheiro" name="valor_inicial_obra" id="valor_inicial_obra"
                      value="<?php echo formatar_moeda($dados['valor_inicial_obra'] ?? ''); ?>"></td></tr>
        <tr><td>Valor de Aditivo da Obra</td>
            <td><input type="text" class="dinheiro" name="valor_aditivo_obra" id="valor_aditivo_obra"
                      value="<?php echo formatar_moeda($dados['valor_aditivo_obra'] ?? ''); ?>"></td></tr>
        <tr><td>Valor Total da Obra</td>
            <td><input type="text" class="dinheiro" name="valor_total_obra" id="valor_total_obra"
               value="<?php echo formatar_moeda($dados['valor_total_obra'] ?? ''); ?>" readonly>
            </td></tr>
        <tr><td>Valor Inicial do Contrato</td>
            <td><input type="text" class="dinheiro" name="valor_inicial_contrato" id="valor_inicial_contrato"
                      value="<?php echo formatar_moeda($dados['valor_inicial_contrato'] ?? ''); ?>"></td></tr>
        <tr><td>Valor do Aditivo do Contrato</td>
            <td><input type="text" class="dinheiro" name="valor_aditivo" id="valor_aditivo"
                      value="<?php echo formatar_moeda($dados['valor_aditivo'] ?? ''); ?>"></td></tr>
        <tr><td>Valor Total do Contrato</td>
        <td><input type="text" class="dinheiro" name="valor_contrato" id="valor_contrato"
                  value="<?php echo formatar_moeda($dados['valor_contrato'] ?? ''); ?>" readonly></td></tr>
        
        <tr><td>Subação (LOA)</td><td><input type="text" name="cod_subtracao" value="<?php echo $dados['cod_subtracao'] ?? ''; ?>"></td></tr>
        <tr><td>Secretaria Demandante</td><td><input type="text" name="secretaria_demandante" value="<?php echo $dados['secretaria_demandante'] ?? ''; ?>"></td></tr>
      </table>
      <div class="button-group">
        <button type="submit" name="salvar" style="background-color:rgb(42, 179, 0);">Salvar</button>
        <button type="button"
          onclick="window.location.href='<?php echo htmlspecialchars($url_voltar, ENT_QUOTES, 'UTF-8'); ?>';">
          &lt; Voltar
        </button>
      </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function brToFloat(v) {
        if (!v) return 0;
        v = v.replace(/[R$\s\.]/g, '').replace(',', '.');
        var n = parseFloat(v);
        return isNaN(n) ? 0 : n;
    }

    function floatToBr(v) {
        return v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function calcTotalObra() {
        var ini = brToFloat(document.getElementById('valor_inicial_obra').value);
        var ad  = brToFloat(document.getElementById('valor_aditivo_obra').value);
        var total = ini + ad;
        var campo = document.getElementById('valor_total_obra');
        campo.value = total > 0 ? ('R$ ' + floatToBr(total)) : 'R$ ';
    }

    function calcTotalContrato() {
        var ini = brToFloat(document.getElementById('valor_inicial_contrato').value);
        var ad  = brToFloat(document.getElementById('valor_aditivo').value);
        var total = ini + ad;
        var campo = document.getElementById('valor_contrato');
        campo.value = total > 0 ? ('R$ ' + floatToBr(total)) : 'R$ ';
    }

    ['valor_inicial_obra', 'valor_aditivo_obra'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', calcTotalObra);
            el.addEventListener('blur',  calcTotalObra);
        }
    });

    ['valor_inicial_contrato', 'valor_aditivo'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', calcTotalContrato);
            el.addEventListener('blur',  calcTotalContrato);
        }
    });

    // já calcula ao carregar a página (quando vier dados do banco)
    calcTotalObra();
    calcTotalContrato();
});
</script>
