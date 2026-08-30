<?php
session_start();

// *controla as duas etapas da recuperação: e-mail e palavra-chave*

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/esqueci-senha.php");
    exit;
}

require "conexao.php";

$etapa = $_POST["etapa"] ?? "email";

if ($etapa === "email") {
    $email = trim($_POST["email"] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../pages/esqueci-senha.php?erro=" . urlencode("E-mail inválido."));
        exit;
    }

    $stmt = $conexao->prepare("SELECT id, chave_recuperacao_hash FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conexao->close();

    if (!$usuario || empty($usuario["chave_recuperacao_hash"])) {
        header("Location: ../pages/esqueci-senha.php?erro=" . urlencode("Não foi possível iniciar a recuperação. Verifique o e-mail ou cadastre uma nova conta com uma palavra-chave de recuperação."));
        exit;
    }

    $_SESSION["recuperacao_email"] = $email;
    header("Location: ../pages/esqueci-senha.php?etapa=chave");
    exit;
}

if ($etapa === "chave") {
    $email = $_SESSION["recuperacao_email"] ?? "";
    $chave = trim($_POST["chave_recuperacao"] ?? "");

    if ($email === "" || $chave === "") {
        header("Location: ../pages/esqueci-senha.php?etapa=chave&erro=" . urlencode("Informe sua palavra-chave de recuperação."));
        exit;
    }

    $stmt = $conexao->prepare("SELECT id, chave_recuperacao_hash FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conexao->close();

    if (!$usuario || empty($usuario["chave_recuperacao_hash"]) || !password_verify($chave, $usuario["chave_recuperacao_hash"])) {
        header("Location: ../pages/esqueci-senha.php?etapa=chave&erro=" . urlencode("Palavra-chave incorreta."));
        exit;
    }

    session_regenerate_id(true);
    $_SESSION["recuperacao_usuario_id"] = (int) $usuario["id"];
    $_SESSION["recuperacao_expira"] = time() + 900;
    unset($_SESSION["recuperacao_email"]);

    header("Location: ../pages/redefinir-senha.php");
    exit;
}

header("Location: ../pages/esqueci-senha.php");
exit;
