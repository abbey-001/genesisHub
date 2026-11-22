# GenesisHub: Multi-Vendor Marketplace

A multi-vendor e-commerce marketplace for the Nigerian market (fashion, electronics, phones, beauty, home goods). It connects **buyers**, **sellers**, **delivery riders** and **admins** in one Laravel application. **70 registered sellers.**

<!-- Add 3–4 screenshots here: storefront, seller dashboard, rider app, admin reports -->
<!-- ![Storefront](docs/screenshots/storefront.png) -->

**Live demo:** _add link_ · **Stack:** Laravel 12 · PHP 8.2 · MySQL · Blade · Fortify · Socialite · Scout

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

## Running locally
```bash
git clone https://github.com/abbey-001/genesisHub.git
cd genesisHub
composer install && npm install
cp .env.example .env && php artisan key:generate
# set DB_*, PAYSTACK_*, FLUTTERWAVE_* and TELEGRAM_* in .env
php artisan migrate --seed
npm run dev & php artisan serve
```

## Roadmap
- Next.js storefront rewrite (in progress)
- Feature tests for checkout, payments and payouts

---
Built by [Umar Awobajo](https://github.com/abbey-001)
