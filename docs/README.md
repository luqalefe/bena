# Documentação do Bena

Pasta canônica de docs do projeto. Cada arquivo tem um foco claro;
abra o que interessa pra sua tarefa.

## Visão de quem chega ao projeto

| Você quer… | Leia |
|---|---|
| Entender o que é o sistema, por que existe, o que entrega | [`visao-geral.md`](./visao-geral.md) |
| Subir local em 5 minutos | [`../README.md`](../README.md) (raiz) |
| Conhecer convenções e workflow (TDD, XP, "como pedimos PR") | [`../CLAUDE.md`](../CLAUDE.md) |
| Ver a lista de histórias e critérios | [`../REQUISITOS.md`](../REQUISITOS.md) |

## Aprofundamento técnico

| Você quer… | Leia |
|---|---|
| Camadas, middlewares, services e como o sistema responde uma request | [`arquitetura.md`](./arquitetura.md) |
| Cada modelo, regra de negócio e invariante | [`dominio.md`](./dominio.md) |
| Comandos artisan, jobs agendados, troubleshooting | [`operacao.md`](./operacao.md) |
| Trocar usuário simulado em runtime (dev) | [`dev-sessao.md`](./dev-sessao.md) |

## Operação e identidade

| Você quer… | Leia |
|---|---|
| Subir / atualizar em produção (passo a passo pra infra) | [`deploy-prod.md`](./deploy-prod.md) |
| Paleta TRE-AC, tokens gov.br DS, fontes | [`identidade-visual.md`](./identidade-visual.md) |

## Cultura

| Você quer… | Leia |
|---|---|
| Origem do nome, mascotes, narrativa, Lucander | [`lore.md`](./lore.md) |

---

## Convenções desta pasta

- **Português** sempre, salvo termo técnico que perde semântica
  traduzido (`middleware`, `factory`, `request`).
- **Markdown CommonMark**, renderizado pelo GitHub.
- Quando uma página fala de uma classe, **sempre** linkar pelo path
  (`app/Services/AssinaturaService.php`) — facilita pular pra IDE.
- Atualizar o doc no **mesmo PR** que muda o código. Doc desatualizado
  é dívida técnica.
