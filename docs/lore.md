# Lore do Bena

A parte "humana" do sistema: por que o nome, quem é o narrador, o que
cada mascote representa. Para detalhes técnicos do sistema buddy
(atribuição, frase, persistência), veja [`arquitetura.md#buddyservice`](./arquitetura.md).

---

## O nome

**Bena** é uma palavra em **Hãtxa Kuĩ**, língua do povo **Huni Kuin**,
que vive no Acre. Significa **"novo"**.

Vem da expressão **Xinã Bena** — *"novo tempo"* — usada pelos Huni Kuin
para falar do momento atual de renovação cultural. É o nome certo para
o sistema que substitui a folha de ponto em papel pelo registro
digital.

> *"Se a entrega da folha de ponto vira uma pequena novela mensal —
> com adiamento, promessa de oração e reza pelo bom senso de quem vai
> entregar por último — talvez o problema não seja quem assina. Seja a
> folha."*

---

## Para quem o sistema foi feito

> *"Esta é a marca que quero deixar neste tribunal: um sistema feito
> por um estagiário, para estagiários."*
> — Lucas Alefe (tela "Sobre" do sistema)

O Bena nasceu pra **devolver tempo às pessoas**. Estagiários e
supervisores deixam de perder horas todo mês com a "novela" da folha
de papel, e podem investir esse tempo onde rende: no aprendizado e no
trabalho que importa.

Isso atravessa cada decisão de design — incluindo a presença dos
mascotes, que existem pra que um sistema interno de tribunal não seja
mais frio do que precisa ser.

---

## O narrador — Lucander, o Improvisador

```
       🧙‍♂️
   Lucander, o Improvisador
   Bardo-Mago · Improviso
   Carta lendária — STI
```

Quando você abre o `/bem-vindo` pela primeira vez, quem aparece é o
**Lucander**. Bardo-mago, classe rara. Estagiário ainda aprendendo as
manhas da casa.

> *"Se não funciona com regra, funciona com riff."*

Lucander é a versão mascote do **Lucas Alefe**, autor do sistema.
Aparece como narrador no onboarding, mas é também uma das nove cartas
lendárias da STI — ou seja, qualquer estagiário lotado em STI ou SSEC
pode tirar o Lucander como buddy permanente no sorteio.

> A história de carta dele:
> *"Estagiário ainda aprendendo as manhas da casa. Quando um sistema
> trava num momento ruim, vai testando caminhos até achar um que passe
> — nem sempre o do manual, mas um que serve. Anota o atalho num
> caderno pra não esquecer e pra quem vier depois aproveitar."*

---

## Sistema de pools e raridades

Existem **três pools** de mascotes, cada um com uma intenção:

| Pool | Quando aparece | Tom | Quantidade |
|---|---|---|---|
| **Padrão** | Estagiários comuns (qualquer lotação) | Variado, leve, peer-to-peer | 8 |
| **Sênior** | Supervisores e admin | Mentoria, calmo, experiente | 4 |
| **Lendário (STI)** | Estagiários lotados em STI ou SSEC | Carta de RPG/TCG: classe + habilidade + flavor | 9 |

A escolha é aleatória dentro do pool e **permanente** — você adota o
seu mascote na primeira visita à dashboard ou ao `/bem-vindo`. Cada
mascote tem um sprite PNG em `public/images/buddies/<tipo>.png`; se o
arquivo não existe, cai pro emoji.

A página `/mascotes` (acessível a qualquer grupo logado) mostra todos
os mascotes — padrão, sênior, e a galeria lendária com as cartas
únicas da STI.

---

## Pool padrão — 8 mascotes

Estagiários do dia a dia. Personagens locais, cada um com uma história
amarrada à Justiça Eleitoral do Acre.

| Mascote | Personalidade | História |
|---|---|---|
| 🦉 **Coruinha** | Sábia, formal | Vista pela primeira vez na sala de apuração de 1996, quando a urna eletrônica chegou ao Acre. Estudou cada protocolo enquanto os servidores conferiam atas. Hoje observa, em silêncio, cada folha de ponto — como observou cada boletim. |
| 🐱 **Miau** | Preguiçoso, cético | Apareceu numa zona eleitoral do interior, dormindo em cima de uma pilha de boletins entre dois pleitos. Adotou o tribunal porque nada por ali tem urgência fora de eleição. Estagia, mas com prioridades claras — a soneca vem antes da apuração. |
| 🐶 **Totó** | Empolgado, afetuoso | Cresceu correndo entre caixas de seção eleitoral durante o transporte das urnas pelo Acre. Dá um latido por cada carga despachada — já passou de 200, sem perder a contagem. *Tudo motivo de festa: a urna chegou! O caminhão saiu! VIVA A DEMOCRACIA!!!* |
| 🦫 **Capi** | Zen, contemplativa | Vive nas margens do Rio Acre e assistiu, sem se mexer, à chegada da primeira urna eletrônica numa cidade ribeirinha. *"Tudo flui — voto, rio, tempo."* Aprendeu a contar votos só observando os fiscais; em Bena, observa o ponto. |
| 🦜 **Louro** | Fofoqueiro, antenado | Morava na sala dos juízes eleitorais de uma comarca antiga do interior. Aprendeu a dizer *"ata aberta"* e *"ata fechada"* antes de aprender a falar *"oi"*. Tudo que o tribunal cochicha, ele repete — mas só do tipo que não vira problema. |
| 🐢 **Lenta** | Devagar, café-dependente | Atravessou a Praça do TRE ao longo de três eleições. Quando chegou, seu próprio estágio já tinha começado e terminado três vezes. Mas revisa folhas com mais calma que qualquer auditor — devagar e detalhista. |
| 🐧 **Pingu** | Profissional, pontual | Chegou ao TRE-AC de gravata. Ninguém soube explicar de onde. Foi visto pela primeira vez na sala da Presidência durante a transição de um pleito. Desde então, organiza pautas e nunca atrasou uma sessão. |
| 🐸 **Sapão** | Descolado, parceiro | Veio das margens do Bacurau, depois de uma operação de logística reversa pós-apuração. Subiu numa urna que voltava pra estoque e ficou. *"Mano, o tribunal é firmeza, eu fico."* Hoje é o mais informal do quadro — mas na hora H, entrega. |

---

## Pool sênior — 4 mascotes

Supervisores e admin. Tom de mentoria, voz de quem já viu muito ciclo
eleitoral.

| Mascote | Personalidade | História |
|---|---|---|
| 🦅 **Águia** | Estrategista, visão panorâmica | Voou sobre a Amazônia acompanhando o transporte aéreo das urnas eletrônicas para as zonas mais remotas do Acre. Conhece cada pista de pouso, cada ribeirão. Em Bena, vê a operação inteira do alto — antes que alguém perceba que algo saiu do plano. |
| 🦁 **Leão** | Mentor calmo, autoridade | Liderou as primeiras equipes de mesários do Acre nas eleições municipais dos anos 80. Não precisava falar alto — a presença bastava. Hoje acompanha quem lidera estagiários e ensina que comando se exerce com calma, não com volume. |
| 🐘 **Elefon** | Memória prodigiosa, experiente | Acompanhou cada eleição do TRE-AC desde a fundação. Lembra do nome de cada juiz eleitoral, de cada presidente, de cada plebiscito. *"Quem não esquece, não repete erros."* É o registro vivo do que funcionou — e do que não. |
| 🐻 **Urso** | Protetor, paciente | Vigiou a primeira urna eletrônica que chegou em uma comunidade indígena no Acre. Sentou na porta do galpão e ninguém passou sem ele acompanhar. Sereno, presente, paciente. Em Bena, cuida da equipe como cuidou daquele galpão. |

---

## Pool lendário — 10 cartas da STI

Cartas únicas inspiradas em servidores reais que ensinaram o autor a
amar a profissão (e uma carta institucional — a desembargadora que
preside o TRE-AC no pleito atual). Cada uma tem **classe**, **habilidade**
e **flavor text** no estilo de carta de RPG/TCG. Aparecem **apenas** no
sorteio de estagiários lotados em STI ou SSEC (config
`buddies.lotacoes_lendarias`).

A intenção é dupla: atribuir um buddy temático pra quem entra na STI,
e deixar registrado no próprio sistema o agradecimento de quem
herdou a casa — *"a STI me ensinou a amar essa profissão"*.

### 👩‍⚖️ Waldirene, a Magistrada das Florestas

- **Classe:** Presidenta do Pleito
- **Habilidade:** *Voto em Cada Canto* — sob sua presidência, nenhuma
  seção fica sem fiscal e nenhum boletim deixa de ser apurado.
  Estagiários cumprimentados por ela no corredor ganham +2 em coragem
  até o fim do dia.
- **Flavor:** *"Não se trata de ocupar um cargo, mas de abraçar um
  compromisso com a democracia, a cidadania e a história."* (frase
  textual do discurso de posse na Presidência do TRE-AC, ago/2025)

> *Começou em Xapuri em 1998 — promotora numa comarca onde o cartório
> fechava cedo e a floresta começava na esquina. Levou esse mapa pra
> capital: doze vezes assumiu o governo do Acre interinamente sem
> nunca perder o jeito de quem despachou em vara de interior. Em 2025,
> foi eleita por aclamação a 24ª Presidência do TRE-AC e comanda agora
> o pleito de 2026, prometendo que "cada eleitor — cidade, ribeirinho
> ou aldeia indígena" terá voto assegurado. Os xales floridos sobre a
> toga preta viraram lenda no plenário. "É só um lenço", diz. Os
> servidores antigos juram que não.*

Carta institucional, ancorada na carreira real da **Desembargadora
Waldirene Oliveira da Cruz Lima Cordeiro** (UFAC 1991 → promotora em
Xapuri 1998 → Desembargadora TJAC 2012 → Presidente TJAC 2021-23 →
Presidente TRE-AC ago/2025-27). A única lendária que não vem da STI
do tribunal: é a primeira a comandar o pleito sob o qual o Bena nasce.

### 👨‍🔧 Edcleu, o Forjador de Raiz

- **Classe:** Engenheiro de Núcleo
- **Habilidade:** *Do Zero ao Núcleo* — ignora todo framework
  opcional. Sistemas forjados sob sua tutela ganham +2 em longevidade
  e nunca dependem de plugin de terceiros.
- **Flavor:** *"Não sou um visionário, sou um engenheiro."*

> *Reza a lenda que o Edcleu forjou os primeiros lacres das urnas
> eletrônicas que chegaram ao Acre. Não confiava em peça pronta — cada
> selo saía da bancada dele, do zero, e ninguém jamais conseguiu violar
> um pleito sob sua guarda. Quando perguntam o segredo, responde curto:
> "se não posso forjar, não posso garantir".*

### 🧙 Lucírio, o Alquimista

- **Classe:** Pioneiro Alquimista
- **Habilidade:** *Ideia Maluca* — uma vez por sprint, propõe um
  experimento que parece absurdo. 70% de chance de virar MVP funcional.
- **Flavor:** *"Louco é quem me diz e não é feliz."*

> *Aparece quando uma eleição precisa de algo que ainda não existe.
> Foi visto pela primeira vez numa apuração noturna de 1992,
> transformando uma planilha manuscrita num mapa que indicava com
> precisão onde cada urna estaria no dia seguinte — ninguém soube
> explicar como. Tem fama de propor soluções absurdas que insistem em
> funcionar.*

### 👴 Bortelmo, Mestre do Legado

- **Classe:** Mestre do COBOL e do Clipper
- **Habilidade:** *Gambiarra Funcional* — sustenta sistema legado com
  solução improvável que dura mais que reescrita planejada. +∞ em
  código que ninguém mais ousa tocar.
- **Flavor:** *"Tá feio. Mas funciona desde 1992 — e vai funcionar
  amanhã também."*

> *Escreveu rotina em COBOL antes do tribunal ter monitor colorido —
> e várias delas ainda rodam no canto, gerando relatório que ninguém
> mais sabe regenerar do zero. Domina Clipper como dialeto materno.
> Quando algo precisa funcionar HOJE e não há tempo de refazer
> direito, é nele que se busca o jeitinho que segura o pleito — e que,
> no fim, dura uma década.*

### 🧘🏽‍♂️ Ilíseo, o Domador de Plone

- **Classe:** Sussurrador de CMS
- **Habilidade:** *Sussurrador de CMS* — domestica qualquer sistema
  legado sem perder o tom calmo. +3 em paciência diante de tecnologia
  descontinuada.
- **Flavor:** *"Calma. Vai funcionar. Só precisa de paciência."*

> *Catalogou, durante três pleitos seguidos, todos os boletins de urna
> de uma comarca inteira do interior — sem perder uma classificação
> sequer. Calmo, paciente, jamais se exaltou nem com prazo curto nem
> com sistema travado. Hoje, quando alguma ferramenta antiga reclama,
> basta ele chegar perto que volta a funcionar.*

### 🙆🏿‍♂️ Felippo, a Calmaria

- **Classe:** Resolvedor Tranquilo
- **Habilidade:** *Águas Calmas* — reduz o pânico do time em 50%
  durante incidentes. Bugs resolvidos sob sua liderança não voltam
  estressados.
- **Flavor:** *"Respira. A gente resolve."*

> *Acompanhou a apuração mais tensa do interior em 2008, quando uma
> chuva forte derrubou o gerador e quatro urnas pararam de uma vez.
> Enquanto todos corriam, ele apenas reorganizou os fios e religou
> tudo, no mesmo tom de voz do começo do dia. Desde então, "respira,
> a gente resolve" virou bordão de plenário em pleito complicado.*

### 🕵️‍♂️ Thallion, o Estrategista

- **Classe:** Mestre da Análise
- **Habilidade:** *Estudo Profundo* — dedica três turnos à análise
  antes de codar. Entregas suas levam 90% menos refator no PR.
- **Flavor:** *"Mede duas vezes, corta uma."*

> *Antes de qualquer pleito, mapeia rota das urnas, horário das mesas
> e até a previsão do clima do dia. Ninguém o vê apressado — porque já
> planejou tudo dois meses antes. Reza a lenda que, num pleito de
> 2010, previu com três semanas de antecedência qual seção precisaria
> de mesário reserva. Acertou.*

### 🧑‍🚒 Jonatão, o Guardião da Infra

- **Classe:** Guardião de Plantão
- **Habilidade:** *Apaga Incêndio* — detecta e neutraliza B.O.s de
  produção antes que o time perceba. Uptime sob sua guarda: 99,97%.
- **Flavor:** *"Tá no ar. Pode rodar."*

> *Vigia em silêncio cada cabo, cada nobreak, cada link do tribunal.
> Quando algo está prestes a cair, ele já está com a peça de troca na
> mão. Conta-se que, durante uma apuração nacional, evitou três
> quedas seguidas sem que ninguém no plenário sequer percebesse. A
> urna chega no ar porque ele chegou primeiro.*

### 🧑‍🔬 Jairón, o Mestre dos Dados

- **Classe:** Sênior dos Dados
- **Habilidade:** *Pipeline Limpo* — extrai padrão de qualquer dataset
  caótico. Relatórios sob sua orientação nascem auditáveis.
- **Flavor:** *"Dado sujo conta meia história. A outra metade é onde
  mora a verdade."*

> *Recolhe os dados de cada apuração e os organiza em arquivos
> auditáveis até o último voto. Trata cada número como prova judicial:
> não aceita um valor sem saber de onde veio. Conta-se que reconstruiu,
> numa única madrugada, o histórico inteiro de uma zona cujos arquivos
> haviam se perdido — e cada linha bateu certinho com a ata original.*

### 🧙‍♂️ Lucander, o Improvisador

- **Classe:** Bardo-Mago
- **Habilidade:** *Solo de Guitarra* — invoca soluções fora do
  manual. Uma vez por dia, pode trocar uma reunião por um commit que
  resolve o ticket.
- **Flavor:** *"Se não funciona com regra, funciona com riff."*

> Já apresentado acima — é o narrador do `/bem-vindo`, e é a versão
> mascote do autor do sistema.

---

## A página "Sobre" — agradecimentos

A tela `/bem-vindo` tem um trecho que dá nome aos servidores
homenageados nas cartas lendárias. Citação direta:

> *"O TRE-AC, e em especial a STI, foi quem me ensinou a amar essa
> profissão. Em quase dois anos de estágio aprendi com gente que faz
> esse setor existir todo dia, e devo este sistema, e muito do que
> sei, a essas pessoas."*
>
> *"Ao **mestre Edcley**, que sempre acreditou no meu potencial e me
> entregou projetos desafiadores que me fizeram crescer. Engenheiro
> de raiz, daqueles que preferem construir tudo do zero, bem no
> estilo do próprio Linus Torvalds, que gostava de resumir: 'não sou
> um visionário, sou um engenheiro'. Menos plano de cinco anos, mais
> código rodando."*
>
> *"Ao **Lucir**, um dos primeiros analistas de TI do tribunal, que
> também apostou em mim e me passou ideias 'malucas'. Ao **Keith**,
> também um dos servidores mais antigos da casa, que conduziu comigo a
> análise de requisitos e me ajudou a entender o domínio do começo ao
> fim. Ao **Bortoli**, talvez o amigo com mais anos de TI em toda a
> Justiça Eleitoral, e referência viva."*
>
> *"À paciência do **Ilis**, que insistiu em me ensinar algumas
> tarefas no Plone. E aos colegas que chegaram um pouco depois e
> viraram parceria de verdade — **Felipe, Thallys, Jair e Jonatan** —
> que abriram espaço para eu contribuir no projeto AGRECOM cuidando
> de todo o módulo de relatórios."*

Os mascotes lendários são essas pessoas (e o autor) traduzidos pra
linguagem de carta. Cada um vira um buddy possível pra quem entra na
STI hoje.

---

## Frases — vocabulário da Justiça Eleitoral

As ~150 frases dos buddies em `config/buddies.php` foram costuradas
para soar como **gente do tribunal falando**. O vocabulário é
deliberado:

- **urna**, **ata**, **boletim de urna (BU)**, **mesário**, **mesa
  receptora**, **fiscal**, **apuração**, **pleito**, **recurso**,
  **diplomação**, **ciclo eleitoral**, **zona eleitoral**, **seção**.

A escolha por contexto:

- A **frase do dia** na dashboard usa chave
  `(buddy, dia_da_semana, status_ponto)` onde `status_ponto` é
  `aguardando_entrada` / `em_andamento` / `concluido`.
- A escolha é **determinística** por `(dia_do_mês, bloco_de_12h)` —
  manhã (0–11h) ou tarde (12–23h). Mesma frase no mesmo bloco; muda
  de manhã pra tarde.
- A frase de **boas-vindas** no `/bem-vindo` vem do array dedicado
  `boas_vindas` por buddy, escolha aleatória.
- Quando não há frase pra um contexto: cai no array `generica` do
  buddy.

Amostras:

> **Coruinha (segunda, aguardando entrada):**
> *"Prezado(a), o pleito da semana se inicia. Bata o ponto e cumpra
> prazos com diligência. 📚"*

> **Miau (sexta, aguardando entrada):**
> *"Sexta. Acordei só pra ver você bater o ponto. Vou voltar a
> hibernar até o próximo pleito. 😼"*

> **Totó (sexta, concluído):**
> *"SEXTA LACRADA!!! Bom trabalho!!! FINAL DE SEMANA INAUGUROU!!! 🎉🐕"*

> **Águia (quarta, aguardando entrada):**
> *"Meio da semana. Reavalie prioridades — como antes de fechar uma
> zona eleitoral."*

> **Elefon (terça, aguardando entrada):**
> *"Terça-feira. Lembro de uma terça em 2018, antes do pleito
> municipal — muito parecida com essa."*

---

## Trilha sonora e SFX

O Bena toca música. Não como um detalhe acidental — como parte do
gesto de tornar um sistema interno de tribunal **menos frio do que
precisa ser**.

### `BENA` — trilha original

Música autoral gravada pelo autor (Lucander, o Improvisador) num
feriadão, com guitarra, baixo e bateria. Loop discreto que sustenta a
navegação no sistema sem distrair.

O **mini player** aparece em qualquer view autenticada — widget
flutuante no canto inferior direito, estilo macOS Spotify:

- **Cover** = pixel art do mascote sorteado pro usuário (Waldirene,
  Edcleu, Coruinha, etc.). Cada usuário vê o seu próprio mascote na
  capa.
- **Artista** = nome do mascote (ex: "Waldirene, a Magistrada das
  Florestas", "Coruinha").
- **Controles** revelados em hover: play/pause, mute, volume.
- **Arrastável** com mouse e touch; posição persistida em
  `sessionStorage`.
- **X vermelho** fecha o player e silencia a trilha — flag dismissed
  persiste entre views.
- Navegação entre páginas via **Turbo (Hotwire)** preserva o `<audio>`
  (atributo `data-turbo-permanent`), então a música **não corta**.

### Revelação cinematográfica do mascote

Na primeira visita ao `/bem-vindo`, o player começa em modo
"Aguardando sorteio…" (sem spoiler). O usuário clica em **Descobrir
meu mascote** e duas coisas acontecem **no mesmo frame**:

1. **Slot machine de 4 segundos** dentro da carta de reveal — sprites
   ciclam aleatoriamente do roster inteiro (22 mascotes), com
   deceleração quadrática (60ms entre swaps no início, 400ms no fim,
   pra simular roleta parando).
2. **Cover do mini player** cicla em sincronia exata — a cada tick do
   slot, a capa do player troca pra mesma sprite.

Ao final dos 4s, no mesmo `requestAnimationFrame`:
- Slot trava no sprite real do mascote sorteado.
- Cover do mini player atualiza pro mesmo sprite.
- Texto do artista no player vira o nome do mascote.
- Carta de reveal aparece com animação de scale-in.

Não há `setTimeout` separando esses eventos. A intenção é que a
revelação aconteça **exatamente no mesmo momento** nos três lugares.

### `urna-song` — SFX

A cada **bater de ponto** (entrada ou saída), toca o som da urna
eletrônica, ~3s, sampleado de uma urna real. Disparado por handler
global no submit dos forms `/ponto/entrada` e `/ponto/saida`. O
`<audio>` é `data-turbo-permanent` pra reprodução não cortar quando
o Turbo redireciona a página depois do submit.

---

## A história do projeto

### O feriadão

O Bena foi escrito em um único feriadão. Três dias, mais ou menos 17
horas por dia — **51 horas no total** —, começando em **1º de Maio**.

Sim: um sistema para tirar a folha de papel das mãos dos
trabalhadores foi escrito justamente no Dia do Trabalhador. A ironia
é parte do projeto.

O combustível foi uma dose generosa de teimosia: alguns comentários
irônicos de colegas estagiários sobre a tal "novela mensal" da folha
de ponto serviram de empurrão. O Bena não chega nem perto da
complexidade do **Git**, guardadas as devidíssimas proporções, mas a
indignação foi parecida com a do Linus Torvalds quando escreveu o
dele e, no primeiro commit, batizou o projeto de *"information
manager from hell"*.

A pessoa por trás do projeto — **Lucas Alefe** — é neurodivergente,
convive com o que se chama de hiperfoco, esse traço de cair de cabeça
em tarefas que ama. Engenharia de software entra exatamente nessa
lista. No fim, foi mais fácil escrever o sistema do que continuar
reclamando dele.

### Princípio de design

A motivação é **MonolithFirst** (Martin Fowler):

> *"Você não deveria começar um novo projeto com microserviços, nem
> que tenha certeza de que a aplicação vai ficar grande o bastante
> para justificar."*

O Bena começou pequeno, monolítico, propositalmente simples, para que
quem herdar o sistema encontre um terreno entendível e só evolua a
complexidade quando o domínio realmente pedir. Nada de microserviço
prematuro só para parecer moderno.

### Para quem vier depois

Mantenha o foco no que importa: **aprender e evoluir naquilo a que
cada um se propôs**, seja na engenharia de software, no suporte ou na
área jurídica. Burocracia administrativa não pode roubar esse tempo.

Este lugar é terreno fértil para quem quer crescer.
