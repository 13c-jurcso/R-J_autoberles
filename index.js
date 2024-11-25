// Modal megjelenítése
function openModal(modalId) {
    document.getElementById(modalId).style.display = "flex";
}

// Modal bezárása
function closeModal() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = "none";
    });
}
document.querySelector(".menu-toggle").addEventListener("click", function () {
    document.querySelector("header").classList.toggle("menu-opened");
});