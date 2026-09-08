<?php
session_start();
// Permite cancelar somente uma reserva pertencente ao usuário logado.
// Como o pagamento é uma SIMULAÇÃO, o cancelamento também simula o reembolso integral.
require_once __DIR__ . '/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(401);
        echo json_encode(['sucesso'=>false,'mensagem'=>'Usuário não autenticado.'], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: ../pages/login.php');
    }
    exit;
}

$idReserva = 0;
$isJson = isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json');
if ($isJson) {
    $dados = json_decode(file_get_contents('php://input'), true);
    $idReserva = filter_var($dados['id_reserva'] ?? null, FILTER_VALIDATE_INT) ?: 0;
} else {
    $idReserva = filter_var($_POST['id_reserva'] ?? null, FILTER_VALIDATE_INT) ?: 0;
}

function responder($ok, $mensagem, $status = 200, $dados = []) {
    global $isJson;
    if ($isJson) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($status);
        echo json_encode(array_merge(['sucesso'=>$ok,'mensagem'=>$mensagem], $dados), JSON_UNESCAPED_UNICODE);
    } else {
        $query = $ok
            ? 'sucesso_cancelamento=1&reembolso=1'
            : 'erro=' . urlencode($mensagem);
        header('Location: ../pages/dashboard.php?' . $query . '#minhas-viagens');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responder(false, 'Método não permitido.', 405);
if ($idReserva < 1) responder(false, 'Reserva inválida.', 400);

$idUsuario = (int)$_SESSION['usuario_id'];
$stmt = $conexao->prepare('SELECT data_viagem, status, status_pagamento, valor_total FROM reservas WHERE id_reserva = ? AND id_usuario = ? LIMIT 1');
if (!$stmt) responder(false, 'Erro ao consultar a reserva.', 500);
$stmt->bind_param('ii', $idReserva, $idUsuario);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reserva) responder(false, 'Reserva não encontrada.', 404);
if (strtolower(trim((string)$reserva['status'])) === 'cancelada') responder(false, 'Esta reserva já está cancelada.', 409);
if (strtotime((string)$reserva['data_viagem']) < strtotime('today')) responder(false, 'Não é possível cancelar uma viagem que já passou.', 409);

$valorReembolso = (float)$reserva['valor_total'];

// O projeto não processa dinheiro real. O valor é registrado como reembolso integral simulado.
$stmt = $conexao->prepare("UPDATE reservas
    SET status = 'cancelada',
        status_pagamento = 'reembolsado',
        valor_reembolso = ?,
        data_cancelamento = NOW(),
        data_reembolso = NOW()
    WHERE id_reserva = ?
      AND id_usuario = ?
      AND status <> 'cancelada'");

if (!$stmt) responder(false, 'Erro ao preparar o cancelamento.', 500);
$stmt->bind_param('dii', $valorReembolso, $idReserva, $idUsuario);
$ok = $stmt->execute() && $stmt->affected_rows > 0;
$erro = $stmt->error;
$stmt->close();
$conexao->close();

if (!$ok) {
    error_log('TopTurismo cancelar-reserva: ' . $erro);
    responder(false, 'Não foi possível cancelar a reserva.', 500);
}

responder(true, 'Reserva cancelada com sucesso. Reembolso integral simulado realizado.', 200, [
    'id_reserva' => $idReserva,
    'valor_reembolso' => $valorReembolso,
    'status_pagamento' => 'reembolsado'
]);
