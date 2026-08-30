<?php
header("Content-Type: application/json; charset=UTF-8");
// *retorna os assentos ocupados para impedir escolhas duplicadas*

function responderAssentos(int $status, bool $sucesso, string $mensagem, array $extra = []): never
{
    http_response_code($status);
    echo json_encode(
        array_merge(["sucesso" => $sucesso, "mensagem" => $mensagem], $extra),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

set_exception_handler(function (Throwable $erro): void {
    error_log("TopTurismo assentos: " . $erro->getMessage());
    responderAssentos(500, false, "Não foi possível consultar os assentos.");
});

require_once __DIR__ . "/conexao.php";

$idDestino = filter_input(INPUT_GET, "id_destino", FILTER_VALIDATE_INT);
$dataViagem = trim((string)($_GET["data_viagem"] ?? ""));
$transporte = trim((string)($_GET["transporte"] ?? ""));
$classe = trim((string)($_GET["classe"] ?? ""));

if (!$idDestino || !$dataViagem || !in_array($transporte, ["Avião", "Ônibus"], true) || !in_array($classe, ["Econômica", "Executiva", "VIP"], true)) {
    responderAssentos(400, false, "Parâmetros inválidos.");
}

$data = DateTime::createFromFormat("Y-m-d", $dataViagem);
if (!$data || $data->format("Y-m-d") !== $dataViagem) {
    responderAssentos(400, false, "Data de viagem inválida.");
}

$stmt = $conexao->prepare(
    "SELECT assento
     FROM reservas
     WHERE id_destino = ?
       AND data_viagem = ?
       AND transporte = ?
       AND classe = ?
       AND LOWER(status) <> 'cancelada'"
);

if (!$stmt) {
    responderAssentos(500, false, "Não foi possível consultar os assentos.");
}

$stmt->bind_param("isss", $idDestino, $dataViagem, $transporte, $classe);
$stmt->execute();
$resultado = $stmt->get_result();
$ocupados = [];

while ($row = $resultado->fetch_assoc()) {
    foreach (preg_split('/\s*,\s*/', (string)$row["assento"]) as $assento) {
        $assento = strtoupper(trim($assento));
        if ($assento !== "") $ocupados[] = $assento;
    }
}

$stmt->close();
$conexao->close();

responderAssentos(200, true, "Assentos consultados.", [
    "ocupados" => array_values(array_unique($ocupados))
]);
