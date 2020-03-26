<!-- Navigation -->
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
<hr class="my-3">
