<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\BlogPostSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_pages_and_old_wordpress_addresses(): void
    {
        $this->seed(BlogPostSeeder::class);

        $this->get('/blog')->assertOk()->assertSee('Comunicato stampa 1 – 2026');
        $this->get('/blog/comunicato-stampa-1-2026')->assertOk()->assertSee('LUCULLIANO IL VIAGGIO');
        $this->get('/blog/non-esiste')->assertNotFound();

        $this->get('/comunicato-stampa-28-01-2025-2-2-3-2')->assertRedirect('/blog/comunicato-stampa-1-2026')->assertStatus(301);
        $this->get('/comunicato-stampa-non-esiste')->assertNotFound();
        $this->get('/sponsor')->assertRedirect('/partnership');
    }

    public function test_the_seeder_does_not_overwrite_admin_changes(): void
    {
        $this->seed(BlogPostSeeder::class);
        Post::where('slug', 'comunicato-stampa-1-2026')->update(['title' => 'Titolo cambiato']);

        $this->seed(BlogPostSeeder::class);

        $this->assertSame('Titolo cambiato', Post::where('slug', 'comunicato-stampa-1-2026')->value('title'));
        $this->assertSame(38, Post::count());
    }

    public function test_the_editor_requires_login(): void
    {
        $this->get('/admin/blog')->assertRedirect('/login');
        $this->post('/admin/blog', ['title' => 'x'])->assertRedirect('/login');
    }

    public function test_admin_creates_a_draft_then_publishes_it_with_a_photo(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post('/admin/blog', [
            'title' => 'Comunicato stampa 1 – 2027',
            'published_on' => '2027-01-10',
            'body' => "## Aperte le iscrizioni 2027\r\n\r\nDa oggi ci si iscrive online.\r\nSecondo rigo dello stesso paragrafo.\r\n\r\nSecondo paragrafo.",
            'published' => '0',
        ])->assertRedirect('/admin/blog');

        $post = Post::firstWhere('slug', 'comunicato-stampa-1-2027');
        $this->assertFalse($post->published);
        $this->get('/blog/comunicato-stampa-1-2027')->assertNotFound();

        $this->actingAs($admin)->put("/admin/blog/{$post->id}", [
            'title' => 'Comunicato stampa 1 – 2027',
            'slug' => 'comunicato-stampa-1-2027',
            'published_on' => '2027-01-10',
            'body' => $post->body,
            'published' => '1',
            'image' => UploadedFile::fake()->image('foto.jpg', 1200, 800),
            'image_credit' => '© Fotografo',
        ])->assertRedirect('/admin/blog');

        $post->refresh();
        $this->assertTrue($post->published);
        $this->assertStringStartsWith('/uploads/blog/comunicato-stampa-1-2027-', $post->image);
        $this->assertFileExists(public_path(ltrim($post->image, '/')));
        $this->assertSame([
            ['type' => 'heading', 'text' => 'Aperte le iscrizioni 2027'],
            ['type' => 'text', 'text' => 'Da oggi ci si iscrive online. Secondo rigo dello stesso paragrafo.'],
            ['type' => 'text', 'text' => 'Secondo paragrafo.'],
        ], $post->paragraphs());

        $this->get('/blog/comunicato-stampa-1-2027')->assertOk()
            ->assertSee('Aperte le iscrizioni 2027')
            ->assertSee('© Fotografo')
            ->assertSee($post->image);

        // eliminando il comunicato si cancella anche la foto caricata
        $image = public_path(ltrim($post->image, '/'));
        $this->actingAs($admin)->delete("/admin/blog/{$post->id}")->assertRedirect('/admin/blog');
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertFileDoesNotExist($image);
    }

    public function test_slugs_stay_unique_and_valid(): void
    {
        $admin = User::factory()->create();
        $data = ['title' => 'Novità 2027', 'published_on' => '2027-02-01', 'body' => 'Testo.', 'published' => '1'];

        $this->actingAs($admin)->post('/admin/blog', $data);
        $this->actingAs($admin)->post('/admin/blog', $data);
        $this->assertEqualsCanonicalizing(['novita-2027', 'novita-2027-2'], Post::pluck('slug')->all());

        $this->actingAs($admin)->post('/admin/blog', $data + ['slug' => 'Con Spazi!'])->assertSessionHasErrors('slug');
        $this->actingAs($admin)->post('/admin/blog', $data + ['slug' => 'novita-2027'])->assertSessionHasErrors('slug');
    }

    protected function tearDown(): void
    {
        foreach (File::glob(public_path('uploads/blog/comunicato-stampa-1-2027-*')) as $file) {
            File::delete($file);
        }
        parent::tearDown();
    }
}
