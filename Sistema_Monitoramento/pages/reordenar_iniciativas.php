<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
header('Content-Type: application/json; charset=utf-8');

// Não quebrar JSON com notices/warnings
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Ajuste o caminho do config conforme sua árvore:
require_once __DIR__ . '/../templates/config.php';

if (empty($_SESSION['id_usuario'])) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Não autenticado']);
  exit;
}

$uid = (int) $_SESSION['id_usuario'];

// Lê o corpo JSON
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
$itens = $body['itens'] ?? null;

if (!is_array($itens) || empty($itens)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Payload inválido']);
  exit;
}

// Normaliza {id, ordem}
$rows = [];
foreach ($itens as $r) {
  $id    = (int)($r['id']    ?? 0);
  $ordem = (int)($r['ordem'] ?? 0);
  if ($id > 0 && $ordem > 0) {
    $rows[$id] = $ordem;
  }
}
if (!$rows) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Nada para atualizar']);
  exit;
}

$conexao->begin_transaction();
try {
  // atualiza apenas se a iniciativa for do usuário logado (segurança)
  $stmt = $conexao->prepare("UPDATE iniciativas SET ordem = ? WHERE id = ? AND id_usuario = ?");
  foreach ($rows as $id => $ordem) {
    $stmt->bind_param('iii', $ordem, $id, $uid);
    if (!$stmt->execute()) {
      throw new Exception('Falha ao atualizar id '.$id);
    }
  }
  $stmt->close();
  $conexao->commit();

  echo json_encode(['ok' => true]);
} catch (Throwable $e) {
  $conexao->rollback();
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Erro ao salvar ordem']);
}
