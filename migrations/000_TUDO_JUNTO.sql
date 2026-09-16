-- ============================================================
-- MSE Academy — TODAS as migrações num arquivo só
-- ============================================================
-- Isso é a junção exata dos arquivos 001 até 012, na ordem certa,
-- só pra facilitar rodar tudo de uma vez (reduz o risco de esquecer
-- algum arquivo no meio do caminho).
--
-- Como usar:
--   mysql -u SEU_USUARIO -p --default-character-set=utf8mb4 < 000_TUDO_JUNTO.sql
--
-- Isso é EQUIVALENTE a rodar, na ordem, cada um dos arquivos
-- 001_create_schema.sql até 012_area_id_nullable.sql — se preferir rodar
-- separado (por exemplo, pra conferir cada etapa), os arquivos
-- originais continuam aqui normalmente, nada foi removido.
-- ============================================================


-- ============================================================
-- Início de: 001_create_schema.sql
-- ============================================================
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
  area_id INT UNSIGNED NULL, -- NULL pros cursos de "onboarding" (integração), que são universais
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


-- ============================================================
-- Início de: 002_sso_login.sql
-- ============================================================
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


-- ============================================================
-- Início de: 003_access_tracking.sql
-- ============================================================
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


-- ============================================================
-- Início de: 004_remove_manual_login.sql
-- ============================================================
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


-- ============================================================
-- Início de: 005_promote_admin.sql
-- ============================================================
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


-- ============================================================
-- Início de: 006_videos_mse_engenharia.sql
-- ============================================================
-- ============================================================
-- MSE Academy — Vídeos reais do canal oficial @MSEEngenharia no YouTube
-- ============================================================
-- Troca o "dQw4w9WgXcQ" (placeholder de teste) pelos vídeos reais da
-- empresa, só pra já sair do zero com algo melhor que um vídeo qualquer.
-- São só 9 vídeos institucionais reais — não existe um vídeo específico
-- ensinando "como lançar férias", então reaproveitei alguns entre os
-- 13 espaços (mesma distribuição já usada no front-end). Quando tiver
-- os vídeos de treinamento de verdade, troque usando o próprio painel
-- de admin (POST /api/admin/courses/create.php) ou um UPDATE direto.
--
-- Como aplicar:
--   mysql -u SEU_USUARIO -p mse_academy < 006_videos_mse_engenharia.sql
-- ============================================================

USE mse_academy;

UPDATE courses SET youtube_id = 'arb-cN3PRS4' WHERE title = 'Visão geral do Portal MSE';
UPDATE courses SET youtube_id = 'Te3h188k1Q0' WHERE title = 'Atualizando seus dados cadastrais';
UPDATE courses SET youtube_id = 'P4t5BFKNeQ8' WHERE title = 'Lançando horas e solicitando férias';
UPDATE courses SET youtube_id = '-8-ztgUeRSc' WHERE title = 'Emitindo contracheque e documentos';

UPDATE courses SET youtube_id = 'CNr-OSIlZ1Q' WHERE title = 'Como solicitar férias';
UPDATE courses SET youtube_id = 'p6Jwh0EeU34' WHERE title = 'Lançando horas no sistema';
UPDATE courses SET youtube_id = 'USqdKNogLfA' WHERE title = 'Emitir contracheque';
UPDATE courses SET youtube_id = 'k37cNp1BddM' WHERE title = 'Solicitar reembolso de despesas';
UPDATE courses SET youtube_id = 'p4jUmxI42KU' WHERE title = 'Recuperar senha de acesso';
UPDATE courses SET youtube_id = 'arb-cN3PRS4' WHERE title = 'Abrindo um chamado de TI';
UPDATE courses SET youtube_id = 'CNr-OSIlZ1Q' WHERE title = 'Preenchendo o RDO';
UPDATE courses SET youtube_id = 'p6Jwh0EeU34' WHERE title = 'Lançando medição de obra';
UPDATE courses SET youtube_id = 'Te3h188k1Q0' WHERE title = 'Antecipação de pagamento a fornecedor';


-- ============================================================
-- Início de: 007_reverter_para_placeholder.sql
-- ============================================================
-- ============================================================
-- MSE Academy — Reverte pro vídeo de teste (Erro 153 no canal real)
-- ============================================================
-- Os vídeos do canal oficial @MSEEngenharia colocados na migração 006
-- vieram com a INCORPORAÇÃO (embed) desativada pelo dono do canal —
-- comum em canais institucionais/corporativos. Isso quebra o player
-- embutido da Academy com "Erro 153: Erro de configuração do player
-- de vídeo", e junto com ele quebra todo o sistema de "assista até o
-- fim pra liberar a pergunta" (que depende do player embutido).
--
-- Volta pro vídeo de teste (dQw4w9WgXcQ) — que É garantidamente
-- incorporável, é o vídeo mais usado no mundo todo justamente pra
-- testar embed — até a empresa ter vídeos de treinamento de verdade
-- com incorporação liberada.
--
-- Como aplicar:
--   mysql -u SEU_USUARIO -p mse_academy < 007_reverter_para_placeholder.sql
-- ============================================================

USE mse_academy;

UPDATE courses SET youtube_id = 'dQw4w9WgXcQ' WHERE youtube_id != 'dQw4w9WgXcQ';


-- ============================================================
-- Início de: 008_videos_s3.sql
-- ============================================================
-- ============================================================
-- MSE Academy — Vídeos vindos do S3 (em vez de / além do YouTube)
-- ============================================================
-- Cada curso agora sabe de ONDE o vídeo dele vem: YouTube (como já era)
-- ou S3 (novo). Isso permite migrar aos poucos — dá pra ter alguns
-- cursos ainda no YouTube e outros já no S3 ao mesmo tempo, sem quebrar
-- nada, só trocando video_source + video_key de cada linha.
--
-- Como aplicar:
--   mysql -u SEU_USUARIO -p mse_academy < 008_videos_s3.sql
-- ============================================================

USE mse_academy;

ALTER TABLE courses
  MODIFY COLUMN youtube_id VARCHAR(30) NULL, -- deixa de ser obrigatório

  ADD COLUMN video_source ENUM('youtube','s3') NOT NULL DEFAULT 'youtube' AFTER youtube_id,

  -- Caminho do arquivo DENTRO do bucket (nunca a URL inteira — a URL
  -- assinada é montada na hora, na API, com tempo de expiração curto).
  -- Ex: 'onboarding/modulo-1-visao-geral.mp4'
  ADD COLUMN video_key VARCHAR(255) NULL AFTER video_source;

-- Os 4 módulos da trilha de integração passam a vir do S3. Ajuste os
-- video_key abaixo se organizar as pastas do bucket de outro jeito —
-- só precisa bater com o que está realmente dentro do bucket.
UPDATE courses SET video_source = 's3', video_key = 'onboarding/modulo-1-visao-geral.mp4'
  WHERE type = 'onboarding' AND order_index = 1;
UPDATE courses SET video_source = 's3', video_key = 'onboarding/modulo-2-dados-cadastrais.mp4'
  WHERE type = 'onboarding' AND order_index = 2;
UPDATE courses SET video_source = 's3', video_key = 'onboarding/modulo-3-horas-ferias.mp4'
  WHERE type = 'onboarding' AND order_index = 3;
UPDATE courses SET video_source = 's3', video_key = 'onboarding/modulo-4-contracheque.mp4'
  WHERE type = 'onboarding' AND order_index = 4;

-- O catálogo de cursos (type='curso') continua no YouTube por enquanto
-- — não mexemos nessas linhas. Pra migrar um curso específico pro S3
-- depois, basta:
--   UPDATE courses SET video_source='s3', video_key='catalogo/nome-do-arquivo.mp4'
--   WHERE id = <id do curso>;


-- ============================================================
-- Início de: 009_cursos_por_cargo.sql
-- ============================================================
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


-- ============================================================
-- Início de: 010_cargo_keywords_exemplo.sql
-- ============================================================
-- ============================================================
-- MSE Academy — Exemplos de palavras-chave de cargo
-- ============================================================
-- A migração 009 criou a TABELA (course_cargo_keywords), mas não
-- colocou nenhuma linha dentro — sem isso, a recomendação por cargo
-- nunca acontecia na prática (só a recomendação por área, que já
-- existia antes). Esses são exemplos RAZOÁVEIS baseados só no título
-- de cada curso — ajuste pra bater com os cargos reais da MSE
-- (RH/gestão de cada área é quem sabe melhor).
--
-- Como funciona: se qualquer uma dessas palavras aparecer DENTRO do
-- cargo da pessoa (comparação sem acento/case, "contém" — não precisa
-- ser o cargo inteiro igual), o curso entra na recomendação dela.
--
-- Como aplicar:
--   mysql -u SEU_USUARIO -p mse_academy < 010_cargo_keywords_exemplo.sql
-- ============================================================

USE mse_academy;

-- "Como solicitar férias" / "Lançando horas no sistema" — quem GERENCIA
-- gente (aprova férias e horas de subordinados) se beneficia mesmo sem
-- ser de DP.
INSERT INTO course_cargo_keywords (course_id, keyword)
SELECT id, 'gerente' FROM courses WHERE title IN ('Como solicitar férias', 'Lançando horas no sistema')
UNION ALL
SELECT id, 'supervisor' FROM courses WHERE title IN ('Como solicitar férias', 'Lançando horas no sistema')
UNION ALL
SELECT id, 'coordenador' FROM courses WHERE title IN ('Como solicitar férias', 'Lançando horas no sistema');

-- "Solicitar reembolso de despesas" — quem viaja/gasta em campo,
-- mesmo não sendo de Financeiro.
INSERT INTO course_cargo_keywords (course_id, keyword)
SELECT id, 'engenheiro' FROM courses WHERE title = 'Solicitar reembolso de despesas'
UNION ALL
SELECT id, 'consultor' FROM courses WHERE title = 'Solicitar reembolso de despesas';

-- "Preenchendo o RDO" / "Lançando medição de obra" — quem trabalha em
-- campo/canteiro, mesmo não sendo formalmente da área Obras.
INSERT INTO course_cargo_keywords (course_id, keyword)
SELECT id, 'mestre de obras' FROM courses WHERE title IN ('Preenchendo o RDO', 'Lançando medição de obra')
UNION ALL
SELECT id, 'encarregado' FROM courses WHERE title IN ('Preenchendo o RDO', 'Lançando medição de obra')
UNION ALL
SELECT id, 'fiscal de obra' FROM courses WHERE title IN ('Preenchendo o RDO', 'Lançando medição de obra');

-- "Antecipação de pagamento a fornecedor" — quem negocia com
-- fornecedor mesmo fora de Suprimentos.
INSERT INTO course_cargo_keywords (course_id, keyword)
SELECT id, 'comprador' FROM courses WHERE title = 'Antecipação de pagamento a fornecedor'
UNION ALL
SELECT id, 'gestor de contrato' FROM courses WHERE title = 'Antecipação de pagamento a fornecedor';

-- "Emitir contracheque", "Recuperar senha de acesso" e "Abrindo um
-- chamado de TI" ficam só com a recomendação por ÁREA mesmo — são
-- genéricos o bastante pra não precisar de palavra-chave de cargo.


-- ============================================================
-- Início de: 011_quiz_catalogo.sql
-- ============================================================
-- ============================================================
-- MSE Academy — Perguntas de quiz pro catálogo de cursos
-- ============================================================
-- A migração 001 criou os 9 cursos do catálogo (aba Cursos), mas
-- esqueceu de cadastrar as perguntas de quiz deles — só os 4 módulos
-- de integração tinham pergunta. Isso deixava o catálogo inteiro sem
-- conseguir "concluir" nada via POST /api/quiz/submit.php (o endpoint
-- existe e funciona, mas não tinha question_id nenhum pra usar).
--
-- Usei EXATAMENTE as mesmas perguntas que já estavam no front-end
-- (script.js, array `courses`), pra não ter pergunta diferente no
-- site e no banco.
--
-- Como aplicar:
--   mysql -u SEU_USUARIO -p mse_academy < 011_quiz_catalogo.sql
-- ============================================================

USE mse_academy;

INSERT INTO quiz_questions (course_id, question_text, order_index)
SELECT id, 'Onde você abre o pedido de férias?', 1 FROM courses WHERE title = 'Como solicitar férias'
UNION ALL
SELECT id, 'O que fazer se um lançamento de horas estiver errado?', 1 FROM courses WHERE title = 'Lançando horas no sistema'
UNION ALL
SELECT id, 'Onde você baixa o contracheque?', 1 FROM courses WHERE title = 'Emitir contracheque'
UNION ALL
SELECT id, 'O que precisa anexar no pedido de reembolso?', 1 FROM courses WHERE title = 'Solicitar reembolso de despesas'
UNION ALL
SELECT id, 'Esqueceu a senha do portal — o que fazer primeiro?', 1 FROM courses WHERE title = 'Recuperar senha de acesso'
UNION ALL
SELECT id, 'Onde você acompanha o status de um chamado de TI?', 1 FROM courses WHERE title = 'Abrindo um chamado de TI'
UNION ALL
SELECT id, 'O que é o RDO?', 1 FROM courses WHERE title = 'Preenchendo o RDO'
UNION ALL
SELECT id, 'Depois de lançar a medição, o que acontece?', 1 FROM courses WHERE title = 'Lançando medição de obra'
UNION ALL
SELECT id, 'Quem pode solicitar antecipação de pagamento a fornecedor?', 1 FROM courses WHERE title = 'Antecipação de pagamento a fornecedor';

-- Opções — sempre na ordem certa (is_correct=1 na que bate com o
-- "correct" do array do front-end).
INSERT INTO quiz_options (question_id, option_text, is_correct, order_index)
SELECT q.id, 'Pelo Portal MSE', 1, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Como solicitar férias'
UNION ALL SELECT q.id, 'Só verbalmente com o gestor', 0, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Como solicitar férias'
UNION ALL SELECT q.id, 'Não precisa de pedido formal', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Como solicitar férias'

UNION ALL SELECT q.id, 'Ignorar, se ajusta sozinho', 0, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Lançando horas no sistema'
UNION ALL SELECT q.id, 'Corrigir direto no sistema, na mesma tela', 1, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Lançando horas no sistema'
UNION ALL SELECT q.id, 'Só o RH pode corrigir', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Lançando horas no sistema'

UNION ALL SELECT q.id, 'No Portal MSE, na área Financeiro', 1, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Emitir contracheque'
UNION ALL SELECT q.id, 'Só recebe impresso', 0, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Emitir contracheque'
UNION ALL SELECT q.id, 'Precisa pedir pro RH toda vez', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Emitir contracheque'

UNION ALL SELECT q.id, 'Nada, só descrever a despesa', 0, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Solicitar reembolso de despesas'
UNION ALL SELECT q.id, 'A nota fiscal da despesa', 1, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Solicitar reembolso de despesas'
UNION ALL SELECT q.id, 'Print de conversa com o gestor', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Solicitar reembolso de despesas'

UNION ALL SELECT q.id, 'Usar a opção "esqueci minha senha" na tela de login', 1, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Recuperar senha de acesso'
UNION ALL SELECT q.id, 'Criar um usuário novo', 0, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Recuperar senha de acesso'
UNION ALL SELECT q.id, 'Ligar pro suporte de outra empresa', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Recuperar senha de acesso'

UNION ALL SELECT q.id, 'No próprio Portal MSE', 1, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Abrindo um chamado de TI'
UNION ALL SELECT q.id, 'Só por telefone', 0, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Abrindo um chamado de TI'
UNION ALL SELECT q.id, 'Não dá pra acompanhar', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Abrindo um chamado de TI'

UNION ALL SELECT q.id, 'Relatório Diário de Obra', 1, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Preenchendo o RDO'
UNION ALL SELECT q.id, 'Registro de Débitos e Ordens', 0, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Preenchendo o RDO'
UNION ALL SELECT q.id, 'Um tipo de contrato', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Preenchendo o RDO'

UNION ALL SELECT q.id, 'Ela some do sistema', 0, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Lançando medição de obra'
UNION ALL SELECT q.id, 'Ela vai pra aprovação', 1, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Lançando medição de obra'
UNION ALL SELECT q.id, 'Nada, é só um registro solto', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Lançando medição de obra'

UNION ALL SELECT q.id, 'Qualquer colaborador autorizado, pelo portal', 1, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Antecipação de pagamento a fornecedor'
UNION ALL SELECT q.id, 'Só o fornecedor, por telefone', 0, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Antecipação de pagamento a fornecedor'
UNION ALL SELECT q.id, 'Ninguém, isso não existe', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Antecipação de pagamento a fornecedor';


-- ============================================================
-- Início de: 012_area_id_nullable.sql
-- ============================================================
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

