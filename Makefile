###############################################################################
# Makefile — atalhos para a stack Docker.
# Use `make help` pra listar tudo.
###############################################################################

DC      := docker compose
DC_TEST := docker compose -f docker-compose.yml -f docker-compose.test.yml
DX      := $(DC) exec -T app

.DEFAULT_GOAL := help

.PHONY: help
help: ## Lista os alvos disponíveis
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-18s\033[0m %s\n", $$1, $$2}'

###############################################################################
# Ciclo de vida da stack
###############################################################################

.PHONY: build
build: ## Build de todas as imagens (sem cache)
	$(DC) build --pull

.PHONY: up
up: ## Sobe a stack em background
	$(DC) up -d

.PHONY: down
down: ## Derruba a stack (mantém volumes)
	$(DC) down

.PHONY: restart
restart: ## Reinicia o container app
	$(DC) restart app

.PHONY: logs
logs: ## Tail dos logs de todos os containers
	$(DC) logs -f --tail=100

.PHONY: ps
ps: ## Status dos containers
	$(DC) ps

.PHONY: nuke
nuke: ## CUIDADO — derruba e apaga TODOS os volumes (perde dados)
	$(DC) down -v

###############################################################################
# Acesso ao container app
###############################################################################

.PHONY: shell
shell: ## Bash dentro do container app
	$(DC) exec app bash

.PHONY: tinker
tinker: ## Abre tinker no container app
	$(DX) php artisan tinker

###############################################################################
# Testes / qualidade
###############################################################################

.PHONY: test
test: ## Roda toda a suíte de testes (sqlite in-memory)
	$(DX) php artisan test

.PHONY: test-coverage
test-coverage: ## Roda os testes com cobertura
	$(DX) php artisan test --coverage --min=80

.PHONY: test-parallel
test-parallel: ## Roda os testes em paralelo
	$(DX) php artisan test --parallel

.PHONY: pint
pint: ## Formata o código com Laravel Pint
	$(DX) ./vendor/bin/pint

.PHONY: pint-check
pint-check: ## Verifica formatação sem alterar arquivos
	$(DX) ./vendor/bin/pint --test

.PHONY: check
check: pint-check test ## pint --test + test (rodar antes de cada commit)

###############################################################################
# Banco de dados
###############################################################################

.PHONY: migrate
migrate: ## Aplica migrations pendentes (Oracle dev)
	$(DX) php artisan migrate

.PHONY: migrate-status
migrate-status: ## Mostra status das migrations
	$(DX) php artisan migrate:status

.PHONY: fresh
fresh: ## CUIDADO — drop + migrate + seed (apaga TUDO no Oracle dev)
	$(DX) php artisan migrate:fresh --seed

.PHONY: seed
seed: ## Roda seeders
	$(DX) php artisan db:seed

###############################################################################
# CI
###############################################################################

.PHONY: ci
ci: ## Roda a pipeline de CI localmente (build + pint --test + test)
	$(DC_TEST) build app
	$(DC_TEST) run --rm app

###############################################################################
# Bootstrap (primeira vez)
###############################################################################

.PHONY: bootstrap
bootstrap: ## Setup inicial: build + up + composer install + key:generate + migrate
	@if [ ! -f .env ]; then cp .env.example .env; echo "[bootstrap] .env criado a partir do .env.example"; fi
	$(DC) build
	$(DC) up -d
	@echo "[bootstrap] aguardando containers ficarem healthy..."
	@sleep 10
	$(DX) composer install
	$(DX) php artisan key:generate
	@echo "[bootstrap] aguardando oracle (pode levar até 3min)..."
	$(DC) exec oracle bash -c 'until healthcheck.sh; do sleep 5; done'
	$(DX) php artisan migrate
	@echo "[bootstrap] pronto. acesse https://ponto.localhost"
