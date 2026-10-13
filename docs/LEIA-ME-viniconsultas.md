# consultasmia — consulta dos contratados e da integração na MSE Academy

Uma tabela **só de consulta** no banco da MSE Academy (schema `mse_academy`).
Quem consulta só **lê** (`SELECT`); quem grava é a própria Academy.

| Tabela | O que é |
|---|---|
| `consultasmia` | Uma linha por **aula obrigatória concluída** de cada pessoa contratada recentemente |

Os dados de acesso ao banco **não** estão aqui: peça ao TI um usuário **somente leitura**
com `SELECT` nessa tabela. Exemplo para o TI:

```sql
GRANT SELECT ON mse_academy.consultasmia TO 'usuario_consulta'@'%';
```

---

## consultasmia (uma linha por aula concluída)

| Coluna | Significado |
|---|---|
| `nome`, `cpf` (11 dígitos), `email` | A pessoa (dados da ficha do Portal) |
| `aulas_obrigatorias_concluidas` | Quantas aulas **obrigatórias** a pessoa já concluiu (repete em todas as linhas dela) |
| `aula` | Nome da aula obrigatória concluída daquela linha |
| `assunto` | Assunto(s) da aula, separados por vírgula |
| `descricao` | Descrição da aula |
| `palavras_chave` | Palavras-chave da aula, separadas por vírgula |

- Aulas obrigatórias são as marcadas como obrigatórias para o **departamento** da pessoa e as obrigatórias para todos.
  Aulas só **recomendadas** e cursos de **integração** não entram.
- Quem ainda não concluiu nenhuma aparece em **1 linha**, com `aula` vazia e `aulas_obrigatorias_concluidas = 0`.
- Atualiza sozinha quando a pessoa conclui uma aula. Contratados novos entram na próxima atualização
  (botão **Atualizar banco** ou a tarefa agendada do servidor).

Exemplos:

```sql
-- Todas as aulas obrigatórias concluídas por uma pessoa
SELECT aula, assunto, descricao, palavras_chave FROM consultasmia WHERE cpf = '00000000000';

-- Quantas cada pessoa concluiu (1 linha por pessoa)
SELECT nome, cpf, email, MAX(aulas_obrigatorias_concluidas) AS concluidas
FROM consultasmia GROUP BY nome, cpf, email ORDER BY nome;
```

---

## Quando os dados valem

- As linhas de quem **já está na tabela** mudam na hora em que a pessoa conclui uma aula.
- Contratados **novos** entram quando um administrador clica em **Relatórios → Pendentes → Atualizar banco**
  (ou quando a tarefa agendada do servidor roda).

## Cuidados

- Contém CPF, e-mail e o histórico de aulas concluídas: são **dados pessoais** (LGPD). Use só para a finalidade combinada.
- Não grave, altere nem apague nada nessa tabela.
- Não guarde senha do banco no código: use variável de ambiente ou cofre de senhas.
