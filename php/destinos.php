<?php
header('Content-Type: application/json; charset=UTF-8');
// *retorna os destinos em JSON para outras partes do projeto*
require_once __DIR__ . '/destinos-data.php';

echo json_encode(buscarDestinos(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
