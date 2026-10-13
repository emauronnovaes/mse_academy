/*
  consultasmia: tira as colunas do baú e a de data de verificação.

  ATENÇÃO: ação irreversível. O que estiver nessas colunas é perdido.
  (O registro de abertura do baú continua no log, em consultasmia_2,
  com tipo = 'bau_aberto'.)

  Rode NOS DOIS bancos (local e produção), ANTES de publicar o código novo.
  Banco da Academy: mse_academy.
*/

ALTER TABLE consultasmia
  DROP COLUMN bau_vezes,
  DROP COLUMN bau_primeira_abertura,
  DROP COLUMN bau_ultima_abertura,
  DROP COLUMN bau_prova,
  DROP COLUMN verificado_em;

-- Também sai (rode só se a coluna ainda existir):
ALTER TABLE consultasmia DROP COLUMN total_entradas_video;
