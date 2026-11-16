<footer class="site-footer">
    <div class="site-footer__top">
        <div class="site-footer__brand">
            <img src="/images/valdorcia-gravel/logo-valdorcia-gravel.png" alt="{{ config('event.name') }}" width="154" height="148" loading="lazy">
            <div>
                <span>{{ config('event.location') }} · {{ config('event.date_label') }}</span>
            </div>
        </div>

        <nav aria-label="Evento">
            <a href="/percorsi">Percorsi</a>
            <a href="/programma">Programma</a>
            <a href="/iscrizione">Iscrizioni</a>
            <a href="/come-arrivare">Come arrivare</a>
            <a href="/gallery">Foto</a>
            <a href="/#faq">FAQ</a>
        </nav>

        <nav aria-label="Informazioni">
            <a href="/regolamento">Regolamento</a>
            <a href="/chi-siamo">Chi siamo</a>
            <a href="/partnership">Partner</a>
            <a href="/contatti">Contatti</a>
        </nav>

        <nav aria-label="Note legali">
            <a href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a>
            <a href="tel:{{ str_replace(' ', '', config('event.phone')) }}">{{ config('event.phone') }}</a>
            @foreach (config('event.social') as $name => $url)
                <a href="{{ $url }}" rel="noopener" target="_blank">{{ $name }}</a>
            @endforeach
            <a href="/privacy-policy">Privacy Policy</a>
            <a href="/cookie-policy">Cookie Policy</a>
        </nav>
    </div>

    <div class="site-footer__bottom">
        <span>© {{ config('event.edition_year') }} {{ config('event.organizer') }} · {{ config('event.organizer_address') }} · P.IVA {{ config('event.organizer_vat') }}</span>
        <a href="https://tommasogiovannoni.com">Sviluppato da tommasogiovannoni.com</a>
    </div>
</footer>
