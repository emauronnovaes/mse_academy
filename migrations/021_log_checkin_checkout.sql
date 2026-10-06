/*
  Log de presença: cada check-in, check-out e "Estou aqui", com data e hora.

  Até aqui a Academy guardava só um check-in (a primeira vez que a pessoa
  abriu o vídeo) e um check-out (quando concluiu). Pra auditoria, cada vez
  que a pessoa entra e sai do treinamento precisa ficar registrada:

  - checkin  — abriu o vídeo
  - checkout — saiu (fechou o vídeo, trocou de aula, fechou a aba ou o
               vídeo terminou), com o % assistido naquele momento
  - presenca — respondeu "Estou aqui" no aviso de presença

  O log só cresce: nada aqui é apagado pela limpeza automática. Apagar a
  aula (Excluir, não Arquivar) apaga o log dela junto, como já acontece com
  o progresso.

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco da Academy, abra a aba
  SQL, cole este arquivo inteiro e execute. O código já no ar funciona
  antes e depois — enquanto não rodar, o log simplesmente não é gravado.
*/

CREATE TABLE IF NOT EXISTS presenca_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  evento ENUM('checkin', 'checkout', 'presenca') NOT NULL,
  watched_pct DECIMAL(5,2) NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  user_agent VARCHAR(255) NULL,
  INDEX idx_presenca_log_pessoa (user_id, course_id, criado_em),
  INDEX idx_presenca_log_curso (course_id, criado_em),
  CONSTRAINT fk_presenca_log_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_presenca_log_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
