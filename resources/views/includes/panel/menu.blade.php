<!-- Navigation -->
<h6 class="navbar-heading text-muted">
@if (auth()->user()->role == 'admin')
  Gestionar datos
@else 
  Menú
@endif
</h6>
<ul class="navbar-nav">
  @if (auth()->user()->role == 'admin')
    <li class="nav-item">
      <a class="nav-link" href="/home">
        <i class="ni ni-tv-2 text-primary"></i> Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/specialties">
        <i class="ni ni-planet text-primary"></i> Gestionar especialidades
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/doctors">
        <i class="ni ni-single-02 text-primary"></i> Médicos
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/patients">
        <i class="ni ni-satisfied text-primary"></i> Pacientes
      </a>
    </li>
  @elseif (auth()->user()->role == 'doctor')
  <li class="nav-item">
      <a class="nav-link" href="/schedule">
        <i class="ni ni-calendar-grid-58 text-primary"></i> Gestionar horario
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/specialties">
        <i class="ni ni-time-alarm text-orange"></i> Mis citas
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/patients">
        <i class="ni ni-satisfied text-primary"></i>Mis Pacientes
      </a>
    </li>
  @else {{-- patient --}}
  <li class="nav-item">
      <a class="nav-link" href="/home">
        <i class="ni ni-send text-primary"></i> Reservar cita
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="/specialties">
        <i class="ni ni-time-alarm text-orange"></i> Mis citas
      </a>
    </li>
  @endif
  <li class="nav-item">
    <a class="nav-link" href="" onclick="event.preventDefault(); document.getElementById('formLogout').submit();">
      <i class="ni ni-key-25 "></i> Cerrar sesión
    </a>
    <form action="{{ route('logout') }}" method="POST" style="display: none;" id="formLogout">
      @csrf
    </form>
  </li>
</ul>
@if (auth()->user()->role == 'admin')
<!-- Divider -->
<hr class="my-3">
<!-- Heading -->
<h6 class="navbar-heading text-muted">Reportes</h6>
<!-- Navigation -->
<ul class="navbar-nav mb-md-3">
  <li class="nav-item">
    <a class="nav-link" href="$">
      <i class="ni ni-collection text-red"></i> Frecuencia de citas
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="$">
      <i class="ni ni-spaceship"></i> Médicos mas activos
    </a>
  </li>
</ul>
@endif