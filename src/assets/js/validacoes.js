/**
 * Biblioteca de validações do TopTurismo.
 *
 * Estas funções são usadas no navegador para dar feedback imediato ao
 * usuário. Elas não substituem as validações do PHP: toda regra importante
 * também precisa ser conferida no servidor.
 */

// ============================================================
// VALIDADORES DE DADOS
// ============================================================

function validarNome(nome) {
    const valor = nome.trim();

    if (!valor) return "O nome é obrigatório.";
    if (valor.length < 3) return "O nome deve ter pelo menos 3 caracteres.";
    if (!/^[A-Za-zÀ-ÖØ-öø-ÿ\s']+$/.test(valor)) {
        return "O nome deve conter apenas letras e espaços.";
    }

    if (valor.trim().split(/\s+/).length < 2) {
        return "Digite o nome completo (nome e sobrenome).";
    }

    return null;
}

function validarEmail(email) {
    const valor = email.trim();

    if (!valor) return "O e-mail é obrigatório.";

    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    if (!regex.test(valor)) {
        return "Digite um e-mail válido.";
    }

    return null;
}

function validarTelefone(telefone) {
    const digitos = telefone.replace(/\D/g, "");

    if (!digitos) return "O telefone é obrigatório.";

    if (digitos.length < 10 || digitos.length > 11) {
        return "Telefone inválido. Use DDD + número.";
    }

    return null;
}

/**
 * Valida os dígitos verificadores do CPF.
 */
function validarCPF(cpf) {
    const digitos = cpf.replace(/\D/g, "");

    if (!digitos) return "O CPF é obrigatório.";
    if (digitos.length !== 11) return "O CPF deve ter 11 dígitos.";

    // *bloqueia CPFs compostos pelo mesmo dígito*
    if (/^(\d)\1{10}$/.test(digitos)) {
        return "CPF inválido.";
    }

    let soma = 0;

    // *calcula o primeiro dígito verificador*
    for (let i = 0; i < 9; i++) {
        soma += parseInt(digitos[i]) * (10 - i);
    }

    let resto = (soma * 10) % 11;

    if (resto === 10 || resto === 11) {
        resto = 0;
    }

    if (resto !== parseInt(digitos[9])) {
        return "CPF inválido.";
    }

    soma = 0;

    // *calcula o segundo dígito verificador*
    for (let i = 0; i < 10; i++) {
        soma += parseInt(digitos[i]) * (11 - i);
    }

    resto = (soma * 10) % 11;

    if (resto === 10 || resto === 11) {
        resto = 0;
    }

    if (resto !== parseInt(digitos[10])) {
        return "CPF inválido.";
    }

    return null;
}

/**
 * Confere se a data é válida e se o usuário possui idade mínima.
 *
 * Esta é uma validação de interface; a mesma regra é conferida no PHP.
 */
function validarDataNascimento(data) {
    if (!data) return "A data de nascimento é obrigatória.";

    const nascimento = new Date(data + "T00:00:00");

    if (isNaN(nascimento.getTime())) {
        return "Data inválida.";
    }

    const hoje = new Date();

    let idade = hoje.getFullYear() - nascimento.getFullYear();

    const aindaNaoFezAniversario =
        hoje.getMonth() < nascimento.getMonth()
        || (
            hoje.getMonth() === nascimento.getMonth()
            && hoje.getDate() < nascimento.getDate()
        );

    if (aindaNaoFezAniversario) {
        idade--;
    }

    if (nascimento > hoje) {
        return "A data de nascimento não pode ser no futuro.";
    }

    if (idade < 18) {
        return "É necessário ter 18 anos ou mais para se cadastrar.";
    }

    if (idade > 120) {
        return "Verifique a data de nascimento informada.";
    }

    return null;
}

/**
 * Exige uma senha com tamanho e complexidade mínimos.
 */
function validarSenha(senha) {
    if (!senha) return "A senha é obrigatória.";
    if (senha.length < 8) {
        return "A senha deve ter no mínimo 8 caracteres.";
    }

    if (!/[a-z]/.test(senha)) {
        return "A senha deve ter ao menos uma letra minúscula.";
    }

    if (!/[A-Z]/.test(senha)) {
        return "A senha deve ter ao menos uma letra maiúscula.";
    }

    if (!/[0-9]/.test(senha)) {
        return "A senha deve ter ao menos um número.";
    }

    return null;
}

function validarConfirmarSenha(senha, confirmarSenha) {
    if (!confirmarSenha) return "Confirme sua senha.";

    if (senha !== confirmarSenha) {
        return "As senhas não coincidem.";
    }

    return null;
}

function validarCampoObrigatorio(valor, nomeCampo) {
    if (!valor || !valor.trim()) {
        return `${nomeCampo} é obrigatório.`;
    }

    return null;
}

// ============================================================
// HELPERS DE INTERFACE
// ============================================================

/**
 * Mostra ou remove a mensagem de erro de um campo.
 */
function exibirErroCampo(input, mensagem) {
    let erroEl = input.parentElement.querySelector(".erro-campo");

    if (!erroEl) {
        erroEl = document.createElement("div");
        erroEl.className = "erro-campo";
        erroEl.style.color = "#dc2626";
        erroEl.style.fontSize = "0.85rem";
        erroEl.style.marginTop = "4px";
        input.parentElement.appendChild(erroEl);
    }

    if (mensagem) {
        erroEl.textContent = mensagem;
        erroEl.style.display = "block";
        input.classList.add("campo-invalido");
        input.style.borderColor = "#dc2626";
        return;
    }

    erroEl.textContent = "";
    erroEl.style.display = "none";
    input.classList.remove("campo-invalido");
    input.style.borderColor = "";
}

/**
 * Formata CPF enquanto o usuário digita.
 */
function aplicarMascaraCPF(input) {
    input.addEventListener("input", () => {
        let valor = input.value.replace(/\D/g, "").slice(0, 11);

        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");

        input.value = valor;
    });
}

/**
 * Formata telefone brasileiro enquanto o usuário digita.
 */
function aplicarMascaraTelefone(input) {
    input.addEventListener("input", () => {
        let valor = input.value.replace(/\D/g, "").slice(0, 11);

        if (valor.length > 10) {
            valor = valor.replace(
                /(\d{2})(\d{5})(\d{4})/,
                "($1) $2-$3"
            );
        } else if (valor.length > 5) {
            valor = valor.replace(
                /(\d{2})(\d{4})(\d{0,4})/,
                "($1) $2-$3"
            );
        } else if (valor.length > 2) {
            valor = valor.replace(
                /(\d{2})(\d{0,5})/,
                "($1) $2"
            );
        } else {
            valor = valor.replace(/(\d{0,2})/, "($1");
        }

        input.value = valor;
    });
}
