<ul class="navbar-nav">
  <li class="nav-item">
    <a class="nav-link {{ request()->is('home') ? 'is-active' : '' }}" href="{{ url('/home') }}">Resumen</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ request()->is('my-day') ? 'is-active' : '' }}" href="{{ url('/my-day') }}">Mi día</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ request()->is('appointments*') ? 'is-active' : '' }}" href="{{ url('/appointments') }}">Mis citas</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ request()->is('schedule*') ? 'is-active' : '' }}" href="{{ url('/schedule') }}">Horario de atención</a>
  </li>
</ul>
