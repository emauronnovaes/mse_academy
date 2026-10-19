-- Regra das perguntas: errou, só responde de novo depois de reassistir o
-- trecho; a aula só conclui com 70% de acerto de primeira. Abaixo disso a
-- aula recomeça e as respostas anteriores deixam de contar: só valem as
-- tentativas com id maior que quiz_rodada_desde.
--
-- O sistema cria esta coluna sozinho se ela não existir; rodar aqui só
-- adianta. Pode rodar mais de uma vez? Não: a segunda dá erro de coluna
-- duplicada (sem estrago nenhum).
ALTER TABLE user_course_progress
  ADD COLUMN quiz_rodada_desde BIGINT UNSIGNED NOT NULL DEFAULT 0;
