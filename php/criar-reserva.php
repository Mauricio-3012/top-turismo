<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário não autenticado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

require __DIR__ . '/conexao.php';

$usuarioId = (int) $_SESSION['usuario_id'];
$destinoId = filter_input(INPUT_POST, 'destino_id', FILTER_VALIDATE_INT);
$passageiros = filter_input(INPUT_POST, 'passageiros', FILTER_VALIDATE_INT);
$dataViagem = trim($_POST['data'] ?? '');
$transporte = trim($_POST['transporte'] ?? '');
$assento = trim($_POST['assento'] ?? '');
$valorTotal = filter_var($_POST['valor_total'] ?? null, FILTER_VALIDATE_FLOAT);

$transportesPermitidos = ['Avião', 'Ônibus'];
$assentosPermitidos = ['Padrão', 'VIP', 'Executiva'];

$data = DateTime::createFromFormat('Y-m-d', $dataViagem);
$hoje = new DateTime('today');

if (!$destinoId || !$passageiros || $passageiros < 1 || $passageiros > 9 || !$data || $data->format('Y-m-d') !== $dataViagem || $data < $hoje || !in_array($transporte, $transportesPermitidos, true) || !in_array($assento, $assentosPermitidos, true) || $valorTotal === false || $valorTotal < 0) {
    http_response_code(422);
    echo json_encode(['erro' => 'Dados da reserva inválidos.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmtDestino = $conexao->prepare('SELECT id_destino FROM destinos WHERE id_destino = ? LIMIT 1');
$stmtDestino->bind_param('i', $destinoId);
$stmtDestino->execute();
$destinoExiste = $stmtDestino->get_result()->fetch_assoc();
$stmtDestino->close();

if (!$destinoExiste) {
    http_response_code(404);
    echo json_encode(['erro' => 'Destino não encontrado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = 'INSERT INTO reservas (usuario_id, id_destino, data_viagem, passageiros, transporte, assento, valor_total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
$status = 'Confirmada';
$stmt = $conexao->prepare($sql);
$stmt->bind_param('iisissds', $usuarioId, $destinoId, $dataViagem, $passageiros, $transporte, $assento, $valorTotal, $status);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['erro' => 'Não foi possível salvar a reserva.'], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}

$idReserva = $stmt->insert_id;
$stmt->close();

echo json_encode(['sucesso' => true, 'id_reserva' => $idReserva], JSON_UNESCAPED_UNICODE);
