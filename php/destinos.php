<?php

header("Content-Type: application/json; charset=UTF-8");

require "conexao.php";

$sql = "
    SELECT
        id_destino,
        nome_destino,
        descricao_destino,
        cidade_destino,
        pais_destino,
        img_destino,
        preco_destino
    FROM destinos
    ORDER BY nome_destino ASC
";

$resultado = $conexao->query($sql);

if (!$resultado) {
    http_response_code(500);

    echo json_encode(
        [
            "sucesso" => false,
            "mensagem" => "Não foi possível carregar os destinos.",
        ],
        JSON_UNESCAPED_UNICODE
    );

    $conexao->close();
    exit;
}

$destinos = [];

while ($destino = $resultado->fetch_assoc()) {
    $destinos[] = $destino;
}

$conexao->close();

echo json_encode($destinos, JSON_UNESCAPED_UNICODE);
?>