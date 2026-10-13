/*
  consultasmia no formato novo: UMA LINHA POR AULA OBRIGATÓRIA CONCLUÍDA.

    nome, cpf, email                pessoa
    aulas_obrigatorias_concluidas   quantas aulas obrigatórias ela já concluiu
    aula, assunto, descricao,       a aula concluída daquela linha
    palavras_chave

  Quem ainda não concluiu nenhuma aparece em 1 linha, com a aula vazia.

  A tabela antiga NÃO é apagada: é renomeada para consultasmia_antiga (os
  dados continuam lá). Quando não precisar mais dela, apague à mão:
    DROP TABLE consultasmia_antiga;   -- ⚠ irreversível

  Opcional: a Academy faz isso sozinha no próximo login ou "Atualizar banco".
  Rode no banco da Academy (mse_academy).
*/

RENAME TABLE consultasmia TO consultasmia_antiga;

CREATE TABLE IF NOT EXISTS consultasmia (
  id                             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome                           VARCHAR(200) NOT NULL,
  cpf                            VARCHAR(11)  NULL,
  email                          VARCHAR(150) NULL,
  aulas_obrigatorias_concluidas  INT          NOT NULL DEFAULT 0,
  aula                           VARCHAR(200) NULL,
  assunto                        VARCHAR(500) NULL,
  descricao                      TEXT         NULL,
  palavras_chave                 VARCHAR(1000) NULL,
  PRIMARY KEY (id),
  KEY idx_consultasmia_cpf (cpf),
  KEY idx_consultasmia_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
