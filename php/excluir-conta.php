<?php
# Inclui o arquivo de conexão
include('conexao.php');
# Executa a função de iniciar sessão do usuário
session_start();
# Caso o usuário não esteja logado, redireciona para a página de login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.html");
    exit();
}
# Armazena o ID do usuário logado na variável ID
$id = $_SESSION['id_usuario'];
# Exclui a conta usando Prepared Statements (Seguro)
$stmt = $conexao->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    # Se excluiu com sucesso, destrói a sessão e desloga o usuário
    session_destroy();
    header("Location: ../../public/index.html");
    exit();
} else {
    echo "Erro ao excluir conta: " . $conexao->error;
}
$stmt->close();
$conexao->close();
?>