-- ============================================================
-- MSE Academy — Recomendação de cursos por CARGO (além de área)
-- ============================================================
-- O "cargo" vem como texto livre do Portal (ex: "Analista Financeiro
-- Jr", "Analista Financeiro Sr", "Gerente de Obras") — não dá pra
-- bater cargo exato com curso exato, porque a mesma área tem várias
-- variações de cargo escritas de formas diferentes.
--
-- Em vez disso, cada curso pode ter uma ou mais PALAVRAS-CHAVE
-- associadas (ex: "financeiro", "analista"). Se alguma dessas palavras
-- aparecer dentro do cargo da pessoa (comparação sem acento/case), o
-- curso entra na recomendação dela — mesmo que seja de outra área.
--
-- Como aplicar:
--   mysql -u SEU_USUARIO -p mse_academy < 009_cursos_por_cargo.sql
-- ============================================================

USE mse_academy;

CREATE TABLE course_cargo_keywords (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  keyword VARCHAR(80) NOT NULL, -- guardada em minúsculas, sem acento
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  UNIQUE KEY uq_course_keyword (course_id, keyword),
  INDEX idx_keyword (keyword)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
