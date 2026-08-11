@props(['crumb' => 'Halaman', 'active', 'title', 'subtitle' => null])

<div class="breadcrumb">{{ $crumb }} / <span>{{ $active }}</span></div>

<div class="page-header">
  <div>
    <h1 class="page-title">{{ $title }}</h1>
    @if($subtitle)
      <p class="page-subtitle">{{ $subtitle }}</p>
    @endif
  </div>
  {{ $slot ?? '' }}
</div>