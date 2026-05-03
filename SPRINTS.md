# SPRINTS

> Plano de execução das histórias do `REQUISITOS.md`. Cada sprint = 1 semana
> de trabalho. As histórias dentro de cada sprint respeitam dependências
> entre si. Sprints sucessivos só começam quando o anterior fecha com a
> barra verde no CI.

**Como ler este documento:**
- **Objetivo:** o resultado tangível ao fim do sprint.
- **Histórias:** lista referenciando o `REQUISITOS.md`.
- **Definition of Done (DoD):** o que precisa estar verde pra dar o sprint
  como fechado.
- **Riscos:** o que pode atrasar — vigiar de perto.

---

## Sprint 0 — Fundação (semana 1)

**Objetivo:** stack Docker rodando localmente com um comando, Oracle dev
acessível, Authelia validando login com 2FA, CI rodando testes em container.

| História | Resumo |
|----------|--------|
| **H0.1** | `docker compose up -d` sobe app + nginx + oracle + authelia + traefik + redis com healthchecks |
| **H0.2** | `Dockerfile` multi-stage; imagem `prod` < 300 MB, sem Xdebug |
| **H0.3** | Pipeline de CI executando `pint --test` + `php artisan test --coverage` em container |
| **H0.4** | Authelia local com `lucas.dev` (estagiarios) e `marco.dev` (admin), TOTP funcional |

**DoD:**
- `docker compose up -d` em máquina limpa (sem cache) sobe tudo em < 10 min.
- `make check` passa (mesmo com suíte vazia).
- README documenta setup + primeiro login Authelia.
- CI verde no primeiro PR.

**Riscos:**
- Oracle no Docker é lento na primeira subida (~3 min). Mitigação: usar
  `gvenzl/oracle-free:23-slim-faststart` e documentar a espera.
- Compilar oci8 em alpine pode quebrar — testar build cedo.

---

## Sprint 1 — Núcleo: bater ponto ponta a ponta (semana 2)

**Objetivo:** estagiário autenticado consegue bater entrada e saída, ver o
status do dia e horas do mês.

| História | Depende |
|----------|---------|
| **H1** — Autenticar via Authelia | Sprint 0 |
| **H2** — Bater entrada | H1 |
| **H3** — Bater saída | H2 |
| **H4** — Dashboard inicial | H2, H3 |
| **H7** — Cálculo correto com viradas (antecipado) | H3 |

> **Por que H7 entra aqui:** H3 já calcula horas. Garantir o cálculo certo
> agora evita rework e fixa o `PontoService` antes de ele virar dependência
> de tudo na Iteração 2.

**DoD:**
- Estagiário faz login, bate entrada, bate saída, vê o status no dashboard.
- Suíte de testes cobre 100% dos critérios das 5 histórias.
- Cobertura ≥ 90% em `PontoService` e `CalendarioService`.

**Riscos:**
- Middleware Authelia depende de `TRUSTED_PROXIES`. Errar aqui é falha
  de segurança séria — pair review obrigatório.

---

## Sprint 2 — Folha mensal e calendário básico (semana 3)

**Objetivo:** estagiário enxerga sua folha do mês com classificação correta
de dias úteis/feriados/fds. Admin já consegue cadastrar feriados.

| História | Depende |
|----------|---------|
| **H8** — Listar feriados do ano | H1 (admin) |
| **H9** — Cadastrar feriado | H8 |
| **H5** — Ver folha do mês | H3, H9 (precisa do calendário) |
| **H6** — Navegar entre meses | H5 |

> **Decisão de sequenciamento:** H5 originalmente vinha antes do calendário
> de feriados, mas a folha mensal sem feriados cadastrados é meio inútil.
> Trazemos H8/H9 pra antes pra que H5 nasça já realista.

**DoD:**
- Admin cadastra feriados nacionais via seeder + UI.
- Estagiário abre `/frequencia/2026/05` e vê a folha pintada corretamente.
- Navegação anterior/próximo funcional.

**Riscos:**
- Performance da folha: NF8 exige < 500 ms. Vigiar N+1 queries.

---

## Sprint 3 — Edição de feriados, PDF e assinatura (semana 4)

**Objetivo:** estagiário gera PDF no formato CIEE e assina. Admin
contra-assina. Edição/remoção de feriado completa o CRUD.

| História | Depende |
|----------|---------|
| **H10** — Editar/remover feriado | H9, H13 |
| **H11** — Gerar PDF da folha mensal | H5 |
| **H12** — Assinar folha como estagiário | H5, H11 |
| **H13** — Contra-assinar folha como supervisor | H12 |

**DoD:**
- PDF visualmente igual à FCF oficial do CIEE (validar com print da
  versão em papel).
- Hash SHA-256 reproduzível: rodar a verificação duas vezes na mesma
  folha sem alteração dá o mesmo hash.
- Aviso de "X folhas terão hash invalidado" funciona ao remover feriado.

**Riscos:**
- Layout pixel-perfect com DomPDF é trabalhoso. Reservar tempo.
- Determinismo do hash exige ordenação canônica do snapshot.

---

## Sprint 4 — Visão admin completa (semana 5)

**Objetivo:** admin vê todos os estagiários, filtra por lotação, abre folha
de qualquer um e gerencia dados administrativos.

| História | Depende |
|----------|---------|
| **H16** — Cadastrar dados administrativos do estagiário | (independente) |
| **H14** — Dashboard de estagiários | H5, H16 |
| **H15** — Ver folha de qualquer estagiário | H5, H13 |

> H16 vai primeiro porque sem matrícula/lotação/SEI o dashboard exibe
> dados pela metade.

**DoD:**
- Admin abre `/admin`, filtra por lotação, clica num estagiário e vê
  a folha mensal dele.
- Estagiário inativo não aparece no dashboard nem consegue bater ponto.
- Estagiário comum tentando ver folha de outro recebe 403 (teste explícito).

**Riscos:**
- NF9 exige < 2s pra dashboard com 100 estagiários. Caching ou query
  agregada pode ser necessária.

---

## Sprint 5 — CI + fechamento H16 + polimento (semana 6)

**Objetivo:** fundação de CI fechada, débito da Sprint 4 (H16) zerado, e
sistema com rastreabilidade completa + mecanismos de integridade
pós-assinatura.

| Ordem | História | Depende |
|-------|----------|---------|
| 1 | **H0.3** — Pipeline CI rodando em container | H0.2 |
| 2 | **H16-fechamento** — Upload contrato PDF + UI `supervisor_username` | H16 |
| 3 | **H17** — Observações no dia | H12 |
| 4 | **H18** — Verificação de integridade da assinatura | H13 |
| 5 | **H19** — Auditoria de ações | H16 |

> **Por que H0.3 primeiro:** cada sprint sem CI é débito de regressão.
> Custo de adicionar agora (suíte de 167 testes já madura) é o mais
> baixo que vai ser. Fundação destrava o resto.
>
> **Por que H16-fechamento antes de H17–H19:** débito da Sprint 4. A
> coluna `supervisor_username` já está em uso (H13/SupervisorDashboard),
> mas o cadastro UI ainda não permite editá-la. E o upload de contrato
> PDF nunca foi codado.

**DoD:**
- `.github/workflows/ci.yml` verde em todo PR; pipeline falha se pint
  detecta problema, qualquer teste falha, ou cobertura cai abaixo de 80%.
- `/admin/estagiarios/{id}/edit` permite escolher supervisor (dropdown
  baseado no grupo `supervisores` do Authelia) e fazer upload de
  contrato PDF (≤ 5 MB, application/pdf).
- Tabela `auditoria` recebe insert em todas as ações listadas.
- Folha alterada após assinatura mostra badge "⚠ alterada" + diff.
- Observação aparece como tooltip na folha e nota de rodapé no PDF.

**Riscos:**
- Auditoria pode virar gargalo se síncrona. Avaliar fila/job se ficar
  lenta — mas só se medir, sem otimização precoce.
- Upload de PDF privado exige que o disk `local` (não `public`) esteja
  configurado e a rota de download tenha checagem de permissão correta.

---

## Sprint 7a — Filtros inteligentes (UI/UX)

**Objetivo:** Eliminar fricção nas listagens admin (auto-submit ao trocar
select) e adicionar busca client-side por texto nas tabelas. Pequeno e
independente — não bloqueia H18/H19.

| História | Resumo |
|----------|--------|
| **H24** | Auto-submit nos selects de lotação/tipo/mês (3 views admin) — botão "Filtrar" passa a `<noscript>` (fallback sem JS) |
| **H25** | Busca client-side em `admin/estagiarios/index` e `admin/dashboard` — input usa `.bena-form__input`, normaliza NFD para busca sem acentos, JS vanilla puro em `@push('scripts')` |

**DoD:**
- Os 3 forms (estagiarios/index, feriados/index, admin/dashboard) submetem
  ao trocar select. Checkbox "liberadas" também auto-submete.
- Busca rápida funciona case-insensitive e sem acentos nas duas tabelas.
- 234 testes verdes (suíte preexistente; nada quebrou).

---

## Sprint 7b — Calendário anual visual (UI/UX)

**Objetivo:** Visão anual com mapa de calor temático por mês, feriados
destacados e link de acesso na nav. Reaproveita `CalendarioService::feriadosDoAno()`.

| História | Resumo |
|----------|--------|
| **H26** | Rota `GET /calendario?ano=YYYY` com 12 mini-calendários em grid responsivo (`auto-fill, minmax(260px, 1fr)`), paleta RGB temática por mês, fim de semana azul `#dbeafe`, feriado âmbar `#fef3c7` com dot dourado, hoje com border navy. Click em feriado abre `admin.feriados.edit` apenas para admin |
| **H27** | Link "Calendário" na `.bena-nav` (visível para todos os grupos) e botão "Calendário anual" na dashboard do estagiário |

**DoD:**
- `CalendarioAnualController` + view `calendario/index.blade.php` + rota
  dentro do grupo `configure.session`.
- 5 testes novos (`CalendarioAnualTest`): 200 para admin/supervisor/
  estagiário, parâmetro `ano`, exibe feriado cadastrado, exibe recorrente
  no ano solicitado, link edit apenas para admin.
- 239 verde / 553 asserções.

---

## Sprint 7c — Buddy / mascote (UI/UX + domínio leve)

**Objetivo:** Cada estagiário ganha um mascote pessoal (estilo Tamagotchi) que
aparece na dashboard com frases contextuais. Vínculo emocional com o sistema
sem custo de banco — a config das frases vive em `config/buddies.php`, só o
tipo do buddy é persistido.

| História | Resumo |
|----------|--------|
| **H28** | 8 buddies (coruja, gato, cachorro, capivara, papagaio, tartaruga, pinguim, sapo) cada um com personalidade. Atribuído aleatoriamente no primeiro acesso à dashboard, persistido em `estagiarios.buddy_tipo`. Frase escolhida deterministicamente por `(dia da semana, status do ponto, dia do mês)` — varia ao longo da semana mas estável dentro de um bloco do dia. Card com gradient navy + animação de bounce no avatar. Não aparece para admin/supervisor |

**DoD:**
- Migration `add_buddy_tipo_to_estagiarios` (string(20) nullable após
  `tutorial_visto_em`).
- `BuddyService::garantirBuddy(Estagiario, ?string $grupo = null)` é
  idempotente — só atribui se ainda não tem. Roteia por grupo:
  `'0'`/`'S'` → pool `tipos_supervisores` (águia, leão, elefante, urso),
  resto → pool `tipos` (8 originais).
- `BuddyService::montar()` retorna `BuddyData` (DTO readonly: tipo,
  emoji, nome, frase) usando seleção determinística por
  `(dia-do-mês + bloco de 12h)` — frase estável dentro do bloco.
- `BuddyService::boasVindas()` retorna BuddyData com frase de
  apresentação, usado na tela `/bem-vindo` para todos os grupos.
- Frases organizadas em `config/buddies.php` por
  `(buddy, dia_semana, status_ponto)` + `boas_vindas` + `generica`.
- Card visível: na dashboard só para grupo `'E'`; no `/bem-vindo` para
  todos os grupos (apresentação faz parte da onboarding).
- Variantes CSS `bena-buddy-card` (default) e
  `bena-buddy-card--apresentacao` (avatar maior, com rodapé explicativo).
- 9 testes unit + 7 testes feature cobrem atribuição, persistência,
  montagem, determinismo, fallback, pools por grupo, e visibilidade
  por contexto. Total: 257 verde / 604 asserções.

---

## Sprint 6 — Hardening e homologação (semana 7)

**Objetivo:** preparar pra entrar em homologação no tribunal. Foco em
NFRs, não em features novas.

**Foco:**
- Validar todos os NFRs de segurança (NF1–NF7) com pen test interno.
- Configurar `docker-compose.prod.yml` apontando pra Oracle externo.
- Smoke tests E2E (Cypress ou Playwright em container).
- Backup automatizado + restore testado.
- Deploy via Swarm/K8s (a definir com infra do tribunal).
- Documentação de operação: como reiniciar, como ler logs, como
  resetar TOTP de um usuário.

**DoD:**
- Sistema sobe em ambiente de homologação do tribunal apontando pro
  Oracle real.
- Time de infra consegue operar sem ajuda do dev.
- Teste de carga: 100 estagiários simultâneos batendo ponto sem erros.

---

## Visão geral em uma tabela

| Sprint | Semana | Histórias | Marco | Status |
|--------|--------|-----------|-------|--------|
| 0 | 1 | H0.1, H0.2, H0.3, H0.4 | Stack roda + CI | ✅ Done (parcial — H0.3 pipeline local pronto; arquivo de CI fica pra criação no GitLab interno em 2026-05-04. H0.4 Authelia em dev foi descartada por decisão) |
| 1 | 2 | H1, H2, H3, H4, H7 | Bate ponto | ✅ **Done** |
| 2 | 3 | H8, H9, H5, H6 | Folha mensal | ✅ **Done** |
| 4 | 4 | H16, H14, H15 | Visão admin (puxada antes da 3) | ✅ **Done** (parciais em H14/H15/H16 — sem coluna assinatura/filtro supervisor/upload contrato) |
| 3 | 5 | H10, H11, H12, H13, H20 | PDF + assinatura | ✅ **Done** |
| 5 | 6 | H0.3🚧, H16✅, H17✅, H18✅, H19✅ | CI + fechamento H16 + polimento | ✅ **Done** (H0.3 pipeline local pronto, falta `.gitlab-ci.yml`; demais histórias fechadas) |
| 7a | — | H24, H25 | Filtros inteligentes (UI/UX) | ✅ **Done** |
| 7b | — | H26, H27 | Calendário anual visual (UI/UX) | ✅ **Done** |
| 7c | — | H28 | Buddy / mascote do estagiário | ✅ **Done** |
| 6 | 7 | (NFRs) | Homologação | 📋 |

---

## Princípios pra ajustar o plano

- **Não furar a ordem sem motivo.** Se uma história aparece "fácil" mas
  depende de outra ainda não pronta, ela vai esperar.
- **Sprint travado é sprint perdido.** Se um item bloquear, troca ele
  pelo próximo independente e resolve em paralelo.
- **Cada sprint fecha com `make check` verde no CI.** Nunca leve débito
  pro próximo sprint sem registrar.
- **Re-priorização vem do `REQUISITOS.md`.** Se prioridade muda lá, este
  documento é atualizado pra refletir.

---

## Histórico

| Data | Mudança |
|------|---------|
| 2026-04-30 | Plano inicial: 7 sprints, 19 histórias + iteração 0 + hardening |
| 2026-05-01 | Sprints 1, 2, 4, 3 fechados (nessa ordem — Sprint 4 puxada à frente da 3 a pedido). H20 (RH baixa PDF) entrou na Sprint 3. Próxima: Sprint 5 (H17/H18/H19). |
| 2026-05-02 | Sprint 5 ampliada: começa por **H0.3** (CI pipeline) + **H16-fechamento** (contrato PDF + supervisor_username) antes de H17/H18/H19. Parte da H0.3 fechada — `make ci` valida o pipeline end-to-end (build dev → pint --test → test --coverage --min=80) via `docker-compose.test.yml`. O arquivo de CI propriamente dito fica pra criação como `.gitlab-ci.yml` quando o projeto migrar pro GitLab interno em 2026-05-04 (originalmente cogitado um `.github/workflows/ci.yml`, descartado: PAT do dev não tem escopo `workflow` e o GitHub é só hospedagem temporária). |
| 2026-05-02 | **H16 fechada** — supervisor_username já estava do trabalho da H13; upload de contrato PDF (`Storage::disk('local')->putFile('contratos', ...)` + `mimes:pdf + mimetypes:application/pdf + max:5120`, novo deleta antigo) e download com autorização inline (admin OR self OR supervisor responsável) implementados. 13 testes novos (180 verde / 410 assertions). Próximo: H17. |
| 2026-05-02 | **H17 fechada** — `ObservacaoController` em `POST /frequencia/{ano}/{mes}/{dia}/observacao`; coluna `observacao` já existia. Dia útil sem `Frequencia` que recebe observação cria registro só com `observacao` (ausência justificada); texto vazio limpa, e se `Frequencia` era só observação, deleta. Bloqueia fds/feriado (422), admin/supervisor (403), e mês já assinado (422). View show + PDF ganharam coluna `Status` separada de `Observação`. 14 testes novos (194 verde / 443 assertions). Próximo: H18. |
| 2026-05-03 | **Sprint 7a/7b fechadas (UI/UX)** — H24 (auto-submit nos selects de filtro em 3 views admin; botão "Filtrar" virou `<noscript>`) + H25 (busca client-side com NFD-normalize em estagiarios/index e admin/dashboard) + H26 (calendário anual `/calendario?ano=YYYY` com 12 mini-calendários, mapa de calor temático por mês, feriado âmbar com dot e link de edição apenas para admin) + H27 (link "Calendário" na nav e botão na dashboard estagiário). 5 testes novos (`CalendarioAnualTest`). Suíte: 239 verde / 553 asserções. Sprints independentes; H18/H19 da Sprint 5 seguem pendentes. |
| 2026-05-03 | **Refluxo do calendário** — `/calendario` passou a renderizar o **mês atual** por padrão (não mais grade anual de 12 meses). Adicionada view mensal `calendario.mes` para `/calendario/{ano}/{mes}`, com hover-tooltip CSS na descrição do feriado. Para admin: clique em dia vazio abre `<dialog>` com form de criar feriado (POST honra `redirect_to=/calendario/...` whitelistado por prefix). Removidas: view `admin/feriados/index.blade.php`, rota `admin.feriados.index`, `FeriadoController::index()`. Inserir/editar/excluir continuam admin-only via `ConfigureUserSession::adminOnlyRouteNames`. Visualização do calendário liberada para todos os grupos auth. Link "Feriados" sai da nav (Calendário cobre). Suíte: 240 verde / 559 asserções. |
| 2026-05-03 | **Sprint 7c fechada (buddy/mascote)** — H28: 8 buddies (coruja, gato, cachorro, capivara, papagaio, tartaruga, pinguim, sapo) com personalidades distintas. Atribuído aleatoriamente no primeiro acesso à dashboard do estagiário, persistido em `estagiarios.buddy_tipo` (migration nova). `BuddyService::garantirBuddy` (idempotente) + `montar()` retorna `BuddyData` (DTO readonly). Frases organizadas em `config/buddies.php` por `(buddy, dia da semana, status do ponto)` + `generica` fallback — seleção determinística por `(dia do mês + bloco de 12h)`. Card com gradient navy + animação de bounce, escondido para admin/supervisor. 9 testes novos (5 unit + 4 feature). Suíte: 249 verde / 583 asserções. |
| 2026-05-03 | **Buddy no `/bem-vindo` + pool sênior** — A apresentação do mascote passou a aparecer também na tela de boas-vindas (não só na dashboard), antes do tutorial. Variante CSS `bena-buddy-card--apresentacao` (avatar maior + rodapé explicativo). Adicionado pool sênior de buddies para supervisor/admin: 4 mascotes mais maduros (águia 🦅, leão 🦁, elefante 🐘, urso 🐻) com tom de mentoria. `BuddyService::garantirBuddy` ganhou parâmetro `?string $grupo` e roteia: `'0'`/`'S'` → `tipos_supervisores`, default → `tipos` (8 originais). `OnboardingController` injeta `BuddyService` e passa o grupo da sessão. 7 testes novos (4 sobre boas-vindas, 3 sobre pools). Suíte: 256 verde / 602 asserções. |
| 2026-05-03 | **Code review pré-commit** — Endurecida a whitelist do `redirect_to` em `FeriadoController::store`: trocado `str_starts_with($path, '/calendario')` por regex `^/calendario(/|\?|$)` que rejeita strings ambíguas como `/calendariofake` (cair no fallback). Comentário do `BuddyService::montar` reescrito pra refletir a regra real ("blocos de 12h" ao invés de "ímpar/par"). Teste novo `test_redirect_to_com_prefixo_falsificado_eh_ignorado`. Suíte: 257 verde / 604 asserções. |
| 2026-05-03 | **Página `/mascotes` + histórias eleitorais** — Página listando todos os 12 buddies (8 do pool padrão + 4 do sênior) com **personalidade** + **história curta** ligada à Justiça Eleitoral do Acre. Cada perfil em `config/buddies.php` ganhou as chaves `personalidade` e `historia` (ex.: Coruinha viu a chegada da urna eletrônica em 1996, Águia voou sobre as zonas remotas acompanhando transporte aéreo de urnas, Urso vigiou a primeira urna em comunidade indígena, etc.). Botão "Conhecer todos os mascotes" no `/bem-vindo`. `MascotesController` magrinho (lê do config). 5 testes feature em `MascotesPageTest` que iteram sobre o config — adicionar/editar mascote no futuro não quebra os testes. Suíte: 262 verde / 638 asserções. |
| 2026-05-03 | **Frases dos buddies reescritas com tema eleitoral** — As ~150 frases (12 buddies × 5 dias × 3 status + boas_vindas + generica) em `config/buddies.php` foram reescritas mantendo a personalidade de cada um, mas costuradas com vocabulário/metáforas da Justiça Eleitoral: urna, ata, apuração, pleito, mesa receptora, mesário, BU, recurso, diplomação, fiscalização, ciclo eleitoral, pleito municipal/estadual, etc. Testes não quebraram porque verificam estrutura, não strings específicas. Suíte: 262 verde / 638 asserções. |
| 2026-05-03 | **Sprint 5 fechada — H18 + H19** ✅ — **H18:** `AssinaturaService::diff(Assinatura)` compara snapshot gravado vs canônico atual; retorna `campo_alterado`/`dia_adicionado`/`dia_removido`. View `frequencia/show.blade.php` mostra `<details>` com lista de mudanças ao lado do badge "⚠ alterada". 6 testes novos. **H19:** Migration `create_auditoria_table` (Oracle-safe). Model `Auditoria` append-only. `AuditoriaService::registrar` insere log com payload JSON. Hooks integrados em `PontoController`, `ObservacaoController`, `AssinaturaController` (4 métodos), `FeriadoController` (3 métodos), `EstagiarioController`. Página `/admin/auditoria` com filtros (usuario, acao, intervalo de datas), limite de 500 linhas/consulta, admin-only. Link "Auditoria" na nav admin. 10 testes novos (3 unit + 7 feature). Suíte: 278 verde / 671 asserções. |
