<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['id_usuario'])) { header('Location: login.php'); exit; }

require_once 'config.php';
mysqli_set_charset($conexao, 'utf8mb4');

// 1) Pega parâmetros e sessão
$id_iniciativa      = isset($_GET['id_iniciativa']) ? (int)$_GET['id_iniciativa'] : 0;
$id_usuario_logado  = (int)($_SESSION['id_usuario'] ?? 0);
$tipo_usuario       = $_SESSION['tipo_usuario'] ?? '';

if ($id_iniciativa <= 0) { die('Iniciativa inválida.'); }

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

$id_dono         = (int)$ini['id_dono'];
$nome_iniciativa = $ini['iniciativa'] ?? 'Iniciativa Desconhecida';
$diretoria       = trim($ini['ib_diretoria'] ?? '');

// 3) Permissão (bypass para admin)
$temAcesso = ($tipo_usuario === 'admin') || ($id_usuario_logado === $id_dono);

if (!$temAcesso) {
  $stmt = $conexao->prepare("
    SELECT 1
      FROM compartilhamentos
     WHERE id_dono = ?
       AND id_compartilhado = ?
       AND id_iniciativa = ?
     LIMIT 1
  ");
  $stmt->bind_param("iii", $id_dono, $id_usuario_logado, $id_iniciativa);
  $stmt->execute();
  $temAcesso = (bool)$stmt->get_result()->fetch_row();
}

if (!$temAcesso) { die("Sem permissão para acessar esta iniciativa."); }

// 4) (o restante do seu código: salvar POST, listar pendências, HTML, etc.)


if (isset($_POST['salvar'])) {
  $problemas      = $_POST['problema']      ?? [];
  $contramedidas  = $_POST['contramedida']  ?? [];
  $prazos         = $_POST['prazo']         ?? [];
  $responsaveis   = $_POST['responsavel']   ?? [];
  $ids            = $_POST['ids']           ?? [];

  for ($i = 0; $i < count($problemas); $i++) {
    $id_existente = (int)($ids[$i] ?? 0);
    $problema     = mysqli_real_escape_string($conexao, $problemas[$i] ?? '');
    $contramedida = mysqli_real_escape_string($conexao, $contramedidas[$i] ?? '');
    $responsavel  = mysqli_real_escape_string($conexao, $responsaveis[$i] ?? '');

    $prazo_bruto  = trim($prazos[$i] ?? '');
    $prazo_sql    = $prazo_bruto === '' ? "NULL" : "'" . mysqli_real_escape_string($conexao, $prazo_bruto) . "'";

    if ($id_existente > 0) {
      $query = "
        UPDATE pendencias SET
          problema='$problema',
          contramedida='$contramedida',
          prazo=$prazo_sql,
          responsavel='$responsavel'
        WHERE id = $id_existente
          AND id_usuario = $id_dono
          AND id_iniciativa = $id_iniciativa
      ";
    } else {
      $query = "
        INSERT INTO pendencias
          (id_usuario, id_iniciativa, problema, contramedida, prazo, responsavel)
        VALUES
          ($id_dono, $id_iniciativa, '$problema', '$contramedida', $prazo_sql, '$responsavel')
      ";
    }
    mysqli_query($conexao, $query);
  }
}

$dados_pendencias = mysqli_query(
  $conexao,
  "SELECT * FROM pendencias
   WHERE id_usuario = $id_dono AND id_iniciativa = $id_iniciativa
   ORDER BY id ASC"
);

// URL do botão Voltar
if ($tipo_usuario === 'admin') {
  // Admin volta para a lista da mesma diretoria (visualizar.php)
  $url_voltar = 'index.php?page=visualizar&diretoria=' . rawurlencode($diretoria ?: 'Educacao');
  // Se preferir voltar para o grid de diretorias, use:
  // $url_voltar = 'index.php?page=diretorias';
} else {
  // Usuário comum volta para a home dele
  $url_voltar = 'index.php?page=home';
}
?>

<div class="table-container">
  <div class="main-title"><?php echo htmlspecialchars($nome_iniciativa); ?> - Acompanhamento de Pendências</div>

  <form method="post" action="index.php?page=acompanhamento&id_iniciativa=<?php echo $id_iniciativa; ?>">
    <table id="spreadsheet">
      <thead>
        <tr>
          <th>Problema</th>
          <th>Contramedida</th>
          <th>Prazo</th>
          <th>Responsável</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($linha = mysqli_fetch_assoc($dados_pendencias)) { ?>
        <tr data-id="<?php echo $linha['id']; ?>">
          <td contenteditable="true"><?php echo htmlspecialchars($linha['problema']); ?></td>
          <td contenteditable="true"><?php echo htmlspecialchars($linha['contramedida']); ?></td>
          
          <?php
            $data = $linha['prazo'];
            if (!$data || $data === '0000-00-00') {
          ?>
              <td contenteditable="true"></td>
          <?php
            } else {
          ?>
              <td class="readonly">
                <?php echo date('d/m/Y', strtotime($data)); ?>
              </td>
          <?php
            }
          ?>
          
          <td contenteditable="true"><?php echo htmlspecialchars($linha['responsavel']); ?></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>

    <div class="button-group">
      <button type="button" onclick="addRow()">Adicionar Linha</button>
      <button type="button" onclick="deleteRow()">Excluir Linha</button>
      <button type="submit" name="salvar" id="submit">Salvar</button>
      <button type="button" onclick="window.location.href='<?php echo htmlspecialchars($url_voltar, ENT_QUOTES, 'UTF-8'); ?>';">
        &lt; Voltar
      </button>
    </div>
  </form>
</div>

<script src="js/acompanhamento.js"></script>
