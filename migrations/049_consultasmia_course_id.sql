/*
  consultasmia: course_id = o id da aula daquela linha. Com ele a Academy
  atualiza cada linha NO LUGAR e o id da linha deixa de mudar (antes as
  linhas da pessoa eram apagadas e regravadas, e o id crescia a cada vez).

  Só adiciona uma coluna. Opcional: a Academy cria sozinha na próxima
  atualização. Se já criou, dá "Duplicate column": é só ignorar.
  Rode no banco da Academy (mse_academy).
*/

ALTER TABLE consultasmia ADD COLUMN course_id INT UNSIGNED NULL AFTER aulas_obrigatorias_concluidas;

UPDATE consultasmia c JOIN courses co ON co.title = c.aula
   SET c.course_id = co.id WHERE c.aula IS NOT NULL AND c.course_id IS NULL;
