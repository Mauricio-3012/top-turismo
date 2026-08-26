<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require "conexao.php";

    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($email === "" || $senha === "") {
        header("Location: ../pages/login.php?erro=2");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../pages/login.php?erro=1");
        exit;
    }

    $sql = "SELECT id, nome, senha, tipo FROM usuarios WHERE email = ?";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        header("Location: ../pages/login.php?erro=1");
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();

    if ($usuario && password_verify($senha, $usuario["senha"])) {
        session_regenerate_id(true);

        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nome"] = $usuario["nome"];
        $_SESSION["usuario_tipo"] = $usuario["tipo"];

        $conexao->close();

        header($usuario["tipo"] === "admin" ? "Location: ../admin/dashboard.php" : "Location: ../pages/dashboard.php");
        exit;
    }

    $conexao->close();

    header("Location: ../pages/login.php?erro=1");
    exit;
}

header("Location: ../pages/login.php");
exit;
?>