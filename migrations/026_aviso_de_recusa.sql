/*
  Aviso de vídeo recusado.

  Guarda quando quem enviou o vídeo viu o aviso de recusa (o pop-up com o
  motivo escrito pelo admin aparece uma vez só por vídeo).

  Rode depois da 024. Opcional: se não rodar, a Academy cria esta coluna
  sozinha na primeira vez que alguém abre o aviso (precisa de permissão
  de ALTER).

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco da Academy, abra a aba
  SQL, cole este arquivo inteiro e execute.
*/

ALTER TABLE courses
  ADD COLUMN recusa_vista_em DATETIME NULL;
