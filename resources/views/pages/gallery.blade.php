@extends('layouts.app')

@section('content')
    <main>
        <section class="page-head">
            <div class="inner">
                <span class="kicker">Foto</span>
                <h1>Trova le tue foto.</h1>
                <p class="lead">Nell'edizione 2026 i fotografi di GOTsportPhoto, lo staff di Luca Giulietti, hanno scattato a tutti i partecipanti da più punti del percorso.</p>
            </div>
        </section>

        <section>
            <div class="inner grid-2">
                <div>
                    <span class="kicker">Edizione 2026</span>
                    <h2>Con un selfie.</h2>
                </div>
                <div>
                    <ol class="steps-list">
                        <li>Le foto sono pubblicate sul portale Endupix a fine giornata.</li>
                        <li>Carichi un selfie, meglio se con casco o occhiali come in gara.</li>
                        <li>Il sistema trova da solo tutti gli scatti in cui compari.</li>
                    </ol>
                    <p class="top-gap">
                        <a class="btn" href="https://join.endu.net/pix/?editionId=102293" target="_blank" rel="noopener">Cerca le tue foto</a>
                    </p>
                    <p class="small-print">Fotografi: <a class="hyperlink" href="https://www.gotsportphoto.it/" target="_blank" rel="noopener">GOTsportPhoto</a>. Le informazioni sulle foto dell'edizione {{ config('event.edition_year') }} saranno pubblicate qui.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
