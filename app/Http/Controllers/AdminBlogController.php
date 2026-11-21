<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

/**
 * Editor dei comunicati stampa nell'area admin. Le foto caricate finiscono in public/uploads/blog.
 */
class AdminBlogController extends Controller
{
    public function index()
    {
        return view('admin.blog.index', [
            'posts' => Post::orderByDesc('published_on')->orderByDesc('id')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.blog.form', ['post' => new Post(['published_on' => now(), 'published' => true])]);
    }

    public function store(Request $request)
    {
        $post = new Post();
        $this->save($request, $post);

        return redirect()->route('admin.blog.index')->with('success', "Comunicato \"{$post->title}\" " . ($post->published ? 'pubblicato.' : 'salvato come bozza.'));
    }

    public function edit(Post $post)
    {
        return view('admin.blog.form', ['post' => $post]);
    }

    public function update(Request $request, Post $post)
    {
        $this->save($request, $post);

        return redirect()->route('admin.blog.index')->with('success', "Comunicato \"{$post->title}\" aggiornato.");
    }

    public function destroy(Post $post)
    {
        $this->deleteUploadedImage($post->image);
        $post->delete();

        return redirect()->route('admin.blog.index')->with('success', "Comunicato \"{$post->title}\" eliminato.");
    }

    private function save(Request $request, Post $post): void
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', Rule::unique('posts', 'slug')->ignore($post->id)],
            'published_on' => ['required', 'date'],
            'body' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'image_credit' => ['nullable', 'string', 'max:200'],
        ], [
            'slug.regex' => 'L\'indirizzo può contenere solo lettere minuscole, numeri e trattini (es. comunicato-stampa-1-2027).',
            'slug.unique' => 'Esiste già un comunicato con questo indirizzo.',
        ], [
            'title' => 'titolo',
            'slug' => 'indirizzo',
            'published_on' => 'data',
            'body' => 'testo',
            'image' => 'foto',
            'image_credit' => 'crediti della foto',
        ]);

        $post->fill([
            'title' => $data['title'],
            'slug' => ($data['slug'] ?? null) ?: ($post->slug ?? Post::uniqueSlug($data['title'], $post->id)),
            'published_on' => $data['published_on'],
            'body' => str_replace("\r\n", "\n", $data['body']),
            'image_credit' => $data['image_credit'] ?? null,
            'published' => $request->boolean('published'),
        ]);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            $this->deleteUploadedImage($post->image);
            $post->image = null;
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = $post->slug . '-' . now()->format('YmdHis') . '.' . $file->extension();
            $file->move(public_path('uploads/blog'), $name);
            $post->image = '/uploads/blog/' . $name;
        }

        $post->save();
    }

    /** Cancella solo le foto caricate dall'admin, non quelle importate dal vecchio sito (che stanno nel repository) */
    private function deleteUploadedImage(?string $path): void
    {
        if ($path && str_starts_with($path, '/uploads/blog/')) {
            File::delete(public_path(ltrim($path, '/')));
        }
    }
}
