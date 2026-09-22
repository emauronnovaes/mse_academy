-- ============================================================
-- Duas coisas novas na trilha de integração:
--
-- 1) SORTEIO — dá pra cadastrar vários vídeos equivalentes (ex: cinco
--    versões de boas-vindas) e cada pessoa recebe UM deles por sorteio,
--    nunca vendo os outros. Vídeos do mesmo grupo_sorteio são
--    alternativas entre si; grupo_sorteio NULL é aula normal, que todo
--    mundo vê (comportamento de sempre).
--
--    O resultado do sorteio é gravado em user_sorteio_aula. Sem isso,
--    sortear na hora faria a pessoa ver um vídeo diferente a cada
--    acesso — e o progresso do vídeo anterior viraria lixo.
--
-- 2) PLAYLIST — video_source ganha o valor 'playlist', e o youtube_id
--    passa a aceitar o id de uma playlist (que é mais longo que o de um
--    vídeo, daí o campo crescer pra 64). Os vídeos dentro da playlist
--    não são controlados pela Academy, então esse tipo de aula nasce
--    como OPCIONAL.
--
-- 3) OBRIGATORIO — aula opcional aparece na trilha, mas não trava o
--    avanço nem é exigida pra abrir o baú. Era necessário pra playlist
--    fazer sentido, e serve pra qualquer aula extra.
--
-- ATENÇÃO AO RODAR: passe o charset, senão acentos entram corrompidos:
--   mysql --default-character-set=utf8mb4 -u SEU_USUARIO -p mse_academy < este_arquivo.sql
-- ============================================================

USE mse_academy;

ALTER TABLE courses
  MODIFY COLUMN video_source ENUM('youtube','s3','playlist') NOT NULL DEFAULT 'youtube',
  MODIFY COLUMN youtube_id VARCHAR(64) NULL,
  ADD COLUMN grupo_sorteio VARCHAR(60) NULL AFTER type,
  ADD COLUMN obrigatorio TINYINT(1) NOT NULL DEFAULT 1 AFTER grupo_sorteio;

-- Índice pra resolver o sorteio sem varrer a tabela inteira a cada
-- carregamento da trilha.
CREATE INDEX idx_courses_grupo_sorteio ON courses (grupo_sorteio);

CREATE TABLE IF NOT EXISTS user_sorteio_aula (
  user_id    INT UNSIGNED NOT NULL,
  grupo      VARCHAR(60)  NOT NULL,
  course_id  INT UNSIGNED NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- Uma escolha por pessoa por grupo: a chave primária é o que garante
  -- que o sorteio não se repita em acessos seguintes.
  PRIMARY KEY (user_id, grupo),
  KEY idx_sorteio_course (course_id),
  CONSTRAINT fk_sorteio_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  CONSTRAINT fk_sorteio_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
