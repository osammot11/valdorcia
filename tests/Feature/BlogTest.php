<?php

namespace Tests\Feature;

use Tests\TestCase;

class BlogTest extends TestCase
{
    public function test_blog_pages_and_old_wordpress_addresses(): void
    {
        $this->get('/blog')->assertOk()->assertSee('Comunicato stampa 1 – 2026');
        $this->get('/blog/comunicato-stampa-1-2026')->assertOk()->assertSee('LUCULLIANO IL VIAGGIO');
        $this->get('/blog/non-esiste')->assertNotFound();

        $this->get('/comunicato-stampa-28-01-2025-2-2-3-2')->assertRedirect('/blog/comunicato-stampa-1-2026')->assertStatus(301);
        $this->get('/comunicato-stampa-non-esiste')->assertNotFound();
        $this->get('/sponsor')->assertRedirect('/partnership');
    }
}
