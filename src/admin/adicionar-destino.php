<?php
// *protege o formulário para que somente administradores possam criar destinos*
require_once __DIR__ . '/../php/admin/auth.php';

$erro = trim($_GET['erro'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar destino - TopTurismo</title>
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
                <h1 class="h2 fw-bold">Adicionar novo destino</h1>
                <p class="text-muted">Preencha os dados e envie as três imagens. O destino aparecerá automaticamente no site.</p>

                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <!-- *envia os dados e as imagens para o PHP cadastrar o destino* -->
                <form class="admin-form" action="../php/admin/criar-destino.php" method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="nome_destino">Nome do destino *</label>
                            <input class="form-control" id="nome_destino" name="nome_destino" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="preco_destino">Preço por pessoa *</label>
                            <input class="form-control" id="preco_destino" name="preco_destino" type="number" min="0" step="0.01" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="cidade_destino">Cidade *</label>
                            <input class="form-control" id="cidade_destino" name="cidade_destino" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="estado_destino">Estado (UF) *</label>
                            <input class="form-control" id="estado_destino" name="estado_destino" maxlength="2" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="pais_destino">País *</label>
                            <input class="form-control" id="pais_destino" name="pais_destino" value="Brasil" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="regiao_destino">Região *</label>
                            <select class="form-select" id="regiao_destino" name="regiao_destino" required>
                                <option value="">Selecione</option>
                                <option value="norte">Norte</option>
                                <option value="nordeste">Nordeste</option>
                                <option value="centro-oeste">Centro-Oeste</option>
                                <option value="sudeste">Sudeste</option>
                                <option value="sul">Sul</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="avaliacao_destino">Avaliação *</label>
                            <input class="form-control" id="avaliacao_destino" name="avaliacao_destino" type="number" min="0" max="5" step="0.1" value="5" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="popularidade_destino">Popularidade (1–5) *</label>
                            <input class="form-control" id="popularidade_destino" name="popularidade_destino" type="number" min="1" max="5" value="3" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="descricao_destino">Descrição *</label>
                            <textarea class="form-control" id="descricao_destino" name="descricao_destino" rows="5" required></textarea>
                        </div>

                        <!-- *cada destino novo recebe três imagens para o carrossel do site* -->
                        <div class="col-md-4">
                            <label class="form-label" for="imagem_principal">Imagem principal *</label>
                            <input class="form-control" id="imagem_principal" type="file" name="imagem_principal" accept="image/jpeg,image/png,image/webp" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="imagem_2">Imagem 2 *</label>
                            <input class="form-control" id="imagem_2" type="file" name="imagem_2" accept="image/jpeg,image/png,image/webp" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="imagem_3">Imagem 3 *</label>
                            <input class="form-control" id="imagem_3" type="file" name="imagem_3" accept="image/jpeg,image/png,image/webp" required>
                        </div>

                        <div class="col-12 d-flex gap-2 mt-4">
                            <button class="btn btn-custom btn-lg" type="submit">
                                <i class="bi bi-plus-circle me-2"></i>Cadastrar destino
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
