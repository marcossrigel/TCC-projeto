<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

include_once('config.php');
mysqli_set_charset($conexao, "utf8mb4");

$tipo_usuario      = $_SESSION['tipo_usuario'] ?? 'usuario';
$id_iniciativa     = (int)($_GET['id_iniciativa'] ?? 0);
$id_usuario_logado = (int)($_SESSION['id_usuario'] ?? 0);
$stmt = $conexao->prepare("
  SELECT id_usuario AS id_dono, iniciativa, ib_diretoria
  FROM iniciativas
  WHERE id = ?
");
$stmt->bind_param("i", $id_iniciativa);
$stmt->execute();
$resIni = $stmt->get_result();
$rowIni = $resIni->fetch_assoc();
if (!$rowIni) { die("Iniciativa não encontrada."); }

$diretoria      = trim($rowIni['ib_diretoria'] ?? '');
$id_dono        = (int)$rowIni['id_dono'];
$nome_iniciativa= $rowIni['iniciativa'] ?? 'Iniciativa Desconhecida';


$temAcesso = ($tipo_usuario === 'admin') || ($id_usuario_logado === $id_dono);

if (!$temAcesso) {
    $stmt = $conexao->prepare("
        SELECT 1 FROM compartilhamentos 
        WHERE id_dono = ? AND id_compartilhado = ? AND id_iniciativa = ? LIMIT 1
    ");
    $stmt->bind_param("iii", $id_dono, $id_usuario_logado, $id_iniciativa);
    $stmt->execute();
    $temAcesso = (bool)$stmt->get_result()->fetch_row();
}
if (!$temAcesso) { die("Sem permissão para acessar esta iniciativa."); }

if (isset($_POST['etapa'])) {
    $id_etapa_custom  = $_POST['id_etapa_custom'] ?? [];
    $etapa            = $_POST['etapa'] ?? [];
    $inicio_previsto  = $_POST['inicio_previsto'] ?? [];
    $termino_previsto = $_POST['termino_previsto'] ?? [];
    $inicio_real      = $_POST['inicio_real'] ?? [];
    $termino_real     = $_POST['termino_real'] ?? [];
    $evolutivo        = $_POST['evolutivo'] ?? [];
    $ids              = $_POST['ids'] ?? [];
    $tipo_etapa       = $_POST['tipo_etapa'] ?? [];

    if (!count($etapa)) {
        echo "<p style='color:red;text-align:center'>Nenhuma linha foi enviada.</p>";
        exit;
    }

    for ($i = 0; $i < count($etapa); $i++) {
        $etapa_custom = mysqli_real_escape_string($conexao, $id_etapa_custom[$i] ?? '');
        $id_existente = (int)($ids[$i] ?? 0);
        $etp          = mysqli_real_escape_string($conexao, $etapa[$i]);

        $ini_prev = trim($inicio_previsto[$i])  !== '' ? "'".mysqli_real_escape_string($conexao,$inicio_previsto[$i])."'"  : "NULL";
        $ter_prev = trim($termino_previsto[$i]) !== '' ? "'".mysqli_real_escape_string($conexao,$termino_previsto[$i])."'" : "NULL";
        $ini_realv= trim($inicio_real[$i])      !== '' ? "'".mysqli_real_escape_string($conexao,$inicio_real[$i])."'"      : "NULL";
        $ter_realv= trim($termino_real[$i])     !== '' ? "'".mysqli_real_escape_string($conexao,$termino_real[$i])."'"     : "NULL";

        $evo_raw = trim($evolutivo[$i]);
        $evo     = $evo_raw !== '' ? "'".mysqli_real_escape_string($conexao,$evo_raw)."'" : "NULL";

        $tipo = mysqli_real_escape_string($conexao, $tipo_etapa[$i] ?? 'linha');

        if ($id_existente > 0) {
            $query = "UPDATE marcos SET 
              tipo_etapa='$tipo',
              etapa='$etp',
              id_etapa_custom='$etapa_custom',
              inicio_previsto=$ini_prev,
              termino_previsto=$ter_prev,
              inicio_real=$ini_realv,
              termino_real=$ter_realv,
              evolutivo=$evo
            WHERE id = $id_existente
              AND id_usuario = $id_dono
              AND id_iniciativa = $id_iniciativa";
        } else {
            $query = "INSERT INTO marcos (
                id_usuario, id_iniciativa, id_etapa_custom, tipo_etapa, etapa,
                inicio_previsto, termino_previsto, inicio_real, termino_real, evolutivo
              ) VALUES (
                '$id_dono', '$id_iniciativa', '$etapa_custom', '$tipo', '$etp',
                $ini_prev, $ter_prev, $ini_realv, $ter_realv, $evo
              )";
        }

        if (!mysqli_query($conexao, $query)) {
            echo "Erro: " . mysqli_error($conexao);
            exit;
        }
    }
}

$query_dados = "
  SELECT * FROM marcos
  WHERE id_usuario = $id_dono AND id_iniciativa = $id_iniciativa
  ORDER BY
    CAST(SUBSTRING_INDEX(id_etapa_custom, '.', 1) AS UNSIGNED),
    CASE
      WHEN id_etapa_custom LIKE '%.%' THEN CAST(SUBSTRING_INDEX(id_etapa_custom, '.', -1) AS UNSIGNED)
      ELSE 0
    END
";
$dados = mysqli_query($conexao, $query_dados);

if ($tipo_usuario === 'admin') {
  $url_voltar = $diretoria
    ? 'index.php?page=visualizar&diretoria=' . rawurlencode($diretoria)
    : 'index.php?page=diretorias';
} else {
  $url_voltar = 'index.php?page=home';
}

function formatarParaBrasileiro($valor) {
    return number_format((float)$valor, 2, ',', '.');
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root {
    --color-dark: #1d2129;
}
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
html, body {
    font-family: 'Poppins', sans-serif;
    background: #e3e8ec;
    min-height: 100vh;
}
.botoes-acoes {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 6px; /* espaçamento entre os botões */
}
.table-container {
    width: 95%;
    margin: 40px auto;
    background: #fff;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    overflow-x: auto;
}
.campo-etapa-subtitulo {
    font-weight: bold;
    width: 100%;
    min-width: 200px;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    padding: 4px 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-sizing: border-box;
    text-align: center;
}
th:nth-child(8), td:nth-child(8) {
    width: 70px;
    text-align: center;
}
textarea {
  overflow: hidden;
  min-height: 40px;
  max-height: 400px;
  resize: none;
}
table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 4px 15px; /* antes era 8px */
    table-layout: fixed;          
}
th, td {
    text-align: left;
    padding: 10px;
}
td[contenteditable] {
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 8px;
    min-width: 120px;
}
td[contenteditable]:focus {
    outline: none;
    border: 1px solid #4da6ff;
    background-color: #f0f8ff;
}
input[type="text"],
input[type="date"] {
    height: 20px;
    padding: 4px 8px;
    font-size: 13px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    width: 100%;
    box-sizing: border-box;
}
.main-title {
    font-size: 26px;
    color: var(--color-dark);
    text-align: center;
    margin-bottom: 20px;
}
.button-group {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
}
.button-group button {
    padding: 10px 20px;
    background-color: #4da6ff;
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
}
.button-group button:hover {
    background-color: #3399ff;
}
textarea {
    resize: vertical; 
}
.btn-acao {
  background-color: white;
  border: 2px solid #ccc;
  border-radius: 8px;
  padding: 6px 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
  margin: 0 2px;
}

.btn-acao i {
  pointer-events: none;
}

.acao-mais i {
  color: #6f42c1; /* Roxo */
}

.acao-menos i {
  color: #e74c3c; /* Vermelho */
}

.btn-acao:hover {
  background-color: #f5f5f5;
  border-color: #999;
  transform: scale(1.1);
}

@media (max-width: 768px) {
    .main-title {
        font-size: 20px;
        padding: 0 10px;
    }
    table {
        font-size: 13px;
        display: block;
        overflow-x: auto;
    }
    td[contenteditable], input[type="text"], input[type="date"], input[type="number"], textarea {
        min-width: 90px;
        font-size: 13px;
    }
    .button-group {
        flex-direction: column;
        align-items: center;
    }
    .button-group button {
        width: 100%;
        max-width: 250px;
    }
}
</style>

<div class="table-container">
  <div class="main-title"><?php echo htmlspecialchars($nome_iniciativa); ?> - Cronograma de Marcos</div>
  <form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8'); ?>">

    <table id="spreadsheet">
      <thead>
        <tr>
          <th style="width: 65px;">ID</th>
          <th>Etapa</th>
          <th>Início Previsto</th>
          <th>Término Previsto</th>
          <th>Início Real</th>
          <th>Término Real</th>
          <th>% Evolutivo</th>
          <th>Ações</th>
        </tr>
      </thead>

      <tbody>
        <?php while ($linha = mysqli_fetch_assoc($dados)) { ?>
          <tr data-id="<?= $linha['id'] ?>">
          
          <td style="max-width:50px;">
            <input type="text" name="id_etapa_custom[]" value="<?php echo htmlspecialchars($linha['id_etapa_custom']); ?>" 
              style="width: 60px; font-size: 13px; padding: 4px 6px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; text-align: center;">
          </td>

          
          <td>
              <?php if ($linha['tipo_etapa'] === 'subtitulo') { ?>
                <input type="text" name="etapa[]" value="<?php echo htmlspecialchars($linha['etapa']); ?>" 
                  class="campo-etapa-subtitulo" style="font-weight: bold; text-align: center;">
                <?php } else { ?>

                <textarea name="etapa[]" rows="2" class="campo-etapa" 
                  style="width:100%; font-family:'Poppins', sans-serif; font-size:13px; padding:4px 8px; border:1px solid #ccc; border-radius:6px; box-sizing:border-box;"><?php echo htmlspecialchars($linha['etapa']); ?></textarea>
              <?php } ?>
              <input type="hidden" name="ids[]" value="<?php echo $linha['id']; ?>">
              <input type="hidden" name="tipo_etapa[]" value="<?php echo htmlspecialchars($linha['tipo_etapa']); ?>">
            </td>

            <td><input type="date" name="inicio_previsto[]" value="<?php echo $linha['inicio_previsto']; ?>"></td>
            <td><input type="date" name="termino_previsto[]" value="<?php echo $linha['termino_previsto']; ?>"></td>
            <td><input type="date" name="inicio_real[]" value="<?php echo $linha['inicio_real']; ?>"></td>
            <td><input type="date" name="termino_real[]" value="<?php echo $linha['termino_real']; ?>"></td>
            <td><input type="number" name="evolutivo[]" value="<?php echo $linha['evolutivo']; ?>" min="0" max="100" step="0.1" placeholder="0 a 100%"></td>
            <td>
            <div class="botoes-acoes">
              <button type="button" class="acao-mais btn-acao" title="Adicionar Subetapa">
                <i class="fas fa-plus"></i>
              </button>
              <button type="button" class="acao-menos btn-acao" title="Remover Subetapa">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </td>
          </tr>
        <?php } ?>
      </tbody>

    </table>
    <div class="button-group">
      <button type="button" onclick="addTitleRow()">Adicionar Etapa</button>
      <button type="button" onclick="addRow()">Adicionar Sub-Etapa</button>
      <button type="button" onclick="deleteRow()">Excluir Linha</button>
      <button type="submit" name="salvar" id="submit" style="background-color:rgb(42, 179, 0);">Salvar</button>
      
      <button type="button"
        onclick="window.location.href='<?php echo htmlspecialchars($url_voltar, ENT_QUOTES, 'UTF-8'); ?>';">
        &lt; Voltar
      </button>
    </div>
    </div>

  </form>
</div>

<script>

let ultimoIdEtapa = 0;
let subEtapasPorEtapa = {};

document.querySelector('form').addEventListener('submit', function(event) {
  const form = this;
  const table = document.getElementById('spreadsheet').getElementsByTagName('tbody')[0];
  const linhas = table.rows;
  let temLinhaValida = false;

  let tituloIndex = -1;
  let datasInicio = [];
  let datasTermino = [];


  for (let i = 0; i < linhas.length; i++) {
    const linha = linhas[i];
    const id = linha.getAttribute('data-id');
    const cells = linha.cells;

    const etapaField = cells[1].querySelector('textarea, input');
    
    let tipo = 'linha';
    if (etapaField?.placeholder === 'Título') {
      tipo = 'subtitulo';
    } else {
      const idValor = linha.querySelector('input[name="id_etapa_custom[]"]')?.value.trim();
      if (idValor && !idValor.includes('.')) {
        tipo = 'subtitulo'; // número inteiro sem ponto: ex 6, 7, 8 → é título
      }
    }

    const dtInicioPrev  = cells[2].querySelector('input')?.value.trim() || '';
    const dtTermPrev    = cells[3].querySelector('input')?.value.trim() || '';
    const dtInicioReal  = cells[4].querySelector('input')?.value.trim() || '';
    const dtTermReal    = cells[5].querySelector('input')?.value.trim() || '';

    const campos = [ (etapaField?.value.trim() || ''), dtInicioPrev, dtTermPrev, dtInicioReal, dtTermReal ];

    const linhaEstaVazia = campos.every(c => c === '');
    if (linhaEstaVazia) continue;

    temLinhaValida = true;

    if (tipo === 'subtitulo') {
      if (tituloIndex !== -1 && datasInicio.length > 0 && datasTermino.length > 0) {
        preencherDatas(linhas[tituloIndex], datasInicio, datasTermino);
      }
      tituloIndex = i;
      datasInicio = [];
      datasTermino = [];
    } else if (tipo === 'linha') {
      if (dtInicioPrev) datasInicio.push(dtInicioPrev);
      if (dtTermPrev)   datasTermino.push(dtTermPrev);
    }
  }

  if (tituloIndex !== -1 && datasInicio.length > 0 && datasTermino.length > 0) {
    preencherDatas(linhas[tituloIndex], datasInicio, datasTermino);
  }

  function preencherDatas(tituloRow, inicios, fins) {
    const campoInicio = tituloRow.querySelector('input[name="inicio_previsto[]"]');
    const campoFim = tituloRow.querySelector('input[name="termino_previsto[]"]');

    const menorData = inicios.filter(Boolean).sort((a,b)=>new Date(a)-new Date(b))[0];
    const maiorData = fins.filter(Boolean).sort((a,b)=>new Date(b)-new Date(a))[0];

    if (campoInicio) campoInicio.value = menorData;
    if (campoFim) campoFim.value = maiorData;
  }

  if (!temLinhaValida) {
    event.preventDefault();
    alert('Nenhuma medição válida para salvar!');
  } else {
    const inputs = form.querySelectorAll('textarea, input[type="text"], input[type="number"], input[type="date"]');
    inputs.forEach(input => {
      input.style.backgroundColor = '#e0ffe0';
      setTimeout(() => input.style.backgroundColor = '', 1000);
    });
  }
});

function addTitleRow() {
  const table = document.getElementById('spreadsheet').getElementsByTagName('tbody')[0];
  const newRow = table.insertRow();

  ultimoIdEtapa++;
  subEtapasPorEtapa[ultimoIdEtapa] = 0;

  const id = ultimoIdEtapa;
  const campos = ['etapa', 'inicio_previsto', 'termino_previsto', 'inicio_real', 'termino_real', 'evolutivo'];

  const idCell = newRow.insertCell();
  const idInput = document.createElement('input');
  idInput.type = 'text';
  idInput.name = 'id_etapa_custom[]';
  idInput.readOnly = true;
  idInput.value = id;
  idInput.className = 'input-padrao';
  idCell.appendChild(idInput);

  campos.forEach((campo, index) => {
    const cell = newRow.insertCell();
    const input = document.createElement('input');
    input.name = campo + '[]';
    input.className = 'input-padrao';
    if (index === 0) {
      input.placeholder = 'Título';
      input.type = 'text';
    } else {
      input.type = campo === 'evolutivo' ? 'number' : 'date';
      if (campo === 'evolutivo') {
        input.min = 0;
        input.max = 100;
        input.step = 0.1;
        input.placeholder = '0 a 100%';
      }
    }
    cell.appendChild(input);
  });

  // Corrigido: adiciona hidden input para tipo
  const tipoInput = document.createElement('input');
  tipoInput.type = 'hidden';
  tipoInput.name = 'tipo_etapa[]';
  tipoInput.value = 'subtitulo';
  newRow.appendChild(tipoInput);

  const idHidden = document.createElement('input');
  idHidden.type = 'hidden';
  idHidden.name = 'ids[]';
  idHidden.value = '';
  newRow.appendChild(idHidden);
}

function addRow() {
  if (ultimoIdEtapa === 0) {
    alert('Adicione uma Etapa antes de adicionar Sub-Etapas.');
    return;
  }

  const etapaPai = ultimoIdEtapa;
  subEtapasPorEtapa[etapaPai] = (subEtapasPorEtapa[etapaPai] || 0) + 1;

  const subId = `${etapaPai}.${subEtapasPorEtapa[etapaPai]}`;

  const table = document.getElementById('spreadsheet').getElementsByTagName('tbody')[0];
  const newRow = table.insertRow();

  const campos = ['etapa', 'inicio_previsto', 'termino_previsto', 'inicio_real', 'termino_real', 'evolutivo'];

  const idCell = newRow.insertCell();
  const idInput = document.createElement('input');
  idInput.type = 'text';
  idInput.name = 'id_etapa_custom[]';
  idInput.readOnly = true;
  idInput.value = subId;
  idInput.className = 'input-padrao';
  idCell.appendChild(idInput);

  campos.forEach((campo, index) => {
    const cell = newRow.insertCell();
    if (index === 0) {
      const textarea = document.createElement('textarea');
      textarea.name = campo + '[]';
      textarea.rows = 2;
      textarea.className = 'campo-etapa';
      cell.appendChild(textarea);
    } else {
      const input = document.createElement('input');
      input.name = campo + '[]';
      input.type = campo === 'evolutivo' ? 'number' : 'date';
      if (campo === 'evolutivo') {
        input.min = 0;
        input.max = 100;
        input.step = 0.1;
        input.placeholder = '0 a 100%';
      }
      input.className = 'input-padrao';
      cell.appendChild(input);
    }
  });

  // Corrigido: adiciona hidden input para tipo
  const tipoInput = document.createElement('input');
  tipoInput.type = 'hidden';
  tipoInput.name = 'tipo_etapa[]';
  tipoInput.value = 'linha';
  newRow.appendChild(tipoInput);

  const idHidden = document.createElement('input');
  idHidden.type = 'hidden';
  idHidden.name = 'ids[]';
  idHidden.value = '';
  newRow.appendChild(idHidden);
}

function deleteRow() {
  const table = document.getElementById('spreadsheet').getElementsByTagName('tbody')[0];
  const lastRow = table.rows[table.rows.length - 1];
  if (!lastRow) return;

  const id = lastRow.getAttribute('data-id');

  if (id) {
    fetch(`templates/marcos_excluir_linha.php?id=${id}`, { method: 'GET' })
      .then(response => {
        if (!response.ok) throw new Error("Erro ao excluir do banco");
        return response.text();
      })
      .then(data => {
        console.log(data);
        table.deleteRow(-1);
      })
      .catch(error => {
        alert("Erro ao excluir no servidor.");
        console.error(error);
      });
  } else {
    table.deleteRow(-1);
    recalcularUltimoIdEtapa();
  }
}

function converterParaFloatBrasileiro(valor) {
  return valor.replace(/\./g, '').replace(',', '.');
}

function converterParaDataISO(dataBR) {
  if (!dataBR.includes('/')) return dataBR;
  const partes = dataBR.split('/');
  if (partes.length === 3) {
    return `${partes[2]}-${partes[1]}-${partes[0]}`;
  }
  return dataBR;
}

document.addEventListener('DOMContentLoaded', () => {
  recalcularUltimoIdEtapa();
  copiarInicioPrevistoDasSubetapas();
  ordenarLinhasPorId();
});

function recalcularUltimoIdEtapa() {
  ultimoIdEtapa = 0;
  subEtapasPorEtapa = {};
  const ids = document.querySelectorAll('input[name="id_etapa_custom[]"]');
  ids.forEach(input => {
    const valor = input.value.trim();
    if (valor.includes('.')) {
      const [etapaStr, subStr] = valor.split('.');
      const etapa = parseInt(etapaStr);
      const sub = parseInt(subStr);
      if (!isNaN(etapa) && !isNaN(sub)) {
        ultimoIdEtapa = Math.max(ultimoIdEtapa, etapa);
        subEtapasPorEtapa[etapa] = Math.max(subEtapasPorEtapa[etapa] || 0, sub);
      }
    } else {
      const etapa = parseInt(valor);
      if (!isNaN(etapa)) {
        ultimoIdEtapa = Math.max(ultimoIdEtapa, etapa);
        subEtapasPorEtapa[etapa] = subEtapasPorEtapa[etapa] || 0;
      }
    }
  });
}

function copiarInicioPrevistoDasSubetapas() {
  const linhas = document.querySelectorAll('#spreadsheet tbody tr');
  const mapaSub1 = {};

  linhas.forEach(linha => {
    const idInput = linha.querySelector('input[name="id_etapa_custom[]"]');
    const inicioPrevistoInput = linha.querySelector('input[name="inicio_previsto[]"]');
    const tipo = linha.querySelector('input[name="tipo_etapa[]"]')?.value;

    if (!idInput || !inicioPrevistoInput) return;

    const idValor = idInput.value.trim();

    if (tipo === 'linha' && idValor.endsWith('.1')) {
      const etapaPai = idValor.split('.')[0];
      mapaSub1[etapaPai] = inicioPrevistoInput.value;
    }
  });

  linhas.forEach(linha => {
    const idInput = linha.querySelector('input[name="id_etapa_custom[]"]');
    const inicioPrevistoInput = linha.querySelector('input[name="inicio_previsto[]"]');
    const tipo = linha.querySelector('input[name="tipo_etapa[]"]')?.value;

    if (!idInput || !inicioPrevistoInput) return;

    const idValor = idInput.value.trim();

    if (tipo === 'subtitulo' && mapaSub1[idValor]) {
      inicioPrevistoInput.value = mapaSub1[idValor];
    }
  });
}

function abrirModalInserirLinha() {
  document.getElementById('modalInserirLinha').style.display = 'block';
}

function fecharModalInserirLinha() {
  document.getElementById('modalInserirLinha').style.display = 'none';
  document.getElementById('idParaInserir').value = '';
}

function confirmarInsercaoLinhaEspecifica() {
  const tipo = document.getElementById('tipoLinhaEspecifica').value;
  const idRef = document.getElementById('idReferenciaLinha').value.trim();
  if (!idRef) return alert('Informe um ID válido.');

  if (tipo === 'subtitulo' && idRef.includes('.')) {
    return alert("Etapa deve ser um número inteiro. Ex: 3");
  }

  if (tipo === 'linha' && !idRef.includes('.')) {
    return alert("Sub-Etapa deve estar no formato 2.1, 2.2, etc.");
  }

  const table = document.querySelector('#spreadsheet tbody');
  const linhas = Array.from(table.querySelectorAll('tr'));
  let indexRef = -1;

  // Localizar índice da linha de referência
  for (let i = 0; i < linhas.length; i++) {
    const inputId = linhas[i].querySelector('input[name="id_etapa_custom[]"]');
    if (inputId && inputId.value.trim() === idRef) {
      indexRef = i;
      break;
    }
  }

   if (indexRef === -1) {
    for (let i = 0; i < linhas.length; i++) {
      const inputId = linhas[i].querySelector('input[name="id_etapa_custom[]"]');
      if (!inputId) continue;
      
      const valor = inputId.value.trim();

      if (tipo === 'subtitulo' && !valor.includes('.') && parseInt(valor) > parseInt(idRef)) {
        indexRef = i;
        break;
      }

      if (tipo === 'linha' && valor.includes('.')) {
        const [etapaValor, subValor] = valor.split('.').map(n => parseInt(n));
        const [refEtapa, refSub] = idRef.split('.').map(n => parseInt(n));

        if (etapaValor > refEtapa || (etapaValor === refEtapa && subValor > refSub)) {
          indexRef = i;
          break;
        }
      }
    }

    if (indexRef === -1) {
      indexRef = linhas.length;
    }
  }

  // Gerar novo ID com base no tipo
  const novoId = (() => {
    if (tipo === 'subtitulo') {
      const partes = idRef.split('.');
      const idEtapa = parseInt(partes[0]);
      return String(idEtapa);
    } else {
      const partes = idRef.split('.');
      const idEtapa = parseInt(partes[0]);
      const subEtapas = linhas
        .map(linha => linha.querySelector('input[name="id_etapa_custom[]"]')?.value.trim())
        .filter(id => id?.startsWith(idEtapa + '.') && !isNaN(Number(id.split('.')[1])));

      let maiorSub = 0;
      subEtapas.forEach(id => {
        const sub = parseInt(id.split('.')[1]);
        if (sub > maiorSub) maiorSub = sub;
      });
      return `${idEtapa}.${maiorSub + 1}`;
    }
  })();

  // Inserir nova linha antes da linha de referência
  const novaLinha = linhas[indexRef].cloneNode(true);
  novaLinha.querySelectorAll('input, textarea').forEach(el => {
    if (el.name === 'id_etapa_custom[]') {
      el.value = novoId;
    } else {
      el.value = '';
    }
  });

  table.insertBefore(novaLinha, linhas[indexRef]);

  // Atualizar os IDs das linhas subsequentes
  atualizarIDsHierarquicos(table);
}

function atualizarIDsEtapas(idInserir) {
  const linhas = document.querySelectorAll('#spreadsheet tbody tr');
  const etapaInserida = parseInt(idInserir);
  let comecarIncremento = false;

  for (let i = 0; i < linhas.length; i++) {
    const inputId = linhas[i].querySelector('input[name="id_etapa_custom[]"]');
    if (!inputId) continue;

    const idAtual = inputId.value.trim();

    // Só começa a incrementar DEPOIS de encontrar o idInserir
    if (!comecarIncremento && !idAtual.includes('.') && parseInt(idAtual) === etapaInserida) {
      comecarIncremento = true;
      continue; // NÃO incrementa a linha da etapa que estamos inserindo
    }

    if (comecarIncremento) {
      if (!idAtual.includes('.')) {
        // É uma etapa
        const etapa = parseInt(idAtual);
        if (!isNaN(etapa)) {
          inputId.value = etapa + 1;
        }
      } else {
        // É uma subetapa
        const [etapa, sub] = idAtual.split('.').map(n => parseInt(n));
        if (!isNaN(etapa) && !isNaN(sub) && etapa >= etapaInserida) {
          inputId.value = `${etapa + 1}.${sub}`;
        }
      }
    }
  }
}

function atualizarIDsHierarquicos(tabela) {
  const linhas = Array.from(tabela.querySelectorAll('tr'));
  const ids = linhas.map(linha =>
    linha.querySelector('input[name="id_etapa_custom[]"]')?.value.trim()
  );

  // Ordenar os IDs corretamente antes de processar
  ids.sort((a, b) => {
    const partesA = a.split('.').map(Number);
    const partesB = b.split('.').map(Number);
    for (let i = 0; i < Math.max(partesA.length, partesB.length); i++) {
      const numA = partesA[i] || 0;
      const numB = partesB[i] || 0;
      if (numA !== numB) return numA - numB;
    }
    return 0;
  });

  let etapaAtual = 1;
  let subEtapaAtual = 1;

  for (let i = 0; i < linhas.length; i++) {
    const linha = linhas[i];
    const inputId = linha.querySelector('input[name="id_etapa_custom[]"]');
    const tipo = linha.querySelector('input[name="tipo_etapa[]"]')?.value;

    if (!inputId) continue;

    if (tipo === 'subtitulo') {
      inputId.value = String(etapaAtual);
      subEtapaAtual = 1;
      etapaAtual++;
    } else {
      inputId.value = `${etapaAtual - 1}.${subEtapaAtual}`;
      subEtapaAtual++;
    }
  }
}


function atualizarIDsSubetapas(idInserir) {
  const [etapaPai, subRef] = idInserir.split('.').map(n => parseInt(n));
  const linhas = document.querySelectorAll('#spreadsheet tbody tr');
  for (let i = linhas.length - 1; i >= 0; i--) {
    const inputId = linhas[i].querySelector('input[name="id_etapa_custom[]"]');
    if (!inputId) continue;
    const idAtual = inputId.value.trim();
    if (idAtual.startsWith(`${etapaPai}.`)) {
      const [et, sub] = idAtual.split('.').map(n => parseInt(n));
      if (sub >= subRef) {
        inputId.value = `${et}.${sub + 1}`;
      }
    }
  }
}

function gerarNovoIdEtapa() {
  return ++ultimoIdEtapa;
}

function gerarNovoIdSubEtapa(idPai) {
  const numPai = parseInt(idPai);
  subEtapasPorEtapa[numPai] = (subEtapasPorEtapa[numPai] || 0) + 1;
  return `${numPai}.${subEtapasPorEtapa[numPai]}`;
}

function ordenarLinhasPorId() {
  const tbody = document.querySelector('#spreadsheet tbody');
  const linhas = Array.from(tbody.querySelectorAll('tr'));

  linhas.sort((a, b) => {
    const idA = a.querySelector('input[name="id_etapa_custom[]"]').value;
    const idB = b.querySelector('input[name="id_etapa_custom[]"]').value;

    const partesA = idA.split('.').map(Number);
    const partesB = idB.split('.').map(Number);

    for (let i = 0; i < Math.max(partesA.length, partesB.length); i++) {
      const numA = partesA[i] || 0;
      const numB = partesB[i] || 0;
      if (numA !== numB) return numA - numB;
    }

    return 0;
  });

  linhas.forEach(linha => tbody.appendChild(linha));
}

document.addEventListener('click', function(event) {
  const botaoMais = event.target.closest('.acao-mais');
  if (!botaoMais) return;

  const linhaAtual = botaoMais.closest('tr');
  const tabela = document.querySelector('#spreadsheet tbody');
  const idAtual = linhaAtual.querySelector('input[name="id_etapa_custom[]"]').value.trim();

  if (!idAtual) return;

  const linhas = Array.from(tabela.querySelectorAll('tr'));

  if (!idAtual.includes('.')) {
    // Caso seja uma ETAPA (ex: 1), usar lógica de adicionar última subetapa (como antes)
    const etapaPai = parseInt(idAtual);
    let maiorSub = 0;

    linhas.forEach(linha => {
      const id = linha.querySelector('input[name="id_etapa_custom[]"]').value.trim();
      if (id.startsWith(etapaPai + '.')) {
        const partes = id.split('.');
        const sub = parseInt(partes[1]);
        if (sub > maiorSub) maiorSub = sub;
      }
    });

    const novoId = `${etapaPai}.${maiorSub + 1}`;

    const novaLinha = criarLinhaSubetapa(novoId);
    let indexInsercao = linhas.findIndex(l => 
      l.querySelector('input[name="id_etapa_custom[]"]').value.trim() === idAtual
    ) + 1;

    for (; indexInsercao < linhas.length; indexInsercao++) {
      const id = linhas[indexInsercao].querySelector('input[name="id_etapa_custom[]"]').value.trim();
      if (!id.startsWith(etapaPai + '.')) break;
    }

    tabela.insertBefore(novaLinha, tabela.rows[indexInsercao]);
  } else {
    // Caso seja SUBETAPA (ex: 1.1), vamos inserir entre ela e as próximas
    const [etapaPai, subAtual] = idAtual.split('.').map(n => parseInt(n));
    const novoSub = subAtual + 1;

    // Reordenar subetapas abaixo
    for (let i = linhas.length - 1; i >= 0; i--) {
      const inputId = linhas[i].querySelector('input[name="id_etapa_custom[]"]');
      if (!inputId) continue;
      const id = inputId.value.trim();
      if (id.startsWith(`${etapaPai}.`)) {
        const [et, sub] = id.split('.').map(n => parseInt(n));
        if (sub >= novoSub) {
          inputId.value = `${et}.${sub + 1}`;
        }
      }
    }

    // Inserir nova subetapa entre
    const novoId = `${etapaPai}.${novoSub}`;
    const novaLinha = criarLinhaSubetapa(novoId);

    const indexAtual = linhas.findIndex(l => 
      l.querySelector('input[name="id_etapa_custom[]"]').value.trim() === idAtual
    );

    tabela.insertBefore(novaLinha, tabela.rows[indexAtual + 1]);
  }
});

document.addEventListener('click', function(event) {
  const btnMenos = event.target.closest('.acao-menos');
  if (btnMenos) {
    const linhaAtual = btnMenos.closest('tr');
    const tabela = document.querySelector('#spreadsheet tbody');
    const idInput = linhaAtual.querySelector('input[name="id_etapa_custom[]"]');

    if (!idInput) return;

    const idValor = idInput.value.trim();

    // Só pode remover subetapas (com ponto) e que não terminam com ".1"
    if (!idValor.includes('.') || idValor.endsWith('.1')) {
      alert('Esta subetapa não pode ser removida.');
      return;
    }

    const [etapaPai, subAtual] = idValor.split('.').map(n => parseInt(n));

    // Remove a linha
    tabela.removeChild(linhaAtual);

    // Reordenar as subetapas abaixo
    const linhas = Array.from(tabela.querySelectorAll('tr'));
    for (let i = 0; i < linhas.length; i++) {
      const idInputLinha = linhas[i].querySelector('input[name="id_etapa_custom[]"]');
      if (!idInputLinha) continue;

      const valor = idInputLinha.value.trim();

      if (valor.startsWith(`${etapaPai}.`)) {
        const [et, sub] = valor.split('.').map(n => parseInt(n));
        if (sub > subAtual) {
          idInputLinha.value = `${et}.${sub - 1}`;
        }
      }
    }

    // Atualizar contador local
    if (subEtapasPorEtapa[etapaPai]) {
      subEtapasPorEtapa[etapaPai]--;
    }
  }
});

document.addEventListener("DOMContentLoaded", function() {
  const textareas = document.querySelectorAll("textarea");

  textareas.forEach(textarea => {
    // Ajusta inicialmente com base no conteúdo carregado
    textarea.style.height = "auto";
    textarea.style.height = textarea.scrollHeight + "px";

    // Ajusta dinamicamente quando o usuário digita
    textarea.addEventListener("input", function() {
      this.style.height = "auto";
      this.style.height = this.scrollHeight + "px";
    });
  });
});

function criarLinhaSubetapa(novoId) {
  const novaLinha = document.createElement('tr');
  novaLinha.innerHTML = `
    <td><input type="text" name="id_etapa_custom[]" value="${novoId}" readonly class="input-padrao"></td>
    <td><textarea name="etapa[]" rows="2" class="campo-etapa"></textarea>
        <input type="hidden" name="ids[]" value="">
        <input type="hidden" name="tipo_etapa[]" value="linha">
    </td>
    <td><input type="date" name="inicio_previsto[]" class="input-padrao"></td>
    <td><input type="date" name="termino_previsto[]" class="input-padrao"></td>
    <td><input type="date" name="inicio_real[]" class="input-padrao"></td>
    <td><input type="date" name="termino_real[]" class="input-padrao"></td>
    <td><input type="number" name="evolutivo[]" class="input-padrao" min="0" max="100" step="0.1" placeholder="0 a 100%"></td>
    <td>
      <div class="botoes-acoes">
        <button type="button" class="acao-mais btn-acao" title="Adicionar Subetapa"><i class="fas fa-plus"></i></button>
        <button type="button" class="acao-menos btn-acao" title="Remover Subetapa"><i class="fas fa-minus"></i></button>
      </div>
    </td>
  `;
  return novaLinha;
}

</script>