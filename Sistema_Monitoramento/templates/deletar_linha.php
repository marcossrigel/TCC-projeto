<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

mysqli_set_charset($conexao, "utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $query = "DELETE FROM projeto_licitacoes WHERE id = $id";
    if (mysqli_query($conexao, $query)) {
        echo 'sucesso';
    } else {
        echo 'erro ao excluir: ' . mysqli_error($conexao);
    }
} else {
    echo 'id inválido';
}
