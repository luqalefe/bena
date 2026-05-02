# Ponto — Controle de Frequência de Estagiários

Sistema web de controle de frequência de estagiários no formato CIEE,
construído para uso interno em tribunal.

**Stack:** Laravel 11 · PHP 8.2 · Oracle 19c+ · AdminLTE 3 · Authelia (SSO+2FA) · Docker

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
| `marco.dev` | `admin` | Fluxo do supervisor/admin |
| `julia.dev` | `admin` | Segundo admin |

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

Detalhes em [`CLAUDE.md`](./CLAUDE.md).

---

## Estrutura

```
.
├── CLAUDE.md            # convenções e workflow (LEIA)
├── REQUISITOS.md        # histórias e critérios de aceitação
├── SPRINTS.md           # plano de execução por sprint
├── docker/
│   ├── app/             # Dockerfile multi-stage, php.ini, entrypoint
│   ├── nginx/
│   ├── authelia/        # configuration.yml, users_database.yml
│   └── traefik/
├── docs/
│   ├── dev-authelia.md  # primeiro login + reset TOTP
│   └── ...
├── docker-compose.yml         # dev (default)
├── docker-compose.prod.yml    # overrides para produção
├── docker-compose.test.yml    # overrides para CI/testes
└── Makefile
```

---

## Status

🚧 **Sprint 0 — Fundação.** Stack Docker subindo, sem Laravel bootstrapado
ainda. Próximo passo: bootstrap do Laravel 11 + modelagem do banco.
