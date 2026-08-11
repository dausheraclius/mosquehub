@extends('layouts.app')

@section('title', 'MosqueHub - Pengaturan Umum')

@push('styles-before-components')
  <link rel="stylesheet" href="{{ asset('assets/css/umum.css') }}">
@endpush

@section('content')

  <x-content-header title="Pengaturan Umum" subtitle="Ketua YMBPK · 20 Juli 2026" />

  <div class="card settings-card">
    <div class="settings-layout">
      <!-- MINI NAV KIRI -->
      <nav class="settings-nav" id="settingsNav">
        <button class="settings-nav-item active" data-target="panelJabatan">
          <i class="fa-solid fa-sitemap"></i>
          <span>Jabatan</span>
        </button>
        <button class="settings-nav-item" data-target="panelProfil">
          <i class="fa-solid fa-building-columns"></i>
          <span>Profil Aplikasi</span>
        </button>
        <button class="settings-nav-item" data-target="panelNotif">
          <i class="fa-brands fa-whatsapp"></i>
          <span>Notifikasi WhatsApp</span>
        </button>
        <button class="settings-nav-item" data-target="panelTampilan">
          <i class="fa-solid fa-palette"></i>
          <span>Tampilan</span>
        </button>
        <button class="settings-nav-item" data-target="panelKeamanan">
          <i class="fa-solid fa-shield-halved"></i>
          <span>Keamanan Akun</span>
        </button>
        <button class="settings-nav-item" data-target="panelBackup">
          <i class="fa-solid fa-database"></i>
          <span>Backup &amp; Data</span>
        </button>
      </nav>

      <!-- PANEL KANAN -->
      <div class="settings-content">
        <!-- PANEL: JABATAN YMBPK -->
        <section class="settings-panel active" id="panelJabatan">
          <h2 class="settings-panel-title">Jabatan Kepengurusan</h2>
          <p class="settings-panel-desc">
            Atur penamaan jabatan di struktur kepengurusan masjid. Sesuaikan dengan istilah yang dipakai di masjid
            Anda.
          </p>

          <div class="jabatan-panel active" id="jabatanDkm">
            <div class="jabatan-table-header">
              <span class="jabatan-table-title">Daftar Jabatan YMBPK</span>
              <button class="btn btn-primary btn-jabatan-tambah"><i class="fa-solid fa-plus"></i> Tambah</button>
            </div>
            <table class="jabatan-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama Jabatan</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td><input type="text" class="jabatan-input" value="Ketua YMBPK" /></td>
                  <td>
                    <button class="icon-action-btn edit"><i class="fa-solid fa-check"></i></button>
                    <button class="icon-action-btn hapus"><i class="fa-solid fa-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>2</td>
                  <td><input type="text" class="jabatan-input" value="Wakil Ketua YMBPK" /></td>
                  <td>
                    <button class="icon-action-btn edit"><i class="fa-solid fa-check"></i></button>
                    <button class="icon-action-btn hapus"><i class="fa-solid fa-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>3</td>
                  <td><input type="text" class="jabatan-input" value="Sekretaris YMBPK" /></td>
                  <td>
                    <button class="icon-action-btn edit"><i class="fa-solid fa-check"></i></button>
                    <button class="icon-action-btn hapus"><i class="fa-solid fa-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>4</td>
                  <td><input type="text" class="jabatan-input" value="Bendahara YMBPK" /></td>
                  <td>
                    <button class="icon-action-btn edit"><i class="fa-solid fa-check"></i></button>
                    <button class="icon-action-btn hapus"><i class="fa-solid fa-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>5</td>
                  <td><input type="text" class="jabatan-input" value="Sie Pendidikan" /></td>
                  <td>
                    <button class="icon-action-btn edit"><i class="fa-solid fa-check"></i></button>
                    <button class="icon-action-btn hapus"><i class="fa-solid fa-trash"></i></button>
                  </td>
                </tr>
                <tr>
                  <td>6</td>
                  <td><input type="text" class="jabatan-input" value="Sie Pembangunan" /></td>
                  <td>
                    <button class="icon-action-btn edit"><i class="fa-solid fa-check"></i></button>
                    <button class="icon-action-btn hapus"><i class="fa-solid fa-trash"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- PANEL: PROFIL APLIKASI -->
        <section class="settings-panel" id="panelProfil">
          <h2 class="settings-panel-title">Profil Aplikasi</h2>
          <p class="settings-panel-desc">Informasi dasar aplikasi yang dipakai di seluruh sistem.</p>

          <div class="form-row">
            <div class="form-field">
              <label for="appName">Nama Instansi</label>
              <input type="text" id="appName" value="YMBPK Baiturrahim" />
            </div>
            <div class="form-field">
              <label for="appTimezone">Zona Waktu</label>
              <select id="appTimezone">
                <option>WIB (GMT+7)</option>
                <option>WITA (GMT+8)</option>
                <option>WIT (GMT+9)</option>
              </select>
            </div>
          </div>
          <div class="form-row single">
            <div class="form-field">
              <label for="appDateFormat">Format Tanggal</label>
              <select id="appDateFormat">
                <option>Masehi (20 Juli 2026)</option>
                <option>Hijriah (5 Muharram 1448)</option>
                <option>Masehi &amp; Hijriah</option>
              </select>
            </div>
          </div>

          <button class="btn btn-primary settings-save" data-panel-save="Profil Aplikasi">
            <i class="fa-solid fa-check"></i> Simpan Perubahan
          </button>
        </section>

        <!-- PANEL: NOTIFIKASI WHATSAPP -->
        <section class="settings-panel" id="panelNotif">
          <h2 class="settings-panel-title">Notifikasi WhatsApp</h2>
          <p class="settings-panel-desc">
            Atur pesan otomatis apa aja yang dikirim ke nomor WhatsApp jamaah/pengurus.
          </p>

          <div class="form-row single">
            <div class="form-field">
              <label for="waAdminNumber">Nomor WhatsApp Gateway</label>
              <input type="text" id="waAdminNumber" value="+62 812 3456 7890" placeholder="cth: +62 812xxxxxxx" />
            </div>
          </div>

          <div class="toggle-list">
            <div class="toggle-row">
              <div>
                <div class="toggle-title">Reminder Infaq Bulanan</div>
                <div class="toggle-sub">Kirim pengingat tiap awal bulan ke jamaah aktif</div>
              </div>
              <label class="switch">
                <input type="checkbox" checked />
                <span class="switch-track"></span>
              </label>
            </div>
            <div class="toggle-row">
              <div>
                <div class="toggle-title">Reminder Agenda/Kegiatan</div>
                <div class="toggle-sub">Kirim H-1 sebelum kajian/kegiatan berlangsung</div>
              </div>
              <label class="switch">
                <input type="checkbox" checked />
                <span class="switch-track"></span>
              </label>
            </div>
            <div class="toggle-row">
              <div>
                <div class="toggle-title">Notifikasi Jemaah Baru</div>
                <div class="toggle-sub">Kirim ke admin tiap ada jamaah baru daftar</div>
              </div>
              <label class="switch">
                <input type="checkbox" />
                <span class="switch-track"></span>
              </label>
            </div>
            <div class="toggle-row">
              <div>
                <div class="toggle-title">Laporan Keuangan Mingguan</div>
                <div class="toggle-sub">Ringkasan kas masuk/keluar tiap hari Jumat</div>
              </div>
              <label class="switch">
                <input type="checkbox" />
                <span class="switch-track"></span>
              </label>
            </div>
          </div>

          <button class="btn btn-primary settings-save" data-panel-save="Notifikasi WhatsApp">
            <i class="fa-solid fa-check"></i> Simpan Perubahan
          </button>
        </section>

        <!-- PANEL: TAMPILAN -->
        <section class="settings-panel" id="panelTampilan">
          <h2 class="settings-panel-title">Tampilan</h2>
          <p class="settings-panel-desc">Sesuaikan tema aplikasi. Perubahan langsung kepreview di halaman ini.</p>

          <div class="toggle-row" style="margin-bottom: 20px">
            <div>
              <div class="toggle-title">Dark Mode</div>
              <div class="toggle-sub">Ubah tampilan jadi tema gelap</div>
            </div>
            <label class="switch">
              <input type="checkbox" id="darkModeToggleSettings" />
              <span class="switch-track"></span>
            </label>
          </div>

          <div class="form-field" style="margin-bottom: 20px">
            <label>Warna Tema</label>
            <div class="theme-swatches" id="themeSwatches">
              <button class="theme-swatch active" data-color="#1f8a5c" style="background: #1f8a5c" title="Hijau"></button>
              <button class="theme-swatch" data-color="#1f6f8a" style="background: #1f6f8a" title="Biru"></button>
              <button class="theme-swatch" data-color="#8a6a1f" style="background: #8a6a1f" title="Emas"></button>
              <button class="theme-swatch" data-color="#7a1f8a" style="background: #7a1f8a" title="Ungu"></button>
            </div>
          </div>

          <div class="form-field">
            <label>Ukuran Font</label>
            <div class="radio-pill-group" id="fontSizeGroup">
              <button class="radio-pill" data-size="small">Kecil</button>
              <button class="radio-pill active" data-size="medium">Sedang</button>
              <button class="radio-pill" data-size="large">Besar</button>
            </div>
          </div>

          <button class="btn btn-primary settings-save" data-panel-save="Tampilan">
            <i class="fa-solid fa-check"></i> Simpan Perubahan
          </button>
        </section>

        <!-- PANEL: KEAMANAN AKUN -->
        <section class="settings-panel" id="panelKeamanan">
          <h2 class="settings-panel-title">Keamanan Akun</h2>
          <p class="settings-panel-desc">Kelola kata sandi dan sesi login akun kamu.</p>

          <div class="form-row single">
            <div class="form-field">
              <label for="oldPassword">Password Lama</label>
              <input type="password" id="oldPassword" placeholder="Masukkan password lama" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label for="newPassword">Password Baru</label>
              <input type="password" id="newPassword" placeholder="Password baru" />
            </div>
            <div class="form-field">
              <label for="confirmPassword">Konfirmasi Password Baru</label>
              <input type="password" id="confirmPassword" placeholder="Ulangi password baru" />
            </div>
          </div>
          <button class="btn btn-outline" id="btnChangePassword" style="margin-bottom: 24px">
            <i class="fa-solid fa-key"></i> Ganti Password
          </button>

          <div class="toggle-row" style="margin-bottom: 16px">
            <div>
              <div class="toggle-title">Verifikasi 2 Langkah</div>
              <div class="toggle-sub">Butuh kode OTP WhatsApp tiap login dari perangkat baru</div>
            </div>
            <label class="switch">
              <input type="checkbox" />
              <span class="switch-track"></span>
            </label>
          </div>

          <div class="form-field">
            <label>Sesi Login Aktif</label>
            <div class="session-list">
              <div class="session-item">
                <div>
                  <div class="toggle-title">Chrome - Windows</div>
                  <div class="toggle-sub">Bandung, Indonesia &middot; Aktif sekarang</div>
                </div>
                <span class="badge-current">Perangkat ini</span>
              </div>
              <div class="session-item">
                <div>
                  <div class="toggle-title">Safari - iPhone</div>
                  <div class="toggle-sub">Bandung, Indonesia &middot; 2 hari lalu</div>
                </div>
                <button class="btn-sm btn-detail" data-logout-session>Keluar</button>
              </div>
            </div>
          </div>
        </section>

        <!-- PANEL: BACKUP & DATA -->
        <section class="settings-panel" id="panelBackup">
          <h2 class="settings-panel-title">Backup &amp; Data</h2>
          <p class="settings-panel-desc">Amankan data jamaah, keuangan, dan kegiatan secara berkala.</p>

          <div class="toggle-row" style="margin-bottom: 16px">
            <div>
              <div class="toggle-title">Backup Otomatis</div>
              <div class="toggle-sub">Backup semua data ke cloud MosqueHub setiap hari</div>
            </div>
            <label class="switch">
              <input type="checkbox" checked />
              <span class="switch-track"></span>
            </label>
          </div>

          <div class="form-row single">
            <div class="form-field">
              <label for="backupFreq">Frekuensi Backup</label>
              <select id="backupFreq">
                <option>Setiap Hari</option>
                <option>Setiap Minggu</option>
                <option>Setiap Bulan</option>
              </select>
            </div>
          </div>

          <div class="backup-actions">
            <button class="btn btn-outline" id="btnBackupNow">
              <i class="fa-solid fa-cloud-arrow-up"></i> Backup Sekarang
            </button>
            <button class="btn btn-outline" id="btnExportData">
              <i class="fa-solid fa-file-export"></i> Ekspor Data (CSV)
            </button>
            <button class="btn btn-outline" id="btnImportData">
              <i class="fa-solid fa-file-import"></i> Impor Data
            </button>
            <input type="file" id="importFileInput" accept=".csv" hidden />
          </div>

          <div class="toggle-row" style="margin-top: 20px">
            <div>
              <div class="toggle-title" style="color: var(--color-red)">Reset Jabatan &amp; Kepengurusan</div>
              <div class="toggle-sub">Hapus semua data jabatan, hierarki, dan penempatan. Kembali ke default.</div>
            </div>
            <button class="btn btn-outline" id="btnResetJabatan" style="border-color: var(--color-red); color: var(--color-red);">
              <i class="fa-solid fa-rotate-left"></i> Reset
            </button>
          </div>

          <div class="backup-log">
            <div class="backup-log-item">
              <i class="fa-solid fa-circle-check"></i> Backup terakhir: 20 Juli 2026, 04:00 WIB
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

@endsection

@section('modals')
  <div class="toast" id="appToast"></div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/umum.js') }}"></script>
@endpush