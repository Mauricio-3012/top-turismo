<?php
session_start();

// *identifica se a sessão atual pertence a um administrador*
$isAdmin = (($_SESSION["usuario_tipo"] ?? "cliente") === "admin");
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8" />
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link href="./assets/css/style.css" rel="stylesheet" />
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
            rel="stylesheet"
        />
        <link href="./assets/imagens/logo-favicon.ico" rel="shortcut icon" type="image/x-icon" />
        <title>TopTurismo - Reserva de Viagens</title>
    </head>
    <body data-logado="<?= isset($_SESSION['usuario_id']) ? '1' : '0' ?>">
        <header>
            <nav class="navbar fixed-top navbar-expand-lg custom-bg p-3">
                <div class="container-fluid d-flex align-items-center flex-wrap">
                    <div class="d-flex align-items-center">
                        <img alt="Logo" height="50" src="./assets/imagens/logo-white.png" width="50" />
                        <a class="text-a ms-2 logo-texto" href="index.php">TopTurismo</a>
                    </div>
                    <div class="flex-grow-1 d-none d-lg-flex justify-content-center">
                        <ul class="navbar-nav flex-row gap-4">
                            <li class="nav-item"><a class="nav-link text-white" href="#destinos" data-section-link="destinos">Destinos</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="#reservar" data-section-link="reservar">Fazer Reserva</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="#sobre-nos" data-section-link="sobre-nos">Sobre nós</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="#contato" data-section-link="contato">Contato</a></li>
                            <!-- *mostra o painel administrativo somente para administradores* -->
                            <?php if ($isAdmin): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="./admin/dashboard.php">Gerenciar destinos</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center ms-auto gap-3">
                        <div class="dropdown">
                            <a
                                aria-expanded="false"
                                class="text-white fs-4 user-icon dropdown-toggle"
                                data-bs-toggle="dropdown"
                                href="#"
                                id="userAuthMenu"
                                role="button"
                            >
                                <i class="bi bi-person-circle"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" id="userAuthMenuList">
                                <?php if (isset($_SESSION["usuario_id"])): ?>
                                <li><a class="dropdown-item" href="./pages/dashboard.php"><i class="bi bi-person-fill me-2"></i>Meu Perfil</a></li>
                                <?php if ($isAdmin): ?><li><a class="dropdown-item" href="./admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Painel Admin</a></li><?php endif; ?>
                                <li><a class="dropdown-item" href="./php/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                                <?php else: ?>
                                <li><a class="dropdown-item" href="./pages/login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Entrar</a></li>
                                <li><a class="dropdown-item" href="./pages/cadastro.php"><i class="bi bi-person-plus me-2"></i>Cadastre-se</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button
                                class="btn btn-light dropdown-toggle"
                                data-bs-toggle="dropdown"
                                id="temaMenu"
                                type="button"
                            >
                                <i class="bi bi-circle-half"></i> Tema
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" onclick="setTheme('light')"
                                        ><i class="bi bi-sun-fill me-2"></i>Claro</a
                                    >
                                </li>
                                <li>
                                    <a class="dropdown-item" onclick="setTheme('dark')"
                                        ><i class="bi bi-moon-fill me-2"></i>Escuro</a
                                    >
                                </li>
                            </ul>
                        </div>
                        <button
                            aria-controls="menuMobile"
                            class="btn text-white d-lg-none"
                            data-bs-target="#menuMobile"
                            data-bs-toggle="offcanvas"
                            type="button"
                        >
                            <i class="bi bi-list fs-2"></i>
                        </button>
                    </div>
                </div>
            </nav>
        </header>
        <main>
            <section class="mt-5 p-0">
                <!-- *exibe o carrossel principal de destinos em destaque* -->
                <div class="carousel slide carousel-fade" data-bs-ride="carousel" id="myCarousel">
                    <div class="carousel-indicators">
                        <button class="active" data-bs-slide-to="0" data-bs-target="#myCarousel" type="button"></button>
                        <button data-bs-slide-to="1" data-bs-target="#myCarousel" type="button"></button>
                        <button data-bs-slide-to="2" data-bs-target="#myCarousel" type="button"></button>
                        <button data-bs-slide-to="3" data-bs-target="#myCarousel" type="button"></button>
                        <button data-bs-slide-to="4" data-bs-target="#myCarousel" type="button"></button>
                        <button data-bs-slide-to="5" data-bs-target="#myCarousel" type="button"></button>
                    </div>
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img alt="Rio" class="img-opacidade w-100" src="./assets/imagens/rio-de-janeiro.jpg" />
                            <div class="carousel-caption">
                                <h3>Rio de Janeiro</h3>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img alt="Salvador" class="img-opacidade w-100" src="./assets/imagens/maceio.jpg" />
                            <div class="carousel-caption">
                                <h3>Maceió</h3>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img alt="Maranhão" class="img-opacidade w-100" src="./assets/imagens/foz-do-iguacu.jpg" />
                            <div class="carousel-caption">
                                <h3>Foz do Iguaçu</h3>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img alt="Maranhão" class="img-opacidade w-100" src="./assets/imagens/amazonia.jpg" />
                            <div class="carousel-caption">
                                <h3>Manaus</h3>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img alt="Maranhão" class="img-opacidade w-100" src="./assets/imagens/sao-paulo.jpg" />
                            <div class="carousel-caption">
                                <h3>São Paulo</h3>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img alt="Maranhão" class="img-opacidade w-100" src="./assets/imagens/gramado.jpg" />
                            <div class="carousel-caption">
                                <h3>Gramado</h3>
                            </div>
                        </div>
                    </div>
                    <button
                        class="carousel-control-prev"
                        data-bs-slide="prev"
                        data-bs-target="#myCarousel"
                        type="button"
                    >
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button
                        class="carousel-control-next"
                        data-bs-slide="next"
                        data-bs-target="#myCarousel"
                        type="button"
                    >
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </section>
            <section class="secao-intro py-5">
                <!-- *apresenta a proposta principal do site ao visitante* -->
                <div class="container text-center">
                    <div class="row justify-content-center">
                        <div class="col-md-8 mb-5">
                            <h2 class="display-5 fw-bold secao-intro-h2">Sua próxima viagem é aqui</h2>
                            <p class="lead mt-3">
                                Explore o Brasil com o conforto e segurança da <strong>TopTurismo</strong>.
                            </p>
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="p-3">
                                <i class="bi bi-shield-check fs-1 text-custom-orange"></i>
                                <h4 class="mt-3">Viagem Segura</h4>
                                <p>Suporte em mais de 10 destinos nacionais.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3">
                                <i class="bi bi-currency-dollar fs-1 text-custom-orange"></i>
                                <h4 class="mt-3">Melhor Preço</h4>
                                <p>Garantimos os melhores descontos através de parcerias exclusivas.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3">
                                <i class="bi bi-clock-history fs-1 text-custom-orange"></i>
                                <h4 class="mt-3">Suporte 24h</h4>
                                <p>Nossa equipe está disponível a qualquer hora para te ajudar.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="py-5" id="sobre-nos">
                <!-- *apresenta informações institucionais da TopTurismo* -->
                <div class="container">
                    <div class="row align-items-center g-4">
                        <div class="col-md-6">
                            <img
                                alt="Sobre a TopTurismo"
                                class="img-fluid rounded"
                                src="./assets/imagens/hero-bg.jpg"
                            />
                        </div>
                        <div class="col-md-6">
                            <h2 class="display-6 fw-bold secao-intro-h2">Sobre a TopTurismo</h2>
                            <p class="lead mt-3">
                                Somos uma agência de viagens dedicada a conectar você aos melhores destinos do Brasil partindo de Brasília,
                                com preços justos e experiências inesquecíveis.
                            </p>
                            <p>
                                Desde o litoral até o coração da Amazônia, cuidamos de cada detalhe da sua viagem para
                                que você só precise se preocupar em aproveitar.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            <section class="py-5" id="destinos">
                <!-- *os destinos são carregados pelo PHP a partir do banco de dados* -->
                <div class="container">
                    <h2 class="display-5 fw-bold secao-intro-h2 text-center">Destinos</h2>
                    <p class="text-center mb-5">Confira os melhores destinos para sua próxima viagem.</p>
                    <div class="destinos-filtros mb-5">
                        <div class="busca-caixa">
                            <i class="bi bi-search"></i>
                            <input aria-label="Buscar destinos" placeholder="Buscar destinos..." type="text" />
                        </div>
                        <div class="filtro-destinos dropdown">
                            <i class="bi bi-sliders"></i>
                            <button aria-expanded="false" class="btn filtro-destinos-btn dropdown-toggle" data-bs-toggle="dropdown" id="filtroDestinos" type="button">
                                <span class="filtro-destinos-label">Todos os destinos</span>
                            </button>
                            <ul class="dropdown-menu filtro-destinos-menu" aria-labelledby="filtroDestinos">
                                <li><button class="dropdown-item filtro-opcao active" data-value="todos" type="button">Todos os destinos</button></li>
                                <li><button class="dropdown-item filtro-opcao" data-value="populares" type="button">Mais populares</button></li>
                                <li><button class="dropdown-item filtro-opcao" data-value="avaliados" type="button">Melhor avaliados</button></li>
                                <li><button class="dropdown-item filtro-opcao" data-value="norte" type="button">Região Norte</button></li>
                                <li><button class="dropdown-item filtro-opcao" data-value="nordeste" type="button">Região Nordeste</button></li>
                                <li><button class="dropdown-item filtro-opcao" data-value="centro-oeste" type="button">Centro-Oeste</button></li>
                                <li><button class="dropdown-item filtro-opcao" data-value="sudeste" type="button">Região Sudeste</button></li>
                                <li><button class="dropdown-item filtro-opcao" data-value="sul" type="button">Região Sul</button></li>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <?php
                        // *carrega a função que consulta os destinos no banco de dados*
                        require_once __DIR__ . '/php/destinos-data.php';
                        // *busca os destinos antes de montar os cards da página*
                        $destinos = buscarDestinos();
                        foreach ($destinos as $destino):
                            $nome = htmlspecialchars($destino['nome_destino'], ENT_QUOTES, 'UTF-8');
                            $local = htmlspecialchars($destino['cidade_destino'] . ($destino['estado_destino'] ? ' - ' . $destino['estado_destino'] : ''), ENT_QUOTES, 'UTF-8');
                            $descricao = htmlspecialchars($destino['descricao_destino'], ENT_QUOTES, 'UTF-8');
                            $imagem = './' . ltrim($destino['img_destino'], './');
                            $foto2 = $destino['img_destino_2'] ? './' . ltrim($destino['img_destino_2'], './') : '';
                            $foto3 = $destino['img_destino_3'] ? './' . ltrim($destino['img_destino_3'], './') : '';
                            $fotos = implode('|', array_filter([$foto2, $foto3]));
                            $maps = htmlspecialchars(implode(', ', array_filter([$destino['nome_destino'], $destino['cidade_destino'], $destino['estado_destino'], $destino['pais_destino'] ?: 'Brasil'])), ENT_QUOTES, 'UTF-8');
                            $avaliacao = number_format((float)$destino['avaliacao_destino'], 1, '.', '');
                            $preco = number_format((float)$destino['preco_destino'], 0, ',', '.');
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 card-destino-custom"
                                data-id-destino="<?= (int)$destino['id_destino'] ?>"
                                data-avaliacao="<?= $avaliacao ?>"
                                data-fotos="<?= htmlspecialchars($fotos, ENT_QUOTES, 'UTF-8') ?>"
                                data-popularidade="<?= (int)$destino['popularidade_destino'] ?>"
                                data-regiao="<?= htmlspecialchars($destino['regiao_destino'], ENT_QUOTES, 'UTF-8') ?>"
                                data-maps-query="<?= $maps ?>">
                                <div class="card-img-container">
                                    <img alt="<?= $nome ?>" class="card-img-top" src="<?= htmlspecialchars($imagem, ENT_QUOTES, 'UTF-8') ?>" />
                                    <div class="card-info-fixa">
                                        <div class="card-top-row">
                                            <h5 class="nome-destino-overlay"><?= $local ?></h5>
                                            <div class="preco-badges">
                                                <div class="preco-badge">R$ <?= $preco ?></div>
                                                <div class="ida-badge"><i class="bi bi-arrow-right"></i> Ida</div>
                                            </div>
                                        </div>
                                        <div class="avaliacao-estrelas" aria-label="Avaliação <?= $avaliacao ?> de 5"></div>
                                    </div>
                                    <div class="card-overlay">
                                        <div class="overlay-conteudo">
                                            <p class="descricao-destino"><?= $descricao ?></p>
                                            <button class="btn btn-custom btn-ver-mais p-4" data-destino="<?= $nome ?>" type="button">
                                                <i class="bi bi-info-circle"></i> Mais Detalhes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>

                    <?php if ($isAdmin): ?>
                    <!-- *mostra o acesso rápido ao formulário de novos destinos somente para administradores* -->
                    <div class="text-center mt-3">
                        <a href="./admin/adicionar-destino.php" class="btn btn-custom btn-lg rounded-pill px-4">
                            <i class="bi bi-plus-circle me-2"></i>Adicionar destino
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            <section class="reserva-generica py-5 mb-5" id="reservar"> <!-- seção reservar -->
                <div class="container">
                    <div class="reserva-generica-card">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="reserva-generica-kicker">PLANEJE SEM COMPLICAÇÃO</span>
                                <h2 class="display-6 fw-bold mb-3">
                                    Transforme seu próximo destino em uma viagem completa
                                </h2>
                                <p class="lead mb-3">
                                    Escolha o destino, informe a data, quantidade de passageiros e a melhor opção de
                                    transporte. A TopTurismo calcula tudo para você antes da confirmação.
                                </p>
                            </div>
                            <div class="col-lg-5 text-lg-end text-center">
                                <button class="btn-custom btn btn-lg text-white px-5 py-3 btn-reservar" data-destino="">
                                    <i class="bi bi-calendar-check me-2"></i>Fazer uma reserva
                                </button>
                                <p class="small text-muted mt-3 mb-0">
                                    Você poderá revisar todos os Detalhes antes de confirmar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        <footer class="footer-principal">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <h5>Informações Gerais</h5>
                        <ul class="footer-links">
                            <li><a href="#">FAQ</a></li>
                            <li><a href="#">Política de Privacidade</a></li>
                            <li><a href="#">Sobre a TopTurismo</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4 mb-4 text-center" id="contato">
                        <h5>Contatos</h5>
                        <div class="footer-contato-item"><i class="bi bi-whatsapp"></i> (99) 99999-9999</div>
                        <div class="footer-contato-item"><i class="bi bi-envelope"></i> contato@topturismo.com</div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <h5>Receba Novidades</h5>
                        <div class="newsletter-form">
                            <input class="footer-input" placeholder="Seu melhor e-mail" type="email" />
                            <button class="footer-btn">Quero assinar!</button>
                        </div>
                    </div>
                </div>
                <hr class="footer-divisor" />
                <div class="text-center footer-bottom">
                    <p>TopTurismo Agência de Viagens Ltda. © 2026 - Todos os direitos reservados.</p>
                </div>
            </div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="./assets/js/script.js"></script>

        <div
            aria-hidden="true"
            aria-labelledby="destinoDetalhesTitulo"
            class="modal fade"
            id="destinoDetalhesModal"
            tabindex="-1"
        > <!-- Modal de Detalhes do destino -->
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content destino-modal-content">
                    <div class="modal-header border-0 pb-0">
                        <button aria-label="Fechar" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                    </div>
                    <div class="modal-body pt-0">
                        <div class="destino-modal-imagem-wrap">
                            <div
                                class="carousel slide destino-modal-carousel"
                                data-bs-interval="4000"
                                id="destinoModalCarousel"
                            >
                                <div class="carousel-indicators">
                                    <button
                                        aria-current="true"
                                        aria-label="Foto 1"
                                        class="active"
                                        data-bs-slide-to="0"
                                        data-bs-target="#destinoModalCarousel"
                                        type="button"
                                    ></button>
                                    <button
                                        aria-label="Foto 2"
                                        data-bs-slide-to="1"
                                        data-bs-target="#destinoModalCarousel"
                                        type="button"
                                    ></button>
                                    <button
                                        aria-label="Foto 3"
                                        data-bs-slide-to="2"
                                        data-bs-target="#destinoModalCarousel"
                                        type="button"
                                    ></button>
                                </div>
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <img alt="Destino" id="destinoModalImagem" src="./assets/imagens/hero-bg.jpg" />
                                    </div>
                                    <div class="carousel-item">
                                        <div class="destino-modal-foto-placeholder">
                                            <i class="bi bi-image"></i><span>Foto adicional</span>
                                        </div>
                                    </div>
                                    <div class="carousel-item">
                                        <div class="destino-modal-foto-placeholder">
                                            <i class="bi bi-image"></i><span>Foto adicional</span>
                                        </div>
                                    </div>
                                </div>
                                <button
                                    aria-label="Foto anterior"
                                    class="carousel-control-prev"
                                    data-bs-slide="prev"
                                    data-bs-target="#destinoModalCarousel"
                                    type="button"
                                >
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button
                                    aria-label="Próxima foto"
                                    class="carousel-control-next"
                                    data-bs-slide="next"
                                    data-bs-target="#destinoModalCarousel"
                                    type="button"
                                >
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            </div>
                            <div class="destino-modal-imagem-overlay"></div>
                            <div class="destino-modal-titulo-overlay">
                                <h2 id="destinoDetalhesTitulo">Destino</h2>
                            </div>
                        </div>
                        <div class="destino-modal-resumo">
                            <div class="destino-modal-info">
                                <span><i class="bi bi-tag-fill"></i>Valor /pessoa</span>
                                <strong id="destinoModalPreco">R$ 0,00 </strong>
                            </div>
                            <div class="destino-modal-info destino-modal-info-ida">
                                <span><i class="bi bi-arrow-right-circle-fill"></i> Modalidade</span>
                                <strong>Ida</strong>
                            </div>
                            <div class="destino-modal-info">
                                <span><i class="bi bi-star-fill"></i> Avaliação</span>
                                <strong id="destinoModalAvaliacao">5,0</strong>
                            </div>
                        </div>
                        <div class="destino-modal-descricao">
                            <span class="destino-modal-kicker">SOBRE O DESTINO</span>
                            <p id="destinoModalDescricao"></p>
                        </div>
                        <div class="destino-modal-localizacao">
                            <div>
                                <span class="destino-modal-kicker">LOCALIZAÇÃO</span>
                                <strong id="destinoModalLocal">Destino</strong>
                                <p>Consulte a localização no mapa antes de planejar sua viagem.</p>
                            </div>
                            <a
                                class="btn btn-outline-dark rounded-pill"
                                href="#"
                                id="destinoModalMaps"
                                rel="noopener noreferrer"
                                target="_blank"
                            >
                                <img class="google-maps-icon" src="./assets/imagens/google-maps.svg" alt="" aria-hidden="true">
                            </a>
                        </div>
                        <div class="text-center pt-3">
                            <button
                                class="btn btn-custom btn-lg px-5 p-3  mt-3 mb-3 rounded-pill"
                                id="btnReservarDestinoModal"
                                type="button"
                            >
                                <i class="me-2"></i>Fazer reserva
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="loginModal"> <!-- pop up fazer login/cadastro -->
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-black text-center p-4">
                    <i
                        class="btn btn-close position-absolute top-0 end-0 p-3"
                        data-bs-dismiss="modal"
                        type="button"
                    ></i>
                    <div class="modal-body">
                        <i class="bi bi-lock-fill fs-1 text-black mb-3"></i>
                        <h5 class="mb-3">Faça login ou cadastre-se para continuar</h5>
                        <div class="d-flex justify-content-center gap-2">
                            <a class="btn btn-custom" href="pages/login.php"> Fazer Login</a>
                            <a class="btn btn-custom" href="pages/cadastro.php">Cadastrar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- menu mobile -->
        <div aria-labelledby="menuMobileLabel" class="offcanvas offcanvas-end" id="menuMobile" tabindex="-1">
            <div class="offcanvas-header">
                <div class="d-flex align-items-center">
                    <img alt="Logo" height="35" src="./assets/imagens/logo-favicon.ico" width="35" />
                    <span class="ms-2 fw-bold" id="menuMobileLabel">TopTurismo</span>
                </div>
                <button aria-label="Fechar" class="btn-close" data-bs-dismiss="offcanvas" type="button"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item" href="#destinos">
                            <i class="bi bi-geo-alt fs-4"></i> Destinos
                        </a>
                    </li>
                    <li class="mb-2">
                        <a class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item" href="#reservar">
                            <i class="bi bi-calendar-check fs-4"></i> Fazer Reserva
                        </a>
                    </li>
                    <li class="mb-2">
                        <a class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item" href="#sobre-nos">
                            <i class="bi bi-info-circle fs-4"></i> Sobre nós
                        </a>
                    </li>
                    <li class="mb-2">
                        <a class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item" href="#contato">
                            <i class="bi bi-telephone fs-4"></i> Contato
                        </a>
                    </li>
                    <?php if ($isAdmin): ?>
                    <li class="mb-2">
                        <a class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item" href="./admin/dashboard.php">
                            <i class="bi bi-speedometer2 fs-4"></i> Gerenciar destinos
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <hr />
                <ul class="list-unstyled" id="userAuthMenuMobileList">
                <?php if (isset($_SESSION["usuario_id"])): ?>
                    <li class="mb-2"><a href="./pages/dashboard.php" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item"><i class="bi bi-person-circle fs-4"></i> Meu Painel</a></li>
                    <?php if ($isAdmin): ?><li class="mb-2"><a href="./admin/dashboard.php" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item"><i class="bi bi-speedometer2 fs-4"></i> Painel Admin</a></li><?php endif; ?>
                    <li><a href="./php/logout.php" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item"><i class="bi bi-box-arrow-right fs-4"></i> Sair</a></li>
                    <?php else: ?>
                    <li class="mb-2"><a class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item" href="./pages/login.php"><i class="bi bi-box-arrow-in-right fs-4"></i> Entrar</a></li>
                    <li><a class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item" href="./pages/cadastro.php"><i class="bi bi-person-plus fs-4"></i> Cadastre-se</a></li>
                <?php endif; ?>
                </ul>
            </div>
        </div>
    </body>
</html>
