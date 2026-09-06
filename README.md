# TopTurismo

Sistema web de turismo desenvolvido para permitir que usuários pesquisem destinos, realizem reservas de viagens e gerenciem sua conta.

O projeto possui também uma área administrativa para gerenciamento dos destinos cadastrados.

---

## Funcionalidades

### Usuário

- Cadastro de usuário
- Login e logout
- Recuperação de senha
- Redefinição de senha
- Exclusão da conta
- Visualização de destinos
- Pesquisa e filtragem de destinos
- Visualização de detalhes dos destinos
- Consulta da programação de viagens
- Escolha de transporte
- Escolha de classe
- Escolha de assento
- Seleção da quantidade de passageiros
- Cálculo do valor da reserva
- Realização de reservas
- Visualização das próprias reservas
- Cancelamento de reservas

### Administrador

- Login administrativo
- Acesso ao dashboard administrativo
- Visualização dos dados do sistema
- Cadastro de destinos
- Edição de destinos
- Exclusão de destinos
- Gerenciamento das informações dos destinos

---

## Tecnologias utilizadas

<div>

<img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/html5/html5-original.svg" width="50px" title="HTML5"/>
&nbsp;&nbsp;&nbsp;
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/css3/css3-original.svg" width="50px" title="CSS3"/>
&nbsp;&nbsp;&nbsp;
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/javascript/javascript-original.svg" width="50px" title="JavaScript"/>
&nbsp;&nbsp;&nbsp;
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-original.svg" width="50px" title="PHP"/>
&nbsp;&nbsp;&nbsp;
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/mysql/mysql-original.svg" width="50px" title="MySQL"/>
&nbsp;&nbsp;&nbsp;
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/bootstrap/bootstrap-original.svg" width="50px" title="Bootstrap"/>
&nbsp;&nbsp;&nbsp;
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/git/git-original.svg" width="50px" title="Git"/>
&nbsp;&nbsp;&nbsp;
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/github/github-original.svg" width="50px" title="GitHub"/>

</div>

---

## Estrutura do projeto

```text
TopTurismo/
│
├── public/
│   └── index.php
│
├── src/
│   │
│   ├── assets/
│   │   └── imagens/
│   │
│   ├── pages/
│   │   ├── login.php
│   │   ├── cadastro.php
│   │   ├── dashboard.php
│   │   ├── reservas.php
│   │   ├── esqueci-senha.php
│   │   ├── redefinir-senha.php
│   │   │
│   │   └── admin/
│   │       ├── dashboard.php
│   │       ├── adicionar-destino.php
│   │       ├── editar-destino.php
│   │       └── _navbar.php
│   │
│   └── php/
│       ├── conexao.php
│       ├── conexao.example.php
│       ├── login.php
│       ├── logout.php
│       ├── cadastro.php
│       ├── dashboard.php
│       ├── destinos.php
│       ├── destinos-data.php
│       ├── programacao.php
│       ├── programacao-dados.php
│       ├── criar-reserva.php
│       ├── minhas-reservas.php
│       ├── cancelar-reserva.php
│       ├── assentos-disponiveis.php
│       ├── usuario-logado.php
│       ├── excluir-conta.php
│       ├── esqueci-senha.php
│       ├── redefinir-senha.php
│       │
│       └── admin/
│           ├── auth.php
│           ├── criar-destino.php
│           ├── editar-destino.php
│           └── excluir-destino.php
│
├── sql/
│   └── topturismo.sql
│
├── .gitignore
├── .vscode/
└── README.md
```

---

# Instalação

## 1. Servidor local

Instale e execute um servidor local com Apache e MySQL/MariaDB.

O projeto pode ser executado utilizando XAMPP ou Laragon.

### XAMPP

Coloque a pasta do projeto dentro de:

```text
C:\xampp\htdocs\
```

Por exemplo:

```text
C:\xampp\htdocs\top-turismo\
```

Depois inicie:

- Apache
- MySQL

### Laragon

Coloque a pasta do projeto dentro da pasta:

```text
C:\laragon\www\
```

Depois inicie:

- Apache
- MySQL

---

# Configuração do banco de dados

O banco utilizado pelo projeto é:

```text
topturismo
```

O arquivo SQL completo está localizado em:

```text
sql/topturismo.sql
```

## Importando o banco

1. Abra o phpMyAdmin.
2. Acesse a opção **Importar**.
3. Selecione:

```text
sql/topturismo.sql
```

4. Execute a importação.

O arquivo cria o banco `topturismo` e suas tabelas.

As principais tabelas são:

```text
destinos
usuarios
reservas
recuperacoes_senha
```

---

# Configuração da conexão

A conexão principal está em:

```text
src/php/conexao.php
```

A configuração padrão utiliza:

```php
$servidor = '127.0.0.1' OU 'localhost';
$usuario  = 'root';
$senha    = '';
$banco    = 'topturismo';
$porta    = 3306;
```

Em uma instalação padrão do XAMPP ou Laragon, normalmente não é necessário alterar esses valores.

Caso o MySQL esteja utilizando outra porta ou senha, altere somente os dados necessários no arquivo de conexão.

O arquivo:

```text
src/php/conexao.example.php
```

serve como modelo de configuração.

---

# Cadastro e login

O usuário pode criar uma conta através da página:

```text
src/pages/cadastro.php
```

As senhas **não são armazenadas em texto puro**.

Durante o cadastro, o sistema utiliza:

```php
password_hash()
```

para criar um hash seguro da senha.

No login, a senha informada pelo usuário é verificada através de:

```php
password_verify()
```

Dessa forma, o sistema nunca precisa armazenar a senha original no banco.

---

# Recuperação de senha

O projeto possui um fluxo completo de recuperação e redefinição de senha.

O processo funciona da seguinte maneira:

```text
Esqueci minha senha
        ↓
Informar e-mail
        ↓
Encontrar usuário
        ↓
Pergunta de recuperação
        ↓
Validar resposta
        ↓
Autorizar recuperação
        ↓
Cadastrar nova senha
        ↓
Gerar novo hash
        ↓
Atualizar senha no banco
        ↓
Verificar o novo hash
        ↓
Voltar para o login
        ↓
Entrar com a nova senha
```

A recuperação utiliza um token temporário armazenado no banco para controlar o processo.

A autorização da recuperação possui tempo limitado e é invalidada depois que a senha é alterada.

---

# Reservas

Usuários autenticados podem realizar reservas de viagens.

Durante o processo de reserva, o sistema permite definir informações como:

- Destino
- Data da viagem
- Data de retorno
- Quantidade de passageiros
- Tipo de viagem
- Transporte
- Classe
- Assento
- Forma de pagamento
- Parcelas
- Horários
- Valor total

As reservas ficam vinculadas ao usuário que realizou a operação.

O usuário pode consultar suas reservas através da área:

```text
Minhas reservas
```

Também é possível cancelar uma reserva.

---

# Destinos

O sistema possui diversos destinos turísticos cadastrados.

Cada destino possui informações como:

- Nome
- Descrição
- Cidade
- Estado
- País
- Região
- Imagens
- Preço
- Avaliação
- Popularidade

As imagens utilizadas pelo sistema estão armazenadas em:

```text
src/assets/imagens/
```

---

# Área administrativa

O projeto possui uma área exclusiva para administradores.

O administrador pode:

- Acessar o dashboard administrativo
- Cadastrar destinos
- Editar destinos
- Excluir destinos
- Visualizar informações do sistema

A autenticação administrativa é controlada pelo arquivo:

```text
src/php/admin/auth.php
```

Usuários comuns não devem ter acesso às funções administrativas.

---

# Teste do sistema

Depois de configurar o servidor e o banco, recomenda-se realizar o seguinte teste:

```text
Cadastro
   ↓
Login
   ↓
Visualização dos destinos
   ↓
Escolha de um destino
   ↓
Criação de reserva
   ↓
Visualização da reserva
   ↓
Cancelamento da reserva
   ↓
Logout
```

Também deve ser testado o fluxo de recuperação:

```text
Login
   ↓
Esqueci minha senha
   ↓
Informar e-mail
   ↓
Responder pergunta de recuperação
   ↓
Cadastrar nova senha
   ↓
Mensagem de sucesso
   ↓
Login com a nova senha
```

---

# Banco de dados

O projeto utiliza o banco:

```text
topturismo
```

As principais tabelas são:

### usuarios

Armazena os dados dos usuários, incluindo o hash da senha.

### destinos

Armazena os destinos turísticos disponíveis.

### reservas

Armazena as reservas realizadas pelos usuários.

### recuperacoes_senha

Controla o processo temporário de recuperação e redefinição de senha.

---

# Arquivo de conexão

O arquivo:

```text
src/php/conexao.php
```

contém as informações utilizadas para conectar ao banco.

O arquivo:

```text
src/php/conexao.example.php
```

serve como exemplo de configuração.

Não é recomendado enviar senhas reais do banco para o GitHub.

---

# Acesso ao projeto

Após iniciar o Apache e o MySQL, o projeto pode ser acessado pelo endereço correspondente à pasta instalada.

Exemplo no XAMPP:

```text
http://localhost/top-turismo/
```

As páginas podem ser acessadas através da estrutura existente dentro de:

```text
src/pages/
```

---

# Desenvolvimento

O projeto foi desenvolvido com foco em uma aplicação web de turismo, integrando:

- Interface web
- Banco de dados
- Autenticação
- Gerenciamento de usuários
- Recuperação de senha
- Sistema de reservas
- Gerenciamento de destinos
- Área administrativa

---

## Autores e Colaboradores
- Maurício Alves
- David Lucas
- Fabiano Assunção

<hr>

Projeto Integrador **TopTurismo**.

