<link rel="stylesheet" href="/navbar/navbar.css?v=9">
<script src="/navbar/navbar.js?v=4" defer></script>

<nav class="navbar" aria-label="Principale">
    <a class="logo" href="/" aria-label="{{ config('event.name') }} - Homepage">
        <img src="/images/valdorcia-gravel/logo-valdorcia-gravel.png" alt="" width="154" height="148">
    </a>

    <div class="nav-links">
        <a href="/percorsi">Percorsi</a>
        <a href="/programma">Programma</a>
        <a href="/regolamento">Regolamento</a>
        <a href="/#faq">FAQ</a>
        <a href="/chi-siamo">Chi siamo</a>
        <a href="/contatti">Contatti</a>
    </div>

    {{-- Fuori da .nav-links: resta visibile anche su mobile, dove i link vanno nel menu laterale --}}
    <div class="navbar-actions">
        <a class="nav-cta" href="/iscrizione">Iscriviti</a>
        <button type="button" class="hamburger" onclick="openMenu()" aria-label="Apri il menu" aria-expanded="false" aria-controls="sideMenu">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>
</nav>

{{-- Chiuso: fuori schermo e con visibility: hidden, quindi i link non ricevono il focus --}}
<div class="side-menu__backdrop" onclick="closeMenu()" aria-hidden="true"></div>
<nav id="sideMenu" class="side-menu" aria-label="Menu">
    <button type="button" class="closebtn" onclick="closeMenu()" aria-label="Chiudi il menu">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
    {{-- Stesso ordine dei link desktop; Iscriviti è sempre nella barra in alto --}}
    <a href="/" onclick="closeMenu()">Home</a>
    <a href="/percorsi" onclick="closeMenu()">Percorsi</a>
    <a href="/programma" onclick="closeMenu()">Programma</a>
    <a href="/regolamento" onclick="closeMenu()">Regolamento</a>
    <a href="/#faq" onclick="closeMenu()">FAQ</a>
    <a href="/chi-siamo" onclick="closeMenu()">Chi siamo</a>
    <a href="/contatti" onclick="closeMenu()">Contatti</a>
    <a href="/partnership" onclick="closeMenu()">Sponsor</a>
    <a href="/blog" onclick="closeMenu()">Blog</a>
</nav>
