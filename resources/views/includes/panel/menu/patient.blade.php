<ul class="navbar-nav">
  <li class="nav-item">
    <a class="nav-link {{ request()->is('home') ? 'is-active' : '' }}" href="{{ url('/home') }}">Resumen</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ request()->is('appointments*') && ! request()->is('appointments/create') ? 'is-active' : '' }}" href="{{ url('/appointments') }}">Mis citas</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ request()->is('appointments/create') ? 'is-active' : '' }}" href="{{ url('/appointments/create') }}">Reservar una cita</a>
  </li>
</ul>
