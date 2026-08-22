<?php

session_start();
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require "conexao.php";

    // dados recebidos do formulário
    $nome = trim($_POST["nome"] ?? "");
    $cpf = preg_replace("/\D/", "", $_POST["cpf"] ?? "");
    $data_nascimento = trim($_POST["data_nascimento"] ?? "");
    $genero = trim($_POST["genero"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = preg_replace("/\D/", "", $_POST["telefone"] ?? "");
    $cidade = trim($_POST["cidade"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmar_senha = $_POST["confirmar_senha"] ?? "";

    $generosPermitidos = ["Masculino", "Feminino", "Outro"];


     // valida o CPF usando os dois dígitos verificadores
    function validarCPF($cpf)
    {
        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;

            for ($c = 0; $c < $t; $c++) {
                $soma += $cpf[$c] * (($t + 1) - $c);
            }

            $digito = ((10 * $soma) % 11) % 10;

            if ($cpf[$t] != $digito) {
                return false;
            }
        }

        return true;
    }

    // formato, faixa etária e datas impossíveis
    function validarDataNascimentoMaiorIdade($data)
    {
        $nascimento = DateTime::createFromFormat("Y-m-d", $data);

        if (!$nascimento || $nascimento->format("Y-m-d") !== $data) {
            return false;
        }

        $hoje = new DateTime();

        if ($nascimento > $hoje) {
            return false;
        }

        $idade = $hoje->diff($nascimento)->y;

        return $idade >= 18 && $idade <= 120;
    }

    // validações de negócio
    if (
        $nome === ""
        || mb_strlen($nome) < 3
        || !preg_match("/^[\p{L}\s']+$/u", $nome)
    ) {
        $erro = "Nome inválido. Use apenas letras e espaços (mínimo 3 caracteres).";
    } elseif (!validarCPF($cpf)) {
        $erro = "CPF inválido.";
    } elseif (
        $data_nascimento === ""
        || !validarDataNascimentoMaiorIdade($data_nascimento)
    ) {
        $erro = "Data de nascimento inválida. É necessário ter 18 anos ou mais.";
    } elseif (!in_array($genero, $generosPermitidos, true)) {
        $erro = "Selecione um gênero válido.";
    } elseif ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";
    } elseif (strlen($telefone) < 10 || strlen($telefone) > 11) {
        $erro = "Telefone inválido. Informe DDD + número.";
    } elseif ($cidade === "" || mb_strlen($cidade) < 2) {
        $erro = "Cidade inválida.";
    } elseif (
        strlen($senha) < 8
        || !preg_match("/[a-z]/", $senha)
        || !preg_match("/[A-Z]/", $senha)
        || !preg_match("/[0-9]/", $senha)
    ) {
        $erro = "A senha deve ter no mínimo 8 caracteres, com letra maiúscula, minúscula e número.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    }

    if ($erro === "") {
        // evita contas duplicadas antes do INSERT
        $sqlVerifica = "SELECT id FROM usuarios WHERE email = ? OR cpf = ? LIMIT 1";
        $stmtVerifica = $conexao->prepare($sqlVerifica);

        if (!$stmtVerifica) {
            $erro = "Não foi possível validar os dados da conta.";
        } else {
            $stmtVerifica->bind_param("ss", $email, $cpf);
            $stmtVerifica->execute();

            $existente = $stmtVerifica->get_result()->fetch_assoc();
            $stmtVerifica->close();

            if ($existente) {
                $erro = "Já existe uma conta cadastrada com esse e-mail ou CPF.";
            }
        }

        if ($erro === "") {
            // criptografa senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $sql = "
                INSERT INTO usuarios
                    (nome, cpf, data_nascimento, genero, email, telefone, cidade, senha, tipo)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, 'cliente')
            ";

            $stmt = $conexao->prepare($sql);

            if (!$stmt) {
                $erro = "Erro ao preparar o cadastro.";
            } else {
                $stmt->bind_param(
                    "ssssssss",
                    $nome,
                    $cpf,
                    $data_nascimento,
                    $genero,
                    $email,
                    $telefone,
                    $cidade,
                    $senha_hash
                );

                if ($stmt->execute()) {
                    $novoId = $conexao->insert_id;

                    // o usuário já entra autenticado após o cadastro
                    session_regenerate_id(true);
                    $_SESSION["usuario_id"] = $novoId;
                    $_SESSION["usuario_nome"] = $nome;
                    $_SESSION["usuario_tipo"] = "cliente";

                    $stmt->close();
                    $conexao->close();

                    header("Location: ../pages/dashboard.php?cadastro=sucesso");
                    exit;
                }

                $erro = "Erro ao cadastrar. Tente novamente em instantes.";
                $stmt->close();
            }
        }
    }

    $conexao->close();

    if ($erro !== "") {
        // reabre a página para exibir a mensagem sem criar uma nova rota
        require __DIR__ . "/../pages/cadastro.php";
        exit;
    }
}

// o cadastro deve ser enviado por POST
header("Location: ../pages/cadastro.php");
exit;
?>
