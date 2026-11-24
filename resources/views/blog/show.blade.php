@extends('layouts.app')

@php
    // La prima frase della notizia fa da titolo e il titolo ufficiale ("Comunicato stampa 2 – 2026") va nell'occhiello.
    // Se la frase è troppo lunga resta il titolo ufficiale e il primo paragrafo sale in apertura come introduzione.
    $sentence = $post->headlineSentence();
    $intro = $sentence ? null : ($post->firstText() ?: null);
    $size = $post->imageSize();
    $leadUsed = false;
@endphp

@section('content')
    <main>
        <article>
            <section class="page-head">
                <div class="inner post-head">
                    <span class="kicker"><a href="/blog">Blog</a> · <time datetime="{{ $post->published_on->toDateString() }}">{{ $post->published_on->translatedFormat('j F Y') }}</time>@if ($sentence) · {{ $post->displayTitle() }}@endif</span>
                    @if ($sentence)
                        <h1 class="post-title">{{ $sentence }}</h1>
                    @else
                        <h1>{{ $post->displayTitle() }}</h1>
                        @if ($intro)
                            <p class="lead post-intro">{{ $intro }}</p>
                        @endif
                    @endif
                </div>
            </section>

            <section>
                <div class="inner post-body">
                    @if ($post->isFromPastEdition())
                        <p class="post-past">Questo comunicato riguarda un'edizione passata. L'edizione {{ config('event.edition_year') }} si svolge il {{ config('event.date_label') }} a {{ config('event.location') }}: <a class="hyperlink" href="/iscrizione">quote e iscrizioni</a>.</p>
                    @endif

                    @if ($post->image)
                        <figure class="post-body__image">
                            <img src="{{ $post->image }}" alt="{{ $post->displayTitle() }}" @if ($size) width="{{ $size['width'] }}" height="{{ $size['height'] }}" @endif>
                            @if ($post->image_credit)
                                <figcaption>{{ $post->image_credit }}</figcaption>
                            @endif
                        </figure>
                    @endif

                    @foreach ($post->paragraphs() as $paragraph)
                        @if ($paragraph['type'] === 'heading')
                            <h2>{{ $paragraph['text'] }}</h2>
                        @else
                            @php
                                // La frase già usata come titolo non si ripete all'inizio del testo
                                // ... e il primo paragrafo, se è già in apertura
                                $text = $paragraph['text'];
                                if (! $leadUsed && $sentence && str_starts_with($text, $sentence)) {
                                    $text = trim(mb_substr($text, mb_strlen($sentence)));
                                } elseif (! $leadUsed && $intro === $text) {
                                    $text = '';
                                }
                                $leadUsed = true;
                            @endphp
                            @if ($text !== '')
                                <p>{{ $text }}</p>
                            @endif
                        @endif
                    @endforeach

                    <p class="post-cta">
                        <a class="btn" href="/iscrizione">Quote e iscrizioni {{ config('event.edition_year') }}</a>
                    </p>
                </div>
            </section>

            <nav class="inner post-nav" aria-label="Altri comunicati">
                @if ($older)
                    <a href="/blog/{{ $older->slug }}"><small>Precedente</small>{{ $older->displayTitle() }}</a>
                @else
                    <span></span>
                @endif
                @if ($newer)
                    <a href="/blog/{{ $newer->slug }}" class="post-nav__next"><small>Successivo</small>{{ $newer->displayTitle() }}</a>
                @endif
            </nav>
        </article>
    </main>
@endsection
