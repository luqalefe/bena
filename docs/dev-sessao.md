# Modo dev — Configurar usuário simulado

Em produção, **Authelia** (atrás do Traefik, conectado ao LDAP do tribunal)
faz login + 2FA e injeta os headers `Remote-User`, `Remote-Groups`,
`Remote-Name`, `Remote-Email` em todas as requisições.

Em **dev local**, não rodamos Authelia. A app simula esses headers via
`AUTHELIA_DEV_BYPASS=true` no `.env`. Isso é decisão consciente:

- Sem container extra pra subir.
- Sem cadastrar TOTP toda vez que reseta o ambiente.
- Sem precisar de hashes argon2 reais no `users_database.yml`.

---

## Defaults (do `.env`)

```env
AUTHELIA_DEV_BYPASS=true
AUTHELIA_DEV_USER=lucas.dev
AUTHELIA_DEV_GROUPS=estagiarios
AUTHELIA_DEV_NAME="Lucas Dev"
AUTHELIA_DEV_EMAIL=lucas.dev@example.local
```

Toda requisição sem o header `Remote-User` real entra como `lucas.dev`
do grupo `estagiarios`. Cria/atualiza o `Estagiario` no banco se ainda
não existe.

---

## Trocar o usuário em runtime

Sem mexer no `.env` (e sem reiniciar o container), abra:

```
https://ponto.localhost/_dev/sessao
```

Tela tem campos:

| Campo | Mapeia para |
|-------|-------------|
| Username | `Remote-User` |
| Grupos | `Remote-Groups` (separado por vírgula) |
| Nome | `Remote-Name` |
| Email | `Remote-Email` |

A configuração vai pra **sessão** do Laravel — sobrevive reload, mas
some quando você fecha a janela ou clica em "Resetar para defaults".

### Atalho — banner no topo

Quando `AUTHELIA_DEV_BYPASS=true` e ambiente não é produção, todas as
páginas mostram um banner magenta com link "Trocar usuário" → vai pro
mesmo formulário.

---

## Receitas comuns

### Testar visão de admin

```
Username: marco.admin
Grupos:   admin
Nome:     Marco Supervisor
Email:    marco.admin@example.local
```

### Testar usuário sem grupo válido (deve dar 403)

```
Username: externo.dev
Grupos:   visitantes
```

### Testar usuário sem header (deve dar 401)

Abra uma janela anônima → não tem cookie de sessão → o middleware
ainda usa o default do `.env`. Pra testar 401 de verdade, ajuste
temporariamente `AUTHELIA_DEV_BYPASS=false` no `.env` + recreate.

---

## Em produção isso some completamente

- A rota `/_dev/sessao` é protegida pelo middleware
  `EnsureNotProduction` → retorna 404 quando `APP_ENV=production`.
- O middleware `AutheliaAuth` checa
  `app()->environment() !== 'production'` antes de aceitar bypass.
- O banner do layout só renderiza se `AUTHELIA_DEV_BYPASS=true` E não é
  produção.

Defesa em profundidade: três checagens independentes. Se uma falhar,
as outras seguram.

---

## Por que não Authelia local?

Tentamos. Foi o caminho mais longo:

1. Authelia v4.38+ rejeita `localhost` como cookie domain (precisa ter
   ponto). Workaround seria `localhost.test` + `/etc/hosts`.
2. Hashes em `users_database.yml` são placeholders — precisa rodar
   `make auth-hash PASS=...` e colar manualmente.
3. Primeiro login exige cadastrar TOTP, recuperar link de
   `/config/notification.txt`, escanear QR com app autenticador.
4. A cada `make nuke`, repete tudo.

Pra dev iterar rápido, o ROI não fecha. O fluxo Authelia continua sendo
o real em produção — está em `docker-compose.prod.yml`.
