// 1. Daftarkan Service Worker
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("/sw.js")
      .catch((err) => console.log("SW Error:", err));
  });
}
