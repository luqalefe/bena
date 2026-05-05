# Operação — runbook do dia a dia

Comandos, jobs agendados e como diagnosticar problemas. Para deploy
em produção, vá direto em [`deploy-prod.md`](./deploy-prod.md).

---

## 1. Setup local rápido

```bash
cp .env.example .env
make bootstrap
```

Isso faz: build → up → aguarda Oracle ficar healthy → `composer install`
→ `key:generate` → `migrate`. Demora ~3 minutos na primeira subida
(Oracle XE precisa baixar e inicializar). App em
`http://localhost:8082`.

> Em WSL/root, antes do bootstrap rode `chown -R 1000:1000 .` no host.
> O container roda como uid `1000`; sem isso, `composer install` e
> `artisan` falham por permissão.

---

## 2. Make targets (lista canônica)

| Alvo | O que faz |
|---|---|
| `make help` | Lista tudo (auto-gerado dos comentários). |
| `make up` / `make down` | Sobe/derruba a stack. |
| `make restart` | Reinicia só o container `app`. |
| `make logs` | Tail dos logs de todos os containers. |
| `make ps` | Status dos containers. |
| `make shell` | `bash` dentro do container `app`. |
| `make tinker` | `php artisan tinker` no container. |
| `make test` | Suíte completa (~13s, SQLite). |
| `make test-coverage` | Testes com cobertura, gate `--min=80`. |
| `make test-parallel` | Testes em paralelo. |
| `make pint` | Formata com Pint. |
| `make pint-check` | Verifica formatação sem alterar. |
| **`make check`** | `pint --test` + `test`. **Rodar antes de cada commit.** |
| `make migrate` | Aplica migrations no Oracle dev. |
| `make migrate-status` | Status das migrations. |
| `make fresh` | ⚠️ **APAGA TUDO** no Oracle dev: `migrate:fresh --seed`. |
| `make seed` | Roda seeders sem dropar. |
| `make ci` | Simula pipeline de CI localmente. |
| `make bootstrap` | Setup inicial (primeira vez). |
| `make nuke` | ⚠️ **APAGA VOLUMES** (perde Oracle dev). |

> Atalho prático sem make: `alias dx='docker compose exec app'`. Aí
> `dx php artisan ...`, `dx composer ...`.

---

## 3. Comandos Artisan customizados

### `php artisan setores:sincronizar`

Sincroniza `setores` com as APIs `/unidades/` + `/lotacao/` do TRE-AC.

```bash
docker compose exec app php artisan setores:sincronizar
```

**Output esperado:** `Sincronização concluída: X criados, Y atualizados,
Z inativados.`

- **Criados** — siglas novas que apareceram na API.
- **Atualizados** — siglas existentes cuja `quantidade_servidores`
  mudou ou que estavam `ativo=false` e voltaram.
- **Inativados** — siglas que sumiram da API. Continuam na tabela
  (estagiários antigos podem referenciar) mas saem dos selects.

Roda no schedule diário **03:00**. Idempotente.

### `php artisan ponto:fechar-abertos`

Fecha pontos esquecidos: `Frequencia` de dias passados com
`entrada NOT NULL` e `saida IS NULL` ganha
`saida = entrada + horas_diarias` e `saida_automatica = true`.

```bash
docker compose exec app php artisan ponto:fechar-abertos
```

Roda no schedule diário **00:05**. Idempotente.

### Comandos Laravel padrão úteis

```bash
docker compose exec app php artisan migrate              # aplica migrations
docker compose exec app php artisan migrate:status       # quem rodou, quem falta
docker compose exec app php artisan migrate:rollback     # desfaz último batch
docker compose exec app php artisan migrate --pretend    # mostra o SQL sem rodar

docker compose exec app php artisan db:seed --class=EstagiariosCsvSeeder
docker compose exec app php artisan tinker
docker compose exec app php artisan route:list           # todas as rotas
docker compose exec app php artisan schedule:list        # jobs agendados
docker compose exec app php artisan config:clear         # limpa cache de config
docker compose exec app php artisan cache:clear          # limpa application cache
```

---

## 4. Schedule (jobs agendados)

Declarados em `routes/console.php`:

| Cron | Job | O que faz |
|---|---|---|
| `0 5 0 * * *` (00:05) | `ponto:fechar-abertos` | Fecha pontos esquecidos do dia anterior. |
| `0 0 3 * * *` (03:00) | `setores:sincronizar` | Sincroniza setores com TRE-AC. |

Ambos têm `withoutOverlapping()` (não rodam em paralelo) e
`onOneServer()` (só uma instância em deploys multi-servidor).

### Como o schedule roda

> ⚠️ **Em dev** o schedule roda só se você executar `php artisan
> schedule:run` manualmente, ou rodar o comando de teste:
> `php artisan schedule:test`.

> ⚠️ **Em prod**, o `docker-compose.prod.yml` **não inclui** um
> container scheduler. Hoje a infra precisa adicionar: ou um cron do
> host com `* * * * * docker compose exec -T app php artisan
> schedule:run`, ou um container dedicado loopando o `schedule:run`.
> Detalhes em [`deploy-prod.md#410-configurar-o-scheduler-do-laravel`](./deploy-prod.md).

### Verificar o que está agendado

```bash
docker compose exec app php artisan schedule:list
```

Output:
```
* * * * *  php artisan ponto:fechar-abertos  ............... Next Due: ...
* * * * *  php artisan setores:sincronizar  ................ Next Due: ...
```

### Forçar um job sem esperar o cron

```bash
docker compose exec app php artisan ponto:fechar-abertos
docker compose exec app php artisan setores:sincronizar
```

Ambos foram desenhados pra rodar manualmente sem efeito colateral.

---

## 5. Inspeção e diagnóstico

### Logs

```bash
# log do Laravel (storage/logs/laravel.log)
docker compose exec app tail -f storage/logs/laravel.log

# stdout/stderr dos containers
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f oracle    # demora ~3min na primeira subida
```

### Estado do banco em dev (via tinker)

```bash
docker compose exec app php artisan tinker

# dentro do tinker:
\App\Models\Estagiario::count();
\App\Models\Setor::ativos()->count();
\App\Models\Frequencia::whereDate('data', today())->get();
\App\Models\Assinatura::whereYear('ano', 2026)->where('mes', 5)->get();
```

### Healthcheck rápido

```bash
# app responde?
curl -fsS http://localhost:8082/up && echo OK

# Oracle responde?
docker compose exec app php artisan tinker --execute="DB::select('select 1 from dual');"
```

### Contagem de testes (sanity)

```bash
make test                    # suíte completa
docker compose exec app php artisan test --filter=NomeDoTeste
docker compose exec app php artisan test --filter='nome do método'   # case insensitive
docker compose exec app php artisan test tests/Feature/PontoTest.php
```

---

## 6. Trocar usuário simulado em runtime (dev)

Como não há Authelia em dev, o `ConfigureUserSession` lê
`AUTHELIA_DEV_USER`/`AUTHELIA_DEV_GROUPS` do `.env`. Pra trocar sem
reiniciar o container:

```
http://localhost:8082/_dev/sessao
```

Form com dropdown de usuário/grupos pré-cadastrados. Detalhes em
[`dev-sessao.md`](./dev-sessao.md).

---

## 7. Troubleshooting

### `Cannot connect to the Docker daemon`

WSL2 não tem o Docker rodando. `sudo service docker start` resolve.

### Oracle: `ORA-12541: TNS:no listener` na primeira subida

Oracle ainda subindo. Aguarde — o healthcheck só fica green quando
realmente pronto:

```bash
docker compose logs -f oracle | grep "DATABASE IS READY TO USE"
```

### `ORA-00942: table or view does not exist` em SESSIONS / CACHE / JOBS

Faltou migrar. Drivers `database` exigem as tabelas:

```bash
docker compose exec app php artisan migrate --force
```

### Permissão negada em `storage/`, `bootstrap/cache/` ou `vendor/`

Uid mismatch entre host e container (container roda como `1000`):

```bash
sudo chown -R 1000:1000 .
```

### Conflito de portas (8080 / 1521)

Outros projetos no host usando essas portas. Defaults daqui são
`8082` (HTTP) e `1523` (Oracle). Se ainda colidir, edite
`docker-compose.yml` no `ports:` do `nginx` e `oracle`.

### Mudou `.env`, comportamento não muda

Reinicie o container:

```bash
docker compose restart app
```

Ou limpe o cache de config:

```bash
docker compose exec app php artisan config:clear
```

### Pint reclama mas você é root, não consegue gravar

Mesma coisa do uid mismatch:

```bash
sudo chown -R 1000:1000 app/ tests/ database/ resources/
docker compose exec app ./vendor/bin/pint
```

### Horário aparece errado na app

Bate o que o app diz com o host:

```bash
docker compose exec app php artisan tinker --execute="echo now()->format('Y-m-d H:i') . ' (' . config('app.timezone') . ');"
date
```

Se divergir, conferir `APP_TIMEZONE` no `.env`. Acre é
`America/Rio_Branco` (UTC-5). `America/Sao_Paulo` (UTC-3) está 2h à
frente.

### `RuntimeException: API TRE-AC retornou HTTP 500`

A API do tribunal pode estar fora. O `setores:sincronizar` mantém o
estado anterior (não dropa nada). Volte a rodar quando a API voltar:

```bash
docker compose exec app php artisan setores:sincronizar
```

### Limpar dados de dev e recomeçar

⚠️ apaga tudo:

```bash
make fresh                                                       # drop + migrate + seed
docker compose exec app php artisan setores:sincronizar          # popula setores
docker compose exec app php artisan db:seed --class=EstagiariosCsvSeeder  # se quer estagiários
```

---

## 8. Workflow de desenvolvimento (resumo)

Detalhe completo em [`../CLAUDE.md`](../CLAUDE.md). Em uma frase: **TDD
estrito, XP simples**, ciclo Red → Green → Refactor.

Antes de qualquer commit:

```bash
make check
```

Que é `pint --test && test`. Se passar, commita. Se não passar, **não
commita** — conserta primeiro. Convenção do repo: nunca subir pra `main`
com a barra vermelha.

### Como nomear o commit

Padrão atual do repo (ver `git log --oneline`):

- `feat(area): descrição` — feature nova.
- `fix(area): descrição` — bug fix.
- `refactor(area): descrição` — sem mudança de comportamento.
- `chore(area): descrição` — infra, deps, build.
- `docs(area): descrição` — só docs.

Mensagem de commit explica o **porquê**, não o **quê** (o diff já
mostra o quê). Exemplo bom:

```
feat(estagiarios): trocar lotacao (string) por FK setor_id

Estagiários agora apontam pra tabela setores via setor_id em vez
de uma string solta. Garante que a lotação só aceita siglas que
existem no TRE-AC e remove o risco de typos como "9º ZONA"
conviver com "9ª ZE" na mesma base.
```

---

## 9. Checklist mental antes de fechar uma sessão de trabalho

- [ ] `make check` passou
- [ ] Tasks da sessão fechadas (TaskUpdate completed) ou descritas no
      próximo passo
- [ ] Se mexi em código, atualizei o doc relevante (em
      `docs/dominio.md`, `docs/arquitetura.md`, etc.)
- [ ] Se introduzi uma nova decisão arquitetural, registrei em
      [`visao-geral.md#decisões-arquiteturais`](./visao-geral.md)
- [ ] Se adicionei comando artisan ou job no schedule, atualizei este
      doc (§3 ou §4)
- [ ] Commits atômicos, com mensagem que explica o porquê
- [ ] Branch local não tem mudanças não-commitadas órfãs
