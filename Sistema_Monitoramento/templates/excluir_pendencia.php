<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  http_response_code(403);
  echo "Acesso negado";
  exit;
}

include_once('config.php');

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $id_usuario = $_SESSION['id_usuario'];
  $query = "DELETE FROM pendencias WHERE id = $id AND id_usuario = $id_usuario";

  if (mysqli_query($conexao, $query)) {
    echo "Excluído com sucesso";
  } else {
    http_response_code(500);
    echo "Erro ao excluir";
  }
} else {
  http_response_code(400);
  echo "ID não informado";
}
?>
