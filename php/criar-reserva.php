<?php

session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once "conexao.php";

/**
 * Resposta JSON padronizada para os endpoints de reserva.
 */
function responder(
    int $statusHttp,
    bool $sucesso,
    string $mensagem,
    array $extra = []
): never {
    http_response_code($statusHttp);

    echo json_encode(
        array_merge(
            [
                "sucesso" => $sucesso,
                "mensagem" => $mensagem,
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

// A criação de reserva aceita somente POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responder(405, false, "Método não permitido.");
}

// A reserva sempre pertence ao usuário autenticado pela sessão.
if (!isset($_SESSION["usuario_id"])) {
    responder(401, false, "Usuário não autenticado.");
}

$dados = json_decode(file_get_contents("php://input"), true);

if (!is_array($dados)) {
    responder(400, false, "Dados da reserva inválidos.");
}

// Os dados do navegador são tratados como não confiáveis.
$id_usuario = (int) $_SESSION["usuario_id"];
$id_destino = filter_var($dados["id_destino"] ?? null, FILTER_VALIDATE_INT);
$data_viagem = trim((string) ($dados["data_viagem"] ?? ""));
$quantidade_passageiros = filter_var(
    $dados["quantidade_passageiros"] ?? null,
    FILTER_VALIDATE_INT
);
$transporte = trim((string) ($dados["transporte"] ?? ""));
$assento = trim((string) ($dados["assento"] ?? ""));
$tipo_viagem = trim((string) ($dados["tipo_viagem"] ?? "ida"));

if (
    !$id_destino
    || !$quantidade_passageiros
    || $data_viagem === ""
    || $transporte === ""
    || $assento === ""
) {
    responder(400, false, "Preencha todos os campos obrigatórios.");
}

// Limite operacional para evitar reservas com quantidade inválida.
if ($quantidade_passageiros < 1 || $quantidade_passageiros > 9) {
    responder(400, false, "A quantidade de passageiros deve estar entre 1 e 9.");
}

// Validação estrita da data recebida.
$data = DateTime::createFromFormat("Y-m-d", $data_viagem);

if (!$data || $data->format("Y-m-d") !== $data_viagem) {
    responder(400, false, "Data de viagem inválida.");
}

$hoje = new DateTime("today");
$dataMinima = (clone $hoje)->modify("+1 month");

if ($data < $hoje) {
    responder(400, false, "A reserva não pode ser feita no passado.");
}

if ($data < $dataMinima) {
    responder(400, false, "A reserva deve ser feita com pelo menos 1 mês de antecedência.");
}

// Apenas as opções existentes no sistema podem chegar ao banco.
if (!in_array($transporte, ["Avião", "Ônibus"], true)) {
    responder(400, false, "Tipo de transporte inválido.");
}

if (!in_array($assento, ["Econômica", "Executiva", "VIP"], true)) {
    responder(400, false, "Classe inválida.");
}

if ($transporte === "Ônibus" && $assento !== "Econômica") {
    responder(400, false, "Ônibus disponível somente na classe Econômica.");
}

/**
 * O preço é buscado novamente no banco.
 *
 * Isso é importante: o navegador não pode enviar um preço próprio e
 * manipular o valor final da reserva antes do INSERT.
 */
$sqlDestino = "
    SELECT preco_destino
    FROM destinos
    WHERE id_destino = ?
    LIMIT 1
";

$stmtDestino = $conexao->prepare($sqlDestino);

if (!$stmtDestino) {
    responder(500, false, "Erro ao consultar o destino.");
}

$stmtDestino->bind_param("i", $id_destino);
$stmtDestino->execute();

$resultadoDestino = $stmtDestino->get_result();
$destino = $resultadoDestino->fetch_assoc();
$stmtDestino->close();

if (!$destino) {
    responder(404, false, "Destino não encontrado.");
}

// O preço do destino é por passageiro.
$precoBase = (float) $destino["preco_destino"];
$subtotal = $precoBase * $quantidade_passageiros;

if ($tipo_viagem === "ida_volta") {
    $subtotal *= 2;
}

if ($transporte === "Ônibus") {
    $subtotal *= 0.70;
}

if ($assento === "VIP") {
    $subtotal += 150 * $quantidade_passageiros;
} elseif ($assento === "Executiva") {
    $subtotal += 300 * $quantidade_passageiros;
}

// Regras comerciais ficam no servidor para evitar manipulação pelo cliente.
$descontoEconomica =
    ($transporte === "Avião" && $assento === "Econômica") ? 0.08 : 0;

$descontoGrupo = min(
    floor($quantidade_passageiros / 2) * 0.03,
    0.12
);

$percentualDesconto = $descontoEconomica + $descontoGrupo;
$desconto = $subtotal * $percentualDesconto;
$valor_total = round($subtotal - $desconto, 2);

$status = "pendente";

$sql = "
    INSERT INTO reservas (
        id_usuario,
        id_destino,
        data_viagem,
        quantidade_passageiros,
        transporte,
        assento,
        valor_total,
        status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $conexao->prepare($sql);

if (!$stmt) {
    responder(500, false, "Erro ao preparar a reserva.");
}

$stmt->bind_param(
    "iisissds",
    $id_usuario,
    $id_destino,
    $data_viagem,
    $quantidade_passageiros,
    $transporte,
    $assento,
    $valor_total,
    $status
);

if (!$stmt->execute()) {
    $stmt->close();
    $conexao->close();

    responder(500, false, "Erro ao salvar a reserva.");
}

$id_reserva = $stmt->insert_id;

$stmt->close();
$conexao->close();

responder(201, true, "Reserva realizada com sucesso!", [
    "id_reserva" => $id_reserva,
    "valor_total" => $valor_total,
]);
