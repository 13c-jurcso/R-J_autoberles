document.addEventListener('DOMContentLoaded', function () {
  // Fizetési felület megnyitása
  function openPaymentForm(berles_id) {
      console.log("Fizetési felület megnyitása, bérlés ID:", berles_id);
      document.getElementById('payment_berles_id').value = berles_id;
      document.getElementById('payment-modal').style.display = "block";
      document.getElementById('overlay').style.display = "block";
  }

  // Fizetési felület bezárása
  function closeModal() {
      document.getElementById('payment-modal').style.display = "none";
      document.getElementById('overlay').style.display = "none";
  }

  // Globálissá tesszük a függvényeket, hogy a PHP kód is hozzáférjen
  window.openPaymentForm = openPaymentForm;
  window.closeModal = closeModal;

  // Bezárás gomb eseménykezelője
  document.querySelectorAll('.close').forEach(function (closeButton) {
      closeButton.addEventListener('click', closeModal);
  });

  // Overlay eseménykezelője
  document.getElementById('overlay').addEventListener('click', closeModal);
});
console.log("Fizetési felület megnyitása, bérlés ID:", berles_id);