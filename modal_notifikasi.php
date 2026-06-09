<?php if (isset($_SESSION['sukses']) || isset($_SESSION['gagal'])): ?>
<div class="modal fade" id="notifikasiModal" tabindex="-1" aria-labelledby="notifikasiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4 text-dark bg-white rounded">
                <?php if (isset($_SESSION['sukses'])): ?>
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 3.5rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color: var(--space-cadet);">Berhasil!</h5>
                    <p class="text-muted small mb-3"><?php echo $_SESSION['sukses']; ?></p>
                <?php else: ?>
                    <div class="mb-3">
                        <i class="fas fa-times-circle text-danger" style="font-size: 3.5rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color: var(--space-cadet);">Gagal!</h5>
                    <p class="text-muted small mb-3"><?php echo $_SESSION['gagal']; ?></p>
                <?php endif; ?>
                <button type="button" class="btn btn-sm w-100 fw-bold text-white shadow-sm" data-bs-dismiss="modal" style="background-color: var(--old-heliotrope);">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var myModal = new bootstrap.Modal(document.getElementById('notifikasiModal'));
        myModal.show();
    });
</script>
<?php 
    // Bersihkan session setelah modal ditampilkan agar tidak muncul berulang kali saat di-refresh
    unset($_SESSION['sukses']);
    unset($_SESSION['gagal']);
endif; 
?>