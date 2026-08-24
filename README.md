<section>
  <div>
  <h1>TopTurismo - Sistema de Reserva de Viagens</h1>
  <p>TopTurismo é um site de reservas de viagens com front-end em HTML, CSS, JavaScript e Bootstrap, e back-end em PHP com banco de dados MySQL. A plataforma oferece uma interface moderna para navegar por destinos turísticos, visualizar imagens e simular reservas, além de um sistema completo de conta de usuário: cadastro, login, dashboard com edição de dados pessoais e exclusão de conta.</p>
  </div>
</section>

<section>
  <div>
    <h2>Funcionalidades</h2>
  <ul>
    <li>Exibição de destinos turísticos em um carrossel interativo</li>
    <li>Cards informativos com imagens e descrições dos destinos</li>
    <li>Sistema de contas de usuário:
      <ul>
        <li>Cadastro com nome, CPF, data de nascimento, gênero, e-mail, telefone, cidade e senha</li>
        <li>Senhas armazenadas com hash (<code>password_hash</code>/<code>password_verify</code>)</li>
        <li>Login com validação de e-mail e senha e controle de sessão (<code>$_SESSION</code>)</li>
        <li>Logout com destruição completa da sessão</li>
      </ul>
    </li>
    <li>Dashboard do usuário (Meu Perfil):
      <ul>
        <li>Exibição dos dados do usuário logado (nome, e-mail, telefone e cidade)</li>
        <li>Edição e atualização dos dados pessoais</li>
        <li>Alteração de senha</li>
        <li>Exclusão de conta com confirmação em modal</li>
      </ul>
    </li>
    <li>Formulário de reserva (acessível apenas para usuários logados) com:
      <ul>
        <li>Preenchimento do nome do passageiro</li>
        <li>Escolha do destino</li>
        <li>Seleção da quantidade de passageiros</li>
        <li>Escolha do tipo de assento</li>
        <li>Cálculo automático e exibição do resumo da viagem</li>
      </ul>
    </li>
    <li>Suporte a tema claro e escuro (dark mode)</li>
    <li>Layout totalmente responsivo (desktop e mobile)</li>
    <li>Interface moderna com uso de Bootstrap e Bootstrap Icons</li>
  </ul>
  </div>
</section>

<section>
  <div>
    <h2>Em desenvolvimento</h2>
    <ul>
      <li>Fluxo de recuperação de senha ("Esqueci minha senha" / redefinição por token) — as telas já existem em <code>pages/esqueci-senha.php</code> e <code>pages/redefinir-senha.php</code>, mas o processamento correspondente em <code>php/</code> ainda precisa ser implementado</li>
    </ul>
  </div>
</section>

<section>
  <div align='center'>
  <h2>Tecnologias utilizadas</h2>
  <p align="center">
    <img src="https://www.svgrepo.com/show/452228/html-5.svg" height="60" title="HTML5"/>
    <img src="https://www.svgrepo.com/show/452185/css-3.svg" height="60" title="CSS3"/>
    <img src="https://www.svgrepo.com/show/452045/js.svg" height="60" title="JavaScript"/>
    <img src="https://www.svgrepo.com/show/452088/php.svg" height="60" title="PHP"/>
    <img src="https://www.svgrepo.com/show/373848/mysql.svg" height="60" title="MySQL"/>
    <img src="https://cdn.simpleicons.org/xampp?viewbox=auto" height="60" title="XAMPP"/>
    <img src="https://www.svgrepo.com/show/374171/vscode.svg" height="60" title="VS Code"/>
    <img src="https://www.svgrepo.com/show/452210/git.svg" height="60" title="Git"/>
    <img src="https://img.icons8.com/?size=100&id=bVGqATNwfhYq&format=png&color=000000.svg" height="60" title="GitHub"/>
    <img src="https://www.svgrepo.com/show/378781/chrome.svg" height="60" title="Chrome"/>
    <img src="https://img.icons8.com/?size=100&id=PndQWK6M1Hjo&format=png&color=000000.svg" height="60" title="Bootstrap"/>
  </p>
  </div>
</section>

<section>
  <div>
    <h2>Estrutura do projeto</h2>
    <pre>
top-turismo/
├── index.php                  # Página inicial (destinos, sobre, contato)
├── pages/                     # Páginas do site
│   ├── login.php
│   ├── cadastro.php
│   ├── dashboard.php          # Meu Perfil (dados do usuário logado)
│   ├── reservas.php           # Formulário de reserva (requer login)
│   ├── esqueci-senha.php
│   └── redefinir-senha.php
├── php/                       # Regras de negócio / back-end (PHP + MySQL)
│   ├── conexao.example.php    # Modelo de configuração da conexão com o banco
│   ├── login.php
│   ├── cadastro.php
│   ├── logout.php
│   ├── dashboard.php          # Atualização de dados/senha do usuário
│   ├── excluir-conta.php
│   └── usuario-logado.php     # Retorna os dados do usuário logado em JSON
└── assets/
    ├── css/
    ├── js/
    └── imagens/
    </pre>
  </div>
</section>

<section>
  <div>
    <h2>Banco de dados</h2>
    <p>O projeto utiliza MySQL através da extensão <code>mysqli</code>. É necessária uma tabela <code>usuarios</code> com, no mínimo, os campos utilizados pelo cadastro, login e dashboard:</p>
    <pre>
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(20) NOT NULL UNIQUE,
    data_nascimento DATE NOT NULL,
    genero VARCHAR(20) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo VARCHAR(20) NOT NULL DEFAULT 'cliente'
);
    </pre>
  </div>
</section>

<section>
  <div>
    <h2>Como rodar</h2>
      <p>1. Baixe o repositório (Clique no Botão “Code” > Download ZIP) ou clone-o (veja a seção abaixo)</p>
      <p>2. Extraia a pasta, se necessário</p>
      <p>3. Crie um banco de dados MySQL e a tabela <code>usuarios</code> (veja a seção "Banco de dados")</p>
      <p>4. Copie <code>php/conexao.example.php</code> para <code>php/conexao.php</code> e preencha com os dados do seu MySQL (servidor, usuário, senha, banco e porta). Esse arquivo é ignorado pelo Git (<code>.gitignore</code>) e não deve ser versionado</p>
      <p>5. Como o projeto usa PHP, ele precisa rodar em um servidor local (não abra o <code>index.php</code> direto no navegador). Opções:</p>
      <ul>
        <li>Rode <code>php -S localhost:8000</code> na raiz do projeto e acesse <code>http://localhost:8000</code>; ou</li>
        <li>Use um ambiente como XAMPP/WAMP/MAMP, colocando o projeto na pasta <code>htdocs</code> (ou equivalente) e acessando via <code>http://localhost/top-turismo</code></li>
      </ul>
  </div>

  <div>
    <h2>Como clonar</h2>
     <p>No terminal:​ <code>git clone https://github.com/Mauricio-3012/top-turismo.git</code></p>

    <p>No GitHub Desktop: no menu > File (Arquivo) > Clone a repository (Clonar repositório) > selecione a aba URL, cole o link do repositório: <code>https://github.com/Mauricio-3012/top-turismo.git</code></p>
  </div>
</section>

<section>
  <div>
    <h2>Melhorias futuras</h2>
    <ul>
      <li>Implementar o back-end do fluxo de recuperação/redefinição de senha</li>
      <li>Persistir e listar as reservas feitas pelo usuário na aba "Minhas Viagens" do dashboard</li>
      <li>Adicionar validações mais robustas nos formulários (front-end e back-end)</li>
      <li>Adicionar mensagens de sucesso/erro mais completas no dashboard (ex.: feedback visual ao salvar alterações)</li>
      <li>Melhorar a responsividade para dispositivos móveis</li>
    </ul>
  </div>
</section>

<section>
  <div align="center">
    <h2>Autores e Colaboradores</h2>
    <p>Maurício Alves</p>
    <p>Fabiano Assunção</p>
    <p>David Lucas</p>
    Turma de Tecnologia em Informática para Internet (Vespertino) - Senac DF
  </div>
</section>
