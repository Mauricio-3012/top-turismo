<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/conexao.php';

$sql = "SELECT id_destino, nome_destino, descricao_destino, cidade_destino, pais_destino, img_destino, preco_destino
        FROM destinos
        ORDER BY nome_destino ASC";

$resultado = $conexao->query($sql);

if (!$resultado) {
    http_response_code(500);
    echo json_encode(['erro' => 'Não foi possível carregar os destinos.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$destinos = [];
while ($destino = $resultado->fetch_assoc()) {
    $destino['preco_destino'] = (float) $destino['preco_destino'];
    $destinos[] = $destino;
}

echo json_encode($destinos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
