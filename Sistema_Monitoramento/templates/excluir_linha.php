<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    exit('Acesso negado.');
}

include_once('config.php');

$id_usuario = (int) $_SESSION['id_usuario'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    $query = "DELETE FROM medicoes WHERE id = $id AND id_usuario = $id_usuario";
    if (mysqli_query($conexao, $query)) {
        echo "OK";
    } else {
        http_response_code(500);
        echo "Erro ao excluir: " . mysqli_error($conexao);
    }
} else {
    http_response_code(400);
    echo "ID inválido.";
}

// mysqli_close($conexao); // opcional
?>
