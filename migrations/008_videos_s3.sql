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
