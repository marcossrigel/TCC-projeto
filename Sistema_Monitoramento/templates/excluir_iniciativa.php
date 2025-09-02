<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

include("config.php");

if (!isset($_GET['id'])) {
    echo "ID não fornecido.";
    exit;
}

$id = intval($_GET['id']);

$check = $conexao->query("SELECT * FROM iniciativas WHERE id = $id") or die("Erro ao buscar iniciativa: " . $conexao->error);
if ($check->num_rows == 0) {
    header("Location: ../index.php?page=visualizar");
    exit;
}

$queries = [
    "DELETE FROM medicoes WHERE id_iniciativa = $id",
    "DELETE FROM pendencias WHERE id_iniciativa = $id",
    "DELETE FROM contratuais WHERE id_iniciativa = $id",
    "DELETE FROM marcos WHERE id_iniciativa = $id"
];

foreach ($queries as $sql) {
    if (!$conexao->query($sql)) {
        die("Erro ao excluir relacionados: " . $conexao->error);
    }
}

if ($conexao->query("DELETE FROM iniciativas WHERE id = $id")) {
    header("Location: ../index.php?page=visualizar");
    exit;
} else {
    die("Erro ao excluir iniciativa: " . $conexao->error);
}
?>
