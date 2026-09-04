<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/esqueci-senha.php");
    exit;
}

require "conexao.php";

$etapa = $_POST["etapa"] ?? "email";

if ($etapa === "email") {
    $email = mb_strtolower(trim($_POST["email"] ?? ""), "UTF-8");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../pages/esqueci-senha.php?erro=" . urlencode("E-mail inválido."));
        exit;
    }

    $stmt = $conexao->prepare("SELECT id, pergunta_recuperacao, resposta_recuperacao_hash, chave_recuperacao_hash FROM usuarios WHERE email = ? LIMIT 1");
    if (!$stmt) {
        error_log("TopTurismo recuperação - erro no SELECT email: " . $conexao->error);
        $conexao->close();
        header("Location: ../pages/esqueci-senha.php?erro=" . urlencode("Não foi possível iniciar a recuperação. Verifique se o banco de dados está atualizado."));
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conexao->close();

    if (!$usuario) {
        header("Location: ../pages/esqueci-senha.php?erro=" . urlencode("Não encontramos uma conta com esse e-mail."));
        exit;
    }

    // Contas novas usam pergunta + resposta. Contas antigas continuam podendo usar a palavra-chave antiga.
    if (!empty($usuario["pergunta_recuperacao"]) && !empty($usuario["resposta_recuperacao_hash"])) {
        $_SESSION["recuperacao_email"] = $email;
        $_SESSION["recuperacao_pergunta"] = $usuario["pergunta_recuperacao"];
        header("Location: ../pages/esqueci-senha.php?etapa=pergunta");
        exit;
    }

    if (!empty($usuario["chave_recuperacao_hash"])) {
        $_SESSION["recuperacao_email"] = $email;
        header("Location: ../pages/esqueci-senha.php?etapa=chave");
        exit;
    }

    header("Location: ../pages/esqueci-senha.php?erro=" . urlencode("Esta conta não possui uma forma de recuperação cadastrada."));
    exit;
}

if ($etapa === "pergunta") {
    $email = $_SESSION["recuperacao_email"] ?? "";
    $resposta = trim($_POST["resposta_recuperacao"] ?? "");

    if ($email === "" || $resposta === "") {
        header("Location: ../pages/esqueci-senha.php?etapa=pergunta&erro=" . urlencode("Informe a resposta da pergunta."));
        exit;
    }

    $stmt = $conexao->prepare("SELECT id, resposta_recuperacao_hash FROM usuarios WHERE email = ? LIMIT 1");
    if (!$stmt) {
        error_log("TopTurismo recuperação - erro no SELECT resposta: " . $conexao->error);
        $conexao->close();
        header("Location: ../pages/esqueci-senha.php?etapa=pergunta&erro=" . urlencode("Não foi possível validar a resposta."));
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conexao->close();

    $respostaNormalizada = mb_strtolower($resposta, 'UTF-8');
    if (!$usuario || empty($usuario["resposta_recuperacao_hash"]) || !password_verify($respostaNormalizada, $usuario["resposta_recuperacao_hash"])) {
        header("Location: ../pages/esqueci-senha.php?etapa=pergunta&erro=" . urlencode("Resposta incorreta."));
        exit;
    }

    session_regenerate_id(true);
    $_SESSION["recuperacao_usuario_id"] = (int) $usuario["id"];
    $_SESSION["recuperacao_expira"] = time() + 900;
    unset($_SESSION["recuperacao_email"], $_SESSION["recuperacao_pergunta"]);

    header("Location: ../pages/redefinir-senha.php");
    exit;
}

// Compatibilidade com contas criadas na versão antiga.
if ($etapa === "chave") {
    $email = $_SESSION["recuperacao_email"] ?? "";
    $chave = trim($_POST["chave_recuperacao"] ?? "");

    if ($email === "" || $chave === "") {
        header("Location: ../pages/esqueci-senha.php?etapa=chave&erro=" . urlencode("Informe sua palavra-chave de recuperação."));
        exit;
    }

    $stmt = $conexao->prepare("SELECT id, chave_recuperacao_hash FROM usuarios WHERE email = ? LIMIT 1");
    if (!$stmt) {
        error_log("TopTurismo recuperação - erro no SELECT chave: " . $conexao->error);
        $conexao->close();
        header("Location: ../pages/esqueci-senha.php?etapa=chave&erro=" . urlencode("Não foi possível validar a palavra-chave."));
        exit;
    }

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
