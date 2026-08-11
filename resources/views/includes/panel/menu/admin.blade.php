<ul class="navbar-nav">
  <li class="nav-item">
    <a class="nav-link {{ request()->is('home') ? 'is-active' : '' }}" href="{{ url('/home') }}">Resumen</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ request()->is('appointments*') ? 'is-active' : '' }}" href="{{ url('/appointments') }}">Citas médicas</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ request()->is('doctors*') ? 'is-active' : '' }}" href="{{ url('/doctors') }}">Médicos</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ request()->is('patients*') ? 'is-active' : '' }}" href="{{ url('/patients') }}">Pacientes</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ request()->is('specialties*') ? 'is-active' : '' }}" href="{{ url('/specialties') }}">Especialidades</a>
  </li>
</ul>
