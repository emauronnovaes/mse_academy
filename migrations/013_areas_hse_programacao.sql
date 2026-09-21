-- ============================================================
-- Adiciona duas áreas novas ao catálogo: HSE e Programação.
--
-- HSE (Health, Safety and Environment) convive de propósito com a área
-- "Segurança do Trabalho" que já existia — na MSE são setores distintos,
-- então cursos continuam podendo ser classificados em qualquer uma das
-- duas. Mesma lógica vale pra "Programação" ao lado de "TI".
--
-- INSERT IGNORE por causa do UNIQUE em slug: se a migração for rodada
-- duas vezes (ou num banco onde alguém já criou a área pela mão), ela
-- não quebra nem duplica — simplesmente não faz nada na segunda vez.
--
-- ATENÇÃO AO RODAR: passe o charset explicitamente, senão o "ç" e o "ã"
-- de "Programação" entram corrompidos no banco (viram "Programa├º├úo"):
--   mysql --default-character-set=utf8mb4 -u SEU_USUARIO -p < este_arquivo.sql
-- O cliente do MySQL no Windows assume a codificação do terminal, não a
-- do arquivo — e o arquivo aqui é UTF-8.
-- ============================================================

USE mse_academy;

INSERT IGNORE INTO areas (slug, name) VALUES
  ('hse', 'HSE'),
  ('programacao', 'Programação');
