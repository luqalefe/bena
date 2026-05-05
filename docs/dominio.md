# Domínio

Modelos do Bena, campos, invariantes e regras de negócio. Quando este
documento e o código divergem, **o código é a fonte da verdade** —
abra um PR atualizando o doc.

---

## Mapa do domínio

```
                ┌──────────────┐
                │    Setor     │  (sincronizado da API TRE-AC)
                └──────┬───────┘
                       │ 1
                       │
                       │ N
                ┌──────┴───────┐ N      1 ┌──────────────┐
                │  Estagiario  │──────────│  Supervisor  │
                └──┬─────┬─────┘          └──────────────┘
                   │     │
            1      │     │     1
                   │     │
              N    │     │   N
        ┌──────────┘     └──────────┐
        ▼                           ▼
┌──────────────┐           ┌────────────────────┐
│  Frequencia  │           │ RecessoEstagiario  │
└──────────────┘           └────────────────────┘
        │
        │ N            ┌──────────────┐         ┌──────────────┐
        └─────────────▶│  Assinatura  │         │   Feriado    │ (global)
                       └──────────────┘         └──────────────┘

                       ┌──────────────┐
                       │  Auditoria   │ (append-only, não tem FK)
                       └──────────────┘
```

---

## `Estagiario`

`app/Models/Estagiario.php` · tabela `estagiarios` · implementa
`Authenticatable`.

Pessoa cadastrada no sistema. **Todo usuário é um `Estagiario`** —
mesmo admin e supervisor têm linha aqui (criada/atualizada pelo
`ConfigureUserSession` no primeiro acesso). O que muda é o grupo na
sessão.

### Campos

| Campo | Tipo | Origem | Notas |
|---|---|---|---|
| `id` | bigint | auto | |
| `username` | string(100) nullable | Authelia (`Remote-User`) | Único quando preenchido. Pode ser null para estagiário criado por seed sem login conhecido. |
| `nome` | string(200) | CSV ou Authelia | |
| `email` | string(200) nullable | CSV ou Authelia | |
| `matricula` | string(30) nullable | CSV | Ex.: `EST00001`. |
| `setor_id` | bigint nullable | FK `setores.id` | Migrado de coluna string `lotacao` em 2026-05-05. |
| `supervisor_id` | bigint nullable | FK `supervisores.id` | |
| `supervisor_nome` | string(200) nullable | desnormalização | Mantido em sync com `supervisor.nome` para preservar autorização legada. |
| `supervisor_username` | string(100) nullable | desnormalização | Usado pelo `SupervisorDashboardController` para listar "meus estagiários". |
| `sei` | string(50) nullable | CSV | Número do processo SEI da contratação. |
| `instituicao_ensino` | string(200) nullable | CSV | Ex.: `IFAC`. |
| `inicio_estagio` | date nullable | CSV | |
| `fim_estagio` | date nullable | CSV | |
| `prorrogacao_inicio` | date nullable | CSV | Quando estágio é prorrogado. |
| `prorrogacao_fim` | date nullable | CSV | |
| `horas_diarias` | decimal(4,2) | default `5.00` | Jornada contratada. |
| `contrato_path` | string nullable | upload PDF | Caminho relativo no disco `local`. |
| `ativo` | boolean | default `true` | |
| `tutorial_visto_em` | datetime nullable | onboarding | Se `null`, redirecionado pra `/bem-vindo`. |
| `buddy_tipo` | string nullable | sorteio | Mascote pessoal. |
| `created_at`, `updated_at` | timestamps | | |

### Relações

- `setor()` → `BelongsTo<Setor>`.
- `supervisor()` → `BelongsTo<Supervisor>`.
- `recessos()` → `HasMany<RecessoEstagiario>`.
- `frequencias()` é implícito (não está declarado no model atual; usar
  `Frequencia::where('estagiario_id', $estagiario->id)`).
- `assinaturas()` é implícito.

### Authenticatable

Identidade pelo `username` (não pelo `id`):

```php
public function getAuthIdentifierName(): string { return 'username'; }
public function getAuthIdentifier(): mixed { return $this->username; }
```

Não há senha — autenticação é via Authelia. Métodos `getAuthPassword()`
retornam string vazia (não usados).

### Invariantes

- `username` único quando preenchido (`uq_estagiarios_username`).
- `setor_id`, se preenchido, **deve** referenciar setor existente
  (validação `exists:setores,id` no `UpdateEstagiarioRequest`).
- `supervisor_id`, se preenchido, **deve** referenciar supervisor
  existente.
- Tutorial (`tutorial_visto_em`) é setado **apenas** pelo
  `OnboardingController::concluir`. Outras ações que exigem usuário
  "preparado" passam pelo middleware `EnsureOnboarded`.

---

## `Frequencia`

`app/Models/Frequencia.php` · tabela `frequencias`.

Um dia de ponto de um estagiário. Uma linha por estagiário por dia.

### Campos

| Campo | Tipo | Notas |
|---|---|---|
| `estagiario_id` | bigint, FK | |
| `data` | date | |
| `entrada` | string(8) `HH:MM:SS` | Acessor devolve `CarbonImmutable`; mutator aceita string ou Carbon. Oracle não tem TIME — daí o tipo string. |
| `saida` | string(8) `HH:MM:SS` nullable | |
| `horas` | decimal(5,2) nullable | Calculada na hora de bater saída. |
| `observacao` | text nullable | Escrita pelo estagiário (até a assinatura). |
| `ip_entrada` / `ip_saida` | string nullable | Auditoria do endpoint que originou. |
| `saida_automatica` | boolean | `true` quando fechada pelo job `ponto:fechar-abertos`. Mostrada como badge na folha e no PDF. |

### Invariantes

- (estagiario_id, data) é **único** lógico — `PontoService` impede
  duplicar entrada/saída via `whereDate('data', $hoje)->first()`.
- `entrada IS NULL` e `saida NOT NULL` é estado **inválido**
  (controlado pelo service: saída só grava após entrada existir).
- `saida > entrada` (no mesmo dia). Atravessar meia-noite não é
  suportado por design — turnos noturnos foram descartados na história
  H7 (YAGNI). Se aparecer caso real, lance `DomainException` com
  contexto claro.
- Se `saida_automatica = true`, então `horas == estagiario.horas_diarias`
  (invariante mantida pelo job, não pelo schema).

### Regras de negócio (todas no `PontoService`)

1. **Estagiário precisa estar `ativo`**.
2. **Hoje deve estar entre `inicio_estagio` e `fim_estagio`** (vigência).
3. **Hoje não pode estar coberto por `RecessoEstagiario`** do estagiário.
4. **Hoje não pode ser fim de semana ou feriado** (`CalendarioService::ehDiaUtil`).
5. **Não duplicar** entrada/saída no mesmo dia.
6. **Saída > entrada** estritamente.
7. `horas` é arredondada para 2 casas: `round(diffMinutes/60, 2)`.

### Auto-fechamento

`PontoService::fecharPontosAbertos()` roda no schedule diário 00:05:

- Pega frequências de **dias passados** com `entrada NOT NULL` e
  `saida IS NULL`.
- Fecha com `saida = entrada + horas_diarias` do estagiário.
- Marca `saida_automatica = true` e `horas = horas_diarias`.
- Idempotente: dia já fechado não é tocado.

A folha exibe um badge "auto" e o PDF inclui rodapé explicando.

---

## `Feriado`

`app/Models/Feriado.php` · tabela `feriados`.

Data não-útil global. Não pertence a estagiário.

### Campos

| Campo | Tipo | Notas |
|---|---|---|
| `data` | date | Para recorrentes, o ano só importa pra exibição — `feriadosDoAno` remapeia. |
| `descricao` | string | Ex.: `"Independência"`. |
| `tipo` | string | `nacional`, `estadual`, `municipal`, `recesso`. |
| `uf` | string(2) nullable | Quando aplicável (estaduais). |
| `recorrente` | boolean | `true` repete anualmente; `false` é one-shot. |

### Invariantes

- `(data, descricao)` único (lógico) — controlado por validação na
  request, não no schema.
- `recorrente = true` implica que o ano em `data` é só placeholder.

### Regras

- Inclusão / edição / exclusão é **admin-only** (rota
  `admin.feriados.*` no whitelist do `ConfigureUserSession`).
- Inserção é feita pelo **calendário interativo** em `/calendario`:
  admin clica em dia vazio, abre `<dialog>`, submete. O endpoint usa
  `redirect_to` whitelisteado por regex (`^/calendario(/|\?|$)`) pra
  voltar pro calendário.
- Remover feriado **invalida hash** das folhas assinadas naquele mês
  (porque mudaria a classificação dos dias). A confirmDestroy mostra
  o número de folhas afetadas antes da exclusão.

---

## `Assinatura`

`app/Models/Assinatura.php` · tabela `assinaturas`.

Carimbo da folha mensal de um estagiário. Modelo SEI (hash + timestamp,
sem ICP-Brasil).

### Campos

| Campo | Tipo | Notas |
|---|---|---|
| `estagiario_id` | bigint, FK | |
| `ano`, `mes` | int | Mês da folha. |
| `papel` | string | `estagiario` (constante `PAPEL_ESTAGIARIO`) ou `supervisor` (constante `PAPEL_SUPERVISOR`). |
| `assinante_username` | string | `username` de quem assinou (em prod, do Authelia; pode diferir do estagiário titular se admin assinou em nome dele — caso permitido?). |
| `snapshot` | text (JSON) | `canonicalSnapshot()` serializado. |
| `hash` | string(64) | `sha256(snapshot)`. |
| `assinado_em` | datetime | |
| `ip` | string nullable | |
| `substituida_em` | datetime nullable | Se preenchido, esta assinatura foi substituída por uma re-assinatura (não conta como ativa). |

### Invariantes

- Para cada `(estagiario_id, ano, mes, papel)`, **só uma** assinatura
  ativa (`substituida_em IS NULL`).
- `papel` é um dos dois valores constantes — `garantirPapelValido()`.
- Supervisor só pode assinar se estagiário já assinou (`garantirEstagiarioJaAssinou()`).
- `hash` recomputado a partir do `snapshot` deve bater com o gravado.
  Se não bater, é corrupção de dados — investigar.

### Snapshot canônico

Definido em `AssinaturaService::canonicalSnapshot()`:

```json
{
  "estagiario_id": 1,
  "ano": 2026,
  "mes": 5,
  "dias": [
    {
      "data": "2026-05-04",
      "entrada": "09:00:00",
      "saida": "14:00:00",
      "horas": "5.00",
      "observacao": null
    },
    ...
  ]
}
```

- Ordem fixa: `dias[]` ordenado por `data` ASC.
- Sem timestamps internos (`created_at`/`updated_at` ignorados).
- `horas` como string (preserva precisão decimal).
- `JSON_UNESCAPED_UNICODE` para acentos legíveis.

### Detecção de adulteração

```
verificar(estagiario, ano, mes):
  hashAtual = sha256(canonicalSnapshot(estagiario, ano, mes))
  para cada Assinatura ativa do mês:
    integro = (assinatura.hash == hashAtual)
```

Se `integro = false`, a folha mudou após a assinatura. UI mostra badge
**"⚠ alterada"** e botão **"Re-assinar versão atual"**.

### Re-assinatura

`AssinaturaService::reassinar`:

- Marca a assinatura ativa atual com `substituida_em = now()`.
- Cria uma nova assinatura com `hash` da folha atual.
- **Se re-assinatura é como `estagiario`**: também invalida a
  contra-assinatura do supervisor (folha mudou, supervisor precisa
  rever).

### Diff

`AssinaturaService::diff(Assinatura)` produz uma lista de mudanças
entre o `snapshot` gravado e o atual:

```php
[
  ['data' => '2026-05-04', 'tipo' => 'campo_alterado',
   'campo' => 'observacao', 'antes' => null, 'depois' => 'Reunião pleno'],
  ['data' => '2026-05-10', 'tipo' => 'dia_adicionado'],
  ['data' => '2026-05-12', 'tipo' => 'dia_removido'],
]
```

Renderizado inline na folha (H18) pra mostrar exatamente o que mudou.

---

## `Setor`

`app/Models/Setor.php` · tabela `setores`. Sincronizado das APIs TRE-AC
pelo comando `setores:sincronizar`.

### Campos

| Campo | Tipo | Notas |
|---|---|---|
| `sigla` | string(30) | Único. Ex.: `STI`, `SDBD`, `9ª ZE`. |
| `quantidade_servidores` | int nullable | Da API `/lotacao/`. `null` quando setor está em `/unidades/` mas sem ninguém. |
| `ativo` | boolean | `false` quando sumiu da API; reativa se voltar. |
| `sincronizado_em` | datetime nullable | Última vez que o sync tocou neste registro. |

### Scope

`Setor::ativos()` — `where('ativo', true)`.

### Invariantes

- `sigla` única (`uq_setores_sigla`).
- `sincronizado_em` é setado pelo comando, não pela app.
- Setor inativo (`ativo=false`) **continua referenciado** por
  estagiários antigos — não apagamos pra preservar histórico. Form de
  edição mostra apenas `ativos()`.

### Hierarquia

Não há campo `setor_pai_id`. A hierarquia entre setores é **implícita**
na API `/ferias/setor/?sigla=XXX`: passar a sigla pai (ex.: `STI`)
retorna servidores cujos `SIGLA_UNID_TSE` são os filhos (`SDBD`, `CIE`,
etc.). Quando precisarmos materializar isso (ex.: pra listar todos os
estagiários da STI somando todos os filhos), provavelmente vamos
adicionar uma tabela `setor_filiacao` ou um campo `setor_pai_id`
populado por inferência. Não faz parte do escopo atual.

---

## `Supervisor`

`app/Models/Supervisor.php` · tabela `supervisores`.

Servidor responsável por contra-assinar folha de estagiário(s).

### Campos

| Campo | Tipo | Notas |
|---|---|---|
| `nome` | string | |
| `username` | string nullable | Quando conhecido. Usado pra cruzar com `Remote-User` do Authelia (mesmo login). |
| `email` | string nullable | |
| `lotacao` | string(100) nullable | **Ainda string livre** — não migrou pra FK porque a integração de "qualquer servidor pode ser supervisor" foi adiada. |
| `ativo` | boolean | |

### Relações

- `estagiarios()` → `HasMany<Estagiario>` (via `supervisor_id`).

### Invariantes

- `nome` é a chave natural (CSV usa `firstOrCreate` por nome).
- `username`, quando preenchido, deve bater com `Remote-User` do
  Authelia para a autorização funcionar
  (`SupervisorDashboardController` lista por `supervisor_username`).

### Por que `lotacao` ainda é string

Decisão consciente. A integração com a API de servidores do TRE-AC
para resolver supervisor por matrícula/username está em backlog. Por
enquanto, supervisor é cadastrado manualmente pelo admin (CRUD em
`/admin/supervisores`) com lotação digitada. Quando a feature entrar,
trocaremos pra `setor_id` (FK) seguindo o mesmo padrão usado em
`Estagiario`.

---

## `RecessoEstagiario`

`app/Models/RecessoEstagiario.php` · tabela `recessos_estagiario`.

Janela de recesso anual (Lei 11.788, art. 13: 30 dias a cada 12 meses
de estágio quando contratado por mais de 1 ano).

### Campos

| Campo | Tipo | Notas |
|---|---|---|
| `estagiario_id` | bigint, FK | |
| `inicio` | date | |
| `fim` | date | |
| `observacao` | text nullable | Ex.: "Recesso de fim de ano". |

### Relações

- `estagiario()` → `BelongsTo<Estagiario>`.

### Invariantes

- `inicio <= fim`.
- Não há restrição contra sobreposição entre recessos do mesmo
  estagiário (cabe ao admin não criar lixo). Se virar problema, adicionar
  validação no `StoreRecessoEstagiarioRequest`.

### Efeitos

- `PontoService::garantirNaoEmRecesso()` impede bater ponto durante
  recesso ativo (qualquer recesso com `inicio <= hoje <= fim`).
- `ConformidadeService::semRecesso()` zera o alerta `sem_recesso` se
  existe pelo menos um recesso com `inicio` no último ano (recesso
  agendado pra futuro **não silencia** o alerta).

---

## `Auditoria`

`app/Models/Auditoria.php` · tabela `auditoria`. Append-only.

### Campos

| Campo | Tipo | Notas |
|---|---|---|
| `usuario_username` | string | Quem realizou a ação. |
| `acao` | string | Ex.: `estagiario.editar`, `feriado.remover`, `supervisor.criar`. |
| `entidade` | string | Ex.: `estagiario`, `feriado`, `supervisor`. |
| `entidade_id` | string nullable | ID da entidade afetada. |
| `payload` | text (JSON) nullable | Dados relevantes da ação (campos alterados, snapshot, etc.). |
| `ip` | string nullable | |
| `created_at` | datetime | Único timestamp. `$timestamps = false` no model — `updated_at` não existe. |

### Invariantes

- Modelo **nunca** sofre `update()` ou `delete()` na app. Convenção,
  não enforcada por trigger.
- `created_at` é setado explicitamente pelo `AuditoriaService::registrar`
  (não pelo Eloquent automático).
- `payload` é `null` quando o array do caller estava vazio (não grava
  `"[]"` — distinção entre "vazio" e "irrelevante").

### Quando registrar

Convenção: registre quando **a ação tem valor histórico** —
edição de cadastro, remoção de feriado, atribuição de supervisor,
re-assinatura. **Não registre** ações de leitura ou flutuações de
estado triviais (bater ponto não vai pra auditoria — a `Frequencia`
**é** o registro).

Tela `/admin/auditoria` (admin-only) lista as últimas 200 entradas
com filtros por entidade e usuário.

---

## Anexo — Campos derivados / desnormalizações

Algumas duplicações intencionais de dados, com justificativa:

| Campo | Onde mora | Por que duplicado |
|---|---|---|
| `Estagiario.supervisor_nome` | desnormalização de `Supervisor.nome` | Preserva o nome no momento do vínculo (caso supervisor mude de nome). Atualizado quando admin troca o supervisor pelo dropdown. |
| `Estagiario.supervisor_username` | desnormalização | A autorização legada (`SupervisorDashboardController`) lê desse campo direto. Mudar pra join exige reescrever 2 controllers e 1 middleware. |
| `Assinatura.snapshot` | redundância intencional | Snapshot completo da folha **no momento da assinatura**. É o que torna a verificação de integridade possível sem reconstruir histórico. |

Outras duplicações deveriam virar consultas — abrir issue se encontrar.
