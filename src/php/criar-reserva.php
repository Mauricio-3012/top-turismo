<?php
/*
 * TopTurismo - cria uma reserva.
 *
 * O navegador envia os dados em JSON e este arquivo responde sempre em JSON.
 * O banco continua sendo a fonte oficial dos destinos, preços e reservas.
 */

ob_start();
session_start();
header('Content-Type: application/json; charset=UTF-8');

$respostaEnviada = false;

function responder(int $status, bool $sucesso, string $mensagem, array $dados = []): never
{
    global $respostaEnviada, $conexao;
    $respostaEnviada = true;

    if (isset($conexao) && $conexao instanceof mysqli && $conexao->errno === 0) {
        // Se uma transação estiver aberta, desfaz alterações antes de responder.
        try {
            $conexao->rollback();
        } catch (Throwable $e) {
            // Não interrompe a resposta de erro ao usuário.
        }
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code($status);
    echo json_encode(
        array_merge([
            'sucesso' => $sucesso,
            'mensagem' => $mensagem
        ], $dados),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

// Impede avisos do PHP de misturarem HTML com o JSON enviado ao JavaScript.
set_error_handler(function (int $nivel, string $mensagem, string $arquivo, int $linha): bool {
    if (!(error_reporting() & $nivel)) {
        return false;
    }

    throw new ErrorException($mensagem, 0, $nivel, $arquivo, $linha);
});

set_exception_handler(function (Throwable $erro): void {
    error_log('TopTurismo criar-reserva: ' . $erro->getMessage());
    responder(500, false, 'Não foi possível concluir a reserva. Verifique a conexão com o banco e a estrutura da tabela reservas.');
});

// Se acontecer um erro fatal antes do nosso tratamento, ainda devolvemos JSON.
register_shutdown_function(function (): void {
    global $respostaEnviada;

    if ($respostaEnviada) {
        return;
    }

    $erro = error_get_last();
    if ($erro !== null) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'O servidor encontrou um erro ao processar a reserva. Verifique o PHP e o banco de dados.'
        ], JSON_UNESCAPED_UNICODE);
    }
});

$arquivoConexao = __DIR__ . '/conexao.php';
if (!file_exists($arquivoConexao)) {
    responder(500, false, 'Arquivo php/conexao.php não encontrado. Crie o arquivo de conexão com o seu MySQL.');
}

require_once $arquivoConexao;

if (!isset($conexao) || !($conexao instanceof mysqli)) {
    responder(500, false, 'A conexão com o banco de dados não foi configurada corretamente.');
}

if ($conexao->connect_errno) {
    responder(500, false, 'Não foi possível conectar ao banco de dados.');
}

// Não deixa o mysqli lançar mensagens HTML automaticamente.
mysqli_report(MYSQLI_REPORT_OFF);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, false, 'Método não permitido.');
}

if (!isset($_SESSION['usuario_id'])) {
    responder(401, false, 'Usuário não autenticado. Faça login novamente.');
}

$corpo = file_get_contents('php://input');
$dados = json_decode($corpo, true);

if (!is_array($dados)) {
    responder(400, false, 'Os dados enviados pela reserva são inválidos.');
}

$idUsuario = (int) $_SESSION['usuario_id'];
$idDestino = filter_var($dados['id_destino'] ?? null, FILTER_VALIDATE_INT);
$nomeDestino = trim((string) ($dados['nome_destino'] ?? ''));
$dataViagem = trim((string) ($dados['data_viagem'] ?? ''));
$dataVolta = trim((string) ($dados['data_volta'] ?? ''));
$tipoViagem = trim((string) ($dados['tipo_viagem'] ?? 'ida'));
$passageiros = filter_var($dados['quantidade_passageiros'] ?? null, FILTER_VALIDATE_INT);
$transporte = trim((string) ($dados['transporte'] ?? ''));
$classe = trim((string) ($dados['classe'] ?? ''));
$assentosTexto = trim((string) ($dados['assento'] ?? ''));
$pagamento = trim((string) ($dados['pagamento'] ?? ''));
$parcelas = filter_var($dados['parcelas'] ?? 1, FILTER_VALIDATE_INT);

if (!$idDestino && $nomeDestino === '') {
    responder(400, false, 'Selecione um destino.');
}

if (!$passageiros || $passageiros < 1 || $passageiros > 9) {
    responder(400, false, 'A quantidade de passageiros deve estar entre 1 e 9.');
}

if ($dataViagem === '') {
    responder(400, false, 'Selecione a data da viagem.');
}

if (!in_array($tipoViagem, ['ida', 'ida_volta'], true)) {
    responder(400, false, 'Tipo de viagem inválido.');
}

if (!in_array($transporte, ['Avião', 'Ônibus'], true)) {
    responder(400, false, 'Tipo de transporte inválido.');
}

if (!in_array($classe, ['Econômica', 'Executiva', 'VIP'], true)) {
    responder(400, false, 'Classe inválida.');
}

if ($transporte === 'Ônibus' && $classe !== 'Econômica') {
    responder(400, false, 'Ônibus disponível somente na classe Econômica.');
}

if (!in_array($pagamento, ['Pix', 'Cartão'], true)) {
    responder(400, false, 'Forma de pagamento inválida.');
}

if ($pagamento === 'Pix') {
    $parcelas = 1;
} elseif (!$parcelas || $parcelas < 1 || $parcelas > 12) {
    responder(400, false, 'Parcelamento inválido.');
}

// Datas
$data = DateTime::createFromFormat('!Y-m-d', $dataViagem);
if (!$data || $data->format('Y-m-d') !== $dataViagem) {
    responder(400, false, 'Data de viagem inválida.');
}

$hoje = new DateTime('today');
$dataMaxima = (clone $hoje)->modify('+9 months');

if ($data < $hoje) {
    responder(400, false, 'A data da viagem não pode estar no passado.');
}

if ($data > $dataMaxima) {
    responder(400, false, 'A data máxima para a viagem é de 9 meses a partir de hoje.');
}

if ($tipoViagem === 'ida_volta') {
    $volta = DateTime::createFromFormat('!Y-m-d', $dataVolta);

    if (!$volta || $volta->format('Y-m-d') !== $dataVolta) {
        responder(400, false, 'Data de volta inválida.');
    }

    if ($volta < $data) {
        responder(400, false, 'A data de volta deve ser igual ou posterior à data de ida.');
    }

    if ($volta > $dataMaxima) {
        responder(400, false, 'A data máxima para a volta é de 9 meses a partir de hoje.');
    }
} else {
    $dataVolta = null;
}

// Destino: primeiro tenta pelo ID. Se um link antigo enviar somente o nome,
// procura pelo nome para manter compatibilidade.
$destino = null;

if ($idDestino) {
    $stmt = $conexao->prepare('SELECT id_destino, nome_destino, preco_destino FROM destinos WHERE id_destino = ? LIMIT 1');
    if (!$stmt) {
        responder(500, false, 'Não foi possível consultar os destinos. Confira a tabela destinos.');
    }

    $stmt->bind_param('i', $idDestino);
    if (!$stmt->execute()) {
        $stmt->close();
        responder(500, false, 'Não foi possível consultar o destino.');
    }

    $stmt->bind_result($idBanco, $nomeBanco, $precoBanco);
    if ($stmt->fetch()) {
        $destino = [
            'id_destino' => (int) $idBanco,
            'nome_destino' => $nomeBanco,
            'preco_destino' => (float) $precoBanco
        ];
    }
    $stmt->close();
}

if (!$destino && $nomeDestino !== '') {
    $stmt = $conexao->prepare('SELECT id_destino, nome_destino, preco_destino FROM destinos WHERE nome_destino = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $nomeDestino);
        if ($stmt->execute()) {
            $stmt->bind_result($idBanco, $nomeBanco, $precoBanco);
            if ($stmt->fetch()) {
                $destino = [
                    'id_destino' => (int) $idBanco,
                    'nome_destino' => $nomeBanco,
                    'preco_destino' => (float) $precoBanco
                ];
            }
        }
        $stmt->close();
    }
}

if (!$destino) {
    responder(404, false, 'Destino não encontrado no banco de dados. Importe o SQL base do TopTurismo e tente novamente.');
}

$idDestino = $destino['id_destino'];
$precoBase = $destino['preco_destino'];

// Assentos
$assentos = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', $assentosTexto))));

if (count($assentos) !== $passageiros || count(array_unique(array_map('strtoupper', $assentos))) !== count($assentos)) {
    responder(400, false, 'A quantidade de assentos deve ser igual à quantidade de passageiros, sem repetição.');
}

$assentosValidos = [];
$tiposAssento = [];

foreach ($assentos as $assento) {
    $assento = strtoupper($assento);

    if (!preg_match('/^(\d{1,2})([A-D])$/', $assento, $partes)) {
        responder(400, false, 'Um dos assentos selecionados é inválido.');
    }

    $numero = (int) $partes[1];
    $letra = $partes[2];

    if ($transporte === 'Ônibus') {
        $valido = $numero >= 1 && $numero <= 12;
    } elseif ($classe === 'VIP') {
        $valido = $numero >= 1 && $numero <= 2;
    } elseif ($classe === 'Executiva') {
        $valido = $numero >= 3 && $numero <= 6;
    } else {
        $valido = $numero >= 7 && $numero <= 20;
    }

    if (!$valido) {
        responder(400, false, 'Um ou mais assentos não pertencem à classe escolhida.');
    }

    $assentoFinal = $numero . $letra;
    $assentosValidos[] = $assentoFinal;
    $tiposAssento[] = in_array($letra, ['A', 'D'], true) ? 'Janela' : 'Corredor';
}

$assentoBanco = implode(', ', $assentosValidos);
$tipoAssento = implode(', ', array_values(array_unique($tiposAssento)));

// *busca a programação centralizada; destinos novos recebem horário padrão*
require_once __DIR__ . '/programacao-dados.php';
$programacao = programacaoPorId($idDestino, $transporte);

if (!$programacao) {
    responder(400, false, 'A programação desta viagem não está disponível.');
}

// Reserva uma linha do destino durante a verificação dos assentos.
// Isso evita que duas requisições simultâneas reservem o mesmo assento
// entre a consulta de disponibilidade e o INSERT.
if (!$conexao->begin_transaction()) {
    responder(500, false, 'Não foi possível iniciar a transação da reserva.');
}

$stmtBloqueio = $conexao->prepare('SELECT id_destino FROM destinos WHERE id_destino = ? LIMIT 1 FOR UPDATE');
if (!$stmtBloqueio) {
    responder(500, false, 'Não foi possível bloquear o destino para a reserva.');
}
$stmtBloqueio->bind_param('i', $idDestino);
if (!$stmtBloqueio->execute()) {
    $stmtBloqueio->close();
    responder(500, false, 'Não foi possível validar a disponibilidade do destino.');
}
$stmtBloqueio->close();

// Confere os assentos novamente no servidor.
$stmt = $conexao->prepare(
    "SELECT assento FROM reservas
     WHERE id_destino = ?
       AND data_viagem = ?
       AND transporte = ?
       AND classe = ?
       AND LOWER(status) <> 'cancelada'"
);

if (!$stmt) {
    responder(500, false, 'Não foi possível verificar os assentos reservados.');
}

$stmt->bind_param('isss', $idDestino, $dataViagem, $transporte, $classe);
if (!$stmt->execute()) {
    $stmt->close();
    responder(500, false, 'Não foi possível verificar os assentos reservados.');
}

$stmt->bind_result($assentosOcupadosTexto);
$ocupados = [];
while ($stmt->fetch()) {
    foreach (preg_split('/\s*,\s*/', (string) $assentosOcupadosTexto) as $ocupado) {
        if ($ocupado !== '') {
            $ocupados[] = strtoupper(trim($ocupado));
        }
    }
}
$stmt->close();

if (array_intersect($assentosValidos, $ocupados)) {
    responder(409, false, 'Um dos assentos selecionados acabou de ser ocupado. Volte e escolha outro.');
}

// Cálculo oficial no servidor.
$subtotal = $precoBase * $passageiros;
if ($tipoViagem === 'ida_volta') {
    $subtotal *= 2;
}
if ($transporte === 'Ônibus') {
    $subtotal *= 0.70;
}
if ($classe === 'VIP') {
    $subtotal += 150 * $passageiros;
}
if ($classe === 'Executiva') {
    $subtotal += 300 * $passageiros;
}

$descontoEconomica = ($transporte === 'Avião' && $classe === 'Econômica') ? 0.08 : 0;
$descontoGrupo = min(floor($passageiros / 2) * 0.03, 0.12);
$descontoComercial = $subtotal * ($descontoEconomica + $descontoGrupo);
$totalAntesPagamento = $subtotal - $descontoComercial;

$taxaJuros = ($pagamento === 'Cartão' && $parcelas > 1) ? ($parcelas - 1) * 1.5 : 0;
$descontoPix = $pagamento === 'Pix' ? 0.05 : 0;
$valorTotal = round($totalAntesPagamento * (1 + $taxaJuros / 100) * (1 - $descontoPix), 2);

$status = 'confirmada';
$horarioIda = $programacao['saida'];
$horarioVolta = $tipoViagem === 'ida_volta' ? $programacao['volta'] : null;
$duracao = (int) $programacao['duracao'];

$sql = "INSERT INTO reservas
    (id_usuario, id_destino, data_viagem, data_volta, tipo_viagem,
     quantidade_passageiros, transporte, classe, assento, tipo_assento,
     pagamento, parcelas, taxa_juros_percentual, horario_ida, horario_volta,
     duracao_voo_minutos, valor_total, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexao->prepare($sql);
if (!$stmt) {
    responder(500, false, 'Não foi possível preparar o salvamento da reserva. Confira se a tabela reservas está atualizada.');
}

$stmt->bind_param(
    'iisssisssssidssids',
    $idUsuario,
    $idDestino,
    $dataViagem,
    $dataVolta,
    $tipoViagem,
    $passageiros,
    $transporte,
    $classe,
    $assentoBanco,
    $tipoAssento,
    $pagamento,
    $parcelas,
    $taxaJuros,
    $horarioIda,
    $horarioVolta,
    $duracao,
    $valorTotal,
    $status
);

if (!$stmt->execute()) {
    $erroBanco = $stmt->error;
    $stmt->close();

    error_log('TopTurismo criar-reserva MySQL: ' . $erroBanco);

    if (stripos($erroBanco, 'foreign key') !== false) {
        responder(409, false, 'A reserva não pôde ser salva porque o usuário ou o destino não existe no banco.');
    }

    if (stripos($erroBanco, 'duplicate') !== false || stripos($erroBanco, 'unique') !== false) {
        responder(409, false, 'Não foi possível reservar os assentos selecionados. Eles podem ter acabado de ser ocupados.');
    }

    responder(500, false, 'Não foi possível salvar a reserva. Confira se o SQL base do projeto foi importado corretamente.');
}

$idReserva = $stmt->insert_id;
$stmt->close();

if (!$conexao->commit()) {
    responder(500, false, 'A reserva foi processada, mas não foi possível confirmar a transação. Tente novamente.');
}

$conexao->close();

responder(201, true, 'Reserva confirmada com sucesso!', [
    'id_reserva' => $idReserva,
    'valor_total' => $valorTotal,
    'horario_ida' => $horarioIda,
    'horario_volta' => $horarioVolta,
    'duracao_voo_minutos' => $duracao,
    'taxa_juros_percentual' => $taxaJuros
]);
