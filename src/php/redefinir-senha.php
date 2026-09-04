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
if ($hash === false) {
    $conexao->close();
    header("Location: ../pages/redefinir-senha.php?erro=" . urlencode("Não foi possível gerar a nova senha. Tente novamente."));
    exit;
}

// Atualiza a senha usando o ID liberado pelo processo de recuperação.
$stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ? LIMIT 1");
if (!$stmt) {
    error_log("TopTurismo redefinição - erro no UPDATE prepare: " . $conexao->error);
    $conexao->close();
    header("Location: ../pages/redefinir-senha.php?erro=" . urlencode("Não foi possível atualizar sua senha. Tente novamente."));
    exit;
}

$stmt->bind_param("si", $hash, $idUsuario);
$executou = $stmt->execute();
$erroSql = $stmt->error;
$linhasAlteradas = $stmt->affected_rows;
$stmt->close();

// Confirma no próprio banco que a senha gravada corresponde à senha digitada.
$sucesso = false;
if ($executou && $linhasAlteradas >= 1) {
    $stmtVerifica = $conexao->prepare("SELECT senha FROM usuarios WHERE id = ? LIMIT 1");
    if ($stmtVerifica) {
        $stmtVerifica->bind_param("i", $idUsuario);
        $stmtVerifica->execute();
        $resultado = $stmtVerifica->get_result();
        $usuarioAtualizado = $resultado->fetch_assoc();
        $stmtVerifica->close();
        $sucesso = $usuarioAtualizado
            && !empty($usuarioAtualizado["senha"])
            && password_verify($senha, $usuarioAtualizado["senha"]);
    }
}

if (!$sucesso) {
    error_log("TopTurismo redefinição - falha ao confirmar senha. ID: {$idUsuario}; SQL: {$erroSql}; linhas alteradas: {$linhasAlteradas}");
    $conexao->close();
    header("Location: ../pages/redefinir-senha.php?erro=" . urlencode("A nova senha não pôde ser confirmada no banco de dados. Tente novamente."));
    exit;
}

$conexao->close();

// A recuperação é encerrada somente depois de confirmar que a nova senha foi gravada.
unset($_SESSION["recuperacao_usuario_id"], $_SESSION["recuperacao_expira"]);

header("Location: ../pages/login.php?sucesso=senha");
exit;
