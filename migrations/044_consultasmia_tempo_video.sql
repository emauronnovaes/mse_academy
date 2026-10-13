/*
  consultasmia: coluna tempo_video = duração do vídeo da aula daquela linha,
  como "mm:ss" (ou "h:mm:ss" a partir de 1 hora). Ex.: 05:30.

  Só adiciona uma coluna. Opcional: a Academy cria sozinha no próximo
  "Atualizar banco" ou quando alguém concluir uma aula. Se já criou, este
  arquivo dá "Duplicate column": é só ignorar.
  Rode no banco da Academy (mse_academy).
*/

ALTER TABLE consultasmia ADD COLUMN tempo_video VARCHAR(12) NULL AFTER palavras_chave;
