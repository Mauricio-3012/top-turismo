<?php

header('Content-Type: application/json; charset=UTF-8');
require 'conexao.php';

$sql = 'SELECT id_destino, nome_destino, descricao_destino, cidade_destino, estado_destino, pais_destino, regiao_destino, img_destino, img_destino_2, img_destino_3, preco_destino, avaliacao_destino, popularidade_destino FROM destinos ORDER BY id_destino ASC';
$resultado = $conexao->query($sql);

if (!$resultado) {
    http_response_code(500);
    echo json_encode(['sucesso'=>false,'mensagem'=>'Não foi possível carregar os destinos.'], JSON_UNESCAPED_UNICODE);
    $conexao->close();
    exit;
}

$destinos = [];
while ($destino = $resultado->fetch_assoc()) {
    $destino['preco_destino'] = (float)$destino['preco_destino'];
    $destino['avaliacao_destino'] = (float)$destino['avaliacao_destino'];
    $destino['popularidade_destino'] = (int)$destino['popularidade_destino'];
    $destinos[] = $destino;
}
$conexao->close();
echo json_encode($destinos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
