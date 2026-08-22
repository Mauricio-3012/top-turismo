<?php

session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once "conexao.php";


 // mantém as respostas do endpoint de cancelamento consistentes
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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responder(405, false, "Método não permitido.");
}

if (!isset($_SESSION["usuario_id"])) {
    responder(401, false, "Usuário não autenticado.");
}

$dados = json_decode(file_get_contents("php://input"), true);
$id_reserva = filter_var(
    $dados["id_reserva"] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id_reserva || $id_reserva < 1) {
    responder(400, false, "Reserva inválida.");
}

$id_usuario = (int) $_SESSION["usuario_id"];


 // a reserva é localizada pelo ID + ID do usuário, assim, um usuário não consegue cancelar uma reserva pertencente a outra conta apenas alterando o ID enviado pelo navegador
 
$sql = "
    SELECT id_reserva, data_viagem, status
    FROM reservas
    WHERE id_reserva = ? AND id_usuario = ?
    LIMIT 1
";

$stmt = $conexao->prepare($sql);

if (!$stmt) {
    responder(500, false, "Erro ao consultar a reserva.");
}

$stmt->bind_param("ii", $id_reserva, $id_usuario);
$stmt->execute();

$reserva = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reserva) {
    responder(404, false, "Reserva não encontrada.");
}

$statusAtual = strtolower(trim((string) $reserva["status"]));

if (
    in_array(
        $statusAtual,
        ["cancelada", "concluida", "concluída"],
        true
    )
) {
    responder(409, false, "Esta reserva não pode mais ser cancelada.");
}

$dataViagem = DateTime::createFromFormat(
    "Y-m-d",
    (string) $reserva["data_viagem"]
);

if (!$dataViagem) {
    responder(500, false, "A data da reserva é inválida.");
}

if ($dataViagem < new DateTime("today")) {
    responder(409, false, "Não é possível cancelar uma viagem que já passou.");
}

// a mesma condição do SELECT é repetida no UPDATE para evitar que uma reserva seja alterada depois de mudar de status
$sqlUpdate = "
    UPDATE reservas
    SET status = 'cancelada'
    WHERE id_reserva = ?
      AND id_usuario = ?
      AND status NOT IN ('cancelada', 'concluida', 'concluída')
";

$stmtUpdate = $conexao->prepare($sqlUpdate);

if (!$stmtUpdate) {
    responder(500, false, "Erro ao preparar o cancelamento.");
}

$stmtUpdate->bind_param("ii", $id_reserva, $id_usuario);
$atualizou = $stmtUpdate->execute();

$stmtUpdate->close();
$conexao->close();

if (!$atualizou) {
    responder(500, false, "Não foi possível cancelar a reserva.");
}

responder(200, true, "Reserva cancelada com sucesso.", [
    "id_reserva" => $id_reserva,
    "status" => "cancelada",
]);