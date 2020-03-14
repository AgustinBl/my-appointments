<!-- Navigation -->
<h6 class="navbar-heading text-muted">
@if (auth()->user()->role == 'admin')
  Gestionar datos
@else 
  Menú
@endif
</h6>
<ul class="navbar-nav">
  @include('includes.panel.menu.'.auth()->user()->role)
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