<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../conexao.php';

$id = (int) ($_POST['id_destino'] ?? 0);
if ($id <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/dashboard.php');
    exit;
}

function voltarErro(string $mensagem, int $id): never
{
    header('Location: ../../admin/editar-destino.php?id=' . $id . '&erro=' . urlencode($mensagem));
    exit;
}

$stmt = $conexao->prepare('SELECT * FROM destinos WHERE id_destino = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$atual = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$atual) voltarErro('Destino não encontrado.', $id);

$nome = trim($_POST['nome_destino'] ?? '');
$descricao = trim($_POST['descricao_destino'] ?? '');
$cidade = trim($_POST['cidade_destino'] ?? '');
$estado = trim($_POST['estado_destino'] ?? '');
$pais = trim($_POST['pais_destino'] ?? 'Brasil');
$regiao = trim($_POST['regiao_destino'] ?? '');
$preco = (float) str_replace(',', '.', trim($_POST['preco_destino'] ?? ''));
$avaliacao = (float) ($_POST['avaliacao_destino'] ?? 5);
$popularidade = (int) ($_POST['popularidade_destino'] ?? 1);

if ($nome === '' || $descricao === '' || $cidade === '' || $estado === '' || $pais === '' || $regiao === '' || $preco < 0 || $avaliacao < 0 || $avaliacao > 5 || $popularidade < 1 || $popularidade > 5) {
    voltarErro('Preencha os campos corretamente.', $id);
}

$permitidas = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$pastaRelativa = dirname($atual['img_destino']);
$pastaFisica = dirname(__DIR__, 2) . '/' . $pastaRelativa;
$caminhos = [$atual['img_destino'], $atual['img_destino_2'] ?? '', $atual['img_destino_3'] ?? ''];
$camposUpload = ['imagem_principal' => 0, 'imagem_2' => 1, 'imagem_3' => 2];

foreach ($camposUpload as $campo => $indice) {
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) continue;
    if ($_FILES[$campo]['error'] !== UPLOAD_ERR_OK || $_FILES[$campo]['size'] > 8 * 1024 * 1024) {
        voltarErro('Uma das imagens é inválida ou excede 8 MB.', $id);
    }
    $mime = $finfo->file($_FILES[$campo]['tmp_name']);
    if (!isset($permitidas[$mime])) voltarErro('Use somente imagens JPG, PNG ou WEBP.', $id);

    $nomeArquivo = 'foto-' . ($indice + 1) . '.' . $permitidas[$mime];
    if (!is_dir($pastaFisica)) mkdir($pastaFisica, 0755, true);
    $novoFisico = $pastaFisica . '/' . $nomeArquivo;
    if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $novoFisico)) voltarErro('Não foi possível salvar a imagem.', $id);

    $caminhos[$indice] = $pastaRelativa . '/' . $nomeArquivo;
}

$stmt = $conexao->prepare('UPDATE destinos SET nome_destino=?, descricao_destino=?, cidade_destino=?, estado_destino=?, pais_destino=?, regiao_destino=?, img_destino=?, img_destino_2=?, img_destino_3=?, preco_destino=?, avaliacao_destino=?, popularidade_destino=? WHERE id_destino=?');
$stmt->bind_param('sssssssssddii', $nome, $descricao, $cidade, $estado, $pais, $regiao, $caminhos[0], $caminhos[1], $caminhos[2], $preco, $avaliacao, $popularidade, $id);
if (!$stmt->execute()) voltarErro('Não foi possível salvar as alterações.', $id);
$stmt->close();
$conexao->close();
header('Location: ../../admin/dashboard.php?sucesso=Destino+atualizado+com+sucesso.');
exit;
