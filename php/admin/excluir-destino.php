<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../conexao.php';

$id = (int) ($_POST['id_destino'] ?? 0);
if ($id <= 0) {
    header('Location: ../../admin/dashboard.php?erro=Destino+inválido.');
    exit;
}

$stmt = $conexao->prepare('SELECT img_destino, img_destino_2, img_destino_3 FROM destinos WHERE id_destino = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$destino = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$destino) {
    header('Location: ../../admin/dashboard.php?erro=Destino+não+encontrado.');
    exit;
}

$stmt = $conexao->prepare('DELETE FROM destinos WHERE id_destino = ?');
$stmt->bind_param('i', $id);
if (!$stmt->execute()) {
    $mensagem = str_contains(strtolower($stmt->error), 'foreign key')
        ? 'Este destino possui reservas vinculadas e não pode ser excluído.'
        : 'Não foi possível excluir o destino.';
    $stmt->close();
    $conexao->close();
    header('Location: ../../admin/dashboard.php?erro=' . urlencode($mensagem));
    exit;
}
$stmt->close();
$conexao->close();

$arquivos = [$destino['img_destino'], $destino['img_destino_2'], $destino['img_destino_3']];
foreach ($arquivos as $arquivoRelativo) {
    if (!$arquivoRelativo) continue;
    $arquivo = dirname(__DIR__, 2) . '/' . ltrim($arquivoRelativo, '/');
    if (is_file($arquivo)) @unlink($arquivo);
}

$pasta = dirname(dirname(__DIR__) . '/' . ltrim($destino['img_destino'], '/'));
if (is_dir($pasta)) @rmdir($pasta);

header('Location: ../../admin/dashboard.php?sucesso=Destino+excluído+com+sucesso.');
exit;
