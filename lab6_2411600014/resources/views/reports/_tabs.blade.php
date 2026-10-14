<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('reports.movement') ? 'active' : '' }}" href="{{ route('reports.movement') }}">Stock Movement</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('reports.low-load') ? 'active' : '' }}" href="{{ route('reports.low-load') }}">Low Unit Load</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('reports.valuation') ? 'active' : '' }}" href="{{ route('reports.valuation') }}">Valuation</a></li>
</ul>
