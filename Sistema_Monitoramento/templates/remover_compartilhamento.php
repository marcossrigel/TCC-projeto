<?php
include("config.php");
session_start();

if (!isset($_SESSION["id_usuario"])) {
    http_response_code(403);
    echo "Sessão inválida.";
    exit;
}

$id_dono = $_SESSION["id_usuario"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id_compartilhado"])) {
    $id_compartilhado = intval($_POST["id_compartilhado"]);

    $query = "DELETE FROM compartilhamentos WHERE id_dono = ? AND id_compartilhado = ?";
    $stmt = $conexao->prepare($query);

    if (!$stmt) {
        http_response_code(500);
        echo "Erro ao preparar statement: " . $conexao->error;
        exit;
    }

    $stmt->bind_param("ii", $id_dono, $id_compartilhado);

    if ($stmt->execute()) {
        echo "OK";
    } else {
        http_response_code(500);
        echo "Erro ao executar delete: " . $stmt->error;
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo "Parâmetros inválidos.";
}
