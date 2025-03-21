<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/logo.svg') }}" alt="Billy Logo" height="30" class="d-inline-block align-text-top me-2">
            {{ config('app.name', 'Billy') }}
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Items -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>

                <!-- Only show these items when authenticated --> 
                @auth
                    <!-- Customers -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                            <i class="bi bi-people me-1"></i> Customers
                        </a>
                    </li>

                    <!-- Invoices -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                            <i class="bi bi-receipt me-1"></i> Invoices
                        </a>
                    </li>

                    <!-- Services -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">
                            <i class="bi bi-box me-1"></i> Services
                        </a>
                    </li>

                    <!-- Expenses -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                            <i class="bi bi-cash-coin me-1"></i> Expenses
                        </a>
                    </li>

                    <!-- Recurring Billing -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('recurring.*') ? 'active' : '' }}" href="{{ route('recurring.index') }}">
                            <i class="bi bi-arrow-repeat me-1"></i> Recurring
                        </a>
                    </li>

                    <!-- Reports -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                            <i class="bi bi-bar-chart me-1"></i> Reports
                        </a>
                    </li>
                    
                    <!-- Accounting Portal - Visible to Admin and Accountant roles -->
                    @if(Auth::user()->roles->contains(function($role) { 
                        return $role->name === 'Administrator' || $role->name === 'Accountant'; 
                    }))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('accounting.*') ? 'active' : '' }}" href="{{ route('accounting.index') }}">
                            <i class="bi bi-calculator me-1"></i> Accounting Portal
                        </a>
                    </li>
                    @endif
                @endauth
            </ul>

            <!-- Right Side -->
            <ul class="navbar-nav">
                <!-- Authentication -->
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <!-- Profile -->
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person me-2"></i> {{ __('Profile') }}
                                </a>
                            </li>

                            <!-- Settings -->
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-gear me-2"></i> {{ __('Settings') }}
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <!-- Logout -->
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i> {{ __('Log Out') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i> {{ __('Log in') }}
                        </a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="bi bi-person-plus me-1"></i> {{ __('Register') }}
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </div>
    </div>
</nav>
