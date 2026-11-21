@include('admin.blog._head', ['pageTitle' => $post->exists ? 'Modifica comunicato' : 'Nuovo comunicato'])
<body>
    <div class="wrap wrap--narrow">
        <div class="card page-head">
            <div>
                <h1>{{ $post->exists ? 'Modifica comunicato' : 'Nuovo comunicato' }}</h1>
                @if ($post->exists)
                    <p class="sub">Indirizzo: <span class="code">/blog/{{ $post->slug }}</span></p>
                @endif
            </div>
            <a href="{{ route('admin.blog.index') }}">&larr; Torna al blog</a>
        </div>

        <div class="card card--roomy">
            <form action="{{ $post->exists ? route('admin.blog.update', $post) : route('admin.blog.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if ($post->exists) @method('PUT') @endif

                @if ($errors->any())
                    <div class="alert alert--error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <div class="grid grid--2">
                    <div class="span-2">
                        <label class="label--small" for="title">Titolo</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $post->title) }}" placeholder="Comunicato stampa 1 – {{ config('event.edition_year') }}" required>
                    </div>
                    <div>
                        <label class="label--small" for="published_on">Data</label>
                        <input type="date" id="published_on" name="published_on" value="{{ old('published_on', $post->published_on?->toDateString()) }}" required>
                    </div>
                    <div>
                        <label class="label--small" for="slug">Indirizzo (facoltativo)</label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug', $post->slug) }}" placeholder="si crea dal titolo">
                    </div>
                </div>

                <h3 class="section-title">Testo</h3>
                <p class="small muted">Lascia una riga vuota tra un paragrafo e l'altro. Una riga che inizia con <span class="code">##</span> diventa un sottotitolo: se è la prima, compare in grande sotto il titolo.</p>
                <textarea name="body" rows="22" class="body-editor" required placeholder="## Sottotitolo del comunicato&#10;&#10;Primo paragrafo...&#10;&#10;Secondo paragrafo...">{{ old('body', $post->body) }}</textarea>

                <h3 class="section-title">Foto</h3>
                <div class="grid grid--2">
                    <div>
                        @if ($post->image)
                            <img src="{{ $post->image }}" alt="" class="post-thumb">
                            <label class="inline-check"><input type="checkbox" name="remove_image" value="1"> Togli la foto</label>
                        @else
                            <p class="small muted">Nessuna foto.</p>
                        @endif
                    </div>
                    <div>
                        <label class="label--small" for="image">{{ $post->image ? 'Sostituisci la foto' : 'Carica una foto' }}</label>
                        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <p class="tiny muted">JPG, PNG o WebP, massimo 8 MB. Meglio orizzontale.</p>
                        <label class="label--small" for="image_credit" style="margin-top: 14px;">Crediti della foto (facoltativo)</label>
                        <input type="text" id="image_credit" name="image_credit" value="{{ old('image_credit', $post->image_credit) }}" placeholder="© Nome del fotografo">
                    </div>
                </div>

                <label class="checks publish-toggle inline-check">
                    <input type="hidden" name="published" value="0">
                    <input type="checkbox" name="published" value="1" @checked(old('published', $post->published))>
                    Pubblicato sul sito (se lo togli resta in bozza, visibile solo qui)
                </label>

                <button type="submit" class="btn btn--block btn--big">{{ $post->exists ? 'Salva modifiche' : 'Salva comunicato' }}</button>
            </form>

            @if ($post->exists)
                <form action="{{ route('admin.blog.destroy', $post) }}" method="POST" class="delete-post" onsubmit="return confirm('Eliminare definitivamente questo comunicato?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="link-btn link-btn--danger">Elimina il comunicato</button>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
