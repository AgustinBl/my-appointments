@extends('layouts.panel')

@section('kicker', 'Recepción')
@section('page-title', 'Pacientes')
@section('page-context', 'Los pacientes también pueden registrarse solos desde la pantalla de acceso.')

@section('page-actions')
  <a href="{{ url('patients/create') }}" class="btn btn-primary">Nuevo paciente</a>
@endsection

@section('content')

  @if(session('notification'))
    <div class="alert alert-success mb-4">{{ session('notification') }}</div>
  @endif

  <div class="card card--raised" style="padding: 18px 26px 13px;">
    @if($patients->isEmpty())
      <div class="empty">
        <h4>Todavía no hay pacientes</h4>
        <p class="empty__text">Cargalos desde acá, o esperá a que se registren por su cuenta.</p>
        <a href="{{ url('patients/create') }}" class="btn btn-primary">Nuevo paciente</a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Paciente</th>
              <th style="width: 130px;">DNI</th>
              <th style="width: 150px;">Teléfono</th>
              <th>Dirección</th>
              <th style="width: 130px;" class="cell-actions"></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($patients as $patient)
              <tr>
                <td>@include('includes.person', [
                      'name' => $patient->name,
                      'meta' => $patient->email,
                      'tone' => 'avatar--green',
                    ])</td>
                <td class="cell-num">{{ $patient->dni ?: '—' }}</td>
                <td class="cell-num">{{ $patient->phone ?: '—' }}</td>
                <td class="text-muted" style="font-size: 13px;">{{ $patient->address ?: '—' }}</td>
                <td class="cell-actions">
                  <span class="btn-row btn-row--end">
                    <a href="{{ url('/patients/'.$patient->id.'/edit') }}" class="btn btn-ghost btn-sm">Editar</a>
                    <form action="{{ url('/patients/'.$patient->id) }}" method="POST" class="d-inline-block"
                          onsubmit="return confirm('Se elimina a {{ $patient->name }} y su historial de citas. ¿Continuar?');">
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
          {{ $patients->firstItem() }} – {{ $patients->lastItem() }} de {{ $patients->total() }} pacientes
        </span>
        {{ $patients->links() }}
      </div>
    @endif
  </div>

@endsection
