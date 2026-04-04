.PHONY: up down build restart logs shell test test-unit test-feature test-filter test-coverage db-shell db-reset help

help:
	@echo ""
	@echo "Usage: make <target>"
	@echo ""
	@echo "  up            Start all containers"
	@echo "  up-watch      Start with file watching (hot reload)"
	@echo "  down          Stop all containers"
	@echo "  build         Rebuild images from scratch"
	@echo "  restart       Restart the app container"
	@echo "  logs          Tail logs from all containers"
	@echo "  logs-app      Tail logs from the app container only"
	@echo "  logs-db       Tail logs from the mysql container only"
	@echo "  shell         Open a shell inside the app container"
	@echo ""
	@echo "  test          Run the full test suite"
	@echo "  test-unit     Run unit tests only"
	@echo "  test-feature  Run feature tests only"
	@echo "  test-filter   Run tests matching a name  e.g. make test-filter q=Invoice"
	@echo "  test-coverage Generate HTML coverage report"
	@echo ""
	@echo "  db-shell      Open a MySQL shell"
	@echo "  db-reset      Wipe the database volume and restart fresh"
	@echo ""

up:
	docker compose up

up-watch:
	docker compose up --watch

down:
	docker compose down

build:
	docker compose build --no-cache

restart:
	docker compose restart app

logs:
	docker compose logs -f

logs-app:
	docker compose logs -f app

logs-db:
	docker compose logs -f mysql

shell:
	docker compose exec app sh

test:
	docker compose exec app vendor/bin/phpunit --colors=always

test-unit:
	docker compose exec app vendor/bin/phpunit --testsuite Unit --colors=always

test-feature:
	docker compose exec app vendor/bin/phpunit --testsuite Feature --colors=always

test-filter:
	docker compose exec app vendor/bin/phpunit --filter=$(q) --colors=always

test-coverage:
	docker compose exec app vendor/bin/phpunit --coverage-html coverage --colors=always

db-shell:
	docker compose exec mysql mysql -u app -ppassword ap_invoices

db-reset:
	docker compose down -v
	docker compose up --build