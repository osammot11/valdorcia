<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Iscrizioni chiuse prima del 10 dicembre 2026 (scaglioni caricati dalle migrazioni). */
class RegistrationClosedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-24 10:00:00');
    }

    public function test_the_closed_page_shows_the_opening_date_and_the_chosen_route_price(): void
    {
        $this->get('/iscrizione?percorso=goloso')
            ->assertOk()
            ->assertSee('riapriranno il 10 dicembre 2026!')
            ->assertSee('Goloso · 118 km')
            ->assertSee('25,00 € dal 10 dicembre')
            ->assertSee('/iscrizione/apertura.ics')
            ->assertSee('/percorsi#goloso')
            ->assertDontSee('Scegli il percorso e inserisci i dati')
            ->assertSee('property="og:image"', false);
    }

    public function test_an_unknown_route_in_the_link_is_ignored(): void
    {
        $this->get('/iscrizione?percorso=inventato')
            ->assertOk()
            ->assertDontSee('closed-route', false)
            ->assertSee('href="/percorsi"', false);
    }

    public function test_the_calendar_file_is_an_all_day_event_on_the_opening_day(): void
    {
        $response = $this->get('/iscrizione/apertura.ics')->assertOk();

        $this->assertStringStartsWith('text/calendar', $response->headers->get('Content-Type'));
        $ics = $response->getContent();
        $this->assertStringContainsString("BEGIN:VCALENDAR\r\n", $ics);
        $this->assertStringContainsString("DTSTART;VALUE=DATE:20261210\r\n", $ics);
        $this->assertStringContainsString("DTEND;VALUE=DATE:20261211\r\n", $ics);
        $this->assertStringContainsString('SUMMARY:Aprono le iscrizioni: Val d\'Orcia Gravel 2027', $ics);
    }

    public function test_route_buttons_do_not_promise_a_form_while_registration_is_closed(): void
    {
        $this->get('/')->assertOk()->assertSee('Quote del Goloso')->assertDontSee('Iscriviti al Goloso');
        $this->get('/percorsi')->assertOk()->assertSee('Quote del Goloso');

        $this->travelTo('2026-12-15 10:00:00');
        $this->get('/')->assertOk()->assertSee('Iscriviti al Goloso');
        $this->get('/iscrizione/apertura.ics')->assertNotFound();
    }
}
