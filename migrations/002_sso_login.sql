-- ============================================================
-- MSE Academy — Migração 002: login via SSO do Portal MSE
-- ============================================================
-- Colaboradores NÃO fazem login direto na Academy. Eles já estão
-- logados no Portal MSE; o Portal manda um token assinado (e-mail,
-- CPF, nome, cargo, área) e a Academy confia nesse token.
--
-- password_hash vira opcional: só usuários criados manualmente
-- (ex: admin de conteúdo, plano B sem SSO) têm senha.
-- ============================================================

USE mse_academy;

ALTER TABLE users
  MODIFY password_hash VARCHAR(255) NULL,
  ADD COLUMN cpf CHAR(11) NULL UNIQUE AFTER email,
  ADD COLUMN cargo VARCHAR(150) NULL AFTER cpf,
  ADD COLUMN provisioned_via ENUM('sso','manual') NOT NULL DEFAULT 'manual' AFTER cargo;

ALTER TABLE users
  ADD INDEX idx_users_cpf (cpf);
