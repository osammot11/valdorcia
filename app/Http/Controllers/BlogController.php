<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;

/**
 * Comunicati stampa importati dal vecchio sito (resources/data/blog.json, vedi blog:import-old).
 */
class BlogController extends Controller
{
    public function index()
    {
        return view('blog.index', [
            'posts' => $this->posts(),
            'title' => "Blog e comunicati stampa | " . config('event.name'),
            'description' => 'Notizie e comunicati stampa di ' . config('event.name') . ' dalle edizioni passate a oggi.',
            'canonical' => url('/blog'),
        ]);
    }

    public function show(string $slug)
    {
        $posts = $this->posts();
        $index = $posts->search(fn ($p) => $p['slug'] === $slug);
        abort_if($index === false, 404);
        $post = $posts[$index];

        return view('blog.show', [
            'post' => $post,
            'newer' => $posts[$index - 1] ?? null,
            'older' => $posts[$index + 1] ?? null,
            'title' => $post['title'] . ' | ' . config('event.name'),
            'description' => $post['excerpt'],
            'canonical' => url('/blog/' . $post['slug']),
        ]);
    }

    /** Vecchi indirizzi WordPress (/comunicato-stampa-11-2021-2-2-...) verso il nuovo */
    public function legacy(string $slug)
    {
        $target = $this->data()['legacy_slugs'][$slug] ?? null;
        abort_unless($target, 404);

        return redirect('/blog/' . $target, 301);
    }

    private function posts()
    {
        return collect($this->data()['posts'])->map(function ($post) {
            $headline = collect($post['paragraphs'])->firstWhere('type', 'heading')['text'] ?? null;
            $first = collect($post['paragraphs'])->firstWhere('type', 'text')['text'] ?? '';

            // prima frase del testo: i titoli originali ("COMUNICATO STAMPA 3 – 2026") non dicono di cosa parlano
            $lead = preg_match('/^(.{30,160}?[.!?])(\s|$)/u', $first, $m) ? $m[1] : \Illuminate\Support\Str::limit($first, 140);

            return array_merge($post, [
                'title' => \Illuminate\Support\Str::ucfirst(mb_strtolower($post['title'])),
                'headline' => $headline,
                'lead' => $lead,
                'excerpt' => \Illuminate\Support\Str::limit($first, 180),
                'date_label' => Carbon::parse($post['date'])->locale('it')->translatedFormat('j F Y'),
            ]);
        })->values();
    }

    private function data(): array
    {
        return once(fn () => json_decode(file_get_contents(resource_path('data/blog.json')), true));
    }
}
