@extends('layouts.panel')

@section('kicker', 'Recepción')
@section('page-title', 'Médicos')
@section('page-context', 'Cada médico carga su horario de atención; sin horario no puede recibir reservas.')

@section('page-actions')
  <a href="{{ url('doctors/create') }}" class="btn btn-primary">Nuevo médico</a>
@endsection

@section('content')

  @if(session('notification'))
    <div class="alert alert-success mb-4">{{ session('notification') }}</div>
  @endif

  <div class="card card--raised" style="padding: 18px 26px 13px;">
    @if($doctors->isEmpty())
      <div class="empty">
        <h4>Todavía no hay médicos</h4>
        <p class="empty__text">Cargá el primero para que los pacientes puedan empezar a reservar.</p>
        <a href="{{ url('doctors/create') }}" class="btn btn-primary">Nuevo médico</a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Médico</th>
              <th style="width: 300px;">Especialidades</th>
              <th style="width: 130px;">DNI</th>
              <th style="width: 150px;">Teléfono</th>
              <th style="width: 150px;">Horario</th>
              <th style="width: 130px;" class="cell-actions"></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($doctors as $doctor)
              <tr>
                <td>@include('includes.person', ['name' => $doctor->name, 'meta' => $doctor->email])</td>

                <td>
                  <span style="display: flex; flex-wrap: wrap; gap: 6px;">
                    @forelse ($doctor->specialties as $specialty)
                      <span class="tag tag--green">{{ $specialty->name }}</span>
                    @empty
                      <span class="text-muted" style="font-size: 13px;">Sin asignar</span>
                    @endforelse
                  </span>
                </td>

                <td class="cell-num">{{ $doctor->dni ?: '—' }}</td>
                <td class="cell-num">{{ $doctor->phone ?: '—' }}</td>

                <td>
                  {{-- Un médico sin horario no puede recibir reservas y eso
                       tiene que verse desde el listado. --}}
                  @if($doctor->schedule_label)
                    <span class="text-muted" style="font-size: 13px;">{{ $doctor->schedule_label }}</span>
                  @else
                    <span class="tag tag--accent">Sin cargar</span>
                  @endif
                </td>

                <td class="cell-actions">
                  <span class="btn-row btn-row--end">
                    <a href="{{ url('/doctors/'.$doctor->id.'/edit') }}" class="btn btn-ghost btn-sm">Editar</a>
                    <form action="{{ url('/doctors/'.$doctor->id) }}" method="POST" class="d-inline-block"
                          onsubmit="return confirm('Se elimina a {{ $doctor->name }} y su horario de atención. ¿Continuar?');">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-ghost btn-sm" type="submit">Eliminar</button>
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
          {{ $doctors->firstItem() }} – {{ $doctors->lastItem() }} de {{ $doctors->total() }} médicos
        </span>
        {{ $doctors->links() }}
      </div>
    @endif
  </div>

@endsection
