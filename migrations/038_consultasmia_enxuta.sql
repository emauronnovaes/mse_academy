/*
  consultasmia: apaga TODAS as colunas que sobraram além das essenciais.
  A tabela fica com: id, chave, nome, cpf, email, cargo, departamento,
  data_admissao, user_id, entrou_na_academy, primeiro_login, ultimo_login,
  total_logins, situacao_integracao, integracao_concluida,
  integracao_concluida_em, aulas_obrigatorias, aulas_concluidas.

  ATENÇÃO: ação irreversível. O que estiver nessas colunas é perdido.
  Rode NOS DOIS bancos (local e produção), banco mse_academy.
  Se alguma coluna já tiver sido apagada, dá erro "Can't DROP": é só
  tirá-la da lista e rodar de novo.
*/

ALTER TABLE consultasmia
  DROP COLUMN bau_vezes,
  DROP COLUMN bau_primeira_abertura,
  DROP COLUMN bau_ultima_abertura,
  DROP COLUMN bau_prova,
  DROP COLUMN verificado_em,
  DROP COLUMN total_entradas_video,
  DROP COLUMN ultima_entrada_video,
  DROP COLUMN ultima_saida_video,
  DROP COLUMN cursos_concluidos,
  DROP COLUMN cursos_pendentes,
  DROP COLUMN cursos_obrigatorios,
  DROP COLUMN integracao_aulas,
  DROP COLUMN integracao_aulas_concluidas,
  DROP COLUMN integracao_cursos_concluidos,
  DROP COLUMN integracao_cursos_pendentes;
