<?php

namespace App\Console\Commands;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Importa i comunicati stampa dal vecchio sito WordPress (API REST) in resources/data/blog.json,
 * con le immagini salvate in public/images/blog. Si può rilanciare: riscrive tutto da capo.
 * Il testo dei comunicati resta quello originale dell'organizzazione; si pulisce solo l'HTML.
 */
class ImportOldBlog extends Command
{
    protected $signature = 'blog:import-old {--source=https://valdorciagravel.it}';

    protected $description = 'Importa i comunicati stampa dal vecchio sito WordPress';

    private const LOGO = 'VAL-DORCIA-GRAVEL-marrone-Montalcino';

    public function handle(): int
    {
        ini_set('memory_limit', '512M'); // alcune foto originali sono molto grandi
        $source = rtrim($this->option('source'), '/');
        $posts = Http::timeout(60)->get("$source/wp-json/wp/v2/posts", ['per_page' => 100, '_embed' => 'wp:featuredmedia'])->throw()->json();

        // Lo stesso comunicato a volte è pubblicato due volte: si tiene la versione più recente
        $byTitle = [];
        $legacy = [];
        foreach (collect($posts)->sortByDesc('id') as $post) {
            $title = html_entity_decode($post['title']['rendered'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $slug = Str::slug(str_replace('–', '-', $title));
            $byTitle[$slug] ??= $post + ['clean_title' => $title];
            $legacy[$post['slug']] ??= $slug;
        }

        $imageDir = public_path('images/blog');
        File::ensureDirectoryExists($imageDir);

        $items = [];
        foreach ($byTitle as $slug => $post) {
            [$paragraphs, $inlineImage, $credit] = $this->clean($post['content']['rendered']);
            $featured = $post['_embedded']['wp:featuredmedia'][0]['source_url'] ?? null;
            $imageUrl = $featured ?: $inlineImage;

            $items[] = [
                'slug' => $slug,
                'title' => $post['clean_title'],
                'date' => substr($post['date'], 0, 10),
                'image' => $imageUrl ? $this->downloadImage($imageUrl, "$imageDir/$slug.jpg", "/images/blog/$slug.jpg") : null,
                'image_credit' => $credit,
                'paragraphs' => $paragraphs,
            ];
            $this->line("  $slug ({$items[array_key_last($items)]['date']}) · " . count($paragraphs) . ' paragrafi');
        }

        usort($items, fn ($a, $b) => strcmp($b['date'], $a['date']));

        File::put(resource_path('data/blog.json'), json_encode([
            'posts' => $items,
            'legacy_slugs' => $legacy,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");

        $this->info(count($items) . ' comunicati importati (' . (count($posts) - count($items)) . ' doppioni saltati).');

        return self::SUCCESS;
    }

    /**
     * Trasforma l'HTML di WordPress in paragrafi di testo. Gli a capo dentro un paragrafo vengono dal
     * copia-incolla dei PDF: diventano spazi, salvo quando chiudono una frase.
     *
     * @return array{0: list<array{type: string, text: string}>, 1: ?string, 2: ?string}
     */
    private function clean(string $html): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8"?><div id="root">' . $html . '</div>');
        $xpath = new DOMXPath($dom);

        $image = null;
        foreach ($xpath->query('//img') as $img) {
            $src = $img->getAttribute('src');
            if (! str_contains($src, self::LOGO) && ! $image) {
                // dalla miniatura (es. -1024x683.jpg) all'originale
                $image = preg_replace('/-\d+x\d+(\.\w+)$/', '$1', $src);
            }
        }
        $credit = trim($xpath->query('//figcaption')->item(0)?->textContent ?? '') ?: null;

        foreach ($xpath->query('//script|//style|//iframe|//figure|//img') as $node) {
            $node->parentNode->removeChild($node);
        }

        $paragraphs = [];
        foreach ($xpath->query('//p|//h1|//h2|//h3|//h4|//li|//blockquote') as $block) {
            if ($xpath->query('ancestor::p|ancestor::li|ancestor::blockquote', $block)->length) {
                continue;
            }
            $text = $this->text($block);
            foreach (preg_split('/\n{2,}/', $text) as $chunk) {
                $chunk = trim(preg_replace('/[ \t]+/u', ' ', $chunk));
                if ($chunk === '' || preg_match('/^(www\.)?valdorciagravel\.it$/i', $chunk)) {
                    continue;
                }
                $isHeading = $block instanceof DOMElement
                    && (preg_match('/^h\d$/', $block->tagName) || $this->allBold($block))
                    && mb_strlen($chunk) < 160;
                $paragraphs[] = ['type' => $isHeading ? 'heading' : 'text', 'text' => $chunk];
            }
        }

        return [$paragraphs, $image, $credit];
    }

    private function text(DOMNode $node): string
    {
        $out = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'br') {
                // a capo dopo fine frase: nuovo paragrafo; altrimenti è solo la riga del PDF
                $out = preg_match('/[.!?:;»”"]\s*$/u', $out) ? rtrim($out) . "\n\n" : rtrim($out) . ' ';
            } elseif ($child->nodeType === XML_TEXT_NODE) {
                $out .= str_replace(["\r", "\n"], ' ', $child->textContent);
            } else {
                $out .= $this->text($child);
            }
        }

        return html_entity_decode(str_replace("\u{00A0}", ' ', $out), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function allBold(DOMElement $block): bool
    {
        $length = fn (string $s) => mb_strlen(preg_replace('/\s+/u', '', $s));
        $bold = '';
        foreach ((new DOMXPath($block->ownerDocument))->query('.//strong|.//b', $block) as $b) {
            $bold .= $b->textContent;
        }

        return $length($block->textContent) > 0 && $length($bold) >= $length($block->textContent) * 0.9;
    }

    private function downloadImage(string $url, string $path, string $public): ?string
    {
        $response = Http::timeout(60)->get($url);
        if (! $response->successful()) {
            $this->warn("  immagine non scaricata: $url");

            return null;
        }

        $image = @imagecreatefromstring($response->body());
        if (! $image) {
            return null;
        }

        // massimo 1200 px di larghezza, JPEG
        $width = imagesx($image);
        if ($width > 1200) {
            $image = imagescale($image, 1200);
        }
        $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagejpeg($canvas, $path, 80);
        imagedestroy($image);
        imagedestroy($canvas);

        return $public;
    }
}
