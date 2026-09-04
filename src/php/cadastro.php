<?php

session_start();
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require "conexao.php";

    $nome = trim($_POST["nome"] ?? "");
    $cpf = preg_replace("/\D/", "", $_POST["cpf"] ?? "");
    $data_nascimento = trim($_POST["data_nascimento"] ?? "");
    $genero = trim($_POST["genero"] ?? "");
    $email = mb_strtolower(trim($_POST["email"] ?? ""), "UTF-8");
    $telefone = preg_replace("/\D/", "", $_POST["telefone"] ?? "");
    $cidade = trim($_POST["cidade"] ?? "");
    $pergunta_recuperacao = trim($_POST["pergunta_recuperacao"] ?? "");
    $resposta_recuperacao = trim($_POST["resposta_recuperacao"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmar_senha = $_POST["confirmar_senha"] ?? "";

    $perguntasPermitidas = [
        "pet" => "Qual é o nome do seu pet?",
        "cidade_natal" => "Qual é a sua cidade natal?",
        "comida" => "Qual é a sua comida favorita?",
        "apelido" => "Qual era o seu apelido de infância?",
        "escola" => "Qual era o nome da sua primeira escola?"
    ];
    $generosPermitidos = ["Masculino", "Feminino", "Outro"];

    function validarCPF($cpf)
    {
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) return false;
        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($c = 0; $c < $t; $c++) $soma += $cpf[$c] * (($t + 1) - $c);
            $digito = ((10 * $soma) % 11) % 10;
            if ($cpf[$t] != $digito) return false;
        }
        return true;
    }

    function validarDataNascimentoMaiorIdade($data)
    {
        $nascimento = DateTime::createFromFormat("Y-m-d", $data);
        if (!$nascimento || $nascimento->format("Y-m-d") !== $data) return false;
        $hoje = new DateTime();
        if ($nascimento > $hoje) return false;
        $idade = $hoje->diff($nascimento)->y;
        return $idade >= 18 && $idade <= 120;
    }

    if ($nome === "" || mb_strlen($nome) < 3 || !preg_match("/^[\p{L}\s']+$/u", $nome)) {
        $erro = "Nome inválido. Use apenas letras e espaços (mínimo 3 caracteres).";
    } elseif (!validarCPF($cpf)) {
        $erro = "CPF inválido.";
    } elseif ($data_nascimento === "" || !validarDataNascimentoMaiorIdade($data_nascimento)) {
        $erro = "Data de nascimento inválida. É necessário ter 18 anos ou mais.";
    } elseif (!in_array($genero, $generosPermitidos, true)) {
        $erro = "Selecione um gênero válido.";
    } elseif ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";
    } elseif (strlen($telefone) < 10 || strlen($telefone) > 11) {
        $erro = "Telefone inválido. Informe DDD + número.";
    } elseif ($cidade === "" || mb_strlen($cidade) < 2) {
        $erro = "Cidade inválida.";
    } elseif (!array_key_exists($pergunta_recuperacao, $perguntasPermitidas)) {
        $erro = "Selecione uma pergunta de recuperação válida.";
    } elseif ($resposta_recuperacao === "" || mb_strlen($resposta_recuperacao) < 2) {
        $erro = "Informe uma resposta de recuperação válida.";
    } elseif (
        strlen($senha) < 8 ||
        !preg_match("/[a-z]/", $senha) ||
        !preg_match("/[A-Z]/", $senha) ||
        !preg_match("/[0-9]/", $senha)
    ) {
        $erro = "A senha deve ter no mínimo 8 caracteres, com letra maiúscula, minúscula e número.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    }

    if ($erro === "") {
        $stmtVerifica = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? OR cpf = ? LIMIT 1");

        if (!$stmtVerifica) {
            error_log("TopTurismo cadastro - erro no SELECT: " . $conexao->error);
            $erro = "Não foi possível validar os dados da conta. Verifique se o banco de dados está atualizado.";
        } else {
            $stmtVerifica->bind_param("ss", $email, $cpf);
            $stmtVerifica->execute();
            $existente = $stmtVerifica->get_result()->fetch_assoc();
            $stmtVerifica->close();

            if ($existente) $erro = "Já existe uma conta cadastrada com esse e-mail ou CPF.";
        }

        if ($erro === "") {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $resposta_hash = password_hash(mb_strtolower($resposta_recuperacao, 'UTF-8'), PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios
                (nome, cpf, data_nascimento, genero, email, telefone, cidade,
                 senha, pergunta_recuperacao, resposta_recuperacao_hash, tipo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'cliente')";

            $stmt = $conexao->prepare($sql);

            if (!$stmt) {
                error_log("TopTurismo cadastro - erro no INSERT prepare: " . $conexao->error);
                $erro = "Não foi possível concluir o cadastro. O banco de dados pode estar desatualizado. Execute a migração de recuperação de senha.";
            } else {
                $stmt->bind_param(
                    "ssssssssss",
                    $nome,
                    $cpf,
                    $data_nascimento,
                    $genero,
                    $email,
                    $telefone,
                    $cidade,
                    $senha_hash,
                    $pergunta_recuperacao,
                    $resposta_hash
                );

                if ($stmt->execute()) {
                    $novoId = $conexao->insert_id;
                    session_regenerate_id(true);
                    $_SESSION["usuario_id"] = $novoId;
                    $_SESSION["usuario_nome"] = $nome;
                    $_SESSION["usuario_tipo"] = "cliente";

                    $stmt->close();
                    $conexao->close();
                    header("Location: ../pages/dashboard.php?cadastro=sucesso");
                    exit;
                }

                error_log("TopTurismo cadastro - erro no INSERT execute: " . $stmt->error);
                $erro = "Não foi possível concluir o cadastro. Verifique os dados e tente novamente.";
                $stmt->close();
            }
        }
    }

    $conexao->close();

    if ($erro !== "") {
        require __DIR__ . "/../pages/cadastro.php";
        exit;
    }
}

header("Location: ../pages/cadastro.php");
exit;
