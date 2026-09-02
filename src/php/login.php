<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/login.php");
    exit;
}

require_once __DIR__ . "/conexao.php";

$email = mb_strtolower(trim((string) ($_POST["email"] ?? "")), "UTF-8");
$senha = (string) ($_POST["senha"] ?? "");

if ($email === "" || $senha === "") {
    $conexao->close();
    header("Location: ../pages/login.php?erro=" . urlencode("Informe seu e-mail e sua senha."));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $conexao->close();
    header("Location: ../pages/login.php?erro=" . urlencode("Informe um e-mail válido."));
    exit;
}

$stmt = $conexao->prepare("SELECT id, nome, senha, tipo FROM usuarios WHERE email = ? LIMIT 1");
if (!$stmt) {
    $conexao->close();
    header("Location: ../pages/login.php?erro=" . urlencode("Não foi possível realizar o login. Tente novamente."));
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();
$conexao->close();

if (!$usuario || !is_string($usuario["senha"] ?? null) || !password_verify($senha, $usuario["senha"])) {
    header("Location: ../pages/login.php?erro=" . urlencode("E-mail ou senha incorretos."));
    exit;
}

session_regenerate_id(true);
$_SESSION["usuario_id"] = (int) $usuario["id"];
$_SESSION["usuario_nome"] = $usuario["nome"];
$_SESSION["usuario_tipo"] = $usuario["tipo"];

header($usuario["tipo"] === "admin" ? "Location: ../admin/dashboard.php" : "Location: ../pages/dashboard.php");
exit;
