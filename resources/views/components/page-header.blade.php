@props(['crumb' => 'Halaman', 'active', 'title', 'subtitle' => null, 'titleId' => null, 'subtitleId' => null])

<div class="breadcrumb">{{ $crumb }} / <span>{{ $active }}</span></div>

<div class="page-header">
  <div>
    <h1 class="page-title" @if($titleId) id="{{ $titleId }}" @endif>{{ $title }}</h1>
    @if($subtitle)
      <p class="page-subtitle" @if($subtitleId) id="{{ $subtitleId }}" @endif>{{ $subtitle }}</p>
    @endif
  </div>
  <div class="page-header-actions">{{ $slot ?? '' }}</div>
</div>
