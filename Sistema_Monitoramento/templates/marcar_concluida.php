<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  http_response_code(403);
  echo "Acesso negado";
  exit;
}

include_once("config.php");

$id = intval($_POST['id']);
$concluida = intval($_POST['concluida']);

$query = "UPDATE iniciativas SET concluida = $concluida WHERE id = $id";

if (mysqli_query($conexao, $query)) {
  echo "Atualizado com sucesso";
} else {
  http_response_code(500);
  echo "Erro ao atualizar";
}
