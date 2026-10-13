/*
  Classificação do vídeo no "Adicionar vídeo":

    palavras_chave   pelo menos 3, separadas por vírgula (entram na busca)
    obrigatoriedade  'recomendado'   = recomendado para os departamentos relacionados
                     'departamentos' = obrigatório para os departamentos relacionados
                     'todos'         = obrigatório para todos os colaboradores
                     NULL            = vídeos antigos (obrigatório para os departamentos marcados)
    tema_sugerido    assunto novo sugerido por quem enviou ("Outro" no Tema)
    tema_status      'pendente' | 'aprovado' | 'recusado'

  Só adiciona colunas vazias; nada existente muda. Opcional: a Academy cria
  sozinha no primeiro envio de vídeo. Se ela já criou, dá "Duplicate column"
  (é só ignorar).

  Rode no banco da Academy (mse_academy).
*/

ALTER TABLE courses
  ADD COLUMN palavras_chave  VARCHAR(1000) NULL,
  ADD COLUMN obrigatoriedade VARCHAR(20)   NULL,
  ADD COLUMN tema_sugerido   VARCHAR(120)  NULL,
  ADD COLUMN tema_status     VARCHAR(10)   NULL;
