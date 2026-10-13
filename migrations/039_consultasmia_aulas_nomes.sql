/*
  consultasmia: coluna com os NOMES das aulas obrigatórias (do departamento da
  pessoa) que ela já concluiu, separados por " | ". A integração não entra.

  Só adiciona uma coluna. Opcional: a Academy cria sozinha na próxima
  atualização; se já criou, dá "Duplicate column" (é só ignorar).
*/

ALTER TABLE consultasmia ADD COLUMN aulas_concluidas_nomes TEXT NULL;
