<?php
# Inclui o arquivo de conexão
include('conexao.php');
# Executa a função de iniciar sessão do usuário
session_start();
# Caso o usuário não esteja logado, redireciona para a página de login
if (!isset($_SESSION['id_usuario'])) { header("Location: ../pages/login.php"); exit(); }
# Armazena o ID do usuário logado na variável ID
$id = $_SESSION['id_usuario'];
# Verifica se o usuário enviou o formulário de alteração (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $nascimento = $_POST['nascimento'];
    # Atualização de dados básicos usando Prepared Statements (Seguro)
    $stmt = $conexao->prepare("UPDATE usuarios SET nome = ?, email = ?, nascimento = ? WHERE id_usuario = ?");
    $stmt->bind_param("sssi", $nome, $email, $nascimento, $id);
    $stmt->execute();
    $stmt->close();
    # Se o usuário digitou uma nova senha, atualiza também a senha
    if (!empty($_POST['senha_nova'])) {
        $novaSenha_segura = password_hash($_POST['senha_nova'], PASSWORD_DEFAULT);
        $stmt_senha = $conexao->prepare("UPDATE usuarios SET senha_segura = ? WHERE id_usuario = ?");
        $stmt_senha->bind_param("si", $novaSenha_segura, $id);
        $stmt_senha->execute();
        $stmt_senha->close();
    }
    # Após salvar, redireciona para a mesma página para evitar reenvio de formulário
    header("Location: ../pages/dashboard.php?sucesso=1"); exit();
}
# Se o usuário apenas acessou a página (GET), redireciona para o HTML do dashboard
header("Location: ../pages/dashboard.php"); exit();
?>