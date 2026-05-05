# Deploy em produção — bena (Ponto Estagiários)

Guia para a equipe de infra subir / atualizar o sistema no ambiente do
tribunal. **Este projeto roda inteiro em containers**; não instale
PHP/Composer/Oracle no host.

---

## 1. Visão geral

```
┌─────────────────┐    ┌──────────────────┐    ┌──────────────┐
│ Reverse proxy   │ ── │  Authelia (SSO)  │ ── │  nginx       │
│ do tribunal     │    │  do tribunal     │    │  (compose)   │
│ (TLS, FQDN)     │    │  (LDAP/AD)       │    │              │
└─────────────────┘    └──────────────────┘    └──────┬───────┘
                                                       │ fastcgi
                                                       ▼
                                              ┌──────────────┐
                                              │  app         │
                                              │  (PHP-FPM)   │
                                              │  (compose)   │
                                              └──────┬───────┘
                                                     │ oci8
                                                     ▼
                                              ┌──────────────┐
                                              │  Oracle      │
                                              │  do tribunal │
                                              │  (externo)   │
                                              └──────────────┘
```

**O que ESTE compose sobe:** `app` (php-fpm + Oracle Instant Client) e
`nginx`. Só isso.

**O que NÃO sobe (responsabilidade da infra do tribunal):**
- Reverse proxy + TLS (Traefik / nginx institucional). Precisa terminar
  TLS, encaminhar pro nginx do compose e estar atrás do Authelia.
- Authelia (SSO contra LDAP/AD). Precisa autenticar e injetar headers
  `Remote-User`, `Remote-Groups`, `Remote-Name`, `Remote-Email`.
- Oracle. O schema/usuário precisa ser criado antes do primeiro deploy.

---

## 2. Pré-requisitos (infra antes do dia D)

| Item | Detalhe | Como validar |
|------|---------|--------------|
| FQDN | Ex.: `ponto.tre-ac.jus.br` | `dig +short …` |
| Certificado TLS | No reverse proxy, não no nginx do compose | `curl -vI https://FQDN` |
| Authelia | ACL para o domínio acima, exigindo grupo `admin`, `supervisores` ou `estagiarios` | Login manual antes de subir a app |
| Conectividade host → Oracle | Porta 1521 aberta da máquina-host até o Oracle do tribunal | `nc -zv ORACLE_HOST 1521` |
| Schema Oracle | `usuário`, `senha`, `service_name`. **Schema vazio** (as migrations criam tudo). | Conectar via `sqlplus user/pass@ORACLE_HOST:1521/SERVICE` |
| Acesso ao repo | Clone HTTPS ou SSH em `/opt/bena` (ou path que seu padrão usa) | `git clone …` |
| Docker / Compose v2 | Engine ≥ 24, Compose ≥ 2.20 | `docker version`, `docker compose version` |
| Volume pra `storage/` | Persistência de logs e uploads de contrato | Decisão arquitetural: bind mount ou volume nomeado |

---

## 3. Variáveis de ambiente (`.env` em produção)

Crie `/opt/bena/.env` no host (modo `chmod 600`, dono `root` ou usuário do
deploy). **Nunca commitar**. Use `.env.example` como base e sobrescreva:

```ini
APP_NAME="Ponto Estagiários"
APP_ENV=production
APP_KEY=                                   # gerar — ver §4
APP_DEBUG=false
APP_TIMEZONE=America/Rio_Branco
APP_URL=https://ponto.tre-ac.jus.br        # FQDN público
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

LOG_CHANNEL=stack
LOG_LEVEL=info                             # info em prod; debug só se investigando

DB_CONNECTION=oracle
DB_HOST=oracle.interno.tre-ac.jus.br       # [infra preencher]
DB_PORT=1521
DB_SERVICE_NAME=PROD                       # [infra preencher]
DB_DATABASE=PROD                           # mesmo valor do SERVICE_NAME
DB_USERNAME=bena
DB_PASSWORD=                               # [infra preencher — vault]

CACHE_STORE=database
SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database

AUTHELIA_DEV_BYPASS=false                  # OBRIGATÓRIO em prod
# AUTHELIA_DEV_* não devem aparecer em prod
```

> **Atenção** ao `APP_TIMEZONE`. Tem que ser `America/Rio_Branco` (UTC-5,
> Acre). `America/Sao_Paulo` está 2h à frente e vai exibir o ponto errado.

---

## 4. Primeiro deploy (instalação inicial)

### 4.1 Clonar e checar branch

```bash
sudo mkdir -p /opt/bena && cd /opt/bena
git clone https://github.com/luqalefe/bena.git .
git checkout main          # ou a tag de release acordada
```

### 4.2 Criar `.env` (ver §3) e gerar `APP_KEY`

```bash
# Gera uma chave nova SEM persistir no .env (você cola depois):
docker run --rm -v "$PWD":/app -w /app php:8.2-cli \
  php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

Cole o valor em `APP_KEY=` no `.env`. **Nunca reaproveite a chave de dev.**

### 4.3 Build da imagem

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml build --pull
```

A imagem é self-contained: o código vai pra dentro dela. Cada release é
uma rebuild.

### 4.4 Subir nginx + app

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

Aguarde ~10s. Confira:

```bash
docker compose ps
docker compose logs --tail=50 app
docker compose logs --tail=50 nginx
```

### 4.5 Validar conectividade com Oracle

```bash
docker compose exec app php artisan tinker --execute="DB::select('select 1 from dual');"
```

Deve voltar `[{"1":1}]`. Se der erro, é DNS, firewall, credenciais ou
service_name — não siga adiante.

### 4.6 Rodar migrations

```bash
docker compose exec app php artisan migrate --force
```

> A flag `--force` é necessária porque `APP_ENV=production` desativa a
> confirmação interativa. **Faça backup antes** (ver §8).

### 4.7 Sincronizar setores (PRIMEIRA carga)

A tabela `setores` é populada pelo comando que consome a API do TRE-AC:

```bash
docker compose exec app php artisan setores:sincronizar
```

Output esperado: `Sincronização concluída: 80 criados, 0 atualizados, 0 inativados.`

> **Por que rodar à mão?** Em fluxo normal isso roda no scheduler diário
> (ver §6), mas no deploy inicial você precisa popular a tabela antes
> de qualquer outra coisa que dependa dela.

### 4.8 (Opcional) Seed de dados iniciais

Se houver carga histórica de estagiários a importar do CSV
institucional, rode **depois** do `setores:sincronizar` (a resolução
sigla → `setor_id` depende dos setores estarem lá):

```bash
docker compose exec app php artisan db:seed --class=EstagiariosCsvSeeder
```

> Em deploys sem essa carga, pule. Os estagiários serão criados
> sob demanda no primeiro login via Authelia.

### 4.9 Cache de config / rotas / views

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

### 4.10 Configurar o scheduler do Laravel

> ⚠️ **Gap conhecido**: este compose **não inclui** um container que
> rode `php artisan schedule:run` a cada minuto. Sem isso, os jobs
> agendados (`ponto:fechar-abertos` à 00:05 e `setores:sincronizar` às
> 03:00) **não rodam**.

Duas opções aceitas; escolham uma com a infra:

**Opção A — cron do host** (mais simples):

```cron
* * * * * docker compose -f /opt/bena/docker-compose.yml -f /opt/bena/docker-compose.prod.yml exec -T app php artisan schedule:run >> /var/log/bena-scheduler.log 2>&1
```

**Opção B — container scheduler dedicado**: adicione ao
`docker-compose.prod.yml` um serviço extra `scheduler` reusando a
imagem do `app` mas com o entrypoint `while true; do php artisan
schedule:run; sleep 60; done`. Vantagem: não depende do cron do host.

Sem isso, a tabela `setores` fica congelada e pontos esquecidos não
fecham automaticamente.

### 4.11 Permissões de `storage/`

A imagem roda como uid `1000` (`laravel`). Se você usar bind mount pra
`storage/`, garanta que o diretório no host pertença a `1000:1000`:

```bash
sudo chown -R 1000:1000 /opt/bena/storage
```

---

## 5. Smoke tests (validar que está vivo)

```bash
# 5.1 — Healthcheck do Laravel (rota /up nativa do framework)
curl -fsS http://localhost:8082/up && echo OK

# 5.2 — Pelo reverse proxy, com Authelia no caminho:
#   abrir https://ponto.tre-ac.jus.br no navegador → deve cair na tela
#   de login do Authelia → após autenticar, ver o dashboard.

# 5.3 — Logs sem erro fatal nos primeiros minutos:
docker compose logs --since=5m app | grep -iE 'error|exception|fatal'
```

Casos felizes que devem passar com um usuário de teste de cada grupo:

- **estagiario**: vê dashboard pessoal, consegue bater entrada/saída.
- **supervisor**: vê dashboard listando seus estagiários.
- **admin**: vê dashboard global, lista estagiários, edita um (form usa
  o select de setores carregado da tabela).

---

## 6. Operação contínua

### Logs

Os logs do Laravel ficam em `storage/logs/laravel.log` dentro do
container. Para acompanhar:

```bash
docker compose exec app tail -f storage/logs/laravel.log
docker compose logs -f app   # stdout/stderr do php-fpm + nginx
```

> Recomendação: encaminhar para o stack de logs institucional via
> driver Docker (ex.: `gelf`, `syslog`) configurando `logging:` no
> serviço `app` do `docker-compose.prod.yml`.

### Comandos úteis

```bash
# entrar no container
docker compose exec app bash

# rodar artisan ad-hoc
docker compose exec app php artisan ...

# forçar resync de setores (sem esperar o cron)
docker compose exec app php artisan setores:sincronizar

# ver schedule list
docker compose exec app php artisan schedule:list
```

---

## 7. Atualização (segunda release em diante)

```bash
cd /opt/bena
git fetch origin
git checkout vX.Y.Z                       # tag da nova release

# 1. backup do Oracle (ver §8)

# 2. rebuild
docker compose -f docker-compose.yml -f docker-compose.prod.yml build --pull

# 3. derruba e sobe
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --force-recreate

# 4. migrations
docker compose exec app php artisan migrate --force

# 5. invalidar caches
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# 6. smoke tests (§5)
```

**Migrations destrutivas** (drop coluna, FK nova, etc.) ficam
documentadas no CHANGELOG. Para essas, considere janela de manutenção
e backup imediatamente antes.

---

## 8. Backup do Oracle (antes de toda migration)

> A app não toma backup. Quem cuida do Oracle (DBA do tribunal) deve
> rodar o procedimento padrão **antes** de cada `php artisan migrate`.

Mínimo aceitável:

```bash
# No servidor que tem acesso ao Oracle:
expdp bena/SENHA@HOST:1521/SERVICE \
  schemas=BENA \
  directory=DATA_PUMP_DIR \
  dumpfile=bena_$(date +%Y%m%d_%H%M).dmp \
  logfile=bena_$(date +%Y%m%d_%H%M).log
```

Guardar o dump por pelo menos 7 dias após o deploy.

---

## 9. Rollback

Se a nova release quebrou:

### 9.1 Aplicação (rápido)

```bash
cd /opt/bena
git checkout vANTERIOR
docker compose -f docker-compose.yml -f docker-compose.prod.yml build --pull
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --force-recreate
```

### 9.2 Banco (se a release subiu migration que o rollback de código
não cobre)

```bash
docker compose exec app php artisan migrate:rollback --step=N --force
```

Onde `N` é o número de migrations da release atual. Ver
`database/migrations/` ordenado por timestamp.

> Se o rollback da migration não for confiável (ex.: dropou coluna com
> dados), o caminho seguro é **restaurar o dump** pré-deploy (§8).

---

## 10. Checklist de cutover (dia do deploy)

Imprima isso e rasure conforme avança:

- [ ] DBA confirmou backup do Oracle
- [ ] FQDN e TLS funcionando (curl -I)
- [ ] Authelia configurado e testado com 1 usuário de cada grupo
- [ ] `.env` com `APP_KEY` único, `APP_ENV=production`,
      `APP_DEBUG=false`, `AUTHELIA_DEV_BYPASS=false`,
      `APP_TIMEZONE=America/Rio_Branco`
- [ ] `docker compose ... build --pull` sem erro
- [ ] `docker compose ... up -d` containers `Up (healthy)`
- [ ] `php artisan tinker` consulta `select 1 from dual` retorna OK
- [ ] `php artisan migrate --force` sem erro
- [ ] `php artisan setores:sincronizar` cria 80 setores
- [ ] (se aplicável) `db:seed --class=EstagiariosCsvSeeder` sem warnings
- [ ] `config:cache route:cache view:cache` aplicados
- [ ] Cron / container do scheduler ativo (`schedule:list` mostra os 2 jobs)
- [ ] Smoke test: estagiario / supervisor / admin logam e veem dashboards
- [ ] Smoke test: bater ponto, ver folha mensal, baixar PDF
- [ ] Monitorando logs por 30min sem `ERROR`/`EXCEPTION`

---

## Anexo A — Onde reportar problemas

- Bugs de aplicação: GitHub Issues do repo `luqalefe/bena`
- Problemas de infra (Oracle, Authelia, reverse proxy): canal padrão da
  infra do tribunal
