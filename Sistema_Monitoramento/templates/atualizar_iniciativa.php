<?php
// templates/atualizar_iniciativa.php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

if (empty($_SESSION['id_usuario'])) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Não autenticado']);
  exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || empty($body['id_iniciativa'])) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Payload inválido']);
  exit;
}

$idUser = (int)$_SESSION['id_usuario'];
$id     = (int)$body['id_iniciativa'];

// Permissão: dono OU compartilhada com ele (para editar, normalmente só dono. Ajuste se quiser)
$checkSql = "
  SELECT 1
  FROM iniciativas i
  WHERE i.id = ?
    AND i.id_usuario = ?
  LIMIT 1";
$chk = $conexao->prepare($checkSql);
$chk->bind_param('ii', $id, $idUser);
$chk->execute();
$canEdit = $chk->get_result()->num_rows > 0;
$chk->close();

if (!$canEdit) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'Sem permissão para editar esta iniciativa']);
  exit;
}

// Campos permitidos
$allowed = [
  'data_vistoria','numero_contrato','ib_status','ib_execucao','ib_previsto','ib_variacao',
  'ib_valor_medio','ib_secretaria','ib_diretoria','ib_gestor_responsavel','ib_fiscal',
  'objeto','informacoes_gerais','observacoes'
];

$sets = [];
$params = [];
$types  = '';

foreach ($allowed as $k) {
  if (array_key_exists($k, $body)) {
    $sets[] = "$k = ?";
    $params[] = $body[$k] === '' ? null : $body[$k];
    // tipos: datas e textos = s. Se quiser tratar numéricos, mude aqui.
    $types .= 's';
  }
}

if (!$sets) {
  echo json_encode(['ok'=>true]); // nada para atualizar
  exit;
}

$sql = "UPDATE iniciativas SET ".implode(', ', $sets)." WHERE id = ?";
$types .= 'i';
$params[] = $id;

$stmt = $conexao->prepare($sql);
$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Erro ao salvar']);
  exit;
}

echo json_encode(['ok'=>true]);
