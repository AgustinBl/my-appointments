@extends('layouts.panel')

@section('kicker', 'Recepción')
@section('page-title', 'Especialidades')
@section('page-context', 'Es lo primero que elige el paciente al reservar: define qué médicos se le ofrecen.')

@section('content')

  @if(session('notification'))
    <div class="alert alert-success mb-4">{{ session('notification') }}</div>
  @endif

  <div class="grid-side grid-side--xl">

    <div class="card card--raised" style="padding: 18px 26px 13px;">
      @if($specialties->isEmpty())
        <div class="empty">
          <h4>Todavía no hay especialidades</h4>
          <p class="empty__text">Sin al menos una cargada, los pacientes no pueden reservar. Creá la primera acá al lado.</p>
        </div>
      @else
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th style="width: 200px;">Nombre</th>
                <th>Descripción</th>
                <th style="width: 110px;" class="cell-right">Médicos</th>
                <th style="width: 130px;" class="cell-actions"></th>
              </tr>
            </thead>
            <tbody>
              @foreach ($specialties as $specialty)
                <tr>
                  <th scope="row">{{ $specialty->name }}</th>
                  <td class="text-muted">{{ $specialty->description }}</td>
                  <td class="cell-right cell-num">{{ $specialty->users_count }}</td>
                  <td class="cell-actions">
                    <span class="btn-row btn-row--end">
                      <a href="{{ url('/specialties/'.$specialty->id.'/edit') }}" class="btn btn-ghost btn-sm">Editar</a>
                      <form action="{{ url('/specialties/'.$specialty->id) }}" method="POST" class="d-inline-block"
                            onsubmit="return confirm('Se elimina «{{ $specialty->name }}». {{ $specialty->users_count }} {{ $specialty->users_count == 1 ? 'médico deja' : 'médicos dejan' }} de ofrecerla. ¿Continuar?');">
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
      @endif
    </div>

    {{-- El alta vive al lado de la tabla: dos campos no justifican una pantalla. --}}
    <aside class="card card--raised card-pad sticky-col">
      <h4 style="margin: 0 0 4px;">Nueva especialidad</h4>
      <p class="card-note" style="margin-bottom: 18px;">Son dos campos en total. No hace falta una pantalla aparte.</p>

      @if ($errors->any())
        <div class="notice notice--warn" style="margin-bottom: 16px;">
          <span class="notice__dot">!</span>
          <span>{{ $errors->first() }}</span>
        </div>
      @endif

      <form action="{{ url('specialties') }}" method="post">
        @csrf
        <div class="field">
          <label for="name">Nombre</label>
          <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}"
                 placeholder="Oftalmología" required>
        </div>
        <div class="field">
          <label for="description">Descripción</label>
          <textarea name="description" id="description" class="form-control" style="min-height: 84px;"
                    placeholder="Vista y enfermedades del ojo" required>{{ old('description') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Agregar</button>
      </form>
    </aside>

  </div>

@endsection
