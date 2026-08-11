@props(['title', 'subtitle'])

<div class="content-header">
  <div class="content-header-text">
    <h1 class="greeting-title">{{ $title }}</h1>
    <p class="greeting-subtitle">{{ $subtitle }}</p>
  </div>
  @if($slot->isNotEmpty())
    <div class="content-header-actions">
      {{ $slot }}
    </div>
  @endif
</div>