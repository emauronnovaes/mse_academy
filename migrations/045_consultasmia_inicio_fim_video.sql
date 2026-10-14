/*
  consultasmia: hora em que a pessoa COMEÇOU e hora em que TERMINOU cada vídeo.

    inicio_video  a primeira vez que a pessoa abriu o vídeo
    fim_video     quando ela concluiu o vídeo
  (horário de Brasília)

  Só adiciona colunas. Opcional: a Academy cria sozinha na próxima atualização.
  Se já criou, este arquivo dá "Duplicate column": é só ignorar.
  Rode no banco da Academy (mse_academy).
*/

ALTER TABLE consultasmia
  ADD COLUMN inicio_video DATETIME NULL AFTER tempo_video,
  ADD COLUMN fim_video    DATETIME NULL AFTER inicio_video;
