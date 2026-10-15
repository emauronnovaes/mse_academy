/*
  consultasmia: passa a trazer também os vídeos RECOMENDADOS da pessoa, tenha
  ela feito ou não.

    tipo_aula  'obrigatoria' (só as concluídas, como antes) ou 'recomendada' (todas)
    concluida  1 = a pessoa concluiu a aula; 0 = ainda não

  Só adiciona colunas. Opcional: a Academy cria sozinha na próxima atualização
  (e marca as linhas antigas como obrigatórias concluídas). Se já criou, dá
  "Duplicate column": é só ignorar.
  Rode no banco da Academy (mse_academy).
*/

ALTER TABLE consultasmia
  ADD COLUMN tipo_aula VARCHAR(12) NULL AFTER aula,
  ADD COLUMN concluida TINYINT(1)  NULL AFTER tipo_aula;

UPDATE consultasmia SET tipo_aula = 'obrigatoria', concluida = 1 WHERE aula IS NOT NULL;
