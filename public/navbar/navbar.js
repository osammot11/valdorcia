function openMenu() {
    document.getElementById("sideMenu").style.width = "min(320px, 100%)";
}

function closeMenu() {
    document.getElementById("sideMenu").style.width = "0";
}

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") closeMenu();
});
