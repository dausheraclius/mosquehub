let inventarisData = window.inventarisData || [];

const kondisiClassMap = { "Baik": "status-good", "Perlu Perbaikan": "status-needs-repair", "Rusak": "status-damaged", "Nonaktif": "status-inactive" };
let currentPage = 1;
const perPage = 8;
let activeDetailId = null;
let editingId = null; // null = mode tambah, angka = mode edit

function populateFilterOptions() {
  const kategoriSet = [...new Set(inventarisData.map((i) => i.kategori))];
  const lokasiSet = [...new Set(inventarisData.map((i) => i.lokasi))];

  const kategoriFilter = document.getElementById("kategoriFilter");
  kategoriSet.forEach((k) => {
    if (![...kategoriFilter.options].some((o) => o.value === k)) {
      kategoriFilter.insertAdjacentHTML("beforeend", `<option value="${k}">${k}</option>`);
    }
  });

  const lokasiFilter = document.getElementById("lokasiFilter");
  lokasiSet.forEach((l) => {
    if (![...lokasiFilter.options].some((o) => o.value === l)) {
      lokasiFilter.insertAdjacentHTML("beforeend", `<option value="${l}">${l}</option>`);
    }
  });
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
        <td>${esc(item.nama)}</td>
        <td>${esc(item.kategori)}</td>
        <td>${esc(item.lokasi)}</td>
        <td><span class="status-badge ${kondisiClassMap[item.kondisi]}">${esc(item.kondisi)}</span></td>
        <td>${esc(item.qty)}</td>
        <td>
          <button class="btn-sm btn-detail inventaris-action-btn" data-id="${item.id}" title="Lihat Detail"><i class="fa-regular fa-eye"></i></button>
          <button class="btn-sm btn-edit inventaris-action-btn" data-id="${item.id}" title="Edit"><i class="fa-solid fa-pen"></i></button>
          <button class="btn-sm btn-hapus inventaris-action-btn" data-id="${item.id}" title="Hapus"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>
    `);
  });

  renderPagination(totalPages);

  // --- TOMBOL AKSI (di-rebind tiap kali tabel dirender) ---
  tbody.querySelectorAll(".btn-detail").forEach((btn) => {
    btn.addEventListener("click", () => renderDetail(parseInt(btn.dataset.id)));
  });

  tbody.querySelectorAll(".btn-edit").forEach((btn) => {
    btn.addEventListener("click", () => openInventarisModal(parseInt(btn.dataset.id)));
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
          try {
            const res = await fetch(adminUrl(`/inventaris/${id}`), {
              method: "DELETE",
              headers: {
                "X-CSRF-TOKEN": getCsrf(),
                Accept: "application/json",
              },
            });
            if (!res.ok) throw new Error("Gagal hapus inventaris");

            inventarisData = inventarisData.filter((i) => i.id !== id);
            if (activeDetailId === id) closeDetailModal();
            updateStatCards();
            renderTable();
            showToast('Item berhasil dihapus.', 'fa-solid fa-trash');
          } catch (err) {
            showToast('Gagal menghapus inventaris.', 'fa-solid fa-triangle-exclamation');
            console.error(err);
            throw err; // biar dialog konfirmasi global kembali aktif
          }
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
  if (!item) return;
  const body = document.getElementById("detailModalBody");

  const imgHtml = item.gambar
    ? `<div class="detail-panel-image"><img src="${item.gambar}" alt="${esc(item.nama)}"></div>`
    : `<div class="detail-panel-image-placeholder"><i class="fa-regular fa-image"></i></div>`;

  body.innerHTML = `
    ${imgHtml}
    <div class="detail-list">
      <div class="detail-row"><span class="detail-label">Kode Inventaris</span><span class="detail-value">${esc(item.kode)}</span></div>
      <div class="detail-row"><span class="detail-label">Sumber</span><span class="detail-value">${esc(item.sumber)}</span></div>
      <div class="detail-row"><span class="detail-label">Kategori</span><span class="detail-value">${esc(item.kategori)}</span></div>
      <div class="detail-row"><span class="detail-label">Jumlah</span><span class="detail-value">${esc(item.qty)}</span></div>
      <div class="detail-row"><span class="detail-label">Lokasi</span><span class="detail-value">${esc(item.lokasi)}</span></div>
      <div class="detail-row"><span class="detail-label">Tanggal Beli</span><span class="detail-value">${esc(item.tglBeli)}</span></div>
      <div class="detail-row"><span class="detail-label">Harga Beli</span><span class="detail-value">${esc(item.harga)}</span></div>
      <div class="detail-row"><span class="detail-label">Kondisi</span><span class="status-badge ${kondisiClassMap[item.kondisi]}">${esc(item.kondisi)}</span></div>
      <div class="detail-row"><span class="detail-label">Catatan</span><span class="detail-value">${esc(item.catatan)}</span></div>
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

// --- MODAL TAMBAH / EDIT INVENTARIS (satu modal untuk dua mode) ---
function openInventarisModal(id = null) {
  editingId = id ? Number(id) : null;
  const item = editingId ? inventarisData.find((i) => i.id === editingId) : null;

  document.getElementById("tambahModalTitle").textContent = item ? "Edit Inventaris" : "Tambah Inventaris";
  document.getElementById("simpanTambahBtn").innerHTML = item
    ? '<i class="fa-solid fa-check"></i> Simpan Perubahan'
    : '<i class="fa-solid fa-check"></i> Simpan';

  gambarData = "";
  document.getElementById("formGambar").value = "";
  document.getElementById("gambarPreview").innerHTML = item && item.gambar
    ? `<img src="${item.gambar}" style="max-width:100%; max-height:100px; border-radius:6px;">`
    : '<i class="fa-regular fa-image"></i><span>Pilih gambar</span>';

  document.getElementById("formNama").value = item ? item.nama : "";
  document.getElementById("formKategori").value = item ? (item.kategori || "") : "";
  document.getElementById("formLokasi").value = item ? (item.lokasi || "") : "";
  document.getElementById("formKondisi").value = item ? item.kondisi : "Baik";
  document.getElementById("formQty").value = item ? (item.qty || "") : "";
  document.getElementById("formSumber").value = item ? item.sumber : "Beli";
  document.getElementById("formTglBeli").value = item ? (item.tglBeliIso || "") : "";
  document.getElementById("formHarga").value = item ? (item.harga || "") : "";
  document.getElementById("formCatatan").value = item ? (item.catatan || "") : "";

  if (typeof syncCustomSelects === 'function') syncCustomSelects()
  document.getElementById("tambahModal").classList.add("show");
}

function closeTambahModal() {
  document.getElementById("tambahModal").classList.remove("show");
  editingId = null;
  document.getElementById("formNama").value = "";
  document.getElementById("formKategori").value = "";
  document.getElementById("formLokasi").value = "";
  document.getElementById("formKondisi").value = "Baik";
  document.getElementById("formQty").value = "";
  document.getElementById("formSumber").value = "Beli";
  document.getElementById("formTglBeli").value = "";
  document.getElementById("formHarga").value = "";
  document.getElementById("formCatatan").value = "";
}

document.getElementById("tambahInventarisBtn").addEventListener("click", () => openInventarisModal());
document.getElementById("closeTambahModal").addEventListener("click", closeTambahModal);
document.getElementById("cancelTambahBtn").addEventListener("click", closeTambahModal);
document.getElementById("closeDetailModal").addEventListener("click", closeDetailModal);

document.getElementById("simpanTambahBtn").addEventListener("click", async () => {
  const nama = document.getElementById("formNama").value.trim();
  if (!nama) { showToast('Nama Item wajib diisi.', 'fa-solid fa-triangle-exclamation'); return; }

  const btn = document.getElementById("simpanTambahBtn");
  btn.disabled = true;

  const formData = new FormData();
  formData.append("nama", nama);
  formData.append("kategori", document.getElementById("formKategori").value || "Lainnya");
  formData.append("lokasi", document.getElementById("formLokasi").value || "-");
  formData.append("kondisi", document.getElementById("formKondisi").value);
  formData.append("qty", document.getElementById("formQty").value || "1");
  formData.append("sumber", document.getElementById("formSumber").value);
  formData.append("tgl_beli", document.getElementById("formTglBeli").value);
  formData.append("harga", document.getElementById("formHarga").value || "-");
  formData.append("catatan", document.getElementById("formCatatan").value || "-");

  const file = document.getElementById("formGambar").files[0];
  if (file) formData.append("gambar", file);

  const isEditing = !!editingId;
  const url = adminUrl(isEditing ? `/inventaris/${editingId}` : "/inventaris");

  try {
    const res = await fetch(url, {
      method: isEditing ? "PUT" : "POST",
      headers: {
        "X-CSRF-TOKEN": getCsrf(),
        Accept: "application/json",
      },
      body: formData,
    });

    if (!res.ok) {
      await showFetchError(res, 'Gagal menyimpan inventaris.');
      return;
    }

    const itemBaru = await res.json();

    if (isEditing) {
      const idx = inventarisData.findIndex((i) => i.id === editingId);
      if (idx > -1) inventarisData[idx] = itemBaru;
      closeTambahModal();
      updateStatCards();
      populateFilterOptions();
      renderTable();
      showToast('Item berhasil diperbarui.', 'fa-solid fa-check');
    } else {
      inventarisData.unshift(itemBaru);
      closeTambahModal();
      updateStatCards();
      populateFilterOptions();
      currentPage = 1;
      renderTable();
      renderDetail(itemBaru.id);
    }
  } catch (err) {
    showToast('Gagal menyimpan inventaris.', 'fa-solid fa-triangle-exclamation');
    console.error(err);
  } finally {
    btn.disabled = false;
  }
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
