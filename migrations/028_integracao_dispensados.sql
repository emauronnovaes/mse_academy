/*
  Dispensados da integração.

  Admitidos que NÃO precisam fazer a integração (por exemplo, quem já fez em
  outra época ou cujo cargo não exige) podem ser dispensados em
  Relatórios > Admitidos / Pendentes. Eles saem da lista de pendentes e da
  view v_integracao_pendentes, mas NADA é apagado: dá para reativar a
  qualquer momento (aba Dispensados > Reativar).

  Opcional: se não rodar, a Academy cria esta tabela sozinha no primeiro
  uso (precisa de permissão de CREATE).

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco da Academy, abra a aba
  SQL, cole este arquivo inteiro e execute.
*/

CREATE TABLE IF NOT EXISTS integracao_dispensados (
  chave          VARCHAR(64)  NOT NULL,
  nome           VARCHAR(200) NOT NULL,
  cpf            VARCHAR(11)  NULL,
  data_admissao  DATE         NOT NULL,
  motivo         VARCHAR(300) NULL,
  dispensado_por VARCHAR(200) NULL,
  dispensado_em  DATETIME     NOT NULL,
  PRIMARY KEY (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
