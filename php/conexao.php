<?php
$servidor = "localhost";   // geralmente "localhost"
$usuario  = "root";    // geralmente "root"
$senha    = "senac";      // a senha do seu MySQL
$banco    = "topturismo";  // o nome do banco de dados
$porta    = 3307;             // a porta do MySQL

$conexao = new mysqli($servidor, $usuario, $senha, $banco, $porta);

if ($conexao->connect_error) {
    die("Erro ao conectar: " . $conexao->connect_error);
}
