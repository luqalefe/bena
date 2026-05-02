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
| 5 | 6 | H0.3🚧, H16-fechamento, H17, H18, H19 | CI + fechamento H16 + polimento | 🚧 **Em curso** (H0.3 pipeline local pronto, `.gitlab-ci.yml` aguarda migração pro GitLab interno) |
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
