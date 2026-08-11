let inventarisData = [
  { id: 1, nama: "Sound System", kategori: "Audio", lokasi: "Ruang Sholat Utama", kondisi: "Baik", qty: "1", gambar: "", sumber: "Beli", kode: "MOSQ-AUD-001", tglBeli: "12 Jan 2025", harga: "Rp 25.000.000", catatan: "Full system, terdiri dari speaker, mixer, amplifier. Dipakai utama untuk Jumat." },
  { id: 2, nama: "Wireless Microphone", kategori: "Audio", lokasi: "Ruang Sholat Utama", kondisi: "Perlu Perbaikan", qty: "1 set", gambar: "", sumber: "Beli", kode: "MOSQ-AUD-002", tglBeli: "5 Mar 2024", harga: "Rp 3.500.000", catatan: "Baterai receiver mulai lemah, perlu servis." },
  { id: 3, nama: "Projector", kategori: "Elektronik", lokasi: "Ruang Sholat Utama", kondisi: "Baik", qty: "1 set", gambar: "", sumber: "Beli", kode: "MOSQ-ELC-003", tglBeli: "20 Feb 2024", harga: "Rp 8.200.000", catatan: "Dipakai untuk kajian & presentasi." },
  { id: 4, nama: "Karpet Sholat", kategori: "Perlengkapan", lokasi: "Ruang Sholat Utama", kondisi: "Baik", qty: "1 set", gambar: "", sumber: "Waqaf", kode: "MOSQ-FUR-004", tglBeli: "10 Jan 2023", harga: "Rp 15.000.000", catatan: "Karpet utama ruang sholat." },
  { id: 5, nama: "Kursi Plastik", kategori: "Furnitur", lokasi: "Ruang Sholat Utama", kondisi: "Rusak", qty: "2", gambar: "", sumber: "Beli", kode: "MOSQ-FUR-005", tglBeli: "15 Jun 2022", harga: "Rp 150.000", catatan: "2 unit retak di bagian kaki, perlu diganti." },
  { id: 6, nama: "Al-Qur'an", kategori: "Keagamaan", lokasi: "Ruang Sholat Utama", kondisi: "Baik", qty: "1 set", gambar: "", sumber: "Waqaf", kode: "MOSQ-REL-006", tglBeli: "1 Mei 2024", harga: "Rp 2.000.000", catatan: "50 eksemplar Al-Qur'an untuk jamaah." },
  { id: 7, nama: "Kipas Angin", kategori: "Elektronik", lokasi: "Ruang Sholat Utama", kondisi: "Nonaktif", qty: "1", gambar: "", sumber: "Beli", kode: "MOSQ-ELC-007", tglBeli: "8 Agu 2021", harga: "Rp 500.000", catatan: "Sudah tidak dipakai, sudah digantikan AC." },
  { id: 8, nama: "Air Conditioner", kategori: "Elektronik", lokasi: "Ruang Sholat Utama", kondisi: "Baik", qty: "1", gambar: "", sumber: "Beli", kode: "MOSQ-ELC-008", tglBeli: "3 Okt 2024", harga: "Rp 6.500.000", catatan: "AC 2 PK, service rutin tiap 3 bulan." },
  { id: 9, nama: "Alat Kebersihan", kategori: "Perlengkapan", lokasi: "Ruang Sholat Utama", kondisi: "Baik", qty: "1 set", gambar: "", sumber: "Beli", kode: "MOSQ-EQP-009", tglBeli: "20 Jun 2024", harga: "Rp 800.000", catatan: "Perlengkapan kebersihan harian." },
];

const kondisiClassMap = { "Baik": "status-good", "Perlu Perbaikan": "status-needs-repair", "Rusak": "status-damaged", "Nonaktif": "status-inactive" };
let currentPage = 1;
const perPage = 8;
let activeDetailId = null;

function populateFilterOptions() {
  const kategoriSet = [...new Set(inventarisData.map((i) => i.kategori))];
  const lokasiSet = [...new Set(inventarisData.map((i) => i.lokasi))];

  const kategoriFilter = document.getElementById("kategoriFilter");
  kategoriSet.forEach((k) => kategoriFilter.insertAdjacentHTML("beforeend", `<option value="${k}">${k}</option>`));

  const lokasiFilter = document.getElementById("lokasiFilter");
  lokasiSet.forEach((l) => lokasiFilter.insertAdjacentHTML("beforeend", `<option value="${l}">${l}</option>`));
}

function updateStatCards() {
  document.getElementById("statTotal").textContent = inventarisData.length;
  document.getElementById("statGood").textContent = inventarisData.filter((i) => i.kondisi === "Baik").length;
  document.getElementById("statAttention").textContent = inventarisData.filter((i) => i.kondisi === "Perlu Perbaikan" || i.kondisi === "Rusak").length;
  document.getElementById("statInactive").textContent = inventarisData.filter((i) => i.kondisi === "Nonaktif").length;
}

function getFilteredData() {
  const keyword = document.getElementById("searchInput").value.toLowerCase();
  const kategori = document.getElementById("kategoriFilter").value;
  const kondisi = document.getElementById("kondisiFilter").value;
  const lokasi = document.getElementById("lokasiFilter").value;

  return inventarisData.filter((i) => {
    const matchSearch = i.nama.toLowerCase().includes(keyword);
    const matchKategori = kategori === "" || i.kategori === kategori;
    const matchKondisi = kondisi === "" || i.kondisi === kondisi;
    const matchLokasi = lokasi === "" || i.lokasi === lokasi;
    return matchSearch && matchKategori && matchKondisi && matchLokasi;
  });
}

function renderTable() {
  const filtered = getFilteredData();
  const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
  if (currentPage > totalPages) currentPage = totalPages;

  const start = (currentPage - 1) * perPage;
  const pageItems = filtered.slice(start, start + perPage);

  const tbody = document.getElementById("inventarisTableBody");
  tbody.innerHTML = "";

  if (pageItems.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:24px; color:var(--text-muted);">Data tidak ditemukan</td></tr>`;
  }

  pageItems.forEach((item) => {
    tbody.insertAdjacentHTML("beforeend", `
      <tr>
        <td><input type="checkbox"></td>
        <td>${item.nama}</td>
        <td>${item.kategori}</td>
        <td>${item.lokasi}</td>
        <td><span class="status-badge ${kondisiClassMap[item.kondisi]}">${item.kondisi}</span></td>
        <td>${item.qty}</td>
        <td>
          <button class="btn-sm btn-detail inventaris-action-btn" data-id="${item.id}"><i class="fa-regular fa-eye"></i></button>
          <button class="btn-sm btn-edit inventaris-action-btn" data-id="${item.id}"><i class="fa-solid fa-pen"></i></button>
          <button class="btn-sm btn-hapus inventaris-action-btn" data-id="${item.id}"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>
    `);
  });

  renderPagination(totalPages);

  tbody.querySelectorAll(".btn-detail:not(.hapus)").forEach((btn) => {
    btn.addEventListener("click", () => renderDetail(parseInt(btn.dataset.id)));
  });

  tbody.querySelectorAll(".btn-hapus").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = parseInt(btn.dataset.id);
      const item = inventarisData.find((i) => i.id === id);
      if (!item) return;
      openConfirmDelete({
        title: 'Hapus Item?',
        message: `Yakin ingin menghapus "${item.nama}"? Tindakan ini tidak bisa dibatalkan.`,
        onConfirm: async () => {
          inventarisData = inventarisData.filter((i) => i.id !== id);
          if (activeDetailId === id) closeDetailModal();
          updateStatCards();
          renderTable();
          showToast('Item berhasil dihapus.', 'fa-solid fa-trash');
        },
      });
    });
  });
}

function renderPagination(totalPages) {
  const row = document.getElementById("paginationRow");
  row.innerHTML = "";
  row.insertAdjacentHTML("beforeend", `<button class="pagination-btn" id="prevPageBtn"><i class="fa-solid fa-chevron-left"></i></button>`);
  for (let p = 1; p <= totalPages; p++) {
    row.insertAdjacentHTML("beforeend", `<button class="pagination-btn ${p === currentPage ? "active" : ""}" data-page="${p}">${p}</button>`);
  }
  row.insertAdjacentHTML("beforeend", `<button class="pagination-btn" id="nextPageBtn"><i class="fa-solid fa-chevron-right"></i></button>`);

  row.querySelectorAll("[data-page]").forEach((btn) => {
    btn.addEventListener("click", () => { currentPage = parseInt(btn.dataset.page); renderTable(); });
  });
  document.getElementById("prevPageBtn").addEventListener("click", () => { if (currentPage > 1) { currentPage--; renderTable(); } });
  document.getElementById("nextPageBtn").addEventListener("click", () => { if (currentPage < totalPages) { currentPage++; renderTable(); } });
}

function renderDetail(id) {
  activeDetailId = id;
  const item = inventarisData.find((i) => i.id === id);
  const body = document.getElementById("detailModalBody");

  const imgHtml = item.gambar
    ? `<div class="detail-panel-image"><img src="${item.gambar}" alt="${item.nama}"></div>`
    : `<div class="detail-panel-image-placeholder"><i class="fa-regular fa-image"></i></div>`;

  body.innerHTML = `
    ${imgHtml}
    <div class="detail-list">
      <div class="detail-row"><span class="detail-label">Kode Inventaris</span><span class="detail-value">${item.kode}</span></div>
      <div class="detail-row"><span class="detail-label">Sumber</span><span class="detail-value">${item.sumber}</span></div>
      <div class="detail-row"><span class="detail-label">Kategori</span><span class="detail-value">${item.kategori}</span></div>
      <div class="detail-row"><span class="detail-label">Jumlah</span><span class="detail-value">${item.qty}</span></div>
      <div class="detail-row"><span class="detail-label">Lokasi</span><span class="detail-value">${item.lokasi}</span></div>
      <div class="detail-row"><span class="detail-label">Tanggal Beli</span><span class="detail-value">${item.tglBeli}</span></div>
      <div class="detail-row"><span class="detail-label">Harga Beli</span><span class="detail-value">${item.harga}</span></div>
      <div class="detail-row"><span class="detail-label">Kondisi</span><span class="status-badge ${kondisiClassMap[item.kondisi]}">${item.kondisi}</span></div>
      <div class="detail-row"><span class="detail-label">Catatan</span><span class="detail-value">${item.catatan}</span></div>
    </div>
  `;

  document.getElementById("detailModal").classList.add("active");
}

function closeDetailModal() {
  activeDetailId = null;
  document.getElementById("detailModal").classList.remove("active");
}

document.getElementById("checkAll").addEventListener("change", (e) => {
  document.querySelectorAll("#inventarisTableBody input[type='checkbox']").forEach((cb) => cb.checked = e.target.checked);
});

document.getElementById("searchInput").addEventListener("input", () => { currentPage = 1; renderTable(); });
document.getElementById("kategoriFilter").addEventListener("change", () => { currentPage = 1; renderTable(); });
document.getElementById("kondisiFilter").addEventListener("change", () => { currentPage = 1; renderTable(); });
document.getElementById("lokasiFilter").addEventListener("change", () => { currentPage = 1; renderTable(); });

// --- Image Upload Preview ---
let gambarData = "";

document.getElementById("formGambar").addEventListener("change", function () {
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function (e) {
    gambarData = e.target.result;
    const preview = document.getElementById("gambarPreview");
    preview.innerHTML = `<img src="${gambarData}" style="max-width:100%; max-height:100px; border-radius:6px;">`;
  };
  reader.readAsDataURL(file);
});

// --- Modal Tambah Inventaris ---
document.getElementById("tambahInventarisBtn").addEventListener("click", () => {
  gambarData = "";
  document.getElementById("formGambar").value = "";
  document.getElementById("gambarPreview").innerHTML = '<i class="fa-regular fa-image"></i><span>Pilih gambar</span>';
  document.getElementById("tambahModal").classList.add("show");
});

document.getElementById("closeTambahModal").addEventListener("click", closeTambahModal);
document.getElementById("cancelTambahBtn").addEventListener("click", closeTambahModal);

document.getElementById("closeDetailModal").addEventListener("click", closeDetailModal);

function closeTambahModal() {
  document.getElementById("tambahModal").classList.remove("show");
  document.getElementById("formNama").value = "";
  document.getElementById("formKategori").value = "";
  document.getElementById("formLokasi").value = "";
  document.getElementById("formQty").value = "";
  document.getElementById("formSumber").value = "Beli";
  document.getElementById("formTglBeli").value = "";
  document.getElementById("formHarga").value = "";
  document.getElementById("formCatatan").value = "";
}

document.getElementById("simpanTambahBtn").addEventListener("click", () => {
  const nama = document.getElementById("formNama").value.trim();
  if (!nama) { showToast('Nama Item wajib diisi.', 'fa-solid fa-triangle-exclamation'); return; }

  const kodePrefix = "MOSQ-NEW-" + String(inventarisData.length + 1).padStart(3, "0");

  const itemBaru = {
    id: Date.now(),
    nama,
    kategori: document.getElementById("formKategori").value || "Lainnya",
    lokasi: document.getElementById("formLokasi").value || "-",
    kondisi: document.getElementById("formKondisi").value,
    qty: document.getElementById("formQty").value || "1",
    gambar: gambarData,
    sumber: document.getElementById("formSumber").value,
    kode: kodePrefix,
    tglBeli: document.getElementById("formTglBeli").value || "-",
    harga: document.getElementById("formHarga").value || "-",
    catatan: document.getElementById("formCatatan").value || "-",
  };

  inventarisData.unshift(itemBaru);
  closeTambahModal();
  updateStatCards();
  populateFilterOptions();
  currentPage = 1;
  renderTable();
  renderDetail(itemBaru.id);
});

document.querySelectorAll(".modal-overlay").forEach((overlay) => {
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      overlay.classList.remove("show");
      overlay.classList.remove("active");
    }
  });
});

populateFilterOptions();
updateStatCards();
renderTable();