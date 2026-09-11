-- ============================================================
-- MSE Academy — Perguntas de quiz pro catálogo de cursos
-- ============================================================
-- A migração 001 criou os 9 cursos do catálogo (aba Cursos), mas
-- esqueceu de cadastrar as perguntas de quiz deles — só os 4 módulos
-- de integração tinham pergunta. Isso deixava o catálogo inteiro sem
-- conseguir "concluir" nada via POST /api/quiz/submit.php (o endpoint
-- existe e funciona, mas não tinha question_id nenhum pra usar).
--
-- Usei EXATAMENTE as mesmas perguntas que já estavam no front-end
-- (script.js, array `courses`), pra não ter pergunta diferente no
-- site e no banco.
--
-- Como aplicar:
--   mysql -u SEU_USUARIO -p mse_academy < 011_quiz_catalogo.sql
-- ============================================================

USE mse_academy;

INSERT INTO quiz_questions (course_id, question_text, order_index)
SELECT id, 'Onde você abre o pedido de férias?', 1 FROM courses WHERE title = 'Como solicitar férias'
UNION ALL
SELECT id, 'O que fazer se um lançamento de horas estiver errado?', 1 FROM courses WHERE title = 'Lançando horas no sistema'
UNION ALL
SELECT id, 'Onde você baixa o contracheque?', 1 FROM courses WHERE title = 'Emitir contracheque'
UNION ALL
SELECT id, 'O que precisa anexar no pedido de reembolso?', 1 FROM courses WHERE title = 'Solicitar reembolso de despesas'
UNION ALL
SELECT id, 'Esqueceu a senha do portal — o que fazer primeiro?', 1 FROM courses WHERE title = 'Recuperar senha de acesso'
UNION ALL
SELECT id, 'Onde você acompanha o status de um chamado de TI?', 1 FROM courses WHERE title = 'Abrindo um chamado de TI'
UNION ALL
SELECT id, 'O que é o RDO?', 1 FROM courses WHERE title = 'Preenchendo o RDO'
UNION ALL
SELECT id, 'Depois de lançar a medição, o que acontece?', 1 FROM courses WHERE title = 'Lançando medição de obra'
UNION ALL
SELECT id, 'Quem pode solicitar antecipação de pagamento a fornecedor?', 1 FROM courses WHERE title = 'Antecipação de pagamento a fornecedor';

-- Opções — sempre na ordem certa (is_correct=1 na que bate com o
-- "correct" do array do front-end).
INSERT INTO quiz_options (question_id, option_text, is_correct, order_index)
SELECT q.id, 'Pelo Portal MSE', 1, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Como solicitar férias'
UNION ALL SELECT q.id, 'Só verbalmente com o gestor', 0, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Como solicitar férias'
UNION ALL SELECT q.id, 'Não precisa de pedido formal', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Como solicitar férias'

UNION ALL SELECT q.id, 'Ignorar, se ajusta sozinho', 0, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Lançando horas no sistema'
UNION ALL SELECT q.id, 'Corrigir direto no sistema, na mesma tela', 1, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Lançando horas no sistema'
UNION ALL SELECT q.id, 'Só o RH pode corrigir', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Lançando horas no sistema'

UNION ALL SELECT q.id, 'No Portal MSE, na área Financeiro', 1, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Emitir contracheque'
UNION ALL SELECT q.id, 'Só recebe impresso', 0, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Emitir contracheque'
UNION ALL SELECT q.id, 'Precisa pedir pro RH toda vez', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Emitir contracheque'

UNION ALL SELECT q.id, 'Nada, só descrever a despesa', 0, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Solicitar reembolso de despesas'
UNION ALL SELECT q.id, 'A nota fiscal da despesa', 1, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Solicitar reembolso de despesas'
UNION ALL SELECT q.id, 'Print de conversa com o gestor', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Solicitar reembolso de despesas'

UNION ALL SELECT q.id, 'Usar a opção "esqueci minha senha" na tela de login', 1, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Recuperar senha de acesso'
UNION ALL SELECT q.id, 'Criar um usuário novo', 0, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Recuperar senha de acesso'
UNION ALL SELECT q.id, 'Ligar pro suporte de outra empresa', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Recuperar senha de acesso'

UNION ALL SELECT q.id, 'No próprio Portal MSE', 1, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Abrindo um chamado de TI'
UNION ALL SELECT q.id, 'Só por telefone', 0, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Abrindo um chamado de TI'
UNION ALL SELECT q.id, 'Não dá pra acompanhar', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Abrindo um chamado de TI'

UNION ALL SELECT q.id, 'Relatório Diário de Obra', 1, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Preenchendo o RDO'
UNION ALL SELECT q.id, 'Registro de Débitos e Ordens', 0, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Preenchendo o RDO'
UNION ALL SELECT q.id, 'Um tipo de contrato', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Preenchendo o RDO'

UNION ALL SELECT q.id, 'Ela some do sistema', 0, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Lançando medição de obra'
UNION ALL SELECT q.id, 'Ela vai pra aprovação', 1, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Lançando medição de obra'
UNION ALL SELECT q.id, 'Nada, é só um registro solto', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Lançando medição de obra'

UNION ALL SELECT q.id, 'Qualquer colaborador autorizado, pelo portal', 1, 1 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Antecipação de pagamento a fornecedor'
UNION ALL SELECT q.id, 'Só o fornecedor, por telefone', 0, 2 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Antecipação de pagamento a fornecedor'
UNION ALL SELECT q.id, 'Ninguém, isso não existe', 0, 3 FROM quiz_questions q JOIN courses c ON c.id=q.course_id WHERE c.title='Antecipação de pagamento a fornecedor';
