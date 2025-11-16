<?php
// templates/remover_compartilhamento.php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['id_usuario'])) { http_response_code(401); exit('Sem sessão'); }
require_once __DIR__ . '/config.php';

$id_dono = (int)$_SESSION['id_usuario'];
$id_comp = (int)($_POST['id_compartilhado'] ?? 0);
if (!$id_comp) { http_response_code(400); exit('Faltou id'); }

$st = $conexao->prepare("DELETE FROM compartilhamentos WHERE id_dono = ? AND id_compartilhado = ?");
$st->bind_param("ii", $id_dono, $id_comp);
$st->execute();
$ok = $st->affected_rows >= 0;
$st->close();

echo $ok ? "OK" : "ERRO";
