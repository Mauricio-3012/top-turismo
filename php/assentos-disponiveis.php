<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "conexao.php";

$idDestino = filter_input(INPUT_GET, "id_destino", FILTER_VALIDATE_INT);
$dataViagem = trim((string)($_GET["data_viagem"] ?? ""));
$transporte = trim((string)($_GET["transporte"] ?? ""));
$classe = trim((string)($_GET["classe"] ?? ""));

if (!$idDestino || !$dataViagem || !in_array($transporte, ["Avião", "Ônibus"], true) || !in_array($classe, ["Econômica", "Executiva", "VIP"], true)) {
    http_response_code(400);
    echo json_encode(["sucesso" => false, "mensagem" => "Parâmetros inválidos."], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "SELECT assento FROM reservas WHERE id_destino = ? AND data_viagem = ? AND transporte = ? AND classe = ? AND LOWER(status) <> 'cancelada'";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("isss", $idDestino, $dataViagem, $transporte, $classe);
$stmt->execute();
$resultado = $stmt->get_result();
$ocupados = [];
while ($row = $resultado->fetch_assoc()) {
    foreach (preg_split('/\s*,\s*/', (string)$row["assento"]) as $assento) {
        if ($assento !== "") $ocupados[] = $assento;
    }
}
$stmt->close();
$conexao->close();

echo json_encode(["sucesso" => true, "ocupados" => array_values(array_unique($ocupados))], JSON_UNESCAPED_UNICODE);
