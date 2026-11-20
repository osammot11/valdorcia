@extends('layouts.app')

@section('content')
    <main>
        <article>
            <section class="page-head">
                <div class="inner post-head">
                    <span class="kicker"><a href="/blog">Blog</a> · <time datetime="{{ $post['date'] }}">{{ $post['date_label'] }}</time></span>
                    <h1>{{ $post['title'] }}</h1>
                    @if ($post['headline'])
                        <p class="lead">{{ $post['headline'] }}</p>
                    @endif
                </div>
            </section>

            <section>
                <div class="inner post-body">
                    @if ($post['image'])
                        <figure class="post-body__image">
                            <img src="{{ $post['image'] }}" alt="">
                            @if ($post['image_credit'])
                                <figcaption>{{ $post['image_credit'] }}</figcaption>
                            @endif
                        </figure>
                    @endif

                    @foreach ($post['paragraphs'] as $i => $paragraph)
                        @continue($i === 0 && $paragraph['type'] === 'heading' && $paragraph['text'] === $post['headline'])
                        @if ($paragraph['type'] === 'heading')
                            <h2>{{ $paragraph['text'] }}</h2>
                        @else
                            <p>{{ $paragraph['text'] }}</p>
                        @endif
                    @endforeach
                </div>
            </section>

            <nav class="inner post-nav" aria-label="Altri comunicati">
                @if ($older)
                    <a href="/blog/{{ $older['slug'] }}"><small>Precedente</small>{{ $older['title'] }}</a>
                @else
                    <span></span>
                @endif
                @if ($newer)
                    <a href="/blog/{{ $newer['slug'] }}" class="post-nav__next"><small>Successivo</small>{{ $newer['title'] }}</a>
                @endif
            </nav>
        </article>
    </main>
@endsection
