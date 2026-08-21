<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário não autenticado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

require __DIR__ . '/conexao.php';

$sql = "SELECT r.id_reserva, r.data_viagem, r.passageiros, r.transporte, r.assento, r.valor_total, r.status,
               d.nome_destino, d.cidade_destino, d.pais_destino, d.img_destino
        FROM reservas r
        INNER JOIN destinos d ON d.id_destino = r.id_destino
        WHERE r.usuario_id = ?
        ORDER BY r.data_viagem DESC, r.id_reserva DESC";

$stmt = $conexao->prepare($sql);
$stmt->bind_param('i', $_SESSION['usuario_id']);
$stmt->execute();
$resultado = $stmt->get_result();

$viagens = [];
while ($viagem = $resultado->fetch_assoc()) {
    $viagem['passageiros'] = (int) $viagem['passageiros'];
    $viagem['valor_total'] = (float) $viagem['valor_total'];
    $viagens[] = $viagem;
}

$stmt->close();
echo json_encode($viagens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
