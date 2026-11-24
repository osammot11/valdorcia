<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/** Il modulo di iscrizione aperto: ogni campo ha un'etichetta collegata, errori e suggerimenti compresi. */
class RegistrationFormAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-12-15 10:00:00'); // primo scaglione aperto
    }

    private function dom(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8"?>' . $html);

        return new DOMXPath($dom);
    }

    public function test_every_visible_field_has_a_label_and_ids_are_unique(): void
    {
        $xpath = $this->dom($this->get('/iscrizione')->assertOk()->getContent());
        $form = $xpath->query('//form[@id="registration-form"]')->item(0);
        $this->assertNotNull($form);

        $ids = [];
        foreach ($xpath->query('.//*[@id]', $form) as $el) {
            $ids[] = $el->getAttribute('id');
        }
        $this->assertSame(array_unique($ids), $ids, 'Id duplicati nel modulo');

        foreach ($xpath->query('.//label[@for]', $form) as $label) {
            $this->assertContains($label->getAttribute('for'), $ids, 'Etichetta senza campo: ' . $label->textContent);
        }

        // Ogni campo compilabile ha un'etichetta (for, etichetta che lo contiene, o aria-label)
        foreach ($xpath->query('.//input[not(@type="hidden")] | .//select | .//textarea', $form) as $field) {
            $id = $field->getAttribute('id');
            $labelled = ($id && $xpath->query('.//label[@for="' . $id . '"]', $form)->length)
                || $xpath->query('ancestor::label', $field)->length
                || $field->getAttribute('aria-label') !== '';
            $this->assertTrue($labelled, 'Campo senza etichetta: ' . ($field->getAttribute('name') ?: $field->getAttribute('class')));
        }

        // Il certificato annuncia il suggerimento sul tipo e l'errore del controllo nel browser
        $certificate = $xpath->query('.//input[@id="t0-medical_certificate"]', $form)->item(0);
        $this->assertSame('t0-certificate-hint t0-certificate-error', $certificate->getAttribute('aria-describedby'));
        $this->assertSame('bday', $xpath->query('.//input[@id="t0-dob"]', $form)->item(0)->getAttribute('autocomplete'));
    }

    public function test_a_server_error_is_linked_to_its_field(): void
    {
        $errors = (new ViewErrorBag())->put('default', new MessageBag(['tickets.0.email' => "L'email non è valida."]));

        $html = $this->withSession(['errors' => $errors, '_old_input' => ['tickets' => [0 => ['email' => 'sbagliata']]]])
            ->get('/iscrizione')->assertOk()->getContent();
        $xpath = $this->dom($html);

        $email = $xpath->query('//input[@id="t0-email"]')->item(0);
        $this->assertSame('true', $email->getAttribute('aria-invalid'));
        $this->assertSame('t0-email-error', $email->getAttribute('aria-describedby'));
        $this->assertStringContainsString("L'email non è valida.", $xpath->query('//*[@id="t0-email-error"]')->item(0)->textContent);
        $this->assertFalse($xpath->query('//input[@id="t0-first_name"]')->item(0)->hasAttribute('aria-invalid'));
    }
}
