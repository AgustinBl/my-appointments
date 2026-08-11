@if($confirmedAppointments->isEmpty())
  @include('appointments.tables._empty', [
    'title' => 'No hay citas confirmadas',
    'text'  => $role == 'doctor'
        ? 'Las que confirmes desde «Por confirmar» pasan a esta lista.'
        : 'Cuando el consultorio confirme una cita reservada, la vas a ver acá.',
  ])
@else
  <div class="card card--raised" style="padding: 18px 26px 13px;">
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 190px;">Fecha y hora</th>
            @if($role == 'patient')
              <th>Médico</th>
            @elseif($role == 'doctor')
              <th>Paciente</th>
            @else
              <th>Paciente</th>
              <th>Médico</th>
            @endif
            <th style="width: 150px;">Especialidad</th>
            <th style="width: 110px;">Tipo</th>
            <th style="width: 140px;">Estado</th>
            <th style="width: 130px;" class="cell-actions"></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($confirmedAppointments as $appointment)
            <tr>
              <td class="when">
                <span class="when__main">{{ $appointment->schedule_day_label }} · {{ $appointment->schedule_time_short }}</span>
                <span class="when__rel">{{ $appointment->relative_label }}</span>
              </td>

              @if($role == 'patient')
                <td>{{ $appointment->doctor->name }}</td>
              @elseif($role == 'doctor')
                <td>@include('includes.person', ['name' => $appointment->patient->name, 'meta' => $appointment->patient->email, 'size' => 'sm'])</td>
              @else
                <td>{{ $appointment->patient->name }}</td>
                <td>{{ $appointment->doctor->name }}</td>
              @endif

              <td>{{ $appointment->specialty->name }}</td>
              <td>{{ $appointment->type }}</td>
              <td>@include('includes.status', ['status' => $appointment->status])</td>
              <td class="cell-actions">
                @if($role == 'admin')
                  <a href="{{ url('/appointments/'.$appointment->id) }}" class="btn btn-ghost btn-sm">Ver</a>
                @endif
                <a href="{{ url('/appointments/'.$appointment->id.'/cancel') }}" class="btn btn-ghost btn-sm">Cancelar</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="table-foot">
      <span class="table-foot__count">
        {{ $confirmedAppointments->total() }} {{ $confirmedAppointments->total() == 1 ? 'cita' : 'citas' }}
      </span>
      {{ $confirmedAppointments->links() }}
    </div>
  </div>
@endif
