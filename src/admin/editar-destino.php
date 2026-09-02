<?php
// *confere o acesso antes de carregar os dados do destino*
require_once __DIR__ . '/../php/admin/auth.php';
require_once __DIR__ . '/../php/conexao.php';

$id = (int) ($_GET['id'] ?? 0);

// *busca no banco o destino que será editado*
$stmt = $conexao->prepare('SELECT * FROM destinos WHERE id_destino = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$destino = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conexao->close();

if (!$destino) {
    header('Location: dashboard.php?erro=Destino+não+encontrado.');
    exit;
}

$erro = trim($_GET['erro'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar destino - TopTurismo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <?php require __DIR__ . '/_navbar.php'; ?>

    <main class="admin-page">
        <div class="container">
            <div class="admin-card">
                <h1 class="h2 fw-bold">Editar destino</h1>
                <p class="text-muted">Altere os dados ou substitua qualquer uma das imagens.</p>

                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <!-- *envia as alterações para o PHP atualizar o banco* -->
                <form class="admin-form" action="../php/admin/editar-destino.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id_destino" value="<?= $id ?>">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="nome_destino">Nome do destino *</label>
                            <input class="form-control" id="nome_destino" name="nome_destino" value="<?= htmlspecialchars($destino['nome_destino']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="preco_destino">Preço por pessoa *</label>
                            <input class="form-control" id="preco_destino" name="preco_destino" type="number" min="0" step="0.01" value="<?= htmlspecialchars($destino['preco_destino']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="cidade_destino">Cidade *</label>
                            <input class="form-control" id="cidade_destino" name="cidade_destino" value="<?= htmlspecialchars($destino['cidade_destino']) ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="estado_destino">Estado (UF) *</label>
                            <input class="form-control" id="estado_destino" name="estado_destino" maxlength="2" value="<?= htmlspecialchars($destino['estado_destino'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="pais_destino">País *</label>
                            <input class="form-control" id="pais_destino" name="pais_destino" value="<?= htmlspecialchars($destino['pais_destino']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="regiao_destino">Região *</label>
                            <select class="form-select" id="regiao_destino" name="regiao_destino" required>
                                <?php foreach (['norte' => 'Norte', 'nordeste' => 'Nordeste', 'centro-oeste' => 'Centro-Oeste', 'sudeste' => 'Sudeste', 'sul' => 'Sul'] as $valor => $label): ?>
                                    <option value="<?= $valor ?>" <?= (($destino['regiao_destino'] ?? '') === $valor ? 'selected' : '') ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="avaliacao_destino">Avaliação *</label>
                            <input class="form-control" id="avaliacao_destino" name="avaliacao_destino" type="number" min="0" max="5" step="0.1" value="<?= htmlspecialchars($destino['avaliacao_destino'] ?? 5) ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="popularidade_destino">Popularidade (1–5) *</label>
                            <input class="form-control" id="popularidade_destino" name="popularidade_destino" type="number" min="1" max="5" value="<?= htmlspecialchars($destino['popularidade_destino'] ?? 3) ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="descricao_destino">Descrição *</label>
                            <textarea class="form-control" id="descricao_destino" name="descricao_destino" rows="5" required><?= htmlspecialchars($destino['descricao_destino']) ?></textarea>
                        </div>

                        <!-- *permite trocar cada uma das três imagens sem apagar as demais* -->
                        <?php
                        $imagens = [
                            $destino['img_destino'],
                            $destino['img_destino_2'] ?? '',
                            $destino['img_destino_3'] ?? '',
                        ];
                        ?>

                        <?php foreach ($imagens as $indice => $imagem): ?>
                            <div class="col-md-4">
                                <label class="form-label">Imagem <?= $indice + 1 ?> <small class="text-muted">(opcional)</small></label>
                                <?php if ($imagem): ?>
                                    <img class="admin-preview mb-2" src="../<?= htmlspecialchars($imagem) ?>" alt="Imagem <?= $indice + 1 ?>">
                                <?php endif; ?>
                                <input class="form-control" type="file" name="<?= $indice === 0 ? 'imagem_principal' : 'imagem_' . ($indice + 1) ?>" accept="image/jpeg,image/png,image/webp">
                            </div>
                        <?php endforeach; ?>

                        <div class="col-12 d-flex gap-2 mt-4">
                            <button class="btn btn-custom btn-lg" type="submit">
                                <i class="bi bi-check-circle me-2"></i>Salvar alterações
                            </button>
                            <a class="btn btn-outline-secondary btn-lg" href="dashboard.php">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
