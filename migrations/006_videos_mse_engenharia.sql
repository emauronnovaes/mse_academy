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
