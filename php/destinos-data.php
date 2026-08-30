<?php
/**
 * Carrega os destinos do banco.
 * Se a conexão ainda não estiver configurada, usa os dados do SQL base.
 */
function buscarDestinos(): array
{
    $colunas = 'id_destino, nome_destino, descricao_destino, cidade_destino, estado_destino, pais_destino, regiao_destino, img_destino, img_destino_2, img_destino_3, preco_destino, avaliacao_destino, popularidade_destino';

    $arquivoConexao = __DIR__ . '/conexao.php';
    if (file_exists($arquivoConexao)) {
        require_once $arquivoConexao;

        $resultado = $conexao->query("SELECT $colunas FROM destinos ORDER BY id_destino ASC");
        if ($resultado) {
            $destinos = $resultado->fetch_all(MYSQLI_ASSOC);
            $resultado->free();
            $conexao->close();

            if (!$destinos) {
                return buscarDestinosPadrao();
            }

            foreach ($destinos as &$destino) {
                $destino['preco_destino'] = (float) $destino['preco_destino'];
                $destino['avaliacao_destino'] = (float) $destino['avaliacao_destino'];
                $destino['popularidade_destino'] = (int) $destino['popularidade_destino'];
            }
            unset($destino);

            return $destinos;
        }

        // Compatibilidade com bancos antigos que ainda não possuem as colunas
        // de avaliação/popularidade/imagens extras.
        $resultadoBasico = $conexao->query(
            "SELECT id_destino, nome_destino, descricao_destino, cidade_destino,
                    estado_destino, pais_destino, regiao_destino, img_destino,
                    preco_destino
             FROM destinos ORDER BY id_destino ASC"
        );

        if ($resultadoBasico) {
            $destinos = $resultadoBasico->fetch_all(MYSQLI_ASSOC);
            $resultadoBasico->free();
            $conexao->close();

            foreach ($destinos as &$destino) {
                $destino['img_destino_2'] = null;
                $destino['img_destino_3'] = null;
                $destino['avaliacao_destino'] = 5.0;
                $destino['popularidade_destino'] = 3;
                $destino['preco_destino'] = (float) $destino['preco_destino'];
            }
            unset($destino);

            return $destinos ?: buscarDestinosPadrao();
        }

        $conexao->close();
    }

    return buscarDestinosPadrao();
}

function buscarDestinosPadrao(): array
{
    return [
        ['id_destino'=>1,'nome_destino'=>'Maceió','descricao_destino'=>'Praias de águas cristalinas, piscinas naturais e muito descanso no litoral de Alagoas.','cidade_destino'=>'Maceió','estado_destino'=>'AL','pais_destino'=>'Brasil','regiao_destino'=>'nordeste','img_destino'=>'assets/imagens/maceio.jpg','img_destino_2'=>'assets/imagens/destinos/maceio/foto-2.jpeg','img_destino_3'=>'assets/imagens/destinos/maceio/foto-3.jpg','preco_destino'=>650,'avaliacao_destino'=>4.8,'popularidade_destino'=>5],
        ['id_destino'=>2,'nome_destino'=>'Rio de Janeiro','descricao_destino'=>'Cidade maravilhosa, com praias, montanhas e atrações famosas.','cidade_destino'=>'Rio de Janeiro','estado_destino'=>'RJ','pais_destino'=>'Brasil','regiao_destino'=>'sudeste','img_destino'=>'assets/imagens/rio-de-janeiro.jpg','img_destino_2'=>'assets/imagens/destinos/rio-de-janeiro/foto-2.jpg','img_destino_3'=>'assets/imagens/destinos/rio-de-janeiro/foto-3.jpg','preco_destino'=>500,'avaliacao_destino'=>4.8,'popularidade_destino'=>5],
        ['id_destino'=>3,'nome_destino'=>'Salvador','descricao_destino'=>'Cultura, história, praias e gastronomia baiana em uma cidade cheia de energia.','cidade_destino'=>'Salvador','estado_destino'=>'BA','pais_destino'=>'Brasil','regiao_destino'=>'nordeste','img_destino'=>'assets/imagens/salvador.jpg','img_destino_2'=>'assets/imagens/destinos/salvador/foto-2.jpg','img_destino_3'=>'assets/imagens/destinos/salvador/foto-3.jpg','preco_destino'=>550,'avaliacao_destino'=>4.6,'popularidade_destino'=>5],
        ['id_destino'=>4,'nome_destino'=>'Gramado','descricao_destino'=>'Destino turístico conhecido pelo clima europeu, gastronomia e atrações familiares.','cidade_destino'=>'Gramado','estado_destino'=>'RS','pais_destino'=>'Brasil','regiao_destino'=>'sul','img_destino'=>'assets/imagens/gramado.jpg','img_destino_2'=>'assets/imagens/destinos/gramado/foto-2.png','img_destino_3'=>'assets/imagens/destinos/gramado/foto-3.jpg','preco_destino'=>700,'avaliacao_destino'=>4.7,'popularidade_destino'=>4],
        ['id_destino'=>5,'nome_destino'=>'São Paulo','descricao_destino'=>'A maior cidade do país reúne gastronomia, cultura, negócios, museus e vida noturna.','cidade_destino'=>'São Paulo','estado_destino'=>'SP','pais_destino'=>'Brasil','regiao_destino'=>'sudeste','img_destino'=>'assets/imagens/sao-paulo.jpg','img_destino_2'=>'assets/imagens/destinos/sao-paulo/foto-2.png','img_destino_3'=>'assets/imagens/destinos/sao-paulo/foto-3.png','preco_destino'=>450,'avaliacao_destino'=>4.5,'popularidade_destino'=>5],
        ['id_destino'=>6,'nome_destino'=>'Foz do Iguaçu','descricao_destino'=>'Conheça as Cataratas do Iguaçu e uma das maiores experiências de natureza do Brasil.','cidade_destino'=>'Foz do Iguaçu','estado_destino'=>'PR','pais_destino'=>'Brasil','regiao_destino'=>'sul','img_destino'=>'assets/imagens/foz-do-iguacu.jpg','img_destino_2'=>'assets/imagens/destinos/foz-do-iguacu/foto-2.jpg','img_destino_3'=>'assets/imagens/destinos/foz-do-iguacu/foto-3.png','preco_destino'=>600,'avaliacao_destino'=>4.9,'popularidade_destino'=>5],
        ['id_destino'=>7,'nome_destino'=>'Lençois Maranhenses','descricao_destino'=>'Dunas de areia branca e lagoas cristalinas formam uma paisagem única.','cidade_destino'=>'Barreirinhas','estado_destino'=>'MA','pais_destino'=>'Brasil','regiao_destino'=>'nordeste','img_destino'=>'assets/imagens/maranhao.jpg','img_destino_2'=>'assets/imagens/destinos/lencois-maranhenses/foto-2.jpg','img_destino_3'=>'assets/imagens/destinos/lencois-maranhenses/foto-3.jpg','preco_destino'=>750,'avaliacao_destino'=>4.9,'popularidade_destino'=>5],
        ['id_destino'=>8,'nome_destino'=>'Manaus','descricao_destino'=>'Porta de entrada para a Amazônia, com natureza exuberante e experiências na floresta.','cidade_destino'=>'Manaus','estado_destino'=>'AM','pais_destino'=>'Brasil','regiao_destino'=>'norte','img_destino'=>'assets/imagens/amazonia.jpg','img_destino_2'=>'assets/imagens/destinos/manaus/foto-2.jpg','img_destino_3'=>'assets/imagens/destinos/manaus/foto-3.jpg','preco_destino'=>800,'avaliacao_destino'=>4.7,'popularidade_destino'=>4],
        ['id_destino'=>9,'nome_destino'=>'Florianópolis','descricao_destino'=>'A Ilha da Magia combina praias, natureza, gastronomia e infraestrutura moderna.','cidade_destino'=>'Florianópolis','estado_destino'=>'SC','pais_destino'=>'Brasil','regiao_destino'=>'sul','img_destino'=>'assets/imagens/florianopolis.jpg','img_destino_2'=>'assets/imagens/destinos/florianopolis/foto-2.jpg','img_destino_3'=>'assets/imagens/destinos/florianopolis/foto-3.jpg','preco_destino'=>500,'avaliacao_destino'=>4.8,'popularidade_destino'=>5],
        ['id_destino'=>10,'nome_destino'=>'Curitiba','descricao_destino'=>'Capital conhecida pelo planejamento urbano, parques, cultura e gastronomia.','cidade_destino'=>'Curitiba','estado_destino'=>'PR','pais_destino'=>'Brasil','regiao_destino'=>'sul','img_destino'=>'assets/imagens/curitiba.png','img_destino_2'=>'assets/imagens/destinos/curitiba/foto-2.jpg','img_destino_3'=>'assets/imagens/destinos/curitiba/foto-3.jpg','preco_destino'=>500,'avaliacao_destino'=>4.5,'popularidade_destino'=>4],
        ['id_destino'=>11,'nome_destino'=>'Fernando de Noronha','descricao_destino'=>'Paraíso brasileiro com praias cristalinas, trilhas, mergulho e vida marinha.','cidade_destino'=>'Fernando de Noronha','estado_destino'=>'PE','pais_destino'=>'Brasil','regiao_destino'=>'nordeste','img_destino'=>'assets/imagens/fernando-de-nornoha.png','img_destino_2'=>'assets/imagens/destinos/fernando-de-noronha/foto-2.png','img_destino_3'=>'assets/imagens/destinos/fernando-de-noronha/foto-3.png','preco_destino'=>1500,'avaliacao_destino'=>4.9,'popularidade_destino'=>5],
        ['id_destino'=>12,'nome_destino'=>'Campo Grande','descricao_destino'=>'Porta de entrada para o Pantanal, com natureza, cultura e gastronomia regional.','cidade_destino'=>'Campo Grande','estado_destino'=>'MS','pais_destino'=>'Brasil','regiao_destino'=>'centro-oeste','img_destino'=>'assets/imagens/campo-grande.png','img_destino_2'=>'assets/imagens/destinos/campo-grande/foto-2.jpg','img_destino_3'=>'assets/imagens/destinos/campo-grande/foto-3.jpg','preco_destino'=>400,'avaliacao_destino'=>4.4,'popularidade_destino'=>3],
        ['id_destino'=>13,'nome_destino'=>'Fortaleza','descricao_destino'=>'A capital do sol encanta com praias, falésias, cultura e excelente gastronomia.','cidade_destino'=>'Fortaleza','estado_destino'=>'CE','pais_destino'=>'Brasil','regiao_destino'=>'nordeste','img_destino'=>'assets/imagens/Fortaleza.png','img_destino_2'=>'assets/imagens/destinos/fortaleza/foto-2.jpg','img_destino_3'=>'assets/imagens/destinos/fortaleza/foto-3.jpg','preco_destino'=>700,'avaliacao_destino'=>4.7,'popularidade_destino'=>5],
        ['id_destino'=>14,'nome_destino'=>'Goiânia','descricao_destino'=>'Cidade arborizada que une boa gastronomia, parques, cultura e vida urbana.','cidade_destino'=>'Goiânia','estado_destino'=>'GO','pais_destino'=>'Brasil','regiao_destino'=>'centro-oeste','img_destino'=>'assets/imagens/Goiania.png','img_destino_2'=>'assets/imagens/destinos/goiania/foto-2.jpg','img_destino_3'=>'assets/imagens/destinos/goiania/foto-3.png','preco_destino'=>250,'avaliacao_destino'=>4.3,'popularidade_destino'=>3],
        ['id_destino'=>15,'nome_destino'=>'Jericoacoara','descricao_destino'=>'Vilarejo paradisíaco cercado por dunas, lagoas e praias de águas tranquilas.','cidade_destino'=>'Jijoca de Jericoacoara','estado_destino'=>'CE','pais_destino'=>'Brasil','regiao_destino'=>'nordeste','img_destino'=>'assets/imagens/Jericoacoara.png','img_destino_2'=>'assets/imagens/destinos/jericoacoara/foto-2.png','img_destino_3'=>'assets/imagens/destinos/jericoacoara/foto-3.jpg','preco_destino'=>750,'avaliacao_destino'=>4.9,'popularidade_destino'=>5],
        ['id_destino'=>16,'nome_destino'=>'Porto Alegre','descricao_destino'=>'A capital gaúcha reúne tradição, gastronomia, cultura e belas áreas ao ar livre.','cidade_destino'=>'Porto Alegre','estado_destino'=>'RS','pais_destino'=>'Brasil','regiao_destino'=>'sul','img_destino'=>'assets/imagens/porto-alegre.jpg','img_destino_2'=>'assets/imagens/destinos/porto-alegre/foto-2.png','img_destino_3'=>'assets/imagens/destinos/porto-alegre/foto-3.jpg','preco_destino'=>700,'avaliacao_destino'=>4.5,'popularidade_destino'=>4],
    ];
}
