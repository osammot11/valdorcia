@extends('layouts.app')

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Blog</span>
                <h1>Comunicati stampa.</h1>
                <p class="lead">Le notizie di {{ config('event.name') }} dalla prima edizione a oggi: percorsi, novità, ospiti e numeri di ogni anno.</p>
            </div>
        </section>

        <section>
            <div class="inner post-list">
                @foreach ($posts as $post)
                    <article class="post-card">
                        <a href="/blog/{{ $post['slug'] }}" class="post-card__link">
                            <div class="post-card__media">
                                @if ($post['image'])
                                    <img src="{{ $post['image'] }}" alt="" loading="lazy">
                                @endif
                            </div>
                            <div class="post-card__body">
                                <span class="kicker"><time datetime="{{ $post['date'] }}">{{ $post['date_label'] }}</time> · {{ $post['title'] }}</span>
                                <h2>{{ $post['lead'] }}</h2>
                                @if ($post['headline'])
                                    <p>{{ $post['headline'] }}</p>
                                @endif
                                <span class="post-card__more">Leggi il comunicato</span>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    </main>
@endsection
