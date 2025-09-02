<?php
session_start();
include_once("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        echo json_encode(['erro' => 'Formato inválido']);
        exit;
    }

    foreach ($data as $index => $item) {
        $id = intval($item['id']);
        $ordem = intval($index);

        $query = "UPDATE iniciativas SET ordem = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexao, $query);
        mysqli_stmt_bind_param($stmt, "ii", $ordem, $id);
        mysqli_stmt_execute($stmt);
    }

    echo json_encode(['status' => 'sucesso']);
    exit;
}

echo json_encode(['erro' => 'Requisição inválida']);
