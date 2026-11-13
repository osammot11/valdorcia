<link rel="stylesheet" href="/navbar/navbar.css?v=7">
<script src="/navbar/navbar.js?v=3" defer></script>

<nav class="navbar" aria-label="Principale">
    <a class="logo" href="/" aria-label="{{ config('event.name') }} - Homepage">
        <img src="/images/valdorcia-gravel/logo-valdorcia-gravel.png" alt="" width="154" height="148">
    </a>

    <div class="nav-links">
        <a href="/#percorsi">Percorsi</a>
        <a href="/programma">Programma</a>
        <a href="/regolamento">Regolamento</a>
        <a href="/#faq">FAQ</a>
        <a href="/chi-siamo">Chi siamo</a>
        <a href="/contatti">Contatti</a>
        <a class="nav-cta" href="/iscrizione">Iscriviti</a>
    </div>

    <div class="navbar-actions">
        <button type="button" class="hamburger" onclick="openMenu()" aria-label="Apri il menu">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>
</nav>

<div id="sideMenu" class="side-menu">
    <a href="javascript:void(0)" class="closebtn" onclick="closeMenu()" aria-label="Chiudi il menu">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </a>
    <a href="/" onclick="closeMenu()">Home</a>
    <a href="/#percorsi" onclick="closeMenu()">Percorsi</a>
    <a href="/programma" onclick="closeMenu()">Programma</a>
    <a href="/#faq" onclick="closeMenu()">FAQ</a>
    <a href="/chi-siamo" onclick="closeMenu()">Chi siamo</a>
    <a href="/iscrizione">Iscrizione</a>
    <a href="/regolamento">Regolamento</a>
    <a href="/partnership">Partner</a>
    <a href="/contatti">Contatti</a>
</div>
