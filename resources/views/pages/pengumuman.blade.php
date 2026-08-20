@extends('layouts.app')

@section('title', 'MosqueHub - Pengumuman')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/pengumuman.css')
@endpush

@section('content')

  <x-page-header active="{{ __('menu.pengumuman') }}" title="{{ __('pages.pengumuman_title') }}" subtitle="{{ __('pages.pengumuman_subtitle') }}">
    <button class="btn btn-primary" id="btnBuatPengumuman">
      <i class="fa-solid fa-plus"></i> {{ __('general.buat_pengumuman') }}
    </button>
    <a href="{{ route('ekspor.pengumuman', ['locale' => App::getLocale()]) }}" class="btn btn-outline" title="{{ __('general.unduh') }}">
      <i class="fa-solid fa-file-excel"></i> {{ __('general.ekspor_excel') }}
    </a>
  </x-page-header>

  <!-- STATISTIK PENGUMUMAN (Interaktif) -->
  <div class="stat-cards">
    <div class="stat-card custom-stat-card clickable" onclick="filterPengumuman('semua')">
      <div class="stat-card-top">
        <span class="stat-label">{{ __('general.total') }} {{ __('menu.pengumuman') }}</span>
      </div>
      <div class="stat-value-row">
        <i class="fa-regular fa-file-lines stat-icon-sm"></i>
        <span class="stat-value-sm" id="statTotal">{{ $stats['total'] }}</span>
      </div>
    </div>

    <div class="stat-card custom-stat-card clickable" onclick="filterPengumuman('aktif')">
      <div class="stat-card-top">
        <span class="stat-label">{{ __('menu.pengumuman') }} {{ __('general.aktif') }}</span>
      </div>
      <div class="stat-value-row">
        <i class="fa-regular fa-circle-check stat-icon-sm"></i>
        <span class="stat-value-sm" id="statAktif">{{ $stats['aktif'] }}</span>
      </div>
    </div>

    <div class="stat-card custom-stat-card clickable" onclick="filterPengumuman('terjadwal')">
      <div class="stat-card-top">
        <span class="stat-label">{{ __('general.terjadwal') }}</span>
      </div>
      <div class="stat-value-row">
        <i class="fa-regular fa-calendar-days stat-icon-sm"></i>
        <span class="stat-value-sm" id="statTerjadwal">{{ $stats['terjadwal'] }}</span>
      </div>
    </div>

    <div class="stat-card custom-stat-card clickable" onclick="filterPengumuman('arsip')">
      <div class="stat-card-top">
        <span class="stat-label">{{ __('general.arsip') }}</span>
      </div>
      <div class="stat-value-row">
        <i class="fa-solid fa-box-archive stat-icon-sm"></i>
        <span class="stat-value-sm" id="statArsip">{{ $stats['arsip'] }}</span>
      </div>
    </div>
  </div>

  <!-- FITUR UTAMA (Card besar) -->
  @if ($featured)
    <div class="card featured-card clickable" onclick="openModal({{ $featured->id }})">
      <div class="featured-img-placeholder">
        <h3 class="featured-img-title">{{ $featured->judul }}</h3>
      </div>
      <div class="featured-content">
        <div class="featured-title-wrap">
          <h2 class="featured-title">
            {{ $featured->judul }}
          </h2>
        </div>
        <p class="featured-desc">
          {{ Str::limit($featured->isi, 140) }}
        </p>
        <div class="tags-container">
          @if ($featured->kategori)
            <span class="tag">{{ $featured->kategori }}</span>
          @endif
          @if ($featured->status === 'Aktif')
            <span class="status-badge status-aktif">{{ $featured->status }}</span>
          @endif
        </div>
        <button class="btn btn-primary btn-featured">{{ __('general.detail') }}</button>
      </div>
    </div>
  @endif

  <!-- FILTERS -->
  <div class="filter-bar">
    <div class="filter-group filter-search-group">
      <span class="filter-label">Cari</span>
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Cari" id="searchPengumuman" />
      </div>
    </div>
    <div class="filter-group">
      <span class="filter-label">Kategori</span>
      <select class="filter-select" id="kategoriFilter">
        <option value="">Kategori</option>
        <option value="umum">Umum</option>
        <option value="kegiatan">Kegiatan</option>
        <option value="darurat">Darurat</option>
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">Status</span>
      <select class="filter-select" id="statusFilter">
        <option value="">Status</option>
        <option value="aktif">Aktif</option>
        <option value="terjadwal">Terjadwal</option>
        <option value="arsip">Arsip</option>
      </select>
    </div>
    <div class="filter-actions">        <button class="btn-outline" id="btnResetFilter"><i class="fa-solid fa-rotate-left"></i> {{ __('general.reset') }}</button>
    </div>
  </div>

  <!-- LIST PENGUMUMAN -->
  <div class="pengumuman-list-container">
    @forelse ($list as $pengumuman)
      <div
        class="card pengumuman-item"
        data-status="{{ strtolower($pengumuman->status) }}"
        data-category="{{ strtolower($pengumuman->kategori ?? '') }}"
        data-pengumuman-id="{{ $pengumuman->id }}"
        onclick="openModal({{ $pengumuman->id }})"
      >
        <div class="pengumuman-item-left">
          <div class="list-img-placeholder">
            <i class="fa-solid fa-mosque"></i>
          </div>
          <div>
            <h4 class="pengumuman-item-title">{{ $pengumuman->judul }}</h4>
            <p class="pengumuman-item-meta">
              {{ Str::limit($pengumuman->isi, 70) }} - {{ $pengumuman->tanggal->format('d M Y') }}
            </p>
          </div>
        </div>
        <div class="pengumuman-item-right">
          @if ($pengumuman->status === 'Aktif')
            <span class="status-badge status-aktif">Aktif</span>
          @elseif ($pengumuman->status === 'Terjadwal')
            <span class="status-badge status-akan-datang">Terjadwal</span>
          @else
            <span class="status-badge status-arsip">Arsip</span>
          @endif
          <div class="pengumuman-actions">
            <button class="icon-action-btn btn-detail" title="Lihat Detail" onclick="event.stopPropagation(); openModal({{ $pengumuman->id }})"><i class="fa-regular fa-eye"></i></button>
            <button class="icon-action-btn edit btn-edit" title="Edit" onclick="event.stopPropagation(); openEditModal({{ $pengumuman->id }})">
              <i class="fa-solid fa-pen"></i>
            </button>
            <button class="icon-action-btn hapus btn-hapus" title="Hapus" onclick="event.stopPropagation(); openDeleteModal({{ $pengumuman->id }})">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </div>
      </div>
    @empty
      <div class="card pengumuman-item" style="justify-content:center">
        <p class="empty-state">Belum ada pengumuman. Klik "Buat Pengumuman" untuk membuat yang pertama.</p>
      </div>
    @endforelse
  </div>

@endsection

@section('modals')
  <!-- MODAL: DETAIL PENGUMUMAN -->
  <div class="modal-overlay" id="detailModal">
    <div class="modal-box">
      <div class="modal-header">
        <div>
          <h2 id="modalDetailTitle">-</h2>
          <p class="modal-subtitle" id="modalDetailSubtitle">-</p>
        </div>
        <button class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <div class="modal-body">
        <div class="modal-img-placeholder">
          <h3 class="modal-img-title" id="modalDetailImgTitle">-</h3>
        </div>
        <p id="modalDetailIsi">-</p>
        <div class="tags-container" id="modalDetailTags" style="margin-top: 14px;"></div>
      </div>
    </div>
  </div>

  <!-- MODAL: BUAT / EDIT PENGUMUMAN -->
  <div class="modal-overlay" id="formModal">
    <div class="modal-box">
      <div class="modal-header">
        <h2 id="formModalTitle">Buat Pengumuman</h2>
        <button class="modal-close" onclick="closeFormModal()" type="button" aria-label="Tutup">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form method="POST" action="{{ route('pengumuman.store', ['locale' => App::getLocale()]) }}" id="pengumumanForm">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST" />
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label" for="inputJudul">Judul Pengumuman</label>
            <input class="form-input" type="text" id="inputJudul" name="judul" placeholder="cth: Kajian Akbar Muharram" required />
          </div>

          <div class="form-row" style="margin-top:14px;">
            <div class="form-group">
              <label class="form-label" for="inputKategori">Kategori</label>
              <select class="form-select" id="inputKategori" name="kategori" data-native-select>
                <option value="Umum">Umum</option>
                <option value="Kegiatan">Kegiatan</option>
                <option value="Darurat">Darurat</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="inputStatus">Status</label>
              <select class="form-select" id="inputStatus" name="status" data-native-select>
                <option value="Aktif">Aktif</option>
                <option value="Terjadwal">Terjadwal</option>
                <option value="Arsip">Arsip</option>
              </select>
            </div>
          </div>

          <div class="form-group" style="margin-top:14px;">
            <label class="form-label" for="inputTanggal">Tanggal</label>
            <input class="form-input" type="date" id="inputTanggal" name="tanggal" value="{{ date('Y-m-d') }}" required />
          </div>

          <div class="form-group" style="margin-top:14px;">
            <label class="form-label" for="inputIsi">Isi Pengumuman</label>
            <textarea class="form-textarea" id="inputIsi" name="isi" rows="5" placeholder="Tulis isi pengumuman secara lengkap..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-outline" onclick="closeFormModal()">{{ __('general.batal') }}</button>
          <button type="submit" class="btn btn-primary" id="formSubmitBtn"><i class="fa-solid fa-check"></i> {{ __('general.simpan') }} {{ __('menu.pengumuman') }}</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts-late')
  <script>
    @if (session('success'))
      showToast(@json(session('success')), 'fa-solid fa-circle-check');
    @endif

    window.__PENGUMUMAN_DATA__ = @json($pengumumanList);
    window.__PENGUMUMAN_ROUTES__ = {
      store: '{{ route('pengumuman.store', ['locale' => App::getLocale()]) }}',
      update: '{{ route('pengumuman.update', ['__ID__', 'locale' => App::getLocale()]) }}',
      destroy: '{{ route('pengumuman.destroy', ['__ID__', 'locale' => App::getLocale()]) }}',
    };
  </script>
  <script src="{{ asset('assets/js/pengumuman.js') }}"></script>
@endpush
