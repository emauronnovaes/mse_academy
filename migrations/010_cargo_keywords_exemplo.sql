-- ============================================================
-- MSE Academy — Exemplos de palavras-chave de cargo
-- ============================================================
-- A migração 009 criou a TABELA (course_cargo_keywords), mas não
-- colocou nenhuma linha dentro — sem isso, a recomendação por cargo
-- nunca acontecia na prática (só a recomendação por área, que já
-- existia antes). Esses são exemplos RAZOÁVEIS baseados só no título
-- de cada curso — ajuste pra bater com os cargos reais da MSE
-- (RH/gestão de cada área é quem sabe melhor).
--
-- Como funciona: se qualquer uma dessas palavras aparecer DENTRO do
-- cargo da pessoa (comparação sem acento/case, "contém" — não precisa
-- ser o cargo inteiro igual), o curso entra na recomendação dela.
--
-- Como aplicar:
--   mysql -u SEU_USUARIO -p mse_academy < 010_cargo_keywords_exemplo.sql
-- ============================================================

USE mse_academy;

-- "Como solicitar férias" / "Lançando horas no sistema" — quem GERENCIA
-- gente (aprova férias e horas de subordinados) se beneficia mesmo sem
-- ser de DP.
INSERT INTO course_cargo_keywords (course_id, keyword)
SELECT id, 'gerente' FROM courses WHERE title IN ('Como solicitar férias', 'Lançando horas no sistema')
UNION ALL
SELECT id, 'supervisor' FROM courses WHERE title IN ('Como solicitar férias', 'Lançando horas no sistema')
UNION ALL
SELECT id, 'coordenador' FROM courses WHERE title IN ('Como solicitar férias', 'Lançando horas no sistema');

-- "Solicitar reembolso de despesas" — quem viaja/gasta em campo,
-- mesmo não sendo de Financeiro.
INSERT INTO course_cargo_keywords (course_id, keyword)
SELECT id, 'engenheiro' FROM courses WHERE title = 'Solicitar reembolso de despesas'
UNION ALL
SELECT id, 'consultor' FROM courses WHERE title = 'Solicitar reembolso de despesas';

-- "Preenchendo o RDO" / "Lançando medição de obra" — quem trabalha em
-- campo/canteiro, mesmo não sendo formalmente da área Obras.
INSERT INTO course_cargo_keywords (course_id, keyword)
SELECT id, 'mestre de obras' FROM courses WHERE title IN ('Preenchendo o RDO', 'Lançando medição de obra')
UNION ALL
SELECT id, 'encarregado' FROM courses WHERE title IN ('Preenchendo o RDO', 'Lançando medição de obra')
UNION ALL
SELECT id, 'fiscal de obra' FROM courses WHERE title IN ('Preenchendo o RDO', 'Lançando medição de obra');

-- "Antecipação de pagamento a fornecedor" — quem negocia com
-- fornecedor mesmo fora de Suprimentos.
INSERT INTO course_cargo_keywords (course_id, keyword)
SELECT id, 'comprador' FROM courses WHERE title = 'Antecipação de pagamento a fornecedor'
UNION ALL
SELECT id, 'gestor de contrato' FROM courses WHERE title = 'Antecipação de pagamento a fornecedor';

-- "Emitir contracheque", "Recuperar senha de acesso" e "Abrindo um
-- chamado de TI" ficam só com a recomendação por ÁREA mesmo — são
-- genéricos o bastante pra não precisar de palavra-chave de cargo.
