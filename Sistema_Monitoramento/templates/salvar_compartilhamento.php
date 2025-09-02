<?php
session_start();
require_once 'config.php';

$id_dono    = (int)$_SESSION['id_usuario'];
$usuarioAlvo= trim($_POST['usuario'] ?? '');
$inics      = $_POST['iniciativas'] ?? [];

if (!$id_dono || !$usuarioAlvo || empty($inics)) { exit('Dados inválidos'); }

// Dono precisa ser 'comum'
$tipoDono = $conexao->query("SELECT tipo FROM usuarios WHERE id_usuario = $id_dono")->fetch_column();
if ($tipoDono !== 'comum') { exit('Compartilhamento permitido apenas para usuários comuns.'); }

// Achar destino por nome OU login e garantir que é 'comum'
$stmt = $conexao->prepare("SELECT id_usuario, tipo FROM usuarios WHERE (nome = ? OR usuario = ?) LIMIT 1");
$stmt->bind_param("ss", $usuarioAlvo, $usuarioAlvo);
$stmt->execute();
$stmt->bind_result($id_comp, $tipo_comp);
if (!$stmt->fetch()) { exit('Usuário de destino não encontrado.'); }
$stmt->close();

if ($tipo_comp !== 'comum') { exit('Só é permitido compartilhar com usuários do tipo comum.'); }

// Inserir um a um, garantindo que a iniciativa é do dono
$checkIni = $conexao->prepare("SELECT 1 FROM iniciativas WHERE id = ? AND id_usuario = ?");
$ins      = $conexao->prepare("INSERT IGNORE INTO compartilhamentos (id_iniciativa, id_compartilhado) VALUES (?, ?)");

foreach ($inics as $id_ini) {
  $id_ini = (int)$id_ini;

  $checkIni->bind_param("ii", $id_ini, $id_dono);
  $checkIni->execute();
  if ($checkIni->get_result()->fetch_row()) {
    $ins->bind_param("ii", $id_ini, $id_comp);
    $ins->execute();
  }
}

$checkIni->close();
$ins->close();

header("Location: index.php?page=compartilhar");
exit;
