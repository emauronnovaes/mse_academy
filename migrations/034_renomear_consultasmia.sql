/*
  Renomeia as duas tabelas de consulta (os dados continuam todos lá):

    viniconsultas          ->  consultasmia
    viniconsultas_eventos  ->  consultasmia_2

  Opcional: se não rodar, a Academy faz a troca sozinha na próxima
  atualização. Se rodar depois que a Academy já trocou, dá erro "doesn't
  exist": é só ignorar.

  ATENÇÃO: o outro sistema que lê essas tabelas precisa passar a usar os
  nomes novos. Para desfazer: RENAME TABLE consultasmia TO viniconsultas, ...

  Rode no banco da Academy (mse_academy), conexão de produção.
*/

RENAME TABLE viniconsultas TO consultasmia, viniconsultas_eventos TO consultasmia_2;
