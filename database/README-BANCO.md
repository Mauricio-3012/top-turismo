# Banco de dados do TopTurismo

## Base limpa

Use `topturismo-base.sql` para começar o projeto do zero. Ela cria:

- banco `topturismo`;
- tabela `usuarios`;
- tabela `destinos` com os 16 destinos do projeto;
- tabela `reservas`;
- **0 usuários** no início;
- **0 reservas** no início.

As reservas e usuários são criados pelo próprio sistema.

## Administrador

A base limpa não cria uma conta administrativa para continuar realmente limpa.
Se precisar do administrador de teste no PC do curso, importe `admin-inicial.sql` depois da base.

Credenciais do administrador de teste:

- e-mail: `admin@topturismo.com`
- senha: `Admin@123`
- palavra-chave: `TopTurismo2026`

## Ordem

1. Importe `topturismo-base.sql`.
2. Crie/ajuste `php/conexao.php` com os dados do seu MySQL.
3. Se quiser usar o painel administrativo, importe `admin-inicial.sql`.
4. Abra `index.php` pelo servidor PHP/Apache.
