# Identidade visual — TRE-AC sobre gov.br DS

Este sistema usa o [gov.br Design System v3](https://www.gov.br/ds/) como
base e **sobrescreve** os tokens primários para a paleta institucional
do **Tribunal Regional Eleitoral do Acre**.

---

## Paleta TRE-AC (referência: tre-ac.jus.br)

| Cor | Hex | Uso |
|-----|-----|-----|
| **Navy institucional** | `#003366` | Cabeçalho, logos, botões primários |
| **Azul médio** | `#0066cc` | Links, destaques |
| **Navy profundo** | `#001f3f` | Hover/active de primário |
| **Azul preto** | `#000d1a` | Press states, sombras |
| **Pastel claro** | `#cce0ff` | Fundos suaves, backgrounds de info |
| **Magenta acento** | `#e91e63` | Categorias temáticas, badges destacados |

---

## Como funciona o override

O gov.br DS expõe tokens via CSS Custom Properties no `:root`. Por exemplo:

```css
--color-primary-default:    #1351b4;  /* default gov.br */
--color-primary-darken-01:  #0c326f;
```

`public/css/tre-ac-theme.css` redefine essas variáveis depois do
`core.min.css`:

```css
--color-primary-default:    #003366;  /* TRE-AC navy */
--color-primary-darken-01:  #001f3f;
```

Todos os componentes do gov.br (`.br-button.primary`, `.br-input`, links,
etc.) que referenciam `--color-primary-default` herdam automaticamente a
cor do tribunal — sem fork de CSS, sem build.

---

## Ordem de carregamento (importante)

No layout base (`resources/views/layouts/app.blade.php`):

```html
<link href=".../core.min.css" rel="stylesheet">           <!-- 1. gov.br DS -->
<link href="{{ asset('css/tre-ac-theme.css') }}" rel="stylesheet">  <!-- 2. overrides -->
```

O cache-buster `?v={{ filemtime(...) }}` força reload quando o arquivo
muda em dev.

---

## Tokens auxiliares definidos pelo tema

Em `tre-ac-theme.css` adicionamos tokens *novos* (não pertencem ao
gov.br) para uso interno:

```css
--brand-tre-ac:           #003366;   /* alias mais legível para a navy */
--brand-tre-ac-contrast:  #ffffff;
--accent-tre-ac:          #e91e63;
--accent-tre-ac-contrast: #ffffff;
```

E classes de conveniência:

| Classe | Onde usar |
|--------|-----------|
| `.br-header.tre-ac` | Cabeçalho institucional com fundo navy |
| `.tre-ac-card` | Cards de métrica do dashboard |
| `.badge-tre-ac.is-{completo,entrada,feriado,fimdesemana,pendente}` | Status do dia na folha mensal |

---

## Quando trocar para bundle (Vite + npm)

Hoje o gov.br DS vem via CDN (`cdn.jsdelivr.net`). Vamos migrar para
bundle local quando:

- A app precisar de **JS** dos componentes do gov.br (modal, dropdown,
  tabs, etc.) — atualmente usamos só CSS.
- Precisar de **CSP estrita** que proíba `cdn.jsdelivr.net`.
- Time pedir **ambiente offline** (homologação isolada).

Migração: `npm i @govbr-ds/core` + `@import` no `resources/css/app.css`
+ `npm run build` via Vite. O `tre-ac-theme.css` continua igual.

---

## Acessibilidade (NF39)

A combinação navy `#003366` sobre branco tem contraste **12.6:1** (passa
WCAG AAA). Botão primário com texto branco passa AAA também. O magenta
`#e91e63` precisa de cuidado — não usar como única indicação (usar
ícone + texto, conforme NF41).

Validar contraste em [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
antes de adicionar nova cor à paleta.
