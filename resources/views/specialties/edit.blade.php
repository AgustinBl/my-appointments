@extends('layouts.panel')

@section('kicker', 'Especialidades · edición')
@section('page-title', $specialty->name)
@section('page-context', 'Cambiar el nombre no afecta a los médicos que ya la tienen asignada.')

@section('content')

  <div style="max-width: 520px;">
    <div class="card card--raised card-pad">
      @if ($errors->any())
        <div class="notice notice--warn" style="margin-bottom: 16px;">
          <span class="notice__dot">!</span>
          <span>{{ $errors->first() }}</span>
        </div>
      @endif

      <form action="{{ url('specialties/'.$specialty->id) }}" method="post">
        @csrf
        @method('PUT')

        <div class="field">
          <label for="name">Nombre</label>
          <input type="text" name="name" id="name" class="form-control"
                 value="{{ old('name', $specialty->name) }}" required>
        </div>
        <div class="field">
          <label for="description">Descripción</label>
          <textarea name="description" id="description" class="form-control" style="min-height: 84px;"
                    required>{{ old('description', $specialty->description) }}</textarea>
          <p class="field-hint">La ve el paciente al elegir especialidad.</p>
        </div>

        <div class="btn-row" style="margin-top: 20px;">
          <button type="submit" class="btn btn-primary">Guardar los cambios</button>
          <a href="{{ url('specialties') }}" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>

@endsection
