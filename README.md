# TopTurismo

> Sistema de reserva de viagens web desenvolvido com PHP, MySQL, JavaScript, HTML, CSS e Bootstrap.

O **TopTurismo** é uma aplicação web desenvolvida como projeto integrador no curso Técnico em Informática para Internet com o objetivo de simular uma plataforma de turismo, permitindo que usuários consultem destinos nacionais, realizem reservas de viagens e acompanhem suas viagens.

O sistema também possui uma área administrativa, permitindo o gerenciamento dos destinos disponíveis na plataforma.

## Sobre o projeto

O TopTurismo foi desenvolvido com foco no aprendizado e na aplicação prática de conceitos de desenvolvimento web, integração entre frontend e backend, banco de dados relacional, autenticação de usuários e operações CRUD.

### Objetivos

- Desenvolver uma aplicação web completa.
- Praticar integração entre PHP e MySQL.
- Trabalhar com autenticação e sessões.
- Utilizar operações CRUD.
- Desenvolver um sistema de reservas.
- Aplicar validações no frontend e backend.
- Trabalhar com organização de arquivos e separação de responsabilidades.
- Criar uma interface responsiva utilizando Bootstrap.
- Aplicar conceitos básicos de segurança.

## Funcionalidades

### Usuários

- Cadastro de usuários.
- Login e logout.
- Controle de sessão.
- Visualização dos dados pessoais.
- Alteração de senha.
- Exclusão da conta.
- Recuperação de senha.
- Redefinição de senha por palavra-chave de recuperação.

### Destinos

- Listagem de destinos nacionais.
- Cards com informações dos destinos.
- Preço por passageiro.
- Avaliação dos destinos.
- Filtro de destinos.
- Busca por destinos.
- Informações detalhadas.
- Galeria com múltiplas imagens.
- Integração com localização através do Google Maps.

A base inicial possui 16 destinos brasileiros.

### Reservas

O usuário autenticado pode realizar uma reserva seguindo o fluxo:

```text
Escolha do destino
        ↓
Quantidade de passageiros
        ↓
Tipo de viagem
        ↓
Data da viagem
        ↓
Transporte
        ↓
Classe
        ↓
Escolha dos assentos
        ↓
Revisão da reserva
        ↓
Pagamento
        ↓
Confirmação
```

As reservas são armazenadas no banco de dados e posteriormente exibidas na área **Minhas Viagens**.

Também é possível cancelar uma reserva.

### Área administrativa

Usuários com perfil de administrador possuem acesso a recursos exclusivos:

- Dashboard administrativo.
- Cadastro de destinos.
- Edição de destinos.
- Exclusão de destinos.
- Gerenciamento dos destinos disponíveis.
- Upload de imagens dos destinos.

Quando um novo destino é cadastrado pelo administrador, seus dados passam a fazer parte da base de destinos utilizada pelo sistema.

## Tecnologias utilizadas

<p align="left">
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg" width="45" alt="HTML5" title="HTML5"/>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" width="45" alt="CSS3" title="CSS3"/>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" width="45" alt="JavaScript" title="JavaScript"/>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="45" alt="PHP" title="PHP"/>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" width="45" alt="MySQL" title="MySQL"/>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/bootstrap/bootstrap-original.svg" width="45" alt="Bootstrap" title="Bootstrap"/>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/git/git-original.svg" width="45" alt="Git" title="Git"/>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/github/github-original.svg" width="45" alt="GitHub" title="GitHub"/>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/vscode/vscode-original.svg" width="45" alt="Visual Studio Code" title="Visual Studio Code"/>
</p>

## Estrutura do projeto

```text
top-turismo/
│
├── admin/
│   ├── adicionar-destino.php
│   ├── dashboard.php
│   ├── editar-destino.php
│   └── _navbar.php
│
├── assets/
│   ├── css/
│   │   ├── bootstrap/
│   │   ├── admin.css
│   │   ├── login-cadastro.css
│   │   └── style.css
│   │
│   ├── imagens/
│   │   ├── destinos/
│   │   ├── logo.png
│   │   ├── logo-white.png
│   │   ├── google-maps.svg
│   │   └── top-turismo-preview.gif
│   │
│   └── js/
│       ├── bootstrap/
│       ├── reservas.js
│       ├── script.js
│       └── validacoes.js
│
├── pages/
│   ├── cadastro.php
│   ├── dashboard.php
│   ├── esqueci-senha.php
│   ├── login.php
│   ├── redefinir-senha.php
│   └── reservas.php
│
├── php/
│   ├── admin/
│   │   ├── auth.php
│   │   ├── criar-destino.php
│   │   ├── editar-destino.php
│   │   └── excluir-destino.php
│   │
│   ├── assentos-disponiveis.php
│   ├── cadastro.php
│   ├── cancelar-reserva.php
│   ├── conexao.example.php
│   ├── criar-reserva.php
│   ├── dashboard.php
│   ├── destinos-data.php
│   ├── destinos.php
│   ├── esqueci-senha.php
│   ├── excluir-conta.php
│   ├── login.php
│   ├── logout.php
│   ├── minhas-reservas.php
│   ├── programacao.php
│   ├── programacao-dados.php
│   ├── redefinir-senha.php
│   └── usuario-logado.php
│
├── sql/
│   └── topturismo-backup.sql
│
├── .gitignore
├── index.php
└── README.md
```

## Banco de dados

O projeto utiliza **MySQL** como banco de dados.

O arquivo:

```text
sql/topturismo-backup.sql
```

contém a estrutura inicial do banco e os destinos cadastrados.

### Principais tabelas

#### `usuarios`

Armazena os dados dos usuários cadastrados, incluindo informações pessoais, credenciais e tipo de usuário.

O campo `tipo` diferencia usuários comuns de administradores:

```text
cliente
admin
```

#### `destinos`

Armazena as informações dos destinos turísticos, como:

- Nome.
- Descrição.
- Cidade.
- Estado.
- País.
- Região.
- Imagens.
- Preço.
- Avaliação.
- Popularidade.

#### `reservas`

Armazena as reservas realizadas pelos usuários, incluindo:

- Usuário.
- Destino.
- Data da viagem.
- Data de volta.
- Tipo de viagem.
- Quantidade de passageiros.
- Transporte.
- Classe.
- Assentos.
- Forma de pagamento.
- Parcelas.
- Horários.
- Duração.
- Valor total.
- Status.

## Instalação

### Requisitos

Para executar o projeto localmente, recomenda-se:

- XAMPP.
- Apache.
- MySQL.
- PHP.
- Navegador atualizado.

### 1. Clonar o projeto

```bash
git clone https://github.com/seu-usuario/top-turismo.git
```

Entre na pasta:

```bash
cd top-turismo
```

### 2. Configurar o servidor

Coloque a pasta do projeto no diretório do servidor Apache.

No XAMPP, normalmente:

```text
C:\xampp\htdocs\
```

A estrutura ficará semelhante a:

```text
C:\xampp\htdocs\top-turismo\
```

### 3. Criar o banco de dados

Abra o **phpMyAdmin** e importe:

```text
sql/topturismo-backup.sql
```

O script contém a estrutura inicial necessária para executar o projeto.

### 4. Configurar a conexão

O projeto possui um arquivo de exemplo:

```text
php/conexao.example.php
```

Faça uma cópia e renomeie para:

```text
php/conexao.php
```

Depois configure os dados do seu MySQL:

```php
$servidor = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'topturismo';
$porta = 3306;
```

> O arquivo `php/conexao.php` deve permanecer fora do GitHub caso contenha credenciais locais. O `.gitignore` do projeto já deve ser utilizado para evitar o envio dessas informações.

## Executando o projeto

Com **Apache** e **MySQL** ligados no XAMPP, abra:

```text
http://localhost/top-turismo/
```

A página inicial apresenta os destinos disponíveis.

Para acessar o sistema de usuários:

```text
http://localhost/top-turismo/pages/login.php
```

## Área administrativa

O sistema possui dois níveis de usuário:

| Tipo | Permissões |
|---|---|
| Cliente | Navegar, cadastrar-se, reservar e gerenciar suas viagens |
| Administrador | Recursos do cliente + gerenciamento de destinos |

O administrador pode:

```text
Dashboard
   ↓
Adicionar destino
   ↓
Editar destino
   ↓
Excluir destino
```

Para utilizar a área administrativa, é necessário possuir um usuário com:

```text
tipo = admin
```

no banco de dados.

## Segurança

O projeto utiliza algumas práticas básicas de segurança:

- Senhas armazenadas com `password_hash()`.
- Verificação de senhas com `password_verify()`.
- Controle de acesso por sessão.
- Proteção das páginas administrativas.
- Separação das credenciais do banco através do `.gitignore`.
- Validações no frontend e backend.
- Uso de consultas preparadas em operações sensíveis.
- Armazenamento seguro de informações de recuperação.

### Pagamento

O pagamento presente no sistema possui finalidade exclusivamente acadêmica.

Nenhuma transação financeira real é realizada.

Dados reais de cartão não devem ser utilizados.

## Recuperação de senha

O sistema possui um fluxo de recuperação baseado em:

```text
Esqueci minha senha
        ↓
E-mail
        ↓
Palavra-chave de recuperação
        ↓
Nova senha
```

A palavra-chave de recuperação é armazenada de forma protegida no banco de dados.

## Organização do código

O projeto procura separar as responsabilidades entre frontend e backend.

O JavaScript é utilizado principalmente para comportamentos de interface, como:

- Seleção de assentos.
- Navegação entre etapas.
- Máscaras.
- Interações da página.
- Comportamentos visuais.

O PHP fica responsável pelas regras principais do sistema, como:

- Autenticação.
- Sessões.
- Comunicação com o banco.
- Cadastro de usuários.
- Criação de reservas.
- Validações.
- Gerenciamento dos destinos.

Essa divisão facilita a manutenção do projeto e evita concentrar regras importantes somente no JavaScript.

## Responsividade

A interface foi desenvolvida para diferentes tamanhos de tela utilizando:

- Bootstrap.
- CSS personalizado.
- Grid responsivo.
- Componentes adaptáveis.

O objetivo é proporcionar uma experiência adequada em computadores e dispositivos móveis.

## Destinos disponíveis

A versão inicial possui destinos nacionais, incluindo:

- Maceió.
- Rio de Janeiro.
- Salvador.
- Gramado.
- São Paulo.
- Foz do Iguaçu.
- Lençóis Maranhenses.
- Manaus.
- Florianópolis.
- Curitiba.
- Fernando de Noronha.
- Campo Grande.
- Fortaleza.
- Goiânia.
- Jericoacoara.
- Porto Alegre.

Novos destinos podem ser adicionados através da área administrativa.

## Fluxo de teste

Para testar o sistema completo:

```text
1. Acessar o TopTurismo
2. Criar uma conta
3. Fazer login
4. Escolher um destino
5. Abrir os detalhes
6. Iniciar uma reserva
7. Escolher passageiros
8. Selecionar ida/volta
9. Definir data
10. Escolher transporte
11. Escolher classe
12. Selecionar assentos
13. Revisar a reserva
14. Simular o pagamento
15. Confirmar
16. Acessar "Minhas Viagens"
17. Conferir a reserva
18. Testar o cancelamento
```

## Objetivo do projeto

O TopTurismo foi desenvolvido como projeto integrador para aplicar conhecimentos de desenvolvimento web e integração de sistemas com banco de dados.

Durante o desenvolvimento foram trabalhados conceitos como:

- Desenvolvimento frontend.
- Desenvolvimento backend.
- PHP.
- MySQL.
- SQL.
- Sessões.
- Autenticação.
- Validação de formulários.
- Relacionamento entre tabelas.
- Manipulação de arquivos.
- JavaScript.
- Bootstrap.
- Responsividade.
- Git e GitHub.
- Organização de projetos.

## Possíveis melhorias futuras

- Integração com APIs de mapas.
- Sistema de avaliações.
- Integração com APIs de companhias aéreas.
- Integração com gateway de pagamento.
- Envio de e-mails.
- Recuperação de senha por e-mail.
- Dashboard administrativo com estatísticas.
- Relatórios de reservas.
- Sistema de cupons.
- Histórico de alterações.
- Melhorias de acessibilidade.
- API própria para os destinos.

## Projeto Integrador

**TopTurismo**  
Sistema web de turismo e reservas desenvolvido no curso Técnico em informática para Internet pelo Senac.

### Stack

```text
HTML5 + CSS3
      ↓
JavaScript + Bootstrap
      ↓
PHP
      ↓
MySQL
```

## Autores e Colaboradores

- Maurício Alves
- David Lucas
- Fabiano Assunção