function openModal() {
  document.getElementById("modal").style.display = "flex"; // A modális ablak megjelenítése
  document.getElementById("overlay").style.display = "block"; // A homályosító réteg megjelenítése
  document.body.style.overflow = "hidden"; // A görgetés letiltása, hogy a háttér ne görgessen
  setTimeout(function() {
    document.getElementById("modal").style.opacity = 1; // Áttűnés effektus hozzáadása
    document.getElementById("overlay").style.opacity = 1;
  }, 10); // Kis késleltetés az animáció elindulásához
}

// A modális ablak bezárása
function closeModal() {
  document.getElementById("modal").style.opacity = 0; // Az áttűnés eltüntetése
  document.getElementById("overlay").style.opacity = 0;
  setTimeout(function() {
    document.getElementById("modal").style.display = "none"; // A modális ablak eltüntetése
    document.getElementById("overlay").style.display = "none"; // A homályosító réteg eltüntetése
    document.body.style.overflow = "auto"; // A görgetés engedélyezése újra
  }, 300); // Késleltetés, hogy az animáció befejeződjön, mielőtt eltűnik
}

document.querySelector(".menu-toggle").addEventListener("click", function () {
document.querySelector("header").classList.toggle("menu-opened");
});
