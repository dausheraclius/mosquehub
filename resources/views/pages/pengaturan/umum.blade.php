@extends('layouts.app')

@section('title', 'MosqueHub - Pengaturan Umum')

@push('styles-before-components')
  @vite('resources/assets/css/umum.css')
@endpush

@section('content')

  <x-page-header crumb="Pengaturan" active="Umum" title="Pengaturan Umum" subtitle="{{ auth()->user()->role ?? 'Ketua YMBPK' }} · {{ now()->translatedFormat('d F Y') }}" />

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
          <svg width="16" height="16" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true">
            <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
          </svg>
          <span>Notifikasi WhatsApp</span>
        </button>
        <button class="settings-nav-item" data-target="panelTampilan">
          <i class="fa-solid fa-palette"></i>
          <span>Tampilan</span>
        </button>
        <button class="settings-nav-item" data-target="panelJadwalSholat">
          <i class="fa-solid fa-clock"></i>
          <span>Jadwal Sholat</span>
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
                @forelse ($namaJabatanList as $idx => $namaJabatan)
                  <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td><input type="text" class="jabatan-input" value="{{ $namaJabatan }}" data-original="{{ $namaJabatan }}" /></td>
                    <td>
                      <button class="icon-action-btn edit"><i class="fa-solid fa-check"></i></button>
                      <button class="icon-action-btn hapus"><i class="fa-solid fa-trash"></i></button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3" class="empty-state">Belum ada jabatan. Klik "Tambah" untuk membuat.</td>
                  </tr>
                @endforelse
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
              <input type="text" id="appName" value="{{ $pengaturan->app_name ?: 'YMBPK Masjid Al-Firdaus' }}" />
            </div>
            <div class="form-field">
              <label for="appTimezone">Zona Waktu</label>
              <select id="appTimezone">
                <option {{ $pengaturan->timezone === 'WIB (GMT+7)' ? 'selected' : '' }}>WIB (GMT+7)</option>
                <option {{ $pengaturan->timezone === 'WITA (GMT+8)' ? 'selected' : '' }}>WITA (GMT+8)</option>
                <option {{ $pengaturan->timezone === 'WIT (GMT+9)' ? 'selected' : '' }}>WIT (GMT+9)</option>
              </select>
            </div>
          </div>
          <div class="form-row single">
            <div class="form-field">
              <label for="appDateFormat">Format Tanggal</label>
              <select id="appDateFormat">
                <option {{ $pengaturan->date_format === 'Masehi' ? 'selected' : '' }} value="Masehi">Masehi (20 Juli 2026)</option>
                <option {{ $pengaturan->date_format === 'Hijriah' ? 'selected' : '' }} value="Hijriah">Hijriah (5 Muharram 1448)</option>
                <option {{ $pengaturan->date_format === 'Keduanya' ? 'selected' : '' }} value="Keduanya">Masehi &amp; Hijriah</option>
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
              <input type="text" id="waAdminNumber" value="{{ $pengaturan->wa_gateway_number }}" placeholder="cth: +62 812xxxxxxx" />
            </div>
          </div>

          <div class="toggle-list">
            <div class="toggle-row">
              <div>
                <div class="toggle-title">Reminder Infaq Bulanan</div>
                <div class="toggle-sub">Kirim pengingat tiap awal bulan ke jamaah aktif</div>
              </div>
              <label class="switch">
                <input type="checkbox" {{ $pengaturan->notif_infaq_bulanan ? 'checked' : '' }} />
                <span class="switch-track"></span>
              </label>
            </div>
            <div class="toggle-row">
              <div>
                <div class="toggle-title">Reminder Agenda/Kegiatan</div>
                <div class="toggle-sub">Kirim H-1 sebelum kajian/kegiatan berlangsung</div>
              </div>
              <label class="switch">
                <input type="checkbox" {{ $pengaturan->notif_agenda_kegiatan ? 'checked' : '' }} />
                <span class="switch-track"></span>
              </label>
            </div>
            <div class="toggle-row">
              <div>
                <div class="toggle-title">Notifikasi Jemaah Baru</div>
                <div class="toggle-sub">Kirim ke admin tiap ada jamaah baru daftar</div>
              </div>
              <label class="switch">
                <input type="checkbox" {{ $pengaturan->notif_jamaah_baru ? 'checked' : '' }} />
                <span class="switch-track"></span>
              </label>
            </div>
            <div class="toggle-row">
              <div>
                <div class="toggle-title">Laporan Keuangan Mingguan</div>
                <div class="toggle-sub">Ringkasan kas masuk/keluar tiap hari Jumat</div>
              </div>
              <label class="switch">
                <input type="checkbox" {{ $pengaturan->notif_laporan_mingguan ? 'checked' : '' }} />
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

        <!-- PANEL: JADWAL SHOLAT -->
        <section class="settings-panel" id="panelJadwalSholat">
          <h2 class="settings-panel-title">Atur Jadwal Sholat</h2>
          <p class="settings-panel-desc">
            Atur jadwal sholat harian masjid. Jadwal ini yang akan ditampilkan di website publik (landing page).
          </p>

          <div class="sholat-card">
            <div class="sholat-table-header">
              <span class="sholat-table-title">Jadwal Sholat Harian</span>
              <span class="sholat-note">Zona waktu: WIB (GMT+7)</span>
            </div>
            @php
              $sholatJadwal = $pengaturan->sholatJadwal();
            @endphp
            <table class="sholat-table">
              <thead>
                <tr>
                  <th>Sholat</th>
                  <th>Waktu</th>
                  <th>Tampil di Website</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($sholatJadwal as $key => $s)
                  @php
                    [$jamDefault, $menitDefault] = explode(':', $s['waktu']);
                  @endphp
                  <tr>
                    <td><div class="sholat-name"><span class="sholat-dot sholat-dot-{{ $key }}"></span>{{ $s['label'] }}</div></td>
                    <td>
                      <div class="time-picker sholat-time-picker">
                        <select class="form-control time-select" data-sholat="{{ $key }}" data-unit="jam" aria-label="Jam {{ $s['label'] }}">
                          @for ($h = 0; $h < 24; $h++)
                            @php $hv = str_pad($h, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $hv }}" @selected($hv === $jamDefault)>{{ $hv }}</option>
                          @endfor
                        </select>
                        <span class="time-sep">:</span>
                        <select class="form-control time-select" data-sholat="{{ $key }}" data-unit="menit" aria-label="Menit {{ $s['label'] }}">
                          @for ($m = 0; $m < 60; $m++)
                            @php $mv = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $mv }}" @selected($mv === $menitDefault)>{{ $mv }}</option>
                          @endfor
                        </select>
                      </div>
                    </td>
                    <td>
                      <label class="switch">
                        <input type="checkbox" class="sholat-visible" data-sholat="{{ $key }}" {{ $s['tampil'] ? 'checked' : '' }} />
                        <span class="switch-track"></span>
                      </label>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <button class="btn btn-primary settings-save" data-panel-save="Jadwal Sholat">
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
              <div class="toggle-sub">Backup semua data masjid secara berkala</div>
            </div>
            <label class="switch">
              <input type="checkbox" id="backupOtomatisToggle" {{ $pengaturan->backup_otomatis ? 'checked' : '' }} />
              <span class="switch-track"></span>
            </label>
          </div>

          <div class="form-row single">
            <div class="form-field">
              <label for="backupFreq">Frekuensi Backup</label>
              <select id="backupFreq">
                <option value="Setiap Hari" @selected($pengaturan->backup_frekuensi === 'Setiap Hari')>Setiap Hari</option>
                <option value="Setiap Minggu" @selected($pengaturan->backup_frekuensi === 'Setiap Minggu')>Setiap Minggu</option>
                <option value="Setiap Bulan" @selected($pengaturan->backup_frekuensi === 'Setiap Bulan')>Setiap Bulan</option>
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
            <button class="btn btn-outline" id="btnResetJabatan">
              <i class="fa-solid fa-rotate-left"></i> Reset
            </button>
          </div>

          <div class="backup-log">
            <div class="backup-log-item">
              <i class="fa-solid fa-circle-check"></i> Backup otomatis: {{ $pengaturan->backup_otomatis ? 'Aktif (' . $pengaturan->backup_frekuensi . ')' : 'Nonaktif' }}
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

@endsection

@section('modals')
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/umum.js') }}"></script>
@endpush