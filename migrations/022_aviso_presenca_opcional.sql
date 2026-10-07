/*
  Aviso "Você ainda está aí?" opcional, por vídeo.

  1 = o vídeo pausa de tempos em tempos e pergunta se a pessoa continua
  assistindo (como era até agora, pra todos). 0 = sem esse aviso. O admin
  escolhe em Treinamentos > Editar ou ao adicionar o vídeo.

  Todo vídeo que já existe continua com o aviso ligado.

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco da Academy, abra a aba
  SQL, cole este arquivo inteiro e execute. Enquanto não rodar, o aviso
  segue ligado em todos os vídeos e a opção fica bloqueada na tela.
*/

ALTER TABLE courses
  ADD COLUMN aviso_presenca TINYINT(1) NOT NULL DEFAULT 1;
