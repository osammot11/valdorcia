# valdorcia

Sito di **Val d'Orcia Gravel**: pagine pubbliche, iscrizioni online con pagamento (Stripe/PayPal), biglietti PDF via email (Brevo) e area admin.

Laravel, CSS scritto a mano (nessun framework CSS).

## Dove cambiare cosa

- `config/event.php` — nome, date, località, organizzatore, contatti, posti e percorsi dell'evento (contenuti dal sito ufficiale valdorciagravel.it)
- `app/Services/Registration.php` — scaglione di prezzo valido oggi e posti rimasti per percorso
- Prezzi: tabella `price_tiers`, gestita dalla dashboard admin (uno scaglione con codice vale solo per chi inserisce quel codice)
- Certificati medici: `storage/app/private/certificates/{ordine}`, scaricabili solo dall'admin
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
