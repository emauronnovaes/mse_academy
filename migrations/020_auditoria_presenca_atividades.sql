/*
  Auditoria (ISO 9001, 14001 e 45001): dados do treinamento, lista de
  presença automática e atividades no meio do vídeo.

  1) courses — o que a auditoria pede de cada treinamento e o sistema não
     tinha: tipo (treinamento interno, DDS...), normas, instrutor, conteúdo
     programático e assuntos. Tudo opcional: vídeo antigo continua igual
     e a tela de busca mostra "—" até alguém preencher.

  2) user_course_progress — a evidência de presença de cada pessoa:
     - checkin_em: quando abriu o treinamento pela primeira vez. Antes não
       era gravado; o que já foi assistido fica sem check-in (não há como
       recuperar o passado) e o check-out continua sendo o completed_at.
     - confirmacoes_presenca / ultima_confirmacao_em: quantas vezes a
       pessoa respondeu "Estou aqui" no aviso de presença do vídeo.

  3) quiz_questions.momento_seg — em que segundo do vídeo a pergunta
     aparece. NULL = no fim do vídeo, que era o único jeito até agora.

  Nada é apagado e nenhum dado existente muda.

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco, abra a aba SQL, cole
  este arquivo inteiro e execute. O código já no ar funciona antes e
  depois — enquanto não rodar, a tela mostra os campos novos vazios e o
  check-in não é gravado.
*/

ALTER TABLE courses
  ADD COLUMN tipo_treinamento VARCHAR(60) NULL AFTER description,
  ADD COLUMN normas VARCHAR(120) NULL AFTER tipo_treinamento,
  ADD COLUMN instrutor VARCHAR(150) NULL AFTER normas,
  ADD COLUMN conteudo_programatico TEXT NULL AFTER instrutor,
  ADD COLUMN assuntos VARCHAR(500) NULL AFTER conteudo_programatico;

ALTER TABLE user_course_progress
  ADD COLUMN checkin_em DATETIME NULL AFTER watched_pct,
  ADD COLUMN confirmacoes_presenca INT UNSIGNED NOT NULL DEFAULT 0 AFTER completed_at,
  ADD COLUMN ultima_confirmacao_em DATETIME NULL AFTER confirmacoes_presenca;

ALTER TABLE quiz_questions
  ADD COLUMN momento_seg INT UNSIGNED NULL AFTER question_text;
