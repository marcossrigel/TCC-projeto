<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once("config.php");

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id_usuario'] ?? 0;
    $id_iniciativa = intval($_POST['id_iniciativa'] ?? 0);
    $problema = mysqli_real_escape_string($conexao, $_POST['problema'] ?? '');
    $contramedida = mysqli_real_escape_string($conexao, $_POST['contramedida'] ?? '');
    $prazo = trim($_POST['prazo'] ?? '');
    $responsavel = mysqli_real_escape_string($conexao, $_POST['responsavel'] ?? '');

    $prazo_sql = $prazo !== '' ? "'" . mysqli_real_escape_string($conexao, $prazo) . "'" : "NULL";

    if ($problema || $contramedida || $prazo || $responsavel) {
        $query = "INSERT INTO pendencias (id_usuario, id_iniciativa, problema, contramedida, prazo, responsavel) 
                  VALUES ('$id_usuario', '$id_iniciativa', '$problema', '$contramedida', $prazo_sql, '$responsavel')";

        if (mysqli_query($conexao, $query)) {
            echo json_encode(['status' => 'ok', 'id' => mysqli_insert_id($conexao)]);
        } else {
            echo json_encode(['status' => 'erro', 'mensagem' => mysqli_error($conexao)]);
        }
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Campos vazios']);
    }
}
