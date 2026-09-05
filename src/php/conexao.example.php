<?php
/**
 * TopTurismo - conexão com o banco de dados MySQL.
 *
 * Copie este arquivo e renomeie para "conexao.php".
 * Não envie o conexao.php para o GitHub.
 */

$servidor = '[nome do servidor]'; // Servidor do banco. Geralmente "localhost".
$usuario = '[nome do usuário]'; // Usuário do banco. Geralmente "root" no XAMPP.
$senha = '[senha do banco]'; // Senha do banco.
$banco = '[nome do banco]'; // Nome do banco de dados.
$porta = '[porta do MySQL]'; // Porta do MySQL. Geralmente 3306.

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