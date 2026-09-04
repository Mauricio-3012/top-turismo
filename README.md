# TopTurismo — estrutura baseada no MovieApp

## Estrutura

- `public/index.php` — página inicial
- `src/pages/` — páginas da aplicação
- `src/pages/admin/` — páginas do administrador
- `src/php/` — processamentos PHP
- `src/php/admin/` — processamentos administrativos
- `src/assets/css/` — estilos
- `src/assets/js/` — JavaScript
- `src/assets/imagens/` — imagens dos destinos e interface
- `uploads/` — reservado para uploads
- `sql/top-turismo-base.sql` — banco base

## Configuração

1. Crie o banco importando `sql/top-turismo-base.sql`.
2. Copie `src/php/conexao.example.php` para `src/php/conexao.php`.
3. Configure servidor, usuário, senha, banco e porta do MySQL.
4. Abra `index.php` ou `public/index.php`.
5. Cadastre um usuário normalmente.
6. Para administrador, altere o campo `tipo` do usuário para `admin` no banco.

## Recuperação de senha

O fluxo é:
1. e-mail;
2. palavra-chave de recuperação;
3. redefinição da senha.

A palavra-chave e a senha são armazenadas com hash, e a autorização temporária para redefinição expira após 15 minutos.

## Observação

O arquivo `src/php/conexao.php` não deve ser enviado ao GitHub com credenciais reais. Use `conexao.example.php` como modelo.
