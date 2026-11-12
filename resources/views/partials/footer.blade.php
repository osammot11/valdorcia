<footer class="site-footer">
    <div class="site-footer__top">
        <div class="site-footer__brand">
            <img src="/images/valdorcia-gravel/logo.svg" alt="" width="56" height="56" loading="lazy">
            <div>
                <strong>{{ config('event.name') }}</strong>
                <span>{{ config('event.location') }} · {{ config('event.date_label') }}</span>
            </div>
        </div>

        <nav aria-label="Evento">
            <a href="/#percorsi">Percorsi</a>
            <a href="/#territorio">Il territorio</a>
            <a href="/iscrizione">Iscrizioni</a>
        </nav>

        <nav aria-label="Informazioni">
            <a href="/regolamento">Regolamento</a>
            <a href="/partnership">Partner</a>
            <a href="/contatti">Contatti</a>
        </nav>

        <nav aria-label="Note legali">
            <a href="mailto:{{ config('event.email') }}">{{ config('event.email') }}</a>
            <a href="/privacy-policy">Privacy Policy</a>
            <a href="/cookie-policy">Cookie Policy</a>
        </nav>
    </div>

    <div class="site-footer__bottom">
        <span>© {{ config('event.edition_year') }} {{ config('event.organizer') }}</span>
        <a href="https://tommasogiovannoni.com">Sviluppato da tommasogiovannoni.com</a>
    </div>
</footer>
