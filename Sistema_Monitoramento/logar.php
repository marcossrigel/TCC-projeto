<?php
session_start();
require_once('templates/config.php');

// valida se os campos vieram
if (!isset($_POST['usuario'], $_POST['senha'])) {
    die("Requisição inválida.");
}

$login = trim($_POST['usuario']);
$senha = trim($_POST['senha']);

$sql = "SELECT id_usuario, usuario, nome, senha, tipo, setor
        FROM usuarios
        WHERE usuario = ? AND senha = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "ss", $login, $senha);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($result);

if (!$usuario) {
    echo "<script>alert('Usuário ou senha inválidos'); window.location.href = 'login.php';</script>";
    exit;
}

// cria sessão
$_SESSION['id_usuario']   = $usuario['id_usuario'];
$_SESSION['usuario']      = $usuario['usuario'];
$_SESSION['nome']         = $usuario['nome'];
$_SESSION['tipo_usuario'] = $usuario['tipo'];
$_SESSION['setor']        = $usuario['setor']; // <- corrigido (antes estava 'setorsetor')

// redireciona conforme tipo
$page = ($usuario['tipo'] === 'admin') ? 'diretorias' : 'home'; // <- 'diretorias' e não 'setor'
header("Location: index.php?page=" . $page);
exit;
