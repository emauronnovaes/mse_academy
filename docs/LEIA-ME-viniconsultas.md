# consultasmia — consulta dos contratados e da integração na MSE Academy

Duas tabelas **só de consulta** no banco da MSE Academy (schema `mse_academy`).
Quem consulta só **lê** (`SELECT`); quem grava é a própria Academy.

| Tabela | O que é |
|---|---|
| `consultasmia` | Uma linha por pessoa contratada recentemente (resumo) |
| `consultasmia_2` | O log: cada login, entrada e saída de vídeo e abertura do baú, com data e hora |

Os dados de acesso ao banco **não** estão aqui: peça ao TI um usuário **somente leitura**
com `SELECT` nessas duas tabelas. Exemplo para o TI:

```sql
GRANT SELECT ON mse_academy.consultasmia TO 'usuario_consulta'@'%';
GRANT SELECT ON mse_academy.consultasmia_2 TO 'usuario_consulta'@'%';
```

---

## consultasmia (resumo por pessoa)

| Coluna | Significado |
|---|---|
| `nome`, `cpf` (11 dígitos), `email`, `cargo`, `departamento` | Dados da ficha do Portal |
| `data_admissao` | Data de admissão (API de integração do RH) |
| `entrou_na_academy` | 1 = já fez login na Academy; 0 = nunca entrou |
| `primeiro_login`, `ultimo_login`, `total_logins` | Logins na Academy |
| `total_entradas_video`, `ultima_entrada_video`, `ultima_saida_video` | Entradas (check-in) e saídas (check-out) de vídeo — ver abaixo |
| `situacao_integracao` | `sem_acesso`, `nao_iniciou`, `em_andamento`, `concluiu` ou `dispensado` |
| `integracao_concluida` | 1 = concluiu todos os cursos obrigatórios |
| `integracao_concluida_em` | Data e hora em que concluiu |
| `integracao_aulas`, `integracao_aulas_concluidas` | **Integração** (cursos de onboarding, obrigatórios para todo contratado): quantos são e quantos já concluiu |
| `integracao_cursos_concluidos`, `integracao_cursos_pendentes` | Texto, um curso por linha: os da integração concluídos (com data e hora) e os que faltam (com % assistido) |
| `aulas_obrigatorias`, `aulas_concluidas` | **Aulas obrigatórias**: só as marcadas como obrigatórias para o **departamento** da pessoa. Quantas são e quantas já concluiu. Cursos de integração **não** entram aqui |
| `cursos_concluidos`, `cursos_pendentes` | Texto, um curso por linha: as aulas obrigatórias do departamento concluídas (com data e hora) e as que faltam (com % assistido) |
| `cursos_obrigatorios` | **JSON** das aulas obrigatórias do departamento: status, % assistido, data de conclusão, perguntas certas, nº de entradas, primeira entrada e última saída |

Exemplos:

```sql
-- Contratados que NÃO concluíram a integração
SELECT nome, cpf, email, data_admissao, aulas_concluidas, aulas_obrigatorias, ultimo_login
FROM consultasmia WHERE integracao_concluida = 0 AND situacao_integracao <> 'dispensado';

-- O que cada um já fez e o que falta
SELECT nome, cursos_concluidos, cursos_pendentes FROM consultasmia WHERE integracao_concluida = 0;

-- Concluíram: com a data e a prova do baú
SELECT nome, integracao_concluida_em, cursos_concluidos
FROM consultasmia WHERE integracao_concluida = 1;
```

---

## consultasmia_2 (o log)

| Coluna | Significado |
|---|---|
| `user_id`, `cpf`, `email` | Quem fez |
| `tipo` | `login` (entrou na Academy), `video_entrada`, `video_saida` ou `bau_aberto` |
| `curso_id`, `curso` | Qual vídeo (para `video_entrada` e `video_saida`; 0 nos demais) |
| `evento_em` | Data e hora (horário de Brasília) |
| `detalhe` | JSON. No `bau_aberto`: **a prova** — ver abaixo |

**Ligar o log à pessoa:** `consultasmia_2.cpf = consultasmia.cpf` (ou `user_id`).

```sql
-- Todos os acessos de uma pessoa, do mais antigo ao mais recente
SELECT tipo, evento_em FROM consultasmia_2
WHERE cpf = '00000000000' ORDER BY evento_em;
```

### A prova do baú

Quando a pessoa clica no baú da trilha, a Academy **confere no banco** (nunca pelo navegador) se todos os
cursos obrigatórios estão concluídos e grava no `detalhe`:

```json
{
  "concluida": true,
  "obrigatorias": 4,
  "concluidas": 4,
  "prova": "Em 09/10/2026 às 15:47, a MSE Academy confirma que todos os 4 cursos obrigatórios da integração estão concluídos.",
  "cursos": [
    {"curso": "Visão geral do Portal MSE", "status": "concluido", "percentual_assistido": 100,
     "concluido_em": "2026-10-09 15:40:11", "perguntas_certas": 1, "perguntas": 1}
  ]
}
```

Se `concluida` for `false`, a frase diz que a integração **não** está completa naquele momento.

---

## Entrada e saída de cada vídeo

- `video_entrada` = a pessoa **abriu** o vídeo (check-in); `video_saida` = **saiu** do vídeo (check-out).
  O `curso` diz qual vídeo, e o `detalhe` traz o percentual assistido naquele momento.
- A Academy **não registra saída do portal**: o que conta é a entrada e a saída de cada vídeo.
- Se a pessoa fechar o navegador de forma brusca (queda de energia, celular), a saída daquele vídeo pode não
  ser registrada.

```sql
-- Entradas e saídas de vídeo de uma pessoa
SELECT tipo, curso, evento_em FROM consultasmia_2
WHERE cpf = '00000000000' AND tipo IN ('video_entrada', 'video_saida') ORDER BY evento_em;
```

## Quando os dados valem

- O **log** (`consultasmia_2`) é gravado **na hora** em que o acontecimento ocorre, desde o dia em que
  o log foi ativado. Os logins **anteriores** vêm do histórico de sessões que a Academy já guardava (aparecem
  com `detalhe = {"origem":"historico"}`), e as entradas e saídas de vídeo **anteriores** vêm do registro de
  check-in e check-out que já existia. O baú **não existe** antes da ativação.
- O **resumo** (`consultasmia`) só muda quando um administrador clica em **Relatórios → Pendentes → Atualizar banco**
  (ou quando a chamada agendada do servidor roda).

## Cuidados

- Contém CPF, e-mail e histórico de acesso: são **dados pessoais** (LGPD). Use só para a finalidade combinada.
- Não grave, altere nem apague nada nessas tabelas.
- Não guarde senha do banco no código: use variável de ambiente ou cofre de senhas.
