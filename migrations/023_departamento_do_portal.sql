/*
  Departamento do colaborador como vem do Portal (ficha do RH, campo
  obras_departamento — ex.: "PROGRAMAÇÃO").

  Antes a Academy só guardava o departamento quando ele batia com um dos
  departamentos cadastrados na Academy (os cartões da aba Cursos); sem
  correspondência, o relatório mostrava "—". Esta coluna guarda o texto
  oficial do Portal, e os relatórios e a lista REH-002-F1 passam a usá-lo.

  Preenchida no próximo login de cada pessoa. Nada é apagado.

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco da Academy, abra a aba
  SQL, cole este arquivo inteiro e execute.
*/

ALTER TABLE users
  ADD COLUMN setor_portal VARCHAR(150) NULL;
