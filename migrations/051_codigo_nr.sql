-- Liga o vídeo à prova de crachá do Portal (NR-06 ou NR-12).
-- NULL = o Portal reconhece pelo título, assuntos ou palavras-chave.
-- ''   = este vídeo não entra na trava do crachá, mesmo se o título citar a NR.
-- A Academy também cria a coluna sozinha ao salvar o treinamento.

ALTER TABLE courses
  ADD COLUMN codigo_nr VARCHAR(10) NULL;
