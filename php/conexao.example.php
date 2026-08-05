<?php
$servidor = "[SEU_SERVIDOR]";   // geralmente "localhost"
$usuario  = "[SEU_USUARIO]";    // geralmente "root"
$senha    = "[SUA_SENHA]";      // a senha do seu MySQL
$banco    = "[NOME_DO_BANCO]";  // o nome do banco de dados
$porta    = 0000;             // a porta do MySQL

$conexao = new mysqli($servidor, $usuario, $senha, $banco, $porta);

if ($conexao->connect_error) {
    die("Erro ao conectar: " . $conexao->connect_error);
}