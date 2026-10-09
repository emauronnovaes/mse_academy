/*
  Ajustes da lista de departamentos (tela "Gerenciar lista").

  O campo de departamento da ficha do Portal mistura departamentos com
  nomes de obra/cliente. Aqui o admin escolhe o que fica fora da lista de
  "Obrigatório para quais departamentos?" e pode adicionar departamentos à
  mão. Nada é apagado: ocultar e mostrar só mudam o que aparece na lista.

    tipo = ocultar  nome do Portal que fica fora da lista
    tipo = mostrar  nome que estava oculto (por padrão ou por regra) e voltou
    tipo = extra    departamento adicionado à mão

  Opcional: se não rodar, a Academy cria esta tabela sozinha no primeiro uso
  (precisa de permissão de CREATE).

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco da Academy, abra a aba
  SQL, cole este arquivo inteiro e execute.
*/

CREATE TABLE IF NOT EXISTS departamentos_config (
  chave      VARCHAR(150) NOT NULL,
  nome       VARCHAR(150) NOT NULL,
  tipo       VARCHAR(10)  NOT NULL,
  criado_por VARCHAR(200) NULL,
  criado_em  DATETIME     NOT NULL,
  PRIMARY KEY (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
