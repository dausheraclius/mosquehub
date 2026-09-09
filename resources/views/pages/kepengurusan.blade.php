@extends('layouts.app')

@section('title', 'MosqueHub - Kepengurusan')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/kepengurusan.css')
@endpush

@section('content')

  <x-page-header crumb="{{ __('menu.pengelolaan') }}" active="{{ __('pages.kepengurusan_title') }}" title="{{ __('pages.kepengurusan_title') }}" subtitle="{{ __('pages.kepengurusan_subtitle') }}" />

  <!-- TAB ORGANISASI: YMBPK / IKRAM (gaya seperti tab ZISWAF) -->
  <div class="org-tabs" id="orgTabs">
    @foreach ($organisasiTersedia as $namaOrganisasi)
      <a href="{{ route('kepengurusan', ['locale' => App::getLocale(), 'organisasi' => $namaOrganisasi]) }}"
         class="org-tab {{ $organisasi === $namaOrganisasi ? 'active' : '' }}"
         data-org="{{ $namaOrganisasi }}">
        <i class="fa-solid {{ $namaOrganisasi === 'IKRAM' ? 'fa-hands-holding-circle' : 'fa-people-group' }}"></i>
        {{ $namaOrganisasi }}
      </a>
    @endforeach
  </div>

  <!-- ACTION ROW: tombol aksi utama -->
  <div class="kepengurusan-actions">
    <button class="btn btn-primary" id="toggleEditBtn"><i class="fa-solid fa-pen"></i> {{ __('general.edit_kepengurusan') }}</button>
    <a href="{{ route('ekspor.kepengurusan', ['locale' => App::getLocale()]) }}" class="btn btn-outline" title="{{ __('general.unduh') }}">
      <i class="fa-solid fa-file-excel"></i> {{ __('general.ekspor_excel') }}
    </a>
  </div>

  <!-- TOP ROW: 3 CARD RINGKASAN -->
  <div class="kepengurusan-top-row">
    <div class="top-card top-card-split">
      <div>
        <div class="top-card-label">{{ __('general.periode_aktif') }}</div>
        <div class="top-card-value">2026 - 2029</div>
      </div>
      <div>
        <div class="top-card-label">{{ __('general.jumlah_pengurus') }}</div>
        <div class="top-card-value" id="jumlahPengurusValue">0</div>
      </div>
    </div>

    <div class="top-card">
      <div class="top-card-badge-row">
        <span class="top-card-label">{{ __('general.jabatan_terisi') }}</span>
        <span class="stat-badge up" id="jabatanTerisiBadge">0 {{ __('general.terisi') }}</span>
      </div>
      <div class="top-card-value" id="jabatanTerisiValue">0</div>
    </div>

    <div class="top-card">
      <div class="top-card-badge-row">
        <span class="top-card-label">{{ __('general.jabatan_kosong') }}</span>
        <span class="stat-badge" id="jabatanKosongBadge" style="background: #fdeaea; color: var(--color-red)">0 {{ __('general.kosong') }}</span>
      </div>
      <div class="top-card-value" id="jabatanKosongValue">0</div>
    </div>
  </div>

  <!-- ORG CHART + LIST JABATAN -->
  <div class="dashboard-grid">
    <!-- KIRI: ORG CHART -->
    <div class="card">
      <div class="org-chart-wrapper" id="orgChartWrapper"></div>
    </div>

    <!-- KANAN: STRUKTUR + PILIH JAMAAH -->
    <div class="card">
      <div class="struktur-section" id="strukturSection" style="display:none;">
        <div class="struktur-section-title">{{ __('general.atur_struktur_jabatan') }}</div>
        <p class="struktur-section-hint">{{ __('general.pilih_atasan_hint') }}</p>
        <div id="strukturList"></div>
      </div>

      <div class="jabatan-list-header">
        <span>{{ __('general.nama_jabatan') }}</span>
        <span>{{ __('general.pilih_jemaah') }}</span>
      </div>
      <div id="jabatanList"></div>

      <div class="jabatan-footer-actions" id="jabatanFooterActions" style="display: none">
        <button class="btn btn-outline" id="resetBtn">{{ __('general.reset') }}</button>
        <button class="btn btn-primary" id="simpanBtn">{{ __('general.simpan_perubahan') }}</button>
      </div>
    </div>
  </div>

@endsection

@section('modals')
  <!-- MODAL: TAMBAH JABATAN BARU -->
  <div class="modal-overlay" id="addJabatanModal">
    <div class="modal-box" style="max-width: 420px;">
      <div class="modal-header">
        <span class="modal-title" id="addJabatanModalTitle">{{ __('general.tambah_jabatan') }}</span>
        <button class="modal-close" id="closeAddJabatanModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">{{ __('general.nama_jabatan_baru') }}</label>
          <input type="text" class="form-control" id="addJabatanInput" placeholder="{{ __('general.contoh_jabatan') }}" autofocus />
        </div>
        <p id="addJabatanHint" style="font-size:12px;color:var(--text-muted);margin-top:8px;"></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" id="cancelAddJabatanBtn">{{ __('general.batal') }}</button>
        <button class="btn btn-primary" id="confirmAddJabatanBtn"><i class="fa-solid fa-plus"></i> {{ __('general.tambah') }}</button>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    window.__JABATAN_LIST__ = @json($namaJabatanList);
    window.__HIERARKI__ = @json($hierarki);
    window.__PENEMPATAN__ = @json($penempatan);
    window.__POSISI_ORG__ = @json($posisiOrg);
    window.__DAFTAR_JAMAAH__ = @json($daftarJamaah);
    window.__ORGANISASI__ = @json($organisasi);
  </script>
  <script src="{{ asset('assets/js/kepengurusan.js') }}?v={{ filemtime(public_path('assets/js/kepengurusan.js')) }}"></script>
@endpush
