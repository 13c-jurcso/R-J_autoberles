<?php
// Csak akkor fut, ha van üzenet, session_start() máshol van
if (!isset($_SESSION['alert_message'])) {
    return;
}

$message = htmlspecialchars($_SESSION['alert_message']);
$alert_type = isset($_SESSION['alert_type']) ? $_SESSION['alert_type'] : 'warning';
unset($_SESSION['alert_message']);
unset($_SESSION['alert_type']);
?>

<!-- Modal HTML -->
<div id="alertModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">×</span>
        <h3><?php echo $alert_type === 'success' ? 'Siker' : 'Figyelmeztetés'; ?></h3>
        <p><?php echo $message; ?></p>
        <button type="button" class="btn" onclick="closeModal()">Bezárás</button>
    </div>
</div>

<!-- Automatikus megnyitás -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openModal('alertModal');
    });
</script>