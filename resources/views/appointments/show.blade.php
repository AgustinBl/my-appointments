@extends('layouts.panel')

@section('kicker', 'Cita #'.$appointment->id)
@section('page-title', $appointment->schedule_day_label.' · '.$appointment->schedule_time_short)
@section('page-context', $appointment->specialty->name.' · '.$appointment->type.' · '.$appointment->relative_label)

@section('page-actions')
  <a href="{{ url('/appointments') }}" class="btn btn-secondary">Volver a las citas</a>
  @if($appointment->status == 'Confirmada')
    <a href="{{ url('/appointments/'.$appointment->id.'/cancel') }}" class="btn btn-primary">Cancelar la cita</a>
  @endif
@endsection

@section('content')

  <div class="grid-side grid-side--md">
    <div class="stack" style="gap: 20px;">

      <div class="card card--raised card-pad">
        <h4>Detalle</h4>

        <dl style="margin: 16px 0 0;">
          <div class="summary__row"><dt>Fecha</dt><dd>{{ $appointment->schedule_day_label }}</dd></div>
          <div class="summary__row"><dt>Hora</dt><dd>{{ $appointment->schedule_time_12 }}</dd></div>
          @if($role == 'patient' || $role == 'admin')
            <div class="summary__row"><dt>Médico</dt><dd>{{ $appointment->doctor->name }}</dd></div>
          @endif
          @if($role == 'doctor' || $role == 'admin')
            <div class="summary__row"><dt>Paciente</dt><dd>{{ $appointment->patient->name }}</dd></div>
          @endif
          <div class="summary__row"><dt>Especialidad</dt><dd>{{ $appointment->specialty->name }}</dd></div>
          <div class="summary__row"><dt>Tipo</dt><dd>{{ $appointment->type }}</dd></div>
          <div class="summary__row"><dt>Duración</dt><dd>30 min</dd></div>
        </dl>

        @if($appointment->description)
          <hr>
          <span class="kicker kicker--muted">Motivo de la consulta</span>
          <p style="margin: 6px 0 0;">{{ $appointment->description }}</p>
        @endif
      </div>

      @if($appointment->status == 'Cancelada')
        <div class="card card--raised card-pad">
          <h4>Cancelación</h4>

          @if($appointment->cancellation)
            <dl style="margin: 16px 0 0;">
              <div class="summary__row"><dt>Canceló</dt><dd>{{ $appointment->cancellation->cancelled_by->name }}</dd></div>
              <div class="summary__row"><dt>Fecha</dt><dd>{{ $appointment->cancellation->created_at->format('d/m/Y H:i') }}</dd></div>
            </dl>
            <hr>
            <span class="kicker kicker--muted">Motivo</span>
            <p style="margin: 6px 0 0;">{{ $appointment->cancellation->justification }}</p>
          @else
            <p class="card-note" style="margin-top: 12px;">
              Esta cita se canceló antes de que la confirmaran, así que no quedó registrado un motivo.
            </p>
          @endif
        </div>
      @endif
    </div>

    <aside class="card card--raised card-pad sticky-col">
      <span class="kicker kicker--muted">Estado</span>
      <div style="margin: 12px 0 16px;">
        @include('includes.status', ['status' => $appointment->status])
      </div>

      <p class="card-note" style="margin: 0;">
        @if($appointment->status == 'Reservada')
          La franja está reservada, pero la cita no queda firme hasta que el consultorio la confirme.
        @elseif($appointment->status == 'Confirmada')
          La cita está confirmada. Si se cancela, la franja vuelve a quedar libre.
        @elseif($appointment->status == 'Atendida')
          La consulta ya se realizó.
        @else
          La cita se canceló y la franja volvió a quedar disponible.
        @endif
      </p>
    </aside>
  </div>

@endsection
