/*
  consultasmia — tabela só de CONSULTA (leitura) com os contratados
  recentes e a prova de que fizeram, ou não, a integração na MSE Academy.

  consultasmia          uma linha por pessoa contratada recentemente:
                         nome, CPF, e-mail, login na Academy, se concluiu a
                         integração, abertura do baú, cursos obrigatórios
  consultasmia_2  o log, um registro por acontecimento, com data e
                         hora: login na Academy, entrada em cada vídeo
                         (check-in), saída de cada vídeo (check-out) e
                         abertura do baú (esta com a prova, na hora, do que
                         a pessoa já tinha concluído)

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
