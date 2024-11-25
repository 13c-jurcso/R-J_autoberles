// A modális ablak megnyitása
function openModal(button) {
  // Adatok lekérése a gomb adat attribútumaiból
  var gyarto = button.getAttribute('data-gyarto');
  var tipus = button.getAttribute('data-típus');
  var ev = button.getAttribute('data-ev');
  var motor = button.getAttribute('data-motor');
  var ar = button.getAttribute('data-ar');
  var leiras = button.getAttribute('data-leiras');

  // A modal tartalmának frissítése
  var modalContent = document.getElementById('modal-info');
  modalContent.innerHTML = `
      <h2>${gyarto} ${tipus}</h2>
      <p><strong>Gyártási év:</strong> ${ev}</p>
      <p><strong>Motor:</strong> ${motor}</p>
      <p><strong>Ár:</strong> ${ar} Ft</p>
      <p><strong>Leírás:</strong> ${leiras}</p>
      <button type="submit" class="berles-button" name="berles">Bérlés</button> 
  `;

  // Modális ablak megjelenítése
  document.getElementById('modal').style.display = 'flex';
  document.getElementById('overlay').style.display = 'block';
  setTimeout(function() {
      document.getElementById('modal').style.opacity = 1;
  }, 10);
}

// A modális ablak bezárása
function closeModal() {
  document.getElementById('modal').style.opacity = 0;
  document.getElementById('overlay').style.display = 'none';
  setTimeout(function() {
      document.getElementById('modal').style.display = 'none';
  }, 300);
}

