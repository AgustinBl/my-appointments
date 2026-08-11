@php
  $months = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
             'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
  $weekdays = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
  $now = \Carbon\Carbon::today();
  $todayLabel = $weekdays[$now->dayOfWeek].' '.$now->day.' de '.$months[$now->month];
  $firstName = explode(' ', trim(auth()->user()->name))[0];

  // El saludo se arma acá: los @if en línea junto a texto acentuado
  // no los parsea Blade de forma confiable.
  $greeting = $role == 'admin' ? 'Buen día, recepción' : 'Hola, '.$firstName;
@endphp

@extends('layouts.panel')

@section('kicker', $todayLabel)

@section('page-title', $greeting)

@section('page-context')
  @if($pendingCount)
    @if($role == 'admin')
      Hay {{ $pendingCount }} {{ $pendingCount == 1 ? 'cita esperando confirmación' : 'citas esperando confirmación' }}. El resto del día está en orden.
    @elseif($role == 'doctor')
      Tenés {{ $pendingCount }} {{ $pendingCount == 1 ? 'cita' : 'citas' }} esperando que la confirmes.
    @else
      Tenés {{ $pendingCount }} {{ $pendingCount == 1 ? 'cita' : 'citas' }} sin confirmar todavía.
    @endif
  @else
    @if($role == 'patient')
      Acá ves el estado de tus citas y podés reservar una nueva.
    @else
      No hay nada esperando confirmación. El día está en orden.
    @endif
  @endif
@endsection

@section('page-actions')
  @if($role == 'patient')
    <a href="{{ url('/appointments/create') }}" class="btn btn-primary">Reservar una cita</a>
  @elseif($role == 'doctor')
    <a href="{{ url('/my-day') }}" class="btn btn-primary">Ver mi día</a>
  @elseif($pendingCount)
    <a href="{{ url('/appointments') }}" class="btn btn-primary">Ver citas por confirmar</a>
  @endif
@endsection

@section('content')

  @if(session('status'))
    <div class="alert alert-success mb-4">{{ session('status') }}</div>
  @endif

  <div class="metrics">
    @foreach($metrics as $metric)
      <div class="metric {{ $metric['tone'] == 'accent' && $metric['value'] ? 'metric--accent' : ($metric['tone'] == 'green' ? 'metric--green' : '') }}">
        <span class="metric__rule"></span>
        <span class="metric__value">{{ $metric['value'] }}</span>
        <span class="metric__label">{{ $metric['label'] }}</span>
        <span class="metric__hint">{{ $metric['hint'] }}</span>
      </div>
    @endforeach
  </div>

  <div class="{{ $role == 'admin' ? 'grid-side grid-side--lg' : '' }}" style="margin-top: 24px;">

    <div class="card card--raised card-pad">
      <div class="split" style="margin-bottom: 16px;">
        <h4 style="margin: 0;">Próximas citas</h4>
        <a href="{{ url('/appointments') }}" style="font-size: 14px;">Ver todas</a>
      </div>

      @if($upcoming->isEmpty())
        <div class="empty" style="padding: 40px 16px;">
          <span class="empty__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75"
                 stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </span>
          <h4>No hay citas próximas</h4>
          <p class="empty__text">
            @if($role == 'patient')
              Cuando reserves una cita, va a aparecer acá con su hora y su estado.
            @else
              Las citas reservadas de hoy en adelante aparecen en esta lista.
            @endif
          </p>
          @if($role == 'patient')
            <a href="{{ url('/appointments/create') }}" class="btn btn-primary">Reservar una cita</a>
          @endif
        </div>
      @else
        <div class="upnext">
          @foreach($upcoming as $appointment)
            <div class="upnext__row">
              <span class="upnext__when">
                <span class="upnext__time">{{ $appointment->schedule_time_short }}</span>
                <span class="upnext__date">{{ $appointment->schedule_day_label }}</span>
              </span>
              <span style="flex: 1; min-width: 0;">
                <span class="upnext__who">
                  @if($role == 'patient')
                    {{ $appointment->doctor->name }}
                  @else
                    {{ $appointment->patient->name }}
                  @endif
                </span>
                <span class="upnext__meta">
                  @if($role == 'admin')
                    {{ $appointment->doctor->name }} · {{ $appointment->specialty->name }}
                  @else
                    {{ $appointment->specialty->name }} · {{ $appointment->type }}
                  @endif
                </span>
              </span>
              @include('includes.status', ['status' => $appointment->status])
            </div>
          @endforeach
        </div>
      @endif
    </div>

    @if($role == 'admin')
      <div class="card card--raised card-pad">
        <span class="kicker kicker--muted">Aviso a pacientes</span>
        <h4 style="margin: 6px 0 4px;">Mandar un aviso</h4>
        <p class="card-note" style="margin-bottom: 18px;">
          Llega como notificación al celular de todos los pacientes con la app instalada.
        </p>

        @if(session('notification'))
          <div class="alert alert-success mb-3">{{ session('notification') }}</div>
        @endif

        <form action="{{ url('/fcm/send') }}" method="post"
              onsubmit="return confirm('El aviso se manda a {{ $patientCount }} pacientes y no se puede deshacer. ¿Enviarlo?');">
          @csrf
          <div class="field">
            <label for="title">Título</label>
            <input type="text" class="form-control" name="title" id="title"
                   placeholder="Cambio de horario" required>
          </div>
          <div class="field">
            <label for="body">Mensaje</label>
            <textarea name="body" class="form-control" id="body" style="min-height: 110px;"
                      placeholder="El consultorio va a atender de 9 a 13 el lunes 17." required></textarea>
          </div>
          <div class="split" style="margin-top: 18px;">
            <span class="text-muted" style="font-size: 13px;">
              {{ $patientCount }} {{ $patientCount == 1 ? 'paciente' : 'pacientes' }}
            </span>
            <button class="btn btn-primary" type="submit">Enviar el aviso</button>
          </div>
        </form>
      </div>
    @endif

  </div>

@endsection
