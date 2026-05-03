# Bena — Controle de Frequência de Estagiários do TRE-AC

> **Bena** vem do *Hãtxa Kuĩ* (língua do povo Huni Kuin) e significa
> *"novo"*. Da expressão *Xinã Bena*, "novo tempo" — o nome certo
> para o sistema que substitui a folha de ponto em papel pelo
> registro digital.

> **Para uma visão completa do sistema** (história, arquitetura, fluxos,
> sistema de mascotes, decisões), leia
> [`docs/visao-geral.md`](./docs/visao-geral.md).

## Visão geral

Sistema web interno de tribunal para registrar, consolidar e assinar a
**Ficha de Controle de Frequência (FCF)** de estagiários — substituindo o
preenchimento manual em papel no formato CIEE. O estagiário bate ponto pelo
navegador, vê a folha do mês com horas calculadas, gera o PDF no layout
oficial e assina digitalmente; o supervisor contra-assina; o RH baixa o PDF
assinado pra anexar no processo SEI.

Inclui ainda:

- **Calendário visual interativo** com mapa de calor temático por mês,
  feriados destacados e adição inline pra admin (clique no dia → modal).
- **Sistema de mascotes (Buddy)** — cada usuário recebe um companheiro
  sorteado no primeiro acesso, com frases contextuais com tema
  eleitoral. Pool padrão para estagiários (8 mascotes); pool sênior
  para supervisores e admin (4 mascotes). Galeria em `/mascotes`.
- **Onboarding** no primeiro acesso explicando o fluxo e apresentando
  o mascote.

### Inspiração: o modelo SEI

O fluxo de assinatura segue o modelo do **SEI (Sistema Eletrônico de
Informações)**, usado pelos tribunais brasileiros para tramitar documentos
oficiais. Como o SEI, este sistema **dispensa certificado ICP-Brasil**: a
assinatura tem valor institucional pelo conjunto _identidade autenticada
forte_ + _carimbo de tempo_ + _registro íntegro do conteúdo assinado_.
Quem opera o sistema já está autenticado pelo SSO institucional com 2FA,
então o "quem assinou" é confiável sem precisar de token criptográfico.

### Segurança da assinatura

- **Identidade.** Em produção, autenticação é delegada ao **Authelia atrás
  do reverse proxy** (forward-auth), com **TOTP/2FA obrigatório** e LDAP/AD
  do tribunal como fonte de usuários. A app nunca vê senha — só recebe os
  headers `Remote-User`/`Remote-Groups` injetados após login validado.
- **Snapshot canônico.** Ao assinar, o `AssinaturaService` extrai um
  snapshot determinístico da folha do mês (campos significativos, ordem
  fixa, sem timestamps internos) e serializa em JSON UTF-8.
- **Hash SHA-256 + carimbo.** O hash do snapshot é gravado junto com o
  `assinante_username`, `assinado_em` (timestamp servidor), `papel`
  (estagiário/supervisor) e `ip`. O snapshot completo também é
  persistido — verificação = re-hash + comparação.
- **Detecção de adulteração.** Se qualquer registro de frequência mudar
  depois da assinatura, o hash recalculado diverge e a folha exibe
  badge "⚠ alterada" + botão "Re-assinar versão atual" (H22).
- **Ordem dos papéis.** Supervisor só consegue contra-assinar **depois**
  que o estagiário assinou. Cada papel só pode assinar uma vez por mês —
  re-assinatura cria nova `Assinatura` e marca a anterior como
  `substituida_em`.
- **Slot pra PAdES.** O `AssinaturaService` é a única superfície que
  toca crypto — quando exigido, troca-se hash+carimbo por ICP-Brasil
  PAdES sem mexer no resto do código.

**Stack:** Laravel 11 · PHP 8.2 · Oracle 19c+ · gov.br DS v3 (tema TRE-AC) · Authelia (SSO+2FA, em prod) · Docker

> Antes de mexer em código, leia [`CLAUDE.md`](./CLAUDE.md), [`REQUISITOS.md`](./REQUISITOS.md)
> e [`SPRINTS.md`](./SPRINTS.md). Seguimos **TDD estrito** e XP — não é cosmético.

---

## Setup local

Pré-requisitos: Docker 24+ e Docker Compose v2 instalados. Não precisa de
PHP, Composer, Oracle ou nada disso na máquina — tudo roda em container.

```bash
# Subir a stack pela primeira vez:
cp .env.example .env
make bootstrap          # build + up + composer install + key:generate + migrate
```

> **Sem mexer em `/etc/hosts`.** Chrome/Firefox/Edge resolvem `*.localhost`
> automaticamente para 127.0.0.1 (RFC 6761).

A primeira subida demora porque:

- O build da imagem `app` baixa Oracle Instant Client (~60 MB) e compila `oci8`.
- O container `oracle` inicializa o banco (~3 min).

Depois da primeira vez, `make up` é rápido.

**Acesso:**
- App: <https://ponto.localhost>
- Trocar usuário simulado: <https://ponto.localhost/_dev/sessao>
- Traefik dashboard (dev): <http://localhost:8080>

O navegador vai reclamar do certificado auto-assinado — aceite. Em prod
usamos cert real.

### Usuário simulado

Em dev não rodamos Authelia — o middleware lê os defaults do `.env`
(`AUTHELIA_DEV_USER`, `AUTHELIA_DEV_GROUPS`, etc.). Para trocar em runtime
sem reiniciar, abra `/_dev/sessao`. Detalhes em
[`docs/dev-sessao.md`](./docs/dev-sessao.md).

Usuários sugeridos:
| Usuário | Grupo | Uso |
|---------|-------|-----|
| `lucas.dev` | `estagiarios` | Fluxo do estagiário |
| `paula.dev` | `estagiarios` | Segundo estagiário |
| `marco.dev` | `supervisores` | Fluxo do supervisor |
| `rh.admin` | `admin` | Fluxo do admin/RH |

---

## Comandos do dia a dia

```bash
make help            # lista tudo
make up              # sobe stack
make down            # derruba (preserva volumes)
make logs            # tail de todos os containers
make shell           # bash no container app

make test            # roda suíte (sqlite in-memory)
make pint            # formata
make check           # pint --test && test  (RODAR antes de cada commit)

make migrate         # aplica migrations no oracle dev
make fresh           # drop + migrate + seed (CUIDADO: apaga dados)

make ci              # simula pipeline de CI localmente
```

---

## Arquitetura em uma frase

> Forward-auth do Authelia → middleware Laravel resolve `Estagiario` →
> controllers finos → services com lógica de domínio → Eloquent → Oracle.

Diagrama detalhado, modelos do domínio e decisões em
[`docs/visao-geral.md`](./docs/visao-geral.md). Convenções de código em
[`CLAUDE.md`](./CLAUDE.md).

---

## Estrutura

```
.
├── README.md            # este arquivo
├── CLAUDE.md            # convenções e workflow (LEIA antes de codar)
├── REQUISITOS.md        # histórias e critérios de aceitação
├── SPRINTS.md           # plano de execução por sprint
├── STATUS.md            # handoff entre sessões
├── app/
│   ├── Http/Controllers/      # Calendario, Mascotes, Onboarding, Folha, Ponto, etc.
│   ├── Http/Middleware/       # ConfigureUserSession, EnsureOnboarded
│   ├── Models/                # Estagiario, Frequencia, Feriado, Assinatura
│   └── Services/              # AssinaturaService, BuddyService, CalendarioService, ...
├── config/
│   └── buddies.php            # 12 mascotes + ~150 frases + histórias eleitorais
├── docker/
│   ├── app/             # Dockerfile multi-stage, php.ini, entrypoint
│   ├── nginx/
│   ├── authelia/        # configuration.yml, users_database.yml
│   └── traefik/
├── docs/
│   ├── visao-geral.md       # documentação narrativa completa do sistema
│   ├── dev-sessao.md        # como trocar usuário simulado em runtime
│   └── identidade-visual.md # paleta TRE-AC + tokens
├── docker-compose.yml         # dev (default)
├── docker-compose.prod.yml    # overrides para produção
├── docker-compose.test.yml    # overrides para CI/testes
└── Makefile
```

---

## Status

✅ **Sprints 0–4 fechadas.** Estagiário bate ponto, vê folha do mês, gera
PDF e assina. Supervisor contra-assina. RH baixa o PDF assinado pra
anexar no SEI. Admin cadastra estagiários e gerencia feriados.

✅ **Sprint 5 quase fechada.** H16 (upload contrato + `supervisor_username`),
H17 (observações por dia), H21 (auto-fechamento de ponto esquecido), H22
(re-assinatura quando hash diverge), H23 (onboarding no primeiro acesso).
Pendentes: H0.3 (pipeline CI no GitLab interno) e H18 (verificação de
integridade da assinatura)/H19 (auditoria de ações).

✅ **Sprint 7a/7b/7c fechadas (UI/UX).**
- **H24/H25** — Auto-submit nos filtros admin + busca client-side com
  NFD-normalize nas tabelas.
- **H26/H27** — Calendário interativo (`/calendario` mostra o mês atual
  com mapa de calor temático, dialog admin pra adicionar feriado, link
  na nav e dashboard). View de listagem `/admin/feriados` removida — a
  entrada agora é exclusivamente pelo calendário.
- **H28** — Sistema **Buddy/mascote**: 12 mascotes (8 padrão para
  estagiários + 4 sênior para supervisor/admin), atribuição aleatória
  e permanente no primeiro acesso, frases contextuais determinísticas
  por dia/status/bloco do dia, ~150 frases com tema eleitoral, página
  `/mascotes` com galeria e história de cada um ligada à Justiça
  Eleitoral do Acre.

**Suíte:** 262 testes / 638 assertions, todos verdes (SQLite in-memory) ·
gate de cobertura ≥ 80 %.

🚧 **Próximas:**
- **H18 + H19** (Sprint 5) — verificação automática de integridade na
  visualização da folha + tabela de auditoria de ações sensíveis.
- **Sprint 6 (Hardening)** — NFRs de segurança (NF1–NF7), deploy via
  Swarm/K8s, smoke tests E2E, backup automatizado, doc operacional.

Para o estado de handoff entre sessões, ver [`STATUS.md`](./STATUS.md).
