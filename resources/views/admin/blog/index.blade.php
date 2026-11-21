@include('admin.blog._head', ['pageTitle' => 'Blog'])
<body>
    <div class="wrap">
        <div class="card page-head">
            <div>
                <h1>Blog e comunicati</h1>
                <p class="sub">{{ $posts->count() }} comunicati · {{ $posts->where('published', false)->count() }} in bozza</p>
            </div>
            <div class="head-links">
                <a href="{{ route('admin.dashboard') }}">&larr; Dashboard</a>
                <a href="{{ route('admin.blog.create') }}" class="btn btn--small">Nuovo comunicato</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert--ok">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Titolo</th>
                            <th>Stato</th>
                            <th class="right">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr>
                                <td class="nowrap">{{ $post->published_on->translatedFormat('j M Y') }}</td>
                                <td>
                                    <strong>{{ $post->displayTitle() }}</strong>
                                    <div class="tiny muted">{{ \Illuminate\Support\Str::limit($post->headline() ?? $post->lead(), 110) }}</div>
                                </td>
                                <td>
                                    @if ($post->published)
                                        <span class="tag">Pubblicato</span>
                                    @else
                                        <span class="tag tag--alt">Bozza</span>
                                    @endif
                                </td>
                                <td class="right nowrap">
                                    <div class="row-actions">
                                        @if ($post->published)
                                            <a href="/blog/{{ $post->slug }}" target="_blank" class="tiny">Vedi</a>
                                        @endif
                                        <a href="{{ route('admin.blog.edit', $post) }}" class="btn btn--soft btn--small">Modifica</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="center muted">Nessun comunicato.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
