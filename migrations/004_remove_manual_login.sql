-- ============================================================
-- MSE Academy — Remove o login manual (usuário/senha)
-- ============================================================
-- A partir de agora o ÚNICO jeito de entrar é pelo SSO do Portal MSE
-- (a pessoa já chega logada, sem tela de login própria na Academy).
--
-- Como aplicar:
--   mysql -u SEU_USUARIO -p mse_academy < 004_remove_manual_login.sql
-- ============================================================

USE mse_academy;

-- Tabela só existia pra bloquear força bruta no login manual — sem login
-- manual, não tem mais o que proteger aqui.
DROP TABLE IF EXISTS login_attempts;

-- Coluna nunca mais é lida nem escrita (só o SSO cria/atualiza usuário,
-- e SSO nunca usa senha).
ALTER TABLE users DROP COLUMN password_hash;
