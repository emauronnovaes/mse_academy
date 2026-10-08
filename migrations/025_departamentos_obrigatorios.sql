/*
  Departamentos do Portal como "obrigatório para".

  Até aqui "obrigatório para" usava os departamentos cadastrados na
  Academy (tabela areas, os cartões da aba Cursos). Agora a lista vem da
  API de ficha do Portal (Programação, Administrativo, ...) e a escolha
  fica guardada aqui, pelo nome do departamento.

  Vídeo sem nenhuma linha aqui (e sem linha em course_areas) continua
  obrigatório para todos. Nada é apagado: o que já foi marcado em
  course_areas continua valendo.

  Opcional: se não rodar, a Academy cria esta tabela sozinha na primeira
  vez que alguém marca um departamento (precisa de permissão de CREATE).

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco da Academy, abra a aba
  SQL, cole este arquivo inteiro e execute.
*/

CREATE TABLE IF NOT EXISTS course_departamentos (
  course_id         INT UNSIGNED NOT NULL,
  departamento      VARCHAR(150) NOT NULL,
  departamento_norm VARCHAR(150) NOT NULL,
  PRIMARY KEY (course_id, departamento_norm),
  CONSTRAINT fk_course_deptos_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
