{{-- Los requisitos se muestran antes, no como errores después de guardar. --}}
<aside class="card card--raised card-pad sticky-col">
  <span class="kicker kicker--muted">Antes de guardar</span>

  <ol class="reqs" style="margin-top: 14px;">
    <li><span class="reqs__n">1</span><span>Nombre y correo son obligatorios.</span></li>
    <li><span class="reqs__n">2</span><span>El DNI se valida con 8 dígitos si lo cargás.</span></li>
    <li><span class="reqs__n">3</span><span>Con la contraseña que le des entra y reserva sus turnos.</span></li>
  </ol>

  <hr>

  <button type="submit" class="btn btn-primary btn-block btn-lg-action">{{ $label }}</button>
  <a href="{{ url('patients') }}" class="btn btn-secondary btn-block">Cancelar</a>
</aside>
