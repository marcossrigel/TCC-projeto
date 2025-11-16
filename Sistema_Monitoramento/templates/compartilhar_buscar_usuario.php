<<<<<<< HEAD
<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['id_usuario'])) { http_response_code(401); exit; }

require_once __DIR__ . '/config.php';

$termo = trim($_GET['termo'] ?? '');
if (mb_strlen($termo) < 2) { echo json_encode([]); exit; }

$sql = "SELECT nome, usuario_rede
          FROM usuarios
         WHERE nome LIKE CONCAT('%', ?, '%')
            OR usuario_rede LIKE CONCAT('%', ?, '%')
         ORDER BY nome
         LIMIT 20";
$st = $conexao->prepare($sql);
$st->bind_param("ss", $termo, $termo);
$st->execute();
$r = $st->get_result();

$out = [];
while ($row = $r->fetch_assoc()) {
  // Mostra “Nome (usuario_rede)” para evitar ambiguidade
  $display = $row['nome'];
  if (!empty($row['usuario_rede'])) $display .= " ({$row['usuario_rede']})";
  $out[] = $display;
}
$st->close();

header('Content-Type: application/json; charset=utf-8');
echo json_encode($out);
=======
<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['id_usuario'])) { http_response_code(401); exit; }

require_once __DIR__ . '/config.php';

$termo = trim($_GET['termo'] ?? '');
if (mb_strlen($termo) < 2) { echo json_encode([]); exit; }

$sql = "SELECT nome, usuario_rede
          FROM usuarios
         WHERE nome LIKE CONCAT('%', ?, '%')
            OR usuario_rede LIKE CONCAT('%', ?, '%')
         ORDER BY nome
         LIMIT 20";
$st = $conexao->prepare($sql);
$st->bind_param("ss", $termo, $termo);
$st->execute();
$r = $st->get_result();

$out = [];
while ($row = $r->fetch_assoc()) {
  // Mostra “Nome (usuario_rede)” para evitar ambiguidade
  $display = $row['nome'];
  if (!empty($row['usuario_rede'])) $display .= " ({$row['usuario_rede']})";
  $out[] = $display;
}
$st->close();

header('Content-Type: application/json; charset=utf-8');
echo json_encode($out);
>>>>>>> 7a6b3a60ed50304554a32283faa4a38b5b504435
