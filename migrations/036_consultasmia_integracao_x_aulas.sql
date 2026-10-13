/*
  consultasmia: separa INTEGRAÇÃO de AULAS OBRIGATÓRIAS.

    INTEGRAÇÃO          cursos de integração (onboarding) obrigatórios, que
                        todo contratado faz. Colunas novas:
                          integracao_aulas, integracao_aulas_concluidas,
                          integracao_cursos_concluidos, integracao_cursos_pendentes
                        (e, como antes, integracao_concluida / _em / situacao_integracao)

    AULAS OBRIGATÓRIAS  só as aulas marcadas como obrigatórias para o
                        DEPARTAMENTO da pessoa (em Cursos > "Obrigatório para
                        quais departamentos?"). Colunas que já existiam e
                        agora têm este sentido:
                          aulas_obrigatorias, aulas_concluidas,
                          cursos_concluidos, cursos_pendentes, cursos_obrigatorios

  Só adiciona colunas. Opcional: a Academy cria sozinha na próxima atualização;
  se já criou, este arquivo dá "Duplicate column" (é só ignorar).
*/

ALTER TABLE consultasmia
  ADD COLUMN integracao_aulas              INT  NOT NULL DEFAULT 0,
  ADD COLUMN integracao_aulas_concluidas   INT  NOT NULL DEFAULT 0,
  ADD COLUMN integracao_cursos_concluidos  TEXT NULL,
  ADD COLUMN integracao_cursos_pendentes   TEXT NULL;
