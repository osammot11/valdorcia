@if (!request()->cookie('site_cookie_consent'))
    {{-- Rifiuta e Accetta hanno lo stesso peso, come chiedono le linee guida del Garante --}}
    <section id="cookieBanner" class="cookie-banner" aria-labelledby="cookieBannerTitle">
        <div class="cookie-banner__content">
            <div class="cookie-banner__text">
                <p class="cookie-title" id="cookieBannerTitle">Questo sito usa i cookie</p>
                <p class="cookie-banner__copy">
                    Usiamo i cookie tecnici necessari al sito; gli altri solo con il tuo consenso. Dettagli nella
                    <a href="/cookie-policy" class="hyperlink">Cookie Policy</a> e nella
                    <a href="/privacy-policy" class="hyperlink">Privacy Policy</a>.
                </p>
            </div>

            <div class="cookie-banner__actions">
                <button type="button" class="cookie-choice" id="rejectAllCookies">Rifiuta</button>
                <button type="button" class="cookie-choice" id="acceptAllCookies">Accetta</button>
                <button type="button" class="cookie-link" id="openCookieSettings" aria-haspopup="dialog">Personalizza</button>
            </div>
        </div>
    </section>

    <div id="cookieModal" class="cookie-modal" aria-hidden="true">
        <div class="cookie-modal__overlay" id="cookieModalOverlay"></div>

        <div class="cookie-modal__box" role="dialog" aria-modal="true" aria-labelledby="cookieModalTitle">
            <div class="cookie-modal__header">
                <h2 class="cookie-title" id="cookieModalTitle">Preferenze cookie</h2>
                <button type="button" class="cookie-modal__close" id="closeCookieSettings" aria-label="Chiudi le preferenze cookie"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="cookie-modal__body">
                <p>Puoi scegliere quali categorie di cookie autorizzare. I cookie tecnici sono sempre attivi perché necessari al corretto funzionamento del sito.</p>

                <div class="cookie-option top-margin-large">
                    <div>
                        <p class="cookie-option__title">Cookie tecnici</p>
                        <p class="top-margin-small">Sempre attivi. Servono al funzionamento del sito.</p>
                    </div>
                    <label class="cookie-switch cookie-switch--disabled">
                        <input type="checkbox" checked disabled aria-label="Cookie tecnici, sempre attivi">
                        <span class="cookie-slider"></span>
                    </label>
                </div>

                <div class="cookie-option top-margin-large">
                    <div>
                        <p class="cookie-option__title">Cookie statistici</p>
                        <p class="top-margin-small">Ci aiutano a capire come viene usato il sito.</p>
                    </div>
                    <label class="cookie-switch">
                        <input type="checkbox" id="analyticsCookies" aria-label="Cookie statistici">
                        <span class="cookie-slider"></span>
                    </label>
                </div>

                <div class="cookie-option top-margin-large">
                    <div>
                        <p class="cookie-option__title">Cookie marketing</p>
                        <p class="top-margin-small">Utilizzati per tracciamento, advertising o remarketing.</p>
                    </div>
                    <label class="cookie-switch">
                        <input type="checkbox" id="marketingCookies" aria-label="Cookie marketing">
                        <span class="cookie-slider"></span>
                    </label>
                </div>
            </div>

            <div class="cookie-modal__footer">
                <button type="button" class="cookie-choice" id="saveCustomCookies">Salva preferenze</button>
                <button type="button" class="cookie-choice" id="acceptAllFromModal">Accetta tutto</button>
            </div>
        </div>
    </div>
@endif

<script>
(function () {
    const consentCookieName = 'site_cookie_consent';
    const consentDurationDays = 180;

    function setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + date.toUTCString() + '; path=/; SameSite=Lax';
    }

    function getCookie(name) {
        const nameEQ = name + '=';
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            const c = ca[i].trim();
            if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length));
        }
        return null;
    }

    function hideBannerAndModal() {
        const banner = document.getElementById('cookieBanner');
        const modal = document.getElementById('cookieModal');

        if (banner) banner.remove();
        if (modal) modal.remove();
    }

    function saveConsent(preferences) {
        setCookie(consentCookieName, JSON.stringify(preferences), consentDurationDays);
        window.cookieConsent = preferences;
        hideBannerAndModal();
    }

    let returnFocus = null;

    function openModal() {
        const modal = document.getElementById('cookieModal');
        if (!modal) return;
        returnFocus = document.activeElement;
        modal.classList.add('is-active');
        modal.setAttribute('aria-hidden', 'false');
        document.getElementById('closeCookieSettings').focus();
    }

    function closeModal() {
        const modal = document.getElementById('cookieModal');
        if (!modal || !modal.classList.contains('is-active')) return;
        modal.classList.remove('is-active');
        modal.setAttribute('aria-hidden', 'true');
        if (returnFocus) returnFocus.focus();
    }

    // Esc chiude le preferenze; Tab resta dentro la finestra finché è aperta
    document.addEventListener('keydown', function (event) {
        const modal = document.getElementById('cookieModal');
        if (!modal || !modal.classList.contains('is-active')) return;
        if (event.key === 'Escape') {
            closeModal();
            return;
        }
        if (event.key === 'Tab') {
            const items = [...modal.querySelectorAll('button, input:not([disabled]), a[href]')];
            const first = items[0];
            const last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const existingConsent = getCookie(consentCookieName);

        if (existingConsent) {
            try {
                window.cookieConsent = JSON.parse(existingConsent);
            } catch (e) {}
        } else {
            setTimeout(() => {
                const banner = document.getElementById('cookieBanner');
                if (banner) banner.classList.add('is-visible');
            }, 1000);
        }

        const acceptAllBtn = document.getElementById('acceptAllCookies');
        const rejectAllBtn = document.getElementById('rejectAllCookies');
        const openSettingsBtn = document.getElementById('openCookieSettings');
        const closeSettingsBtn = document.getElementById('closeCookieSettings');
        const saveCustomBtn = document.getElementById('saveCustomCookies');
        const acceptAllFromModalBtn = document.getElementById('acceptAllFromModal');
        const overlay = document.getElementById('cookieModalOverlay');
        const analyticsCheckbox = document.getElementById('analyticsCookies');
        const marketingCheckbox = document.getElementById('marketingCookies');

        if (acceptAllBtn) {
            acceptAllBtn.addEventListener('click', function () {
                saveConsent({ necessary: true, analytics: true, marketing: true });
            });
        }

        if (rejectAllBtn) {
            rejectAllBtn.addEventListener('click', function () {
                saveConsent({ necessary: true, analytics: false, marketing: false });
            });
        }

        if (openSettingsBtn) openSettingsBtn.addEventListener('click', openModal);
        if (closeSettingsBtn) closeSettingsBtn.addEventListener('click', closeModal);
        if (overlay) overlay.addEventListener('click', closeModal);

        if (saveCustomBtn) {
            saveCustomBtn.addEventListener('click', function () {
                saveConsent({
                    necessary: true,
                    analytics: analyticsCheckbox ? analyticsCheckbox.checked : false,
                    marketing: marketingCheckbox ? marketingCheckbox.checked : false
                });
            });
        }

        if (acceptAllFromModalBtn) {
            acceptAllFromModalBtn.addEventListener('click', function () {
                saveConsent({ necessary: true, analytics: true, marketing: true });
            });
        }
    });
})();
</script>
