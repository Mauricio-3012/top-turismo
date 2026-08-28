<?php
require_once __DIR__ . '/../php/admin/auth.php';
require_once __DIR__ . '/../php/conexao.php';

$resultado = $conexao->query('SELECT id_destino, nome_destino, cidade_destino, estado_destino, preco_destino, avaliacao_destino, img_destino FROM destinos ORDER BY id_destino DESC');
$destinos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
$total = count($destinos);
$conexao->close();
$sucesso = trim($_GET['sucesso'] ?? '');
$erro = trim($_GET['erro'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administração - TopTurismo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<?php require __DIR__ . '/_navbar.php'; ?>
<main class="admin-page"><div class="container">
<div class="admin-header-card mb-4"><div class="d-flex justify-content-between align-items-center gap-3 flex-wrap"><div><span class="text-uppercase small fw-bold">Painel administrativo</span><h1 class="fw-bold mb-1">Gerenciar destinos</h1><p class="mb-0 text-muted">Adicione, edite ou remova destinos que aparecem automaticamente no site.</p></div><a href="adicionar-destino.php" class="btn btn-custom btn-lg"><i class="bi bi-plus-circle me-2"></i>Novo destino</a></div></div>
<?php if ($sucesso): ?><div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<div class="row g-4 mb-4"><div class="col-md-4"><div class="admin-card"><div class="admin-stat"><div class="admin-stat-icon"><i class="bi bi-geo-alt"></i></div><div><div class="text-muted small">Destinos cadastrados</div><strong class="fs-3"><?= $total ?></strong></div></div></div></div><div class="col-md-4"><div class="admin-card"><div class="admin-stat"><div class="admin-stat-icon"><i class="bi bi-person-badge"></i></div><div><div class="text-muted small">Acesso</div><strong>Administrador</strong></div></div></div></div><div class="col-md-4"><div class="admin-card"><div class="admin-stat"><div class="admin-stat-icon"><i class="bi bi-images"></i></div><div><div class="text-muted small">Fotos por destino</div><strong>3 imagens</strong></div></div></div></div></div>
<div class="admin-card"><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0">Destinos atuais</h2><span class="badge text-bg-secondary"><?= $total ?> cadastrados</span></div>
<div class="table-responsive"><table class="table admin-table align-middle"><thead><tr><th>Imagem</th><th>Destino</th><th>Local</th><th>Preço</th><th>Avaliação</th><th class="text-end">Ações</th></tr></thead><tbody>
<?php foreach ($destinos as $destino): ?><tr><td data-label="Imagem"><img src="../<?= htmlspecialchars($destino['img_destino']) ?>" alt="<?= htmlspecialchars($destino['nome_destino']) ?>"></td><td data-label="Destino"><strong><?= htmlspecialchars($destino['nome_destino']) ?></strong></td><td data-label="Local"><?= htmlspecialchars($destino['cidade_destino'] . ' - ' . ($destino['estado_destino'] ?? '')) ?></td><td data-label="Preço">R$ <?= number_format((float)$destino['preco_destino'],2,',','.') ?></td><td data-label="Avaliação"><i class="bi bi-star-fill text-warning"></i> <?= number_format((float)($destino['avaliacao_destino'] ?? 0),1,',','.') ?></td><td data-label="Ações"><div class="admin-actions justify-content-end"><a class="btn btn-sm btn-outline-primary" href="editar-destino.php?id=<?= (int)$destino['id_destino'] ?>"><i class="bi bi-pencil"></i> Editar</a><form method="post" action="../php/admin/excluir-destino.php" onsubmit="return confirm('Excluir este destino? Esta ação não pode ser desfeita.');"><input type="hidden" name="id_destino" value="<?= (int)$destino['id_destino'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> Excluir</button></form></div></td></tr><?php endforeach; ?>
</tbody></table></div></div></div></main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script><script src="../assets/js/script.js"></script>
</body></html>
