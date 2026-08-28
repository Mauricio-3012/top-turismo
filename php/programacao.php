<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "conexao.php";

$idDestino = filter_input(INPUT_GET, "id_destino", FILTER_VALIDATE_INT);
$transporte = trim((string)($_GET["transporte"] ?? ""));

if (!$idDestino || !in_array($transporte, ["Avião", "Ônibus"], true)) {
    http_response_code(400);
    echo json_encode(["sucesso" => false, "mensagem" => "Destino ou transporte inválido."], JSON_UNESCAPED_UNICODE);
    exit;
}

// Horários são definidos pela programação da TopTurismo; o passageiro não escolhe.
$programacaoAviao = [
    1 => ["saida" => "08:30", "volta" => "18:00", "duracao" => 170],
    2 => ["saida" => "09:15", "volta" => "19:10", "duracao" => 100],
    3 => ["saida" => "07:45", "volta" => "18:20", "duracao" => 140],
    4 => ["saida" => "10:00", "volta" => "20:00", "duracao" => 130],
    5 => ["saida" => "11:20", "volta" => "21:10", "duracao" => 100],
    6 => ["saida" => "08:20", "volta" => "17:50", "duracao" => 120],
    7 => ["saida" => "06:50", "volta" => "18:30", "duracao" => 210],
    8 => ["saida" => "09:40", "volta" => "19:40", "duracao" => 220],
    9 => ["saida" => "08:50", "volta" => "18:40", "duracao" => 120],
    10 => ["saida" => "10:30", "volta" => "20:30", "duracao" => 105],
    11 => ["saida" => "07:10", "volta" => "17:00", "duracao" => 200],
    12 => ["saida" => "09:00", "volta" => "18:00", "duracao" => 120],
    13 => ["saida" => "08:10", "volta" => "19:00", "duracao" => 200],
    14 => ["saida" => "10:50", "volta" => "20:50", "duracao" => 95],
    15 => ["saida" => "06:40", "volta" => "17:40", "duracao" => 210],
    16 => ["saida" => "09:30", "volta" => "18:50", "duracao" => 140],
];

$programacaoOnibus = [
    1 => ["saida" => "06:30", "volta" => "18:30", "duracao" => 570],
    2 => ["saida" => "07:00", "volta" => "20:00", "duracao" => 360],
    3 => ["saida" => "06:00", "volta" => "19:00", "duracao" => 480],
    4 => ["saida" => "07:30", "volta" => "19:30", "duracao" => 720],
    5 => ["saida" => "08:00", "volta" => "20:00", "duracao" => 300],
    6 => ["saida" => "06:20", "volta" => "18:20", "duracao" => 840],
    7 => ["saida" => "05:30", "volta" => "17:30", "duracao" => 1080],
    8 => ["saida" => "05:00", "volta" => "17:00", "duracao" => 1080],
    9 => ["saida" => "07:10", "volta" => "19:10", "duracao" => 600],
    10 => ["saida" => "08:20", "volta" => "20:20", "duracao" => 360],
    11 => ["saida" => "06:00", "volta" => "18:00", "duracao" => 900],
    12 => ["saida" => "07:40", "volta" => "19:40", "duracao" => 480],
    13 => ["saida" => "06:40", "volta" => "18:40", "duracao" => 900],
    14 => ["saida" => "08:30", "volta" => "20:30", "duracao" => 300],
    15 => ["saida" => "05:50", "volta" => "17:50", "duracao" => 960],
    16 => ["saida" => "06:50", "volta" => "18:50", "duracao" => 780],
];

$stmt = $conexao->prepare("SELECT id_destino FROM destinos WHERE id_destino = ? LIMIT 1");
$stmt->bind_param("i", $idDestino);
$stmt->execute();
$existe = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conexao->close();

if (!$existe) {
    http_response_code(404);
    echo json_encode(["sucesso" => false, "mensagem" => "Destino não encontrado."], JSON_UNESCAPED_UNICODE);
    exit;
}

$programacao = $transporte === "Avião" ? ($programacaoAviao[$idDestino] ?? null) : ($programacaoOnibus[$idDestino] ?? null);
if (!$programacao) {
    http_response_code(404);
    echo json_encode(["sucesso" => false, "mensagem" => "Programação não disponível para este destino."], JSON_UNESCAPED_UNICODE);
    exit;
}

$programacao["duracao_minutos"] = $programacao["duracao"];
unset($programacao["duracao"]);
$hoje = new DateTime("today");
$maximo = (clone $hoje)->modify("+9 months");
$programacao["data_minima"] = $hoje->format("Y-m-d");
$programacao["data_maxima"] = $maximo->format("Y-m-d");
echo json_encode(["sucesso" => true, "programacao" => $programacao], JSON_UNESCAPED_UNICODE);
