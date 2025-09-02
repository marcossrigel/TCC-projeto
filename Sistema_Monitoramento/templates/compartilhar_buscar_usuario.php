<?php
include("config.php");

$termo = $_GET['termo'] ?? '';

if (strlen($termo) < 1) {
    echo json_encode([]);
    exit;
}

$stmt = $conexao->prepare("SELECT usuario_rede FROM usuarios WHERE usuario_rede LIKE CONCAT('%', ?, '%') LIMIT 10");
$stmt->bind_param("s", $termo);
$stmt->execute();
$result = $stmt->get_result();

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row['usuario_rede'];
}

echo json_encode($usuarios);
?>
