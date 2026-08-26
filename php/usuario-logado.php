<?php

session_start();
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION["usuario_id"])) {
    http_response_code(401);

    echo json_encode(
        ["erro" => "Não autenticado"],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

require "conexao.php";

$stmt = $conexao->prepare(
    "SELECT nome, email, telefone, cidade, tipo
     FROM usuarios
     WHERE id = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(
        ["erro" => "Erro ao consultar usuário"],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$stmt->bind_param("i", $_SESSION["usuario_id"]);
$stmt->execute();

$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$stmt->close();
$conexao->close();

if (!$usuario) {
    http_response_code(404);

    echo json_encode(
        ["erro" => "Usuário não encontrado"],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

echo json_encode($usuario, JSON_UNESCAPED_UNICODE);
?>