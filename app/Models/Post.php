<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Comunicato stampa del blog. Il testo è semplice: paragrafi separati da una riga vuota,
 * le righe che iniziano con "## " sono sottotitoli.
 */
class Post extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published_on' => 'date',
        'published' => 'boolean',
    ];

    /** @return list<array{type: string, text: string}> */
    public function paragraphs(): array
    {
        $blocks = preg_split('/\R\s*\R/u', trim((string) $this->body));

        return collect($blocks)
            ->map(fn ($b) => trim(preg_replace('/\s*\R\s*/u', ' ', $b)))
            ->filter()
            ->map(fn ($b) => str_starts_with($b, '## ')
                ? ['type' => 'heading', 'text' => trim(substr($b, 3))]
                : ['type' => 'text', 'text' => $b])
            ->values()
            ->all();
    }

    /** Primo sottotitolo, se il comunicato si apre con uno */
    public function headline(): ?string
    {
        $first = $this->paragraphs()[0] ?? null;

        return $first && $first['type'] === 'heading' ? $first['text'] : null;
    }

    public function firstText(): string
    {
        return collect($this->paragraphs())->firstWhere('type', 'text')['text'] ?? '';
    }

    /** Prima frase del testo, per le schede dell'elenco */
    public function lead(): string
    {
        $first = $this->firstText();

        return preg_match('/^(.{30,160}?[.!?])(\s|$)/u', $first, $m) ? $m[1] : Str::limit($first, 140);
    }

    public function excerpt(): string
    {
        return Str::limit($this->firstText(), 180);
    }

    public function displayTitle(): string
    {
        // i titoli importati sono tutti maiuscoli ("COMUNICATO STAMPA 3 – 2026")
        return $this->title === mb_strtoupper($this->title) ? Str::ucfirst(mb_strtolower($this->title)) : $this->title;
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug(str_replace('–', '-', $title)) ?: 'comunicato';
        $slug = $base;
        for ($i = 2; static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists(); $i++) {
            $slug = "$base-$i";
        }

        return $slug;
    }
}
