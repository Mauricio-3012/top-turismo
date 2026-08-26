<?php
$adminNome = $_SESSION['usuario_nome'] ?? 'Administrador';
?>
<header>
    <nav class="navbar fixed-top navbar-expand-lg custom-bg p-3">
        <div class="container-fluid d-flex align-items-center flex-wrap">
            <div class="d-flex align-items-center">
                <img src="../assets/imagens/logo-white.png" width="50" height="50" alt="Logo">
                <a href="../index.php" class="text-a ms-2 logo-texto">TopTurismo</a>
            </div>

            <div class="flex-grow-1 d-none d-lg-flex justify-content-center">
                <ul class="navbar-nav flex-row gap-4">
                    <li class="nav-item"><a class="nav-link text-white" href="../index.php#destinos"><i class="bi bi-geo-alt me-1"></i>Destinos</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="../index.php#reservar"><i class="bi bi-calendar-check me-1"></i>Fazer Reserva</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="../index.php#sobre-nos"><i class="bi bi-info-circle me-1"></i>Sobre nós</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="../index.php#contato"><i class="bi bi-telephone me-1"></i>Contato</a></li>
                    <li class="nav-item"><a class="nav-link text-white fw-semibold" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Gerenciar destinos</a></li>
                </ul>
            </div>

            <div class="d-flex align-items-center ms-auto gap-3">
                <div class="dropdown">
                    <a href="#" class="text-white fs-4 user-icon dropdown-toggle" id="userAuthMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" id="userAuthMenuList">
                        <li class="px-3 py-2 small text-muted border-bottom">Olá, <strong><?= htmlspecialchars($adminNome, ENT_QUOTES, 'UTF-8') ?></strong></li>
                        <li><a class="dropdown-item" href="../pages/dashboard.php"><i class="bi bi-person-fill me-2"></i>Meu Perfil</a></li>
                        <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Gerenciar destinos</a></li>
                        <li><a class="dropdown-item" href="../php/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>

                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="temaMenu" data-bs-toggle="dropdown">
                        <i class="bi bi-circle-half"></i> Tema
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" onclick="setTheme('light')"><i class="bi bi-sun-fill me-2"></i>Claro</a></li>
                        <li><a class="dropdown-item" onclick="setTheme('dark')"><i class="bi bi-moon-fill me-2"></i>Escuro</a></li>
                    </ul>
                </div>

                <button class="btn text-white d-lg-none" type="button" data-bs-target="#menuMobile" data-bs-toggle="offcanvas" aria-controls="menuMobile">
                    <i class="bi bi-list fs-2"></i>
                </button>
            </div>
        </div>
    </nav>
</header>

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
            <li class="mb-2"><a class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item" href="../index.php#destinos"><i class="bi bi-geo-alt fs-4"></i> Destinos</a></li>
            <li class="mb-2"><a class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item" href="../index.php#reservar"><i class="bi bi-calendar-check fs-4"></i> Fazer Reserva</a></li>
            <li class="mb-2"><a class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item" href="../index.php#sobre-nos"><i class="bi bi-info-circle fs-4"></i> Sobre nós</a></li>
            <li class="mb-2"><a class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item" href="../index.php#contato"><i class="bi bi-telephone fs-4"></i> Contato</a></li>
            <li class="mb-2"><a class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item" href="dashboard.php"><i class="bi bi-speedometer2 fs-4"></i> Gerenciar destinos</a></li>
        </ul>
        <hr>
        <ul class="list-unstyled">
            <li class="mb-2"><a href="../pages/dashboard.php" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item"><i class="bi bi-person-circle fs-4"></i> Meu Perfil</a></li>
            <li><a href="../php/logout.php" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item"><i class="bi bi-box-arrow-right fs-4"></i> Sair</a></li>
        </ul>
    </div>
</div>
