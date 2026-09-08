<?php
// *somente administradores podem alterar o status de pagamento das reservas*
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/admin/dashboard.php?erro=' . urlencode('Ação inválida.'));
    exit;
}

$idReserva = filter_input(INPUT_POST, 'id_reserva', FILTER_VALIDATE_INT);
$statusPagamento = strtolower(trim($_POST['status_pagamento'] ?? ''));
$permitidos = ['pendente', 'pago', 'reembolsado'];

if (!$idReserva || !in_array($statusPagamento, $permitidos, true)) {
    $conexao->close();
    header('Location: ../../pages/admin/dashboard.php?erro=' . urlencode('Dados de pagamento inválidos.'));
    exit;
}

$stmt = $conexao->prepare('SELECT id_reserva, valor_total, status FROM reservas WHERE id_reserva = ? LIMIT 1');
$stmt->bind_param('i', $idReserva);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reserva) {
    $conexao->close();
    header('Location: ../../pages/admin/dashboard.php?erro=' . urlencode('Reserva não encontrada.'));
    exit;
}

if ($statusPagamento === 'reembolsado' && strtolower((string)$reserva['status']) !== 'cancelada') {
    $conexao->close();
    header('Location: ../../pages/admin/dashboard.php?erro=' . urlencode('Para marcar uma reserva como reembolsada, ela precisa estar cancelada.'));
    exit;
}

if ($statusPagamento === 'reembolsado') {
    $sql = 'UPDATE reservas SET status_pagamento = ?, valor_reembolso = valor_total, data_reembolso = COALESCE(data_reembolso, NOW()) WHERE id_reserva = ?';
} elseif ($statusPagamento === 'pago') {
    $sql = 'UPDATE reservas SET status_pagamento = ?, valor_reembolso = NULL, data_reembolso = NULL WHERE id_reserva = ?';
} else {
    $sql = 'UPDATE reservas SET status_pagamento = ?, valor_reembolso = NULL, data_reembolso = NULL WHERE id_reserva = ?';
}

$stmt = $conexao->prepare($sql);
$stmt->bind_param('si', $statusPagamento, $idReserva);
$ok = $stmt->execute();
$stmt->close();
$conexao->close();

if ($ok) {
    $mensagem = match ($statusPagamento) {
        'pago' => 'Pagamento marcado como pago.',
        'reembolsado' => 'Pagamento marcado como reembolsado.',
        default => 'Pagamento marcado como pendente.'
    };
    header('Location: ../../pages/admin/dashboard.php?sucesso=' . urlencode($mensagem) . '#reservas');
    exit;
}

header('Location: ../../pages/admin/dashboard.php?erro=' . urlencode('Não foi possível atualizar o pagamento.') . '#reservas');
exit;
