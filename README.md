# COD ERP — Cash On Delivery CRM / ERP for African markets

A production-ready, single-workspace ERP/CRM for COD (Cash On Delivery) e-commerce operations.
Built with **Laravel 11**, **PHP 8.3**, **FilamentPHP v3**, **Tailwind**, **Livewire**, **Alpine.js**.

The UI is modeled on **Linear / Stripe Dashboard / Attio CRM**: minimal, premium, clean, responsive, dark-mode ready.

---

## Modules

| # | Module | Highlights |
|---|---|---|
| 1 | **Dashboard** | Global KPI cards (Spend, Leads, Avg CPL, Revenue, Net Profit, Delivered, Top Product, Top Country) + 5 charts + Recent Orders + Low Stock alerts |
| 2 | **Daily Spend Tracking** | Media buying performance table, CPL auto-calc, color-coded profitability badges, advanced filters, summarizers, CSV export |
| 3 | **Team Management** | Users, teams, role badges, leaderboard, KPIs per agent |
| 4 | **Sourcing Requests** | Pending → Reviewing → Approved → Testing → Winning workflow, supplier links, estimated margin |
| 5 | **Profit Calculator** | Revenue − Product Cost − Delivery − Ad Spend − Refunds − Misc Expenses; per product / per country breakdowns; ROAS & Margin |
| 6 | **Stock Management** | Multi-warehouse, low-stock alerts, auto-decrement on confirmed orders, stock movements audit trail (in/out/damaged/returned) |
| 7 | **Expenses** | Ads / Salaries / Logistics / Rent / Software / Suppliers / Refunds / Misc with invoice upload + recurring expenses |
| 8 | **Roles & Permissions** | spatie/laravel-permission with roles: admin, manager, media_buyer, agent, sourcing_manager, accountant + per-resource gates |
| 9 | **Analytics** | ROAS, CPL, CPA, Margin, Confirmation Rate, Delivery Rate; platform + team performance breakdown |
| 10 | **Settings** | Countries, currencies, platforms, ad accounts, teams |

---

## Tech stack

- **Backend** — Laravel 11, PHP 8.3, Eloquent ORM, Policies & Gates, Queues (database driver by default)
- **Admin UI** — Filament v3 panel with custom Indigo/Slate theme, Inter font, dark mode
- **Front-end** — Livewire 3, Alpine.js, Tailwind, Filament's Vite pipeline
- **Logging** — `spatie/laravel-activitylog` on Products, Orders, Sourcing Requests
- **Media** — `spatie/laravel-medialibrary` for file/invoice uploads
- **Database** — MySQL in production, SQLite supported for development

---

## Quick start (local development)

```bash
git clone https://github.com/RACHCHAD/cod-erp.git
cd cod-erp

composer install
cp .env.example .env
php artisan key:generate

# Quick local DB — SQLite
echo 'DB_CONNECTION=sqlite' >> .env
touch database/database.sqlite

php artisan migrate --seed
php artisan storage:link

npm install
npm run build

php artisan serve
```

The admin panel is at `http://localhost:8000/admin`.

### Demo accounts (after seeding)

| Role | Email | Password |
|---|---|---|
| Admin | `admin@cod-erp.test` | `password` |
| Manager | `manager@cod-erp.test` | `password` |
| Media Buyer | `buyer@cod-erp.test` | `password` |
| Agent | `agent@cod-erp.test` | `password` |
| Sourcing Manager | `sourcer@cod-erp.test` | `password` |
| Accountant | `accountant@cod-erp.test` | `password` |

---

## Production deployment

See [`DEPLOYMENT.md`](./DEPLOYMENT.md) for a full **Hostinger VPS + Nginx + MySQL** guide, including:

- Nginx server block
- PHP-FPM config
- MySQL DB & user setup
- Supervisor for queues
- Cron for `schedule:run` (recurring expenses, etc.)
- Cache & opcache tuning

---

## Architecture

```
app/
├── Filament/
│   ├── Pages/           Dashboard, ProfitCalculator, Analytics
│   ├── Resources/       17 resources (Products, AdSpends, Orders, Leads, …)
│   └── Widgets/         KPI stats, charts, recent orders, low stock
├── Models/              18 Eloquent models with relationships & accessors
├── Observers/           OrderObserver (stock auto-decrement), OrderItemObserver (auto-recalculate)
├── Providers/           AppServiceProvider (Gate::before admin bypass)
│                        Filament/AdminPanelProvider (premium theme)
database/
├── migrations/          20 ordered migrations
└── seeders/             RoleSeeder, CurrencySeeder, CountrySeeder, PlatformSeeder, ExpenseCategorySeeder, DemoSeeder
resources/views/
├── components/cod/      kpi.blade.php, table.blade.php (reusable)
└── filament/pages/      Custom views for Profit Calculator & Analytics
```

### Stock auto-decrement

When an order's status transitions to `confirmed`, `OrderObserver` decrements stock for every line item from the order's warehouse. When the status moves from `confirmed` to `cancelled` / `returned` / `refunded`, stock is restored (and tracked in `stock_movements`).

### Profit formula (per order)

```
profit = (subtotal − refund_amount) − product_cost − delivery_cost
```

The dashboard's **Net Profit** stat adds total ad spend on top:

```
net_profit = Σ order.profit − Σ ad_spend
```

---

## Roles & permissions

`Spatie\Permission` is used with a per-resource ability matrix. The `admin` role is granted everything via `Gate::before` in `AppServiceProvider`. Other roles get a curated subset:

- **manager** — full access except deleting users/settings
- **media_buyer** — spend, products (read), ad accounts (read), analytics, profit
- **agent** — leads (update), orders, products (read)
- **sourcing_manager** — sourcing approval workflow, suppliers, products
- **accountant** — expenses, orders/spends (read), analytics, profit, exports

Add roles & permissions in `database/seeders/RoleSeeder.php`.

---

## License

MIT.
