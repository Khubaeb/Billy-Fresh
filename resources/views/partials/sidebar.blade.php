<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3 sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-house me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                    <i class="bi bi-people me-2"></i>
                    Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                    <i class="bi bi-receipt me-2"></i>
                    Invoices
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">
                    <i class="bi bi-box me-2"></i>
                    Services
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                    <i class="bi bi-cash-stack me-2"></i>
                    Expenses
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('recurring.*') ? 'active' : '' }}" href="{{ route('recurring.index') }}">
                    <i class="bi bi-arrow-repeat me-2"></i>
                    Recurring
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="bi bi-bar-chart me-2"></i>
                    Reports
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
            <span>Administration</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <i class="bi bi-person-badge me-2"></i>
                    Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                    <i class="bi bi-gear me-2"></i>
                    Settings
                </a>
            </li>
        </ul>
        
        <div class="border-top my-3"></div>
        
        <div class="px-3 mt-4">
            <div class="d-grid gap-2">
                <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-2"></i> New Invoice
                </a>
            </div>
        </div>
    </div>
</nav>
