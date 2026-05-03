# Bena — Visão geral

Documento de referência do **Bena**, o sistema interno de controle de
frequência de estagiários do Tribunal Regional Eleitoral do Acre (TRE-AC).
Pensado para quem chega ao projeto e precisa entender, em uma leitura, o
que ele é, por que existe, como está construído e o que já foi entregue.

> Para setup local e comandos, leia o [`README.md`](../README.md). Para
> convenções e workflow de desenvolvimento, [`CLAUDE.md`](../CLAUDE.md).
> Para histórias e critérios de aceitação, [`REQUISITOS.md`](../REQUISITOS.md).

---

## Sumário

1. [Apresentação](#apresentação)
2. [Por que "Bena"](#por-que-bena)
3. [História do projeto](#história-do-projeto)
4. [Arquitetura](#arquitetura)
5. [Domínio e fluxos](#domínio-e-fluxos)
6. [Funcionalidades entregues](#funcionalidades-entregues)
7. [Sistema Buddy — mascotes](#sistema-buddy--mascotes)
8. [Setup e operação](#setup-e-operação)
9. [Decisões arquiteturais](#decisões-arquiteturais)
10. [Estrutura de diretórios](#estrutura-de-diretórios)
11. [O que vem em seguida](#o-que-vem-em-seguida)

---

## Apresentação

O Bena é o sistema web que substitui a **Ficha de Controle de Frequência
(FCF)** em papel — preenchida manualmente pelos estagiários do TRE-AC no
formato CIEE — pelo registro digital, com assinatura eletrônica no modelo
do **SEI (Sistema Eletrônico de Informações)**.

O fluxo completo:

1. **Estagiário** bate ponto pelo navegador (entrada/saída).
2. Vê a **folha mensal** com horas calculadas, fins de semana e feriados
   já classificados, e adiciona observações em dias específicos.
3. No fim do mês, **assina digitalmente** sua folha — hash SHA-256 do
   conteúdo + carimbo de tempo, sem necessidade de certificado físico.
4. **Supervisor** contra-assina pelo sistema.
5. **RH** baixa o PDF assinado e anexa direto no processo SEI.

**O que o sistema entrega de valor:**

- **Tempo de volta para a equipe.** Estagiários e supervisores deixam de
  perder horas todo mês com a "novela" da folha de papel.
- **Rastreabilidade.** Cada ponto, cada assinatura, cada edição fica
  registrado em base auditável (Oracle institucional).
- **Integridade.** Folha alterada após assinatura é detectada
  automaticamente (hash diverge → badge "alterada").
- **Continuidade.** Mesmo que feriados sejam editados meses depois, o
  sistema avisa quais folhas perdem a validade do hash.

**Quem usa:**

| Grupo | Acesso | Função |
|---|---|---|
| `estagiarios` | grupo `'E'` | Bater ponto, ver folha, assinar |
| `supervisores` | grupo `'S'` | Ver folhas dos seus estagiários e contra-assinar |
| `admin` | grupo `'0'` | Cadastrar estagiários, gerenciar feriados, baixar PDFs |

A separação dos grupos é feita pelo **Authelia** em produção (LDAP/AD do
tribunal + 2FA). Em desenvolvimento, simulada via `.env` e a rota
`/_dev/sessao`.

---

## Por que "Bena"

**Bena** é uma palavra em **Hãtxa Kuĩ**, língua do povo **Huni Kuin**, que
vive no Acre. Significa **"novo"**.

Vem da expressão **Xinã Bena** — *"novo tempo"* — usada pelos Huni Kuin
para falar do momento atual de renovação cultural. É o nome certo para o
sistema que substitui a folha de ponto em papel pelo registro digital.

O sistema nasceu de uma observação simples: **se a entrega da folha de
ponto vira uma pequena novela mensal — com adiamento, promessa de oração
e reza pelo bom senso de quem vai entregar por último — talvez o
problema não seja quem assina. Seja a folha.**

O Bena resolve isso de uma vez, para que estagiários e supervisores
possam investir o tempo onde ele rende: no aprendizado e no trabalho que
importa.

---

## História do projeto

### O feriadão

O Bena foi escrito em um único feriadão. Três dias, mais ou menos 17
horas por dia — **51 horas no total** —, começando em **1º de Maio**.
Sim: um sistema para tirar a folha de papel das mãos dos trabalhadores
foi escrito justamente no Dia do Trabalhador. A ironia é parte do
projeto.

O combustível foi uma dose generosa de teimosia: alguns comentários
irônicos de colegas estagiários sobre a tal "novela mensal" da folha de
ponto serviram de empurrão. O Bena não chega nem perto da complexidade
do **Git**, guardadas as devidíssimas proporções, mas a indignação foi
parecida com a do Linus Torvalds quando escreveu o dele e, no primeiro
commit, batizou o projeto de *"information manager from hell"*.

A pessoa por trás do projeto — **Lucas Alefe** — é neurodivergente,
convive com o que se chama de hiperfoco, esse traço de cair de cabeça em
tarefas que ama. Engenharia de software entra exatamente nessa lista.
No fim, foi mais fácil escrever o sistema do que continuar reclamando
dele.

### Quem ensinou

O Bena existe porque um conjunto de servidores e colegas do TRE-AC
ensinou a amar essa profissão. Um trecho que vale a citação direta da
tela "Sobre" do sistema:

> O TRE-AC, e em especial a **STI**, foi quem me ensinou a amar essa
> profissão. Em quase dois anos de estágio aprendi com gente que faz
> esse setor existir todo dia, e devo este sistema, e muito do que sei,
> a essas pessoas.
>
> Ao **mestre Edcley**, que sempre acreditou no meu potencial e me
> entregou projetos desafiadores que me fizeram crescer. Engenheiro de
> raiz, daqueles que preferem construir tudo do zero, bem no estilo do
> próprio Linus Torvalds, que gostava de resumir: *"não sou um
> visionário, sou um engenheiro"*. Menos plano de cinco anos, mais
> código rodando.
>
> Ao **Lucir**, um dos primeiros analistas de TI do tribunal, que
> também apostou em mim e me passou ideias "malucas". Ao **Keith**,
> também um dos servidores mais antigos da casa, que conduziu comigo a
> análise de requisitos e me ajudou a entender o domínio do começo ao
> fim. Ao **Bortoli**, talvez o amigo com mais anos de TI em toda a
> Justiça Eleitoral, e referência viva.
>
> À paciência do **Ilis**, que insistiu em me ensinar algumas tarefas no
> Plone. E aos colegas que chegaram um pouco depois e viraram parceria
> de verdade — **Felipe, Thallys, Jair e Jonatan** — que abriram espaço
> para eu contribuir no projeto AGRECOM cuidando de todo o módulo de
> relatórios.

### Para quem vier depois

A motivação de design é **MonolithFirst** (Martin Fowler):

> *"Você não deveria começar um novo projeto com microserviços, nem que
> tenha certeza de que a aplicação vai ficar grande o bastante para
> justificar."*

O Bena começou pequeno, monolítico, propositalmente simples, para que
quem herdar o sistema encontre um terreno entendível e só evolua a
complexidade quando o domínio realmente pedir. Nada de microserviço
prematuro só para parecer moderno.

O que prevalece: o foco no que importa — **aprender e evoluir naquilo
a que cada um se propôs**, seja na engenharia de software, no suporte
ou na área jurídica. Burocracia administrativa não pode roubar esse
tempo.

---

## Arquitetura

### Stack

- **PHP 8.2** com `declare(strict_types=1)` e tipagem em tudo.
- **Laravel 11** como framework.
- **Oracle 19c+** (institucional) em produção; **Oracle 23ai (slim-faststart)**
  em desenvolvimento via Docker; **SQLite in-memory** em testes.
- **Authelia** como SSO + 2FA, com forward-auth pelo reverse proxy
  (Traefik em dev, nginx + traefik em prod).
- **gov.br Design System v3** com tema **TRE-AC** (paleta navy `#003366`
  sobrescrevendo as cores primárias do gov.br).
- **DomPDF** para geração do PDF da folha mensal (sem binário externo).
- **Redis** para cache e sessões.
- **Docker / Docker Compose** em dev, CI e produção.

### Camadas

```
HTTP request
    ↓
Reverse proxy (Authelia forward-auth em prod; passthrough em dev)
    ↓
Middleware Laravel
    ├─ ConfigureUserSession        → resolve Estagiario, popula sessão, gate admin-only
    ├─ EnsureOnboarded             → redireciona para /bem-vindo se tutorial_visto_em IS NULL
    └─ EnsureNotProduction         → bloqueia rotas /_dev em prod
    ↓
Controller (HTTP fino)
    ↓
Service (lógica de domínio)
    ↓
Eloquent Model
    ↓
Oracle (prod / dev) · SQLite (testes)
```

**Princípio:** controllers orquestram (validam input, chamam services,
escolhem view); services contêm a lógica de negócio; models só
relacionamentos, casts e scopes simples.

### Modelos do domínio

| Modelo | Tabela | O que representa |
|---|---|---|
| `Estagiario` | `estagiarios` | Pessoa cadastrada (todo usuário do sistema, mesmo admin/supervisor, é um `Estagiario` — o que muda é o grupo). |
| `Frequencia` | `frequencias` | Um dia de ponto: data, entrada, saída, horas, observação, marca de auto-fechamento. |
| `Feriado` | `feriados` | Data não útil: data, descrição, tipo (`nacional` / `estadual` / `municipal` / `recesso`), UF, recorrente. |
| `Assinatura` | `assinaturas` | Carimbo da folha mensal: papel (`estagiario`/`supervisor`), hash, snapshot, timestamp, IP, e (se substituída) `substituida_em`. |

### Services principais

| Service | Responsabilidade |
|---|---|
| `PontoService` | Bater entrada/saída com validações (não bate em fds/feriado, não duplica). |
| `CalendarioService` | Determina `ehDiaUtil` / `ehFeriado` / `feriadosDoAno`. |
| `FolhaMensalService` | Monta a `FolhaMensal` (DTO com lista de `DiaFolha`). |
| `PdfFolhaMensalService` | Renderiza a folha em PDF no layout CIEE via DomPDF. |
| `AssinaturaService` | Snapshot canônico → SHA-256 → grava `Assinatura`; verifica integridade; suporta re-assinatura. |
| `DashboardService` | DTO `DashboardData` para a home do estagiário. |
| `DashboardAdminService` | DTO `DashboardAdminLinha[]` para o `/admin` (1 linha por estagiário no mês). |
| `BuddyService` | Sistema de mascotes (atribuição, montagem da frase, boas-vindas). |

### Convenções de nomenclatura

- **Português** para domínio: `Estagiario`, `Frequencia`, `Feriado`,
  `Assinatura`, `bater()`, `assinar()`, `lotacao`, `feriadosDoAno()`.
- **Inglês** para infra/Laravel: `Controller`, `Service`, `Middleware`,
  `Migration`, `Factory`.
- **Não misturar** no mesmo identificador (`StagiarioController` ❌
  vs. `EstagiarioController` ✅).

---

## Domínio e fluxos

### Atores

- **Estagiário** (grupo `'E'`) — bate o próprio ponto, assina sua folha.
- **Supervisor** (grupo `'S'`) — vê folhas dos estagiários sob sua
  responsabilidade (campo `supervisor_username`), contra-assina.
- **Admin / RH** (grupo `'0'`) — cadastra estagiários, gerencia
  feriados, baixa PDFs assinados para anexar no SEI.

A separação é feita pelo **Authelia** (LDAP/AD do tribunal). Em
desenvolvimento, simulada por `AUTHELIA_DEV_BYPASS=true` no `.env` +
`/_dev/sessao` para alternar usuário em runtime.

### Fluxo principal: do ponto à folha assinada

```
[1] Estagiário faz login (Authelia + 2FA em prod)
    ↓
[2] Vê a dashboard com mascote, status do dia, horas do mês, dias batidos
    ↓
[3] Bate entrada (botão na home ou em /ponto/entrada)
    ↓ (5h depois, ou manualmente)
[4] Bate saída (sistema calcula horas)
    └─ Esqueceu? Job diário às 00:05 fecha ponto aberto: saída = entrada + horas_diarias, marca como auto
    ↓ (no fim do mês)
[5] Estagiário abre /frequencia, vê folha, adiciona observações em dias específicos
    ↓
[6] Estagiário ASSINA a folha (hash do snapshot canônico + carimbo de tempo)
    ↓
[7] Supervisor responsável vê em /supervisor, abre a folha, CONTRA-ASSINA
    ↓
[8] Folha aparece como "liberada para RH" no /admin
    ↓
[9] Admin baixa o PDF assinado, anexa no processo SEI
```

### Detecção de adulteração

- Cada `Assinatura` guarda o **snapshot completo** (JSON canônico) +
  o **hash SHA-256** desse snapshot.
- A verificação re-calcula o hash a partir do snapshot atual e compara
  com o gravado.
- Se algum registro de `Frequencia` muda depois da assinatura, o hash
  diverge → folha exibe badge **"⚠ alterada"** e oferece **"Re-assinar
  versão atual"** (cria nova `Assinatura` e marca a anterior como
  `substituida_em`).

### Calendário e feriados

- `/calendario` (todos os grupos auth) renderiza o **mês atual** por
  padrão; navegável para outros meses via prev/next.
- Cada mês tem paleta RGB própria (`--cal-theme`), feriado em âmbar
  com dot dourado e tooltip CSS de hover (descrição), hoje com border
  navy.
- Para **admin**, clique em dia vazio abre `<dialog>` com form de criar
  feriado. POST aproveita `FeriadoController::store` com whitelist
  defensiva no `redirect_to` (regex `^/calendario(/|\?|$)`).
- Inserir / editar / excluir feriados continuam **admin-only** via
  `ConfigureUserSession::adminOnlyRouteNames`. A view de listagem
  `/admin/feriados` foi removida — a entrada é exclusivamente o
  calendário.

---

## Funcionalidades entregues

A entrega foi organizada em **sprints** (ver [`SPRINTS.md`](../SPRINTS.md)).
Aqui um resumo do que está em produção do ponto de vista do usuário.

### Estagiário

- Bater entrada e saída pelo navegador, com validações
  (não bate em fds/feriado, não duplica, jornada ≤ 19h por design).
- Tela de sucesso amigável após bater (com mascote).
- Folha mensal com horas calculadas, classificação automática de dias,
  observações por dia.
- Geração de PDF no layout oficial da FCF/CIEE.
- Assinatura digital ao final do mês (modelo SEI).
- Re-assinatura quando a folha foi alterada após a assinatura inicial.
- Auto-fechamento de ponto esquecido (saída = entrada + `horas_diarias`,
  marcada como `auto`, indicador visual na folha e no PDF).
- Onboarding no primeiro acesso (`/bem-vindo`) com tutorial,
  apresentação do mascote e link para a galeria de mascotes.
- Mascote pessoal (buddy) na dashboard com frase contextual diária.

### Supervisor

- Tela `/supervisor` com lista dos estagiários sob sua responsabilidade.
- Acesso à folha de cada um e botão de contra-assinatura.
- Re-contra-assinatura quando a versão atual diverge.

### Admin / RH

- Dashboard `/admin` com todos os estagiários ativos do mês, filtros
  por lotação/ano/mês, busca client-side por nome/lotação, indicadores
  de assinaturas e link de download do PDF (quando liberado).
- Cadastro/edição de estagiários (CRUD com upload de contrato PDF +
  campo `supervisor_username` para vincular ao supervisor responsável).
- Gerenciamento de feriados pelo calendário interativo (clique em dia
  vazio = adicionar; clique em feriado = editar/remover).
- Aviso ao remover feriado: número de folhas assinadas no mês cujo
  hash será invalidado.
- Download do contrato PDF de qualquer estagiário sob autorização.

### Histórias-chave

| ID | Título | Status |
|---|---|---|
| H1–H4 | Login + bater ponto + dashboard | ✅ |
| H5–H6 | Folha mensal + navegação entre meses | ✅ |
| H7 | Cálculo correto com viradas | ✅ (turnos noturnos descartados por YAGNI) |
| H8–H10 | CRUD de feriados | ✅ |
| H11 | PDF da folha | ✅ |
| H12–H13 | Assinatura e contra-assinatura | ✅ |
| H14–H16 | Visão admin (dashboard + cadastros) | ✅ |
| H17 | Observações por dia | ✅ |
| H18 | Verificação de integridade da assinatura (diff inline) | ✅ |
| H19 | Auditoria de ações (tabela append-only + tela /admin/auditoria) | ✅ |
| H20 | RH baixa o PDF assinado | ✅ |
| H21 | Auto-fechamento de ponto esquecido | ✅ |
| H22 | Re-assinatura da versão atual | ✅ |
| H23 | Onboarding no primeiro acesso | ✅ |
| H24 | Auto-submit nos filtros admin | ✅ |
| H25 | Busca client-side nas tabelas | ✅ |
| H26 | Calendário visual com mapa de calor | ✅ |
| H27 | Link calendário na nav e dashboard | ✅ |
| H28 | Sistema buddy / mascote | ✅ |

Detalhes de critérios de aceitação em [`REQUISITOS.md`](../REQUISITOS.md).

---

## Sistema Buddy — mascotes

O Bena tem **mascotes**. Cada usuário recebe um mascote sorteado no
primeiro acesso, persistido em `estagiarios.buddy_tipo`.

### Por que isso existe

Sistemas internos de tribunal historicamente são frios. O Bena nasceu
com o objetivo declarado de **devolver tempo às pessoas** — e parte
disso é não ser um sistema que dá mais trabalho mental do que precisa.
Os mascotes acolhem o usuário, comentam o dia com bom humor e dão um
mínimo de personalidade ao registro de ponto. Cada um traz uma
referência local: o Acre, a Justiça Eleitoral, as urnas que viajam de
avião pelas zonas remotas, o ribeirinho que vê a urna chegar.

### Pools

Existem **dois pools de mascotes**, com personalidades adequadas ao
papel do usuário no sistema:

- **Pool padrão** — atribuído ao grupo `estagiarios`. 8 mascotes com
  personalidades variadas: do empolgado ao preguiçoso, do zen ao
  fofoqueiro.
- **Pool sênior** — atribuído ao grupo `supervisores` e `admin`.
  4 mascotes em tom de mentoria, mais maduros, com voz de quem já
  acompanhou muito ciclo eleitoral.

A escolha é aleatória dentro do pool e **permanente** — você adota o
seu mascote na primeira visita à dashboard ou ao `/bem-vindo`.

### Pool padrão (estagiários)

| Mascote | Personalidade | História |
|---|---|---|
| 🦉 **Coruinha** | Sábia, formal | Foi vista pela primeira vez na sala de apuração de 1996, quando a urna eletrônica chegou ao Acre. Estudou cada protocolo enquanto os servidores conferiam atas. Hoje observa, em silêncio, cada folha de ponto — como observou cada boletim. |
| 🐱 **Miau** | Preguiçoso, cético | Apareceu numa zona eleitoral do interior, dormindo em cima de uma pilha de boletins entre dois pleitos. Adotou o tribunal porque nada por ali tem urgência fora de eleição. Estagia, mas com prioridades claras — a soneca vem antes da apuração. |
| 🐶 **Totó** | Empolgado, afetuoso | Cresceu correndo entre caixas de seção eleitoral durante o transporte das urnas pelo Acre. Dá um latido por cada carga despachada — já passou de 200, sem perder a contagem. Tudo motivo de festa: a urna chegou! O caminhão saiu! VIVA A DEMOCRACIA!!! |
| 🦫 **Capi** | Zen, contemplativa | Vive nas margens do Rio Acre e assistiu, sem se mexer, à chegada da primeira urna eletrônica numa cidade ribeirinha. *"Tudo flui — voto, rio, tempo."* Aprendeu a contar votos só observando os fiscais; em Bena, observa o ponto. |
| 🦜 **Louro** | Fofoqueiro, antenado | Morava na sala dos juízes eleitorais de uma comarca antiga do interior. Aprendeu a dizer *"ata aberta"* e *"ata fechada"* antes de aprender a falar *"oi"*. Tudo que o tribunal cochicha, ele repete — mas só do tipo que não vira problema. |
| 🐢 **Lenta** | Devagar, café-dependente | Atravessou a Praça do TRE ao longo de três eleições. Quando chegou, seu próprio estágio já tinha começado e terminado três vezes. Mas revisa folhas com mais calma que qualquer auditor — devagar e detalhista. |
| 🐧 **Pingu** | Profissional, pontual | Chegou ao TRE-AC de gravata. Ninguém soube explicar de onde. Foi visto pela primeira vez na sala da Presidência durante a transição de um pleito. Desde então, organiza pautas e nunca atrasou uma sessão. |
| 🐸 **Sapão** | Descolado, parceiro | Veio das margens do Bacurau, depois de uma operação de logística reversa pós-apuração. Subiu numa urna que voltava pra estoque e ficou. *"Mano, o tribunal é firmeza, eu fico."* Hoje é o mais informal do quadro — mas na hora H, entrega. |

### Pool sênior (supervisores e admin)

| Mascote | Personalidade | História |
|---|---|---|
| 🦅 **Águia** | Estrategista, visão panorâmica | Voou sobre a Amazônia acompanhando o transporte aéreo das urnas eletrônicas para as zonas mais remotas do Acre. Conhece cada pista de pouso, cada ribeirão. Em Bena, ela vê a operação inteira do alto — antes que alguém perceba que algo saiu do plano. |
| 🦁 **Leão** | Mentor calmo, autoridade | Liderou as primeiras equipes de mesários do Acre nas eleições municipais dos anos 80. Não precisava falar alto — a presença bastava. Hoje acompanha quem lidera estagiários e ensina que comando se exerce com calma, não com volume. |
| 🐘 **Elefon** | Memória prodigiosa, experiente | Acompanhou cada eleição do TRE-AC desde a fundação. Lembra do nome de cada juiz eleitoral, de cada presidente, de cada plebiscito. *"Quem não esquece, não repete erros."* É o registro vivo do que funcionou — e do que não. |
| 🐻 **Urso** | Protetor, paciente | Vigiou a primeira urna eletrônica que chegou em uma comunidade indígena no Acre. Sentou na porta do galpão e ninguém passou sem ele acompanhar. Sereno, presente, paciente. Em Bena, cuida da equipe como cuidou daquele galpão. |

### Como funciona — mecânica

```
┌──────────────────────────┐
│ Primeiro acesso (qualquer│
│ rota com 'configure.     │
│ session' middleware)     │
└──────────────┬───────────┘
               │
               ▼
   garantirBuddy(Estagiario, ?string $grupo)
               │
       buddy_tipo IS NULL?
               │
        ┌──────┴──────┐
       NÃO            SIM
        │              │
        ▼              ▼
    return    Pool baseado no grupo:
              ├─ '0','S' → tipos_supervisores
              └─ default → tipos
                       │
                       ▼
              array_rand → atribui → save
```

A **frase exibida** depende do contexto:

- Na **dashboard** (`montar()`): chave é `(buddy, dia_da_semana, status_ponto)`.
  Status pode ser `aguardando_entrada` / `em_andamento` / `concluido`.
  A escolha é determinística por `(dia_do_mês + bloco_de_12h)`, então
  a frase é estável dentro do bloco (manhã 0-11h ou tarde 12-23h) e
  varia ao longo do dia/semana.
- Na tela `/bem-vindo` (`boasVindas()`): array dedicado `boas_vindas`
  por buddy, escolha aleatória.
- Quando não há frase para o contexto: cai no array `generica` do buddy.

### Frases (~150) com tema da Justiça Eleitoral

Cada combinação (buddy × dia × status) tem ao menos uma frase, e todas
foram **costuradas com vocabulário e metáforas eleitorais** mantendo a
personalidade individual de cada mascote: urna, ata, apuração, pleito,
mesa receptora, BU (boletim de urna), mesário, recurso, diplomação,
ciclo eleitoral, fiscalização.

Algumas amostras por buddy:

> **Coruinha (segunda, aguardando entrada):** *"Prezado(a), o pleito da
> semana se inicia. Bata o ponto e cumpra prazos com diligência. 📚"*

> **Miau (sexta, aguardando entrada):** *"Sexta. Acordei só pra ver você
> bater o ponto. Vou voltar a hibernar até o próximo pleito. 😼"*

> **Totó (sexta, concluído):** *"SEXTA LACRADA!!! Bom trabalho!!! FINAL
> DE SEMANA INAUGUROU!!! 🎉🐕"*

> **Águia (quarta, aguardando entrada):** *"Meio da semana. Reavalie
> prioridades — como antes de fechar uma zona eleitoral."*

> **Elefon (terça, aguardando entrada):** *"Terça-feira. Lembro de uma
> terça em 2018, antes do pleito municipal — muito parecida com essa."*

### Onde aparece

| Tela | Visível para | Variante |
|---|---|---|
| Dashboard `/` | Apenas grupo `'E'` | `bena-buddy-card` (compacto, frase do dia) |
| Onboarding `/bem-vindo` | Todos os grupos | `bena-buddy-card--apresentacao` (avatar maior, rodapé explicativo) |
| Galeria `/mascotes` | Todos os grupos | Cards padrão (pool padrão) e âmbar (pool sênior) com personalidade + história |

**Visibilidade por contexto** — o card do mascote aparece na dashboard
**apenas para o grupo `'E'`**, mas a apresentação no onboarding e a
galeria são abertas a todos os grupos. Admin/supervisor recebem buddy
do pool sênior e o veem na própria onboarding e galeria.

### Acessibilidade

- A animação de entrada e o bounce do avatar respeitam
  `prefers-reduced-motion: reduce` (CSS desliga as animações para
  usuários com sensibilidade vestibular).
- Avatar marcado com `aria-hidden="true"` (decorativo); a personalidade
  e a frase são lidas pelo screen reader.
- Cards de mascote na galeria são `<article>` semânticos.

---

## Setup e operação

### Subir local em 30 segundos

```bash
cp .env.example .env
make bootstrap   # build + up + composer install + key:generate + migrate
```

App em `https://ponto.localhost`. **Não precisa mexer em `/etc/hosts`**:
Chrome/Firefox/Edge resolvem `*.localhost` para 127.0.0.1 (RFC 6761).

### Comandos do dia a dia

```bash
make help            # lista tudo
make up              # sobe stack
make down            # derruba (preserva volumes)
make logs            # tail dos containers
make shell           # bash no container app

make test            # roda suíte (sqlite in-memory, ~60s)
make pint            # auto-fix style
make check           # pint --test && test  (rodar antes de cada commit)

make migrate         # aplica migrations no oracle dev
make fresh           # drop + migrate + seed (apaga dados)

make ci              # simula pipeline de CI localmente
```

### Trocar usuário simulado em runtime (dev)

`https://ponto.localhost/_dev/sessao` permite alternar
username/grupos sem reiniciar o container. Útil para testar cada papel.

Detalhes em [`docs/dev-sessao.md`](./dev-sessao.md).

---

## Decisões arquiteturais

Decisões já tomadas. Mudanças aqui exigem justificativa explícita no PR.

### Banco

- **Oracle, não Postgres/MySQL.** Restrição institucional do tribunal.
- **Oracle no compose APENAS para dev.** Em CI usa SQLite in-memory; em
  produção, conecta no Oracle externo do tribunal.
- **SQLite in-memory para testes.** Migrations são compatíveis com
  ambos. Trade-off aceito: testes rápidos > paridade total de banco.
- **Cuidados específicos do Oracle:** sem tipo `TIME` (usar `string(8)`
  HH:MM:SS + Attribute no model); identifiers ≤ 30 chars; `Rule::unique`
  em coluna data falha com cast `date` puro — usar `whereDate` em
  closure de validação.

### Autenticação

- **Authelia em produção, simulação em dev.** Em prod, o tribunal usa
  Authelia atrás do Traefik (LDAP/AD), e ele injeta os headers
  `Remote-*`. Em dev **NÃO** subimos Authelia: `AUTHELIA_DEV_BYPASS=true`
  simula os headers via `.env`, e a rota `/_dev/sessao` permite trocar
  usuário/grupo simulado em runtime sem reiniciar.
- A escolha é prática: rodar Authelia local exige TOTP, hashes argon2,
  configuração especial de cookie domain — não vale o ROI para iterar.

### Assinatura

- **Hash + carimbo de tempo, não ICP-Brasil.** Por enquanto. Authelia
  + 2FA dão segurança suficiente para uso interno. Slot pronto para
  trocar por PAdES no `AssinaturaService`.
- Snapshot canônico: campos significativos, ordem fixa, sem timestamps
  internos, JSON UTF-8.
- Verificação determinística: re-hash a partir do snapshot atual e
  compara com o gravado.

### UI / DS

- **gov.br Design System v3 (com tema TRE-AC), não AdminLTE/Tailwind.**
  Padrão obrigatório para sistemas do governo federal e seguido pelos
  tribunais. Tokens primários sobrescritos em
  `public/css/tre-ac-theme.css` para usar a navy institucional do
  TRE-AC (`#003366`) em vez do azul gov.br (`#1351b4`).
- Carregamos o gov.br DS via CDN (jsdelivr) — quando precisarmos de
  bundle (JS dos componentes dropdown/modal), troca-se pelo
  `@govbr-ds/core` via npm + Vite.
- **CSS namespaced co-localizado** quando faz sentido: `bena-form-*` e
  `bena-buddy-*` ficam no `<style>` do layout (visíveis em qualquer
  view); `bena-cal-*`, `bena-mes-*`, `bena-mascotes-*` ficam em
  `@push('styles')` na própria view (escopo local).

### PDF

- **DomPDF, não Snappy/wkhtmltopdf.** PHP puro, não exige binário
  externo, suficiente para layout da FCF.

### Docker

- **Docker para dev, CI e produção.** Mesma stack em todos os
  ambientes; "funciona na minha máquina" deixa de existir.
- Em dev, código montado como volume (`./:/var/www/html`); em prod,
  copiado dentro da imagem.
- Multi-stage build: dev tem Xdebug, prod não.
- Imagens de prod < 300 MB; rodam como UID 1000 (`www-data`), não root.

### Workflow

- **TDD estrito.** Toda mudança de comportamento começa por um teste
  que falha (Red → Green → Refactor). Inclusive bugfixes — o teste
  de regressão vem antes do fix.
- **YAGNI.** Não implementar nada sem história/critério.
- **Simple design** (Kent Beck, em ordem): passa em todos os testes;
  revela a intenção; não tem duplicação; tem o menor número de
  elementos.
- **Iterações curtas** (1 semana). Cada sprint = 3–5 histórias.
- **CI sempre verde.** Nunca subir para `main` com a barra vermelha.

---

## Estrutura de diretórios

```
.
├── README.md                       # Setup e visão básica
├── CLAUDE.md                       # Convenções e workflow (LEIA antes de codar)
├── REQUISITOS.md                   # Histórias e critérios de aceitação
├── SPRINTS.md                      # Plano de execução
├── STATUS.md                       # Handoff entre sessões
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # FeriadoController, EstagiarioController, DashboardController
│   │   │   ├── AssinaturaController.php
│   │   │   ├── CalendarioAnualController.php   # /calendario, /calendario/{ano}/{mes}
│   │   │   ├── DashboardController.php          # / (estagiário)
│   │   │   ├── DevSessionController.php         # /_dev/sessao (apenas em dev)
│   │   │   ├── FolhaMensalController.php        # /frequencia*
│   │   │   ├── MascotesController.php           # /mascotes
│   │   │   ├── ObservacaoController.php
│   │   │   ├── OnboardingController.php          # /bem-vindo
│   │   │   ├── PontoController.php               # /ponto/entrada, /ponto/saida
│   │   │   └── SupervisorDashboardController.php # /supervisor
│   │   └── Middleware/
│   │       ├── ConfigureUserSession.php  # resolve user, popula sessão, gate admin-only
│   │       ├── EnsureOnboarded.php       # redireciona quem não viu tutorial
│   │       └── EnsureNotProduction.php   # bloqueia /_dev em prod
│   ├── Models/
│   │   ├── Assinatura.php
│   │   ├── Estagiario.php   # Authenticatable
│   │   ├── Feriado.php
│   │   └── Frequencia.php
│   └── Services/
│       ├── AssinaturaService.php
│       ├── BuddyData.php / BuddyService.php
│       ├── CalendarioService.php
│       ├── DashboardData.php / DashboardService.php
│       ├── DashboardAdminLinha.php / DashboardAdminService.php
│       ├── DiaFolha.php
│       ├── FolhaMensal.php / FolhaMensalService.php
│       ├── PdfFolhaMensalService.php
│       └── PontoService.php
│
├── config/
│   ├── authelia.php       # dev_bypass + dev_user/groups
│   ├── buddies.php        # 12 mascotes (perfis + ~150 frases + histórias)
│   └── oracle.php         # NLS_* (publicado do yajra)
│
├── database/
│   ├── factories/         # EstagiarioFactory (com states inativo, admin, semOnboarding, comBuddy)
│   ├── migrations/
│   └── seeders/
│
├── docker/
│   ├── app/               # Dockerfile multi-stage, php.ini, entrypoint.sh
│   ├── nginx/
│   ├── authelia/          # config.yml, users_database.yml (só dev fictício)
│   ├── traefik/
│   └── oracle/init/       # SQL idempotente de bootstrap do schema
│
├── docker-compose.yml      # dev (default)
├── docker-compose.prod.yml # overrides para produção
├── docker-compose.test.yml # overrides para CI/testes
├── Makefile
│
├── docs/
│   ├── dev-sessao.md         # como trocar usuário simulado em runtime
│   ├── identidade-visual.md  # paleta TRE-AC + tokens
│   └── visao-geral.md        # este documento
│
├── public/
│   ├── css/tre-ac-theme.css  # override de tokens primários
│   └── img/bena.png          # logo
│
├── resources/views/
│   ├── layouts/app.blade.php          # gov.br DS + tema TRE-AC + CSS bena-* compartilhado
│   ├── admin/                         # dashboard, estagiarios/{index,edit}, feriados/{create,edit,confirm-destroy}
│   ├── calendario/mes.blade.php       # mês interativo (todos os grupos)
│   ├── frequencia/{show,pdf}.blade.php
│   ├── mascotes/index.blade.php       # galeria de mascotes
│   ├── onboarding/show.blade.php      # tela "Sobre" / boas-vindas
│   ├── ponto/sucesso.blade.php
│   ├── supervisor/dashboard.blade.php
│   ├── dashboard.blade.php            # home estagiário
│   └── dev/sessao.blade.php           # form do /_dev/sessao
│
├── routes/web.php
└── tests/
    ├── Feature/                # 22 arquivos cobrindo HTTP + integração
    │   ├── Admin/              # FeriadoCadastro, FeriadoEdicao, EstagiarioListagem, etc.
    │   ├── BuddyDashboardTest.php
    │   ├── CalendarioAnualTest.php
    │   ├── MascotesPageTest.php
    │   ├── OnboardingTest.php
    │   └── ...
    └── Unit/                   # services e DTOs
        └── Services/           # AssinaturaService, BuddyService, CalendarioService, etc.
```

### Cobertura de testes

- **278 testes / 671 asserções**, todos verdes.
- Razão **teste/produção ≈ 2,8 : 1** — coerente com TDD estrito.
- Suíte roda em ~60s (SQLite in-memory).
- Gate de cobertura: ≥ 80% global (`--min=80`).

---

## O que vem em seguida

### Sprints abertas

- **Sprint 5** ✅ fechada — todas as histórias de produto entregues.
  Único pendente é o `.gitlab-ci.yml` (H0.3), que depende da migração
  do projeto pro GitLab interno do tribunal.
- **Sprint 6** (Hardening / Homologação): NFRs de segurança, deploy
  via Swarm/K8s, smoke tests E2E, backup automatizado, doc operacional.

### Débito conhecido

- `tests/Feature/Admin/FeriadoEdicaoTest:118` ainda checa string
  literal `"1 folhas"` (pluralização incorreta para 1 feriado).
  Resolver com par teste-correção.
- Pluralização da copy em geral (`"1 folhas assinadas"` → `"1 folha
  assinada"`) — UX correctness.
- `BuddyService` mistura ordenação público/privado nos métodos
  (cosmético).
- `lucas.dev.buddy_tipo='gato'` foi setado via tinker no Oracle de dev
  (dev convenience pessoal — não codificado em seeder).
- `gov.br DS` via CDN — migrar para bundle local quando precisar do JS
  dos componentes (dropdown, modal nativo).
- `phpredis` ausente no Dockerfile — usando `predis` por enquanto.
- Pipeline CI no GitLab interno do tribunal — pendente migração
  programada.

### Slots prontos para evolução

- `AssinaturaService` está isolado: troca para PAdES (ICP-Brasil) sem
  mexer no resto.
- `BuddyService` aceita pools por grupo — adicionar pool novo é
  acrescentar chave em `config/buddies.php`.
- `CalendarioAnualController::renderMes` é pure data → view, fácil de
  migrar para visualizações alternativas (lista, exportável).

---

## Princípio que ancora o projeto

> *"Esta é a marca que quero deixar neste tribunal: um sistema feito
> por um estagiário, para estagiários."*
> — Lucas Alefe (tela "Sobre" do sistema)

Mantenha o foco no que importa: **aprender e evoluir**. Burocracia
administrativa não pode roubar esse tempo. Este lugar é terreno fértil
para quem quer crescer.
