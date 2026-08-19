<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="../assets/imagens/logo-favicon.ico" type="image/x-icon">
    <title>Meu Perfil - TopTurismo</title>
</head>

<body>
    <header>
        <nav class="navbar fixed-top navbar-expand-lg custom-bg p-3">
            <div class="container-fluid d-flex align-items-center flex-wrap">
                <div class="d-flex align-items-center">
                    <img src="../assets/imagens/logo-white.png" width="50" height="50" alt="Logo">
                    <a href="index.php" class="text-a ms-2 logo-texto">TopTurismo</a>
                </div>

                <div class="flex-grow-1 d-none d-lg-flex justify-content-center">
                    <ul class="navbar-nav flex-row gap-4">
                        <li class="nav-item"><a class="nav-link text-white" href="../index.php#destinos">Destinos</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="../index.php#reservar">Fazer Reserva</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="../index.php#sobre-nos">Sobre nós</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="../index.php#contato">Contato</a></li>
                    </ul>
                </div>

                <div class="d-flex align-items-center ms-auto gap-3">
                    <div class="dropdown">
                        <a href="#" class="text-white fs-4 user-icon dropdown-toggle" id="userAuthMenu" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" id="userAuthMenuList">
                            <li><a class="dropdown-item" href="./pages/login.php"><i
                                        class="bi bi-box-arrow-in-right me-2"></i>Sair</a></li>
                        </ul>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="temaMenu"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-circle-half"></i> Tema
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" onclick="setTheme('light')"><i
                                        class="bi bi-sun-fill me-2"></i>Claro</a></li>
                            <li><a class="dropdown-item" onclick="setTheme('dark')"><i
                                        class="bi bi-moon-fill me-2"></i>Escuro</a></li>
                        </ul>
                    </div>
                    <button class="btn text-white d-lg-none" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#menuMobile" aria-controls="menuMobile">
                        <i class="bi bi-list fs-2"></i>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="profile-header">
            <div class="container text-center">
                <div class="profile-img-container">
                    <i class="bi bi-person-circle"></i>
                </div>
                <h2 class="mt-3 fw-bold secao-intro-h2" id="nomeUsuarioBoasVindas">Carregando...</h2>
            </div>
            <div class="container py-3">
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="#meus-dados" class="btn btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-person-fill me-1"></i> Meus Dados
                    </a>
                    <a href="#minhas-viagens" class="btn btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-geo-alt-fill me-1"></i> Minhas Viagens
                    </a>
                </div>
            </div>
        </section>

        <section id="meus-dados" class="py-4">
            <div class="container">
                <div class="col-lg-8 mx-auto">
                    <div class="card border-0 shadow-sm p-4">
                        <h4 class="fw-bold mb-4" style="color: var(--btn-bg);">
                            <i class="bi bi-person-fill me-2"></i>Meus Dados
                        </h4>
                        <form class="row g-3 m-0 p-0 bg-transparent" action="../php/dashboard.php" method="POST">
    <div class="col-md-6">
        <label class="form-label">Nome Completo</label>
        <input type="text" class="form-control" id="campoNome" name="nome" value="">
    </div>
    <div class="col-md-6">
        <label class="form-label">E-mail</label>
        <input type="email" class="form-control" id="campoEmail" name="email" value="">
    </div>
    <div class="col-md-6">
        <label class="form-label">Telefone</label>
        <input type="text" class="form-control" id="campoTelefone" name="telefone" value="">
    </div>
    <div class="col-md-6">
        <label class="form-label">Cidade</label>
        <input type="text" class="form-control" placeholder="Não informado">
    </div>
    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-custom px-5 w-100">Salvar Alterações</button>
    </div>
</form>
                        <hr class="my-4">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h6 class="fw-bold text-danger mb-1">Excluir Conta</h6>
        <p class="text-muted small mb-0">Essa ação é permanente e não pode ser desfeita.</p>
    </div>
    <button type="button" class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#modalExcluirConta">
        <i class="bi bi-trash me-1"></i> Excluir minha conta
    </button>
</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="minhas-viagens" class="py-4">
            <div class="container">
                <div class="col-lg-8 mx-auto">
                    <div class="card border-0 shadow-sm p-4 mb-3">
                        <h4 class="fw-bold mb-4" style="color: var(--btn-bg);">
                            <i class="bi bi-geo-alt-fill me-2"></i>Minhas Viagens
                        </h4>

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
                        <li><a href="#">Termos de Uso</a></li>
                        <li><a href="#">Sobre a TopTurismo</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4 text-center">
                    <h5>Contatos</h5>
                    <div class="footer-contato-item"><i class="bi bi-whatsapp"></i> (99) 99999-9999</div>
                    <div class="footer-contato-item"><i class="bi bi-envelope"></i> contato@topturismo.com</div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Receba Novidades</h5>
                    <div class="newsletter-form">
                        <input type="email" placeholder="Seu melhor e-mail" class="footer-input">
                        <button class="footer-btn">Quero assinar!</button>
                    </div>
                </div>
            </div>
            <hr class="footer-divisor">
            <div class="text-center footer-bottom">
                <p>TopTurismo Agência de Viagens Ltda. </p>
                <p> &copy; 2025 - Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        // Busca os dados do usuário logado e preenche a página
        fetch("../php/usuario-logado.php")
            .then(resposta => {
                if (!resposta.ok) {
                    // Não está logado (ou sessão expirou) -> manda pro login
                    window.location.href = "login.php";
                    throw new Error("Não autenticado");
                }
                return resposta.json();
            })
            .then(usuario => {
                document.getElementById("nomeUsuarioBoasVindas").innerText = "Bem-vindo, " + usuario.nome + "!";
                document.getElementById("campoNome").value = usuario.nome;
                document.getElementById("campoEmail").value = usuario.email;
                document.getElementById("campoTelefone").value = usuario.telefone;
            })
            .catch(erro => console.error("Erro ao carregar dados do usuário:", erro));
    </script>

     <!-- menu mobile -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="menuMobile" aria-labelledby="menuMobileLabel">
        <div class="offcanvas-header">
            <div class="d-flex align-items-center">
                <img src="../assets/imagens/logo-favicon.ico" width="35" height="35" alt="Logo">
                <span class="ms-2 fw-bold" id="menuMobileLabel">TopTurismo</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled">
                <li class="mb-2">
                    <a href="../index.php" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item">
                        <i class="bi bi-house fs-4"></i> Início
                    </a>
                </li>
                <li class="mb-2">
                    <a href="../index.php#destinos" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item">
                        <i class="bi bi-geo-alt fs-4"></i> Destinos
                    </a>
                </li>
                <li class="mb-2">
                    <a href="../index.php#reservar" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item">
                        <i class="bi bi-calendar-check fs-4"></i> Fazer Reserva
                    </a>
                </li>
                <li class="mb-2">
                    <a href="../index.php#sobre-nos" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item">
                        <i class="bi bi-info-circle fs-4"></i> Sobre nós
                    </a>
                </li>
                <li class="mb-2">
                    <a href="../index.php#contato" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item">
                        <i class="bi bi-telephone fs-4"></i> Contato
                    </a>
                </li>
            </ul>
            <hr>
            <ul class="list-unstyled" id="userAuthMenuMobileList">
                <li class="mb-2">
                    <a href="./pages/login.php" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item">
                        <i class="bi bi-box-arrow-in-right fs-4"></i> Sair
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>

    <div class="modal fade" id="modalExcluirConta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="../php/excluir-conta.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title text-danger">Excluir Conta</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja excluir sua conta? Essa ação é <strong>permanente</strong>.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-custom">Sim, excluir minha conta</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>

</html>