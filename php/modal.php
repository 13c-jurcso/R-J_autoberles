<?php
session_start();

// Ellenőrizzük, hogy van-e üzenet a session-ben
if (isset($_SESSION['alert_message'])) {
    $message = $_SESSION['alert_message'];
    $alert_type = isset($_SESSION['alert_type']) ? $_SESSION['alert_type'] : 'warning';
    unset($_SESSION['alert_message']);
    unset($_SESSION['alert_type']);
} else {
    return;
}
?>

<!-- Modal HTML -->
<div id="alertModal" class="modal" style="display: block;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header <?php echo $alert_type === 'success' ? 'bg-success text-white' : 'bg-warning text-dark'; ?>">
                <h5 class="modal-title"><?php echo $alert_type === 'success' ? 'Siker' : 'Figyelmeztetés'; ?></h5>
                <button type="button" class="btn-close" onclick="closeAlertModal()" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="closeAlertModal()">Bezárás</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript a modal bezárásához -->
<script>
    function closeAlertModal() {
        var modal = document.getElementById('alertModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Escape billentyű támogatása
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAlertModal();
        }
    });
</script>

<style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
    }
    .modal-dialog {
        margin: 0 auto;
        max-width: 500px;
        top: 50%;
        transform: translateY(-50%);
    }
    .modal-content {
        border-radius: 10px;
    }
    .modal-header.bg-success {
        background-color: #28a745 !important;
    }
    .modal-header.bg-warning {
        background-color: #ffc107 !important;
    }
    .modal-footer {
        justify-content: center;
    }
    .btn-close {
        z-index: 1050;
    }
</style>