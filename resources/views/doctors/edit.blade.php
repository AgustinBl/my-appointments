@extends('layouts.panel')

@section('kicker', 'Médicos · edición')
@section('page-title', $doctor->name)
@section('page-context', 'Los cambios no afectan a las citas ya reservadas.')

@section('content')

  <form action="{{ url('doctors/'.$doctor->id) }}" method="post">
    @csrf
    @method('PUT')

    <div class="grid-side grid-side--md">
      <div class="stack" style="gap: 20px;">

        @if ($errors->any())
          <div class="notice notice--warn">
            <span class="notice__dot">!</span>
            <span>
              @foreach ($errors->all() as $error)
                {{ $error }}@if(!$loop->last)<br>@endif
              @endforeach
            </span>
          </div>
        @endif

        <section class="card card--raised card-pad">
          <h4>Datos personales</h4>
          <div class="form-grid-2" style="margin-top: 18px;">
            <div class="field">
              <label for="name">Nombre y apellido</label>
              <input type="text" name="name" id="name" class="form-control"
                     value="{{ old('name', $doctor->name) }}" required>
            </div>
            <div class="field">
              <label for="dni">DNI</label>
              <input type="text" name="dni" id="dni" class="form-control"
                     value="{{ old('dni', $doctor->dni) }}" placeholder="8 dígitos">
            </div>
            <div class="field">
              <label for="phone">Teléfono</label>
              <input type="text" name="phone" id="phone" class="form-control"
                     value="{{ old('phone', $doctor->phone) }}">
            </div>
            <div class="field">
              <label for="address">Dirección</label>
              <input type="text" name="address" id="address" class="form-control"
                     value="{{ old('address', $doctor->address) }}">
            </div>
          </div>
        </section>

        <section class="card card--raised card-pad">
          <h4>Especialidades</h4>
          <p class="card-note" style="margin-bottom: 18px;">
            Los pacientes lo van a encontrar buscando por estas especialidades.
          </p>

          @if($specialties->isEmpty())
            <p class="field-hint">No hay especialidades cargadas.</p>
          @else
            <div class="check-grid">
              @foreach ($specialties as $specialty)
                <label class="check-row">
                  <input type="checkbox" name="specialties[]" value="{{ $specialty->id }}"
                         @if(in_array($specialty->id, old('specialties', $specialty_ids->all()))) checked @endif>
                  <span class="check__dot"></span>
                  <span>{{ $specialty->name }}</span>
                </label>
              @endforeach
            </div>
          @endif
        </section>

        @include('doctors._access')

      </div>

      @include('doctors._before-save', ['label' => 'Guardar los cambios'])
    </div>
  </form>

@endsection

@section('scripts')
  @include('doctors._password-script')
@endsection
