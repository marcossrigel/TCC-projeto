<?php
$host = '127.0.0.1';   
$usuario = 'root';     
$senha = '';            
$banco = 'siscreche'; 

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die('Erro na conexão com o banco de dados: ' . $conexao->connect_error);
}
?>
