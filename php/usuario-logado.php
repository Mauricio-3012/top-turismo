<?php
session_start();
header("Content-Type: application/json");

// Se não estiver logado, retorna erro 401
if (!isset($_SESSION["usuario_id"])) {
    http_response_code(401);
    echo json_encode(["erro" => "Não autenticado"]);
    exit;
}

require "conexao.php";

$stmt = $conexao->prepare("SELECT nome, email, telefone FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION["usuario_id"]);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    http_response_code(404);
    echo json_encode(["erro" => "Usuário não encontrado"]);
    exit;
}

echo json_encode($usuario);
?>