-- ============================================================
-- MSE Academy — Vídeos de integração não têm área específica
-- ============================================================
-- Os módulos de integração (type='onboarding') aparecem igual pra
-- TODO MUNDO, não importa a área da pessoa — então não faz sentido
-- pedir uma área na hora de cadastrar esses vídeos. Só os cursos do
-- catálogo (type='curso') continuam exigindo área.
--
-- Como aplicar:
--   mysql -u SEU_USUARIO -p mse_academy < 012_area_opcional_onboarding.sql
-- ============================================================

USE mse_academy;

ALTER TABLE courses MODIFY COLUMN area_id INT UNSIGNED NULL;

-- Os 4 módulos de integração que já existem deixam de ter área
-- específica (antes tinham uma área "genérica" só porque a coluna
-- exigia alguma coisa) — passam a valer pra todo mundo de verdade.
UPDATE courses SET area_id = NULL WHERE type = 'onboarding';
