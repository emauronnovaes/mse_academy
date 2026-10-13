/*
  viniconsultas: colunas com os cursos obrigatórios que a pessoa JÁ CONCLUIU
  e os que AINDA FALTAM (texto simples, um curso por linha).

    cursos_concluidos  "Nome do curso (dd/mm/aaaa hh:mm)"
    cursos_pendentes   "Nome do curso (% assistido)"

  Só adiciona colunas; nada é alterado ou apagado. Opcional: se não rodar, a
  Academy cria as colunas sozinha na próxima atualização. Se ela já criou,
  este arquivo dá erro "Duplicate column": é só ignorar.

  Rode no banco da Academy (mse_academy), conexão de produção.
*/

ALTER TABLE viniconsultas
  ADD COLUMN cursos_concluidos TEXT NULL AFTER aulas_concluidas,
  ADD COLUMN cursos_pendentes  TEXT NULL AFTER cursos_concluidos;
