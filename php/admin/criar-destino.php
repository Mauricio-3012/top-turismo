<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/adicionar-destino.php');
    exit;
}

function voltarComErro(string $mensagem): never
{
    header('Location: ../../admin/adicionar-destino.php?erro=' . urlencode($mensagem));
    exit;
}

$nome = trim($_POST['nome_destino'] ?? '');
$descricao = trim($_POST['descricao_destino'] ?? '');
$cidade = trim($_POST['cidade_destino'] ?? '');
$estado = trim($_POST['estado_destino'] ?? '');
$pais = trim($_POST['pais_destino'] ?? 'Brasil');
$regiao = trim($_POST['regiao_destino'] ?? '');
$preco = str_replace(',', '.', trim($_POST['preco_destino'] ?? ''));
$avaliacao = trim($_POST['avaliacao_destino'] ?? '5');
$popularidade = (int) ($_POST['popularidade_destino'] ?? 1);

if ($nome === '' || $descricao === '' || $cidade === '' || $estado === '' || $pais === '' || $regiao === '' || !is_numeric($preco)) {
    voltarComErro('Preencha todos os campos obrigatórios corretamente.');
}

$precoFloat = (float) $preco;
$avaliacaoFloat = (float) $avaliacao;
if ($precoFloat < 0 || $avaliacaoFloat < 0 || $avaliacaoFloat > 5 || $popularidade < 1 || $popularidade > 5) {
    voltarComErro('Preço, avaliação ou popularidade possuem valores inválidos.');
}

$arquivos = ['imagem_principal', 'imagem_2', 'imagem_3'];
foreach ($arquivos as $campo) {
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
        voltarComErro('Envie as três imagens do destino.');
    }
}

$permitidas = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

$finfo = new finfo(FILEINFO_MIME_TYPE);
$extensoes = [];
foreach ($arquivos as $campo) {
    $mime = $finfo->file($_FILES[$campo]['tmp_name']);
    if (!isset($permitidas[$mime])) {
        voltarComErro('Use somente imagens JPG, PNG ou WEBP.');
    }
    if ($_FILES[$campo]['size'] > 8 * 1024 * 1024) {
        voltarComErro('Cada imagem deve ter no máximo 8 MB.');
    }
    $extensoes[$campo] = $permitidas[$mime];
}

$slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome);
$slug = strtolower($slug ?: $nome);
$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
$slug = trim($slug, '-');
$slug .= '-' . bin2hex(random_bytes(3));

$pastaRelativa = 'assets/imagens/destinos/' . $slug;
$pastaFisica = dirname(__DIR__, 2) . '/' . $pastaRelativa;
if (!mkdir($pastaFisica, 0755, true) && !is_dir($pastaFisica)) {
    voltarComErro('Não foi possível criar a pasta das imagens.');
}

$caminhos = [];
try {
    foreach ($arquivos as $indice => $campo) {
        $numero = $indice + 1;
        $nomeArquivo = 'foto-' . $numero . '.' . $extensoes[$campo];
        $destinoFisico = $pastaFisica . '/' . $nomeArquivo;
        if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $destinoFisico)) {
            throw new RuntimeException('Falha ao salvar imagem.');
        }
        $caminhos[] = $pastaRelativa . '/' . $nomeArquivo;
    }

    $stmt = $conexao->prepare(
        'INSERT INTO destinos (nome_destino, descricao_destino, cidade_destino, estado_destino, pais_destino, regiao_destino, img_destino, img_destino_2, img_destino_3, preco_destino, avaliacao_destino, popularidade_destino) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    if (!$stmt) {
        throw new RuntimeException('Não foi possível preparar o cadastro.');
    }

    $stmt->bind_param(
        'ssssssssssdi',
        $nome,
        $descricao,
        $cidade,
        $estado,
        $pais,
        $regiao,
        $caminhos[0],
        $caminhos[1],
        $caminhos[2],
        $precoFloat,
        $avaliacaoFloat,
        $popularidade
    );

    if (!$stmt->execute()) {
        throw new RuntimeException('Não foi possível cadastrar o destino.');
    }

    $stmt->close();
    $conexao->close();
    header('Location: ../../admin/dashboard.php?sucesso=Destino+adicionado+com+sucesso.');
    exit;
} catch (Throwable $e) {
    foreach ($caminhos as $caminho) {
        $arquivo = dirname(__DIR__, 2) . '/' . $caminho;
        if (is_file($arquivo)) {
            @unlink($arquivo);
        }
    }
    @rmdir($pastaFisica);
    $conexao->close();
    voltarComErro($e->getMessage());
}
