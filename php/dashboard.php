<?php
session_start();
include('conexao.php');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';

    $stmt = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nome, $email, $telefone, $id);
    $sucesso = $stmt->execute();
    $stmt->close();

    if (!empty($_POST['senha_nova'])) {
        $novaSenhaHash = password_hash($_POST['senha_nova'], PASSWORD_DEFAULT);
        $stmtSenha = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmtSenha->bind_param("si", $novaSenhaHash, $id);
        $sucesso = $stmtSenha->execute() && $sucesso;
        $stmtSenha->close();
    }
// mantém a sessão atualizada
    $_SESSION['usuario_nome'] = $nome;

    header("Location: ../pages/dashboard.php?" . ($sucesso ? "sucesso=1" : "erro=1"));
    exit();
}

header("Location: ../pages/dashboard.php");
exit();
?>