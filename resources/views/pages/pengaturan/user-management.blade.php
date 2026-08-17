@extends('layouts.app')

@section('title', 'MosqueHub - User Management')

@push('styles-before-components')
  @vite('resources/assets/css/user-management.css')
@endpush

@section('content')

  <x-page-header crumb="Pengaturan" active="Manajemen Pengguna" title="User Management" subtitle="{{ auth()->user()->role ?? 'Ketua YMBPK' }} · {{ now()->translatedFormat('d F Y') }}">
    <button class="btn btn-primary" id="btnTambahUser">
      <i class="fa-solid fa-user-plus"></i> Tambah User
    </button>
  </x-page-header>

  <div class="card table-card">
    <div class="card-header">
      <h2 class="card-title">Daftar Akun</h2>
    </div>
    <div class="filter-bar" style="padding: 0 20px 16px">
      <div class="filter-group filter-search-group">
        <span class="filter-label">Cari</span>
        <div class="filter-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" placeholder="Cari nama/email" id="userSearch" />
        </div>
      </div>
      <div class="filter-group">
        <span class="filter-label">Urutkan</span>
        <select class="filter-select" id="sortFilter">
          <option value="nama-asc">Nama (A-Z)</option>
          <option value="nama-desc">Nama (Z-A)</option>
          <option value="login-baru">Login Terakhir Terbaru</option>
          <option value="login-lama">Login Terakhir Terlama</option>
        </select>
      </div>
      <div class="filter-group">
        <span class="filter-label">Saring Status</span>
        <select class="filter-select" id="statusFilter">
          <option value="">Semua Status</option>
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th><input type="checkbox" /></th>
          <th>Foto</th>
          <th>Nama</th>
          <th>Email / No HP</th>
          <th>Role</th>
          <th>Status</th>
          <th>Login Terakhir</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="userTableBody">
        @forelse ($userList as $u)
          <tr data-id="{{ $u['id'] }}" data-role="{{ $u['role'] }}" data-phone="{{ $u['phone'] }}" data-status="{{ $u['status'] }}" data-lastlogin="{{ $u['lastLoginTs'] }}" data-permissions='{{ json_encode($u['permissions']) }}'>
            <td><input type="checkbox" /></td>
            <td><div class="table-avatar"><i class="fa-solid fa-user"></i></div></td>
            <td>{{ $u['name'] }}</td>
            <td>{{ $u['email'] }}</td>
            <td><span class="role-badge role-{{ $u['roleSlug'] }}">{{ $u['role'] }}</span></td>
            <td>
              <label class="switch">
                <input type="checkbox" {{ $u['status'] === 'aktif' ? 'checked' : '' }} class="status-toggle" />
                <span class="switch-track"></span>
              </label>
            </td>
            <td>{{ $u['lastLogin'] }}</td>
            <td>
              <button class="btn-sm btn-edit btn-edit-user">Edit</button>
              <button class="btn-sm btn-reset-password">Reset Kata Sandi</button>
              <button class="btn-sm btn-hapus btn-hapus-user"><i class="fa-solid fa-trash"></i></button>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="empty-state">Belum ada user.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

@endsection

@section('modals')
  <!-- MODAL TAMBAH/EDIT USER -->
  <div class="modal-overlay" id="userModalOverlay">
    <div class="modal-box">
      <div class="modal-header">
        <h2 id="userModalTitle">Tambah User</h2>
        <button class="modal-close" id="modalCloseBtn"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <div class="modal-body">
        <div class="form-row">
          <div class="form-field">
            <label for="userName">Nama Lengkap</label>
            <input type="text" id="userName" placeholder="cth: Ahmad Zaki" />
          </div>
          <div class="form-field">
            <label for="userEmail">Email</label>
            <input type="text" id="userEmail" placeholder="cth: ahmad.zaki@baiturrahim.id" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-field">
            <label for="userPhone">No. HP</label>
            <input type="text" id="userPhone" placeholder="cth: 0812xxxxxxx" />
          </div>
          <div class="form-field" id="passwordField">
            <label for="userPassword">Password</label>
            <input type="password" id="userPassword" placeholder="Minimal 8 karakter" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-field">
            <label for="userRole">Role</label>
            <select id="userRole">
              <option value="Ketua YMBPK">Ketua YMBPK</option>
              <option value="Sekretaris">Sekretaris</option>
              <option value="Bendahara">Bendahara</option>
              <option value="Petugas Zakat" selected>Petugas Zakat</option>
              <option value="__custom__">+ Buat Role Baru...</option>
            </select>
          </div>
          <div class="form-field" id="customRoleField" style="display: none">
            <label for="customRoleName">Nama Role Baru</label>
            <input type="text" id="customRoleName" placeholder="cth: Panitia Qurban" />
          </div>
        </div>

        <div class="form-field" style="margin-bottom: 8px">
          <label>Status Akun</label>
          <div class="toggle-row" style="border-bottom: none; padding: 6px 0">
            <div class="toggle-sub">Nonaktifin akun ini kalo orangnya udah gak aktif</div>
            <label class="switch">
              <input type="checkbox" id="userStatusToggle" checked />
              <span class="switch-track"></span>
            </label>
          </div>
        </div>

        <div class="access-section">
          <label class="access-title">Hak Akses Menu</label>
          <p class="access-desc">
            Centang menu yang boleh diakses sama role ini. Buat "Petugas Zakat", biasanya cuma dikasih akses ke
            Infaq/Sodaqoh aja.
          </p>

          <div class="access-group">
            <label class="access-parent">
              <input type="checkbox" class="access-check" data-group="beranda" /> Beranda
            </label>
          </div>

          <div class="access-group">
            <label class="access-parent">
              <input type="checkbox" class="access-check group-toggle" data-group="keuangan" /> Keuangan
            </label>
            <div class="access-children" data-children-of="keuangan">
              <label class="access-child"><input type="checkbox" class="access-check" /> Kas Masjid</label>
              <label class="access-child"
                ><input type="checkbox" class="access-check" checked /> Infaq / Sodaqoh (Zakat)</label
              >
            </div>
          </div>

          <div class="access-group">
            <label class="access-parent">
              <input type="checkbox" class="access-check group-toggle" data-group="kegiatan" /> Kegiatan
            </label>
            <div class="access-children" data-children-of="kegiatan">
              <label class="access-child"><input type="checkbox" class="access-check" /> Jadwal Kegiatan</label>
              <label class="access-child"><input type="checkbox" class="access-check" /> Agenda</label>
              <label class="access-child"><input type="checkbox" class="access-check" /> Jadwal Petugas Sholat</label>
              <label class="access-child"><input type="checkbox" class="access-check" /> Galeri</label>
            </div>
          </div>

          <div class="access-group">
            <label class="access-parent">
              <input type="checkbox" class="access-check" data-group="data-jamaah" /> Data Jemaah
            </label>
          </div>
          <div class="access-group">
            <label class="access-parent">
              <input type="checkbox" class="access-check" data-group="pengumuman" /> Pengumuman
            </label>
          </div>
          <div class="access-group">
            <label class="access-parent">
              <input type="checkbox" class="access-check" data-group="kepengurusan" /> Kepengurusan
            </label>
          </div>
          <div class="access-group">
            <label class="access-parent">
              <input type="checkbox" class="access-check" data-group="relawan" /> Relawan
            </label>
          </div>
          <div class="access-group">
            <label class="access-parent">
              <input type="checkbox" class="access-check" data-group="surat" /> Surat
            </label>
          </div>
          <div class="access-group">
            <label class="access-parent">
              <input type="checkbox" class="access-check" data-group="inventaris" /> Inventaris
            </label>
          </div>
          <div class="access-group">
            <label class="access-parent">
              <input type="checkbox" class="access-check" data-group="laporan" /> Laporan
            </label>
          </div>

          <div class="access-group">
            <label class="access-parent">
              <input type="checkbox" class="access-check group-toggle" data-group="pengaturan" /> Pengaturan
            </label>
            <div class="access-children" data-children-of="pengaturan">
              <label class="access-child"><input type="checkbox" class="access-check" /> Profile Masjid</label>
              <label class="access-child"><input type="checkbox" class="access-check" /> Umum</label>
              <label class="access-child"><input type="checkbox" class="access-check" /> User Management</label>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-text" id="modalCancelBtn">Batal</button>
        <button class="btn btn-primary" id="modalSaveBtn"><i class="fa-solid fa-check"></i> Simpan User</button>
      </div>
    </div>
  </div>

  <!-- MODAL RESET PASSWORD -->
  <div class="modal-overlay" id="resetPasswordOverlay">
    <div class="modal-box">
      <div class="modal-header">
        <h2>Reset Kata Sandi</h2>
        <button class="modal-close" id="resetCloseBtn"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-field">
          <label>Pengguna</label>
          <input type="text" id="resetUserName" disabled />
        </div>
        <div class="form-field" style="margin-top: 12px">
          <label for="resetUserPassword">Password Baru</label>
          <input type="text" id="resetUserPassword" placeholder="Minimal 8 karakter" />
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-text" id="resetCancelBtn">Batal</button>
        <button class="btn btn-primary" id="resetSaveBtn"><i class="fa-solid fa-key"></i> Reset</button>
      </div>
    </div>
  </div>

  @endsection

@push('scripts')
  <script src="{{ asset('assets/js/user-management.js') }}"></script>
@endpush