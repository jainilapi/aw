@php
    $user = auth()->guard('web')->user();

    $userManagementOpen = in_array(request()->segment(1), ['users', 'roles']);
    $catalogOpen = in_array(request()->segment(1), ['products', 'categories', 'brands', 'home-page-settings']);
    $settingsOpen = in_array(request()->segment(1), ['settings', 'currencies', 'tax-slabs', 'notification-templates']);
@endphp

@php
    $user = auth()->guard('web')->user();

    $userManagementOpen = in_array(request()->segment(1), ['users', 'roles']);
    $catalogOpen = in_array(request()->segment(1), ['products', 'categories', 'brands', 'home-page-settings']);
@endphp

<nav id="sidebar" class="sidebar">
    <a class="sidebar-brand">
        <img src="{{ Helper::logo() }}" style="height:30px;width:30px;margin-right:8px;position:relative;bottom:3px;">
        {{ Helper::title() }}
    </a>

    <div class="sidebar-content">
        @if(auth()->check())

            <div class="sidebar-user">
                <img src="{{ $user->userprofile }}" class="img-fluid rounded-circle mb-2">
                <div class="fw-bold">{{ $user->name }}</div>
                <small>{{ implode(', ', $user->roles()->pluck('name')->toArray()) }}</small>
            </div>

            <ul class="sidebar-nav">

                <li class="sidebar-header">Main</li>

                <li class="sidebar-item {{ request()->segment(1) == 'dashboard' ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">
                        <i class="fas fa-gauge-high me-2"></i> Dashboard
                    </a>
                </li>

                @if($user->isAdmin() || $user->can('users.index') || $user->can('roles.index'))
                    <li class="sidebar-header">Access Control</li>

                    <li class="sidebar-item {{ $userManagementOpen ? 'active' : '' }}">
                        <a data-bs-toggle="collapse" data-bs-target="#userManagement" class="sidebar-link">
                            <i class="fas fa-user-shield me-2"></i> User Management
                        </a>

                        <ul id="userManagement"
                            class="sidebar-dropdown list-unstyled collapse {{ $userManagementOpen ? 'show' : '' }}">

                            @if($user->isAdmin() || $user->can('users.index'))
                                <li class="sidebar-item {{ request()->segment(1) == 'users' ? 'active' : '' }}">
                                    <a class="sidebar-link" href="{{ route('users.index') }}">
                                        <i class="fas fa-users me-2"></i> Users
                                    </a>
                                </li>
                            @endif

                            @if($user->isAdmin() || $user->can('roles.index'))
                                <li class="sidebar-item {{ request()->segment(1) == 'roles' ? 'active' : '' }}">
                                    <a class="sidebar-link" href="{{ route('roles.index') }}">
                                        <i class="fas fa-id-badge me-2"></i> Roles & Permissions
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <li class="sidebar-header">Business</li>

                @if($user->isAdmin() || $user->can('customers.index'))
                    <li class="sidebar-item {{ request()->segment(1) == 'customers' ? 'active' : '' }}">
                        <a href="{{ route('customers.index') }}" class="sidebar-link">
                            <i class="fas fa-user-tie me-2"></i> Customers
                        </a>
                    </li>
                @endif

                @if($user->isAdmin() || $user->can('suppliers.index'))
                    <li class="sidebar-item {{ request()->segment(1) == 'suppliers' ? 'active' : '' }}">
                        <a href="{{ route('suppliers.index') }}" class="sidebar-link">
                            <i class="fas fa-truck-field me-2"></i> Suppliers
                        </a>
                    </li>
                @endif

                <li class="sidebar-header">Sales</li>

                @if($user->isAdmin() || $user->can('orders.index'))
                    <li class="sidebar-item {{ request()->segment(1) == 'orders' ? 'active' : '' }}">
                        <a href="{{ route('orders.index') }}" class="sidebar-link">
                            <i class="fas fa-clipboard-list me-2"></i> Orders
                        </a>
                    </li>
                @endif

                @if($user->isAdmin() || $user->can('promotions.index'))
                    <li class="sidebar-item {{ request()->segment(1) == 'promotions' ? 'active' : '' }}">
                        <a href="{{ route('promotions.index') }}" class="sidebar-link">
                            <i class="fas fa-percent me-2"></i> Promotions
                        </a>
                    </li>
                @endif

                @if($user->isAdmin() || $user->can('promotions.usage.report'))
                    <li class="sidebar-item {{ request()->segment(1) == 'promotions-usage-report' ? 'active' : '' }}">
                        <a href="{{ route('promotions.usage.report') }}" class="sidebar-link">
                            <i class="fas fa-chart-line me-2"></i> Promotion Usage Report
                        </a>
                    </li>
                @endif

                <li class="sidebar-header">Operations</li>

                @if($user->isAdmin() || $user->can('warehouses.index'))
                    <li class="sidebar-item {{ request()->segment(1) == 'warehouses' ? 'active' : '' }}">
                        <a href="{{ route('warehouses.index') }}" class="sidebar-link">
                            <i class="fas fa-warehouse me-2"></i> Warehouses
                        </a>
                    </li>
                @endif

                @if($user->isAdmin() || $user->can('locations.index'))
                    <li class="sidebar-item {{ request()->segment(1) == 'locations' ? 'active' : '' }}">
                        <a href="{{ route('locations.index') }}" class="sidebar-link">
                            <i class="fas fa-location-dot me-2"></i> Store Locations
                        </a>
                    </li>
                @endif

                @if(
                        $user->isAdmin() ||
                        $user->can('products.index') ||
                        $user->can('categories.index') ||
                        $user->can('brands.index')
                    )
                    <li class="sidebar-header">Catalog</li>

                    <li class="sidebar-item {{ $catalogOpen ? 'active' : '' }}">
                        <a data-bs-toggle="collapse" data-bs-target="#catalogMenu" class="sidebar-link">
                            <i class="fas fa-box-open me-2"></i> Products & Catalog
                        </a>

                        <ul id="catalogMenu" class="sidebar-dropdown list-unstyled collapse {{ $catalogOpen ? 'show' : '' }}">

                            @if($user->isAdmin() || $user->can('products.index'))
                                <li class="sidebar-item {{ request()->segment(1) == 'products' ? 'active' : '' }}">
                                    <a class="sidebar-link" href="{{ route('products.index') }}">
                                        <i class="fas fa-cubes me-2"></i> Products
                                    </a>
                                </li>
                            @endif

                            @if($user->isAdmin() || $user->can('categories.index'))
                                <li class="sidebar-item {{ request()->segment(1) == 'categories' ? 'active' : '' }}">
                                    <a class="sidebar-link" href="{{ route('categories.index') }}">
                                        <i class="fas fa-sitemap me-2"></i> Categories
                                    </a>
                                </li>
                            @endif

                            @if($user->isAdmin() || $user->can('brands.index'))
                                <li class="sidebar-item {{ request()->segment(1) == 'brands' ? 'active' : '' }}">
                                    <a class="sidebar-link" href="{{ route('brands.index') }}">
                                        <i class="fas fa-tag me-2"></i> Brands
                                    </a>
                                </li>
                            @endif

                            @if($user->isAdmin() || $user->can('home-page-settings.index'))
                                <li class="sidebar-item {{ request()->segment(1) == 'home-page-settings' ? 'active' : '' }}">
                                    <a class="sidebar-link" href="{{ route('home-page-settings.index') }}">
                                        <i class="fas fa-sliders-h me-2"></i> Home Page Settings
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if($user->isAdmin() ||
                        $user->can('settings.index') ||
                        $user->can('currencies.index') ||
                        $user->can('tax-slabs.index') ||
                        $user->can('notification-templates.index')
                )
                    <li class="sidebar-header">Configuration</li>

                    <li class="sidebar-item {{ $settingsOpen ? 'active' : '' }}">
                        <a data-bs-toggle="collapse" data-bs-target="#settingsMenu" class="sidebar-link">
                            <i class="fas fa-cogs me-2"></i> Settings
                        </a>

                        <ul id="settingsMenu" class="sidebar-dropdown list-unstyled collapse {{ $settingsOpen ? 'show' : '' }}">
                            <li class="sidebar-item {{ request()->segment(1) == 'settings' ? 'active' : '' }}">
                                <a class="sidebar-link" href="{{ route('settings.index') }}">
                                    <i class="fas fa-wrench me-2"></i> General Settings
                                </a>
                            </li>
                            <li class="sidebar-item {{ request()->segment(1) == 'currencies' ? 'active' : '' }}">
                                <a class="sidebar-link" href="{{ route('currencies.index') }}">
                                    <i class="fas fa-coins me-2"></i> Currencies
                                </a>
                            </li>
                            @if($user->isAdmin() || $user->can('tax-slabs.index'))
                            <li class="sidebar-item {{ request()->segment(1) == 'tax-slabs' ? 'active' : '' }}">
                                <a class="sidebar-link" href="{{ route('tax-slabs.index') }}">
                                    <i class="fas fa-file-invoice-dollar me-2"></i> Tax Slabs
                                </a>
                            </li>
                            @endif
                            @if($user->isAdmin() || $user->can('notification-templates.index'))
                                <li class="sidebar-item {{ request()->segment(1) == 'notification-templates' ? 'active' : '' }}">
                                    <a class="sidebar-link" href="{{ route('notification-templates.index') }}">
                                        <i class="fas fa-bell me-2"></i> Notification Templates
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <li class="sidebar-item mt-3">
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="sidebar-link w-100 text-start border-0 bg-transparent">
                            <i class="fas fa-right-from-bracket me-2"></i> Sign Out
                        </button>
                    </form>
                </li>

            </ul>
        @endif
    </div>
</nav>