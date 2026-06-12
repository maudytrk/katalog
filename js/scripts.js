// 1. Daftarkan Service Worker
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("/sw.js")
      .catch((err) => console.log("SW Error:", err));
  });
}

// ==================== SIDEBAR TOGGLE FUNCTION ====================
// Fungsi untuk toggle sidebar (hamburger menu)
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            document.body.classList.toggle('sb-sidenav-toggled');
        });
    }
});