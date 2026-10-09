/*
  Tabela para outro sistema consultar: quem foi admitido e se fez a
  integração na Academy.

  A Academy preenche e atualiza esta tabela sozinha (botão "Atualizar banco"
  em Relatórios > Pendentes e a chamada agendada api/integracao/sincronizar.php).
  O outro sistema só LÊ — nunca precisa escrever aqui.

  integracao_admitidos   uma linha por admitido, com a situação e a prova
  v_integracao_pendentes só quem ainda NÃO concluiu a integração (use esta)

  Situações: sem_acesso (nunca entrou na Academy), nao_iniciou, em_andamento,
  concluiu e dispensado (admin marcou que não precisa fazer a integração;
  também fica fora da view). A "prova" é o texto pronto + detalhe_aulas (JSON, aula por aula,
  com o status e a data), tirados da própria Academy na data de verificado_em.

  Nada existente é alterado ou apagado: são só objetos novos.

  Opcional: se não rodar, a Academy cria a tabela e a view sozinha na
  primeira sincronização (precisa de permissão de CREATE e CREATE VIEW).

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco da Academy, abra a aba
  SQL, cole este arquivo inteiro e execute.
*/

CREATE TABLE IF NOT EXISTS integracao_admitidos (
  id                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  chave                 VARCHAR(64)  NOT NULL,
  nome                  VARCHAR(200) NOT NULL,
  cpf                   VARCHAR(11)  NULL,
  email                 VARCHAR(150) NULL,
  telefone              VARCHAR(40)  NULL,
  cargo                 VARCHAR(200) NULL,
  departamento          VARCHAR(150) NULL,
  obra                  VARCHAR(200) NULL,
  empresa               VARCHAR(200) NULL,
  vinculo               VARCHAR(60)  NULL,
  gerente_nome          VARCHAR(200) NULL,
  gerente_email         VARCHAR(150) NULL,
  data_admissao         DATE         NOT NULL,
  user_id               INT UNSIGNED NULL,
  situacao              VARCHAR(20)  NOT NULL,
  aulas_obrigatorias    INT          NOT NULL DEFAULT 0,
  aulas_concluidas      INT          NOT NULL DEFAULT 0,
  ultimo_acesso         DATE         NULL,
  concluiu_em           DATETIME     NULL,
  prova                 TEXT         NULL,
  detalhe_aulas         LONGTEXT     NULL,
  primeira_verificacao  DATETIME     NOT NULL,
  verificado_em         DATETIME     NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_integracao_admitidos_chave (chave),
  KEY idx_integracao_admitidos_situacao (situacao, data_admissao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE OR REPLACE VIEW v_integracao_pendentes AS
SELECT
  nome, cpf, email, telefone, cargo, departamento, obra, empresa, vinculo,
  gerente_nome, gerente_email, data_admissao,
  DATEDIFF(CURDATE(), data_admissao) AS dias_desde_admissao,
  situacao, aulas_obrigatorias, aulas_concluidas, ultimo_acesso,
  prova, detalhe_aulas, verificado_em
FROM integracao_admitidos
WHERE situacao NOT IN ('concluiu', 'dispensado')
ORDER BY data_admissao ASC, nome ASC;
