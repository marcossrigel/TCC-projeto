<?php
// templates/salvar_compartilhamento.php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['id_usuario'])) { http_response_code(401); exit('Sem sessão'); }
require_once __DIR__ . '/config.php';

$id_dono = (int)$_SESSION['id_usuario'];
$usuario = trim($_POST['usuario'] ?? '');
$inics   = $_POST['iniciativas'] ?? [];

if ($usuario === '' || empty($inics)) { http_response_code(400); exit('Dados insuficientes'); }

// Tenta encontrar o usuário por nome ou usuário_rede dentro do que veio no input
// Aceita formatos "Nome (usuario_rede)" também.
$clean = preg_replace('/\s*\(([^)]+)\)\s*$/', '', $usuario);
$sqlu = "SELECT id_usuario FROM usuarios WHERE nome = ? OR usuario_rede = ? LIMIT 1";
$st = $conexao->prepare($sqlu);
$st->bind_param("ss", $clean, $clean);
$st->execute();
$id_comp = ($st->get_result()->fetch_assoc()['id_usuario'] ?? 0);
$st->close();

if (!$id_comp) { http_response_code(404); exit('Usuário não encontrado'); }

$ins = $conexao->prepare("INSERT INTO compartilhamentos (id_dono, id_compartilhado, id_iniciativa)
                          SELECT ?, ?, ?
                           WHERE NOT EXISTS (
                             SELECT 1 FROM compartilhamentos
                              WHERE id_dono=? AND id_compartilhado=? AND id_iniciativa=?
                           )");
foreach ($inics as $id_inic) {
  $id_inic = (int)$id_inic;
  $ins->bind_param("iiiiii", $id_dono, $id_comp, $id_inic, $id_dono, $id_comp, $id_inic);
  $ins->execute();
}
$ins->close();

echo "OK";
