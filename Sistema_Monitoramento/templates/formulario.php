<?php
// templates/formulario.php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/config.php';

if (empty($_SESSION['id_usuario'])) {
  header('Location: ../login.php');
  exit;
}

function v($key) { return trim($_POST[$key] ?? ''); }

$id_usuario   = (int)($_SESSION['id_usuario'] ?? 0);

$iniciativa    = v('iniciativa');
$data_vistoria = v('data_vistoria');
$ib_status     = v('ib_status');
$ib_execucao   = v('ib_execucao');
$ib_previsto   = v('ib_previsto');
$ib_variacao   = v('ib_variacao');
$valor_medio   = v('ib_valor_medio');
$secretaria    = v('ib_secretaria');
$diretoria     = v('ib_diretoria');
$gestor        = v('ib_gestor_responsavel');
$fiscal        = v('ib_fiscal');
$objeto        = v('objeto');
$info_gerais   = v('informacoes_gerais');
$observacoes   = v('observacoes');

// numero_contrato: usa hidden; se vier vazio, monta com prefixo/ano
$numero_contrato = v('numero_contrato');
if ($numero_contrato === '') {
  $p = preg_replace('/\D/', '', v('numero_contrato_prefixo'));
  $a = preg_replace('/\D/', '', v('numero_contrato_ano'));
  $numero_contrato = ($p && $a) ? "{$p}/{$a}" : '';
}

// validações mínimas
if ($iniciativa === '' || $data_vistoria === '' || $ib_status === '') {
  header('Location: ../index.php?page=home&msg=campos_obrigatorios');
  exit;
}

// (opcional) respeitar limites de coluna na base
$secretaria  = mb_substr($secretaria, 0, 20); // ib_secretaria é varchar(20)
$diretoria   = mb_substr($diretoria,  0, 50); // ib_diretoria é varchar(50)

$sql = "INSERT INTO iniciativas
        (id_usuario, iniciativa, data_vistoria, numero_contrato,
         ib_status, ib_execucao, ib_previsto, ib_variacao, ib_valor_medio,
         ib_secretaria, ib_diretoria, ib_gestor_responsavel, ib_fiscal,
         objeto, informacoes_gerais, observacoes)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $conexao->prepare($sql);
if (!$stmt) {
  header('Location: ../index.php?page=home&msg=erro_prepare');
  exit;
}

$stmt->bind_param(
  "isssssssssssssss",
  $id_usuario, $iniciativa, $data_vistoria, $numero_contrato,
  $ib_status, $ib_execucao, $ib_previsto, $ib_variacao, $valor_medio,
  $secretaria, $diretoria, $gestor, $fiscal,
  $objeto, $info_gerais, $observacoes
);

if ($stmt->execute()) {
  header('Location: ../index.php?page=home&msg=ok');
  exit;
} else {
  header('Location: ../index.php?page=home&msg=erro_execucao');
  exit;
}
