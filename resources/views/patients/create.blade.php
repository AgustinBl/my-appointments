@extends('layouts.panel')

@section('kicker', 'Pacientes · alta')
@section('page-title', 'Nuevo paciente')
@section('page-context', 'Usá esta pantalla cuando cargues vos la ficha; el paciente también puede registrarse solo.')

@section('content')

  <form action="{{ url('patients') }}" method="post">
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
                     value="{{ old('name') }}" placeholder="Lucía Ferreyra" required>
            </div>
            <div class="field">
              <label for="dni">DNI</label>
              <input type="text" name="dni" id="dni" class="form-control"
                     value="{{ old('dni') }}" placeholder="8 dígitos">
            </div>
            <div class="field">
              <label for="phone">Teléfono</label>
              <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="field">
              <label for="address">Dirección</label>
              <input type="text" name="address" id="address" class="form-control" value="{{ old('address') }}">
            </div>
          </div>
        </section>

        @include('patients._access')

      </div>

      @include('patients._before-save', ['label' => 'Guardar el paciente'])
    </div>
  </form>

@endsection

@section('scripts')
  @include('doctors._password-script')
@endsection
