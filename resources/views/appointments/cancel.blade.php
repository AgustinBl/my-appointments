@extends('layouts.panel')

@section('content')
    <div class="card shadow">
      <div class="card-header border-0">
        <div class="row align-items-center">
          <div class="col">
            <h3 class="mb-0">Cancelar cita</h3>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if(session('notification'))
        <div class="alert alert-success" role="alert">
          {{ session('notification') }}
        </div>
        @endif
 
        @if($role == 'patient')
        <p>Estás a punto de cancelar tu cita reservada el {{$appointment->schedule_date}} con el Dr. {{$appointment->doctor->name}} </p>
        @elseif($role == 'doctor')
        <p>Estás a punto de cancelar tu cita reservada el {{$appointment->schedule_date}} con el paciente {{$appointment->patient->name}} </p>
        @else
        <p>Estás a punto de cancelar la cita reservada el {{$appointment->schedule_date}} con el Dr. {{$appointment->doctor->name}} para el paciente {{$appointment->patient->name}} </p>
        @endif

        <form action="{{ url('/appointments/'.$appointment->id.'/cancel') }}" method="POST">
          @csrf
        <div class="form-group">
          <label for="justification">Por favor indicar motivo de la cancelación:</label>
          <textarea required id="justification" name="justification" rows="3" class="form-control"></textarea>
        </div>
          <button class="btn btn-danger" type="submit">Cancelar cita</button>
          <a class="btn btn-default"
            href="{{ url('/appointments') }}">
              Volver al listado de citas
          </a>
        </form>

      </div>
          
    </div>
@endsection
