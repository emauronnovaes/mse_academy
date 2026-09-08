-- ============================================================
-- MSE Academy — Promove o primeiro admin de conteúdo
-- ============================================================
-- Funciona tanto se a pessoa já tiver conta (já entrou pelo SSO alguma
-- vez) quanto se ainda não tiver — nesse caso cria uma linha "provisória"
-- que fica completa de verdade (nome, cargo, área) no próximo login SSO
-- dela, já que o UPDATE do sso.php nunca mexe na coluna "role".
--
-- Como aplicar:
--   mysql -u SEU_USUARIO -p mse_academy < 005_promote_admin.sql
-- ============================================================

USE mse_academy;

INSERT INTO users (name, first_name, email, role, provisioned_via)
VALUES ('Matheus Batista', 'Matheus', 'matheus.batista@mse.com.br', 'admin', 'sso')
ON DUPLICATE KEY UPDATE role = 'admin';
