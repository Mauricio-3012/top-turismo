<?php
// *abre a conexão do projeto com o banco de dados MySQL*

$servidor = 'localhost';
$usuario  = 'root';
$senha    = '';
$banco    = 'topturismo';
$porta    = 3306;

mysqli_report(MYSQLI_REPORT_OFF);

$conexao = new mysqli($servidor, $usuario, $senha, $banco, $porta);

if ($conexao->connect_errno) {
    die('Não foi possível conectar ao banco de dados. Verifique servidor, porta, usuário e senha.');
}

$conexao->set_charset('utf8mb4');
