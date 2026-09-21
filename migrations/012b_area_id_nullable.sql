-- ============================================================
-- Corrige um bug estrutural desde a criação da tabela: area_id em
-- "courses" estava como NOT NULL, mas cursos do tipo "onboarding"
-- (integração) nunca devem ter área — são universais, aparecem igual
-- pra todo mundo, não importa a área da pessoa (ver README, seção de
-- vídeos de integração).
--
-- Isso NUNCA tinha dado erro antes porque a migração 006 (vídeos reais
-- da MSE Engenharia) inseriu os 4 módulos de onboarding via SQL direto,
-- sem passar pelo INSERT normal validando isso. O bug só apareceu
-- agora que alguém tentou criar um vídeo de integração pela própria
-- interface (api/admin/courses/create.php), que corretamente manda
-- area_id = NULL pra esse tipo — e o banco recusava com:
--   SQLSTATE[23000]: Integrity constraint violation: 1048
--   Column 'area_id' cannot be null
-- ============================================================

USE mse_academy;

ALTER TABLE courses
  MODIFY COLUMN area_id INT UNSIGNED NULL;
