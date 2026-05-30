<div class="modal fade" id="modalGantiPassword" tabindex="-1" aria-labelledby="modalGantiPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="proses_ganti_password.php" method="POST">
                <div class="modal-header border-0" style="background-color: var(--bg-lavender); border-radius: 1rem 1rem 0 0;">
                    <h5 class="modal-title fw-bold" style="color: var(--accent-indigo);" id="modalGantiPasswordLabel">
                        <i class="fas fa-key text-warning me-2"></i>Ganti Password Akun
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="alert alert-info small border-0" style="background-color: #e0f2fe; color: #0284c7;">
                        <i class="fas fa-info-circle me-1"></i> Anda tidak perlu memasukkan password lama.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="password_baru" class="form-control bg-light" required placeholder="Minimal 6 karakter" minlength="6">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="konfirmasi_password" class="form-control bg-light" required placeholder="Ketik ulang password baru" minlength="6">
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit_ganti_password" class="btn rounded-pill px-4 text-white fw-bold shadow-sm" style="background-color: var(--accent-indigo);">Ubah Password</button>
                </div>
            </form>
        </div>
    </div>
</div>