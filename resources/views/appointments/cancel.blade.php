@extends('layouts.panel')

@section('kicker', 'Cita #'.$appointment->id)
@section('page-title', 'Cancelar la cita')
@section('page-context', 'La franja vuelve a quedar disponible y le avisamos al paciente.')

@section('content')

  <div class="grid-side grid-side--md">

    <div class="card card--raised card-pad">
      <h4>Motivo de la cancelación</h4>
      <p class="card-note" style="margin-bottom: 18px;">
        Queda guardado en el historial de la cita y lo ve quien la consulte.
      </p>

      @if(session('notification'))
        <div class="alert alert-success mb-3">{{ session('notification') }}</div>
      @endif

      <form action="{{ url('/appointments/'.$appointment->id.'/cancel') }}" method="POST">
        @csrf
        <div class="field">
          <label for="justification">Por qué se cancela</label>
          <textarea required id="justification" name="justification" class="form-control"
                    placeholder="Ej.: el médico reprograma por una urgencia"></textarea>
        </div>

        <div class="btn-row" style="margin-top: 20px;">
          <button class="btn btn-primary" type="submit">Cancelar la cita</button>
          <a class="btn btn-secondary" href="{{ url('/appointments') }}">Volver sin cancelar</a>
        </div>
      </form>
    </div>

    <aside class="card card--raised card-pad sticky-col">
      <span class="kicker">La cita</span>

      <div style="margin: 12px 0 16px;">
        <div class="summary__time">{{ $appointment->schedule_time_short }}</div>
        <div class="summary__date">{{ $appointment->schedule_day_label }} · {{ $appointment->relative_label }}</div>
      </div>

      <hr>

      <dl style="margin: 0;">
        @if($role == 'patient' || $role == 'admin')
          <div class="summary__row"><dt>Médico</dt><dd>{{ $appointment->doctor->name }}</dd></div>
        @endif
        @if($role == 'doctor' || $role == 'admin')
          <div class="summary__row"><dt>Paciente</dt><dd>{{ $appointment->patient->name }}</dd></div>
        @endif
        <div class="summary__row"><dt>Especialidad</dt><dd>{{ $appointment->specialty->name }}</dd></div>
        <div class="summary__row"><dt>Tipo</dt><dd>{{ $appointment->type }}</dd></div>
      </dl>
    </aside>

  </div>

@endsection
