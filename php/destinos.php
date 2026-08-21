<?php

header("Content-Type: application/json; charset=UTF-8");

require "conexao.php";

$sql = "SELECT id_destino, nome_destino, cidade_destino, pais_destino, preco_destino
        FROM destinos
        ORDER BY nome_destino ASC";

$resultado = $conexao->query($sql);

$destinos = [];

while ($destino = $resultado->fetch_assoc()) {
    $destinos[] = $destino;
}

echo json_encode($destinos, JSON_UNESCAPED_UNICODE);

?>