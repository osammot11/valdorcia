<?php

namespace Tests\Feature;

use App\Jobs\SendTicketEmail;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SendTicketEmailTest extends TestCase
{
    use RefreshDatabase;

    private function ticket()
    {
        $order = Order::create(['group_code' => 'WFU_MAIL', 'total_amount' => 25, 'payment_method' => 'stripe', 'status' => 'paid']);

        return $order->tickets()->create([
            'price_paid' => 25, 'first_name' => 'Anna', 'last_name' => 'Bianchi', 'route_choice' => 'saporito',
            'dob' => '1985-04-12', 'birth_place' => 'Siena', 'residence_address' => 'Via di Prova 1', 'city' => 'Siena',
            'zip_code' => '53100', 'province' => 'Siena', 'region' => 'Toscana', 'country' => 'Italia', 'nationality' => 'IT',
            'email' => 'anna@example.com', 'phone' => '3330000000', 'status' => 'active', 'payment_tag' => 'Stripe',
            'unique_ticket_code' => 'TKT_TEST',
        ]);
    }

    public function test_the_confirmation_is_sent_through_brevo_with_the_configured_key_and_sender(): void
    {
        config(['services.brevo' => ['key' => 'xkeysib-test', 'sender_email' => 'bike@example.com', 'sender_name' => "Val d'Orcia Gravel"]]);
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => '1'], 201)]);

        (new SendTicketEmail($this->ticket()))->handle();

        Http::assertSent(fn (Request $r) => $r->url() === 'https://api.brevo.com/v3/smtp/email'
            && $r->header('api-key')[0] === 'xkeysib-test'
            && $r['sender'] === ['name' => "Val d'Orcia Gravel", 'email' => 'bike@example.com']
            && $r['to'][0]['email'] === 'anna@example.com'
            && str_contains($r['subject'], 'Iscrizione confermata')
            && str_contains($r['htmlContent'], '/checkout/success/WFU_MAIL'));
    }

    public function test_unconfirmed_logistics_are_not_printed_on_ticket_or_email(): void
    {
        config(['event.logistics_confirmed' => false]);
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => '1'], 201)]);
        $ticket = $this->ticket();

        $pdf = view('pdf.ticket', ['ticket' => $ticket->load('order')])->render();
        $this->assertStringNotContainsString('Pro Loco', $pdf);
        $this->assertStringContainsString('Luogo e orari nel programma', $pdf);
        $this->assertStringNotContainsString('dislivello', $pdf);

        (new SendTicketEmail($ticket, 'frugale'))->handle(); // email di cambio percorso, che indica la partenza
        Http::assertSent(fn (Request $r) => str_contains($r['htmlContent'], 'luogo e orari saranno nel programma')
            && ! str_contains($r['htmlContent'], ' ore 8:00'));

        config(['event.logistics_confirmed' => true]);
        $this->assertStringContainsString('Pro Loco', view('pdf.ticket', ['ticket' => $ticket])->render());
    }

    public function test_a_brevo_error_makes_the_job_fail_and_is_logged(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response(['code' => 'unauthorized'], 401)]);

        $this->expectExceptionMessage('Chiamata API Brevo fallita.');
        (new SendTicketEmail($this->ticket()))->handle();
    }
}
