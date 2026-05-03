# REQUISITOS

Sistema de Controle de Frequência de Estagiários · v1.0

> Documento vivo. Atualizar conforme histórias são concluídas, novas
> aparecem ou prioridades mudam. Status: 📋 Backlog · 🚧 Em curso · ✅ Done.

---

## Glossário

| Termo | Significado |
|-------|-------------|
| **Estagiário** | Pessoa cadastrada no Authelia com grupo `estagiarios` |
| **Supervisor** | Pessoa cadastrada no Authelia com grupo `supervisores`, ligada a 1+ estagiários via `Estagiario.supervisor_username` |
| **RH / Admin** | Pessoa cadastrada no Authelia com grupo `admin`. Acesso total + responsável por anexar PDF assinado no SEI |
| **FCF** | Ficha de Controle de Frequência (formato CIEE) |
| **Bater ponto** | Registrar entrada ou saída no dia atual |
| **Folha mensal** | Conjunto dos registros de frequência de um estagiário em um mês |
| **Assinar folha** | Capturar snapshot do mês + gerar hash + persistir |
| **Dia útil** | Dia da semana que não é sábado, domingo nem feriado cadastrado |
| **Recesso** | Período sem expediente além dos feriados oficiais (ex: recesso forense) |

---

## Atores

- **Estagiário** — usuário do dia a dia. Bate ponto, vê a própria folha,
  gera PDF, assina ao final do mês. Authelia group: `estagiarios`.
- **Supervisor** — chefe imediato do estagiário no tribunal. Faz login no
  sistema, vê **apenas os estagiários sob sua responsabilidade**,
  contra-assina folhas mensais. Authelia group: `supervisores`.
- **RH (admin)** — gerencia feriados, vê todos os estagiários, cadastra
  dados administrativos, baixa o PDF final assinado para anexar no
  processo SEI. Authelia group: `admin`.
- **Sistema** — calcula horas, classifica dias, gera hashes, renderiza PDF.

> **Nota de implementação:** até a Iteração 4, supervisor era modelado
> como "qualquer admin". Em 2026-05-01 foi refinado para grupo separado
> `supervisores` com amarração 1↔N (`Estagiario.supervisor_username`).
> Histórias H13, H14, H15, H16 foram atualizadas pra refletir isso.
> Nova história **H20** cobre o passo "RH anexa PDF no SEI".

---

## Visão geral por iteração

| Iteração | Foco | Histórias |
|----------|------|-----------|
| **0 — Fundação** | Stack Docker, CI, Authelia local | H0.1, H0.2, H0.3, H0.4 |
| **1 — Núcleo** | Bater ponto funciona ponta a ponta | H1, H2, H3, H4 |
| **2 — Folha mensal** | Visualização e cálculos do mês | H5, H6, H7 |
| **3 — Calendário** | Feriados gerenciados pelo admin | H8, H9, H10 |
| **4 — PDF e assinatura** | Saída no formato CIEE com assinatura | H11, H12, H13 |
| **5 — Admin** | Dashboard e visão multi-estagiário | H14, H15, H16, H20 |
| **6 — Polimento** | Auditoria, observações, edge cases | H17, H18, H19 |

Cada iteração = ~1 semana de trabalho. Histórias dentro da iteração podem
ser feitas em qualquer ordem, respeitando dependências marcadas.

---

# Iteração 0 — Fundação (infraestrutura)

A primeira iteração entrega um ambiente onde **qualquer pessoa do time
consegue rodar a stack inteira com um comando**, e onde os testes rodam
em CI desde o primeiro commit. Sem essa fundação, TDD vira intenção, não
prática.

## H0.1 — Stack Docker dev sobe com um comando

> **Como** desenvolvedor recém-chegado ao projeto,
> **eu quero** rodar `docker compose up -d` e ter a app, banco, Authelia
> e proxy funcionando,
> **para que** eu não perca um dia configurando ambiente.

**Critérios de aceitação:**

1. Após `git clone` + `cp .env.example .env` + `docker compose up -d`,
   acessar `https://ponto.localhost` redireciona pro Authelia.
2. `docker-compose.yml` define os serviços: `app`, `nginx`, `oracle`,
   `authelia`, `traefik`, `redis`.
3. Todos os serviços têm healthcheck e `depends_on` com
   `condition: service_healthy`.
4. Container `app` roda como UID 1000 (não-root), com permissão de
   escrita em `storage/` e `bootstrap/cache/`.
5. Volumes nomeados pra Oracle, Redis e Authelia storage; dados
   persistem entre `docker compose down && up`.
6. README inclui seção "Setup local" com comandos copy-paste.
7. Teste manual documentado: criar usuário no `users_database.yml` do
   Authelia, fazer login completo (com TOTP), acessar a app.

**Status:** 📋 Backlog

---

## H0.2 — Dockerfile multi-stage com imagem prod enxuta

> **Como** equipe de operações,
> **eu quero** uma imagem de produção pequena e sem ferramentas de dev,
> **para que** o deploy seja rápido e a superfície de ataque mínima.

**Critérios de aceitação:**

1. `docker/app/Dockerfile` tem ao menos 3 stages: `base`, `dev`, `prod`.
2. Stage `prod` NÃO contém: Xdebug, Composer (em runtime), arquivos de
   teste, `.env` de exemplo, `node_modules`.
3. Imagem `prod` final pesa < 300 MB (`docker image ls`).
4. Stage `prod` define `USER www-data` antes do `CMD`.
5. `docker build --target=prod` completa em < 5 min em ambiente CI
   limpo (sem cache).
6. `.dockerignore` exclui: `vendor/`, `node_modules/`, `.git/`, `.env`,
   `tests/`, `storage/logs/*`, `*.md` exceto `README.md`.
7. Tag fixa de PHP no `FROM` (ex: `php:8.2.20-fpm-alpine`), nunca
   `latest`.

**Status:** 📋 Backlog · **Depende de:** H0.1

---

## H0.3 — Pipeline de CI rodando testes em container

> **Como** time,
> **eu quero** que cada push rode toda a suíte de testes
> automaticamente em container,
> **para que** ninguém quebre `main` sem perceber.

**Critérios de aceitação:**

1. Arquivo `.github/workflows/ci.yml` (ou `.gitlab-ci.yml`) configurado.
2. Pipeline executa em todo push e em todo PR.
3. Pipeline roda os passos, em ordem: build da imagem `dev` → `pint --test`
   → `php artisan test --coverage`.
4. Pipeline FALHA se: pint detectar problemas, qualquer teste falhar,
   ou cobertura cair abaixo de 80% no projeto / 90% nos services.
5. Tempo total da pipeline < 5 min para ~50 testes.
6. Badge de status do CI no README.
7. Branch protection em `main`: PR não pode ser merged com CI vermelho.

**Status:** 🚧 Em curso **(parcial)** — pipeline local pronto: `make ci`
constrói a imagem `dev` e executa `composer install` + `pint --test` +
`php artisan test --coverage --min=80` via `docker-compose.test.yml`.
Coverage atual = 97.9 % em 167 testes. **Pendente:** o arquivo de CI
(`.gitlab-ci.yml`) será criado quando o repositório migrar pro GitLab
interno do tribunal (2026-05-04). Outros pendentes: critério 4 —
enforcement **per-service ≥ 90 %** (hoje todos ≥ 98 % na prática, sem
gate automatizado); critério 7 — branch protection em `main`
(configuração no UI do GitLab). **Depende de:** H0.2

---

## H0.4 — Authelia local funcional com 2FA

> **Como** desenvolvedor,
> **eu quero** testar o fluxo completo de login com 2FA localmente,
> **para que** eu não descubra bugs de integração só em homologação.

**Critérios de aceitação:**

1. Container `authelia` sobe configurado com `users_database.yml`
   contendo no mínimo: `lucas.dev` (grupo `estagiarios`),
   `marco.dev` (grupo `admin`).
2. Primeiro login pede cadastro de TOTP (QR code escaneável por
   Google Authenticator ou similar).
3. Após cadastro de TOTP, login subsequente exige usuário + senha + TOTP.
4. Após login bem-sucedido, requisições à app incluem headers
   `Remote-User`, `Remote-Groups`, `Remote-Name`, `Remote-Email`.
5. Estagiário (`lucas.dev`) tentando acessar `/admin` recebe 403.
6. Admin (`marco.dev`) consegue acessar `/admin` e qualquer rota.
7. Documentação em `docs/dev-authelia.md` com passo a passo do primeiro
   login + reset de TOTP.

**Status:** 📋 Backlog · **Depende de:** H0.1

---

# Iteração 1 — Núcleo

## H1 — Autenticar via Authelia

> **Como** um estagiário ou supervisor já autenticado no Authelia,
> **eu quero** acessar o sistema sem precisar fazer login novamente,
> **para que** eu não tenha que digitar senha duas vezes.

**Critérios de aceitação:**

1. Acesso a qualquer rota com header `Remote-User` válido cria/atualiza
   `Estagiario` correspondente e prossegue.
2. Acesso sem header `Remote-User` retorna **401 Unauthorized**.
3. Acesso com `Remote-Groups` que não contém `admin` nem `estagiarios`
   retorna **403 Forbidden**.
4. Em ambiente `local` com `AUTHELIA_DEV_BYPASS=true`, requisições sem
   headers usam usuário simulado de `.env`.
5. Em ambiente `production`, `AUTHELIA_DEV_BYPASS` é ignorado mesmo se
   `true` (defesa contra config errada).
6. `auth()->user()` retorna o `Estagiario` correspondente em todas as
   rotas autenticadas.

**Status:** ✅ Done — `app/Http/Middleware/AutheliaAuth.php` + 6 testes em
`tests/Feature/Middleware/AutheliaAuthTest.php`.

---

## H2 — Bater entrada

> **Como** estagiário,
> **eu quero** clicar em "Bater entrada" no dashboard,
> **para que** o horário atual seja registrado como minha entrada do dia.

**Critérios de aceitação:**

1. Botão "Bater entrada" visível quando: hoje é dia útil **E** ainda não
   há registro de entrada.
2. Ao clicar, cria registro `Frequencia` com `data=hoje` e
   `entrada=horário atual do servidor` (HH:mm:ss).
3. Após registrar, botão muda para "Bater saída".
4. Tentativa de bater entrada quando já há entrada → mensagem de erro
   "Entrada já registrada hoje às HH:MM."
5. Tentativa em sábado/domingo/feriado → erro "Hoje não é dia útil."
6. IP da requisição é persistido em `ip_entrada`.

**Status:** ✅ Done — `PontoService::baterEntrada` + `CalendarioService::ehDiaUtil`,
6 testes em `tests/Unit/Services/PontoServiceTest.php`. UI (botão visível) fica
para H4. **Depende de:** H1

---

## H3 — Bater saída

> **Como** estagiário,
> **eu quero** clicar em "Bater saída" depois de ter batido entrada,
> **para que** o sistema calcule automaticamente as horas trabalhadas.

**Critérios de aceitação:**

1. Botão "Bater saída" visível apenas quando há entrada do dia sem saída.
2. Ao clicar, atualiza o `Frequencia` do dia com `saida=horário atual`.
3. Campo `horas` é calculado e persistido como `(saida - entrada)` em
   horas decimais com 2 casas.
4. Tentativa de bater saída sem ter batido entrada → erro "Você precisa
   bater a entrada antes de bater a saída."
5. Tentativa de bater saída quando já há saída → erro "Saída já
   registrada hoje às HH:MM."
6. IP da requisição é persistido em `ip_saida`.

**Status:** ✅ Done — `PontoService::baterSaida` + endpoints `POST /ponto/{entrada,saida}`
+ `DomainException` → 422 JSON. 7 testes service + 4 controller. **Depende de:** H2

---

## H4 — Dashboard inicial

> **Como** estagiário,
> **eu quero** ver na tela inicial meu status do dia, total de horas no
> mês corrente e dias batidos,
> **para que** eu saiba rapidamente se já bati ponto e quanto falta.

**Critérios de aceitação:**

1. Dashboard mostra cards: "Status hoje", "Horas no mês", "Dias batidos".
2. "Status hoje" exibe um de: "aguardando entrada", "em andamento desde
   HH:MM", "concluído (Xh)".
3. "Horas no mês" soma `horas` de todas as frequências do mês corrente.
4. "Dias batidos" conta frequências do mês corrente com `saida` preenchida.
5. Botão de bater ponto está sempre visível, ajustando label conforme
   estado.

**Status:** ✅ Done — `DashboardService` + `DashboardController` + view com
gov.br DS/TRE-AC. 5 testes service + 4 feature. **Depende de:** H2, H3

---

# Iteração 2 — Folha mensal

## H5 — Ver folha do mês

> **Como** estagiário,
> **eu quero** ver uma folha mensal com todos os dias do mês listados,
> **para que** eu visualize entradas, saídas, feriados e fins de semana
> de uma vez.

**Critérios de aceitação:**

1. Rota `/frequencia/{ano}/{mes}` mostra tabela com 1 linha por dia do mês.
2. Dias úteis com registro mostram entrada, saída e horas.
3. Dias úteis sem registro mostram "—" e badge "não batido".
4. Sábados e domingos têm linha cinza com label "Sábado"/"Domingo" e badge.
5. Feriados têm linha amarela com a descrição do feriado.
6. Total de horas do mês aparece no rodapé.
7. Sem parâmetros, abre o mês corrente.

**Status:** ✅ Done · **Depende de:** H3

---

## H6 — Navegar entre meses

> **Como** estagiário,
> **eu quero** ir para o mês anterior ou próximo,
> **para que** eu consulte folhas passadas sem digitar URL.

**Critérios de aceitação:**

1. Botões "← Anterior" e "Próximo →" no cabeçalho da folha mensal.
2. Botão "← Anterior" navega para `{ano}/{mes-1}`, ajustando virada de ano.
3. Botão "Próximo →" navega para `{ano}/{mes+1}`, desabilitado se
   destino estiver no futuro (mês após o atual).
4. Cabeçalho mostra "MÊS/ANO" por extenso (ex: "Abril / 2026").

**Status:** ✅ Done · **Depende de:** H5

---

## H7 — Cálculo correto com viradas

> **Como** sistema,
> **eu preciso** calcular horas corretamente em qualquer cenário,
> **para que** o total mensal nunca esteja errado.

**Critérios de aceitação:**

1. Entrada 09:30 → saída 14:30 = 5.0 h
2. Entrada 10:00 → saída 14:45 = 4.75 h
3. Entrada 09:00 → saída 09:00 = 0 h (rejeitar como inválido na verdade)
4. Saída antes da entrada → erro de validação, registro não é salvo.
5. Soma do mês com 20 dias úteis × 5h = 100.0 h, sem erros de ponto
   flutuante (ex: nunca aparecer 99.99999...).

**Status:** ✅ Done — validações em `PontoService::baterSaida` (saída
posterior à entrada) + agregação testada em `DashboardServiceTest`.
3 testes adicionais. **Depende de:** H3

---

# Iteração 3 — Calendário de feriados

## H8 — Listar feriados do ano

> **Como** admin,
> **eu quero** ver todos os feriados cadastrados de um ano,
> **para que** eu confira se o calendário está completo antes do início
> do ano.

**Critérios de aceitação:**

1. Rota `/admin/feriados?ano=2026` lista feriados ordenados por data.
2. Lista inclui feriados recorrentes (ex: 25/12 todo ano).
3. Cada linha mostra data, descrição, tipo (nacional/estadual/municipal/recesso),
   UF (se estadual), badge "recorrente" se aplicável.
4. Filtro por tipo no topo da página.

**Status:** ✅ Done · **Depende de:** H1 (com grupo admin)

---

## H9 — Cadastrar feriado

> **Como** admin,
> **eu quero** cadastrar um novo feriado,
> **para que** o sistema saiba que aquele dia não é útil.

**Critérios de aceitação:**

1. Formulário com campos: data, descrição, tipo, UF (opcional),
   recorrente (checkbox).
2. Validação: data obrigatória, descrição até 200 chars, tipo dentre
   `nacional|estadual|municipal|recesso`, UF com 2 letras se preenchida.
3. Salvar duas datas idênticas → erro "Já existe feriado nesta data."
4. Após salvar, redireciona para lista com mensagem de sucesso.
5. Feriado recém-criado afeta imediatamente classificação de dias na
   folha mensal de qualquer estagiário.

**Status:** ✅ Done · **Depende de:** H8

---

## H10 — Editar/remover feriado

> **Como** admin,
> **eu quero** editar ou remover feriados,
> **para que** eu corrija erros ou ajuste recessos.

**Critérios de aceitação:**

1. Botão "Editar" abre formulário pré-preenchido.
2. Botão "Remover" pede confirmação ("Remover feriado X?").
3. Remoção de feriado afeta classificação retroativa: dias que eram
   "feriado" voltam a ser "dia útil sem registro".
4. Se já existem `Assinatura` para aquele mês com hash baseado no estado
   anterior, o sistema mostra aviso ao admin: "X folhas assinadas terão
   o hash invalidado."

**Status:** ✅ Done — `FeriadoController` ganhou `edit/update/confirmDestroy/destroy`.
Aviso de hash invalidado quando há `Assinatura` no mês do feriado a remover.
6 testes em `tests/Feature/Admin/FeriadoEdicaoTest.php`. **Depende de:** H9, H13

---

# Iteração 4 — PDF e assinatura

## H11 — Gerar PDF da folha mensal

> **Como** estagiário,
> **eu quero** baixar minha folha do mês em PDF no formato CIEE,
> **para que** eu possa imprimir, anexar ao SEI ou enviar à coordenação.

**Critérios de aceitação:**

1. Botão "Gerar PDF" na folha mensal.
2. PDF gerado tem layout idêntico (estrutura, ordem de campos, colunas)
   à FCF oficial do CIEE.
3. Cabeçalho: nome, lotação, supervisor, mês, SEI, início do estágio.
4. Tabela: 1 linha por dia, colunas DATA / ENTRADA / SAÍDA / TOTAL DE
   HORAS / VISTO DO ESTAGIÁRIO.
5. Feriados e fins de semana aparecem como linha contínua riscada
   com label.
6. Rodapé: total de horas, espaço para assinatura do supervisor com
   carimbo, observações, data e local.
7. Nome do arquivo: `frequencia_{username}_{ano}-{mes}.pdf`.

**Status:** ✅ Done **(parcial — sem layout pixel-perfect da FCF)** —
`PdfFolhaMensalService` + `barryvdh/laravel-dompdf` + view
`frequencia/pdf.blade.php`. Rota `GET /frequencia/{ano}/{mes}/pdf` com a
mesma autorização de H15 (admin todos; supervisor seus; estagiário só
o próprio). Bloco de assinatura **placeholder** ("Aguardando
assinatura") até H12/H13. 8 testes em
`tests/Feature/PdfFolhaMensalTest.php`. **Pendente:** alinhamento
visual exato com a FCF oficial do CIEE (critério 2). **Depende de:** H5

---

## H12 — Assinar folha como estagiário

> **Como** estagiário,
> **eu quero** assinar minha folha do mês após o último dia útil,
> **para que** ela fique selada e pronta pra contra-assinatura do
> supervisor.

**Critérios de aceitação:**

1. Botão "Assinar folha" visível na folha mensal apenas se: o mês já
   passou (mês < mês atual) **ou** é o último dia útil do mês corrente.
2. Modal de confirmação com texto: "Após assinar, alterações nos
   registros invalidarão o hash. Confirmar?"
3. Ao confirmar, sistema cria `Assinatura` com:
   - `papel='estagiario'`, `assinante_username` do header Authelia
   - snapshot dos registros + hash SHA-256
   - `assinado_em` = timestamp do servidor, `ip` da requisição
4. Tentativa de assinar duas vezes (mesmo papel, mesmo mês) → erro
   "Esta folha já foi assinada como estagiário."
5. Após assinada, folha mostra hash truncado e data/hora da assinatura.

**Status:** ✅ Done — `AssinaturaService` + `AssinaturaController` +
modelo/migration `assinaturas`. Snapshot canônico determinístico +
SHA-256. View mostra hash truncado e badge "íntegra/alterada". 9 testes
em `tests/Feature/AssinaturaTest.php` + 8 testes Unit. Validações:
papel já assinado (422), mês futuro (422), folha de outro (403).
**Depende de:** H5, H11

---

## H13 — Contra-assinar folha como supervisor

> **Como** supervisor,
> **eu quero** contra-assinar a folha de um estagiário sob minha
> responsabilidade,
> **para que** o documento esteja completo e pronto pro RH arquivar.

**Critérios de aceitação:**

1. Supervisor logado vê em `/supervisor` a lista dos estagiários
   ligados a ele via `Estagiario.supervisor_username`.
2. Botão "Contra-assinar como supervisor" é exibido se: estagiário já
   assinou **E** supervisor ainda não assinou **E** o supervisor logado
   é o `supervisor_username` do estagiário.
3. Supervisor tentando contra-assinar folha de estagiário **que não é
   dele** → 403, mesmo no caso em que a folha já foi assinada pelo
   estagiário.
4. Ao clicar, mesmo fluxo de modal/confirmação da H12, mas com
   `papel='supervisor'`.
5. PDF gerado depois da contra-assinatura inclui ambos os blocos de
   assinatura preenchidos com nome, data/hora, hash truncado.
6. Tentativa de assinar como supervisor sem o estagiário ter assinado
   antes → erro "O estagiário precisa assinar antes do supervisor."
7. Após contra-assinatura, a folha fica **automaticamente disponível
   para o RH baixar** (H20). Sem botão de "liberar" — fluxo é implícito.

**Status:** ✅ Done — `AssinaturaController::contraAssinarComoSupervisor`
+ rota `/supervisor` (`SupervisorDashboardController`) listando os
estagiários cujo `supervisor_username` casa com o do supervisor logado.
Validações: ordem (estagiário primeiro), permissão (supervisor
responsável), grupo (`'S'`). PDF preenche os dois blocos quando ambas
existem. 9 testes Feature de assinatura + 4 do dashboard supervisor.
**Depende de:** H12, H16

---

# Iteração 5 — Admin

## H14 — Dashboard de estagiários (admin/RH)

> **Como** admin/RH,
> **eu quero** ver lista de todos os estagiários ativos com horas do
> mês corrente,
> **para que** eu identifique rapidamente quem está em dia.

**Critérios de aceitação:**

1. Rota `/admin` lista estagiários ativos. Acesso restrito ao grupo
   `admin` — supervisor e estagiário comum recebem 403.
2. Cada linha: nome, lotação, horas no mês, dias batidos, status de
   assinatura (estagiário ✓/✗, supervisor ✓/✗).
3. Cada linha tem link "Ver folha" que abre folha mensal do estagiário
   no modo admin.
4. Filtro por lotação no topo.
5. Seletor de mês/ano para visualização histórica.
6. **Estagiário inativo (`ativo=false`) não aparece na lista** —
   completa o critério 4 da H16 do lado da visão admin.

**Status:** ✅ Done **(parcial)** — `app/Http/Controllers/Admin/DashboardController.php`
+ `DashboardAdminService` (query agregada batch). 7 testes em
`tests/Feature/Admin/DashboardAdminTest.php`. **Pendente:** colunas de
status de assinatura (critério 2) — entram quando H12/H13 chegarem.
**Depende de:** H5

---

## H15 — Ver folha de qualquer estagiário

> **Como** admin/RH **ou** supervisor,
> **eu quero** abrir a folha mensal dos estagiários sob minha alçada,
> **para que** eu acompanhe sem precisar pedir ao estagiário.

**Critérios de aceitação:**

1. Folha mensal aceita query string `?estagiario={username}` quando
   acessada por admin **ou** supervisor.
2. Admin pode abrir folha de qualquer estagiário.
3. Supervisor pode abrir folha **apenas dos seus** estagiários
   (`supervisor_username` casa com o do estagiário). Tentativa de abrir
   folha de outro estagiário → 403.
4. Estagiário comum tentando acessar folha de outro → 403.
5. Visão admin/supervisor é igual à do estagiário, mas sem botão de
   bater ponto. O botão "Contra-assinar" aparece apenas pra supervisor
   responsável (regra de H13).

**Status:** ✅ Done **(parcial)** — `FolhaMensalController::show` aceita
`?estagiario={username}`; admin vê qualquer um, estagiário comum
restrito ao próprio (403 fora disso, 404 pra inexistente). Banner
identificando alvo + navegação preserva query. 7 testes em
`tests/Feature/Admin/VerFolhaDeOutroTest.php`. **Pendente:** filtro
"supervisor só vê os seus" (critério 3) e botão "Contra-assinar"
(critério 5) — entram quando grupo `supervisores` e H13 chegarem.
**Depende de:** H5, H13, H16 (`supervisor_username`)

---

## H16 — Cadastrar dados administrativos do estagiário

> **Como** admin/RH,
> **eu quero** preencher matrícula, lotação, supervisor, SEI, início e
> fim do estágio, e anexar o PDF do contrato do estagiário,
> **para que** o PDF gerado tenha todos os campos do formato CIEE e o
> contrato fique acessível via sistema.

**Critérios de aceitação:**

1. Tela `/admin/estagiarios` lista todos.
2. Formulário de edição com: matrícula, lotação, supervisor_nome,
   **supervisor_username (referencia o login Authelia do supervisor)**,
   sei, inicio_estagio, fim_estagio, horas_diarias (default 5), ativo
   (checkbox), **upload de contrato PDF**.
3. Username, nome e email vêm do Authelia, não são editáveis.
4. Estagiário com `ativo=false` não aparece em dashboards e não consegue
   bater ponto (mensagem: "Estágio inativo. Procure a coordenação.").
5. **Upload do contrato:**
   - Aceita apenas `application/pdf`, máx 5 MB.
   - Armazena em disk privado (`storage/app/contratos/`); nome no disco
     é hash gerado pelo Laravel.
   - Novo upload **substitui** o anterior (sem versionamento).
   - Coluna nova `contrato_path` em `estagiarios` (string nullable).
6. **Download do contrato:**
   - Rota autenticada `GET /admin/estagiarios/{id}/contrato` —
     admin/RH baixa qualquer; estagiário pode baixar **o seu próprio**;
     supervisor pode baixar dos estagiários sob ele; demais → 403.

**Status:** ✅ Done — `app/Http/Controllers/Admin/EstagiarioController.php`
com `index/edit/update/contrato` + `PontoService::garantirAtivo`.
`supervisor_username` (crit 2) implementado via migration
`add_supervisor_username_to_estagiarios` + form. Upload de contrato (crit
5) usa `Storage::disk('local')->putFile('contratos', ...)` com validação
`mimes:pdf + mimetypes:application/pdf + max:5120`; novo upload deleta
o anterior. Download (crit 6) em `GET /admin/estagiarios/{id}/contrato`
com autorização inline (admin OR self OR supervisor responsável → 403
senão; 404 sem upload). 19 testes Feature (13 em `EstagiarioEdicaoTest`
+ 6 em `ContratoDownloadTest`) + 2 testes Unit. **Depende de:** H14

---

## H20 — RH baixa PDF assinado pra anexar no SEI

> **Como** RH,
> **eu quero** baixar o PDF da folha mensal já assinada por estagiário e
> supervisor,
> **para que** eu anexe no processo SEI sem ter que pedir aos envolvidos.

**Critérios de aceitação:**

1. Após H13 (contra-assinatura do supervisor), a folha entra
   automaticamente no estado "liberada para RH" — sem botão extra de
   "liberar".
2. Tela `/admin` (H14) mostra coluna ou filtro "liberadas para RH"
   listando folhas com ambas assinaturas.
3. Botão "Baixar PDF assinado" gera o PDF da H11 incluindo os dois
   blocos de assinatura preenchidos.
4. Tentativa de baixar uma folha sem ambas assinaturas pelo RH →
   permitido com aviso "Folha não tem contra-assinatura do supervisor."
   (RH pode optar por baixar mesmo assim para conferência).
5. Estagiário ou supervisor tentando essa rota → 403.

**Status:** ✅ Done — `/admin` (H14) ganhou colunas "estagiário ✓/✗" e
"supervisor ✓/✗", filtro `?liberadas=1` (apenas folhas com ambas
assinaturas), e botão "Baixar PDF" exibido só quando liberada. PDF da
H11 já preenche os blocos de assinatura quando assinaturas existem.
2 testes novos em `DashboardAdminTest`. **Depende de:** H11, H12, H13

---

# Iteração 6 — Polimento

## H17 — Observações no dia

> **Como** estagiário,
> **eu quero** adicionar observação a um dia específico,
> **para que** eu justifique uma ausência ou registre algo relevante.

**Critérios de aceitação:**

1. Cada linha da folha mensal tem ícone "adicionar observação" se for
   dia útil.
2. Modal abre com textarea (máx 500 chars).
3. Observação é mostrada como tooltip/popover na folha mensal e como
   nota de rodapé no PDF.
4. Observação só pode ser editada antes da assinatura do estagiário.

**Status:** ✅ Done — `App\Http\Controllers\ObservacaoController::salvar`
em `POST /frequencia/{ano}/{mes}/{dia}/observacao`. Coluna `observacao`
(string 500) já existia em `frequencias` e já era persistida no
snapshot canônico do `AssinaturaService`. Quando estagiário sem
`Frequencia` no dia adiciona observação, criamos uma `Frequencia`
apenas com `observacao` (entrada/saida/horas nulos) — representa
"ausência justificada". Texto vazio remove a observação; se a
`Frequencia` era só observação, ela é deletada inteira. Fim de semana
e feriado retornam 422; admin/supervisor retornam 403; após
`Assinatura` papel=estagiario para o mês, edição retorna 422
("Folha já assinada"). Folha mensal exibe a observação inline +
`<details>` com `<form>` de edição (textarea maxlength=500); PDF
mostra na coluna Observação. Tabela ganhou nova coluna **Status** pra
não conflitar com a coluna **Observação**, agora com texto real. **14
testes novos** (12 em `ObservacaoControllerTest` + 1 em
`FolhaMensalTest` + 1 em `PdfFolhaMensalTest`). **Depende de:** H12

---

## H18 — Verificação de integridade da assinatura

> **Como** admin,
> **eu quero** ver se uma folha assinada teve seus dados alterados
> depois da assinatura,
> **para que** eu detecte tampering ou correções pós-assinatura.

**Critérios de aceitação:**

1. Tela admin mostra para cada folha assinada: badge "✓ íntegra" (verde)
   se hash atual bate com snapshot, ou "⚠ alterada" (vermelho) se não.
2. Ao clicar em "⚠ alterada", mostra diff: o que mudou desde a assinatura.
3. Verificação roda sob demanda (não em listagens grandes, pra performance).

**Status:** ✅ Done — `AssinaturaService::diff(Assinatura)` compara
snapshot gravado vs canônico atual e retorna lista de mudanças
(`campo_alterado`/`dia_adicionado`/`dia_removido`). View
`frequencia/show.blade.php` mostra `<details>` com diff renderizado
ao lado do badge "⚠ alterada". Verificação roda só em show de folha
(1 estagiário/mês), não em listagens. **6 testes novos** (4 unit em
`AssinaturaServiceTest::diff*`, 2 feature em `AssinaturaTest`).

---

## H19 — Auditoria de ações

> **Como** admin,
> **eu quero** consultar log de ações relevantes (bater ponto, assinar,
> editar feriado),
> **para que** eu investigue inconsistências.

**Critérios de aceitação:**

1. Tabela `auditoria` com: usuário, ação, entidade, entity_id, payload,
   ip, timestamp.
2. Eventos auditados: criar/editar frequência, criar/remover assinatura,
   CRUD de feriado, CRUD de estagiário.
3. Tela `/admin/auditoria` com filtro por usuário, ação, intervalo de
   datas.
4. Logs são append-only (sem update/delete na app — só insert).

**Status:** ✅ Done — Migration `create_auditoria_table` (Oracle compat,
identifiers ≤ 30 chars, índices em usuario/acao/entidade/created_at).
Model `Auditoria` append-only (sem update/delete por convenção).
`AuditoriaService::registrar(usuario, acao, entidade, ?id, payload, ?ip)`
serializa payload em JSON. Hooks em `PontoController` (entrada/saida),
`ObservacaoController`, `AssinaturaController` (assinar/contra-assinar/
re-assinar/re-contra-assinar), `FeriadoController` (criar/editar/remover),
`EstagiarioController` (editar). Tela `/admin/auditoria` com filtros
(usuario, acao, intervalo de datas), limite de 500 linhas/consulta,
admin-only via `adminOnlyRouteNames`. Link "Auditoria" na nav admin.
**10 testes novos** (3 unit + 7 feature).

---

## H23 — Tela explicativa no primeiro acesso

> **Como** usuário que abre o sistema pela primeira vez,
> **eu quero** ver uma página rápida explicando o que cada tela faz e
> qual o fluxo (bater ponto → folha → assinar → contra-assinar → SEI),
> **para que** eu não precise de treinamento separado pra usar o Bena.

**Critérios de aceitação:**

1. Ao acessar uma rota "home" pela primeira vez (`/`, `/admin` ou
   `/supervisor`), usuário com `tutorial_visto_em IS NULL` é redirecionado
   pra `/bem-vindo`.
2. View mostra cabeçalho amigável + 5 cards verticais ilustrando: bater
   ponto, folha mensal, assinatura digital, fluxo supervisor/RH/SEI,
   auto-fechamento.
3. Botão "Entendi, vamos começar" envia POST que seta
   `tutorial_visto_em = now()` e redireciona pro dashboard apropriado.
4. Acesso a `/bem-vindo` continua disponível depois (rever tutorial).
5. Middleware NÃO redireciona em rotas não-home (ex: link salvo da folha
   mensal continua funcionando direto).

**Status:** ✅ Done — Migration `add_tutorial_visto_em_to_estagiarios`,
middleware `EnsureOnboarded` aplicado nas rotas `dashboard`,
`admin.dashboard` e `supervisor.dashboard`. `OnboardingController` com
`show`/`concluir`. View `onboarding/show.blade.php`. Factory `Estagiario`
ganhou state `->semOnboarding()`. **8 testes novos** em `OnboardingTest`.

---

## H22 — Re-assinatura da versão atual quando hash divergiu

> **Como** estagiário ou supervisor que assinou por engano (ou fez
> alteração necessária após assinar via processo administrativo),
> **eu quero** poder re-assinar a versão atual da folha,
> **para que** o registro oficial reflita a folha correta sem perder o
> histórico da assinatura anterior.

**Critérios de aceitação:**

1. Quando a assinatura vigente do user logado aparece como "⚠ alterada"
   na seção de Assinaturas, surge botão "Re-assinar versão atual" ao
   lado do badge.
2. Botão envia POST pra `frequencia.reassinar` (estagiário) ou
   `frequencia.re-contra-assinar` (supervisor) com modal de confirmação.
3. Re-assinatura cria **nova** entrada e marca a anterior como
   `substituida_em = now()`. Histórico preservado pra auditoria.
4. `verificar()` e `assinaturaDoMes()` retornam **apenas** a vigente
   (`substituida_em IS NULL`).
5. **Re-assinar como estagiário invalida automaticamente a
   contra-assinatura do supervisor** — folha mudou, supervisor precisa
   rever. Re-assinar como supervisor não toca na do estagiário.
6. Re-assinatura falha se a vigente ainda está íntegra (hash bate com
   atual) → 422 "Re-assinatura desnecessária".
7. Re-assinatura falha se não há vigente → 422.
8. Estagiário só re-assina a sua. Supervisor só re-contra-assina dos
   estagiários sob ele (mesma regra de H13).

**Status:** ✅ Done — `AssinaturaService::reassinar()` +
`AssinaturaController::{reassinarComoEstagiario,reContraAssinarComoSupervisor}`
+ rotas `frequencia.reassinar`/`frequencia.re-contra-assinar`. Migration
`add_substituida_em_to_assinaturas` (drop unique antiga + coluna nova
+ índice non-unique). Service filtra `substituida_em IS NULL` em
`assinaturaDoMes` e `verificar`. View show.blade.php mostra botão
condicional. **18 testes novos** (7 Unit em `AssinaturaServiceTest`,
6 Feature em `AssinaturaTest`, 2 Feature de view + outros). **Depende
de:** H12, H13

---

## H21 — Fechamento automático de ponto esquecido

> **Como** sistema,
> **eu preciso** fechar pontos em que o estagiário bateu entrada e
> esqueceu de bater saída,
> **para que** o registro do dia fique consistente e a folha mensal não
> tenha "buracos" indefinidos por esquecimento.

**Critérios de aceitação:**

1. Cron diário (00:05) fecha registros com `entrada` preenchida e
   `saida` ainda nula em dias `< hoje`.
2. `saida = entrada + horas_diarias` (jornada do estagiário; default 5h,
   customizável via H16).
3. `horas` é gravada igual a `horas_diarias`.
4. Coluna nova `saida_automatica` (boolean) marca o registro como
   auto-fechado, separando dos pontos batidos manualmente.
5. Idempotente: rodar duas vezes não muda nada (não toca em registros
   com saida já preenchida).
6. Não fecha o ponto do dia atual (estagiário ainda pode bater saída).
7. Folha mensal e PDF indicam visualmente quando saída foi automática
   (badge "batido (auto)" e marcador `*` no PDF).

**Status:** ✅ Done — `PontoService::fecharPontosAbertos()` + comando
`ponto:fechar-abertos` (`App\Console\Commands\FecharPontosAbertosCommand`)
agendado em `routes/console.php` via `Schedule::command(...)->dailyAt('00:05')`.
Migration `add_saida_automatica_to_frequencias`. View `frequencia/show.blade.php`
mostra badge `batido (auto)` e tooltip `⚠ auto`; PDF marca saída com `*`. 9
testes novos (6 Unit em `PontoServiceTest`, 3 Feature em
`FecharPontosAbertosCommandTest`, 1 em `FolhaMensalTest`). **Depende de:** H3, H16

---

# Requisitos não-funcionais

## Segurança

- **NF1.** Aplicação fica em rede interna; só o reverse proxy é exposto.
- **NF2.** `TRUSTED_PROXIES` configurado restritivamente (CIDR específico,
  não `*`).
- **NF3.** Headers `Remote-*` só são aceitos se `REMOTE_ADDR` for proxy
  confiável.
- **NF4.** Sessão expira em 30 min de inatividade (configurado no Authelia).
- **NF5.** 2FA obrigatório no Authelia (TOTP ou WebAuthn/YubiKey).
- **NF6.** Senhas nunca chegam à aplicação Laravel.
- **NF7.** CSRF habilitado em todas as rotas POST/PUT/DELETE.

## Performance

- **NF8.** Folha mensal carrega em < 500 ms para um mês completo.
- **NF9.** Dashboard admin com até 100 estagiários carrega em < 2s.
- **NF10.** Geração de PDF leva < 3s.

## Disponibilidade e operação

- **NF11.** Aplicação roda em containers Docker em todos os ambientes
  (dev, CI, produção).
- **NF12.** Migrations Oracle são reversíveis (cada `up()` tem `down()`
  funcional).
- **NF13.** Backup diário do banco; testes de restore mensais.
- **NF14.** Logs estruturados (JSON) em produção, enviados a stack
  centralizada.

## Infraestrutura (Docker)

- **NF22.** Toda a stack roda em Docker: dev, CI e produção usam a
  mesma imagem da aplicação (tags diferentes, mesma origem).
- **NF23.** `Dockerfile` da app é multi-stage com no mínimo `dev` e
  `prod`; imagem de produção não contém Xdebug, dev tools ou código
  de teste.
- **NF24.** `docker-compose.yml` (dev) sobe a stack completa com
  **um único comando** após `cp .env.example .env`.
- **NF25.** Containers de produção rodam como usuário não-root (UID
  ≥ 1000).
- **NF26.** Imagens de produção usam tags fixas (ex:
  `php:8.2.20-fpm-alpine`), nunca `latest`.
- **NF27.** Todos os serviços têm healthcheck. `depends_on` usa
  `condition: service_healthy` para garantir ordem de inicialização.
- **NF28.** Dados persistentes (Oracle dev, Redis, Authelia storage)
  ficam em volumes nomeados, nunca em bind mounts.
- **NF29.** Secrets (senhas de banco, chaves Authelia, tokens) são
  injetados via Docker secrets ou variáveis de ambiente do
  orquestrador. **Nunca** commitados no repo.
- **NF30.** `.dockerignore` exclui `vendor/`, `node_modules/`, `.git/`,
  `.env`, `tests/`, `storage/logs/*`.
- **NF31.** Imagem da app em produção tem tamanho < 300 MB.
- **NF32.** Build da imagem de produção em CI leva < 5 min.
- **NF33.** Pipeline de CI roda toda a suíte de testes dentro de
  container, contra o `Dockerfile` real (não simula ambiente).

## Compatibilidade

- **NF34.** Oracle 12c R2 ou superior (limite de 30 chars para
  identifiers seguido por compatibilidade ampla).
- **NF35.** PHP 8.2+.
- **NF36.** Docker 24+ e Docker Compose v2.
- **NF37.** Navegadores: Chrome/Edge/Firefox últimas 2 versões.
- **NF38.** Acessível em mobile (folha mensal e dashboard responsivos).

## Acessibilidade

- **NF39.** Contraste mínimo WCAG AA.
- **NF40.** Todos os formulários têm labels associadas a inputs.
- **NF41.** Botões de ação primários não dependem só de cor (ícone + texto).

---

# Histórias futuras (fora do escopo v1)

- Biometria/foto na hora de bater ponto (anti-fraude).
- Integração com SEI para envio automático do PDF mensal.
- App mobile nativo.
- Notificações por email/Slack quando estagiário esquece de bater saída.
- Banco de horas (compensação além das 5h diárias).
- Múltiplos turnos no mesmo dia.
- Relatório consolidado por lotação/período.
- Integração com folha de pagamento.
- Assinatura ICP-Brasil (PAdES) substituindo hash interno.

---

# Histórico de mudanças

| Data | Mudança |
|------|---------|
| 2026-04-30 | Documento inicial, 19 histórias em 6 iterações |
| 2026-04-30 | Adicionada Iteração 0 (fundação Docker) com H0.1–H0.4; novos NF22–NF33 sobre infraestrutura Docker; renumeração de NF15+ |
| 2026-05-01 | Refinado modelo de atores: Supervisor vira grupo Authelia separado (`supervisores`) com amarração 1↔N (`Estagiario.supervisor_username`); RH/Admin assume papel de baixar PDF para SEI. H13 ganha checagem de "supervisor responsável". H14/H15 incluem regras pra supervisor. H16 ganha contrato PDF e `supervisor_username`. Nova história H20 (RH anexa PDF no SEI). |
