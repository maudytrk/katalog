/*!
 * Start Bootstrap - SB Admin v7.0.7 (https://startbootstrap.com/template/sb-admin)
 * Copyright 2013-2023 Start Bootstrap
 * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
 */
//
// Scripts
//

// 1. Daftarkan Service Worker
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("/sw.js")
      .then((registration) => {
        console.log(
          "Service Worker berhasil terdaftar. Scope:",
          registration.scope,
        );
      })
      .catch((error) => {
        console.log("Pendaftaran Service Worker gagal:", error);
      });
  });
}

window.addEventListener("DOMContentLoaded", (event) => {
  // Toggle the side navigation
  const sidebarToggle = document.body.querySelector("#sidebarToggle");
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", (event) => {
      event.preventDefault();
      document.body.classList.toggle("sb-sidenav-toggled");
      localStorage.setItem(
        "sb|sidebar-toggle",
        document.body.classList.contains("sb-sidenav-toggled"),
      );
    });
  }
});

// 2. Inisialisasi Database Lokal (IndexedDB)
const dbPromise = idb.openDB("RahayuOrderDB", 1, {
  upgrade(db) {
    db.createObjectStore("offlineOrders", {
      keyPath: "id",
      autoIncrement: true,
    });
  },
});

// 3. Intersepsi Formulir Pesanan Cepat (Sistem Kunci Tombol)
document.addEventListener("click", async (e) => {
  // Cari apakah yang diklik adalah tombol simpan transaksi
  const tombolSimpan = e.target.closest("#btn-submit-order");

  if (tombolSimpan) {
    e.preventDefault(); // Langkah krusial: Tahan peramban agar tidak pindah halaman

    // Cari tag <form> yang membungkus tombol ini
    const orderForm = tombolSimpan.closest("form");

    if (orderForm) {
      const formData = new FormData(orderForm);
      const dataOrder = Object.fromEntries(formData.entries());
      dataOrder.tanggal = new Date().toISOString();

      if (navigator.onLine) {
        kirimPesananKeServer(dataOrder);
      } else {
        simpanPesananOffline(dataOrder);
      }
    } else {
      alert(
        "Sistem gagal menemukan kerangka formulir. Cek struktur HTML Anda.",
      );
    }
  }
});

// 4. Simpan Data ke Penyimpanan Lokal
async function simpanPesananOffline(data) {
  const db = await dbPromise;
  await db.add("offlineOrders", data);
  alert(
    "Anda sedang offline. Pesanan tersimpan dan akan sistem kirim saat koneksi pulih.",
  );

  const modalElement = document.getElementById("modalOrderCepat");
  const modalInstance =
    bootstrap.Modal.getInstance(modalElement) ||
    new bootstrap.Modal(modalElement);
  modalInstance.hide();
}

// 5. Kirim Data ke Server PHP
async function kirimPesananKeServer(data) {
  try {
    const response = await fetch("/katalog/proses_order_cepat.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    });

    const result = await response.json();

    if (result.status === "success") {
      alert(result.message);
      window.location.reload();
    } else {
      alert(result.message);
    }
  } catch (error) {
    simpanPesananOffline(data);
  }
}

// 6. Sinkronisasi Otomatis Saat Jaringan Pulih
window.addEventListener("online", sinkronisasiPesananOffline);

async function sinkronisasiPesananOffline() {
  const db = await dbPromise;
  const allOrders = await db.getAll("offlineOrders");

  if (allOrders.length > 0) {
    console.log(`Menyinkronkan ${allOrders.length} pesanan...`);
    let berhasilSync = 0;

    for (const order of allOrders) {
      try {
        const response = await fetch("/katalog/proses_order_cepat.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(order),
        });

        const result = await response.json();

        if (result.status === "success") {
          await db.delete("offlineOrders", order.id);
          berhasilSync++;
        } else {
          console.log("Server menolak pesanan:", result.message);
        }
      } catch (err) {
        console.log("Gagal menyinkronkan satu pesanan:", err);
      }
    }

    if (berhasilSync > 0) {
      alert(
        `${berhasilSync} data pesanan offline berhasil tersinkronisasi ke sistem pusat.`,
      );
      window.location.reload();
    }
  }
}
