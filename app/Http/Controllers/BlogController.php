<?php

namespace App\Http\Controllers;

use App\Models\Post;

/**
 * Blog pubblico: comunicati stampa gestiti dall'admin (tabella posts).
 */
class BlogController extends Controller
{
    public function index()
    {
        return view('blog.index', [
            'posts' => $this->visible()->get(),
            'title' => 'Blog e comunicati stampa | ' . config('event.name'),
            'description' => 'Notizie e comunicati stampa di ' . config('event.name') . ' dalle edizioni passate a oggi.',
            'canonical' => url('/blog'),
        ]);
    }

    public function show(string $slug)
    {
        // pochi comunicati: si caricano tutti per avere precedente e successivo nello stesso ordine dell'elenco
        $posts = $this->visible()->get(['id', 'slug', 'title', 'published_on']);
        $index = $posts->search(fn ($p) => $p->slug === $slug);
        abort_if($index === false, 404);
        $post = Post::findOrFail($posts[$index]->id);

        return view('blog.show', [
            'post' => $post,
            'newer' => $posts[$index - 1] ?? null,
            'older' => $posts[$index + 1] ?? null,
            'title' => $post->displayTitle() . ' | ' . config('event.name'),
            'description' => $post->excerpt(),
            'canonical' => url('/blog/' . $post->slug),
        ]);
    }

    /** Vecchi indirizzi WordPress (/comunicato-stampa-11-2021-2-2-...) verso il nuovo */
    public function legacy(string $slug)
    {
        $legacy = json_decode(file_get_contents(resource_path('data/blog.json')), true)['legacy_slugs'];
        abort_unless(isset($legacy[$slug]), 404);

        return redirect('/blog/' . $legacy[$slug], 301);
    }

    private function visible()
    {
        return Post::where('published', true)->orderByDesc('published_on')->orderByDesc('id');
    }
}
