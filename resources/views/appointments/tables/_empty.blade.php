<div class="card card--raised">
  <div class="empty">
    <span class="empty__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75"
           stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <polyline points="12 6 12 12 16 14"></polyline>
      </svg>
    </span>
    <h4>{{ $title }}</h4>
    <p class="empty__text">{{ $text }}</p>
    @if(auth()->user()->role == 'patient')
      <a href="{{ url('/appointments/create') }}" class="btn btn-primary">Reservar una cita</a>
    @endif
  </div>
</div>
