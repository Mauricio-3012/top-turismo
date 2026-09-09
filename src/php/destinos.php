<?php
// retorna os destinos em json
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/destinos-data.php';

echo json_encode(buscarDestinos(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>