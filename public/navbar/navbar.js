// Menu laterale (mobile): apertura, chiusura, focus e tasto Esc

function menuParts() {
    return {
        menu: document.getElementById("sideMenu"),
        toggle: document.querySelector(".hamburger"),
    };
}

function openMenu() {
    const { menu, toggle } = menuParts();
    menu.classList.add("is-open");
    document.body.classList.add("menu-open");
    toggle.setAttribute("aria-expanded", "true");
    // Il focus entra nel menu dopo che è diventato visibile
    requestAnimationFrame(() => menu.querySelector(".closebtn").focus());
}

function closeMenu() {
    const { menu, toggle } = menuParts();
    if (!menu.classList.contains("is-open")) return;
    const hadFocus = menu.contains(document.activeElement);
    menu.classList.remove("is-open");
    document.body.classList.remove("menu-open");
    toggle.setAttribute("aria-expanded", "false");
    if (hadFocus) toggle.focus();
}

document.addEventListener("keydown", function (event) {
    const { menu } = menuParts();
    if (!menu || !menu.classList.contains("is-open")) return;

    if (event.key === "Escape") {
        closeMenu();
        return;
    }

    // Con il menu aperto Tab gira solo tra le sue voci
    if (event.key === "Tab") {
        const items = menu.querySelectorAll("button, a[href]");
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
