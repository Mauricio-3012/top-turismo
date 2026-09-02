# TopTurismo

> Sistema web de turismo e reservas desenvolvido com PHP, MySQL, JavaScript, HTML, CSS e Bootstrap.

O **TopTurismo** é uma aplicação web desenvolvida como projeto integrador do curso Técnico em Informática para Internet. O sistema simula uma plataforma de turismo na qual usuários podem consultar destinos nacionais, realizar reservas e acompanhar suas viagens.

O projeto também possui uma área administrativa para gerenciamento dos destinos.

---

## Funcionalidades

### Usuários

- Cadastro de usuários.
- Login e logout.
- Controle de sessão.
- Atualização de dados pessoais.
- Alteração de senha.
- Exclusão de conta.
- Recuperação e redefinição de senha.

### Recuperação de senha

O fluxo foi organizado seguindo o padrão utilizado no **movieAppMat**, mantendo a recuperação dentro da própria tela de login:

```text
Login
  ↓
Esqueci minha senha
  ↓
E-mail da conta
  ↓
Palavra-chave de recuperação
  ↓
Nova senha
  ↓
Login novamente
```

A palavra-chave e a senha são armazenadas com `password_hash()` e verificadas com `password_verify()`.

A etapa de recuperação possui expiração de 15 minutos e a sessão é regenerada após a validação da palavra-chave.

### Destinos

- Listagem de destinos nacionais.
- Busca e filtros.
- Cards com informações dos destinos.
- Avaliação e popularidade.
- Galeria de imagens.
- Informações detalhadas.
- Localização através do Google Maps.

### Reservas

O usuário autenticado pode realizar uma reserva seguindo o fluxo:

```text
Destino
  ↓
Passageiros
  ↓
Tipo de viagem
  ↓
Data
  ↓
Transporte
  ↓
Classe
  ↓
Assentos
  ↓
Revisão
  ↓
Pagamento
  ↓
Confirmação
```

As reservas ficam armazenadas no banco e podem ser consultadas em **Minhas Viagens**.

Também é possível cancelar uma reserva.

### Área administrativa

Administradores podem:

- Acessar o dashboard.
- Adicionar destinos.
- Editar destinos.
- Excluir destinos.
- Gerenciar as imagens dos destinos.

---

## Tecnologias

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL
- Bootstrap
- Git
- GitHub
- Visual Studio Code

---

## Estrutura do projeto

A estrutura segue uma organização semelhante à utilizada no **movieAppMat**, separando arquivos públicos, páginas, lógica PHP e recursos estáticos.

```text
top-turismo/
│
├── public/
│   └── index.php
│
├── src/
│   ├── admin/
│   │   ├── _navbar.php
│   │   ├── adicionar-destino.php
│   │   ├── dashboard.php
│   │   └── editar-destino.php
│   │
│   ├── assets/
│   │   ├── css/
│   │   │   ├── bootstrap/
│   │   │   ├── admin.css
│   │   │   ├── login-cadastro.css
│   │   │   └── style.css
│   │   │
│   │   ├── imagens/
│   │   │   └── ...
│   │   │
│   │   └── js/
│   │       ├── reservas.js
│   │       ├── script.js
│   │       └── validacoes.js
│   │
│   ├── pages/
│   │   ├── cadastro.php
│   │   ├── dashboard.php
│   │   ├── login.php
│   │   └── reservas.php
│   │
│   └── php/
│       ├── admin/
│       │   ├── auth.php
│       │   ├── criar-destino.php
│       │   ├── editar-destino.php
│       │   └── excluir-destino.php
│       │
│       ├── assentos-disponiveis.php
│       ├── cadastro.php
│       ├── cancelar-reserva.php
│       ├── conexao.example.php
│       ├── criar-reserva.php
│       ├── dashboard.php
│       ├── destinos-data.php
│       ├── destinos.php
│       ├── excluir-conta.php
│       ├── login.php
│       ├── logout.php
│       ├── minhas-reservas.php
│       ├── processar_nova_senha.php
│       ├── programacao-dados.php
│       ├── programacao.php
│       └── usuario-logado.php
│
├── sql/
│   └── topturismo-backup.sql
│
├── .gitignore
└── README.md
```

### Responsabilidades

```text
public/
    Ponto de entrada público da aplicação.

src/pages/
    Telas exibidas ao usuário.

src/php/
    Regras de negócio, autenticação e comunicação com o banco.

src/php/admin/
    Operações administrativas.

src/admin/
    Telas da área administrativa.

src/assets/
    CSS, JavaScript, imagens e outros recursos visuais.

sql/
    Estrutura e dados iniciais do banco.
```

---

## Banco de dados

O projeto utiliza **MySQL**.

O arquivo:

```text
sql/topturismo-backup.sql
```

contém a estrutura inicial do banco.

### Principais tabelas

#### `usuarios`

Armazena os dados dos usuários, incluindo:

- Nome.
- CPF.
- Data de nascimento.
- Gênero.
- E-mail.
- Telefone.
- Cidade.
- Senha.
- Hash da palavra-chave de recuperação.
- Tipo de usuário.

Os tipos disponíveis são:

```text
cliente
admin
```

#### `destinos`

Armazena os dados dos destinos turísticos, incluindo informações de localização, descrição, imagens, preço, avaliação e popularidade.

#### `reservas`

Armazena as reservas realizadas pelos usuários, incluindo destino, passageiros, datas, transporte, classe, assentos, pagamento, valor e status.

---

## Instalação

### Requisitos

- XAMPP ou servidor Apache equivalente.
- PHP.
- MySQL.
- Navegador atualizado.

### 1. Clonar o projeto

```bash
git clone https://github.com/seu-usuario/top-turismo.git
cd top-turismo
```

### 2. Configurar o servidor

Coloque o projeto dentro do diretório do Apache.

No XAMPP:

```text
C:\xampp\htdocs\
```

Exemplo:

```text
C:\xampp\htdocs\top-turismo\
```

### 3. Criar o banco

Abra o **phpMyAdmin**, crie o banco `topturismo` e importe:

```text
sql/topturismo-backup.sql
```

### 4. Configurar a conexão

Copie:

```text
src/php/conexao.example.php
```

para:

```text
src/php/conexao.php
```

Configure os dados do seu MySQL:

```php
$servidor = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'topturismo';
$porta = 3306;
```

O arquivo `conexao.php` está no `.gitignore` para evitar o envio de credenciais locais ao GitHub.

---

## Executando

Com Apache e MySQL ligados, acesse:

```text
http://localhost/top-turismo/public/
```

Login:

```text
http://localhost/top-turismo/src/pages/login.php
```

---

## Área administrativa

O sistema possui dois tipos de usuário:

| Tipo | Permissões |
|---|---|
| Cliente | Navegar, reservar e gerenciar suas viagens |
| Administrador | Recursos do cliente + gerenciamento de destinos |

Para transformar um usuário em administrador, altere o campo `tipo` no banco para:

```text
admin
```

---

## Segurança

O projeto aplica algumas práticas básicas de segurança:

- `password_hash()` para armazenamento de senhas.
- `password_verify()` para validação.
- Consultas preparadas com MySQLi.
- Controle de acesso por sessão.
- Regeneração de sessão durante autenticação e recuperação.
- Expiração do fluxo de recuperação de senha.
- Validação no frontend e backend.
- Credenciais do banco separadas do código versionado.

O sistema possui finalidade acadêmica. O módulo de pagamento é apenas uma simulação e não deve receber dados reais de cartão.

---

## Fluxo de teste

```text
1. Abrir o TopTurismo
2. Criar uma conta
3. Fazer login
4. Consultar os destinos
5. Iniciar uma reserva
6. Escolher passageiros, data, transporte e assentos
7. Finalizar a simulação
8. Conferir a reserva em Minhas Viagens
9. Testar o cancelamento
10. Sair da conta
11. Testar "Esqueci minha senha"
12. Informar o e-mail
13. Informar a palavra-chave
14. Criar uma nova senha
15. Fazer login com a nova senha
```

---

## Melhorias futuras

- Recuperação por e-mail.
- Integração com APIs de mapas.
- Integração com companhias aéreas.
- Gateway de pagamento real.
- Sistema de avaliações.
- Dashboard administrativo com estatísticas.
- Relatórios de reservas.
- Sistema de cupons.
- Melhorias de acessibilidade.
- API própria para destinos.

---

## Projeto Integrador

**TopTurismo**  
Sistema web de turismo e reservas desenvolvido no curso Técnico em Informática para Internet pelo Senac.

### Stack

```text
HTML + CSS + JavaScript + Bootstrap
                 ↓
                PHP
                 ↓
               MySQL
```

## Autores

- Maurício Alves
- David Lucas
- Fabiano Assunção
