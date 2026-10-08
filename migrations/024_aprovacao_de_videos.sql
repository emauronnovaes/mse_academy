/*
  Vídeos enviados por quem não é admin passam por aprovação.

  O vídeo fica em courses com is_published = 0 e aprovacao_status =
  'pendente' — ninguém vê até um admin aprovar na tela Aprovações (link
  também chega por e-mail para os admins).

    aprovacao_status  NULL = cadastrado por admin (fluxo de sempre)
                      'pendente' | 'aprovado' | 'recusado'
    enviado_por       users.id de quem enviou
    enviado_em        quando enviou
    notificado_em     quando o e-mail para os admins saiu
    decidido_por      users.id do admin que aprovou/recusou
    decidido_em       quando
    motivo_recusa     texto do admin ao recusar

  Opcional: se não rodar, a Academy cria essas colunas sozinha no primeiro
  envio (precisa de permissão de ALTER no banco). Nada é apagado.

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco da Academy, abra a aba
  SQL, cole este arquivo inteiro e execute.
*/

ALTER TABLE courses
  ADD COLUMN aprovacao_status VARCHAR(10) NULL,
  ADD COLUMN enviado_por INT UNSIGNED NULL,
  ADD COLUMN enviado_em DATETIME NULL,
  ADD COLUMN notificado_em DATETIME NULL,
  ADD COLUMN decidido_por INT UNSIGNED NULL,
  ADD COLUMN decidido_em DATETIME NULL,
  ADD COLUMN motivo_recusa VARCHAR(500) NULL;
