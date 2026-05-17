# STATUS — handoff entre sessões

> Doc curto pra retomar o trabalho. Atualizar ao fim de cada sessão de
> trabalho. Detalhes longos vivem em `CLAUDE.md`, `REQUISITOS.md`,
> `SPRINTS.md` e `docs/`.

**Última atualização:** 2026-05-16 (Sprint 8 fechada · H29–H34 áudio + player flutuante + reveal cinematográfico)

**Repo:** [`luqalefe/bena`](https://github.com/luqalefe/bena.git) — branch `main` ·
commit inicial em 2026-05-01 cobrindo todo o trabalho até o fim da Sprint 3.

---

## Onde paramos

✅ **Sprint 0 — Fundação** (parcial: falta H0.3 CI pipeline)
✅ **Sprint 1 — Núcleo (bater ponto ponta a ponta)** — fechada
✅ **Sprint 2 — Folha mensal e calendário** — fechada
✅ **Sprint 4 — Visão admin** (puxada pra antes da Sprint 3 a pedido do usuário)
- ✅ H16 — Cadastrar dados administrativos do estagiário (`/admin/estagiarios`)
- ✅ H14 — Dashboard de estagiários (`/admin`) — sem colunas de assinatura
- ✅ H15 — Ver folha de qualquer estagiário — admin vê todos; supervisor (filtro) e contra-assinar ficam pra Sprint 3

✅ **Sprint 3 — PDF + assinatura** — fechada
- ✅ Onda 1 — Infra de supervisor + H11 PDF (DomPDF)
- ✅ Onda 2 — H12 (estagiário assina) + H13 (supervisor contra-assina)
- ✅ Onda 3 — H20 (auto-liberado pra RH no `/admin`) + H10 (editar/remover feriado) + rota `/supervisor`

🚧 **Sprint 5 — CI + fechamento H16 + polimento** — em curso
- 🚧 **H0.3** — pipeline local pronto: `make ci` constrói a imagem `dev` e
  roda `composer install` + `pint --test` + `php artisan test --coverage
  --min=80` via `docker-compose.test.yml`. **Pendente:** criação do
  `.gitlab-ci.yml` quando o projeto migrar pro GitLab interno (2026-05-04);
  gate per-service ≥ 90 % (hoje 100 % na prática mas sem enforcement
  automatizado); branch protection no `main` (config UI).
- ✅ **H16** — fechada. Upload de contrato PDF
  (`Storage::disk('local')->putFile('contratos', ...)` + validação
  `mimes:pdf + mimetypes:application/pdf + max:5120`, novo deleta antigo)
  e download em `GET /admin/estagiarios/{id}/contrato` com autorização
  inline (admin OR self OR supervisor responsável → 403; 404 sem upload).
  13 testes novos.
- ✅ **H17** — fechada. `POST /frequencia/{ano}/{mes}/{dia}/observacao`
  cria/atualiza/remove observação (texto vazio = limpar; se a
  `Frequencia` era só observação, deleta). Dia útil obrigatório,
  só estagiário próprio (admin/supervisor 403), 422 após assinatura.
  Folha mostra observação inline + `<details>` com form de edição;
  PDF mostra na coluna Observação. 14 testes novos.
- ✅ **H18** — `AssinaturaService::diff(Assinatura)` compara snapshot
  gravado vs canônico atual e retorna lista de mudanças
  (`campo_alterado`/`dia_adicionado`/`dia_removido`). View
  `frequencia/show.blade.php` mostra `<details>` com diff ao lado do
  badge "⚠ alterada". Verificação só roda em show de folha individual.
  6 testes novos.
- ✅ **H19** — Migration `create_auditoria_table` (Oracle-safe).
  Model `Auditoria` append-only por convenção (sem update/delete).
  `AuditoriaService::registrar(usuario, acao, entidade, ?id, payload, ?ip)`
  serializa payload JSON. Hooks em PontoController (entrada/saida),
  ObservacaoController, AssinaturaController (assinar/contra-assinar/
  re-assinar/re-contra-assinar), FeriadoController (criar/editar/remover),
  EstagiarioController (editar). Tela `/admin/auditoria` com filtros
  por usuário, ação e intervalo de datas; limite 500 linhas; admin-only.
  Link "Auditoria" na nav admin. 10 testes novos (3 unit + 7 feature).
- ✅ **H21** — auto-fechamento de ponto esquecido (não estava na sprint
  original; pedido em 2026-05-02). `PontoService::fecharPontosAbertos`
  + `php artisan ponto:fechar-abertos` agendado `dailyAt('00:05')`.
  Saída = entrada + `horas_diarias` do estagiário. Coluna nova
  `saida_automatica` (boolean) + badge `batido (auto)` na folha e
  marcador `*` no PDF.
- ✅ **H22** — re-assinatura da versão atual (não estava na sprint
  original; pedido em 2026-05-02). Quando assinatura vigente está
  "⚠ alterada", botão "Re-assinar versão atual" aparece ao lado do
  badge. `AssinaturaService::reassinar()` marca anterior como
  `substituida_em` (histórico preservado) e cria nova. Re-assinar
  como estagiário invalida automaticamente a contra-assinatura do
  supervisor. Coluna nova `substituida_em`; unique antiga foi
  derrubada (vigente passou a ser garantida pelo service).
- ✅ **H23** — onboarding no primeiro acesso. Middleware
  `EnsureOnboarded` redireciona usuário com `tutorial_visto_em IS NULL`
  pra `/bem-vindo` quando ele entra em rotas home (`/`, `/admin`,
  `/supervisor`). View renderiza 5 cards explicativos do fluxo. Botão
  "Entendi" seta o timestamp. Página continua acessível pra revisitar.

✅ **Sprint 7a — Filtros inteligentes (UI/UX)** — fechada
- ✅ **H24** — `onchange="this.form.submit()"` nos selects (lotação,
  tipo, mês) e checkbox `liberadas` em `admin/estagiarios/index`,
  `admin/feriados/index` e `admin/dashboard`. Botão "Filtrar" passou
  pra `<noscript>` como fallback. Inputs de ano continuam manuais.
- ✅ **H25** — input de busca em `admin/estagiarios/index` e
  `admin/dashboard` com classe `bena-form__input`. JS vanilla puro
  em `@push('scripts')` normaliza NFD (`/[̀-ͯ]/g`) pra
  busca case-insensitive e sem acentos. Filtra `<tr>` em tempo real.

✅ **Sprint 7b — Calendário visual (UI/UX)** — fechada (refluxo posterior)
- ✅ **H26** — `GET /calendario` agora renderiza o **mês atual** por
  padrão. `GET /calendario/{ano}/{mes}` mostra mês específico. Todos
  os grupos auth podem visualizar. Paleta RGB temática por mês via
  `--cal-theme`, fim de semana azul, feriado âmbar com dot e tooltip
  CSS de hover (descrição). Hoje com border navy. Para admin: click
  em dia vazio abre `<dialog>` com form de criar feriado (POST honra
  `redirect_to=/calendario/...` whitelistado).
- ✅ **H27** — link "Calendário" na `.bena-nav` (visível para todos,
  antes do "Sobre") e botão "Calendário · feriados" na dashboard do
  estagiário ao lado do "Ver folha mensal".

✅ **Refluxo de feriados (admin)** — fechada
- View `/admin/feriados` (listing) **removida**. Adição de feriado vira
  fluxo do calendário (admin clica num dia vazio → modal). Edit/destroy
  continuam acessíveis pelos próprios feriados clicáveis no calendário.
- `FeriadoController::index()` removido. Rota `admin.feriados.index`
  removida. Adição/edição/exclusão admin-only via
  `ConfigureUserSession::adminOnlyRouteNames`.
- Redirects de mutação: `store` sem `redirect_to` → `calendario.index`;
  com `redirect_to=/calendario/...` (whitelist) → respeita; `update` →
  `calendario.mes` do mês do feriado; `destroy` → `calendario.index`.

✅ **Sprint 7c — Buddy / mascote (H28)** — fechada
- ✅ **H28** — 8 buddies do **pool padrão** (coruja🦉, gato🐱, cachorro🐶,
  capivara🦫, papagaio🦜, tartaruga🐢, pinguim🐧, sapo🐸) com
  personalidades distintas, atribuído ao grupo `'E'`. Persistido em
  `estagiarios.buddy_tipo` (string(20) nullable, migration nova após
  `tutorial_visto_em`).
- ✅ **Pool sênior (`tipos_supervisores`)** para grupos `'0'` (admin) e
  `'S'` (supervisor): 4 mascotes mais maduros (águia 🦅, leão 🦁,
  elefante 🐘, urso 🐻) em tom de mentoria.
  `BuddyService::garantirBuddy(Estagiario, ?string $grupo)` roteia
  para o pool certo na primeira atribuição.
- ✅ `BuddyService::montar(Estagiario, statusPonto)` retorna `BuddyData`
  (DTO readonly: tipo, emoji, nome, frase) por
  `(dia, status, dia + bloco de 12h)` — frase estável dentro do bloco.
- ✅ `BuddyService::boasVindas(Estagiario)` retorna BuddyData com frase
  de apresentação, usado na onboarding.
- ✅ Card visível: na **dashboard** só para grupo `'E'` (estagiário);
  na tela `/bem-vindo` para **todos os grupos** (apresentação faz parte
  do onboarding). Variante CSS `bena-buddy-card--apresentacao` com
  avatar 3.5rem e rodapé explicativo.
- ✅ Animação respeita `prefers-reduced-motion`.

✅ **Code review pré-commit** — segurança e consistência
- 🔒 `FeriadoController::store`: regex `^/calendario(/|\?|$)` substituiu
  `str_starts_with('/calendario')` — bloqueia ambiguidades como
  `/calendariofake`, `//evil.com`, `javascript:`. Teste novo cobre o
  caso. Não havia open redirect explorável (Laravel resolve no host
  atual), mas é boa prática defensiva.
- 📝 `BuddyService::montar` comentário reescrito (era "ímpar/par",
  passou a "blocos de 12h" — reflete a lógica real).

✅ **Página `/mascotes` + frases com tema eleitoral**
- ✅ Rota `GET /mascotes` (`MascotesController::index`) acessível para
  todos os grupos auth. Lista os 12 mascotes com avatar, nome,
  personalidade e história curta ligada à Justiça Eleitoral do Acre.
  Pool padrão em cards brancos, pool sênior em cards âmbar.
- ✅ Cada perfil em `config/buddies.php` ganhou as chaves
  `personalidade` (string curta) e `historia` (parágrafo conectando
  à JE do Acre — chegada da urna eletrônica em 1996, transporte
  aéreo das urnas pela Amazônia, primeira urna em comunidade
  indígena, etc.).
- ✅ Botão **"Conhecer todos os mascotes"** no `/bem-vindo`, logo
  abaixo do card de apresentação do buddy.
- ✅ As ~150 frases dos buddies em `config/buddies.php` (12 × 5 × 3
  + boas_vindas + generica) foram **reescritas com tema eleitoral**:
  urna, ata, apuração, pleito, BU (boletim de urna), mesário, mesa
  receptora, recurso, diplomação, ciclo eleitoral, etc., **mantendo
  a personalidade** de cada buddy.
- ✅ 5 testes feature em `MascotesPageTest` iteram sobre o config
  (resilientes a edição/expansão de mascotes futuros).

✅ **Sprint 8 — Áudio, player flutuante e reveal cinematográfico (H29–H34)** — fechada
- ✅ **H29** — Carta lendária **Waldirene** (10ª da STI), ancorada na
  carreira real da Desembargadora Waldirene Cordeiro: UFAC 1991,
  promotora em Xapuri 1998, presidente do TJAC 2021-23, presidente do
  TRE-AC desde ago/2025 (comandando o pleito de 2026). Flavor extraído
  do discurso de posse. Sprite pixel art 128×128 gerado no PixelLab.
- ✅ **H30** — Histórias dos **22 buddies expandidas pra ~600 chars**
  cada (mesmo tamanho da Waldirene), com ano/local específico, quirk de
  comportamento e citação característica. Primeiros 30 chars preservados
  pra não quebrar testes existentes.
- ✅ **H31** — Mini player flutuante estilo **macOS Spotify** —
  arrastável (mouse+touch) com posição persistente em `sessionStorage`,
  cover quadrada = pixel art do mascote do usuário, controles
  (play/pause/mute/volume) revelados em hover, barra de progresso fina
  no topo, **X vermelho** de fechar com flag dismissed persistente. CSS
  em `layouts/app.blade.php` dentro de `@auth`.
- ✅ **H32** — **Slot machine de 4s** no botão "Descobrir meu mascote".
  `requestAnimationFrame` cicla sprites com deceleração quadrática
  (60ms → 400ms entre swaps). **Sync exato com o cover do mini player no
  T=0 e T=4s** (mesmo frame que a carta de reveal). Pré-cache de todos
  os sprites pra zero flicker. Lazy query do player no click handler
  (script é injetado em `<main>` antes do `<div id="bena-player">`
  existir no DOM).
- ✅ **H33** — **Turbo (Hotwire) via CDN** + `data-turbo-permanent` no
  player → trilha sonora (`bena-master.mp3`) contínua entre views, sem
  corte de áudio. `data-turbo="false"` em downloads de PDF e form de
  upload de contrato. Twemoji refatorado pra rodar em `turbo:load`.
- ✅ **H34** — **SFX da urna eletrônica** ao bater ponto
  (`urna-song.mp3`, ~112KB pré-carregado). Handler global no `submit`
  (capture phase) filtra forms de `ponto/entrada` ou `ponto/saida` e
  dispara `play()` do zero. Sentinel `window.__benaUrnaSongBound` evita
  bind duplicado em Turbo Visits. `<audio>` marcado
  `data-turbo-permanent` pra reprodução não cortar no
  morph pós-submit.

**Decisões importantes da Sprint 8:**
- Player **suprimido no primeiro reveal de `/bem-vindo`** pra não dar
  spoiler — `@php` do layout checa `routeIs('onboarding.show') &&
  tutorial_visto_em === null` e renderiza placeholder ("Aguardando
  sorteio…") em vez do buddy.
- **Sem fallback hardcoded** de Lucander no player. Quando o usuário
  ainda não tem buddy, placeholder com ícone de música.
- Pool lendário **mantido exclusivo** pra STI/SSEC (`lotacoes_lendarias`)
  após experimentos com pool aberto que diluíram a raridade.
- Dev user `lucas.dev` configurado com `setor_id` apontando pra setor STI
  (criado se não existir).

**Suíte:** 437 testes, 1055 assertions · cobertura ≥ 80 % (gate `--min=80`)

### Mudanças de modelagem em 2026-05-01 (registradas em REQUISITOS.md)
Atores refinados: **Supervisor** vira grupo Authelia próprio (`supervisores`),
ligado a 1↔N estagiários via novo campo `Estagiario.supervisor_username`.
**RH/Admin** baixa o PDF assinado pra anexar no SEI (nova história H20). H13
ganha checagem de "supervisor responsável". H16 ganha upload de contrato PDF
(coluna `contrato_path`) e `supervisor_username`. As partes novas da H16
(contrato + supervisor_username) ainda não foram implementadas — entram numa
re-abertura quando o tema for retomado.

### Refactor de auth no caminho da H8
Substituímos `AutheliaAuth` por `ConfigureUserSession` (padrão institucional
do tribunal). Mantém o que o `AutheliaAuth` fazia (Estagiario via
firstOrNew + `Auth::setUser`) e adiciona o que o pattern do tribunal
trouxe: `session(['user', 'matchingGroups', 'grupodeacesso'])` +
`retornaAcesso()` (`admin`→`'0'`, `estagiarios`→`'E'`) + lista
`$adminOnlyRouteNames` que aborta 403 por nome de rota.

Alias do middleware mudou de `authelia.auth` → `configure.session`.

---

## Como subir e validar em 30 segundos

```bash
make up                         # docker compose up -d
docker compose ps               # checar 5 healthy: app, nginx, oracle, redis, traefik
make test                       # 166 verde / 374 assertions
```

Depois abre `https://ponto.localhost` no navegador (aceitar cert
auto-assinado). Banner magenta no topo + dashboard com cards.

> **Não precisa mexer em `/etc/hosts`** — Chrome/Firefox/Edge resolvem
> `*.localhost` para 127.0.0.1 automaticamente (RFC 6761).

Pra trocar usuário simulado: `https://ponto.localhost/_dev/sessao` ou
clica em "Trocar usuário" no banner.

---

## Stack que está rodando

| Serviço | Imagem | Porta no host | Função |
|---------|--------|---------------|--------|
| `app` | `ponto-app` (custom) | — (interno) | Laravel 11 + PHP 8.2 + oci8 |
| `nginx` | `nginx:1.27-alpine` | — | Proxy pra php-fpm |
| `oracle` | `gvenzl/oracle-free:23.5-slim-faststart` | **1521** | Oracle 23ai (dev) |
| `redis` | `redis:7.4-alpine` | — | Cache + sessões Laravel |
| `traefik` | `traefik:v3.4` | **80, 443, 8080** | TLS + roteamento |

**Sem Authelia em dev.** A simulação de identidade vive em
`AUTHELIA_DEV_BYPASS=true` + `/_dev/sessao`. Authelia entra em prod via
`docker-compose.prod.yml`.

---

## Próximo passo concreto — Sprint 5 ✅ fechada · próxima Sprint 6 (Hardening)

Todas as histórias de produto da Sprint 5 fecharam (H0.3 parcial fica
como pendência de infra do GitLab interno). Próxima fronteira:
**Sprint 6 (Hardening / Homologação)** — NFRs de segurança (NF1-NF7),
deploy via Swarm/K8s, smoke tests E2E, backup automatizado, doc
operacional. Não é desenvolvimento de features, é preparação pra entrar
em homologação no tribunal.

Pendente da Sprint 5 (por dependência de infra externa):

1. 🚧 **H0.3** — `.gitlab-ci.yml` aguarda migração pro GitLab interno
   do tribunal. Pipeline local (`make ci`) pronto e validado.

**Decisões tomadas nas últimas sessões:**
- **H7 descartada** (virada meia-noite) — domínio garante turno até 19h.
- **3 grupos no Authelia:** `admin` (RH), `supervisores`, `estagiarios`.
- **Assinatura = hash + log autenticado** (SHA-256 do snapshot canônico).
  Nada de ICP-Brasil por enquanto. Slot pronto pra trocar por PAdES no
  `AssinaturaService` quando exigido.
- **PDF gerado pelo DomPDF** com bloco de assinatura preenchido
  automaticamente após H12/H13. Layout pixel-perfect do CIEE fica como
  débito visual pra futuro polimento.

---

## Arquivos-chave (já existentes, não esqueça)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   └── FeriadoController.php  ← H8/H9
│   │   ├── DashboardController.php
│   │   ├── DevSessionController.php
│   │   ├── FolhaMensalController.php  ← H5 (show + redirectMesCorrente)
│   │   └── PontoController.php
│   └── Middleware/
│       ├── ConfigureUserSession.php   ← lê headers OU bypass; popula sessão; lista admin-only
│       └── EnsureNotProduction.php    ← bloqueia /_dev em prod
├── Models/
│   ├── Estagiario.php  (Authenticatable)
│   ├── Frequencia.php  (entrada/saida via Attribute Carbon ↔ string)
│   └── Feriado.php
└── Services/
    ├── CalendarioService.php   (ehDiaUtil, ehFeriado, feriadosDoAno)
    ├── DashboardService.php
    ├── DashboardData.php       (DTO readonly)
    ├── DiaFolha.php            ← DTO readonly (data, tipo, frequencia, descricaoFeriado)
    ├── FolhaMensal.php         ← DTO readonly (ano, mes, dias, totalHoras)
    ├── FolhaMensalService.php  ← H5 (montar)
    └── PontoService.php

resources/views/
├── layouts/app.blade.php       ← gov.br DS via CDN + tema TRE-AC + banner dev
├── admin/feriados/
│   ├── index.blade.php         ← H8 (lista + filtro)
│   └── create.blade.php        ← H9 (form)
├── frequencia/show.blade.php   ← H5 (folha mensal)
├── dashboard.blade.php
└── dev/sessao.blade.php

config/
├── authelia.php                ← dev_bypass + dev_user/groups/name/email
└── oracle.php                  ← sessionVars NLS_* (publicado do yajra)

public/css/tre-ac-theme.css     ← override de tokens primários

routes/web.php                  ← / (dashboard), /ponto/{entrada,saida},
                                  /frequencia[/{ano}/{mes}],
                                  /admin/feriados[/criar], /_dev/sessao*
```

---

## Pegadinhas resolvidas (nessa sessão)

Lista do que custou tempo. Vale lembrar pra evitar re-aprender:

1. **Oracle 29.x + Traefik docker provider** → Docker Desktop expõe socket
   que retorna 400 em `/info`. Usei roteamento via arquivo
   (`docker/traefik/dynamic/routers.yml`).

2. **`env_file: .env` no compose vazava vars pro `$_SERVER`** → Laravel
   `env()` prefere `$_SERVER` sobre `getenv()`, então phpunit.xml
   `<env force="true">` era ignorado em testes (DB_CONNECTION=oracle
   shadowava sqlite). Removi `env_file:` do app — Laravel/Dotenv lê
   `.env` do disco direto.

3. **Authelia v4.38 rejeita `localhost` como cookie domain** (precisa de
   ponto). Tentamos `*.localhost.test` antes de remover Authelia do dev.
   Como agora não há Authelia em dev, voltei pra `*.localhost` (resolve
   sem `/etc/hosts`).

4. **`.env.testing` exigido quando phpunit.xml seta `APP_ENV=testing`**
   → Laravel não faz fallback pra `.env`. Precisa criar `.env.testing`
   com `APP_KEY` etc.

5. **`phpunit.xml` `<env>` precisa de `force="true"`** se a var já
   estiver setada no environment (caso do compose sem env_file resolveu,
   mas o force ficou de defesa).

6. **Oracle não tem `TIME` type** → `$table->time()` vira `DATE` e
   quebra com `ORA-01843` ao receber `'09:30:00'`. Solução: usar
   `string(8)` HH:MM:SS + `Attribute` no model com getter Carbon e
   setter string.

7. **Cast `date:Y-m-d` causa `ORA-01843`** quando NLS_DATE_FORMAT está
   `YYYY-MM-DD HH24:MI:SS`. Usei cast `date` puro (envia datetime
   `2026-05-04 00:00:00`) que bate com NLS default.

8. **`config/oracle.php`** é mergeado em `database.connections` pelo
   yajra (no service provider). Publicar pra alterar `sessionVars` —
   editar em `config/database.php` direto **não** funciona.

9. **Volume Oracle de build anterior** pulou criação do user porque o
   schema já existia. Daí o
   `docker/oracle/init/01-create-app-user.sql` idempotente.

10. **Docker socket bind-mount cache** do Docker Desktop quebra com
    `docker compose restart` após editar arquivo montado. Usar
    `docker compose up -d --force-recreate <serviço>`.

11. **PHP `Redis` extension ausente** → instalei `predis/predis` via
    composer (composer package, sem extension). Atualizar `.env` pra
    `REDIS_CLIENT=predis` (default era `phpredis`).

12. **Image dev em ~227MB** (alvo NF31 < 300MB pra prod). gcompat +
    Oracle Instant Client são os pesados.

13. **`Rule::unique` falha quando coluna data é armazenada como datetime**
    no SQLite (cast `date` puro grava `2026-09-07 00:00:00`). Buscar
    com `whereDate` em closure validation rule customizada. Não usar
    `Rule::unique('feriados','data')` — a comparação é exata e o valor
    no banco tem `00:00:00` que não bate com `'2026-09-07'`.

---

## TODO conhecidos (fora de sprints)

- [ ] **Refatorar `ConfigureUserSession` para o pattern completo do tribunal**
  quando vierem mais rotas. Hoje implementa só `$adminOnlyRouteNames`;
  o exemplo institucional também tem `$bypassRouteNames`,
  `$magistradoAllowedRoutes`, e leitura via `$request->server(...)`. A
  base já está no formato certo, falta exercitar.
- [ ] **H0.3 follow-ups** — criar `.gitlab-ci.yml` quando o projeto
  migrar pro GitLab interno (2026-05-04). Reaproveitar
  `docker-compose.test.yml` (`make ci`). Adicionar gate de cobertura
  **per-service ≥ 90 %** (hoje só `--min=80` global; implementar parser
  de `clover.xml` ou `php artisan test --coverage-clover` + script
  de verificação por diretório). Configurar branch protection em `main`
  no UI do GitLab.
- [ ] Migrar `gov.br DS` de CDN pra bundle local quando precisar de JS
  dos componentes (dropdown, modal). Hoje só temos CSS do CDN.
- [ ] Adicionar `phpredis` extension ao Dockerfile e voltar
  `REDIS_CLIENT=phpredis` (predis funciona, mas phpredis é mais rápido).
- [ ] Authelia real em prod via `docker-compose.prod.yml` (LDAP do
  tribunal). Documentado nas decisões; ainda não codificado.
- [ ] **Teste de integração** rodando contra Oracle real (não SQLite).
  Pegamos o `ORA-01843` só batendo no Oracle de dev. Adicionar suite
  separada que sobe oracle e roda subset crítico.
- [ ] Aviso de "USER DEPRECATED" do `brick/math` quando float vai pro
  cast `decimal:2`. Investigar e silenciar ou fazer cast string antes
  do save.

---

## Comandos úteis (cola rápido)

```bash
# diário
make up                             # sobe stack
make test                           # 166 testes
make pint                           # auto-fix style
make check                          # pint --test + test  (rodar antes do commit)

# debug
make logs                           # tail de todos
make shell                          # bash no app container
docker compose exec -T app php artisan tinker

# DB
make migrate                        # aplica migrations no oracle dev
docker compose exec -T oracle bash -c "sqlplus ponto/ponto_dev_only@//localhost:1521/FREEPDB1"

# trocar usuário simulado em runtime
# (sem reiniciar): https://ponto.localhost/_dev/sessao
```
