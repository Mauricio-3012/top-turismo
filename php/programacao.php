<?php
header("Content-Type: application/json; charset=UTF-8");
// *consulta os horários da programação usada na reserva*
require_once __DIR__ . "/programacao-dados.php";

$idDestino = filter_input(INPUT_GET, "id_destino", FILTER_VALIDATE_INT);
$transporte = trim((string)($_GET["transporte"] ?? ""));

if (!$idDestino || !in_array($transporte, ["Avião", "Ônibus"], true)) {
    http_response_code(400);
    echo json_encode(["sucesso" => false, "mensagem" => "Destino ou transporte inválido."], JSON_UNESCAPED_UNICODE);
    exit;
}

$programacao = programacaoPorId($idDestino, $transporte);

if (!$programacao) {
    http_response_code(404);
    echo json_encode(["sucesso" => false, "mensagem" => "Programação não disponível para este destino."], JSON_UNESCAPED_UNICODE);
    exit;
}

$hoje = new DateTime("today");
$maximo = (clone $hoje)->modify("+9 months");

$programacao["data_minima"] = $hoje->format("Y-m-d");
$programacao["data_maxima"] = $maximo->format("Y-m-d");

echo json_encode(["sucesso" => true, "programacao" => $programacao], JSON_UNESCAPED_UNICODE);
