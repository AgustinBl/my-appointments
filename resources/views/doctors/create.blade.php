@extends('layouts.panel')

@section('kicker', 'Médicos · alta')
@section('page-title', 'Nuevo médico')
@section('page-context', 'Después del alta, el médico carga su horario de atención y recién ahí empieza a recibir reservas.')

@section('content')

  <form action="{{ url('doctors') }}" method="post">
    @csrf

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
                     value="{{ old('name') }}" placeholder="Dra. Carla Miranda" required>
            </div>
            <div class="field">
              <label for="dni">DNI</label>
              <input type="text" name="dni" id="dni" class="form-control"
                     value="{{ old('dni') }}" placeholder="29740318">
            </div>
            <div class="field">
              <label for="phone">Teléfono</label>
              <input type="text" name="phone" id="phone" class="form-control"
                     value="{{ old('phone') }}" placeholder="11 2255 7743">
            </div>
            <div class="field">
              <label for="address">Dirección</label>
              <input type="text" name="address" id="address" class="form-control"
                     value="{{ old('address') }}" placeholder="Av. Cabildo 2200, CABA">
            </div>
          </div>
        </section>

        <section class="card card--raised card-pad">
          <h4>Especialidades</h4>
          <p class="card-note" style="margin-bottom: 18px;">
            Los pacientes lo van a encontrar buscando por estas especialidades.
          </p>

          @if($specialties->isEmpty())
            <p class="field-hint">
              No hay especialidades cargadas.
              <a href="{{ url('specialties') }}">Creá la primera</a> para poder asignarla.
            </p>
          @else
            <div class="check-grid">
              @foreach ($specialties as $specialty)
                <label class="check-row">
                  <input type="checkbox" name="specialties[]" value="{{ $specialty->id }}"
                         @if(in_array($specialty->id, old('specialties', []))) checked @endif>
                  <span class="check__dot"></span>
                  <span>{{ $specialty->name }}</span>
                </label>
              @endforeach
            </div>
          @endif
        </section>

        @include('doctors._access')

      </div>

      @include('doctors._before-save', ['label' => 'Guardar el médico'])
    </div>
  </form>

@endsection

@section('scripts')
  @include('doctors._password-script')
@endsection
