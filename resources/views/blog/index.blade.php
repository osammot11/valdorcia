@extends('layouts.app')

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Blog</span>
                <h1>Comunicati stampa.</h1>
                <p class="lead">Le notizie di {{ config('event.name') }} dalla prima edizione a oggi: percorsi, novità, ospiti e numeri di ogni anno. Quelle dell'edizione {{ config('event.edition_year') }} arriveranno qui.</p>
            </div>
        </section>

        <section>
            <div class="inner post-years">
                {{-- Archivio diviso per anno di pubblicazione --}}
                @foreach ($posts->groupBy(fn ($post) => $post->published_on->year) as $year => $yearPosts)
                    <section class="post-year" aria-labelledby="anno-{{ $year }}">
                        <h2 class="post-year__title" id="anno-{{ $year }}">{{ $year }}</h2>
                        <div class="post-list">
                            @foreach ($yearPosts as $post)
                                <article class="post-card">
                                    <a href="/blog/{{ $post->slug }}" class="post-card__link {{ $post->image ? '' : 'post-card__link--text' }}">
                                        @if ($post->image)
                                            @php $size = $post->imageSize(); @endphp
                                            <div class="post-card__media">
                                                <img src="{{ $post->image }}" alt="" loading="lazy" @if ($size) width="{{ $size['width'] }}" height="{{ $size['height'] }}" @endif>
                                            </div>
                                        @endif
                                        <div class="post-card__body">
                                            <span class="kicker"><time datetime="{{ $post->published_on->toDateString() }}">{{ $post->published_on->translatedFormat('j F Y') }}</time> · {{ $post->displayTitle() }}</span>
                                            <h3>{{ $post->lead() }}</h3>
                                            <span class="post-card__more">Leggi il comunicato</span>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </section>
    </main>
@endsection
