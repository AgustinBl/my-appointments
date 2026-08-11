/**
 * Pantalla de reserva (03).
 *
 * Especialidad → médicos → (médico + fecha) → franjas → elegir una.
 * El bloque de franjas tiene cuatro estados: sin elegir, cargando, con franjas
 * y sin atención ese día. Las franjas tomadas se muestran tachadas, no se
 * ocultan: así se entiende que el médico sí atiende y que la hora ya está dada.
 */

let $specialty, $doctor, $date, $hours, $form, $submit;
let currentDoctor = null;
let slotIndex = 0;

const MONTHS = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
const WEEKDAYS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

$(function () {
  $specialty = $('#specialty');
  $doctor = $('#doctor');
  $date = $('#date');
  $hours = $('#hours');
  $form = $('#bookForm');
  $submit = $('#submitBtn');

  $specialty.on('change', loadDoctors);
  $doctor.on('change', function () { refreshDoctorCard(); loadHours(); });
  $date.on('change', loadHours);

  // Delegado: los chips se redibujan en cada carga.
  $hours.on('change', 'input[name="schedule_time"]', onSlotPicked);

  $('#typeSeg').on('change', 'input', onTypePicked);

  // Al volver de un error de validación el form ya trae médico y fecha:
  // hay que repintar la tarjeta y el resumen con esos valores.
  refreshDoctorCard();
  syncSummaryFromForm();
});

/* ------------------------------------------------------------- helpers --- */

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, c => (
    { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
  ));
}

function initialsOf(name) {
  return String(name).trim().split(/\s+/).slice(0, 2)
    .map(part => part.charAt(0).toUpperCase()).join('');
}

/** "2026-08-13" → "jueves 13 de agosto" */
function longDate(value) {
  const parts = String(value).split('-');
  if (parts.length !== 3) return '';
  const date = new Date(+parts[0], +parts[1] - 1, +parts[2]);
  if (isNaN(date)) return '';
  return `${WEEKDAYS[date.getDay()]} ${date.getDate()} de ${MONTHS[date.getMonth()]}`;
}

/* -------------------------------------------------------------- médicos --- */

function loadDoctors() {
  const specialtyId = $specialty.val();

  if (!specialtyId) {
    $doctor.html('<option value="">Elegí primero una especialidad</option>');
    currentDoctor = null;
    refreshDoctorCard();
    showIdle();
    syncSummaryFromForm();
    return;
  }

  $.getJSON(`/api/specialties/${specialtyId}/doctors`, function (doctors) {
    if (!doctors.length) {
      $doctor.html('<option value="">No hay médicos en esta especialidad</option>');
      currentDoctor = null;
      refreshDoctorCard();
      showIdle();
      syncSummaryFromForm();
      return;
    }

    $doctor.data('list', doctors).html(
      doctors.map(d => `<option value="${d.id}">${escapeHtml(d.name)}</option>`).join('')
    );

    refreshDoctorCard();
    loadHours();
  });
}

function refreshDoctorCard() {
  const list = $doctor.data('list') || [];
  const id = $doctor.val();
  currentDoctor = list.find(d => String(d.id) === String(id)) || null;

  const $card = $('#doctorCard');

  if (!currentDoctor) {
    // Sin la lista cargada (por ejemplo tras un error de validación) mostramos
    // al menos el nombre que trae el select.
    const label = $doctor.find('option:selected').text().trim();
    if (id && label) {
      $('#doctorInitials').text(initialsOf(label));
      $('#doctorName').text(label);
      $('#doctorMeta').text('');
      $card.show();
    } else {
      $card.hide();
    }
    syncSummaryFromForm();
    return;
  }

  $('#doctorInitials').text(initialsOf(currentDoctor.name));
  $('#doctorName').text(currentDoctor.name);
  $('#doctorMeta').text(
    currentDoctor.days
      ? `${currentDoctor.specialty} · atiende ${currentDoctor.days}`
      : `${currentDoctor.specialty} · sin horario cargado`
  );
  $card.show();

  syncSummaryFromForm();
}

/* -------------------------------------------------------------- franjas --- */

function loadHours() {
  const date = $date.val();
  const doctorId = $doctor.val();

  if (!doctorId || !date) {
    showIdle();
    clearSlotSummary();
    return;
  }

  showLoading();

  $.getJSON(`/api/schedule/hours?date=${encodeURIComponent(date)}&doctor_id=${encodeURIComponent(doctorId)}`)
    .done(renderHours)
    .fail(() => {
      $hours.html(notice('warn', '!', 'No pudimos traer las franjas. Probá de nuevo en un momento.'));
      clearSlotSummary();
    });
}

function notice(kind, dot, html) {
  return `<div class="notice notice--${kind}">
  <span class="notice__dot">${dot}</span>
  <span>${html}</span>
</div>`;
}

function showIdle() {
  $hours.html(notice('idle', '?', 'Elegí un médico y una fecha y acá aparecen las franjas libres de ese día.'));
}

/** Skeletons con la geometría del contenido final: el bloque no salta. */
function showLoading() {
  $hours.html(`<div class="shift">
  <div class="skel skel--line"></div>
  <div class="slots">
    <span class="skel skel--chip"></span><span class="skel skel--chip"></span>
    <span class="skel skel--chip"></span><span class="skel skel--chip"></span>
  </div>
</div>`);
}

function renderHours(data) {
  clearSlotSummary();

  if (!data || !data.attends) {
    $hours.html(closedNotice(data || {}));
    return;
  }

  slotIndex = 0;
  const html = shiftHtml('Turno mañana', data.morning) + shiftHtml('Turno tarde', data.afternoon);

  $hours.html(html || notice('warn', '!', 'Ese día no tiene franjas cargadas.'));
}

/** El aviso siempre ofrece la salida: nunca es un callejón sin salida. */
function closedNotice(data) {
  if (data.reason === 'no-schedule') {
    const who = data.doctor ? escapeHtml(data.doctor) : 'Este médico';
    return notice('warn', '!', `${who} todavía no cargó su horario de atención, así que no tiene franjas para reservar.`);
  }

  const who = data.doctor ? escapeHtml(data.doctor) : 'El médico';
  const when = data.dayLabel ? `el <strong>${escapeHtml(data.dayLabel)}</strong>` : 'ese día';
  const days = data.days ? ` Los días que atiende son ${escapeHtml(data.days)}.` : '';

  let html = notice('warn', '!', `${who} no atiende ${when}.${days}`);

  if (data.next && data.next.length) {
    const buttons = data.next.map(n =>
      `<button type="button" class="btn btn-secondary btn-sm js-jump" data-date="${n.date}">${escapeHtml(n.label)}</button>`
    ).join('');
    html += `<div class="btn-row" style="margin-top: 12px;">${buttons}</div>`;
  }

  return html;
}

// Saltar a la próxima fecha válida que ofrece el aviso.
$(document).on('click', '.js-jump', function () {
  const value = $(this).data('date');
  if ($date.data('datepicker')) {
    $date.datepicker('setDate', value);
  }
  $date.val(value).trigger('change');
});

function shiftHtml(name, shift) {
  if (!shift || !shift.total) return '';

  const chips = shift.slots.map(slotHtml).join('');

  return `<div class="shift">
  <div class="shift__head">
    <span class="shift__name">${name}</span>
    <span class="shift__meta">${escapeHtml(shift.range || '')} · ${shift.free} libres de ${shift.total}</span>
  </div>
  <div class="slots">${chips}</div>
</div>`;
}

function slotHtml(slot) {
  const id = `slot${slotIndex++}`;
  const start = escapeHtml(slot.start);

  if (slot.taken) {
    return `<label class="slot slot--taken">
  <input type="radio" name="schedule_time" value="${start}" disabled>
  <span class="slot__chip" title="Ya reservada">${start}</span>
</label>`;
  }

  return `<label class="slot">
  <input type="radio" id="${id}" name="schedule_time" value="${start}" required>
  <span class="slot__chip">${start}</span>
</label>`;
}

/* -------------------------------------------------------------- resumen --- */

function onSlotPicked() {
  const time = $('input[name="schedule_time"]:checked').val();
  $('#sumTime').text(time || '—');
  $('#sumDate').text(time ? (longDate($date.val()) || $date.val()) : 'Elegí una franja');
  updateSubmitState();
}

function clearSlotSummary() {
  $('#sumTime').text('—');
  $('#sumDate').text('Elegí una franja');
  updateSubmitState();
}

function onTypePicked() {
  $('#typeSeg .seg__opt').removeClass('is-on');
  $(this).closest('.seg__opt').addClass('is-on');
  $('#sumType').text($(this).closest('.seg__opt').text().trim());
}

function syncSummaryFromForm() {
  const doctorLabel = $doctor.find('option:selected').text().trim();
  const specialtyLabel = $specialty.find('option:selected').text().trim();

  $('#sumDoctor').text($doctor.val() ? doctorLabel : '—');
  $('#sumSpecialty').text($specialty.val() ? specialtyLabel : '—');
  $('#sumType').text($('#typeSeg input:checked').closest('.seg__opt').text().trim() || 'Consulta');

  onSlotPicked();
}

/** Sin médico, fecha y franja no se puede reservar. */
function updateSubmitState() {
  const ready = !!$doctor.val() && !!$date.val() && !!$('input[name="schedule_time"]:checked').val();
  $submit.prop('disabled', !ready);
}
