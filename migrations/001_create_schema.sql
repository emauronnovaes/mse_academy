-- ============================================================
-- MSE Academy — Schema do banco de dados
-- ============================================================
-- Roda no MESMO servidor MySQL do MSE Board, mas em um banco
-- (schema) separado, pra não misturar dados de domínios diferentes.
--
-- Como aplicar:
--   mysql -u SEU_USUARIO -p < 001_create_schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS mse_academy
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE mse_academy;

-- ------------------------------------------------------------
-- Áreas do portal (mesma estrutura real do menu do Portal MSE)
-- ------------------------------------------------------------
CREATE TABLE areas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(60) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Usuários (colaboradores)
-- ------------------------------------------------------------
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  area_id INT UNSIGNED NULL,
  role ENUM('colaborador','admin') NOT NULL DEFAULT 'colaborador',
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE SET NULL,
  INDEX idx_users_area (area_id),
  INDEX idx_users_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Sessões (tokens de login). Hash do token fica salvo, nunca o
-- token puro — assim um vazamento do banco não expõe sessões ativas.
-- ------------------------------------------------------------
CREATE TABLE auth_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_tokens_hash (token_hash),
  INDEX idx_tokens_user (user_id),
  INDEX idx_tokens_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tentativas de login (proteção contra força bruta)
-- ------------------------------------------------------------
CREATE TABLE login_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  success TINYINT(1) NOT NULL,
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_attempts_email_time (email, attempted_at),
  INDEX idx_attempts_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Cursos / vídeos
-- type = 'onboarding'  -> trilha obrigatória e sequencial (Integração)
-- type = 'curso'       -> catálogo livre (aba Cursos)
-- ------------------------------------------------------------
CREATE TABLE courses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  area_id INT UNSIGNED NOT NULL,
  type ENUM('onboarding','curso') NOT NULL DEFAULT 'curso',
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  youtube_id VARCHAR(30) NOT NULL,
  duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  order_index SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE RESTRICT,
  INDEX idx_courses_area (area_id),
  INDEX idx_courses_type_order (type, order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Perguntas do quiz (uma ou mais por curso/vídeo)
-- ------------------------------------------------------------
CREATE TABLE quiz_questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  question_text VARCHAR(500) NOT NULL,
  order_index SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  INDEX idx_questions_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE quiz_options (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_id INT UNSIGNED NOT NULL,
  option_text VARCHAR(300) NOT NULL,
  is_correct TINYINT(1) NOT NULL DEFAULT 0,
  order_index SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
  INDEX idx_options_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Progresso de cada usuário em cada curso/vídeo
-- watched_pct nunca regride (ver lógica em progress/update.php)
-- ------------------------------------------------------------
CREATE TABLE user_course_progress (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  status ENUM('nao_iniciado','em_andamento','concluido') NOT NULL DEFAULT 'nao_iniciado',
  watched_pct DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  completed_at DATETIME NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_course (user_id, course_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  INDEX idx_progress_user (user_id),
  INDEX idx_progress_course (course_id),
  INDEX idx_progress_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Histórico de respostas do quiz (auditoria + evita fraude:
-- toda resposta é validada de novo no servidor)
-- ------------------------------------------------------------
CREATE TABLE quiz_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  question_id INT UNSIGNED NOT NULL,
  option_id INT UNSIGNED NOT NULL,
  is_correct TINYINT(1) NOT NULL,
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
  FOREIGN KEY (option_id) REFERENCES quiz_options(id) ON DELETE CASCADE,
  INDEX idx_attempts_user (user_id),
  INDEX idx_attempts_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Gamificação: pontuação agregada (evita somar tudo toda hora)
-- ------------------------------------------------------------
CREATE TABLE user_stats (
  user_id INT UNSIGNED PRIMARY KEY,
  total_points INT UNSIGNED NOT NULL DEFAULT 0,
  modules_completed INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- SEED: as 20 áreas reais do Portal MSE (mesmos slugs do front-end)
-- ============================================================
INSERT INTO areas (slug, name) VALUES
  ('portal-mse', 'Portal MSE'),
  ('administrativo', 'Administrativo'),
  ('almoxarifado', 'Almoxarifado'),
  ('contratos', 'Contratos'),
  ('dp', 'Departamento Pessoal'),
  ('seguranca-trabalho', 'Segurança do Trabalho'),
  ('comercial', 'Comercial'),
  ('financeiro', 'Financeiro'),
  ('fiscal', 'Fiscal'),
  ('fornecedores', 'Fornecedores'),
  ('gestao', 'Gestão'),
  ('gestao-materiais', 'Gestão de Materiais'),
  ('logistica', 'Logística'),
  ('propostas', 'Propostas'),
  ('suprimentos', 'Suprimentos'),
  ('infraestrutura', 'Infraestrutura'),
  ('obras', 'Obras'),
  ('recrutamento', 'Recrutamento e Seleção'),
  ('ti', 'TI'),
  ('qualidade', 'Qualidade');


-- ============================================================
-- SEED: trilha obrigatória de integração (4 módulos sequenciais)
-- TROQUE "youtube_id" pelo ID real de cada vídeo antes de publicar.
-- ============================================================
INSERT INTO courses (area_id, type, title, description, youtube_id, duration_minutes, order_index) VALUES
  ((SELECT id FROM areas WHERE slug = 'portal-mse'), 'onboarding',
   'Visão geral do Portal MSE',
   'Um tour rápido pelas áreas principais e onde encontrar cada coisa.',
   'dQw4w9WgXcQ', 4, 1),
  ((SELECT id FROM areas WHERE slug = 'dp'), 'onboarding',
   'Atualizando seus dados cadastrais',
   'Como manter contato, endereço e documentos sempre em dia.',
   'dQw4w9WgXcQ', 3, 2),
  ((SELECT id FROM areas WHERE slug = 'dp'), 'onboarding',
   'Lançando horas e solicitando férias',
   'Passo a passo para registrar horas e abrir pedidos de férias.',
   'dQw4w9WgXcQ', 6, 3),
  ((SELECT id FROM areas WHERE slug = 'financeiro'), 'onboarding',
   'Emitindo contracheque e documentos',
   'Onde baixar holerite, informe de rendimentos e declarações.',
   'dQw4w9WgXcQ', 3, 4);

-- Uma pergunta por módulo (dá pra adicionar mais depois, a coluna order_index já suporta)
INSERT INTO quiz_questions (course_id, question_text, order_index) VALUES
  ((SELECT id FROM courses WHERE order_index = 1 AND type = 'onboarding'),
   'Onde você encontra os tutoriais de cada área do portal?', 1),
  ((SELECT id FROM courses WHERE order_index = 2 AND type = 'onboarding'),
   'Por que é importante manter seus dados cadastrais atualizados?', 1),
  ((SELECT id FROM courses WHERE order_index = 3 AND type = 'onboarding'),
   'O pedido de férias deve ser feito por qual canal?', 1),
  ((SELECT id FROM courses WHERE order_index = 4 AND type = 'onboarding'),
   'Onde você baixa seu contracheque?', 1);

INSERT INTO quiz_options (question_id, option_text, is_correct, order_index) VALUES
  -- Módulo 1
  ((SELECT id FROM quiz_questions WHERE order_index=1 AND course_id=(SELECT id FROM courses WHERE order_index=1 AND type='onboarding')), 'Na aba Cursos da MSE Academy', 1, 1),
  ((SELECT id FROM quiz_questions WHERE order_index=1 AND course_id=(SELECT id FROM courses WHERE order_index=1 AND type='onboarding')), 'Só recebendo por e-mail', 0, 2),
  ((SELECT id FROM quiz_questions WHERE order_index=1 AND course_id=(SELECT id FROM courses WHERE order_index=1 AND type='onboarding')), 'Perguntando presencialmente ao RH', 0, 3),
  -- Módulo 2
  ((SELECT id FROM quiz_questions WHERE order_index=1 AND course_id=(SELECT id FROM courses WHERE order_index=2 AND type='onboarding')), 'Não afeta nada no dia a dia', 0, 1),
  ((SELECT id FROM quiz_questions WHERE order_index=1 AND course_id=(SELECT id FROM courses WHERE order_index=2 AND type='onboarding')), 'Garante que benefícios e comunicados cheguem certos', 1, 2),
  ((SELECT id FROM quiz_questions WHERE order_index=1 AND course_id=(SELECT id FROM courses WHERE order_index=2 AND type='onboarding')), 'É só uma formalidade sem uso', 0, 3),
  -- Módulo 3
  ((SELECT id FROM quiz_questions WHERE order_index=1 AND course_id=(SELECT id FROM courses WHERE order_index=3 AND type='onboarding')), 'Pelo Portal MSE', 1, 1),
  ((SELECT id FROM quiz_questions WHERE order_index=1 AND course_id=(SELECT id FROM courses WHERE order_index=3 AND type='onboarding')), 'Só verbalmente com o gestor', 0, 2),
  ((SELECT id FROM quiz_questions WHERE order_index=1 AND course_id=(SELECT id FROM courses WHERE order_index=3 AND type='onboarding')), 'Não precisa de pedido formal', 0, 3),
  -- Módulo 4
  ((SELECT id FROM quiz_questions WHERE order_index=1 AND course_id=(SELECT id FROM courses WHERE order_index=4 AND type='onboarding')), 'No Portal MSE, na área Financeiro', 1, 1),
  ((SELECT id FROM quiz_questions WHERE order_index=1 AND course_id=(SELECT id FROM courses WHERE order_index=4 AND type='onboarding')), 'Só recebe impresso', 0, 2),
  ((SELECT id FROM quiz_questions WHERE order_index=1 AND course_id=(SELECT id FROM courses WHERE order_index=4 AND type='onboarding')), 'Precisa pedir para o RH toda vez', 0, 3);


-- ============================================================
-- SEED: catálogo de exemplo (aba Cursos) — mesmo conteúdo de teste
-- que já está no front-end. Substitua/complemente à vontade.
-- ============================================================
INSERT INTO courses (area_id, type, title, description, youtube_id, duration_minutes, order_index) VALUES
  ((SELECT id FROM areas WHERE slug = 'dp'), 'curso', 'Como solicitar férias', 'Passo a passo para abrir o pedido de férias e acompanhar a aprovação.', 'dQw4w9WgXcQ', 3, 1),
  ((SELECT id FROM areas WHERE slug = 'dp'), 'curso', 'Lançando horas no sistema', 'Como registrar horas trabalhadas e corrigir lançamentos.', 'dQw4w9WgXcQ', 4, 2),
  ((SELECT id FROM areas WHERE slug = 'financeiro'), 'curso', 'Emitir contracheque', 'Onde baixar holerite, informe de rendimentos e declarações.', 'dQw4w9WgXcQ', 2, 1),
  ((SELECT id FROM areas WHERE slug = 'financeiro'), 'curso', 'Solicitar reembolso de despesas', 'Como anexar notas fiscais e acompanhar o status do pedido.', 'dQw4w9WgXcQ', 5, 2),
  ((SELECT id FROM areas WHERE slug = 'ti'), 'curso', 'Recuperar senha de acesso', 'O que fazer quando esquecer a senha do Portal MSE.', 'dQw4w9WgXcQ', 2, 1),
  ((SELECT id FROM areas WHERE slug = 'ti'), 'curso', 'Abrindo um chamado de TI', 'Onde relatar um problema técnico e acompanhar a solução.', 'dQw4w9WgXcQ', 2, 2),
  ((SELECT id FROM areas WHERE slug = 'obras'), 'curso', 'Preenchendo o RDO', 'Passo a passo para registrar o Relatório Diário de Obra.', 'dQw4w9WgXcQ', 6, 1),
  ((SELECT id FROM areas WHERE slug = 'obras'), 'curso', 'Lançando medição de obra', 'Como lançar e enviar a medição para aprovação.', 'dQw4w9WgXcQ', 5, 2),
  ((SELECT id FROM areas WHERE slug = 'suprimentos'), 'curso', 'Antecipação de pagamento a fornecedor', 'Como abrir e acompanhar uma solicitação de antecipação.', 'dQw4w9WgXcQ', 3, 1);
