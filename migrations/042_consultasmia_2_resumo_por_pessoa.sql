/*
  consultasmia_2: o resumo POR PESSOA (1 linha por contratado), como era a
  tabela antiga, com nome, cpf, e-mail, cargo, departamento, admissão, logins,
  situação da integração e as aulas obrigatórias.

  Se já existir uma consultasmia_2 que seja o log antigo (login, vídeo, baú),
  a Academy a renomeia para consultasmia_2_log_antigo, sem apagar nada.

  Opcional: a Academy cria sozinha no próximo login ou "Atualizar banco".
  Rode no banco da Academy (mse_academy).
*/

CREATE TABLE IF NOT EXISTS consultasmia_2 (
  id                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  chave                    VARCHAR(64)  NOT NULL,
  nome                     VARCHAR(200) NOT NULL,
  cpf                      VARCHAR(11)  NULL,
  email                    VARCHAR(150) NULL,
  cargo                    VARCHAR(200) NULL,
  departamento             VARCHAR(150) NULL,
  data_admissao            DATE         NOT NULL,
  user_id                  INT UNSIGNED NULL,
  entrou_na_academy        TINYINT(1)   NOT NULL DEFAULT 0,
  primeiro_login           DATETIME     NULL,
  ultimo_login             DATETIME     NULL,
  total_logins             INT          NOT NULL DEFAULT 0,
  situacao_integracao      VARCHAR(20)  NOT NULL,
  integracao_concluida     TINYINT(1)   NOT NULL DEFAULT 0,
  integracao_concluida_em  DATETIME     NULL,
  aulas_obrigatorias       INT          NOT NULL DEFAULT 0,
  aulas_concluidas         INT          NOT NULL DEFAULT 0,
  aulas_concluidas_nomes   TEXT         NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_consultasmia_2_chave (chave),
  KEY idx_consultasmia_2_user (user_id),
  KEY idx_consultasmia_2_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
