<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require "conexao.php";

    $email = $_POST["email"];
    $senha = $_POST["senha"];

    // busca o usuário pelo e-mail
    $sql = "SELECT id, nome, senha, tipo FROM usuarios WHERE email = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    if ($usuario && password_verify($senha, $usuario["senha"])) {
        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nome"] = $usuario["nome"];
        $_SESSION["usuario_tipo"] = $usuario["tipo"];

        header("Location: ../pages/dashboard.html");
        exit;
    } else {
        header("Location: ../pages/login.html?erro=1");
        exit;
    }
} else {
    header("Location: ../pages/login.html");
    exit;
}
?>