<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom">
    <div class="container-fluid">
        <button class="btn btn-outline-light d-lg-none" id="sidebarToggleBtn">
            <i class="bi bi-list"></i>
        </button>

        <span class="navbar-brand ms-2">@yield('page_title', 'Dashboard')</span>

        <div class="ms-auto d-flex align-items-center gap-3">
            <!-- Theme Toggle -->
            <button class="btn btn-outline-light" id="themeToggle" title="Toggle theme">
                <i class="bi bi-moon"></i>
            </button>

            <!-- Branch Switch -->
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-diagram-3"></i> Switch Branch
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">Main Branch</a></li>
                    <li><a class="dropdown-item" href="#">Branch 2</a></li>
                </ul>
            </div>

            <!-- User Menu -->
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> Profile
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">My Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
