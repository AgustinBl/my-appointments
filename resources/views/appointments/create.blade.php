@extends('layouts.panel')

@section('kicker', 'Paso 1 de 1')
@section('page-title', 'Reservar una cita')
@section('page-context', 'Elegí especialidad y médico, después la franja de 30 minutos que te quede mejor.')

@section('page-actions')
  <span class="tag tag--neutral">Hasta 30 días hacia adelante</span>
@endsection

@section('content')

  @if ($errors->any())
    <div class="notice notice--warn mb-4">
      <span class="notice__dot">!</span>
      <span>
        @foreach ($errors->all() as $error)
          {{ $error }}@if(!$loop->last)<br>@endif
        @endforeach
      </span>
    </div>
  @endif

  <form action="{{ url('appointments') }}" method="post" id="bookForm">
    @csrf
    <div class="grid-side">

      <div class="stack" style="gap: 20px;">

        {{-- 1 · Quién te atiende --}}
        <section class="card card--raised card-pad">
          <div class="step__head">
            <span class="step__n">1</span>
            <h4>Quién te atiende</h4>
          </div>

          <div class="form-grid-2">
            <div class="field">
              <label for="specialty">Especialidad</label>
              <select name="specialty_id" id="specialty" class="form-control" required>
                <option value="">Elegí una especialidad</option>
                @foreach ($specialties as $specialty)
                  <option value="{{ $specialty->id }}" @if(old('specialty_id') == $specialty->id) selected @endif>
                    {{ $specialty->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="field">
              <label for="doctor">Médico</label>
              <select name="doctor_id" id="doctor" class="form-control" required>
                @forelse ($doctors as $doctor)
                  <option value="{{ $doctor->id }}" @if(old('doctor_id') == $doctor->id) selected @endif>
                    {{ $doctor->name }}
                  </option>
                @empty
                  <option value="">Elegí primero una especialidad</option>
                @endforelse
              </select>
            </div>
          </div>

          <div class="doctor-card" id="doctorCard" style="{{ $doctors->count() ? '' : 'display:none' }}">
            <span class="avatar avatar--lg" id="doctorInitials"></span>
            <span>
              <span class="person__name" id="doctorName"></span>
              <span class="person__meta" id="doctorMeta"></span>
            </span>
          </div>
        </section>

        {{-- 2 · Cuándo --}}
        <section class="card card--raised card-pad">
          <div class="step__head">
            <span class="step__n">2</span>
            <h4>Cuándo</h4>
          </div>

          <div class="field" style="max-width: 260px;">
            <label for="date">Fecha</label>
            <input class="form-control datepicker" id="date" name="schedule_date" type="text"
                   value="{{ old('schedule_date', date('Y-m-d')) }}"
                   data-date-format="yyyy-mm-dd"
                   data-date-start-date="{{ date('Y-m-d') }}"
                   data-date-end-date="+30d"
                   autocomplete="off">
          </div>

          <div id="hours">
            @if ($day && $day['attends'])
              @php $slotIndex = 0; @endphp
              @foreach (['morning' => 'Turno mañana', 'afternoon' => 'Turno tarde'] as $key => $shiftName)
                @if ($day[$key] && $day[$key]['total'])
                  <div class="shift">
                    <div class="shift__head">
                      <span class="shift__name">{{ $shiftName }}</span>
                      <span class="shift__meta">
                        {{ $day[$key]['range'] }} · {{ $day[$key]['free'] }} libres de {{ $day[$key]['total'] }}
                      </span>
                    </div>
                    <div class="slots">
                      @foreach ($day[$key]['slots'] as $slot)
                        <label class="slot {{ $slot['taken'] ? 'slot--taken' : '' }}">
                          <input type="radio" name="schedule_time" value="{{ $slot['start'] }}"
                                 @if($slot['taken']) disabled @endif
                                 @if(old('schedule_time') === $slot['start']) checked @endif required>
                          <span class="slot__chip">{{ $slot['start'] }}</span>
                        </label>
                      @endforeach
                    </div>
                  </div>
                @endif
              @endforeach
            @else
              <div class="notice notice--idle">
                <span class="notice__dot">?</span>
                <span>Elegí un médico y una fecha y acá aparecen las franjas libres de ese día.</span>
              </div>
            @endif
          </div>
        </section>

        {{-- 3 · Por qué consultás --}}
        <section class="card card--raised card-pad">
          <div class="step__head">
            <span class="step__n">3</span>
            <h4>Por qué consultás</h4>
          </div>

          <div class="field">
            <label>Tipo de atención</label>
            <div class="seg" id="typeSeg">
              @foreach (['Consulta' => 'Consulta', 'Examen' => 'Examen', 'Operacion' => 'Operación'] as $value => $label)
                <label class="seg__opt {{ old('type', 'Consulta') == $value ? 'is-on' : '' }}">
                  <input type="radio" name="type" value="{{ $value }}"
                         @if(old('type', 'Consulta') == $value) checked @endif>{{ $label }}
                </label>
              @endforeach
            </div>
          </div>

          <div class="field">
            <label for="description">Contale al médico el motivo</label>
            <textarea name="description" id="description" class="form-control"
                      placeholder="Ej.: control anual, me pidieron un electrocardiograma" required>{{ old('description') }}</textarea>
          </div>
        </section>
      </div>

      {{-- Resumen de la reserva --}}
      <aside class="card card--raised card-pad summary">
        <span class="kicker">Tu cita</span>

        <div style="margin: 12px 0 16px;">
          <div class="summary__time" id="sumTime">—</div>
          <div class="summary__date" id="sumDate">Elegí una franja</div>
        </div>

        <hr>

        <dl style="margin: 0;">
          <div class="summary__row"><dt>Médico</dt><dd id="sumDoctor">—</dd></div>
          <div class="summary__row"><dt>Especialidad</dt><dd id="sumSpecialty">—</dd></div>
          <div class="summary__row"><dt>Tipo</dt><dd id="sumType">Consulta</dd></div>
          <div class="summary__row"><dt>Duración</dt><dd>30 min</dd></div>
        </dl>

        <div class="notice notice--ok" style="margin: 16px 0;">
          <span>Queda <strong>Por confirmar</strong> hasta que el consultorio la confirme. Te avisamos al celular.</span>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg-action" id="submitBtn" disabled>
          Reservar esta franja
        </button>
        <a href="{{ url('/appointments') }}" class="btn btn-secondary btn-block">Cancelar</a>
      </aside>

    </div>
  </form>

@endsection

@section('scripts')
  <script src="{{ asset('/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
  <script src="{{ asset('/js/appointments/create.js?v=3') }}"></script>
@endsection
