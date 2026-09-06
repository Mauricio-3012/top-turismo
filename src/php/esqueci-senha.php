<?php
// Recuperação de senha do TopTurismo.
// O fluxo usa um token salvo no banco, e não depende da sessão do navegador.
session_start();
require_once __DIR__ . '/conexao.php';

function redirecionar(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function respostaNormalizada(string $valor): string
{
    $valor = trim($valor);
    $valor = preg_replace('/\s+/', ' ', $valor) ?? $valor;
    return mb_strtolower($valor, 'UTF-8');
}

// Garante que a tabela usada pela recuperação exista mesmo se o banco antigo
// ainda estiver sendo usado. Isso evita depender de uma migração manual.
$criarTabela = $conexao->query("\n    CREATE TABLE IF NOT EXISTS recuperacoes_senha (\n        id INT NOT NULL AUTO_INCREMENT,\n        id_usuario INT NOT NULL,\n        token_hash CHAR(64) NOT NULL,\n        expira_em DATETIME NOT NULL,\n        verificado TINYINT(1) NOT NULL DEFAULT 0,\n        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,\n        PRIMARY KEY (id),\n        UNIQUE KEY uk_recuperacao_token (token_hash),\n        KEY idx_recuperacao_usuario (id_usuario),\n        CONSTRAINT fk_recuperacao_usuario\n            FOREIGN KEY (id_usuario) REFERENCES usuarios(id)\n            ON DELETE CASCADE ON UPDATE CASCADE\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\n");

if (!$criarTabela) {
    error_log('TopTurismo recuperação - erro ao criar tabela: ' . $conexao->error);
    redirecionar('../pages/esqueci-senha.php?erro=' . urlencode('Não foi possível iniciar a recuperação.'));
}

$acao = (string) ($_POST['acao'] ?? '');

// =========================================================
// ETAPA 1: recebe o e-mail
// =========================================================
if ($acao === 'email') {
    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')), 'UTF-8');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirecionar('../pages/esqueci-senha.php?erro=' . urlencode('Digite um e-mail válido.'));
    }

    $stmt = $conexao->prepare(
        'SELECT id, pergunta_recuperacao, resposta_recuperacao_hash
         FROM usuarios WHERE email = ? LIMIT 1'
    );

    if (!$stmt) {
        error_log('TopTurismo recuperação - SELECT: ' . $conexao->error);
        redirecionar('../pages/esqueci-senha.php?erro=' . urlencode('Não foi possível consultar a conta.'));
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($idUsuario, $pergunta, $respostaHash);
    $encontrou = $stmt->fetch();
    $stmt->close();

    if (!$encontrou || (int) $idUsuario <= 0 || !$pergunta || !$respostaHash) {
        $conexao->close();
        redirecionar('../pages/esqueci-senha.php?erro=' . urlencode('Não encontramos uma conta com esse e-mail.'));
    }

    // Um token novo invalida recuperações anteriores daquele usuário.
    $stmt = $conexao->prepare('DELETE FROM recuperacoes_senha WHERE id_usuario = ?');
    if ($stmt) {
        $id = (int) $idUsuario;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expiraEm = date('Y-m-d H:i:s', time() + (30 * 60));

    $stmt = $conexao->prepare(
        'INSERT INTO recuperacoes_senha (id_usuario, token_hash, expira_em, verificado)
         VALUES (?, ?, ?, 0)'
    );

    if (!$stmt) {
        error_log('TopTurismo recuperação - INSERT: ' . $conexao->error);
        $conexao->close();
        redirecionar('../pages/esqueci-senha.php?erro=' . urlencode('Não foi possível iniciar a recuperação.'));
    }

    $id = (int) $idUsuario;
    $stmt->bind_param('iss', $id, $tokenHash, $expiraEm);
    $ok = $stmt->execute();
    $stmt->close();
    $conexao->close();

    if (!$ok) {
        redirecionar('../pages/esqueci-senha.php?erro=' . urlencode('Não foi possível iniciar a recuperação.'));
    }

    redirecionar('../pages/esqueci-senha.php?etapa=pergunta&token=' . urlencode($token));
}

// =========================================================
// ETAPA 2: valida a pergunta de segurança
// =========================================================
if ($acao === 'resposta') {
    $token = trim((string) ($_POST['token'] ?? ''));
    $resposta = respostaNormalizada((string) ($_POST['resposta_recuperacao'] ?? ''));

    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        $conexao->close();
        redirecionar('../pages/esqueci-senha.php?erro=' . urlencode('Sua recuperação expirou. Comece novamente.'));
    }

    if ($resposta === '') {
        $conexao->close();
        redirecionar('../pages/esqueci-senha.php?etapa=pergunta&token=' . urlencode($token) . '&erro=' . urlencode('Digite a resposta da pergunta.'));
    }

    $tokenHash = hash('sha256', $token);

    $stmt = $conexao->prepare(
        'SELECT r.id, r.id_usuario, r.expira_em, u.email, u.pergunta_recuperacao, u.resposta_recuperacao_hash
         FROM recuperacoes_senha r
         INNER JOIN usuarios u ON u.id = r.id_usuario
         WHERE r.token_hash = ? LIMIT 1'
    );

    if (!$stmt) {
        error_log('TopTurismo recuperação - SELECT token: ' . $conexao->error);
        redirecionar('../pages/esqueci-senha.php?erro=' . urlencode('Não foi possível validar a recuperação.'));
    }

    $stmt->bind_param('s', $tokenHash);
    $stmt->execute();
    $stmt->bind_result($idRecuperacao, $idUsuario, $expiraEm, $email, $pergunta, $respostaHash);
    $encontrou = $stmt->fetch();
    $stmt->close();

    if (!$encontrou || strtotime($expiraEm) < time()) {
        $conexao->close();
        redirecionar('../pages/esqueci-senha.php?erro=' . urlencode('Sua recuperação expirou. Comece novamente.'));
    }

    if (!password_verify($resposta, (string) $respostaHash)) {
        $conexao->close();
        redirecionar('../pages/esqueci-senha.php?etapa=pergunta&token=' . urlencode($token) . '&erro=' . urlencode('Resposta incorreta. Tente novamente.'));
    }

    $stmt = $conexao->prepare('UPDATE recuperacoes_senha SET verificado = 1 WHERE id = ?');
    if (!$stmt) {
        error_log('TopTurismo recuperação - UPDATE verificado: ' . $conexao->error);
        $conexao->close();
        redirecionar('../pages/esqueci-senha.php?etapa=pergunta&token=' . urlencode($token) . '&erro=' . urlencode('Não foi possível continuar.'));
    }

    $id = (int) $idRecuperacao;
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();
    $conexao->close();

    if (!$ok) {
        redirecionar('../pages/esqueci-senha.php?etapa=pergunta&token=' . urlencode($token) . '&erro=' . urlencode('Não foi possível continuar.'));
    }

    redirecionar('../pages/redefinir-senha.php?token=' . urlencode($token));
}

$conexao->close();
redirecionar('../pages/esqueci-senha.php?erro=' . urlencode('Etapa de recuperação inválida.'));
