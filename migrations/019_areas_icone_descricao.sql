/*
  Departamentos passam a morar no banco de verdade.

  Os 22 cartões da seção "Cursos" estavam escritos à mão no index.html:
  nome, ícone e descrição fixos no arquivo. O banco tinha as áreas, mas só
  o nome e o slug — ícone e descrição não existiam nele. Resultado: criar
  um departamento novo pelo painel não fazia cartão nenhum aparecer, e
  renomear no banco não mudava o que estava escrito na tela.

  Estas colunas são o que faltava pro cartão ser montado a partir do banco.
  Os valores abaixo são exatamente os que estavam no HTML, então a tela
  continua igual no dia em que isso subir.

  A coluna "ordem" existe pra a lista não depender do nome: hoje é
  alfabética, e não havia como colocar o departamento mais usado na frente.

  Nada é apagado aqui, e nenhum curso muda de lugar.

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco, abra a aba SQL, cole
  este arquivo inteiro e execute.
*/

ALTER TABLE areas
  ADD COLUMN icon VARCHAR(60) NOT NULL DEFAULT 'fa-folder-open' AFTER name,
  ADD COLUMN descricao VARCHAR(255) NOT NULL DEFAULT '' AFTER icon,
  ADD COLUMN ordem INT NOT NULL DEFAULT 0 AFTER descricao;

/* Ícone e descrição que cada cartão já tinha no index.html. */
UPDATE areas SET icon = 'fa-house',              descricao = 'Primeiros passos e navegação geral.'              WHERE slug = 'portal-mse';
UPDATE areas SET icon = 'fa-folder-open',        descricao = 'Rotinas administrativas e facilities.'            WHERE slug = 'administrativo';
UPDATE areas SET icon = 'fa-warehouse',          descricao = 'Entrada, saída e controle de estoque.'            WHERE slug = 'almoxarifado';
UPDATE areas SET icon = 'fa-file-signature',     descricao = 'Elaboração, aditivos e não conformidade.'         WHERE slug = 'contratos';
UPDATE areas SET icon = 'fa-user',               descricao = 'Férias, ponto, benefícios e dados cadastrais.'    WHERE slug = 'dp';
UPDATE areas SET icon = 'fa-helmet-safety',      descricao = 'Normas, EPIs e procedimentos de segurança.'       WHERE slug = 'seguranca-trabalho';
UPDATE areas SET icon = 'fa-circle-exclamation', descricao = 'Saúde, segurança e meio ambiente.'                WHERE slug = 'hse';
UPDATE areas SET icon = 'fa-handshake',          descricao = 'Propostas, clientes e relacionamento.'            WHERE slug = 'comercial';
UPDATE areas SET icon = 'fa-credit-card',        descricao = 'Pagamentos, reembolsos e contracheque.'           WHERE slug = 'financeiro';
UPDATE areas SET icon = 'fa-receipt',            descricao = 'Notas fiscais, impostos e obrigações.'            WHERE slug = 'fiscal';
UPDATE areas SET icon = 'fa-truck',              descricao = 'Cadastro, cotação e homologação.'                 WHERE slug = 'fornecedores';
UPDATE areas SET icon = 'fa-chart-line',         descricao = 'Indicadores, metas e acompanhamento.'             WHERE slug = 'gestao';
UPDATE areas SET icon = 'fa-boxes-stacked',      descricao = 'Requisição, movimentação e inventário.'           WHERE slug = 'gestao-materiais';
UPDATE areas SET icon = 'fa-route',              descricao = 'Transporte, entregas e roteirização.'             WHERE slug = 'logistica';
UPDATE areas SET icon = 'fa-file-contract',      descricao = 'Montagem, revisão e envio de propostas.'          WHERE slug = 'propostas';
UPDATE areas SET icon = 'fa-boxes-stacked',      descricao = 'Compras, pedidos e recebimento.'                  WHERE slug = 'suprimentos';
UPDATE areas SET icon = 'fa-building',           descricao = 'Instalações, manutenção e frota.'                 WHERE slug = 'infraestrutura';
UPDATE areas SET icon = 'fa-diagram-project',    descricao = 'Planejamento, execução e medição.'                WHERE slug = 'obras';
UPDATE areas SET icon = 'fa-user-plus',          descricao = 'Vagas, entrevistas e admissão.'                   WHERE slug = 'recrutamento';
UPDATE areas SET icon = 'fa-display',            descricao = 'Sistemas, acessos e suporte técnico.'             WHERE slug = 'ti';
UPDATE areas SET icon = 'fa-file-lines',         descricao = 'Desenvolvimento e sistemas internos.'             WHERE slug = 'programacao';
UPDATE areas SET icon = 'fa-medal',              descricao = 'Procedimentos, auditoria e melhoria.'             WHERE slug = 'qualidade';
