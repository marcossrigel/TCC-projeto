<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    exit('Não autorizado');
}

include_once('config.php');

$id = intval($_POST['id'] ?? 0);
$id_usuario = $_SESSION['id_usuario'];

if ($id > 0) {
    $sql = "DELETE FROM medicoes WHERE id = $id AND id_usuario = $id_usuario";
    if (mysqli_query($conexao, $sql)) {
        echo 'ok';
    } else {
        http_response_code(500);
        echo 'erro';
    }
} else {
    http_response_code(400);
    echo 'id inválido';
}