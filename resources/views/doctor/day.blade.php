@extends('layouts.panel')

@section('kicker', $header['ranges'] ? $header['weekday'].' de atención' : $header['weekday'])
@section('page-title', $header['long'])

@section('page-context')
  @if($header['ranges'])
    {{ $header['ranges'] }} · {{ $summary['citas'] }} {{ $summary['citas'] == 1 ? 'cita' : 'citas' }}{{ $summary['pending'] ? ', '.$summary['pending'].' sin confirmar' : '' }}.
  @else
    Este día no tenés atención cargada.
  @endif
@endsection

@section('page-actions')
  <a href="{{ url('/my-day?date='.$header['prev']) }}" class="btn btn-icon" aria-label="Día anterior">‹</a>
  <a href="{{ url('/my-day') }}" class="btn btn-secondary {{ $header['isToday'] ? 'disabled' : '' }}">Hoy</a>
  <a href="{{ url('/my-day?date='.$header['next']) }}" class="btn btn-icon" aria-label="Día siguiente">›</a>
@endsection

@section('content')

  @if(session('notification'))
    <div class="alert alert-success mb-4">{{ session('notification') }}</div>
  @endif

  <div class="grid-side grid-side--sm">

    <div class="card card--raised card-pad">
      @if(count($rows))
        <div class="rail">
          @foreach($rows as $row)
            <div class="rail__row">
              <span class="rail__time">{{ $row['time'] }}</span>

              @if($row['type'] == 'off')
                <div class="rail__block rail__block--off">
                  <span class="rail__muted">Fuera del horario de atención</span>
                  <span class="rail__muted">{{ $row['range'] }}</span>
                </div>

              @elseif($row['type'] == 'free')
                <div class="rail__block rail__block--free">
                  <span class="rail__muted">Libre</span>
                </div>

              @else
                @php $appointment = $row['appointment']; @endphp
                <div class="rail__block rail__block--{{ $row['type'] }}">
                  @include('includes.person', [
                    'name' => $appointment->patient->name,
                    'meta' => $appointment->type.' · '.$appointment->description,
                    'tone' => $row['type'] == 'confirmed' ? 'avatar--green' : 'avatar--accent',
                    'size' => 'md',
                  ])

                  <span class="rail__end">
                    @include('includes.status', ['status' => $appointment->status])
                    @if($row['type'] == 'pending')
                      <form action="{{ url('/appointments/'.$appointment->id.'/confirm') }}" method="POST">
                        @csrf
                        <button class="btn btn-primary btn-sm" type="submit">Confirmar</button>
                      </form>
                    @endif
                  </span>
                </div>
              @endif
            </div>
          @endforeach
        </div>
      @else
        <div class="empty">
          <span class="empty__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75"
                 stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </span>
          <h4>Este día no atendés</h4>
          <p class="empty__text">
            No hay franjas porque {{ mb_strtolower($header['weekday']) }} está sin atención en tu horario.
            Cargalo y los pacientes van a poder reservar.
          </p>
          <a href="{{ url('/schedule') }}" class="btn btn-primary">Cargar mi horario</a>
        </div>
      @endif
    </div>

    <div class="sticky-col stack">
      <div class="card card--raised card-pad">
        <span class="kicker kicker--muted">El día en números</span>
        <div style="margin-top: 12px;">
          <div class="figure-row">
            <span class="figure-row__label">Citas</span>
            <span class="figure-row__value">{{ $summary['citas'] }}</span>
          </div>
          <div class="figure-row {{ $summary['pending'] ? 'figure-row--accent' : '' }}">
            <span class="figure-row__label">Por confirmar</span>
            <span class="figure-row__value">{{ $summary['pending'] }}</span>
          </div>
          <div class="figure-row">
            <span class="figure-row__label">Franjas libres</span>
            <span class="figure-row__value">{{ $summary['free'] }}</span>
          </div>
        </div>
      </div>

      @if($summary['pending'])
        <div class="card card-pad" style="background: var(--accent-100);">
          <h5>
            {{ $summary['pending'] == 1 ? 'Una espera tu confirmación' : $summary['pending'].' esperan tu confirmación' }}
          </h5>
          @php
            // La enumeración se arma acá para que no queden espacios sueltos
            // por los saltos de línea del bucle.
            $names = $pending->map(function ($appointment) {
                return $appointment->patient->name.' a las '.$appointment->schedule_time_short;
            })->all();

            $last = array_pop($names);
            $who = $names ? implode(', ', $names).' y '.$last : $last;
          @endphp
          <p style="font-size: 13px; color: var(--muted-strong); margin: 0 0 16px;">
            {{ $who }}. Al confirmar, {{ $summary['pending'] == 1 ? 'le llega el aviso' : 'les llega el aviso' }} al celular.
          </p>
          <form action="{{ url('/my-day/confirm-all') }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $header['date'] }}">
            <button class="btn btn-primary" type="submit">
              {{ $summary['pending'] == 1 ? 'Confirmar la cita' : 'Confirmar las '.$summary['pending'] }}
            </button>
          </form>
        </div>
      @endif
    </div>

  </div>

@endsection
