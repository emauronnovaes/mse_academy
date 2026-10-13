/*
  consultasmia: fica só com aulas_obrigatorias (quantas aulas o departamento
  da pessoa exige) e aulas_concluidas (1 = concluiu todas, 0 = falta alguma).
  Tira as colunas de detalhe dos cursos.

  ATENÇÃO: ação irreversível. O que estiver nessas colunas é perdido.
  Rode NOS DOIS bancos (local e produção), banco mse_academy. Se alguma
  coluna não existir, dá erro "Can't DROP": rode as outras separadamente.
*/

ALTER TABLE consultasmia
  DROP COLUMN cursos_concluidos,
  DROP COLUMN cursos_pendentes,
  DROP COLUMN cursos_obrigatorios,
  DROP COLUMN integracao_aulas,
  DROP COLUMN integracao_aulas_concluidas,
  DROP COLUMN integracao_cursos_concluidos,
  DROP COLUMN integracao_cursos_pendentes;
