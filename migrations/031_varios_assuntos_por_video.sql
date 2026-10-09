/*
  Um vídeo em vários assuntos.

  Os cartões da seção "Cursos" (antes chamados de áreas) agora se chamam
  assuntos, e um mesmo vídeo pode aparecer em mais de um. Esta tabela guarda
  todos os assuntos de cada vídeo. courses.area_id continua sendo o assunto
  principal (o primeiro escolhido).

  Nada existente é alterado: vídeo sem linha aqui continua só no assunto
  principal, como antes.

  Opcional: se não rodar, a Academy cria a tabela sozinha na primeira vez
  que alguém adiciona um vídeo com mais de um assunto (precisa de
  permissão de CREATE).

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco da Academy, abra a aba
  SQL, cole este arquivo inteiro e execute.
*/

CREATE TABLE IF NOT EXISTS course_assuntos (
  course_id INT UNSIGNED NOT NULL,
  area_id   INT UNSIGNED NOT NULL,
  PRIMARY KEY (course_id, area_id),
  KEY idx_course_assuntos_area (area_id),
  CONSTRAINT fk_course_assuntos_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  CONSTRAINT fk_course_assuntos_area   FOREIGN KEY (area_id)   REFERENCES areas(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
