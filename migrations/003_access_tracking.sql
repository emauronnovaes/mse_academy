-- ============================================================
-- MSE Academy — Rastreio de acessos + nome derivado do e-mail
-- ============================================================
-- Como aplicar:
--   mysql -u SEU_USUARIO -p mse_academy < 003_access_tracking.sql
-- ============================================================

USE mse_academy;

ALTER TABLE users
  -- Primeiro nome mostrado na saudação ("Bem-vindo, Matheus"), derivado
  -- automaticamente do e-mail (parte antes do @, primeiro pedaço antes
  -- do "."). Fica salvo pra não precisar recalcular a cada request —
  -- ver mse_first_name_from_email() em src/Auth.php.
  ADD COLUMN first_name VARCHAR(80) NULL AFTER name,

  -- Último DIA (não timestamp) em que a pessoa acessou. Compara só a
  -- data — múltiplos logins no mesmo dia não contam de novo.
  ADD COLUMN last_access_date DATE NULL AFTER first_name,

  -- Quantos dias DIFERENTES a pessoa já acessou, no total. Entrar 20
  -- vezes no mesmo dia soma 1 só; entrar em dias diferentes soma 1 a
  -- cada dia novo.
  ADD COLUMN distinct_access_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER last_access_date;

-- Preenche o first_name pra quem já tinha cadastro antes dessa migração
-- (deriva do e-mail existente, mesma regra do PHP)
UPDATE users
SET first_name = CONCAT(
  UPPER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(email, '@', 1), '.', 1), 1, 1)),
  LOWER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(email, '@', 1), '.', 1), 2))
)
WHERE first_name IS NULL;
