<header class="navbar navbar-light sticky-top bg-white flex-md-nowrap p-0 shadow-sm">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 text-white" href="{{ route('dashboard') }}">
        <span class="fs-4">{{ config('app.name', 'Billy') }}</span>
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="w-100"></div>
    <ul class="navbar-nav px-3 d-flex flex-row">
        <li class="nav-item text-nowrap me-3">
            <a class="nav-link" href="#" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notifications">
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    3
                    <span class="visually-hidden">unread notifications</span>
                </span>
            </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=007bff&color=fff" alt="User" class="rounded-circle me-1" width="24" height="24">
                <span class="d-none d-md-inline-block">{{ auth()->user()->name ?? 'User' }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-gear me-2"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</header>
