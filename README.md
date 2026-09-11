# MSE Academy — Front-end + Backend

Pasta única, pronta pra subir de uma vez só no seu servidor PHP.

```
mse-academy-completo/
├── index.html, style.css, script.js  ← front-end (o site em si)
├── api/          ← endpoints da API (auth, cursos, progresso, quiz, admin)
├── config/       ← conexão com o banco (bloqueado pra acesso via navegador)
├── src/          ← lógica interna da API (bloqueado pra acesso via navegador)
├── scripts/      ← script de criar usuário admin (só linha de comando)
├── migrations/   ← SQL pra criar o banco
└── .htaccess     ← bloqueia .env, /src, /config, /scripts do navegador
```

Front-end e API no mesmo domínio (ou não — a lista de origens liberadas
fica fixa em `src/Cors.php`, editável e versionada pelo Git, ver seção
própria mais abaixo).

API em PHP puro (sem framework, sem Composer) + MySQL. Testado de ponta
a ponta com Apache real (não só o servidor de desenvolvimento do PHP,
que não lê `.htaccess` — os bloqueios de segurança abaixo foram
confirmados funcionando de verdade, incluindo o `.env`, `/src`,
`/config` e `/scripts` retornando 403/404 pro navegador).

## Por que um banco separado do MSE Board

Mesmo servidor MySQL, banco (schema) diferente:

```sql
CREATE DATABASE mse_academy;
```

Kanban e trilha de treinamento são domínios sem relação — manter em bancos
separados evita colisão de nomes de tabela e queries acidentais cruzando
os dois sistemas. Se um dia precisar relacionar dado dos dois, MySQL permite
consulta entre bancos no mesmo servidor sem problema.

## Como a pessoa entra (sem tela de login própria)

Colaboradores **não têm senha na Academy**. Eles já estão logados no
Portal MSE — o Portal manda um token assinado com os dados da pessoa,
a Academy confia nesse token e abre a sessão automaticamente. Login
manual (e-mail/senha) só existe como plano B, pra quem administra
conteúdo.

### Como funciona

Existem **duas formas** de identificar a pessoa — escolha uma delas
dependendo do que for mais fácil de implementar do lado do Portal.

#### Opção 0 — Ler a sessão do Portal direto (só se for o MESMO domínio)

Se a Academy for hospedada **dentro do mesmo domínio** do Portal (ex:
`portalmse.com.br/academy`), o cookie de sessão do Portal já chega
sozinho em qualquer página desse domínio — sem precisar de link
especial nem nada digitado na URL. A Academy tenta ler essa sessão
automaticamente, assim que a página carrega.

Isso fica em `src/PortalSession.php` — como eu não sei o nome exato da
variável que o Portal usa pra guardar o login (`$_SESSION['email']`?
`$_SESSION['usuario']['email']`? outro?), tento os formatos mais comuns
em sequência. **Testei que o mecanismo funciona de verdade** (simulei
uma sessão real e confirmei que a Academy lê ela e loga a pessoa
sozinha, sem nada na URL) — mas só vai funcionar em produção **se o
formato real da sessão do Portal bater com um dos que tentei**. Se não
bater, use `scripts/diagnostico_sessao_portal.php` pra descobrir o
formato certo (veja o comentário no topo desse arquivo) e me avise —
é uma mudança pequena ajustar pro formato certo.

Se isso não achar nada (sessão vazia, ou domínio diferente), a Academy
cai automaticamente pras opções abaixo, sem quebrar nada.

#### Opção A — Token assinado (mais segura, recomendada)

1. Você define uma chave secreta em `PORTAL_SSO_SECRET` (no `.env`) —
   a MESMA chave precisa estar configurada no lado do Portal MSE.
2. Quando a pessoa clica em "MSE Academy" dentro do Portal, o **Portal**
   (não a Academy) gera um token assinado com e-mail, CPF, nome, cargo
   e o slug da área dela, válido por poucos minutos, e redireciona pra:
   ```
   https://academy.mse.com.br/?sso=TOKEN_AQUI
   ```
3. O front-end da Academy lê o `?sso=` da URL e manda pra
   `POST /api/auth/sso.php`, que confere a assinatura, cria ou atualiza
   o usuário automaticamente, e devolve um token de sessão normal da
   Academy (mesmo mecanismo de sempre a partir daí).

#### Opção B — E-mail direto na URL (mais simples, sem assinatura)

Se não for viável gerar um token assinado no Portal agora, o link pode
simplesmente levar o e-mail (e opcionalmente o nome) da pessoa:
```
https://academy.mse.com.br/?email=fulano@mse.com.br&nome=Fulano+de+Tal
```
A Academy **confia** nesse e-mail sem verificar assinatura nenhuma —
ver o aviso de segurança completo em `api/auth/quick_login.php`. Isso só
é aceitável porque o link fica **dentro do Portal**, atrás do login
dele — ninguém de fora chega nesse link sem já ter passado pelo Portal
antes. Se o `nome` vier junto, a Academy usa ele pra buscar o cargo
oficial na Ficha Funcional (ver seção abaixo); sem nome, só identifica
pelo e-mail mesmo, sem cargo.

Testei os dois cenários de ponta a ponta (com navegador de verdade):
e-mail + nome (enriquece com cargo da ficha) e só e-mail (funciona
igual, sem cargo).


### Gerando o token no lado do Portal (PHP)

Cole isso no código do Portal MSE, no lugar de onde a pessoa clica pra
abrir a Academy:

```php
<?php
$secret = 'A_MESMA_CHAVE_DO_PORTAL_SSO_SECRET';

$payload = [
    'email'     => $usuarioLogado['email'],
    'cpf'       => $usuarioLogado['cpf'],       // só números ou com pontuação, tanto faz
    'nome'      => $usuarioLogado['nome'],
    'cargo'     => $usuarioLogado['cargo'],
    'area_slug' => $usuarioLogado['area_slug'], // precisa bater com o slug da tabela "areas" da Academy
    'exp'       => time() + 120, // token vale só 2 minutos — é de uso único
];

$json = json_encode($payload);
$b64  = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
$sig  = hash_hmac('sha256', $b64, $secret);
$token = $b64 . '.' . $sig;

$urlAcademy = 'https://academy.mse.com.br/?sso=' . urlencode($token);
// redirecione o usuário (ou coloque em um link/botão) para $urlAcademy
```

Slugs de área válidos: `portal-mse`, `administrativo`, `almoxarifado`,
`contratos`, `dp`, `seguranca-trabalho`, `comercial`, `financeiro`,
`fiscal`, `fornecedores`, `gestao`, `gestao-materiais`, `logistica`,
`propostas`, `suprimentos`, `infraestrutura`, `obras`, `recrutamento`,
`ti`, `qualidade` (ver tabela `areas`).

Se o Portal mandar um `area_slug` que a Academy não reconhece, o login
funciona normalmente — só que sem recomendação por área até alguém
corrigir isso (o vídeo de integração continua aparecendo pra todo mundo
de qualquer jeito).

### Recomendação automática por área e por cargo

`GET /api/courses/list.php?type=curso` (sem mais nada) já devolve **só
os cursos recomendados pra pessoa** — um curso entra se é da mesma
**área** dela (`area_id` do login SSO) **ou** se tem uma palavra-chave
de **cargo** que bate com o cargo dela (ex: um "Mestre de Obras" lotado
na área de TI ainda vê os cursos de Obras, por causa do cargo — testei
esse cenário exato). Pra ver o catálogo inteiro, o front-end chama com
`&scope=all` — é isso que o botão "Todos" nos filtros deve usar.

A trilha de integração (`type=onboarding`) **nunca é filtrada** por
área nem cargo — aparece igual pra todo mundo, porque é obrigatória.

As palavras-chave de cargo ficam na tabela `course_cargo_keywords`
(migração `009`) — a `010` já populou alguns **exemplos** baseados só
no título de cada curso (ex: "mestre de obras", "encarregado" e "fiscal
de obra" pro RDO e medição). **Ajuste esses exemplos pra bater com os
cargos reais da MSE** — quem melhor sabe disso é o RH/gestão de cada
área. Pra adicionar mais:
```sql
INSERT INTO course_cargo_keywords (course_id, keyword) VALUES (11, 'engenheiro civil');
```

## Passo a passo de instalação

### 1. Instalar o banco

```bash
mysql -u SEU_USUARIO -p --default-character-set=utf8mb4 < migrations/001_create_schema.sql
mysql -u SEU_USUARIO -p --default-character-set=utf8mb4 < migrations/002_sso_login.sql
mysql -u SEU_USUARIO -p --default-character-set=utf8mb4 < migrations/003_access_tracking.sql
mysql -u SEU_USUARIO -p --default-character-set=utf8mb4 < migrations/004_remove_manual_login.sql
mysql -u SEU_USUARIO -p --default-character-set=utf8mb4 < migrations/005_promote_admin.sql
mysql -u SEU_USUARIO -p --default-character-set=utf8mb4 < migrations/006_videos_mse_engenharia.sql
mysql -u SEU_USUARIO -p --default-character-set=utf8mb4 < migrations/007_reverter_para_placeholder.sql
mysql -u SEU_USUARIO -p --default-character-set=utf8mb4 < migrations/008_videos_s3.sql
mysql -u SEU_USUARIO -p --default-character-set=utf8mb4 < migrations/009_cursos_por_cargo.sql
mysql -u SEU_USUARIO -p --default-character-set=utf8mb4 < migrations/010_cargo_keywords_exemplo.sql
```

⚠️ **A flag `--default-character-set=utf8mb4` é obrigatória.** Sem ela, o
cliente `mysql` importa o arquivo com outro charset por padrão e os
acentos ficam corrompidos (ex: "Visão" vira "VisÃ£o") mesmo com as tabelas
configuradas corretamente em utf8mb4.

Roda as 8 migrações **nessa ordem** — cada uma parte do que a anterior
deixou. A `001` cria o banco `mse_academy`, todas as tabelas, as 20 áreas
reais do Portal MSE, a trilha de integração (4 módulos) e um catálogo de
exemplo. A `002` adiciona os campos de CPF/cargo e libera o login via
SSO. A `003` adiciona o rastreio de primeiro nome e acessos distintos. A
`004` remove o login manual por completo (só entra pelo SSO agora). A
`005` promove `matheus.batista@mse.com.br` a admin. As `006`/`007` foram
tentativas de vídeo real que não vingaram (o YouTube bloqueou o embed) —
ficam registradas por histórico, mas a `008` é quem define o estado
atual: os 4 módulos de integração passam a vir do **S3**, não mais do
YouTube nem de arquivo local (ver seção própria abaixo).

### 2. Configurar variáveis de ambiente

```bash
cp .env.example .env
```

Edite o `.env` com: usuário/senha reais do MySQL, a URL do front-end
(pra CORS), o `PORTAL_SSO_SECRET` combinado com o Portal, e as
credenciais da AWS (seção "Vídeos vindos do S3", logo abaixo). **Nunca**
suba o `.env` pro Git.

### 3. Subir os arquivos no servidor

Copie a pasta inteira (front-end + API juntos) pro seu servidor PHP —
ela já vem pronta pra funcionar como um site só. As pastas `config/`,
`src/` e `scripts/` já vêm com `.htaccess` bloqueando acesso direto
pelo navegador; testado e confirmado com Apache.

Se o servidor não usa Apache (não lê `.htaccess`), configure o bloqueio
equivalente no Nginx/servidor que estiver usando.

### 4. Marcar alguém como admin (opcional, pra gerenciar conteúdo)

Não existe mais tela nem script separado pra isso — como só se entra
pelo SSO, a pessoa **primeiro precisa ter acessado a Academy pelo menos
uma vez** (isso já cria a conta dela automaticamente). Depois, é só
promover direto no banco:

```sql
UPDATE users SET role = 'admin' WHERE email = 'voce@mse.com.br';
```

Colaboradores comuns não precisam de nada disso — a conta deles é criada
automaticamente no primeiro login via SSO, sempre como `colaborador`.

## Vídeos vindos do S3

O YouTube bloqueou a incorporação (embed) dos vídeos institucionais da
MSE Engenharia no player embutido (erro 153 — o dono do canal desativou
isso, comum em canais corporativos). A solução definitiva: os vídeos de
treinamento ficam num **bucket privado da AWS**, e a API gera uma **URL
assinada** (presigned URL) na hora, só pra quem já passou pelo login e
já tem aquele módulo desbloqueado. A URL expira sozinha em 30 minutos.

Usa o **SDK oficial da AWS pra PHP** (`aws/aws-sdk-php`), em
`src/AwsS3.php`. Instalado como um único arquivo `lib/aws.phar`
(autocontido, com todas as dependências já empacotadas dentro) — é a
forma oficialmente publicada pela própria AWS de instalar o SDK **sem
precisar do Composer**: https://github.com/aws/aws-sdk-php/releases
(baixe o `aws.phar` da versão mais recente).

Se o seu servidor **tem Composer** disponível, é mais fácil usar ele —
já deixei o `composer.json` pronto no projeto:
```bash
composer install
```
E troque, em `src/AwsS3.php`, a linha `require_once __DIR__ .
'/../lib/aws.phar';` por `require_once __DIR__ .
'/../vendor/autoload.php';` — o resto do arquivo continua igual, porque
usa as mesmas classes do SDK de qualquer jeito.

> **Nota sobre versionar o `aws.phar` (54 MB) no Git**: propositalmente
> **não** está no `.gitignore` — diferente de `vendor/` (que qualquer um
> recria com `composer install`), não tem como "regerar" o phar sem
> baixar de novo da internet, e ele é um arquivo estável que não muda a
> cada commit. Se preferir não versionar arquivo binário grande, baixe
> ele à parte (do link do GitHub acima) como parte do processo de
> deploy, e adicione `lib/aws.phar` no `.gitignore` nesse caso.

### Como configurar

1. **Crie um bucket S3 privado** (não marque como público).
2. **Crie um usuário IAM específico** só pra isso (não use a conta root
   da AWS), com uma política permitindo ler, enviar e listar arquivos
   nesse bucket:
   ```json
   {
     "Version": "2012-10-17",
     "Statement": [
       {
         "Effect": "Allow",
         "Action": ["s3:GetObject", "s3:PutObject"],
         "Resource": "arn:aws:s3:::SEU-BUCKET-AQUI/*"
       },
       {
         "Effect": "Allow",
         "Action": "s3:ListBucket",
         "Resource": "arn:aws:s3:::SEU-BUCKET-AQUI"
       }
     ]
   }
   ```
3. **Suba os 4 vídeos da integração** dentro de uma pasta `onboarding/`
   no bucket, com esses nomes exatos (é o que a migração `008` já
   configurou em `courses.video_key`) — ou use o endpoint de upload
   descrito abaixo, que faz isso pelo próprio site:
   ```
   onboarding/modulo-1-visao-geral.mp4
   onboarding/modulo-2-dados-cadastrais.mp4
   onboarding/modulo-3-horas-ferias.mp4
   onboarding/modulo-4-contracheque.mp4
   ```
4. **Preencha no `.env`**: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`,
   `AWS_REGION`, `AWS_S3_BUCKET` (ver `.env.example`).

### Como funciona na prática

`GET /api/courses/detail.php?id=X` (autenticado, já existia) agora
devolve um campo `video_url` já pronto — uma URL do S3 com a assinatura
embutida na própria query string, que funciona direto num
`<video src="...">`, sem precisar de nenhum header especial. Se o curso
ainda usa YouTube (`video_source: "youtube"`), `video_url` vem `null` e
o front-end continua usando `youtube_id` como sempre.

Isso significa que a lógica de controle de acesso já existente
(`mse_course_is_unlocked`) protege os vídeos automaticamente — ninguém
recebe uma URL assinada de um módulo que ainda não devia poder ver,
porque a checagem acontece **antes** de gerar a URL.

### Admin: subir e listar vídeos pelo próprio site (sem AWS Console)

| Método | Rota | O que faz |
|---|---|---|
| POST | `/api/admin/media/upload.php` | (admin) Envia um arquivo de vídeo pro S3. `multipart/form-data` com o arquivo no campo `video` e o caminho de destino em `destination_key` (ex: `onboarding/modulo-1-visao-geral.mp4`) |
| GET | `/api/admin/media/list.php?prefix=onboarding/` | (admin) Lista o que já está no bucket (opcionalmente filtrando por "pasta") |

O upload só aceita `.mp4`, `.webm` ou `.mov`, e bloqueia qualquer
`destination_key` com `..` nele (proteção contra escrever fora da
estrutura esperada do bucket). Depois de subir, associe o `video_key`
devolvido a um curso: `UPDATE courses SET video_source='s3',
video_key='...' WHERE id = X`.

### Migrando o catálogo de cursos pro S3 também

A migração `008` só mexeu na trilha de integração — o catálogo de
cursos continua no YouTube por enquanto. Pra migrar um curso específico
depois, é só isso (sem precisar de código novo):

```sql
UPDATE courses SET video_source = 's3', video_key = 'catalogo/nome-do-arquivo.mp4'
WHERE id = <id do curso>;
```

### ⚠️ O front-end (script.js) ainda não está plugado nisso

Assim como o resto do backend, **o `script.js` do site continua usando
`localStorage` e arquivos de vídeo locais** (`videos/modulo-*.mp4`) —
essa infraestrutura de S3 fica pronta no backend, esperando o dia que
alguém conectar o front-end de verdade à API (trocar `localStorage` por
`fetch()`). Se quiser, posso fazer essa conexão também.

## Endpoints

Todos (exceto o SSO, que É o login) exigem o header:
`Authorization: Bearer TOKEN_RECEBIDO_NO_LOGIN`

| Método | Rota | O que faz |
|---|---|---|
| POST | `/api/auth/sso.php` | Login via token assinado (Opção A). Recebe `{token}`, cria/atualiza o usuário, devolve `{token, user, access}` |
| GET | `/api/auth/portal_session.php` | Login automático lendo a sessão do Portal direto (Opção 0) — só funciona no mesmo domínio |
| GET | `/api/auth/quick_login.php?email=X&nome=Y` | Login simplificado sem assinatura (Opção B) — ver aviso de segurança na seção acima |
| POST | `/api/auth/logout.php` | Invalida o token atual |
| GET | `/api/auth/me.php` | Dados do usuário logado |
| GET | `/api/courses/list.php?type=onboarding` | Lista a trilha obrigatória (igual pra todo mundo) |
| GET | `/api/courses/list.php?type=curso` | Lista cursos **recomendados pra área da pessoa** |
| GET | `/api/courses/list.php?type=curso&scope=all` | Lista cursos de **todas** as áreas |
| GET | `/api/courses/list.php?type=curso&area=financeiro` | Lista cursos de uma área específica |
| GET | `/api/courses/detail.php?id=1` | Detalhe de um curso + perguntas do quiz (sem revelar a resposta certa) + `video_url` já assinada quando o vídeo vem do S3 |
| GET | `/api/progress/list.php` | Progresso do usuário logado em todos os cursos |
| POST | `/api/progress/update.php` | Atualiza o % assistido. Recebe `{course_id, watched_pct}` |
| POST | `/api/quiz/submit.php` | Envia resposta. Recebe `{question_id, option_id}`, devolve `{correct: true/false}` |
| GET | `/api/admin/users_progress.php?page=1` | (admin) Visão geral de quem já concluiu o onboarding |
| GET | `/api/admin/courses/watchers.php` | (admin) Todos os cursos com contagem de quem assistiu cada um |
| GET | `/api/admin/courses/watchers.php?course_id=X` | (admin) Lista completa de quem assistiu um curso específico, com **nome completo**, cargo e data de conclusão |
| POST | `/api/admin/courses/create.php` | (admin) Adiciona um vídeo novo ao catálogo (ou à trilha), com pergunta de quiz opcional |
| POST | `/api/admin/manage_admins.php` | (admin) Promove ou remove o acesso de admin de outro e-mail — `{email, action: "promote"\|"demote"}` |

## Regras de segurança que já estão implementadas

- **Token SSO assinado (HMAC-SHA256) e de curta duração** — o Portal
  assina, a Academy só confere; token expirado ou com assinatura errada
  é recusado (testado).
- **Sem senha nenhuma pra vazar** — não existe mais login manual, então
  não existe mais senha guardada em lugar nenhum da Academy.
- **Token de sessão nunca salvo puro** — só o hash SHA-256 fica no banco.
- **Quiz validado 100% no servidor** — o front-end nunca sabe qual é a
  resposta certa antes de responder; a checagem de acerto/erro acontece
  aqui no backend.
- **Trava sequencial da trilha no servidor** — mesmo que alguém tente
  chamar a API direto pulando a ordem, `mse_course_is_unlocked()` barra
  a tentativa (ver `src/Progress.php`).
- **SQL Injection** — todas as queries usam prepared statements (PDO),
  nunca concatenação de string.
- **CORS restrito** — só as origens listadas em `mse_origens_permitidas()`
  (`src/Cors.php`) podem chamar a API (evita outro site fazer requisições
  autenticadas). Fica fixo no código (versionado pelo Git), não no `.env`
  — testei que a origem certa recebe o header de liberação e uma origem
  qualquer não recebe nada (não fica exposto por engano).

## Aguentando muita gente entrando ao mesmo tempo

Cenário real que testei de propósito: todo mundo da empresa clica em
"MSE Academy" mais ou menos junto (ex: logo depois de um comunicado por
e-mail). Isso expôs duas corridas de concorrência reais que corrigi:

- **Criar o mesmo colaborador novo duas vezes ao mesmo tempo.** Antes, o
  código fazia "vê se já existe, senão cria" em dois passos separados —
  sob carga, duas requisições da mesma pessoa (que ainda não tem
  cadastro) podiam cair juntas, as duas verem "não existe" e as duas
  tentarem criar; a segunda quebrava com erro de e-mail duplicado.
  Testei com 10 requisições simultâneas pra um e-mail novo: 9 falhavam.
  Corrigido com `INSERT ... ON DUPLICATE KEY UPDATE` (upsert atômico —
  quem resolve a corrida é o próprio MySQL, não o PHP). Retestei: as 10
  funcionam, todas resolvem pro mesmo usuário.
- **Contador de acessos dobrando.** Mesmo problema no contador de "dias
  diferentes que a pessoa acessou" (`mse_track_access()` em
  `src/Auth.php`) — corrigido pra um único `UPDATE` com a condição de
  data dentro do próprio `WHERE`, o que faz o MySQL travar a linha e
  resolver a corrida sozinho. Testei com 30 chamadas simultâneas pro
  mesmo usuário: contagem final ficou correta.

### Limpeza periódica (evita as tabelas crescerem pra sempre)

Cada login SSO cria uma linha em `auth_tokens`; sem limpeza, com a
empresa inteira usando todo dia, essa tabela só cresce, e toda consulta
de sessão fica mais lenta com o tempo. Criei
`scripts/cleanup_maintenance.php` pra isso — remove sessões expiradas e
respostas de quiz com mais de 180 dias. Agende no servidor (roda de
madrugada, 1x por dia):

```
crontab -e
0 3 * * * php /caminho/completo/scripts/cleanup_maintenance.php >> /var/log/mse-academy-cleanup.log 2>&1
```

### Outros pontos que ajudam a escalar (ajuste na hospedagem, não no código)

- **PHP-FPM**: aumente `pm.max_children` se muita gente for acessar ao
  mesmo tempo — cada requisição PHP ocupa um "worker" até terminar.
- **MySQL `max_connections`**: o código usa conexão persistente
  (`PDO::ATTR_PERSISTENT`) pra não abrir/fechar TCP a cada requisição;
  confirme que o `max_connections` do MySQL comporta o número de workers
  do PHP-FPM (senão começa a dar erro de "too many connections" nos
  picos, não nos momentos calmos).
- **Índices já existem** nas colunas mais consultadas (`email`,
  `token_hash`, `expires_at`) — não precisa mexer.

## O que fica por sua conta antes de ir pra produção

- **HTTPS obrigatório** — token de sessão e token SSO trafegam em texto;
  sem HTTPS, qualquer um na mesma rede consegue capturá-los.
- **Combinar o `PORTAL_SSO_SECRET`** com quem mantém o código do Portal
  MSE — sem isso alinhado dos dois lados, ninguém consegue logar.
- **Backup do banco** — configure backup automático do `mse_academy`
  junto com o que já existe pro `mse_board`.
- **Rate limit geral** — sem login manual, não tem mais tentativa de
  senha errada pra proteger, mas o endpoint de SSO ainda vale um rate
  limit geral (por IP) se a API for exposta publicamente (não só na rede
  interna) — geralmente configurado no próprio servidor web
  (Nginx/Apache) ou num proxy tipo Cloudflare.

## Compatibilidade com PHP mais antigo (7.x)

O código usava algumas funções que só existem a partir do **PHP 8.0**
(`str_contains`, `str_starts_with`, `str_ends_with`) e um union type de
retorno (`string|false`, também PHP 8.0+). Se o servidor rodar uma
versão mais antiga, chamar essas funções quebra com **erro fatal**
("Call to undefined function"), sem nenhuma mensagem clara — a página
simplesmente não funciona, com o mesmo sintoma de "nada acontece" que
qualquer outro problema silencioso.

Troquei todas por versões próprias, compatíveis com PHP 7+ (em
`config/database.php`: `mse_str_contains()`, `mse_str_starts_with()`,
`mse_str_ends_with()`). Testei que elas se comportam **exatamente
igual** às nativas em 10 casos, incluindo strings vazias e outros casos
de borda.

## Caminhos da API são relativos (funciona em qualquer subpasta)

O `script.js` chama a API com caminhos relativos (`api/auth/sso.php`),
não absolutos (`/api/auth/sso.php`). Isso importa porque, se a Academy
for acessada através de outro sistema (ex: `super_app_view.php?id_menu=X`
carregando a Academy por dentro, seja via iframe ou de outro jeito), um
caminho absoluto assumiria que a Academy está bem na raiz do domínio —
e se os arquivos estiverem numa subpasta, TODAS as chamadas de API
dariam 404 silenciosamente (a página carrega normal, mas login/nome/
acesso nunca funcionam, sem erro visível).

Testei isso de verdade: coloquei a Academy dentro de uma subpasta
simulada e confirmei que o login funciona igual — só funcionou depois
de trocar pra caminho relativo (com caminho absoluto, dava 404 mesmo
com CORS e tudo mais configurado certo).

## Origens liberadas (CORS) — fixo no código, não no .env

`ACADEMY_ALLOWED_ORIGIN` deixou de existir no `.env` — a lista de
domínios que podem chamar a API agora fica **fixa em `src/Cors.php`**,
na função `mse_origens_permitidas()`:

```php
function mse_origens_permitidas(): array
{
    return [
        'https://portalmse.com.br',
    ];
}
```

**Por que mudou**: nem sempre quem tem acesso pra subir código pelo Git
também tem acesso pra editar o `.env` no servidor de produção. Deixando
fixo no código, qualquer atualização passa a valer só com um
`git push`, sem precisar mexer em nada direto no servidor.

**Se precisar adicionar outro domínio** (ex: testar local, ou a Academy
passar a ser acessada por mais de um endereço), edita essa lista —
pode ter quantas origens quiser:
```php
return [
    'https://portalmse.com.br',
    'http://localhost:8000', // exemplo, pra testar local
];
```

Testei os dois cenários de verdade (requisição HTTP real, não só a
lógica): a origem que está na lista recebe o cabeçalho
`Access-Control-Allow-Origin` liberando; qualquer origem fora da lista
não recebe nada (o navegador bloqueia sozinho) — e fica registrado no
log de erro do PHP sempre que isso acontecer, então dá pra achar depois
mesmo sem abrir o navegador.

Se quiser confirmar visualmente se a origem atual está liberada, existe
`scripts/diagnostico_cors.php` — abre pelo mesmo link que abre a
Academy normalmente, e ele mostra se bate ou não (apague esse arquivo
do servidor depois de usar).

## Enriquecimento com a Ficha Funcional do Hub MSE (RH)

O token do Portal (SSO) é sempre quem **identifica** a pessoa (e-mail,
nome) — isso já funcionava e continua igual. O que adicionei foi um
**enriquecimento opcional**: logo depois de validar o token, a Academy
busca o nome da pessoa na API `ff_infos` do Hub MSE, e se achar,
completa/corrige o **cargo** com a função oficial do RH (mais confiável
que o que o Portal eventualmente mande) — isso melhora a recomendação
de cursos por cargo (ver seção acima).

Fica em `src/PortalFichaApi.php`. Testei 4 cenários (com uma simulação
local da API, já que não tenho acesso a ela nesse ambiente): API fora
do ar → login continua funcionando sem travar; cargo vem vazio no token
→ preenchido pela ficha; cargo genérico no token → **sobrescrito** pelo
da ficha (fonte oficial); nome não encontrado na ficha → login segue
normal, sem erro.

**Configuração**: `PORTAL_FICHA_API_BASE` e `PORTAL_FICHA_API_TOKEN`
no `.env` (a chave é gerada no Hub MSE, botão "Ativar API"). Timeout de
5 segundos — se a API demorar mais que isso, o login segue sem esperar.

## Botão "Adicionar pessoas" — conexão real front-end + API

Diferente do resto do site (que roda em `localStorage`), o botão
**"Adicionar pessoas"** no topo da página é a primeira parte da Academy
conectada de verdade com a API — ele só aparece pra quem a API confirma
como admin (nunca é decidido só no navegador, senão qualquer um driblava
isso pelo console).

### Como testar isso no seu computador (sem um Portal de verdade)

Localmente não existe o Portal MSE redirecionando pra cá, então usei um
script pra gerar um link de teste equivalente:

```bash
php scripts/generate_test_login.php matheus.batista@mse.com.br
```

Isso imprime um link tipo `http://localhost/?sso=...` — abre ele no
navegador (com o servidor do site rodando) que já entra logado como
essa pessoa. Se ela for admin, o botão aparece sozinho no topo.

⚠️ **Esse script é só pra desenvolvimento** — nunca disponibilize ele
num servidor de produção (lá o token vem do Portal de verdade, assinado
com a chave real).

### O que testei (via navegador de verdade, não só lendo o código)

- Admin acessa → botão aparece
- Colaborador comum acessa → botão **não** aparece
- Admin abre o modal, digita um e-mail, confirma → a pessoa vira admin **no banco de verdade**
- Essa pessoa nova, ao acessar pela primeira vez, **já vê o botão também** — confirma a "corrente" de permissão que foi pedida (quem é adicionado também pode adicionar)


Depois que `matheus.batista@mse.com.br` roda a migração `005` e vira o
primeiro admin, ele **não precisa mais mexer no banco na mão** — dá pra
fazer tudo chamando a API (ou, quando alguém montar uma telinha pra isso
no front-end, direto pelo navegador).

**Adicionar um vídeo novo** (o `quiz_question`/`quiz_options` são
opcionais — dá pra criar o vídeo sem pergunta e adicionar depois):

```bash
curl -X POST https://SEU-DOMINIO/api/admin/courses/create.php \
  -H "Authorization: Bearer TOKEN_DO_MATHEUS" \
  -H "Content-Type: application/json" \
  -d '{
    "area_slug": "ti",
    "type": "curso",
    "title": "Configurando VPN da empresa",
    "description": "Passo a passo pra acessar a rede interna remotamente.",
    "youtube_id": "SEU_ID_DO_YOUTUBE",
    "duration_minutes": 4,
    "quiz_question": "Onde você configura a VPN?",
    "quiz_options": [
      {"text": "No Portal MSE, área TI", "is_correct": true},
      {"text": "Não precisa configurar nada", "is_correct": false}
    ]
  }'
```

**Promover outro e-mail a admin** (funciona mesmo que a pessoa nunca
tenha acessado a Academy ainda — quando ela entrar pela primeira vez via
SSO, já entra como admin):

```bash
curl -X POST https://SEU-DOMINIO/api/admin/manage_admins.php \
  -H "Authorization: Bearer TOKEN_DO_MATHEUS" \
  -H "Content-Type: application/json" \
  -d '{"email":"outra.pessoa@mse.com.br","action":"promote"}'
```

Testei os dois de ponta a ponta (login real via SSO + token de sessão
real + validação de erro rejeitando dado incompleto sem gravar nada
errado no banco).

## Próximo passo

O front-end (`mse-academy/script.js`) hoje guarda o progresso da trilha
em `localStorage`, no navegador de cada pessoa, e não sabe nada sobre
SSO. Pra usar esse backend de verdade, essa parte do JS precisa:

1. Ler o `?sso=` da URL ao carregar a página e chamar `/api/auth/sso.php`.
2. Trocar as chamadas de `localStorage` por `fetch()` nesses endpoints.

Posso fazer essa integração quando você quiser — é só pedir.
