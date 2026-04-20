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
                    <a href="{{ route('wizard') }}" class="waves-effect {{ request()->routeIs('wizard') ? 'active' : '' }}">
                        <i class="ri-mail-send-line"></i>
                        <span>Nueva notificación</span>
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
