<?php
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require "conexao.php";

    // pega os dados enviados pelo usuario no formulário
    $nome = $_POST["nome"];
    $cpf = $_POST["cpf"];
    $data_nascimento = $_POST["data_nascimento"];
    $genero = $_POST["genero"];
    $email = $_POST["email"];
    $telefone = $_POST["telefone"];
    $cidade = $_POST["cidade"];
    $senha = $_POST["senha"];
    $confirmar_senha = $_POST["confirmar_senha"];

    if ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        // criptografa a senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nome, cpf, data_nascimento, genero, email, telefone, cidade, senha, tipo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'cliente')";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssssssss", $nome, $cpf, $data_nascimento, $genero, $email, $telefone, $cidade, $senha_hash);

        if ($stmt->execute()) {
           header("Location: ../pages/login.php");
            exit;
        } else {
            $erro = "Erro ao cadastrar. Talvez o e-mail ou CPF já estejam em uso.";
        }
    }
}
?>