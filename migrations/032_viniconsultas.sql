/*
  viniconsultas — tabela só de CONSULTA (leitura) com os contratados
  recentes e a prova de que fizeram, ou não, a integração na MSE Academy.

  viniconsultas          uma linha por pessoa contratada recentemente:
                         nome, CPF, e-mail, login na Academy, se concluiu a
                         integração, abertura do baú, cursos obrigatórios
  viniconsultas_eventos  o log, um registro por acontecimento, com data e
                         hora: login, logout, saída da Academy e abertura
                         do baú (esta com a prova, na hora, do que a pessoa
                         já tinha concluído)

  A Academy preenche e atualiza tudo sozinha (botão "Atualizar banco" em
  Relatórios > Pendentes e a chamada agendada api/integracao/sincronizar.php;
  o log de eventos é gravado na hora em que o acontecimento ocorre).
  Quem consulta só LÊ — nunca escreve aqui.

  Nada existente é alterado ou apagado: são só objetos novos.

  Opcional: se não rodar, a Academy cria as duas tabelas sozinha no primeiro
  login ou na primeira atualização (precisa de permissão de CREATE).

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco da Academy, abra a aba
  SQL, cole este arquivo inteiro e execute.
*/

CREATE TABLE IF NOT EXISTS viniconsultas (
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
  ultimo_logout            DATETIME     NULL,
  ultima_saida             DATETIME     NULL,
  situacao_integracao      VARCHAR(20)  NOT NULL,
  integracao_concluida     TINYINT(1)   NOT NULL DEFAULT 0,
  integracao_concluida_em  DATETIME     NULL,
  aulas_obrigatorias       INT          NOT NULL DEFAULT 0,
  aulas_concluidas         INT          NOT NULL DEFAULT 0,
  cursos_obrigatorios      LONGTEXT     NULL,
  bau_vezes                INT          NOT NULL DEFAULT 0,
  bau_primeira_abertura    DATETIME     NULL,
  bau_ultima_abertura      DATETIME     NULL,
  bau_prova                TEXT         NULL,
  verificado_em            DATETIME     NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_viniconsultas_chave (chave),
  KEY idx_viniconsultas_user (user_id),
  KEY idx_viniconsultas_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS viniconsultas_eventos (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  cpf        VARCHAR(11)  NULL,
  email      VARCHAR(150) NULL,
  tipo       VARCHAR(20)  NOT NULL,
  evento_em  DATETIME     NOT NULL,
  detalhe    LONGTEXT     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_viniconsultas_eventos (user_id, tipo, evento_em),
  KEY idx_viniconsultas_eventos_tipo (tipo, evento_em),
  KEY idx_viniconsultas_eventos_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
