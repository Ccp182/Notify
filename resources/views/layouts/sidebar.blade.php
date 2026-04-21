<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title">Menú</li>

                <li>
                    <a href="{{ route('dashboard') }}" class="waves-effect {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="ri-dashboard-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('campains.index') }}" class="waves-effect {{ request()->routeIs('campains.*') || request()->routeIs('wizard') ? 'active' : '' }}">
                        <i class="mdi mdi-bullhorn-outline"></i>
                        <span>Campañas</span>
                    </a>
                </li>

                <li class="menu-title">Cuenta</li>
                <li>
                    <a href="{{ route('select-app.show') }}" class="waves-effect">
                        <i class="ri-apps-2-line"></i>
                        <span>Cambiar app</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
