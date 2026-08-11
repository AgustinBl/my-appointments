@php
  $personInitials = collect(preg_split('/\s+/', trim($name)))
      ->filter()->take(2)
      ->map(function ($part) { return mb_strtoupper(mb_substr($part, 0, 1)); })
      ->implode('');
@endphp
<span class="person">
  <span class="avatar {{ $tone ?? '' }} {{ ($size ?? '') === 'sm' ? 'avatar--sm' : '' }}">{{ $personInitials }}</span>
  <span>
    <span class="person__name">{{ $name }}</span>
    @if(!empty($meta))
      <span class="person__meta">{{ $meta }}</span>
    @endif
  </span>
</span>
