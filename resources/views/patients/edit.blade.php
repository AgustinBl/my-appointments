@extends('layouts.panel')

@section('kicker', 'Pacientes · edición')
@section('page-title', $patient->name)
@section('page-context', 'Los cambios no afectan a las citas ya reservadas.')

@section('content')

  <form action="{{ url('patients/'.$patient->id) }}" method="post">
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
                     value="{{ old('name', $patient->name) }}" required>
            </div>
            <div class="field">
              <label for="dni">DNI</label>
              <input type="text" name="dni" id="dni" class="form-control"
                     value="{{ old('dni', $patient->dni) }}" placeholder="8 dígitos">
            </div>
            <div class="field">
              <label for="phone">Teléfono</label>
              <input type="text" name="phone" id="phone" class="form-control"
                     value="{{ old('phone', $patient->phone) }}">
            </div>
            <div class="field">
              <label for="address">Dirección</label>
              <input type="text" name="address" id="address" class="form-control"
                     value="{{ old('address', $patient->address) }}">
            </div>
          </div>
        </section>

        @include('patients._access')

      </div>

      @include('patients._before-save', ['label' => 'Guardar los cambios'])
    </div>
  </form>

@endsection

@section('scripts')
  @include('doctors._password-script')
@endsection
