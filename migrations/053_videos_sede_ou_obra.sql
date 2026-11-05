/*
  "Para quem" do vídeo: todos, só sede ou só obra (integração e cursos).
  courses.somente_obra já existia (migração 046); esta coluna é a do "só sede".
  Quem é de obra não vê os vídeos "só sede"; quem é da sede não vê os "só obra".

  Opcional: se não rodar, a Academy cria a coluna sozinha na primeira vez que
  alguém salvar um vídeo (precisa de permissão de ALTER).

  COMO RODAR: HeidiSQL ou phpMyAdmin, banco da Academy, aba SQL, colar e executar.
*/

ALTER TABLE courses
  ADD COLUMN somente_sede TINYINT(1) NOT NULL DEFAULT 0;
