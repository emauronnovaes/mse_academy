/*
  users.login_por_cpf: 1 = a pessoa entra no Portal pelo CPF (sem e-mail).
  Gravado a cada login. Os vídeos de integração "só para obra" aparecem só
  para quem entra pelo CPF, é de obra e não é alocado na sede.

  Só adiciona uma coluna. Opcional: a Academy cria sozinha no próximo login.
  Se já criou, dá "Duplicate column": é só ignorar.
  Rode no banco da Academy (mse_academy).
*/

ALTER TABLE users ADD COLUMN login_por_cpf TINYINT(1) NULL;
