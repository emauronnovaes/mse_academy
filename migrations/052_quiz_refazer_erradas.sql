/*
  Abaixo de 70% de acerto, a pessoa refaz só as perguntas que errou (o vídeo
  volta ao trecho antes de cada uma). Esta coluna marca a última tentativa
  de antes do "refazer": resposta errada dada até ela volta a ficar pendente.

  Opcional: se não rodar, a Academy cria a coluna sozinha na primeira vez
  (precisa de permissão de ALTER). Sem a coluna, reprovar recomeça a aula
  do início, como antes.

  COMO RODAR: HeidiSQL ou phpMyAdmin, banco da Academy, aba SQL, colar e executar.
*/

ALTER TABLE user_course_progress
  ADD COLUMN quiz_refazer_desde BIGINT UNSIGNED NOT NULL DEFAULT 0;
