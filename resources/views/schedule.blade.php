@extends('layouts.panel')

@section('kicker', 'Médico')
@section('page-title', 'Horario de atención')
@section('page-context', 'De cada ventana salen franjas de 30 minutos. Lo que marques acá es lo que los pacientes van a poder reservar.')

@section('page-actions')
  <span class="tag tag--green" id="weekTotal">{{ $totalSlots }} franjas por semana</span>
@endsection

@section('content')

  @if(session('notification'))
    <div class="alert alert-success mb-4">{{ session('notification') }}</div>
  @endif

  @if(session('errors'))
    <div class="alert alert-danger mb-4">
      Guardamos los cambios, pero revisá estos días: la hora de inicio es posterior a la de fin.
      <ul class="mt-2">
        @foreach ( session('errors') as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ url('/schedule') }}" method="post" id="scheduleForm">
    @csrf

    <div class="grid-side grid-side--sm">

      <div class="card card--raised" style="padding: 18px 26px 26px;">
        <div class="table-responsive">
          <table class="table" id="weekTable">
            <thead>
              <tr>
                <th style="width: 108px;">Día</th>
                <th style="width: 118px;">Atiende</th>
                <th>Turno mañana</th>
                <th>Turno tarde</th>
                <th style="width: 78px;" class="cell-right">Franjas</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($workDays as $key => $workDay)
                <tr class="week-row {{ $workDay->active ? 'week-row--on' : '' }}" data-day="{{ $key }}">
                  <th scope="row">{{ $days[$key] }}</th>

                  <td>
                    <div class="seg">
                      <label class="seg__opt {{ $workDay->active ? 'is-on' : '' }}">
                        <input type="radio" name="active[{{ $key }}]" value="1" @if($workDay->active) checked @endif>Sí
                      </label>
                      <label class="seg__opt {{ $workDay->active ? '' : 'is-on' }}">
                        <input type="radio" name="active[{{ $key }}]" value="0" @if(!$workDay->active) checked @endif>No
                      </label>
                    </div>
                  </td>

                  {{-- Los selects se ocultan pero siguen en el DOM: el
                       controlador lee los cuatro arrays por posición. --}}
                  <td class="shift-cell" @if(!$workDay->active) style="display:none" @endif>
                    <div class="range">
                      <select class="form-control" name="morning_start[]" aria-label="Inicio del turno mañana, {{ $days[$key] }}">
                        @for ($i = 5; $i <= 11; $i++)
                          <option value="{{ ($i < 10 ? '0' : '') . $i }}:00" @if($i.':00 AM' == $workDay->morning_start) selected @endif>{{ $i }}:00</option>
                          <option value="{{ ($i < 10 ? '0' : '') . $i }}:30" @if($i.':30 AM' == $workDay->morning_start) selected @endif>{{ $i }}:30</option>
                        @endfor
                      </select>
                      <span class="range__sep">a</span>
                      <select class="form-control" name="morning_end[]" aria-label="Fin del turno mañana, {{ $days[$key] }}">
                        @for ($i = 5; $i <= 11; $i++)
                          <option value="{{ ($i < 10 ? '0' : '') . $i }}:00" @if($i.':00 AM' == $workDay->morning_end) selected @endif>{{ $i }}:00</option>
                          <option value="{{ ($i < 10 ? '0' : '') . $i }}:30" @if($i.':30 AM' == $workDay->morning_end) selected @endif>{{ $i }}:30</option>
                        @endfor
                      </select>
                    </div>
                  </td>

                  <td class="shift-cell" @if(!$workDay->active) style="display:none" @endif>
                    <div class="range">
                      <select class="form-control" name="afternoon_start[]" aria-label="Inicio del turno tarde, {{ $days[$key] }}">
                        @for ($i = 1; $i <= 11; $i++)
                          <option value="{{ $i + 12 }}:00" @if($i.':00 PM' == $workDay->afternoon_start) selected @endif>{{ $i + 12 }}:00</option>
                          <option value="{{ $i + 12 }}:30" @if($i.':30 PM' == $workDay->afternoon_start) selected @endif>{{ $i + 12 }}:30</option>
                        @endfor
                      </select>
                      <span class="range__sep">a</span>
                      <select class="form-control" name="afternoon_end[]" aria-label="Fin del turno tarde, {{ $days[$key] }}">
                        @for ($i = 1; $i <= 11; $i++)
                          <option value="{{ $i + 12 }}:00" @if($i.':00 PM' == $workDay->afternoon_end) selected @endif>{{ $i + 12 }}:00</option>
                          <option value="{{ $i + 12 }}:30" @if($i.':30 PM' == $workDay->afternoon_end) selected @endif>{{ $i + 12 }}:30</option>
                        @endfor
                      </select>
                    </div>
                  </td>

                  {{-- Los días sin atención colapsan a una sola celda. --}}
                  <td class="off-cell text-muted" colspan="2" style="font-size: 13px; @if($workDay->active) display:none @endif">
                    No atiende
                  </td>

                  <td class="cell-right cell-num js-slots">{{ $slots[$key] ? $slots[$key] : '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="btn-row btn-row--end" style="margin-top: 20px;">
          <button type="reset" class="btn btn-secondary">Descartar cambios</button>
          <button type="submit" class="btn btn-primary">Guardar el horario</button>
        </div>
      </div>

      <div class="sticky-col">
        <div class="card card--raised card-pad">
          <span class="kicker kicker--muted">Cómo se reparte</span>
          <div class="bars" id="bars" style="margin-top: 14px;">
            @foreach ($workDays as $key => $workDay)
              <div class="bar-row" data-day="{{ $key }}" @if(!$slots[$key]) style="display:none" @endif>
                <span class="bar-row__day">{{ mb_substr($days[$key], 0, 3) }}</span>
                <span class="bar-row__track">
                  <span class="bar-row__fill" style="flex: {{ $slots[$key] }};"></span>
                  <span style="flex: {{ max(0, 24 - $slots[$key]) }};"></span>
                </span>
                <span class="bar-row__n">{{ $slots[$key] }}</span>
              </div>
            @endforeach
          </div>

          <p class="card-note" style="margin-top: 16px;" id="barsNote">
            @if($totalSlots)
              Los pacientes que busquen un día sin atención van a ver el aviso de «no atiende» con tus próximas fechas.
            @else
              Sin ningún día cargado no se puede reservar con vos. Marcá al menos uno.
            @endif
          </p>
        </div>
      </div>

    </div>
  </form>

@endsection

@section('scripts')
<script>
// Franjas de 30 minutos que entran entre dos horas "HH:MM".
function slotsBetween(from, to) {
  const a = from.split(':'), b = to.split(':');
  const minutes = (+b[0] * 60 + +b[1]) - (+a[0] * 60 + +a[1]);
  return minutes > 0 ? Math.floor(minutes / 30) : 0;
}

function recalcRow($row) {
  const on = $row.find('input[type=radio]:checked').val() === '1';
  let total = 0;

  if (on) {
    total = slotsBetween($row.find('[name="morning_start[]"]').val(), $row.find('[name="morning_end[]"]').val())
          + slotsBetween($row.find('[name="afternoon_start[]"]').val(), $row.find('[name="afternoon_end[]"]').val());
  }

  $row.toggleClass('week-row--on', on);
  $row.find('.shift-cell').toggle(on);
  $row.find('.off-cell').toggle(!on);
  $row.find('.js-slots').text(on ? total : '—');

  const day = $row.data('day');
  const $bar = $('#bars .bar-row[data-day="' + day + '"]');
  $bar.toggle(total > 0);
  $bar.find('.bar-row__fill').css('flex', total);
  $bar.find('.bar-row__fill').next().css('flex', Math.max(0, 24 - total));
  $bar.find('.bar-row__n').text(total);

  return total;
}

function recalcAll() {
  let week = 0;
  $('#weekTable tbody tr').each(function () { week += recalcRow($(this)); });
  $('#weekTotal').text(week + (week === 1 ? ' franja por semana' : ' franjas por semana'));
}

$(function () {
  $('#weekTable').on('change', 'input[type=radio], select', function () {
    const $opt = $(this).closest('.seg__opt');
    if ($opt.length) {
      $opt.siblings().removeClass('is-on');
      $opt.addClass('is-on');
    }
    recalcAll();
  });

  // "Descartar cambios" repone los valores originales del form.
  $('#scheduleForm').on('reset', function () { setTimeout(recalcAll, 0); });
});
</script>
@endsection
