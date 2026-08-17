let jadwalData = window.__JADWAL_PETUGAS_DATA__ || [];
let daftarJamaah = window.__JAMAAH_OPTIONS__ || [];

let currentPage = 1;
const perPage = 8;
let editingId = null;

// ============================================================
// CUSTOM DROPDOWN JEMAAH (Khatib / Imam / Muadzin)
// ============================================================
function getJpSelect(field) {
  return document.querySelector(`.jp-select[data-field="${field}"]`);
}

function getJpValue(field) {
  const sel = getJpSelect(field);
  if (!sel) return "";
  const hidden = sel.querySelector('input[type="hidden"]');
  return hidden ? hidden.value : "";
}

function setJpValue(field, value) {
  const sel = getJpSelect(field);
  if (!sel) return;
  const hidden = sel.querySelector('input[type="hidden"]');
  const label = sel.querySelector('.jp-select-label');
  hidden.value = value || "";
  if (value) {
    label.textContent = value;
    label.classList.remove('placeholder');
  } else {
    label.textContent = '— Pilih dari Data Jemaah —';
    label.classList.add('placeholder');
  }
}

function renderJpItems(sel, keyword = "") {
  const field = sel.dataset.field;
  const items = sel.querySelector('.jp-dropdown-items');
  const current = getJpValue(field);
  const filtered = daftarJamaah.filter((p) => p.nama.toLowerCase().includes(keyword.toLowerCase()));

  if (filtered.length === 0) {
    items.innerHTML = `<div class="jp-dropdown-empty">Jemaah tidak ditemukan</div>`;
    return;
  }

  items.innerHTML = filtered
    .map((p) => {
      const isSelected = current === p.nama;
      const meta = [p.email, p.hp].filter(Boolean).join(' | ');
      return `
        <div class="jp-dropdown-item ${isSelected ? "selected" : ""}" data-nama="${esc(p.nama)}">
          <div class="jp-dropdown-avatar"><i class="fa-solid fa-user"></i></div>
          <div class="jp-dropdown-info">
            <span class="jp-dropdown-name">${esc(p.nama)}</span>
            <span class="jp-dropdown-meta">${esc(meta) || '-'}</span>
          </div>
          ${isSelected ? '<i class="fa-solid fa-check jp-dropdown-check"></i>' : ''}
        </div>
      `;
    })
    .join("");

  items.querySelectorAll('.jp-dropdown-item').forEach((el) => {
    el.addEventListener('click', () => {
      setJpValue(field, el.dataset.nama);
      sel.querySelector('.jp-dropdown').classList.remove('show');
      sel.classList.remove('open');
    });
  });
}

function initJpDropdowns() {
  document.querySelectorAll('.jp-select').forEach((sel) => {
    const trigger = sel.querySelector('.jp-select-trigger');
    const dropdown = sel.querySelector('.jp-dropdown');
    const searchInput = dropdown.querySelector('input');

    trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      document.querySelectorAll('.jp-dropdown.show').forEach((d) => {
        if (d !== dropdown) {
          d.classList.remove('show');
          d.closest('.jp-select')?.classList.remove('open');
        }
      });
      dropdown.classList.toggle('show');
      sel.classList.toggle('open', dropdown.classList.contains('show'));
      if (dropdown.classList.contains('show')) {
        renderJpItems(sel);
        searchInput.value = "";
        searchInput.focus();
      }
    });

    searchInput.addEventListener('input', (e) => renderJpItems(sel, e.target.value));
    dropdown.addEventListener('click', (e) => e.stopPropagation());
  });

  document.addEventListener('click', () => {
    document.querySelectorAll('.jp-dropdown.show').forEach((d) => {
      d.classList.remove('show');
      d.closest('.jp-select')?.classList.remove('open');
    });
  });
}

function updateStatCards() {
  document.getElementById("statTotal").textContent = jadwalData.length;
  document.getElementById("statJumat").textContent = jadwalData.filter((j) => j.sholat === "Jumat").length;
  const now = new Date();
  const month = now.getMonth();
  const year = now.getFullYear();
  document.getElementById("statBulan").textContent = jadwalData.filter((j) => {
    const d = new Date(j.tanggal);
    return d.getMonth() === month && d.getFullYear() === year;
  }).length;
  document.getElementById("statKhatib").textContent = jadwalData.filter((j) => j.khatib).length;
}

function getFilteredData() {
  const keyword = document.getElementById("searchInput").value.toLowerCase();
  const sholat = document.getElementById("sholatFilter").value;
  const hideLewat = document.getElementById("hideLewatFilter").checked;

  return jadwalData.filter((j) => {
    const matchSearch =
      (j.khatib || "").toLowerCase().includes(keyword) ||
      (j.imam || "").toLowerCase().includes(keyword) ||
      (j.muadzin || "").toLowerCase().includes(keyword) ||
      j.sholat.toLowerCase().includes(keyword);
    const matchSholat = sholat === "" || j.sholat === sholat;
    const matchLewat = hideLewat ? !j.lewat : true;
    return matchSearch && matchSholat && matchLewat;
  });
}

function sholatBadgeClass(sholat) {
  return sholat === "Jumat" ? "sholat-jumat" : "sholat-harian";
}

function renderTable() {
  const filtered = getFilteredData();
  const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
  if (currentPage > totalPages) currentPage = totalPages;

  const start = (currentPage - 1) * perPage;
  const pageItems = filtered.slice(start, start + perPage);

  const tbody = document.getElementById("tableBody");
  tbody.innerHTML = "";

  if (pageItems.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:24px; color:var(--text-muted);">Data tidak ditemukan</td></tr>`;
  }

  pageItems.forEach((j) => {
    tbody.insertAdjacentHTML("beforeend", `
      <tr class="${j.lewat ? "jadwal-lewat" : ""}">
        <td>${esc(j.tanggal)}</td>
        <td>${esc(j.hari)}</td>
        <td><span class="status-badge ${sholatBadgeClass(j.sholat)}">${esc(j.sholat)}</span></td>
        <td>${esc(j.khatib) || '-'}</td>
        <td>${esc(j.imam) || '-'}</td>
        <td>${esc(j.muadzin) || '-'}</td>
        <td>${esc(j.keterangan) || '-'}</td>
        <td>
          <button class="btn-sm btn-edit jadwal-action-btn" data-id="${j.id}" title="Edit"><i class="fa-solid fa-pen"></i></button>
          <button class="btn-sm btn-hapus jadwal-action-btn" data-id="${j.id}" title="Hapus"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>
    `);
  });

  renderPagination(totalPages);

  tbody.querySelectorAll(".btn-edit").forEach((btn) => {
    btn.addEventListener("click", () => openModal(parseInt(btn.dataset.id)));
  });
  tbody.querySelectorAll(".btn-hapus").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = parseInt(btn.dataset.id);
      const item = jadwalData.find((j) => j.id === id);
      if (!item) return;
      openConfirmDelete({
        title: "Hapus Jadwal?",
        message: `Yakin ingin menghapus jadwal ${item.sholat} tanggal ${item.tanggal}? Tindakan ini tidak bisa dibatalkan.`,
        onConfirm: async () => {
          try {
            const res = await fetch(`/kegiatan/jadwal-petugas-sholat/${id}`, {
              method: "DELETE",
              headers: {
                "X-CSRF-TOKEN": getCsrf(),
                Accept: "application/json",
              },
            });
            if (!res.ok) throw new Error("Gagal hapus");
            jadwalData = jadwalData.filter((j) => j.id !== id);
            updateStatCards();
            renderTable();
            showToast("Jadwal berhasil dihapus.", "fa-solid fa-trash");
          } catch (err) {
            showToast("Gagal menghapus jadwal.", "fa-solid fa-triangle-exclamation");
            console.error(err);
            throw err;
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

function openModal(id = null) {
  editingId = id ? Number(id) : null;
  const item = editingId ? jadwalData.find((j) => j.id === editingId) : null;

  document.getElementById("formModalTitle").textContent = item ? "Edit Jadwal" : "Tambah Jadwal";
  document.getElementById("saveBtn").innerHTML = item
    ? '<i class="fa-solid fa-check"></i> Simpan Perubahan'
    : '<i class="fa-solid fa-check"></i> Simpan';

  document.getElementById("formTanggal").value = item ? item.tanggal : "";
  document.getElementById("formSholat").value = item ? item.sholat : "Jumat";
  setJpValue("khatib", item ? item.khatib : "");
  setJpValue("imam", item ? item.imam : "");
  setJpValue("muadzin", item ? item.muadzin : "");
  document.getElementById("formKeterangan").value = item ? (item.keterangan || "") : "";

  if (typeof syncCustomSelects === 'function') syncCustomSelects()
  document.getElementById("formModal").classList.add("show");
}

function closeModal() {
  document.getElementById("formModal").classList.remove("show");
  editingId = null;
}

document.getElementById("tambahBtn").addEventListener("click", () => openModal());
document.getElementById("closeModal").addEventListener("click", closeModal);
document.getElementById("cancelBtn").addEventListener("click", closeModal);

document.getElementById("saveBtn").addEventListener("click", async () => {
  const tanggal = document.getElementById("formTanggal").value;
  if (!tanggal) { showToast("Tanggal wajib diisi.", "fa-solid fa-triangle-exclamation"); return; }

  const btn = document.getElementById("saveBtn");
  btn.disabled = true;

  const payload = {
    tanggal,
    sholat: document.getElementById("formSholat").value,
    khatib: getJpValue("khatib") || null,
    imam: getJpValue("imam") || null,
    muadzin: getJpValue("muadzin") || null,
    keterangan: document.getElementById("formKeterangan").value.trim() || null,
  };

  const isEditing = !!editingId;
  const url = isEditing ? `/kegiatan/jadwal-petugas-sholat/${editingId}` : "/kegiatan/jadwal-petugas-sholat";

  try {
    const res = await fetch(url, {
      method: isEditing ? "PUT" : "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": getCsrf(),
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!res.ok) {
      await showFetchError(res, "Gagal menyimpan jadwal.");
      return;
    }

    const itemBaru = await res.json();

    if (isEditing) {
      const idx = jadwalData.findIndex((j) => j.id === editingId);
      if (idx > -1) jadwalData[idx] = itemBaru;
    } else {
      jadwalData.unshift(itemBaru);
      currentPage = 1;
    }
    closeModal();
    updateStatCards();
    renderTable();
    showToast(isEditing ? "Jadwal berhasil diperbarui." : "Jadwal berhasil ditambahkan.", "fa-solid fa-check");
  } catch (err) {
    showToast("Gagal menyimpan jadwal.", "fa-solid fa-triangle-exclamation");
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

document.getElementById("searchInput").addEventListener("input", () => { currentPage = 1; renderTable(); });
document.getElementById("sholatFilter").addEventListener("change", () => { currentPage = 1; renderTable(); });
document.getElementById("hideLewatFilter").addEventListener("change", () => { currentPage = 1; renderTable(); });

updateStatCards();
renderTable();
initJpDropdowns();