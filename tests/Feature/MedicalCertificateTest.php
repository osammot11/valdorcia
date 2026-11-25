<?php

namespace Tests\Feature;

use App\Jobs\SendCertificateReminder;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Certificato medico caricato dopo l'iscrizione: portale del biglietto e promemoria ogni 10 giorni. */
class MedicalCertificateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2027-03-01 09:00:00');
        Storage::fake('local');
    }

    private function ticket(string $route = 'saporito', ?string $certificate = null, string $code = 'TKT_CERT1'): Ticket
    {
        $order = Order::firstOrCreate(['group_code' => 'WFU_CERT'], ['total_amount' => 35, 'payment_method' => 'stripe', 'status' => 'paid', 'discount_code' => 'Secondo scaglione']);

        return $order->tickets()->create([
            'price_paid' => 35, 'first_name' => 'Anna', 'last_name' => 'Bianchi', 'route_choice' => $route,
            'dob' => '1985-04-12', 'birth_place' => 'Siena', 'residence_address' => 'Via di Prova 1', 'city' => 'Siena',
            'zip_code' => '53100', 'province' => 'Siena', 'region' => 'Toscana', 'country' => 'Italia', 'nationality' => 'IT',
            'email' => 'anna@example.com', 'phone' => '3330000000', 'status' => 'active', 'payment_tag' => 'Stripe',
            'unique_ticket_code' => $code, 'medical_certificate_path' => $certificate,
        ]);
    }

    public function test_the_portal_uploads_the_certificate_and_stops_the_reminders(): void
    {
        $ticket = $this->ticket();
        $ticket->update(['certificate_reminded_at' => now()->subDays(3)]);
        $url = '/ordine/WFU_CERT/biglietto/TKT_CERT1/certificato';

        $this->get($url)->assertOk()->assertSee('non è ancora stato caricato')->assertSee('idoneità agonistica');

        // Senza dichiarazione non passa
        $this->post($url, ['medical_certificate' => UploadedFile::fake()->create('cert.pdf', 300, 'application/pdf')])
            ->assertSessionHasErrors('truthful_declaration');

        $this->post($url, [
            'medical_certificate' => UploadedFile::fake()->create('cert.pdf', 300, 'application/pdf'),
            'truthful_declaration' => '1',
        ])->assertRedirect($url);

        $ticket->refresh();
        $this->assertFalse($ticket->missingCertificate());
        $this->assertNull($ticket->certificate_reminded_at);
        Storage::disk('local')->assertExists($ticket->medical_certificate_path);
        $this->get($url)->assertSee('Certificato caricato, grazie'); // subito dopo il caricamento
        $this->get($url)->assertSee('è già stato caricato');
    }

    public function test_the_portal_needs_the_right_codes_and_closes_after_the_event(): void
    {
        $this->ticket();
        $this->get('/ordine/WFU_CERT/biglietto/TKT_SBAGLIATO/certificato')->assertNotFound();
        $this->get('/ordine/WFU_ALTRO/biglietto/TKT_CERT1/certificato')->assertNotFound();

        $this->travelTo('2027-10-18 09:00:00');
        $this->get('/ordine/WFU_CERT/biglietto/TKT_CERT1/certificato')->assertOk()->assertSee('caricamento online è chiuso');
        $this->post('/ordine/WFU_CERT/biglietto/TKT_CERT1/certificato', [
            'medical_certificate' => UploadedFile::fake()->create('cert.pdf', 300, 'application/pdf'),
            'truthful_declaration' => '1',
        ])->assertForbidden();
    }

    public function test_reminders_go_out_every_ten_days_only_while_the_certificate_is_missing(): void
    {
        Queue::fake();
        $new = $this->ticket(code: 'TKT_NEW');
        $old = $this->ticket(code: 'TKT_OLD');
        $remindedRecently = $this->ticket(code: 'TKT_RECENT');
        $remindedLongAgo = $this->ticket(code: 'TKT_DUE');
        $withCertificate = $this->ticket(certificate: 'certificates/x.pdf', code: 'TKT_OK');

        Ticket::whereKey([$old->id, $remindedRecently->id, $remindedLongAgo->id, $withCertificate->id])->update(['created_at' => now()->subDays(30)]);
        Ticket::whereKey($new->id)->update(['created_at' => now()->subDays(5)]);
        $remindedRecently->update(['certificate_reminded_at' => now()->subDays(9)]);
        $remindedLongAgo->update(['certificate_reminded_at' => now()->subDays(10)]);

        $this->artisan('certificates:remind')->assertSuccessful();

        Queue::assertPushed(SendCertificateReminder::class, 2);
        Queue::assertPushed(SendCertificateReminder::class, fn ($job) => $job->ticket->is($old));
        Queue::assertPushed(SendCertificateReminder::class, fn ($job) => $job->ticket->is($remindedLongAgo));
        $this->assertNotNull($old->fresh()->certificate_reminded_at);

        // Lo stesso giorno non riparte nulla; dopo l'evento nemmeno
        $this->artisan('certificates:remind');
        Queue::assertPushed(SendCertificateReminder::class, 2);
        $this->travelTo('2027-10-18 09:00:00');
        $this->artisan('certificates:remind');
        Queue::assertPushed(SendCertificateReminder::class, 2);
    }

    public function test_the_reminder_email_links_to_the_portal_and_is_skipped_once_uploaded(): void
    {
        config(['services.brevo' => ['key' => 'k', 'sender_email' => 'bike@example.com', 'sender_name' => 'VdOG']]);
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => '1'], 201)]);
        $ticket = $this->ticket();

        (new SendCertificateReminder($ticket))->handle();
        Http::assertSent(fn (Request $r) => str_contains($r['subject'], 'carica il certificato medico')
            && str_contains($r['htmlContent'], '/ordine/WFU_CERT/biglietto/TKT_CERT1/certificato')
            && $r['to'][0]['email'] === 'anna@example.com');

        $ticket->update(['medical_certificate_path' => 'certificates/x.pdf']);
        (new SendCertificateReminder($ticket))->handle();
        Http::assertSentCount(1);
    }

    public function test_moving_to_a_competitive_route_without_a_new_certificate_makes_it_missing_again(): void
    {
        Queue::fake();
        config(['event.online_closes_iso' => '2027-10-14']);
        $ticket = $this->ticket('frugale', 'certificates/non-agonistico.pdf');
        // Frugale e Saporito hanno la stessa quota: il cambio è possibile
        $this->post('/ordine/WFU_CERT/biglietto/TKT_CERT1/percorso', ['route_choice' => 'saporito'])->assertRedirect();

        $ticket->refresh();
        $this->assertSame('saporito', $ticket->route_choice);
        $this->assertTrue($ticket->missingCertificate());
        $this->get('/checkout/success/WFU_CERT')->assertOk()->assertSee('Certificato medico da caricare')->assertSee('Carica il certificato');
    }
}
