/*
  Integração diferente para quem é de OBRA.

    courses.somente_obra  1 = vídeo de integração que só aparece para quem é
                          de obra (marcado em "Só para quem é de obra")
    users.eh_obra         1 = a pessoa é de obra e NÃO está alocada na sede;
                          gravado pela ficha do Portal a cada login
                          (mse_sede diferente de SIM e com obra)

  Só adiciona colunas. Opcional: a Academy cria sozinha (no primeiro vídeo
  marcado e no próximo login de cada pessoa). Se já criou, dá
  "Duplicate column": é só ignorar.
  Rode no banco da Academy (mse_academy).
*/

ALTER TABLE courses ADD COLUMN somente_obra TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE users   ADD COLUMN eh_obra      TINYINT(1) NULL;
