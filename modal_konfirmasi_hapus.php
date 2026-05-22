<div class="modal fade" id="modalKonfirmasiHapus" tabindex="-1" aria-labelledby="modalKonfirmasiHapusLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white" style="border-bottom: 2px solid #b52a25;">
                <h5 class="modal-title" id="modalKonfirmasiHapusLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus Data
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark bg-white">
                <p>Apakah Anda yakin ingin menghapus data ini?</p>
                <p class="text-danger small fw-semibold mb-0">
                    <i class="fas fa-info-circle me-1"></i> Semua data terkait beserta berkas yang melekat akan dihapus secara permanen dari server.
                </p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="btnTombolHapusEksekusi" class="btn btn-danger shadow-sm fw-bold">Ya, Hapus Permanen</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Script pasif untuk menangkap pemicu modal dan memperbarui tautan hapus secara dinamis
    document.addEventListener('DOMContentLoaded', function () {
        const modalHapus = document.getElementById('modalKonfirmasiHapus');
        if (modalHapus) {
            modalHapus.addEventListener('show.bs.modal', function (event) {
                // Tombol pemicu yang diklik
                const button = event.relatedTarget;
                // Ambil URL hapus dari atribut data-href
                const urlHapus = button.getAttribute('data-href');
                
                // Perbarui action tombol konfirmasi hapus di dalam modal
                const btnEksekusi = document.getElementById('btnTombolHapusEksekusi');
                btnEksekusi.setAttribute('href', urlHapus);
            });
        }
    });
</script>