{{-- Los requisitos se muestran antes, no como errores después de guardar. --}}
<aside class="card card--raised card-pad sticky-col">
  <span class="kicker kicker--muted">Antes de guardar</span>

  <ol class="reqs" style="margin-top: 14px;">
    <li><span class="reqs__n">1</span><span>Nombre, DNI y correo son obligatorios.</span></li>
    <li><span class="reqs__n">2</span><span>Al menos una especialidad, o no va a aparecer en el buscador de turnos.</span></li>
    <li><span class="reqs__n">3</span><span>El horario de atención lo carga el médico desde su cuenta.</span></li>
  </ol>

  <hr>

  <button type="submit" class="btn btn-primary btn-block btn-lg-action">{{ $label }}</button>
  <a href="{{ url('doctors') }}" class="btn btn-secondary btn-block">Cancelar</a>
</aside>
