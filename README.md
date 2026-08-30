# TopTurismo — versão de estudo corrigida

Esta versão mantém o visual e as funcionalidades do projeto, mas deixa a reserva mais estável e simples de entender.

## Para testar no XAMPP

1. Faça uma cópia do projeto atual.
2. Coloque esta pasta em `htdocs`/`www` conforme sua instalação.
3. No phpMyAdmin, importe `database/topturismo-base.sql` para começar com o banco limpo (16 destinos, 0 usuários e 0 reservas).
4. Copie `php/conexao.example.php` para `php/conexao.php`.
5. Ajuste servidor, usuário, senha, banco e porta do MySQL.
6. Se quiser testar o painel administrativo, importe `database/admin-inicial.sql`.
7. Abra `pages/login.php`.
8. Para testar como administrador: `admin@topturismo.com` / `Admin@123`.
9. Para testar cadastro/login de cliente, crie uma conta pelo formulário.

## Teste completo da reserva

Login → destino → passageiros → ida/volta → data → transporte → classe → assentos → revisar → pagamento → confirmar → Minhas Viagens.

## Importante

O arquivo `php/conexao.php` não deve ser enviado ao GitHub porque contém as credenciais locais do banco. O projeto usa `php/conexao.example.php` apenas como modelo.

O cartão é apenas uma simulação acadêmica. Número, validade e CVV não são enviados nem gravados no banco.

## Recuperação de senha

O cadastro agora pede uma **palavra-chave de recuperação**. Ela é armazenada somente como hash.

Fluxo: **Esqueci minha senha → e-mail → palavra-chave → nova senha**.

A autorização para redefinir a senha dura 15 minutos e a nova senha usa o mesmo `password_hash()` do cadastro e da alteração de senha.

Se você já tinha um banco criado antes desta atualização, execute `database/atualizacao-recuperacao-senha.sql`.

Administrador opcional (`database/admin-inicial.sql`): palavra-chave `TopTurismo2026`.
A base limpa não cria usuários de teste.

## Organização do código

- `pages/`: telas HTML/PHP apresentadas ao usuário.
- `php/`: processamento, validações, sessões e banco de dados.
- `assets/css/`: estilos.
- `assets/js/`: scripts gerais e o script específico de reservas.
- `database/`: scripts SQL do projeto.

O `reservas.js` foi reduzido e ficou responsável principalmente pela interface: seleção de assentos, troca de etapas, máscara do cartão e envio dos dados. As regras importantes e o cálculo oficial da reserva continuam no PHP.
