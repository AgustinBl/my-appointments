@extends('layouts.panel')

@section('kicker', 'Especialidades · alta')
@section('page-title', 'Nueva especialidad')
@section('page-context', 'Una vez creada, asignásela a los médicos que la atienden.')

@section('content')

  <div style="max-width: 520px;">
    <div class="card card--raised card-pad">
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
          <input type="text" name="name" id="name" class="form-control"
                 value="{{ old('name') }}" placeholder="Oftalmología" required>
        </div>
        <div class="field">
          <label for="description">Descripción</label>
          <textarea name="description" id="description" class="form-control" style="min-height: 84px;"
                    placeholder="Vista y enfermedades del ojo" required>{{ old('description') }}</textarea>
          <p class="field-hint">La ve el paciente al elegir especialidad.</p>
        </div>

        <div class="btn-row" style="margin-top: 20px;">
          <button type="submit" class="btn btn-primary">Guardar la especialidad</button>
          <a href="{{ url('specialties') }}" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>

@endsection
