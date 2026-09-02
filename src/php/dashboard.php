<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../pages/login.php");
    exit;
}

require_once __DIR__ . "/conexao.php";

$id = (int) $_SESSION["usuario_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $conexao->close();
    header("Location: ../pages/dashboard.php");
    exit;
}

$nome = trim((string) ($_POST["nome"] ?? ""));
$email = mb_strtolower(trim((string) ($_POST["email"] ?? "")), "UTF-8");
$telefone = preg_replace("/\D/", "", (string) ($_POST["telefone"] ?? ""));
$cidade = trim((string) ($_POST["cidade"] ?? ""));
$pergunta = trim((string) ($_POST["pergunta_seguranca"] ?? ""));
$resposta = trim((string) ($_POST["resposta_seguranca"] ?? ""));

$perguntasPermitidas = [
    "Qual é o nome do seu primeiro pet?",
    "Qual é o nome da sua cidade natal?",
    "Qual era o nome da sua escola?",
    "Qual é o seu destino turístico favorito?"
];

$erro = null;
if ($nome === "" || mb_strlen($nome) < 3 || !preg_match("/^[\p{L}\s']+$/u", $nome)) {
    $erro = "Nome inválido.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erro = "E-mail inválido.";
} elseif (strlen($telefone) < 10 || strlen($telefone) > 11) {
    $erro = "Telefone inválido. Informe DDD + número.";
} elseif ($cidade === "" || mb_strlen($cidade) < 2) {
    $erro = "Cidade inválida.";
} elseif ($pergunta !== "" && !in_array($pergunta, $perguntasPermitidas, true)) {
    $erro = "Pergunta de segurança inválida.";
} elseif ($pergunta !== "" && mb_strlen($resposta) < 2) {
    $erro = "A resposta da pergunta deve ter pelo menos 2 caracteres.";
}

if ($erro !== null) {
    $conexao->close();
    header("Location: ../pages/dashboard.php?erro=" . urlencode($erro));
    exit;
}

// Verifica se o e-mail já pertence a outra conta.
$stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1");
if (!$stmt) {
    $conexao->close();
    header("Location: ../pages/dashboard.php?erro=" . urlencode("Não foi possível validar o e-mail."));
    exit;
}
$stmt->bind_param("si", $email, $id);
$stmt->execute();
$duplicado = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ($duplicado) {
    $conexao->close();
    header("Location: ../pages/dashboard.php?erro=" . urlencode("Este e-mail já está cadastrado em outra conta."));
    exit;
}

$conexao->begin_transaction();
try {
    $stmt = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ?, cidade = ? WHERE id = ? LIMIT 1");
    if (!$stmt) {
        throw new RuntimeException("Não foi possível preparar os dados pessoais.");
    }
    $stmt->bind_param("ssssi", $nome, $email, $telefone, $cidade, $id);
    if (!$stmt->execute()) {
        throw new RuntimeException("Não foi possível atualizar os dados pessoais.");
    }
    $stmt->close();

    $senhaAtual = (string) ($_POST["senha_atual"] ?? "");
    $senhaNova = (string) ($_POST["senha_nova"] ?? "");
    $senhaConfirmacao = (string) ($_POST["senha_confirmacao"] ?? "");

    if ($senhaAtual !== "" || $senhaNova !== "" || $senhaConfirmacao !== "") {
        if ($senhaAtual === "" || $senhaNova === "" || $senhaConfirmacao === "") {
            throw new RuntimeException("Para alterar a senha, preencha os três campos de senha.");
        }
        if (strlen($senhaNova) < 8 || !preg_match("/[a-z]/", $senhaNova) || !preg_match("/[A-Z]/", $senhaNova) || !preg_match("/[0-9]/", $senhaNova)) {
            throw new RuntimeException("A nova senha deve ter no mínimo 8 caracteres, com letra maiúscula, minúscula e número.");
        }
        if ($senhaNova !== $senhaConfirmacao) {
            throw new RuntimeException("A confirmação da nova senha não confere.");
        }

        $stmt = $conexao->prepare("SELECT senha FROM usuarios WHERE id = ? LIMIT 1");
        if (!$stmt) throw new RuntimeException("Não foi possível validar a senha atual.");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $usuarioSenha = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$usuarioSenha || !is_string($usuarioSenha["senha"] ?? null) || !password_verify($senhaAtual, $usuarioSenha["senha"])) {
            throw new RuntimeException("A senha atual está incorreta.");
        }

        $novaSenhaHash = password_hash($senhaNova, PASSWORD_DEFAULT);
        $stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ? LIMIT 1");
        if (!$stmt) throw new RuntimeException("Não foi possível atualizar a senha.");
        $stmt->bind_param("si", $novaSenhaHash, $id);
        if (!$stmt->execute()) throw new RuntimeException("Não foi possível atualizar a senha.");
        $stmt->close();
    }

    // Permite que usuários antigos da migração definam a pergunta de segurança.
    if ($pergunta !== "") {
        $respostaHash = password_hash(mb_strtolower($resposta, "UTF-8"), PASSWORD_DEFAULT);
        $stmt = $conexao->prepare("UPDATE usuarios SET pergunta_seguranca = ?, resposta_seguranca_hash = ? WHERE id = ? LIMIT 1");
        if (!$stmt) throw new RuntimeException("Não foi possível atualizar a recuperação de senha.");
        $stmt->bind_param("ssi", $pergunta, $respostaHash, $id);
        if (!$stmt->execute()) throw new RuntimeException("Não foi possível atualizar a recuperação de senha.");
        $stmt->close();
    }

    $conexao->commit();
    $_SESSION["usuario_nome"] = $nome;
    $conexao->close();
    header("Location: ../pages/dashboard.php?sucesso=1");
    exit;
} catch (Throwable $e) {
    $conexao->rollback();
    $conexao->close();
    header("Location: ../pages/dashboard.php?erro=" . urlencode($e->getMessage()));
    exit;
}
