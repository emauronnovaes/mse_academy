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
