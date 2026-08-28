<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once "conexao.php";

// O endpoint sempre responde JSON, inclusive quando o MySQL/PHP gerar uma exceção.
// Isso evita o erro genérico "Unexpected token <" no navegador.
set_exception_handler(function (Throwable $e): void {
    error_log("TopTurismo criar-reserva: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não foi possível processar o pagamento/reserva. Verifique se o banco possui todas as colunas da versão atual."
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

function responder(int $statusHttp, bool $sucesso, string $mensagem, array $extra = []): never {
    http_response_code($statusHttp);
    echo json_encode(array_merge(["sucesso" => $sucesso, "mensagem" => $mensagem], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") responder(405, false, "Método não permitido.");
if (!isset($_SESSION["usuario_id"])) responder(401, false, "Usuário não autenticado.");

$dados = json_decode(file_get_contents("php://input"), true);
if (!is_array($dados)) responder(400, false, "Dados da reserva inválidos.");

$idUsuario = (int)$_SESSION["usuario_id"];

// Verifica a estrutura antes do INSERT para retornar uma mensagem JSON clara
// caso o banco local ainda não tenha recebido a atualização das reservas.
$colunasNecessarias = [
    "data_volta", "tipo_viagem", "classe", "tipo_assento", "pagamento",
    "parcelas", "taxa_juros_percentual", "horario_ida", "horario_volta",
    "duracao_voo_minutos"
];
$colunasEncontradas = [];
$consultaColunas = $conexao->query("SHOW COLUMNS FROM reservas");
if ($consultaColunas) {
    while ($coluna = $consultaColunas->fetch_assoc()) $colunasEncontradas[$coluna["Field"]] = true;
    $consultaColunas->free();
}
$colunasFaltantes = array_values(array_filter($colunasNecessarias, fn($coluna) => empty($colunasEncontradas[$coluna])));
if ($colunasFaltantes) {
    responder(500, false, "O banco de dados ainda não foi atualizado. Execute database/atualizacao-completa-reservas.sql no phpMyAdmin.", ["colunas_faltantes" => $colunasFaltantes]);
}

$idDestino = filter_var($dados["id_destino"] ?? null, FILTER_VALIDATE_INT);
$dataViagem = trim((string)($dados["data_viagem"] ?? ""));
$dataVolta = trim((string)($dados["data_volta"] ?? ""));
$tipoViagem = trim((string)($dados["tipo_viagem"] ?? "ida"));
$passageiros = filter_var($dados["quantidade_passageiros"] ?? null, FILTER_VALIDATE_INT);
$transporte = trim((string)($dados["transporte"] ?? ""));
$classe = trim((string)($dados["classe"] ?? ""));
$assentos = array_values(array_filter(array_map("trim", preg_split('/\s*,\s*/', (string)($dados["assento"] ?? "")))));
$tipoAssento = trim((string)($dados["tipo_assento"] ?? ""));
$pagamento = trim((string)($dados["pagamento"] ?? ""));
$parcelas = filter_var($dados["parcelas"] ?? 1, FILTER_VALIDATE_INT);

if (!$idDestino || !$passageiros || !$dataViagem || !$transporte || !$classe || !$assentos || !$pagamento) responder(400, false, "Preencha todos os campos obrigatórios.");
if ($passageiros < 1 || $passageiros > 9) responder(400, false, "A quantidade de passageiros deve estar entre 1 e 9.");
if (count($assentos) !== $passageiros || count(array_unique($assentos)) !== count($assentos)) responder(400, false, "A quantidade de assentos deve ser igual à quantidade de passageiros, sem repetição.");
if (!in_array($transporte, ["Avião", "Ônibus"], true)) responder(400, false, "Tipo de transporte inválido.");
if (!in_array($classe, ["Econômica", "Executiva", "VIP"], true)) responder(400, false, "Classe inválida.");
if ($transporte === "Ônibus" && $classe !== "Econômica") responder(400, false, "Ônibus disponível somente na classe Econômica.");
if (!in_array($pagamento, ["Pix", "Cartão"], true)) responder(400, false, "Forma de pagamento inválida.");
if ($pagamento === "Pix") $parcelas = 1;
if ($pagamento === "Cartão" && (!$parcelas || $parcelas < 1 || $parcelas > 12)) responder(400, false, "Parcelamento inválido.");

$data = DateTime::createFromFormat("Y-m-d", $dataViagem);
if (!$data || $data->format("Y-m-d") !== $dataViagem) responder(400, false, "Data de viagem inválida.");
$hoje = new DateTime("today");
$dataMaxima = (clone $hoje)->modify("+9 months");
if ($data < $hoje) responder(400, false, "A data da viagem não pode estar no passado.");
if ($data > $dataMaxima) responder(400, false, "A data máxima para a viagem é de 9 meses a partir de hoje.");

if ($tipoViagem === "ida_volta") {
    $volta = DateTime::createFromFormat("Y-m-d", $dataVolta);
    if (!$volta || $volta->format("Y-m-d") !== $dataVolta) responder(400, false, "Data de volta inválida.");
    if ($volta < $data) responder(400, false, "A data de volta deve ser posterior à data de ida.");
    if ($volta > $dataMaxima) responder(400, false, "A data máxima para a volta é de 9 meses a partir de hoje.");
} elseif ($tipoViagem === "ida") {
    $dataVolta = null;
} else responder(400, false, "Tipo de viagem inválido.");

// Programação oficial da simulação. O horário enviado pelo navegador é ignorado.
$programacaoAviao = [
    1=>["saida"=>"08:30","volta"=>"18:00","duracao"=>170], 2=>["saida"=>"09:15","volta"=>"19:10","duracao"=>100],
    3=>["saida"=>"07:45","volta"=>"18:20","duracao"=>140], 4=>["saida"=>"10:00","volta"=>"20:00","duracao"=>130],
    5=>["saida"=>"11:20","volta"=>"21:10","duracao"=>100], 6=>["saida"=>"08:20","volta"=>"17:50","duracao"=>120],
    7=>["saida"=>"06:50","volta"=>"18:30","duracao"=>210], 8=>["saida"=>"09:40","volta"=>"19:40","duracao"=>220],
    9=>["saida"=>"08:50","volta"=>"18:40","duracao"=>120], 10=>["saida"=>"10:30","volta"=>"20:30","duracao"=>105],
    11=>["saida"=>"07:10","volta"=>"17:00","duracao"=>200], 12=>["saida"=>"09:00","volta"=>"18:00","duracao"=>120],
    13=>["saida"=>"08:10","volta"=>"19:00","duracao"=>200], 14=>["saida"=>"10:50","volta"=>"20:50","duracao"=>95],
    15=>["saida"=>"06:40","volta"=>"17:40","duracao"=>210], 16=>["saida"=>"09:30","volta"=>"18:50","duracao"=>140]
];
$programacaoOnibus = [
    1=>["saida"=>"06:30","volta"=>"18:30","duracao"=>570], 2=>["saida"=>"07:00","volta"=>"20:00","duracao"=>360],
    3=>["saida"=>"06:00","volta"=>"19:00","duracao"=>480], 4=>["saida"=>"07:30","volta"=>"19:30","duracao"=>720],
    5=>["saida"=>"08:00","volta"=>"20:00","duracao"=>300], 6=>["saida"=>"06:20","volta"=>"18:20","duracao"=>840],
    7=>["saida"=>"05:30","volta"=>"17:30","duracao"=>1080], 8=>["saida"=>"05:00","volta"=>"17:00","duracao"=>1080],
    9=>["saida"=>"07:10","volta"=>"19:10","duracao"=>600], 10=>["saida"=>"08:20","volta"=>"20:20","duracao"=>360],
    11=>["saida"=>"06:00","volta"=>"18:00","duracao"=>900], 12=>["saida"=>"07:40","volta"=>"19:40","duracao"=>480],
    13=>["saida"=>"06:40","volta"=>"18:40","duracao"=>900], 14=>["saida"=>"08:30","volta"=>"20:30","duracao"=>300],
    15=>["saida"=>"05:50","volta"=>"17:50","duracao"=>960], 16=>["saida"=>"06:50","volta"=>"18:50","duracao"=>780]
];
$programacao = $transporte === "Avião" ? ($programacaoAviao[$idDestino] ?? null) : ($programacaoOnibus[$idDestino] ?? null);
if (!$programacao) responder(404, false, "Programação indisponível para este destino.");
$horarioIda = $programacao["saida"];
$horarioVolta = $tipoViagem === "ida_volta" ? $programacao["volta"] : null;
$duracao = (int)$programacao["duracao"];

// A classe define a faixa de assentos. A coluna tipo_assento é derivada do layout.
$validos = [];
foreach ($assentos as $assento) {
    if (!preg_match('/^(\d{1,2})([A-D])$/', strtoupper($assento), $m)) responder(400, false, "Assento inválido.");
    $numero = (int)$m[1];
    $letra = $m[2];
    $limite = $transporte === "Ônibus" ? ($numero >= 1 && $numero <= 12) :
        ($classe === "VIP" ? ($numero >= 1 && $numero <= 2) : ($classe === "Executiva" ? ($numero >= 3 && $numero <= 6) : ($numero >= 7 && $numero <= 20)));
    if (!$limite) responder(400, false, "Um ou mais assentos não pertencem à classe escolhida.");
    $validos[] = strtoupper($numero . $letra);
}
$assentos = $validos;
$tipos = [];
foreach ($assentos as $assento) $tipos[] = in_array(substr($assento, -1), ["A","D"], true) ? "Janela" : "Corredor";
$tipoAssento = implode(", ", array_values(array_unique($tipos)));

// Confere novamente no servidor se algum assento já foi reservado para o mesmo trecho/data.
$stmtOcupados = $conexao->prepare("SELECT assento FROM reservas WHERE id_destino = ? AND data_viagem = ? AND transporte = ? AND classe = ? AND LOWER(status) <> 'cancelada'");
$stmtOcupados->bind_param("isss", $idDestino, $dataViagem, $transporte, $classe);
$stmtOcupados->execute();
$resOcupados = $stmtOcupados->get_result();
$ocupados = [];
while ($row = $resOcupados->fetch_assoc()) foreach (preg_split('/\s*,\s*/', (string)$row["assento"]) as $seat) if ($seat !== "") $ocupados[] = strtoupper($seat);
$stmtOcupados->close();
if (array_intersect($assentos, $ocupados)) responder(409, false, "Um dos assentos selecionados acabou de ser ocupado. Volte e escolha outro.");

$stmtDestino = $conexao->prepare("SELECT preco_destino FROM destinos WHERE id_destino = ? LIMIT 1");
$stmtDestino->bind_param("i", $idDestino);
$stmtDestino->execute();
$destino = $stmtDestino->get_result()->fetch_assoc();
$stmtDestino->close();
if (!$destino) responder(404, false, "Destino não encontrado.");

$precoBase = (float)$destino["preco_destino"];
$subtotal = $precoBase * $passageiros;
if ($tipoViagem === "ida_volta") $subtotal *= 2;
if ($transporte === "Ônibus") $subtotal *= 0.70;
if ($classe === "VIP") $subtotal += 150 * $passageiros;
if ($classe === "Executiva") $subtotal += 300 * $passageiros;
$descontoEconomica = ($transporte === "Avião" && $classe === "Econômica") ? 0.08 : 0;
$descontoGrupo = min(floor($passageiros / 2) * 0.03, 0.12);
$descontoComercial = $subtotal * ($descontoEconomica + $descontoGrupo);
$totalAntesPagamento = $subtotal - $descontoComercial;
$taxaJuros = $pagamento === "Cartão" && $parcelas > 1 ? ($parcelas - 1) * 1.5 : 0.0;
$descontoPix = $pagamento === "Pix" ? 0.05 : 0.0;
$valorTotal = $totalAntesPagamento * (1 + $taxaJuros / 100) * (1 - $descontoPix);
$valorTotal = round($valorTotal, 2);
$status = "confirmada";

$sql = "INSERT INTO reservas (id_usuario,id_destino,data_viagem,data_volta,tipo_viagem,quantidade_passageiros,transporte,classe,assento,tipo_assento,pagamento,parcelas,taxa_juros_percentual,horario_ida,horario_volta,duracao_voo_minutos,valor_total,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
$stmt = $conexao->prepare($sql);
if (!$stmt) responder(500, false, "Erro ao preparar a reserva.");
$stmt->bind_param("iisssisssssidssids", $idUsuario,$idDestino,$dataViagem,$dataVolta,$tipoViagem,$passageiros,$transporte,$classe,implode(", ",$assentos),$tipoAssento,$pagamento,$parcelas,$taxaJuros,$horarioIda,$horarioVolta,$duracao,$valorTotal,$status);
if (!$stmt->execute()) {
    $erro = $stmt->error;
    $stmt->close(); $conexao->close();
    if (str_contains(strtolower($erro), "duplicate") || str_contains(strtolower($erro), "unique")) responder(409, false, "Não foi possível reservar os assentos selecionados.");
    responder(500, false, "Erro ao salvar a reserva.");
}
$idReserva = $stmt->insert_id;
$stmt->close(); $conexao->close();

responder(201, true, "Reserva confirmada com sucesso!", [
    "id_reserva" => $idReserva,
    "valor_total" => $valorTotal,
    "horario_ida" => $horarioIda,
    "horario_volta" => $horarioVolta,
    "duracao_voo_minutos" => $duracao,
    "taxa_juros_percentual" => $taxaJuros
]);
