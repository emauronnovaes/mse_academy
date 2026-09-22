/*
  Um curso pode valer para várias áreas.

  Até aqui cada curso tinha UMA área (courses.area_id), o que obrigava a
  escolher: ou "Como solicitar férias" era de Departamento Pessoal e só
  gente do DP via como relevante, ou não era de área nenhuma. Na prática
  há cursos que valem pra empresa inteira e outros que só fazem sentido
  pra dois ou três setores.

  Esta tabela diz, por curso, para quais áreas ele é OBRIGATÓRIO.

  Curso SEM nenhuma linha aqui continua valendo pra todo mundo — é o
  comportamento de antes, então nada muda para o que já está cadastrado
  enquanto ninguém configurar.

  Importante: isto não esconde curso de ninguém. Quem não é da área
  continua vendo e podendo assistir; só não conta como pendência dele
  nem entra na barra de progresso.

  courses.area_id continua existindo e serve para outra coisa: é a área
  em que o curso aparece organizado no catálogo. Uma é "onde fica", a
  outra é "para quem é obrigatório".

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco, abra a aba SQL, cole
  este arquivo inteiro e execute.
*/

CREATE TABLE IF NOT EXISTS course_areas (
  course_id INT UNSIGNED NOT NULL,
  area_id   INT UNSIGNED NOT NULL,
  PRIMARY KEY (course_id, area_id),
  KEY idx_course_areas_area (area_id),
  CONSTRAINT fk_course_areas_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  CONSTRAINT fk_course_areas_area   FOREIGN KEY (area_id)   REFERENCES areas(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
