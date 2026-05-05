# Arquitetura

Como o Bena está organizado por dentro: do request HTTP ao banco, e
das integrações externas. Este doc complementa
[`visao-geral.md`](./visao-geral.md) — lá explica o **quê** e **por
quê**; aqui detalha o **como**.

---

## 1. Diagrama de camadas

```
┌────────────────────────────────────────────────────────────────────┐
│                       PRODUÇÃO (tribunal)                          │
│  ┌──────────────┐   ┌──────────────┐   ┌────────────────────────┐  │
│  │ Reverse proxy│──>│   Authelia   │──>│  nginx (compose Bena)  │  │
│  │ TLS, FQDN    │   │  LDAP/AD/2FA │   │  injeta Remote-* hdrs  │  │
│  └──────────────┘   └──────────────┘   └────────────┬───────────┘  │
└────────────────────────────────────────────────────┼───────────────┘
                                                     │ fastcgi
                                                     ▼
┌────────────────────────────────────────────────────────────────────┐
│                        APP (PHP-FPM)                               │
│                                                                    │
│   Routing                                                          │
│      │                                                             │
│      ▼                                                             │
│   Middleware pipeline                                              │
│      ├─ ConfigureUserSession  (resolve user, gate admin-only)      │
│      ├─ EnsureOnboarded       (redireciona pra /bem-vindo)         │
│      └─ EnsureNotProduction   (bloqueia /_dev em prod)             │
│      │                                                             │
│      ▼                                                             │
│   Controller (HTTP fino)                                           │
│      │                                                             │
│      ▼                                                             │
│   Service (lógica de domínio)        ────► TreApiClient (HTTP out) │
│      │                                                             │
│      ▼                                                             │
│   Eloquent Model                                                   │
│      │                                                             │
│      ▼                                                             │
│   oci8 / SQLite                                                    │
└────────────────────────────────────────────────────┬───────────────┘
                                                     │
                                                     ▼
                                          ┌─────────────────────┐
                                          │  Oracle institucional│
                                          │      (externo)       │
                                          └─────────────────────┘
```

**Princípio de separação:**

- **Controller**: valida entrada HTTP, chama service, escolhe view ou
  resposta. Sem regra de negócio. Idealmente ≤ 30 linhas por método.
- **Service**: lógica de domínio. Recebe modelos via parâmetro (não
  acessa `auth()->user()` direto — injeção explícita).
- **Model**: relacionamentos, casts, scopes simples. Sem regra de
  negócio.

---

## 2. Pipeline de uma request

Exemplo: estagiário bate entrada em `POST /ponto/entrada`.

1. **Reverse proxy + Authelia** (em prod) validam sessão SSO. Se OK,
   encaminham com headers `Remote-User`, `Remote-Groups`,
   `Remote-Name`, `Remote-Email`.
2. **nginx** do compose recebe e passa pra `app:9000` via FastCGI.
3. **`ConfigureUserSession`**:
   - Lê headers `Remote-*` (ou simula em dev via `.env` quando
     `AUTHELIA_DEV_BYPASS=true`).
   - Resolve grupo (`admin` → `'0'`, `supervisores` → `'S'`,
     `estagiarios` → `'E'`).
   - `firstOrNew(['username' => $remoteUser])` → cria/atualiza
     `Estagiario` (mesmo admin/supervisor é um `Estagiario` registrado;
     o que muda é o grupo na sessão).
   - Popula `session(['user'=>..., 'matchingGroups'=>..., 'grupodeacesso'=>...])`.
   - Se a rota está em `$adminOnlyRouteNames` e o grupo não é `'0'` →
     `abort(403)`.
   - `Auth::setUser($estagiario)`.
4. **`EnsureOnboarded`** (na rota `dashboard`): se
   `tutorial_visto_em` é `null`, redireciona pra `/bem-vindo`.
5. **`PontoController::entrada`**: valida CSRF, chama
   `PontoService::baterEntrada($user, $request->ip())`.
6. **`PontoService`**:
   - `garantirAtivo()` — `ativo === true`.
   - `garantirVigencia()` — `inicio_estagio ≤ hoje ≤ fim_estagio`.
   - `garantirNaoEmRecesso()` — sem `RecessoEstagiario` cobrindo `hoje`.
   - `CalendarioService::ehDiaUtil()` — não é fds nem feriado.
   - Não duplica entrada do dia.
   - `Frequencia::create(['data'=>hoje, 'entrada'=>now()->H:i:s, ...])`.
7. **Response**: redirect pra `/ponto/sucesso?evento=entrada&horario=…`
   com flash de sucesso.

Qualquer `DomainException` no service vira flash de erro + redirect
back. Validation errors viram 422 (JSON) ou redirect com errors (HTML).

---

## 3. Middlewares

### `ConfigureUserSession` — pilar da autenticação

`app/Http/Middleware/ConfigureUserSession.php`

Substitui o `auth` padrão do Laravel. Lê os headers que o Authelia
injeta em prod (ou simulação dev) e popula:

- `auth()->user()` — `Estagiario` (criado/atualizado ad-hoc).
- `session('user')` — `['username'=>..., 'groups'=>...]`.
- `session('matchingGroups')` — interseção entre `Remote-Groups` e os
  grupos válidos do sistema.
- `session('grupodeacesso')` — letra do papel principal:
  - `'0'` para `admin`
  - `'S'` para `supervisores`
  - `'E'` para `estagiarios`

**Whitelist de rotas admin-only** está hardcoded no array
`$adminOnlyRouteNames`. Outras rotas (ex.: bater ponto, ver folha)
são acessíveis a qualquer grupo autenticado e validam autorização
adicional dentro do controller (ex.: `SupervisorDashboardController`
filtra estagiários por `supervisor_username`).

> Em dev, `AUTHELIA_DEV_BYPASS=true` faz com que `resolverIdentidade()`
> leia das envs `AUTHELIA_DEV_USER`, `AUTHELIA_DEV_GROUPS`, etc. A rota
> `GET/POST /_dev/sessao` permite trocar isso em runtime sem reiniciar
> o container — ver [`dev-sessao.md`](./dev-sessao.md).

### `EnsureOnboarded`

`app/Http/Middleware/EnsureOnboarded.php`. Se `tutorial_visto_em IS
NULL`, redireciona para `/bem-vindo`. Aplicado nas rotas que assumem
o usuário "preparado": `dashboard`, `admin.dashboard`,
`supervisor.dashboard`. Outras rotas (incluindo `/bem-vindo`,
`/_dev/*`, `/mascotes`, `/ponto/*`) são acessíveis durante o onboarding.

### `EnsureNotProduction`

`app/Http/Middleware/EnsureNotProduction.php`. Aborta com 404 se
`APP_ENV === 'production'`. Aplicado em `/_dev/*`. Defesa em
profundidade pra um esquecimento do `AUTHELIA_DEV_BYPASS=false`.

---

## 4. Services em detalhe

### `PontoService` (`app/Services/PontoService.php`)

Coração do registro de ponto. Métodos:

- `baterEntrada(Estagiario, ?ip): Frequencia` — valida e grava.
- `baterSaida(Estagiario, ?ip): Frequencia` — calcula horas, grava.
- `fecharPontosAbertos(): int` — chamado pelo schedule diário 00:05.
  Pega `Frequencia` de dias passados com `entrada NOT NULL` e
  `saida IS NULL`, fecha com `saida = entrada + horas_diarias` e marca
  `saida_automatica = true`. Idempotente.

Validações lançam `DomainException` com mensagem em pt-BR pra ir direto
pro flash do usuário.

### `CalendarioService` (`app/Services/CalendarioService.php`)

- `ehDiaUtil(CarbonInterface): bool` — não é fds nem feriado.
- `ehFeriado(CarbonInterface): bool`.
- `feriadosDoAno(int): Collection<Feriado>` — inclui recorrentes
  remapeados pro ano consultado (ex.: Natal `2020-12-25` recorrente
  vira `2026-12-25` quando consulta 2026).

### `AssinaturaService` (`app/Services/AssinaturaService.php`)

Modelo SEI: hash + carimbo de tempo, sem ICP-Brasil.

- `canonicalSnapshot($estagiario, $ano, $mes): array` — campos
  significativos das frequências do mês, ordem fixa por data ASC, sem
  timestamps internos. JSON UTF-8 sem escape.
- `hash(array): string` — `sha256(json_encode(snapshot))`.
- `assinar($estagiario, $ano, $mes, $papel, $assinante, ?$ip): Assinatura`
  - papel `estagiario` ou `supervisor`.
  - Supervisor só pode assinar se estagiário já assinou.
  - Cada (estagiario, ano, mes, papel) só pode ter UMA assinatura
    ativa (não-substituída). Tentar assinar duas vezes lança.
- `verificar($estagiario, $ano, $mes): array` — re-calcula hash do
  snapshot atual e compara com cada assinatura ativa, retorna
  `[['papel'=>..., 'integro'=>bool, 'assinatura'=>...]]`.
- `diff(Assinatura): array` — produz lista de mudanças entre o
  snapshot gravado e o atual: `dia_adicionado`, `dia_removido` ou
  `campo_alterado` (com `campo`, `antes`, `depois`).
- `reassinar(...): Assinatura` — quando hash divergiu. Marca
  `substituida_em` na atual e cria nova. Quando re-assinatura é como
  `estagiario`, **invalida também** a contra-assinatura do supervisor
  (folha mudou, supervisor precisa rever).

### `ConformidadeService` (`app/Services/ConformidadeService.php`)

Alertas exibidos no `/admin` (Lei 11.788/2008):

- `tce_vencendo` — `fim_estagio` está nos próximos 30 dias.
- `sem_recesso` — estágio com 12+ meses e nenhum `RecessoEstagiario`
  com `inicio` no último ano.
- `jornada_excedida` — soma de `frequencias.horas` na semana corrente
  > `horas_diarias × 5`.

Cada alerta tem código fixo (constantes) e descrição via
`descricao(string $codigo)`.

### `BuddyService` (`app/Services/BuddyService.php`)

Sistema de mascotes. Detalhes narrativos em [`lore.md`](./lore.md);
mecanicamente:

- `garantirBuddy(Estagiario, ?string $grupo): void` — se
  `buddy_tipo IS NULL`, sorteia do pool baseado em `(grupo, lotação)`.
  - Estagiário (`'E'`) lotado em STI/SSEC → `tipos_lendarios` (9 cartas).
  - Estagiário fora dessas lotações → `tipos` (8 padrão).
  - Supervisor/admin (`'S'`/`'0'`) → `tipos_supervisores` (4 sêniores).
- `montar(Estagiario, string $statusPonto): BuddyData` — escolhe
  frase determinística por `(dia_da_semana, status_ponto, bloco_de_12h)`.
  Frase estável dentro do bloco e varia ao longo do dia/semana.
- `boasVindas(Estagiario): BuddyData` — frase do pool `boas_vindas`
  do mascote (apresentação).

Frases ficam em `config/buddies.php`. ~150 entradas, todas com
vocabulário da Justiça Eleitoral (urna, ata, mesário, BU, pleito).

### `TreApiClient` (`app/Services/TreApiClient.php`)

Cliente HTTP com cache para as APIs do TRE-AC:

| Método | Endpoint | Retorno | TTL |
|---|---|---|---|
| `unidades()` | `/painel/view/api/unidades/` | `string[]` (siglas) | 1h |
| `lotacoes()` | `/painel/view/api/lotacao/` | `array<string,int>` (sigla → quantidade) | 1h |

Tratamento de erro unificado: timeout, HTTP ≠ 2xx, JSON inválido e
payload não-list todos lançam `RuntimeException`. Cache via driver
`database` (`Cache::remember`). Configuração em `config/services.php`
sob a chave `tre_ac` (`base_url`, `timeout`, `cache_ttl`).

A API `/ferias/setor/?sigla=XXX` está mapeada e validada
(retorna servidores em férias com `MAT_SERVIDOR`, `NOM`, `SIGLA_UNID_TSE`,
3 períodos de férias por ano), mas **ainda não é consumida** —
guardada para a feature de "supervisão por suplência durante férias".

---

## 5. Comandos Artisan customizados

| Comando | Quando roda | O que faz |
|---|---|---|
| `php artisan ponto:fechar-abertos` | Diário 00:05 (schedule) | Chama `PontoService::fecharPontosAbertos`. Pega frequências esquecidas e fecha com `saida = entrada + horas_diarias`. Idempotente. |
| `php artisan setores:sincronizar` | Diário 03:00 (schedule) | Chama `TreApiClient::unidades()` e `lotacoes()`, mescla, faz upsert em `setores` por sigla. Setores sumidos da API viram `ativo=false`; voltaram → reativam. |

Ambos podem ser rodados manualmente sem efeito colateral. Schedule
declarado em `routes/console.php`.

---

## 6. Banco de dados

### Estratégia tri-banco

- **Dev** → Oracle XE 21-slim no compose (volume nomeado `oracle-data`).
- **CI / testes** → SQLite in-memory (`phpunit.xml`). Migrations
  compatíveis com ambos.
- **Prod** → Oracle institucional do tribunal. Migrations idempotentes
  garantem reaplicação segura.

### Cuidados Oracle

- **Sem tipo `TIME`**: usar `string(8)` `HH:MM:SS` + `Attribute` no
  model (ver `Frequencia::entrada()` e `saida()`).
- **Identifiers ≤ 30 caracteres** — afeta nomes de índices e
  constraints. Use índice nomeado quando o auto-gerado passar:
  `$table->unique('username', 'uq_estagiarios_username')`.
- **`Rule::unique` em coluna data** com cast `date` puro pode falhar
  por ambiguidade — usar `whereDate` numa closure de validação.
- **Schema multitenancy**: identifiers ficam em `UPPER` no Oracle, mas
  o Eloquent abstrai. Não há divergência de comportamento entre dev e
  prod.

### Migrations

`database/migrations/` (ordenado por timestamp). Highlights:

- `0001_01_01_000001_create_cache_table` / `0001_01_01_000002_create_jobs_table`
  — defaults do Laravel (driver `database`).
- `2026_05_01_021636_create_estagiarios_table` — base.
- `2026_05_01_175431_create_assinaturas_table` — snapshot, hash,
  papel, IP, `substituida_em`.
- `2026_05_03_180000_create_auditoria_table` — log append-only.
- `2026_05_03_200000_create_recessos_estagiario_table`.
- `2026_05_04_140000_create_supervisores_table` — entidade nova.
- `2026_05_05_120000_create_setores_table` — sincronização TRE-AC.
- `2026_05_05_130000_replace_lotacao_with_setor_id_on_estagiarios`
  — data migration normaliza `Nª ZONA → Nª ZE` antes de dropar coluna.

---

## 7. Integrações externas

### Authelia (produção)

Não está no compose. Configurado pelo time de infra do tribunal,
atrás do reverse proxy. Injeta os headers `Remote-User`,
`Remote-Groups`, `Remote-Name`, `Remote-Email`. Grupos relevantes:
`admin`, `supervisores`, `estagiarios` (via LDAP/AD).

### TRE-AC Visão API

Base: `https://visao.tre-ac.jus.br/painel/view/api/`. Endpoints
consumidos por `TreApiClient`:

- **`/unidades/`** — lista de 80 siglas (`SIGLA_UNID_TSE`). Sem nome
  longo, sem hierarquia explícita.
- **`/lotacao/`** — 74 siglas com `QUANTIDADE` (servidores). Os 6 que
  faltam são setores vazios.
- **`/ferias/setor/?sigla=XXX`** (mapeada, não consumida ainda) —
  servidores em férias do setor (e dos sub-setores: passar a sigla
  pai retorna filhos). Datas em formato `dd-MMM-yy` mês em inglês.

A hierarquia entre setores é **implícita na API**: passar a sigla pai
(ex.: `STI`) retorna servidores cujos setores são os filhos
(`SDBD`, `CIE`, `GSTI`, etc.). Não existe mapeamento explícito de
pai/filho em endpoint próprio.

### Oracle

Instant Client 21.10 instalado no `Dockerfile` (oci8 enabled). Em prod
conecta no Oracle externo via `DB_HOST`, `DB_SERVICE_NAME`. Em dev,
no container `oracle:1521` (mapeado pra `localhost:1523` no host pra
não bater com outros projetos).

---

## 8. Sessão e cache

Tabelas no próprio Oracle:

- `sessions` — driver `database`, `SESSION_LIFETIME=120` minutos.
- `cache` — driver `database` (criada por `0001_01_01_000001`).
- `jobs` — driver `database` (queue).

Trade-off declarado em [`visao-geral.md`](./visao-geral.md): Redis
não justifica o serviço extra na carga prevista (dezenas de
estagiários internos, não milhares simultâneos).

---

## 9. Estratégia de teste

Estrutura:

```
tests/
├── Unit/                    # services, models — sem HTTP
│   ├── Models/              # SetorTest, SupervisorTest, EstagiarioSetorTest
│   ├── Services/            # PontoService, AssinaturaService, …, TreApiClientTest
│   └── Support/             # BuddySpriteTest, NomeNormalizerTest
└── Feature/                 # rotas/HTTP — com banco SQLite in-memory
    ├── Admin/               # FeriadoCadastro, EstagiarioListagem, DashboardAdmin, …
    ├── Console/             # FecharPontosAbertosCommand, SincronizarSetoresCommand
    ├── Database/            # EstagiariosCsvSeeder
    ├── Http/                # PontoController, DashboardController
    ├── Middleware/          # ConfigureUserSessionTest
    └── (raiz)               # PontoTest, FrequenciaTest, AssinaturaTest, OnboardingTest, …
```

Convenções:

- **SQLite in-memory** via `phpunit.xml` (`DB_CONNECTION=sqlite`,
  `DB_DATABASE=:memory:`) — testes não dependem do Oracle.
- **`RefreshDatabase`** em testes Feature.
- **`Carbon::setTestNow()`** sempre que depende de data/hora.
- **`Http::fake()`** pra testes que envolvem `TreApiClient`.
- **Helper `setorId(string $sigla): int`** no `Tests\TestCase` evita
  repetição de `Setor::firstOrCreate(...)` nos testes que precisam de
  estagiário com setor.
- **Snapshot atual: 425 testes / 978 asserções, ~13s.**

---

## 10. Onde encontrar o quê

| Quero entender… | Olhe em |
|---|---|
| Como uma rota é resolvida | `routes/web.php` |
| O que cada modelo armazena e suas relações | [`dominio.md`](./dominio.md) |
| Como rodar comandos / debugar / lidar com erros comuns | [`operacao.md`](./operacao.md) |
| Decisões já tomadas e justificativa | [`visao-geral.md#decisões-arquiteturais`](./visao-geral.md#decisões-arquiteturais) |
| Como subir em prod | [`deploy-prod.md`](./deploy-prod.md) |
