@if($pendingAppointments->isEmpty())
  @include('appointments.tables._empty', [
    'title' => 'Nada esperando confirmación',
    'text'  => $role == 'doctor'
        ? 'Cuando un paciente reserve una franja de tu agenda, la vas a poder confirmar desde acá.'
        : 'Las citas reservadas y todavía sin confirmar aparecen en esta lista.',
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
              <th>Motivo</th>
            @else
              <th>Paciente</th>
              <th>Médico</th>
            @endif
            <th style="width: 150px;">Especialidad</th>
            <th style="width: 110px;">Tipo</th>
            @if($role != 'doctor')
              <th style="width: 140px;">Estado</th>
            @endif
            <th style="width: 200px;" class="cell-actions"></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($pendingAppointments as $appointment)
            <tr>
              <td class="when">
                <span class="when__main">{{ $appointment->schedule_day_label }} · {{ $appointment->schedule_time_short }}</span>
                <span class="when__rel">{{ $appointment->relative_label }}</span>
              </td>

              @if($role == 'patient')
                <td>{{ $appointment->doctor->name }}</td>
              @elseif($role == 'doctor')
                <td>@include('includes.person', ['name' => $appointment->patient->name, 'meta' => $appointment->patient->email, 'size' => 'sm'])</td>
                <td class="text-muted" style="font-size: 13px;">{{ $appointment->description }}</td>
              @else
                <td>{{ $appointment->patient->name }}</td>
                <td>{{ $appointment->doctor->name }}</td>
              @endif

              <td>{{ $appointment->specialty->name }}</td>
              <td>{{ $appointment->type }}</td>

              @if($role != 'doctor')
                <td>@include('includes.status', ['status' => $appointment->status])</td>
              @endif

              <td class="cell-actions">
                <span class="btn-row btn-row--end">
                  @if($role == 'admin')
                    <a href="{{ url('/appointments/'.$appointment->id) }}" class="btn btn-ghost btn-sm">Ver</a>
                  @endif

                  @if($role == 'doctor' || $role == 'admin')
                    <form action="{{ url('/appointments/'.$appointment->id.'/confirm') }}" method="POST" class="d-inline-block">
                      @csrf
                      <button class="btn btn-primary btn-sm" type="submit">Confirmar</button>
                    </form>
                  @endif

                  <form action="{{ url('/appointments/'.$appointment->id.'/cancel') }}" method="POST" class="d-inline-block"
                        onsubmit="return confirm('Se cancela la cita y la franja vuelve a quedar libre. ¿Continuar?');">
                    @csrf
                    <button class="btn btn-ghost btn-sm" type="submit">Cancelar</button>
                  </form>
                </span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="table-foot">
      <span class="table-foot__count">
        {{ $pendingAppointments->total() }} {{ $pendingAppointments->total() == 1 ? 'cita' : 'citas' }} sin confirmar
      </span>
      {{ $pendingAppointments->links() }}
    </div>
  </div>
@endif
