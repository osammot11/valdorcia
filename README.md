# valdorcia

Sito di **Val d'Orcia Gravel**: pagine pubbliche, iscrizioni online con pagamento (Stripe/PayPal), biglietti PDF via email (Brevo) e area admin.

Laravel, CSS scritto a mano (nessun framework CSS).

## Dove cambiare cosa

- `config/event.php` — nome, data, località, email e percorsi dell'evento
- `public/style.css` e `public/navbar/` — sito pubblico
- `public/css/checkout.css` — iscrizione e pagamento
- `public/css/admin.css` — area admin, login, conferma ordine

## Avvio in locale

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan serve
```
