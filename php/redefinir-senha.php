<?php
session_start();

// *confere o prazo da recuperação antes de aceitar uma nova senha*

$idUsuario = (int) ($_SESSION["recuperacao_usuario_id"] ?? 0);
$expira = (int) ($_SESSION["recuperacao_expira"] ?? 0);

if (!$idUsuario || time() > $expira) {
    unset($_SESSION["recuperacao_usuario_id"], $_SESSION["recuperacao_expira"]);
    header("Location: ../pages/esqueci-senha.php?erro=Sua recuperação expirou. Comece novamente.");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/redefinir-senha.php");
    exit;
}

$senha = $_POST["senha"] ?? "";
$confirmar = $_POST["confirmar_senha"] ?? "";

if (
    strlen($senha) < 8
    || !preg_match("/[a-z]/", $senha)
    || !preg_match("/[A-Z]/", $senha)
    || !preg_match("/[0-9]/", $senha)
) {
    header("Location: ../pages/redefinir-senha.php?erro=" . urlencode("A senha deve ter no mínimo 8 caracteres, com letra maiúscula, minúscula e número."));
    exit;
}

if ($senha !== $confirmar) {
    header("Location: ../pages/redefinir-senha.php?erro=" . urlencode("As senhas não coincidem."));
    exit;
}

require "conexao.php";

$hash = password_hash($senha, PASSWORD_DEFAULT);
$stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
$stmt->bind_param("si", $hash, $idUsuario);
$sucesso = $stmt->execute() && $stmt->affected_rows >= 0;
$stmt->close();
$conexao->close();

unset($_SESSION["recuperacao_usuario_id"], $_SESSION["recuperacao_expira"]);

if (!$sucesso) {
    header("Location: ../pages/redefinir-senha.php?erro=" . urlencode("Não foi possível atualizar sua senha. Tente novamente."));
    exit;
}

header("Location: ../pages/login.php?sucesso=senha");
exit;
