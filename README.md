# GenesisHub: Multi-Vendor Marketplace

[![Tests](https://github.com/abbey-001/genesisHub/actions/workflows/tests.yml/badge.svg)](https://github.com/abbey-001/genesisHub/actions/workflows/tests.yml)

A multi-vendor e-commerce marketplace for the Nigerian market (fashion, electronics, phones, beauty, home goods). It connects **buyers**, **sellers**, **delivery riders** and **admins** in one Laravel application. **38 registered sellers.**

<!-- Add 3–4 screenshots here: storefront, seller dashboard, rider app, admin reports -->
<!-- ![Storefront](docs/screenshots/storefront.png) -->

> **Version 2:** this Laravel app is the original GenesisHub. It has since been rebuilt on Next.js and Supabase: [live demo](https://genesis-hub-nextjs.vercel.app) · [case study](https://github.com/abbey-001/abbey-001/blob/main/case-studies/genesishub.md)

**Stack:** Laravel 12 · PHP 8.2 · MySQL · Blade · Fortify · Socialite · Scout

## Features

**Buyers**
- Product search with suggestions (Laravel Scout + full-text indexes), filtering, brand and category browsing, product comparison and quick view
- Cart with coupons, wishlist, saved addresses
- Checkout with **Paystack** and **Flutterwave**
- Order tracking by order number with OTP verification, plus live delivery status
- Reviews with images and "helpful" votes
- Social login, two-factor authentication, login-activity history, email-change verification

**Sellers**
- Self-registration with email verification, shop setup, product and coupon management
- Order fulfilment, review responses, sales dashboard
- **Wallet and payouts** with commission handling and payout requests
- Telegram notifications for new orders

**Riders (delivery)**
- Rider registration and dashboard, delivery assignment and bundling, earnings and payout tracking
- Delivery zones, shop-to-rider and rider-to-address delivery legs
- Telegram notifications and broadcasts

**Admin**
- Role and permission system
- Seller, rider, customer, product, category and review management
- Refunds, payout batches, delivery payouts, wallet oversight
- Financial reports and dashboards, Telegram admin alerts

## Architecture notes
- Three separately authenticated user types (customer, seller, rider) plus admin, each with its own guard and dashboard
- Money flows (order → seller wallet → payout request → payout batch) are handled in a dedicated service (`app/Services/SellerWalletService.php`)
- Telegram bot webhooks are split per audience (`TelegramWebhookController`, `SellerTelegramWebhookController`, `AdminTelegramWebhookController`)
- Scheduled tasks run through `Api/CronController`
- Payment webhooks verify the gateway signature and are idempotent: a retried `charge.success` never takes stock twice
- Seller earnings are credited only on delivery, net of commission, held for a configurable period, then released by a daily job; a guard prevents crediting the same item twice
- Wallet balance changes use row locks (`lockForUpdate`) inside transactions so concurrent payouts cannot overdraw a wallet

## Running locally
Requires PHP 8.2, Composer, Node and MySQL/MariaDB.
```bash
git clone https://github.com/abbey-001/genesisHub.git
cd genesisHub
composer install && npm install
cp .env.example .env && php artisan key:generate
# in .env: set DB_CONNECTION=mysql and DB_*, plus PAYSTACK_*, FLUTTERWAVE_*,
# TELEGRAM_*_BOT_TOKEN and ALGOLIA_* (test/placeholder values are fine locally)
php artisan migrate --seed   # builds the schema from database/schema/mysql-schema.sql
npm run dev & php artisan serve
```

## Testing
Feature tests cover the parts of the system where money moves:

| Area | What is verified |
|---|---|
| Payment webhooks | Forged signatures are rejected; a signed `charge.success` marks the order paid and reserves stock; retried webhooks are idempotent; `charge.failed` cancels the order |
| Seller earnings | Commission is deducted; unpaid orders never credit sellers; no double-crediting; funds are held, then released exactly once |
| Payouts | Only verified sellers; no overdrawing; one payout in progress at a time; failed payouts return funds and correct lifetime totals; status order is enforced |
| Refunds | Refunds debit the seller; refunds larger than the balance overdraw it exactly once |
| Coupons | Percent caps, fixed-amount limits, validity dates, minimum order, global and per-customer usage limits |

```bash
mysql -uroot -e "create database genesis_testing"
php artisan test
```
Tests run on every push via GitHub Actions against MariaDB.

## Roadmap
- Next.js + Supabase rebuild: see the case study linked above
- Browser tests for the checkout flow

---
Built by [Umar Awobajo](https://github.com/abbey-001)
