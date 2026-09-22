-- ============================================================
-- Duração exata do vídeo, em segundos.
--
-- Até aqui só existia duration_minutes, preenchido à mão por quem
-- cadastra a aula — sempre um arredondamento ("3 min"), nunca o tempo
-- real. Pra mostrar minutos E segundos seria preciso alguém cronometrar
-- cada vídeo e digitar, o que ninguém faria de forma confiável.
--
-- Em vez disso o próprio player informa: quando a aula é aberta, o
-- navegador já sabe a duração exata (do <video> ou da API do YouTube) e
-- manda pro servidor. Fica NULL até alguém abrir a aula pela primeira
-- vez, e nesse meio tempo a tela cai de volta no duration_minutes.
--
-- SMALLINT UNSIGNED vai até 65535 segundos (~18 horas), de sobra pra
-- qualquer vídeo de treinamento.
--
-- ATENÇÃO AO RODAR: passe o charset, senão acentos entram corrompidos:
--   mysql --default-character-set=utf8mb4 -u SEU_USUARIO -p mse_academy < este_arquivo.sql
-- ============================================================

USE mse_academy;

ALTER TABLE courses
  ADD COLUMN duration_seconds SMALLINT UNSIGNED NULL AFTER duration_minutes;
