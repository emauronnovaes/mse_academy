/*
  Esconder uma pessoa dos relatórios de acompanhamento.

  Os relatórios listam todo mundo que já entrou na Academy — incluindo
  contas de teste, o próprio pessoal de TI e admins que não são público
  do treinamento. Isso polui a leitura de quem está acompanhando quem
  realmente precisa fazer os cursos.

  Esta coluna marca quem não deve aparecer nessas listas. É só visual:
  a pessoa continua com a conta ativa, acessando a Academy normalmente,
  e o progresso dela continua sendo registrado. Nada é apagado — desmarcar
  faz a pessoa voltar a aparecer com todo o histórico.

  O padrão é 0 (aparece), então nada muda para quem já está cadastrado.

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco, abra a aba SQL, cole
  este arquivo inteiro e execute.
*/

ALTER TABLE users
  ADD COLUMN oculto_em_relatorios TINYINT(1) NOT NULL DEFAULT 0 AFTER active;
