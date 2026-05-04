# CLAUDE.md

Este arquivo orienta o Claude Code (e outros assistentes de IA) ao trabalhar
neste repositório. Leia antes de propor qualquer mudança.

---

## Visão geral

Sistema web de controle de frequência de estagiários no formato CIEE,
construído para uso interno em tribunal. Substitui o preenchimento manual
da Ficha de Controle de Frequência (FCF) em papel.

**Stack:** Laravel 11 · PHP 8.2+ · Oracle (XE 21 em dev, externo em prod) · gov.br DS v3 (com tema TRE-AC) · Authelia (SSO em prod, simulado em dev) · Docker

**Documentos relacionados (leitura obrigatória antes de implementar):**
- `REQUISITOS.md` — user stories, critérios de aceitação, priorização
- `docs/dev-sessao.md` — bypass de Authelia em dev e troca de usuário simulado

---

## Princípios de desenvolvimento

Este projeto segue **Extreme Programming (XP)** com **TDD estrito**. Não é
uma sugestão. É como o código é escrito aqui.

### TDD — ciclo Red/Green/Refactor

**Toda mudança de comportamento começa por um teste que falha.** Sem exceções,
incluindo bugfixes (o teste de regressão vem antes do fix).

1. **Red:** escreva o teste. Rode. Confirme que falha pelo motivo certo
   (não por erro de sintaxe ou import faltando).
2. **Green:** escreva o **mínimo** de código pra fazer passar. Feio é OK
   nessa fase. Hardcode é OK. Duplicação é OK.
3. **Refactor:** com a barra verde, melhore o design. Rode os testes a cada
   pequena mudança. Se quebrar, reverta — não tente "consertar pra frente".

**Regras práticas:**
- Um teste falhando por vez. Não escreva 5 testes e depois implemente.
- Se você não sabe que teste escrever em seguida, é sinal de que precisa
  pensar mais sobre o design antes de continuar — não de pular o teste.
- Testes que ficam pendentes ("vou escrever depois") **não existem**. Ou
  está testado, ou não está no código.

### XP — práticas que adotamos

- **Pair programming**: PRs pequenos, code review síncrono quando possível.
  Quando o par é o Claude, considere a sessão como pair — Claude propõe,
  você decide; você propõe, Claude critica.
- **Simple design** (regras do Kent Beck, em ordem):
  1. Passa em todos os testes
  2. Revela a intenção
  3. Não tem duplicação
  4. Tem o menor número de elementos
- **YAGNI**: não implemente nada que não tenha história/critério no
  `REQUISITOS.md`. Se aparecer uma "boa ideia" no meio do caminho, anote
  e siga; discuta na próxima iteração.
- **Refactor contínuo**: pequenas melhorias a cada commit verde. Refactor
  grande é um cheiro de débito acumulado — tente evitar.
- **Iterações curtas**: entregas de 1 semana. Cada iteração tem 3–5
  histórias do `REQUISITOS.md`.
- **Continuous integration**: nunca subir pra `main` com a barra vermelha.

---

## Comandos

**Tudo roda em Docker.** Não instale PHP, Composer, Oracle ou nada disso
na sua máquina. Se um comando da lista abaixo falha por falta de
dependência local, é porque você está rodando fora do container — entre
nele primeiro.

### Setup inicial (depois de clonar)

```bash
cp .env.example .env
docker compose up -d --build              # sobe app, oracle, nginx
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed --class=FeriadosNacionaisSeeder
```

Atalho equivalente: `make bootstrap`.

A app fica em `http://localhost:8082` (HTTP direto, sem proxy local).
Em dev não há TLS nem `ponto.localhost` — Traefik/Authelia ficam só em
produção (na infra do tribunal, externos a este compose).

### Atalho recomendado

Crie um alias pra reduzir digitação:

```bash
alias dx='docker compose exec app'
# então:
dx php artisan test
dx ./vendor/bin/pint
```

Ou use o `Makefile` do projeto:

```bash
make up           # sobe os containers
make down         # derruba
make test         # roda toda a suíte
make pint         # formata
make check        # pint --test && test (rodar antes do commit)
make shell        # abre bash dentro do container app
make migrate      # migrate
make fresh        # migrate:fresh --seed (CUIDADO: apaga dados)
make logs         # tail dos logs de todos os containers
```

### Rodando testes (faça antes E depois de cada mudança)

```bash
make test                                              # todos
docker compose exec app php artisan test --filter=PontoServiceTest
docker compose exec app php artisan test --coverage    # exige Xdebug/PCOV no container
docker compose exec app php artisan test --parallel    # paralelo
```

Os testes usam **SQLite in-memory** (configurado em `phpunit.xml`),
então não dependem do Oracle do compose — rodam rápido e isolados.

### Inspeção e debug

```bash
make shell                                # bash no container app
docker compose exec app php artisan tinker
docker compose logs -f app                # logs do Laravel
docker compose logs -f nginx              # logs do HTTP
docker compose logs -f oracle             # logs do banco (demora pra subir!)
```

**Antes de qualquer commit, esta sequência precisa passar:**

```bash
make check     # equivalente a: dx ./vendor/bin/pint --test && dx php artisan test
```

Se você é o Claude rodando isso, **rode de verdade** — não pressuma que
passou. Cole o output no chat se houver dúvida.

---

## Infraestrutura Docker

Toda a stack — dev, CI e produção — roda em containers. Mudanças em
`Dockerfile` ou `docker-compose.yml` exigem o mesmo cuidado de revisão
que código de aplicação (são código também).

### Containers do compose

| Serviço | Imagem base | Função |
|---------|-------------|--------|
| `app` | `php:8.2-fpm` (custom, Dockerfile na raiz) | Laravel + Oracle Instant Client + oci8 |
| `nginx` | `nginx:alpine` | Serve a app, fala com php-fpm via TCP `app:9000` |
| `oracle` | `gvenzl/oracle-xe:21-slim` | Oracle local pra dev (dados em volume) |

Sem Traefik, sem Redis em dev. Sessions/cache/queue no driver `database`.
Acesso: `http://localhost:8082` (porta 8080 ocupada por outros projetos —
remap pra 8082; Oracle em `localhost:1523`).

> Authelia **não roda em dev nem em prod via compose**. O middleware
> `AutheliaAuth` simula os headers via `.env` quando
> `AUTHELIA_DEV_BYPASS=true`. Em produção, o reverse proxy + Authelia do
> tribunal ficam à frente do nginx daqui (externos a este compose) e
> injetam os headers `Remote-*`.

`oracle` demora ~3 min pra ficar healthy na primeira subida — é normal.
Use `docker compose logs -f oracle` pra acompanhar e espere ver
"DATABASE IS READY TO USE!".

### Estrutura de arquivos Docker

```
.
├── Dockerfile                      # single-stage, código copiado in
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── local.ini               # upload/post/memory limits
├── docker-compose.yml              # base (dev)
├── docker-compose.prod.yml         # overrides para produção
├── docker-compose.test.yml         # overrides para CI/testes
├── .dockerignore
└── Makefile
```

### Boas práticas que seguimos

- **Dockerfile simples na raiz.** Single-stage. O código é copiado pra
  dentro da imagem; em dev um bind mount sobrepõe pra hot reload.
- **.dockerignore robusto.** `vendor/`, `node_modules/`, `.git/`, `.env`
  ficam fora do contexto de build.
- **Volume nomeado pros dados do Oracle dev** (`oracle-data`). Nunca
  dependa de bind mount pra dados.
- **Healthcheck no Oracle.** `depends_on: condition: service_healthy`
  garante que app/nginx só sobem depois do banco pronto.
- **Secrets via env**, nunca commitados. `.env.example` só tem
  placeholders.
- **Container app como `laravel` (uid 1000).** Mantenha os arquivos do
  projeto chowned pra `1000:1000` no host (em WSL/root, rode
  `chown -R 1000:1000 .` no setup inicial — sem isso composer/artisan
  falham por permissão).

### Ambientes

| Ambiente | Como subir |
|----------|------------|
| **Dev local** | `docker compose up -d` (usa `docker-compose.yml`) |
| **CI (GitHub Actions / GitLab CI)** | `docker compose -f docker-compose.yml -f docker-compose.test.yml run --rm app` |
| **Produção** | `docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d` (Authelia + reverse proxy externos a este compose) |

A diferença chave entre dev e prod:

- **Dev**: monta o código como volume (`./:/var/www/html`), Oracle local
  no compose (XE 21), HTTP em `localhost:8082`.
- **Prod**: código self-contained na imagem (sem bind mount), Oracle
  externo (do tribunal), Authelia + reverse proxy do tribunal à frente
  do nginx, secrets injetados pelo orquestrador.

### Pegadinhas comuns

- **Esqueceu de rodar `composer install` no container** depois de
  alterar `composer.json`? `docker compose exec app composer install`.
- **Mudou `.env`?** Reinicie a app: `docker compose restart app`.
- **Permissões em `storage/`, `bootstrap/cache/` ou `vendor/`?**
  Provável uid mismatch. O container roda como `laravel` (uid 1000);
  no host, `chown -R 1000:1000 .` resolve.
- **Oracle "ORA-12541: TNS:no listener"?** Aguarde mais — o Oracle ainda
  está subindo. Healthcheck só fica green quando está realmente pronto.
- **ORA-00942 em `SESSIONS` / `CACHE` / `JOBS`?** Falta migrar. Como
  usamos drivers `database` para sessions/cache/queue, as tabelas
  precisam estar criadas (`php artisan migrate`).
- **Conflito de portas (8080/1521)?** Outros projetos no host podem
  estar usando essas portas. Os defaults aqui são `8082` e `1523`.

---

## Arquitetura

### Camadas

```
HTTP (request)
   ↓
Middleware (AutheliaAuth → EnsureGroup)
   ↓
Controller (fino — só orquestra request/response)
   ↓
Service (lógica de negócio — onde mora o domínio)
   ↓
Model / Eloquent (persistência)
   ↓
Oracle
```

**Onde colocar lógica:**
- Lógica de domínio (regras: "não pode bater em feriado", "horas =
  saída − entrada", hash do snapshot) → `app/Services/`
- Validação de input HTTP, redirects, status codes → `app/Http/Controllers/`
- Relacionamentos, scopes simples, casts → `app/Models/`
- **Nunca** coloque lógica de negócio em controllers ou views. Se um
  controller passa de ~30 linhas, provavelmente está com lógica que
  pertence a um service.

### Services principais

| Service | Responsabilidade |
|---------|------------------|
| `CalendarioService` | Determina dias úteis/feriados/fds de um mês |
| `PontoService` | Bater entrada/saída com todas as regras |
| `AssinaturaService` | Hash + carimbo de tempo do snapshot mensal |
| `PdfService` | Geração da folha CIEE em PDF |

### Autenticação (Authelia)

A app **não tem tela de login**. Toda autenticação acontece no Authelia
atrás do reverse proxy, que injeta os headers:

- `Remote-User` — username
- `Remote-Groups` — grupos separados por vírgula (ex: `admin,estagiarios`)
- `Remote-Name` — nome completo
- `Remote-Email` — email

O middleware `AutheliaAuth` lê esses headers e popula `auth()->user()`
com um `Estagiario`. O middleware `EnsureGroup` valida o grupo por rota.

**Em desenvolvimento local**, use `AUTHELIA_DEV_BYPASS=true` no `.env`
pra simular um usuário sem subir o Authelia. Isso só funciona com
`APP_ENV=local`.

---

## Convenções de teste

### Estrutura

```
tests/
├── Unit/                    # services, models — sem banco quando possível
│   ├── Services/
│   │   ├── CalendarioServiceTest.php
│   │   ├── PontoServiceTest.php
│   │   └── AssinaturaServiceTest.php
│   └── Models/
└── Feature/                 # rotas/HTTP — com banco (sqlite in-memory)
    ├── PontoTest.php
    ├── FrequenciaTest.php
    ├── AssinaturaTest.php
    └── Admin/
        ├── FeriadoTest.php
        └── DashboardTest.php
```

### Banco de testes

Use **SQLite in-memory** pra testes (rápido, sem precisar de Oracle local).
As migrations são compatíveis com ambos. Configurar em `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Use trait `RefreshDatabase` em testes Feature.

### Padrões de teste

```php
// Nome do teste = comportamento, não método
public function test_estagiario_nao_consegue_bater_ponto_em_feriado(): void
{
    // Arrange
    Feriado::create(['data' => '2026-04-30', 'descricao' => 'Teste', 'tipo' => 'nacional']);
    $estagiario = Estagiario::factory()->create();
    Carbon::setTestNow('2026-04-30 10:00:00');

    // Act + Assert
    $this->expectException(ValidationException::class);
    app(PontoService::class)->baterEntrada($estagiario);
}
```

**Regras:**
- **Um assert principal por teste.** Asserções de setup/contexto OK.
- **Nomes em português, descritivos**, começando com `test_`.
- **AAA** (Arrange/Act/Assert) — comente as seções se ajudar.
- **Não teste implementação, teste comportamento.** Se você precisa
  mockar 4 coisas pra testar uma classe, o design provavelmente está ruim.
- **Use factories** (`Estagiario::factory()->create()`), não criação manual.
- **`Carbon::setTestNow()`** sempre que o teste depender de data/hora.
- **Não use `$this->markTestSkipped`** — se o teste não roda, deleta ele
  ou conserta.

### Cobertura

Meta: **>= 90% nos services**, **>= 80% no projeto todo**. Cobertura não é
o objetivo (testes ruins inflam cobertura), mas é um sinal — se cair muito,
investigue.

---

## Convenções de código

- **Laravel Pint** com preset `laravel` (já configurado). Roda no pre-commit.
- **PHP 8.2+**: use `readonly`, `match`, constructor promotion, named args.
- **Strict types** no topo de todo arquivo PHP novo:
  ```php
  <?php declare(strict_types=1);
  ```
- **Type hints sempre** — params, retorno, propriedades. `mixed` é cheiro.
- **Nomes em português** pra domínio (Estagiario, Frequencia, Feriado,
  bater, assinar, lotacao). **Nomes em inglês** pra infra/Laravel
  (Controller, Service, Middleware, Migration). Não misture no mesmo
  identificador (`StagiarioController` ❌, `EstagiarioController` ✅).
- **Migrations Oracle**: identifiers ≤ 30 caracteres pra compatibilidade
  ampla (use índice nomeado se necessário: `uq_freq_estagiario_data`).
- **Sem comentários óbvios.** Comentário explica *por quê*, não *o quê*.
  Se o código precisa de comentário pra ser entendido, refatore o código.

---

## Workflow ao implementar uma história

Quando você (Claude) recebe uma tarefa do tipo "implemente a história X
do REQUISITOS.md":

1. **Leia a história inteira** + critérios de aceitação. Não comece a
   codar antes de entender.
2. **Liste os critérios como casos de teste.** Mostre a lista pro humano
   antes de escrever os testes — alinhamento barato.
3. **Escreva o primeiro teste.** Rode. Confirme que falha.
4. **Implemente o mínimo pra passar.** Rode. Verde.
5. **Refatore.** Rode. Verde.
6. **Próximo teste.** Repita até cobrir todos os critérios.
7. **Rode `make check`** completo antes de declarar "pronto".
8. **Atualize o REQUISITOS.md** marcando a história como ✅ Done.

Se você se pegar implementando algo que não tem teste, **pare** e escreva
o teste. Se você se pegar escrevendo código que não tem critério na
história, **pare** e pergunte se entra na história ou vira nova.

---

## Anti-padrões a evitar

- ❌ Criar `helpers.php` global. Use injeção de dependência.
- ❌ Lógica de negócio em Blade. Views são burras.
- ❌ Query Eloquent direto em controller. Use repository ou método no model.
- ❌ `Auth::user()` em service. Receba o `Estagiario` por parâmetro
   (injeção explícita > acoplamento global).
- ❌ Testes que dependem de outros testes (ordem de execução, estado
   compartilhado). Cada teste é uma ilha.
- ❌ Mock excessivo. Se está mockando muito, considere testes de
   integração ou redesign.
- ❌ "Vamos fazer assim por enquanto, depois a gente arruma." Não. O
   "depois" não chega.

---

## Decisões já tomadas (não rediscutir sem motivo forte)

- **Oracle, não Postgres/MySQL.** Restrição institucional do tribunal.
- **Stack mínima espelhando `cadastro-magistrados`.** 3 serviços em dev:
   `app` (PHP 8.2-fpm + Oracle Instant Client + oci8), `nginx`, `oracle`
   (XE 21-slim). Sem Traefik, sem Redis, sem Authelia em compose. A
   simplicidade ganhou do isolamento que Traefik/Redis dariam — todos os
   projetos do time seguem esse mesmo formato.
- **Authelia em produção, simulação em dev.** Em prod o tribunal usa
   Authelia (LDAP/AD) atrás do reverse proxy de infra, externos a este
   compose; ele injeta os headers `Remote-*`. Em dev NÃO subimos
   Authelia: `AUTHELIA_DEV_BYPASS=true` simula os headers via `.env`, e
   a rota `/_dev/sessao` permite trocar usuário/grupo simulado em
   runtime sem reiniciar. Ver `docs/dev-sessao.md`.
- **Drivers `database` para sessions/cache/queue.** Sem Redis. Trade-off
   aceito: o ganho de Redis não justifica o serviço extra na stack;
   carga prevista é interna ao tribunal (dezenas de estagiários, não
   milhares de usuários simultâneos).
- **Assinatura por hash + carimbo, não ICP-Brasil.** Por enquanto. Authelia
   + 2FA dão segurança suficiente pra uso interno. Slot pronto pra trocar
   por PAdES no `AssinaturaService`.
- **gov.br Design System v3 (com tema TRE-AC), não AdminLTE/Tailwind.**
   Padrão obrigatório para sistemas do governo federal e seguido pelos
   tribunais. Tokens primários sobrescritos em `public/css/tre-ac-theme.css`
   para usar a navy institucional do TRE-AC (`#003366`) em vez do azul
   gov.br (`#1351b4`). Carregamos via CDN (jsdelivr) — quando precisarmos
   de bundle, troca-se pelo `@govbr-ds/core` via npm + Vite.
- **PDF via DomPDF, não Snappy/wkhtmltopdf.** DomPDF é PHP puro, não exige
   binário externo, suficiente pra layout da FCF.
- **Docker pra dev, CI e produção.** Mesma imagem em todos os ambientes;
   "funciona na minha máquina" deixa de existir. Em prod, orquestrado
   por Docker Swarm ou Kubernetes (decisão da infra do tribunal).
- **Oracle no compose APENAS pra dev.** Em CI usa SQLite in-memory,
   em produção conecta no Oracle externo do tribunal. Não rode Oracle
   em container em prod.
- **SQLite in-memory para testes.** Migrations são compatíveis com
   ambos os bancos. Trade-off aceito: testes rápidos > paridade total
   de banco. Funcionalidades específicas do Oracle (ex: sequences
   nomeadas) precisam de teste de integração separado.

Mudanças nessas decisões precisam de justificativa explícita no PR.

---

## Quando estiver em dúvida

- Releia a história em `REQUISITOS.md`.
- Verifique se já existe service/model que faz parte do que você precisa.
- Pergunte ao humano antes de inventar abstração nova.
- "Não sei" é resposta válida e melhor que adivinhação confiante.
