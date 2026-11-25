// Animazioni della home con GSAP: poche, una volta sola, mai su chi chiede di ridurre il movimento.
// 1. Apertura: testi che salgono in sequenza e foto dell'arrivo che si scopre da destra.
// 2. Percorsi: le barre tratteggiate si "percorrono" fino alla lunghezza del tracciato.
// 3. Ringraziamento: il numero dei partecipanti conta da zero.
(function () {
    const root = document.documentElement;
    if (!window.gsap || !root.classList.contains('motion')) {
        root.classList.remove('motion');
        return;
    }

    gsap.registerPlugin(ScrollTrigger);
    const ease = 'expo.out';

    // 1. Apertura
    // A fine apertura si toglie la classe .motion (lo stato iniziale nel CSS) e gli stili in linea
    const intro = gsap.timeline({
        defaults: { ease },
        onComplete: function () {
            root.classList.remove('motion');
            gsap.set('.hero [data-enter], .hero__photo img', { clearProps: 'all' });
        },
    });
    intro
        .fromTo('.hero [data-enter]',
            { opacity: 0, y: 28 },
            { opacity: 1, y: 0, duration: 1, stagger: 0.08 })
        .fromTo('.hero__photo img',
            { clipPath: 'inset(0 0 0 100%)' },
            { clipPath: 'inset(0 0 0 0%)', duration: 1.3, ease: 'expo.inOut' },
            0.15);

    // 2. Barre dei percorsi: da zero alla loro lunghezza (--len), una dopo l'altra
    // (lunghezza finale da data-len: GSAP non rilegge in modo affidabile la variabile CSS in linea)
    const bars = gsap.utils.toArray('.route__bar[data-len]');
    if (bars.length) {
        const route = gsap.timeline({
            scrollTrigger: { trigger: '.route-list', start: 'top 80%', once: true },
        });
        bars.forEach(function (bar, i) {
            route.fromTo(bar, { '--len': 0 }, { '--len': parseFloat(bar.dataset.len), duration: 1.4, ease: 'power2.inOut' }, i * 0.12);
        });
    }

    // Le foto cambiano l'altezza della pagina mentre caricano: si ricalcolano i punti di partenza
    window.addEventListener('load', function () { ScrollTrigger.refresh(); });

    // 3. Numero dei partecipanti 2026
    document.querySelectorAll('[data-count]').forEach(function (el) {
        const target = parseInt(el.dataset.count, 10);
        const counter = { value: 0 };
        el.textContent = '0';
        gsap.to(counter, {
            value: target,
            duration: 1.6,
            ease: 'power2.out',
            onUpdate: function () { el.textContent = Math.round(counter.value); },
            scrollTrigger: { trigger: el, start: 'top 85%', once: true },
        });
    });
})();
