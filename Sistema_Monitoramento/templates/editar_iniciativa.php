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


