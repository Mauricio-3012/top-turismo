# TopTurismo

## Instalação

1. Coloque a pasta `top-turismo` dentro do `htdocs` do XAMPP ou na pasta pública do Laragon.
2. Ligue o Apache e o MySQL.
3. Confira `src/php/conexao.php` e ajuste usuário, senha ou porta somente se o seu MySQL for diferente do padrão.
4. No phpMyAdmin, importe `sql/topturismo.sql`. Esse SQL cria o banco `topturismo`, recria as três tabelas e não depende de variáveis `@OLD_*`.
5. Abra `src/pages/cadastro.php` e faça um cadastro para testar.

## Login

A senha nunca é armazenada em texto puro. O cadastro usa `password_hash()` e o login usa `password_verify()`.

## Esqueci minha senha

O fluxo foi simplificado e reconstruído em três etapas:

1. usuário informa o e-mail;
2. o sistema mostra a pergunta de recuperação cadastrada e valida a resposta;
3. o usuário cria uma nova senha e o sistema grava um novo hash.

Depois da atualização, o próprio sistema lê a senha novamente do banco e executa `password_verify()` antes de informar que a redefinição terminou. A autorização de recuperação expira em 15 minutos e é removida depois do uso.

## Teste completo

`Cadastro -> sair -> Esqueci minha senha -> e-mail -> resposta -> nova senha -> login com a nova senha`.

## Observação importante

Use a mesma instalação do MySQL para o projeto e para o phpMyAdmin. O arquivo `src/php/conexao.php` usa `127.0.0.1:3306` por padrão para deixar explícito qual servidor está sendo consultado.
