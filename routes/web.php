<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Services\Registration;
use App\Http\Controllers\PageController;

// Rotta per inviare i dati del modulo (POST)
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Pagamento carta interno con Stripe Elements
Route::get('/checkout/{order_code}/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');

// Rotta per il ritorno DOPO il pagamento confermato (GET)
Route::get('/checkout/success/{order_code}', [CheckoutController::class, 'success'])->name('checkout.success');

// Rotta per il ritorno in caso di ANNULLAMENTO (GET)
Route::get('/checkout/cancel/{order}', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// Cambio di percorso da parte dell'iscritto (link dalla pagina di conferma)
Route::get('/ordine/{order}/biglietto/{ticket}/percorso', [\App\Http\Controllers\RouteChangeController::class, 'show'])->name('route-change.show');
Route::post('/ordine/{order}/biglietto/{ticket}/percorso', [\App\Http\Controllers\RouteChangeController::class, 'update'])
    ->middleware('throttle:10,1')
    ->name('route-change.update');

// Rotta per il download on-demand del singolo biglietto PDF (GET)
Route::get('/ticket/{unique_code}/download', [CheckoutController::class, 'downloadTicket'])->name('ticket.download');

Route::get('/iscrizione', function () {
    // Scaglione di prezzo valido oggi e posti rimasti (vedi App\Services\Registration)
    return view('welcome', [
        'publicTier' => Registration::publicTier(),
        'reservedTier' => Registration::reservedTier(),
        'nextTier' => Registration::nextPublicTier(),
        'remaining' => Registration::remainingByRoute(),
    ]);
})->name('iscrizione');

// Verifica del codice ex iscritti prima dell'invio, per non far perdere i dati del modulo
Route::post('/iscrizione/codice', function (\Illuminate\Http\Request $request) {
    $reserved = Registration::reservedTier();
    $valid = $reserved && strcasecmp(trim((string) $request->input('code')), $reserved->access_code) === 0;

    return response()->json(['valid' => $valid, 'required' => $reserved && ! Registration::publicTier()]);
})->middleware('throttle:10,1')->name('iscrizione.codice');

Route::redirect('/ticketing', '/iscrizione', 301);
Route::redirect('/ticketing/index.php', '/iscrizione', 301);


use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

// Rotte Pubbliche per il Login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\WebhookController;

Route::post('/webhook/stripe', [WebhookController::class, 'handleStripe'])->name('webhook.stripe');

// 🔒 GRUPPO ROTTE PROTETTE (Lucchetto)
Route::middleware('auth')->group(function () {
    
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/price', [AdminController::class, 'updatePrice'])->name('admin.update_price');
    
    Route::get('/admin/pending', [AdminController::class, 'pending'])->name('admin.pending');
    Route::post('/admin/upload-csv', [AdminController::class, 'uploadCsv'])->name('admin.upload_csv');
    Route::post('/admin/ticket/{id}/approve', [AdminController::class, 'approveTicket'])->name('admin.approve_ticket');
    Route::delete('/admin/ticket/{id}/delete', [AdminController::class, 'deleteTicket'])->name('admin.delete_ticket');
    
    Route::get('/admin/ticket/create', [AdminController::class, 'createTicket'])->name('admin.create_ticket_form');
    Route::post('/admin/ticket/store', [AdminController::class, 'storeTicket'])->name('admin.store_ticket');
    
    // Esportazione CSV
    Route::get('/admin/export-csv', [AdminController::class, 'exportCsv'])->name('admin.export_csv');
    
    // Azioni sui singoli biglietti
    Route::get('/admin/ticket/{id}/edit', [AdminController::class, 'editTicket'])->name('admin.edit_ticket');
    Route::post('/admin/ticket/{id}/update', [AdminController::class, 'updateTicketData'])->name('admin.update_ticket');
    Route::get('/admin/ticket/{id}/pdf', [AdminController::class, 'downloadPdf'])->name('admin.download_pdf');
    Route::get('/admin/ticket/{id}/certificato', [AdminController::class, 'downloadCertificate'])->name('admin.download_certificate');
});

// Pagine che vivono dentro il programma
Route::redirect('/orario', '/programma', 301);
Route::redirect('/ricettivita', '/programma#dormire', 301);

// Indirizzi del vecchio sito WordPress, se questo sito prende il dominio
Route::redirect('/percorso', '/percorsi', 301);
Route::redirect('/programma-2', '/programma', 301);
Route::redirect('/iscrizione-2025', '/iscrizione', 301);
Route::redirect('/foto-2026', '/gallery', 301);
Route::redirect('/termini-e-condizioni', '/regolamento', 301);

Route::get('/', [PageController::class, 'show'])->name('home');
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', 'programma|chi-siamo|faq|come-arrivare|contattaci|contatti|cookie-policy|gallery|partnership|percorsi|privacy-policy|regolamento|styleguide|termini-condizioni|come-arrivare\.php|contattaci\.php|contatti\.php|cookie-policy\.php|gallery\.php|partnership\.php|percorsi\.php|privacy-policy\.php|regolamento\.php|styleguide\.php|termini-condizioni\.php');
