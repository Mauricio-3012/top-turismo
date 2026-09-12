<?php

$servidor = '[NOME DO SERVIDOR]'; // Servidor do banco. Geralmente "localhost".
$usuario = '[USUARIO DO BANCO]'; // Usuário do banco. Geralmente "root".
$senha = '[SENHA DO BANCO]'; // Senha do banco.
$banco = '[NOME DO BANCO]'; // Nome do banco de dados.
$porta = '[PORTA DO MYSQL]'; // Porta do MySQL.

mysqli_report(MYSQLI_REPORT_OFF);

$conexao = new mysqli($servidor, $usuario, $senha, $banco, $porta);

if ($conexao->connect_errno) {
    error_log(
        'TopTurismo - falha ao conectar no banco: ' .
        $conexao->connect_error
    );

    die(
        'Não foi possível conectar ao banco de dados. ' .
        'Verifique as configurações da conexão.'
    );
}

$conexao->set_charset('utf8mb4');
?>