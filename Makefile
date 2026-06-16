.PHONY: up down build restart logs shell-backend artisan migrate seed

up:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose build --no-cache

restart:
	docker-compose restart

logs:
	docker-compose logs -f

shell-backend:
	docker-compose exec backend sh

artisan:
	docker-compose exec backend php artisan $(cmd)

migrate:
	docker-compose exec backend php artisan migrate --database=master

tenant-migrate:
	docker-compose exec backend php artisan tenant:migrate $(org_id)

seed:
	docker-compose exec backend php artisan db:seed --class=MasterSeeder --database=master

key:
	docker-compose exec backend php artisan key:generate

fresh:
	docker-compose exec backend php artisan migrate:fresh --database=master
	docker-compose exec backend php artisan db:seed --class=MasterSeeder --database=master

dev-backend:
	cd backend && php artisan serve --port=8000

dev-frontend:
	cd frontend && npm run dev

dev-realtime:
	cd realtime && npm run dev

# ── Production (Oracle Cloud / any VPS) ──────────────────────────────────────
prod-up:
	docker compose -f docker-compose.prod.yml up -d

prod-down:
	docker compose -f docker-compose.prod.yml down

prod-build:
	docker compose -f docker-compose.prod.yml build --parallel

prod-logs:
	docker compose -f docker-compose.prod.yml logs -f

prod-migrate:
	docker compose -f docker-compose.prod.yml exec backend php artisan migrate --path=database/migrations/master --force
	docker compose -f docker-compose.prod.yml exec backend php artisan migrate --force

prod-seed:
	docker compose -f docker-compose.prod.yml exec backend php artisan db:seed --class=DatabaseSeeder --force

prod-shell:
	docker compose -f docker-compose.prod.yml exec backend sh

prod-restart:
	docker compose -f docker-compose.prod.yml restart
