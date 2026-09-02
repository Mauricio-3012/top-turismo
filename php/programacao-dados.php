<?php
/**
 * Horários da simulação da TopTurismo.
 *
 * Mantemos os horários em um único arquivo para evitar repetir as mesmas
 * tabelas no JavaScript e em vários endpoints PHP.
 */

function todasProgramacoes(): array
{
    return [
        "Avião" => [
            1 => ["saida" => "08:30", "volta" => "18:00", "duracao" => 170],
            2 => ["saida" => "09:15", "volta" => "19:10", "duracao" => 100],
            3 => ["saida" => "07:45", "volta" => "18:20", "duracao" => 140],
            4 => ["saida" => "10:00", "volta" => "20:00", "duracao" => 130],
            5 => ["saida" => "11:20", "volta" => "21:10", "duracao" => 100],
            6 => ["saida" => "08:20", "volta" => "17:50", "duracao" => 120],
            7 => ["saida" => "06:50", "volta" => "18:30", "duracao" => 210],
            8 => ["saida" => "09:40", "volta" => "19:40", "duracao" => 220],
            9 => ["saida" => "08:50", "volta" => "18:40", "duracao" => 120],
            10 => ["saida" => "10:30", "volta" => "20:30", "duracao" => 105],
            11 => ["saida" => "07:10", "volta" => "17:00", "duracao" => 200],
            12 => ["saida" => "09:00", "volta" => "18:00", "duracao" => 120],
            13 => ["saida" => "08:10", "volta" => "19:00", "duracao" => 200],
            14 => ["saida" => "10:50", "volta" => "20:50", "duracao" => 95],
            15 => ["saida" => "06:40", "volta" => "17:40", "duracao" => 210],
            16 => ["saida" => "09:30", "volta" => "18:50", "duracao" => 140],
        ],
        "Ônibus" => [
            1 => ["saida" => "06:30", "volta" => "18:30", "duracao" => 570],
            2 => ["saida" => "07:00", "volta" => "20:00", "duracao" => 360],
            3 => ["saida" => "06:00", "volta" => "19:00", "duracao" => 480],
            4 => ["saida" => "07:30", "volta" => "19:30", "duracao" => 720],
            5 => ["saida" => "08:00", "volta" => "20:00", "duracao" => 300],
            6 => ["saida" => "06:20", "volta" => "18:20", "duracao" => 840],
            7 => ["saida" => "05:30", "volta" => "17:30", "duracao" => 1080],
            8 => ["saida" => "05:00", "volta" => "17:00", "duracao" => 1080],
            9 => ["saida" => "07:10", "volta" => "19:10", "duracao" => 600],
            10 => ["saida" => "08:20", "volta" => "20:20", "duracao" => 360],
            11 => ["saida" => "06:00", "volta" => "18:00", "duracao" => 900],
            12 => ["saida" => "07:40", "volta" => "19:40", "duracao" => 480],
            13 => ["saida" => "06:40", "volta" => "18:40", "duracao" => 900],
            14 => ["saida" => "08:30", "volta" => "20:30", "duracao" => 300],
            15 => ["saida" => "05:50", "volta" => "17:50", "duracao" => 960],
            16 => ["saida" => "06:50", "volta" => "18:50", "duracao" => 780],
        ],
    ];
}

function programacaoPorId(int $idDestino, string $transporte): ?array
{
    $programacoes = todasProgramacoes();

    if (!in_array($transporte, ["Avião", "Ônibus"], true)) {
        return null;
    }

    // Os 16 destinos originais possuem horários próprios.
    if (isset($programacoes[$transporte][$idDestino])) {
        return $programacoes[$transporte][$idDestino];
    }

    // Destinos adicionados pelo administrador também podem ser reservados.
    // Eles recebem uma programação padrão da simulação.
    return $transporte === "Avião"
        ? ["saida" => "09:00", "volta" => "18:00", "duracao" => 120]
        : ["saida" => "07:00", "volta" => "19:00", "duracao" => 480];
}
