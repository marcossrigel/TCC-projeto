<?php
require_once __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario   = (int) $_SESSION['id_usuario'];
$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'comum';

if ($tipo_usuario === 'admin' && isset($_GET['diretoria'])) {
    $diretoria = $conexao->real_escape_string($_GET['diretoria']);
    $sql = "SELECT * FROM iniciativas 
            WHERE ib_diretoria = '$diretoria'
            ORDER BY id ASC";
} else {
    $sql = "SELECT * FROM iniciativas i
            WHERE i.id_usuario = $id_usuario
               OR EXISTS (
                    SELECT 1 FROM compartilhamentos c
                    WHERE c.id_iniciativa = i.id
                      AND c.id_compartilhado = $id_usuario
               )
            ORDER BY i.id ASC";
}

$resultado = $conexao->query($sql);
?>

<div class="container">

<div class="top-bar">
  <div class="top-bar-lado">
    <a href="index.php?page=home" class="botao-topo">&lt; Voltar</a>
  </div>

  <h1 class="titulo-topo">
    <?php 
      if ($tipo_usuario === 'admin' && isset($_GET['diretoria'])) {
        echo "Iniciativas da Diretoria: " . htmlspecialchars($_GET['diretoria']);
      } else {
        echo "Iniciativas Cadastradas";
      }
    ?>
  </h1>

  <div class="top-bar-lado">
    <?php if ($tipo_usuario === 'comum'): ?>
      <a href="index.php?page=compartilhar&id=<?php echo $id_usuario; ?>" class="botao-topo">
        👥 Compartilhar
      </a>
    <?php endif; ?>
  </div>
</div>

  <div id="sortable">
    
  <?php while ($row = $resultado->fetch_assoc()): ?>
    <?php $isConcluida = !empty($row['concluida']); ?>
    <?php $classe_concluido = $isConcluida ? 'concluido' : ''; ?>

  <div class="item">
    <button class="accordion <?= $classe_concluido ?>" data-id="<?= $row['id']; ?>">
      <strong><?= htmlspecialchars($row['iniciativa']) ?></strong>
      <span class="seta">⌄</span>
    </button>

    <div class="panel" id="panel-<?= $row['id']; ?>">
      <p><strong>Status:</strong> <?= htmlspecialchars($row['ib_status']) ?> |
         <strong>Data da Vistoria:</strong> <?= htmlspecialchars($row['data_vistoria']) ?> |
         <strong>Nº do Contrato:</strong> <?= htmlspecialchars($row['numero_contrato']) ?></p>

      <p><strong>Execução:</strong> <?= htmlspecialchars($row['ib_execucao']) ?> |
         <strong>Previsto:</strong> <?= htmlspecialchars($row['ib_previsto']) ?> |
         <strong>Variação:</strong> <?= htmlspecialchars($row['ib_variacao']) ?> |
         <strong>Valor Medido Acumulado:</strong> <?= htmlspecialchars($row['ib_valor_medio']) ?></p>

      <p><strong>Secretaria:</strong> <?= htmlspecialchars($row['ib_secretaria']) ?> |
         <strong>Diretoria:</strong> <?= htmlspecialchars($row['ib_diretoria']) ?> |
         <strong>Órgão:</strong> <?= htmlspecialchars($row['ib_orgao']) ?> |
         <strong>Processo SEI:</strong> <?= htmlspecialchars($row['ib_numero_processo_sei']) ?></p>

      <p><strong>Gestor Responsável:</strong> <?= htmlspecialchars($row['ib_gestor_responsavel']) ?> |
         <strong>Fiscal Responsável:</strong> <?= htmlspecialchars($row['ib_fiscal']) ?></p>

      <p><strong>Objeto:</strong> <?= nl2br(htmlspecialchars($row['objeto'])) ?></p>
      <p><strong>Informações Gerais:</strong> <?= nl2br(htmlspecialchars($row['informacoes_gerais'])) ?></p>
      <p><strong>Observações:</strong> <?= nl2br(htmlspecialchars($row['observacoes'])) ?></p>

      <div class="button-left">
        <button onclick="window.location.href='index.php?page=editar_iniciativa&id=<?= $row['id']; ?>';">Status andamento</button>
      </div>

      <div class="acoes">
        <button onclick="window.location.href='index.php?page=acompanhamento&id_iniciativa=<?= $row['id']; ?>';">🛠 Acompanhar Pendências</button>
        <button onclick="window.location.href='index.php?page=projeto_licitacoes&id_iniciativa=<?= $row['id']; ?>';">📋 Projeto e Licitação</button>
        <button onclick="window.location.href='index.php?page=info_contratuais&id_iniciativa=<?= $row['id']; ?>';">📄 Informações Contratuais</button>
        <button onclick="window.location.href='index.php?page=medicoes&id_iniciativa=<?= $row['id']; ?>';">📊 Acompanhamento de Medições</button>
        <button onclick="window.location.href='index.php?page=cronogramamarcos&id_iniciativa=<?= $row['id']; ?>';">📆 Cronograma</button>
        <button onclick="marcarComoConcluida(this)"
                style="<?= $isConcluida ? 'background-color:#28a745;' : '' ?>">
          <?= $isConcluida ? '✅ Concluído' : '✔️ Concluída' ?>
        </button>
      </div>
    </div>
  </div>
<?php endwhile; ?>

  </div>

  <div class="botao-voltar">
    <button onclick="window.location.href='<?php echo $tipo_usuario === "admin" ? "index.php?page=diretorias" : "index.php?page=home"; ?>';">&lt; Voltar</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const abertaId = localStorage.getItem('iniciativaAberta');
  if (abertaId) {
    const btn = document.querySelector(`.accordion[data-id='${abertaId}']`);
    const panel = document.getElementById(`panel-${abertaId}`);
    if (btn && panel) {
      btn.classList.add('active');
      panel.style.display = 'block';
    }
    localStorage.removeItem('iniciativaAberta');
  }

  const botoes = document.querySelectorAll('.acoes button');
  botoes.forEach(botao => {
    botao.addEventListener('click', function() {
      const id = this.closest('.item').querySelector('.accordion').dataset.id;
      localStorage.setItem('iniciativaAberta', id);
    });
  });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="js/visualizar.js"></script>

