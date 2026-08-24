<?php

session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../pages/login.php");
    exit();
}

$id = (int) $_SESSION["usuario_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $cidade = trim($_POST["cidade"] ?? "");

    if ($nome === "" || $email === "" || $telefone === "" || $cidade === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $conexao->close();
        header("Location: ../pages/dashboard.php?erro=" . urlencode("Preencha os dados pessoais corretamente."));
        exit();
    }

    $sucesso = true;

    $stmt = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ?, cidade = ? WHERE id = ?");
    if (!$stmt) {
        $sucesso = false;
    } else {
        $stmt->bind_param("ssssi", $nome, $email, $telefone, $cidade, $id);
        $sucesso = $stmt->execute();
        $stmt->close();
    }

    $senhaAtual = $_POST["senha_atual"] ?? "";
    $senhaNova = $_POST["senha_nova"] ?? "";
    $senhaConfirmacao = $_POST["senha_confirmacao"] ?? "";
    $mensagemErro = null;

    if ($senhaAtual !== "" || $senhaNova !== "" || $senhaConfirmacao !== "") {
        if ($senhaAtual === "" || $senhaNova === "" || $senhaConfirmacao === "") {
            $sucesso = false;
            $mensagemErro = "Para alterar a senha, preencha os três campos de senha.";
        } elseif (strlen($senhaNova) < 6) {
            $sucesso = false;
            $mensagemErro = "A nova senha deve ter pelo menos 6 caracteres.";
        } elseif ($senhaNova !== $senhaConfirmacao) {
            $sucesso = false;
            $mensagemErro = "A confirmação da nova senha não confere.";
        } else {
            $stmtSenhaAtual = $conexao->prepare("SELECT senha FROM usuarios WHERE id = ? LIMIT 1");
            if (!$stmtSenhaAtual) {
                $sucesso = false;
                $mensagemErro = "Não foi possível validar a senha atual.";
            } else {
                $stmtSenhaAtual->bind_param("i", $id);
                $stmtSenhaAtual->execute();
                $usuarioSenha = $stmtSenhaAtual->get_result()->fetch_assoc();
                $stmtSenhaAtual->close();
                if (!$usuarioSenha || !password_verify($senhaAtual, $usuarioSenha["senha"])) {
                    $sucesso = false;
                    $mensagemErro = "A senha atual está incorreta.";
                } else {
                    $novaSenhaHash = password_hash($senhaNova, PASSWORD_DEFAULT);
                    $stmtSenha = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                    if (!$stmtSenha) {
                        $sucesso = false;
                        $mensagemErro = "Não foi possível atualizar a senha.";
                    } else {
                        $stmtSenha->bind_param("si", $novaSenhaHash, $id);
                        $sucesso = $stmtSenha->execute() && $sucesso;
                        $stmtSenha->close();
                    }
                }
            }
        }
    }

    $_SESSION["usuario_nome"] = $nome;
    $conexao->close();

    if (!$sucesso) {
        $mensagemErro = $mensagemErro ?? "Não foi possível salvar as alterações.";
        header("Location: ../pages/dashboard.php?erro=" . urlencode($mensagemErro));
        exit();
    }

    header("Location: ../pages/dashboard.php?sucesso=1");
    exit();
}

$conexao->close();

header("Location: ../pages/dashboard.php");
exit();
?>