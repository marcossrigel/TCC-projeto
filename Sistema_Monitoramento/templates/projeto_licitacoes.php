<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once('config.php');
mysqli_set_charset($conexao, "utf8mb4");

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php'); exit;
}

$id_usuario_logado = (int)($_SESSION['id_usuario'] ?? 0);
$id_iniciativa     = isset($_GET['id_iniciativa']) ? (int)$_GET['id_iniciativa'] : 0;

$stmt = $conexao->prepare("SELECT id_usuario AS id_dono, iniciativa FROM iniciativas WHERE id = ?");
$stmt->bind_param("i", $id_iniciativa);
$stmt->execute();
$res = $stmt->get_result();
$ini = $res->fetch_assoc();
if (!$ini) { die("Iniciativa não encontrada."); }
$id_dono = (int)$ini['id_dono'];
$nome_iniciativa = $ini['iniciativa'] ?? 'Iniciativa Desconhecida';

$temAcesso = ($id_usuario_logado === $id_dono);
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
if (!$temAcesso) { die("Sem permissão para acessar esta iniciativa."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['etapa'])) {
    $ids              = $_POST['ids'] ?? [];
    $ordens           = $_POST['ordem'] ?? [];
    $etapas           = $_POST['etapa'] ?? [];
    $responsaveis     = $_POST['responsavel'] ?? [];
    $inicio_previsto  = $_POST['inicio_previsto'] ?? [];
    $termino_previsto = $_POST['termino_previsto'] ?? [];
    $inicio_real      = $_POST['inicio_real'] ?? [];
    $termino_real     = $_POST['termino_real'] ?? [];
    $status           = $_POST['status'] ?? [];
    $observacoes      = $_POST['observacao'] ?? [];

    for ($i = 0; $i < count($etapas); $i++) {
        $id = isset($ids[$i]) ? (int)$ids[$i] : 0;

        $ordem        = ($ordens[$i]        ?? '') !== '' ? "'".mysqli_real_escape_string($conexao,$ordens[$i])."'"        : "NULL";
        $etapa        = ($etapas[$i]        ?? '') !== '' ? "'".mysqli_real_escape_string($conexao,$etapas[$i])."'"        : "NULL";
        $responsavel  = ($responsaveis[$i]  ?? '') !== '' ? "'".mysqli_real_escape_string($conexao,$responsaveis[$i])."'"  : "NULL";
        $status_val   = ($status[$i]        ?? '') !== '' ? "'".mysqli_real_escape_string($conexao,$status[$i])."'"        : "NULL";
        $obs          = ($observacoes[$i]   ?? '') !== '' ? "'".mysqli_real_escape_string($conexao,$observacoes[$i])."'"   : "NULL";

        $prev_inicio  = ($inicio_previsto[$i]  ?? '') !== '' ? "'".mysqli_real_escape_string($conexao,$inicio_previsto[$i])."'"  : "NULL";
        $prev_fim     = ($termino_previsto[$i] ?? '') !== '' ? "'".mysqli_real_escape_string($conexao,$termino_previsto[$i])."'" : "NULL";
        $real_inicio  = ($inicio_real[$i]      ?? '') !== '' ? "'".mysqli_real_escape_string($conexao,$inicio_real[$i])."'"      : "NULL";
        $real_fim     = ($termino_real[$i]     ?? '') !== '' ? "'".mysqli_real_escape_string($conexao,$termino_real[$i])."'"     : "NULL";

        if ($id > 0) {
            $sql = "UPDATE projeto_licitacoes SET 
                ordem = $ordem,
                etapa = $etapa,
                responsavel = $responsavel,
                inicio_previsto = $prev_inicio,
                termino_previsto = $prev_fim,
                inicio_real = $real_inicio,
                termino_real = $real_fim,
                status = $status_val,
                observacao = $obs
            WHERE id = $id AND id_iniciativa = $id_iniciativa";
        } else {
            $sql = "INSERT INTO projeto_licitacoes (
                id_iniciativa, ordem, etapa, responsavel,
                inicio_previsto, termino_previsto,
                inicio_real, termino_real, status, observacao
            ) VALUES (
                $id_iniciativa, $ordem, $etapa, $responsavel,
                $prev_inicio, $prev_fim,
                $real_inicio, $real_fim, $status_val, $obs
            )";
        }

        mysqli_query($conexao, $sql);
    }

    header("Location: index.php?page=projeto_licitacoes&id_iniciativa=$id_iniciativa");
    exit;
}

$sql = "
  SELECT * FROM projeto_licitacoes
  WHERE id_iniciativa = $id_iniciativa
  ORDER BY 
    CASE WHEN ordem REGEXP '^[0-9]+$' THEN CAST(ordem AS UNSIGNED) ELSE 999999 END,
    ordem, id
";
$dados = mysqli_query($conexao, $sql);

$nome_iniciativa = htmlspecialchars($nome_iniciativa);
?>

<div class="container">
    <h2>Projeto - <?php echo htmlspecialchars($nome_iniciativa); ?></h2>

    <form method="post" action="index.php?page=projeto_licitacoes&id_iniciativa=<?php echo $id_iniciativa; ?>">
        <div class="table-wrapper">
        
        <table id="medicoes">
        <colgroup>
            <col class="col-ordem">
            <col class="col-texto-largo">      
            <col class="col-texto-largo">      
            <col class="col-data">             
            <col class="col-data">             
            <col class="col-data">             
            <col class="col-data">             
            <col class="col-status">          
            <col class="col-observacao">       
            <col class="col-acoes">            
        </colgroup>

        <thead>
        <tr>
            <th>Ordem</th>
            <th>Etapa</th>
            <th>Responsável</th>
            <th>Início Previsto</th>
            <th>Término Previsto</th>
            <th>Início Real</th>
            <th>Término Real</th>
            <th>Status</th>
            <th>Observação</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
            <?php while ($linha = mysqli_fetch_assoc($dados)) { ?>
            <tr data-id="<?= $linha['id'] ?>">
                <td>
                <input type="hidden" name="ids[]" value="<?= htmlspecialchars($linha['id']) ?>">
                <input class="num" type="text" name="ordem[]" value="<?= htmlspecialchars($linha['ordem'] ?? '') ?>">
                </td>

                <td><input type="text" name="etapa[]"        value="<?= htmlspecialchars($linha['etapa'] ?? '') ?>"></td>
                <td><input type="text" name="responsavel[]"  value="<?= htmlspecialchars($linha['responsavel'] ?? '') ?>"></td>

                <td><input type="date" name="inicio_previsto[]"  value="<?= htmlspecialchars($linha['inicio_previsto'] ?? '') ?>"></td>
                <td><input type="date" name="termino_previsto[]" value="<?= htmlspecialchars($linha['termino_previsto'] ?? '') ?>"></td>
                <td><input type="date" name="inicio_real[]"      value="<?= htmlspecialchars($linha['inicio_real'] ?? '') ?>"></td>
                <td><input type="date" name="termino_real[]"     value="<?= htmlspecialchars($linha['termino_real'] ?? '') ?>"></td>

                <td><input type="text" name="status[]" value="<?= htmlspecialchars($linha['status'] ?? '') ?>"></td>

                <td>
                <textarea name="observacao[]" class="obs" rows="1" wrap="soft"><?= htmlspecialchars($linha['observacao'] ?? '') ?></textarea>
                </td>

                <td class="celula-acoes">
                <button type="button" class="botao-acao botao-mais"  onclick="inserirAntes(this)">➕</button>
                <button type="button" class="botao-acao botao-menos" onclick="deletarLinha(<?= (int)$linha['id'] ?>)">❌</button>
                </td>
            </tr>
            <?php } ?>
        </tbody>
        </table>


        <div class="buttons">
            <button type="button" onclick="adicionarLinha()">Adicionar Linha</button>
            <button type="submit" name="salvar">Salvar</button>
            <button type="button" onclick="window.location.href='index.php?page=visualizar';">&lt; Voltar</button>
        </div>
    </form>
</div>
</div>
<script src="js/projeto_licitacoes.js"></script>
