@php
  $labels = ['admin' => 'Recepción', 'doctor' => 'Médico', 'patient' => 'Paciente'];

  $upcoming = $confirmedAppointments->total() + $pendingAppointments->total();
  $next = $confirmedAppointments->first() ?: $pendingAppointments->first();
@endphp

@extends('layouts.panel')

@section('kicker', $labels[$role] ?? '')
@section('page-title', $role == 'admin' ? 'Citas médicas' : 'Mis citas')

@section('page-context')
  @if($role == 'admin')
    Todas las citas del consultorio. Las pestañas separan lo que hay que hacer de lo que ya está.
  @elseif($upcoming)
    Tenés {{ $upcoming }} {{ $upcoming == 1 ? 'cita' : 'citas' }} por delante.@if($next) La más próxima es {{ $next->relative_label }}.@endif
  @else
    Por ahora no tenés citas por delante.
  @endif
@endsection

@section('page-actions')
  @if($role == 'patient')
    <a href="{{ url('/appointments/create') }}" class="btn btn-primary">Reservar una cita</a>
  @elseif($role == 'doctor')
    <a href="{{ url('/my-day') }}" class="btn btn-secondary">Ver mi día</a>
  @endif
@endsection

@section('content')

  @if(session('notification'))
    <div class="alert alert-success mb-4">{{ session('notification') }}</div>
  @endif

  <ul class="tabs" id="appointments-tab" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" id="tab-confirmed" data-toggle="pill" href="#confirmed-appointments"
         role="tab" aria-controls="confirmed-appointments" aria-selected="true">
        Confirmadas <span class="n">{{ $confirmedAppointments->total() }}</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="tab-pending" data-toggle="pill" href="#pending-appointments"
         role="tab" aria-controls="pending-appointments" aria-selected="false">
        Por confirmar <span class="n">{{ $pendingAppointments->total() }}</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="tab-old" data-toggle="pill" href="#old-appointments"
         role="tab" aria-controls="old-appointments" aria-selected="false">
        Historial <span class="n">{{ $oldAppointments->total() }}</span>
      </a>
    </li>
  </ul>

  <div class="tab-content" id="appointments-tabContent">
    <div class="tab-pane fade show active" id="confirmed-appointments" role="tabpanel" aria-labelledby="tab-confirmed">
      @include('appointments.tables.confirmed')
    </div>
    <div class="tab-pane fade" id="pending-appointments" role="tabpanel" aria-labelledby="tab-pending">
      @include('appointments.tables.pending')
    </div>
    <div class="tab-pane fade" id="old-appointments" role="tabpanel" aria-labelledby="tab-old">
      @include('appointments.tables.old')
    </div>
  </div>

@endsection
