<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../pages/login.php");
    exit();
}

$id = (int) $_SESSION["usuario_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $cidade = trim($_POST["cidade"] ?? "");

    $stmt = $conexao->prepare(
        "UPDATE usuarios
         SET nome = ?, email = ?, telefone = ?, cidade = ?
         WHERE id = ?"
    );

    $stmt->bind_param(
        "ssssi",
        $nome,
        $email,
        $telefone,
        $cidade,
        $id
    );

    $sucesso = $stmt->execute();
    $stmt->close();

    if (!empty($_POST["senha_nova"])) {
        $novaSenhaHash = password_hash(
            $_POST["senha_nova"],
            PASSWORD_DEFAULT
        );

        $stmtSenha = $conexao->prepare(
            "UPDATE usuarios SET senha = ? WHERE id = ?"
        );

        $stmtSenha->bind_param("si", $novaSenhaHash, $id);

        $sucesso = $stmtSenha->execute() && $sucesso;
        $stmtSenha->close();
    }

    $_SESSION["usuario_nome"] = $nome;

    $conexao->close();

    header(
        "Location: ../pages/dashboard.php?"
        . ($sucesso ? "sucesso=1" : "erro=1")
    );
    exit();
}

$conexao->close();

header("Location: ../pages/dashboard.php");
exit();
?>