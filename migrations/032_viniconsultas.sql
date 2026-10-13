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
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  cpf        VARCHAR(11)  NULL,
  email      VARCHAR(150) NULL,
  tipo       VARCHAR(20)  NOT NULL,
  evento_em  DATETIME     NOT NULL,
  curso_id   INT UNSIGNED NOT NULL DEFAULT 0,
  curso      VARCHAR(200) NULL,
  detalhe    LONGTEXT     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_viniconsultas_eventos (user_id, tipo, evento_em, curso_id),
  KEY idx_viniconsultas_eventos_tipo (tipo, evento_em),
  KEY idx_viniconsultas_eventos_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
