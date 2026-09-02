<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

// *retorna somente as reservas do usuário logado*

require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    http_response_code(401);

    echo json_encode(
        [
            "sucesso" => false,
            "mensagem" => "Usuário não autenticado.",
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$id_usuario = (int) $_SESSION["usuario_id"];

$sql = "
    SELECT
        r.id_reserva,
        r.id_destino,
        r.data_viagem,
        r.data_volta,
        r.tipo_viagem,
        r.quantidade_passageiros,
        r.transporte,
        r.classe,
        r.assento,
        r.tipo_assento,
        r.pagamento,
        r.parcelas,
        r.taxa_juros_percentual,
        r.horario_ida,
        r.horario_volta,
        r.duracao_voo_minutos,
        r.valor_total,
        CASE WHEN LOWER(r.status) = 'cancelada' THEN 'cancelada' ELSE 'confirmada' END AS status,
        d.nome_destino,
        d.cidade_destino,
        d.pais_destino,
        d.img_destino
    FROM reservas r
    INNER JOIN destinos d ON d.id_destino = r.id_destino
    WHERE r.id_usuario = ?
    ORDER BY r.data_viagem DESC, r.id_reserva DESC
";

$stmt = $conexao->prepare($sql);

if (!$stmt) {
    http_response_code(500);

    echo json_encode(
        [
            "sucesso" => false,
            "mensagem" => "Erro ao consultar as reservas.",
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$stmt->bind_param("i", $id_usuario);
$stmt->execute();

$resultado = $stmt->get_result();
$reservas = [];

while ($reserva = $resultado->fetch_assoc()) {
    $reservas[] = $reserva;
}

$stmt->close();
$conexao->close();

echo json_encode(
    [
        "sucesso" => true,
        "reservas" => $reservas,
    ],
    JSON_UNESCAPED_UNICODE
);
