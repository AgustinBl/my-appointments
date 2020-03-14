@extends('layouts.panel')

@section('content')
    <div class="card shadow">
      <div class="card-header border-0">
        <div class="row align-items-center">
          <div class="col">
            <h3 class="mb-0">Cita #{{ $appointment->id }}</h3>
          </div>
        </div>
      </div>
      <div class="card-body">
        <ul>
          <li>
            <strong>Fecha:</strong> {{ $appointment->schedule_date }}
          </li>
          <li>
            <strong>Hora:</strong> {{ $appointment->schedule_time }}
          </li>
          
          @if($role == 'patient' || $role == 'admin')
            <li>
              <strong>Médico:</strong> {{ $appointment->doctor->name }}
            </li>
          @endif 

          @if($role =='doctor' || $role == 'admin')
            <li>
              <strong>Paciente:</strong> {{ $appointment->patient->name }}
            </li>
          @endif
          <li>
            <strong>Especialidad:</strong> {{ $appointment->specialty->name }}
          </li>
          <li>
            <strong>Tipo:</strong> {{ $appointment->type }}
          </li>
          <li>
            <strong>Estado:</strong> 
            @if($appointment->status == 'Cancelada')
              <span class="badge badge-danger">Cancelada</span>
            @else
              <span class="badge badge-success">{{ $appointment->status }}</span>
            @endif
          </li>
        </ul>

        @if($appointment->status == 'Cancelada')
          <div class="alert alert-warning">
            <p>Acerca de la cancelación</p>
            <ul>
            @if($appointment->cancellation)
              <li>
                <strong>Motivo de la cancelación:</strong>
                {{ $appointment->cancellation->justification }}
              </li>
              <li>
                <strong>¿Quién cancelo la cita?:</strong>
                {{ $appointment->cancellation->cancelled_by->name }}
              </li>
              <li>
                <strong>Fecha de la cancelación:</strong>
                {{ $appointment->cancellation->created_at }}
              </li>
            @else
            <li>Esta cita fue cancelada antes de su confirmación.</li>
            @endif
            </ul>
          </div>
        @endif
        
        <a href="{{ url('/appointments') }}" class="btn btn-default">
          Volver
        </a>
      </div>    
    </div>
@endsection
