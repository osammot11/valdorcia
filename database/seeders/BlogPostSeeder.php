<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Carica i comunicati importati dal vecchio sito (resources/data/blog.json, vedi blog:import-old).
 * Salta quelli già presenti: non sovrascrive le modifiche fatte dall'admin.
 */
class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(resource_path('data/blog.json')), true);
        $created = 0;

        // dal più vecchio al più recente: a parità di data l'id più alto è il comunicato più recente
        foreach (array_reverse($data['posts']) as $post) {
            $body = collect($post['paragraphs'])
                ->map(fn ($p) => ($p['type'] === 'heading' ? '## ' : '') . $p['text'])
                ->implode("\n\n");

            $created += Post::firstOrCreate(['slug' => $post['slug']], [
                'title' => $post['title'],
                'published_on' => $post['date'],
                'image' => $post['image'],
                'image_credit' => $post['image_credit'],
                'body' => $body,
            ])->wasRecentlyCreated ? 1 : 0;
        }

        $this->command?->info("Comunicati del vecchio sito: $created aggiunti.");
    }
}
