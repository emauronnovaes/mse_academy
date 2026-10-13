/*
  configuracoes: ajustes gerais da Academy que o admin liga e desliga na tela.
  Hoje: envio_video_colaboradores ('1' liberado, '0' desabilitado) = se quem
  não é admin pode usar o "Adicionar vídeo".

  Só cria uma tabela nova. Opcional: a Academy cria sozinha na primeira vez
  que o admin mexe no ajuste. Sem a tabela, o envio fica liberado.
  Rode no banco da Academy (mse_academy).
*/

CREATE TABLE IF NOT EXISTS configuracoes (
  chave           VARCHAR(60)  NOT NULL,
  valor           VARCHAR(255) NOT NULL,
  atualizado_por  VARCHAR(200) NULL,
  atualizado_em   DATETIME     NOT NULL,
  PRIMARY KEY (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
