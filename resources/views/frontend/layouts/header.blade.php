<header>
    <nav class="navbar navbar-expand-lg px-0 py-2">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset('assets/images/aw-log.svg') }}" class="h-8" alt="...">
            </a>
            <!-- Navbar toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Collapse -->
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <!-- Nav -->
                <div class="navbar-nav mx-lg-auto head-middle">
                    <form action="javascript:void(0);" class="w-100" autocomplete="off">
                        <div class="header__srh-box position-relative">
                            <div class="srh-left">
                                <i class="fa fa-bars" aria-hidden="true"></i>
                                <input type="text" id="global-search-input" placeholder="Search products or categories">
                            </div>
                            <div class="srh-rgt">
                                <i class="fa fa-search" aria-hidden="true"></i>
                            </div>

                            {{-- Live search dropdown --}}
                            <div id="global-search-results" class="wish-life d-none">
                                <ul class="list-unstyled mb-0" id="global-search-list"></ul>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- Right navigation -->
                <div class="navbar-nav ms-lg-4">
                    <div class="navbar-nav mx-lg-auto">
                        <!-- Currency Selector -->
                        @if(isset($activeCurrencies) && $activeCurrencies->count() > 1)
                            <div class="nav-item dropdown currency-selector me-2">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false" id="currencyDropdown">
                                    <span class="currency-symbol me-1">{{ $selectedCurrency->symbol ?? '$' }}</span>
                                    <span class="currency-code">{{ $selectedCurrency->iso_code ?? 'USD' }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="currencyDropdown">
                                    @foreach($activeCurrencies as $curr)
                                        <li>
                                            <a class="dropdown-item currency-option {{ ($selectedCurrency && $selectedCurrency->id == $curr->id) ? 'active' : '' }}"
                                                href="#" data-currency-id="{{ $curr->id }}">
                                                <span class="fw-bold">{{ $curr->symbol }}</span>
                                                {{ $curr->iso_code }} - {{ $curr->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <a class="nav-item nav-link cart" href="{{ route('cart') }}">
                            <img src="{{ asset('assets/images/cart.svg') }}" alt="">
                            <span id="cart-item-count">0</span>
                        </a>
                        <a class="nav-item nav-link cart notification-clk" href="#">
                            <img src="{{ asset('assets/images/notification.svg') }}" alt="">
                            <span>11</span>
                        </a>
                        <a class="nav-item nav-link user-admn" href="">
                            <span>
                                <img src="{{ asset('assets/images/user-admin.svg') }}" alt="">
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div class="menu-account">
        @if(auth()->guard('customer')->check())
            <div class="container">
                <h3 class="h-24 mb-3">{{ auth()?->guard('customer')?->name }}</h3>
                <div class="act-switch">
                    <div class="mb-3">
                        <a class="text-white" href="{{ route('switch-account') }}">Switch Accounts</a>
                    </div>
                    <div class="mb-3">
                        <form action="{{ route('logout') }}" method="POST"> @csrf
                            <button type="submit" class="text-white" style="border: none;background:transparent;">Sign
                                Out</button>
                        </form>
                    </div>
                </div>
                <ul>
                    <li>
                        <a href="{{ route('customer.dashboard') }}" class="btn">
                            <img src="{{ asset('front-theme/images/menuicon-1.svg') }}" alt="">
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('customer.orders') }}" class="btn">
                            <img src="{{ asset('front-theme/images/cart.svg') }}" alt="">
                            <span>Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('customer.wishlist') }}" class="btn">
                            <img src="{{ asset('front-theme/images/menuicon-3.svg') }}" alt="">
                            <span>Wishlist</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('customer.addresses') }}" class="btn">
                            <img src="{{ asset('front-theme/images/menuicon-4.svg') }}" alt="">
                            <span>Addresses</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('customer.profile') }}" class="btn">
                            <img src="{{ asset('front-theme/images/menuicon-5.svg') }}" alt="">
                            <span>Profile Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="" class="btn">
                            <img src="{{ asset('front-theme/images/menuicon-7.svg') }}" alt="">
                            <span>Payment Methods</span>
                        </a>
                    </li>
                    <li>
                        <a href="" class="btn">
                            <img src="{{ asset('front-theme/images/menuicon-8.svg') }}" alt="">
                            <span>Notifications</span>
                        </a>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="btn"
                                style="width: 100%; text-align: left; border: none; background: transparent;">
                                <img src="{{ asset('front-theme/images/menuicon-9.svg') }}" alt="">
                                <span>Logout</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @else
            <!-- Without Login -->
            <div class="container">
                <a href="{{ route('login') }}" class="h-24 text-white"> Login </a>
            </div>
            <!-- Without Login -->
        @endif
    </div>
    <div class="notification">
        <div class="notification-block">
            <h3 class="h-24">Notifications</h3>
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all"
                        type="button" role="tab">
                        All
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="unread-tab" data-bs-toggle="tab" data-bs-target="#unread" type="button"
                        role="tab">
                        Unread
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <!-- All Tab -->
                <div class="tab-pane fade show active" id="all" role="tabpanel">
                    <ul class="notification-list">
                        <li class="notification-item">
                            <img src="{{ asset('assets/images/nitficatio9m.svg') }}" alt="">
                            <div>
                                <div class="notification-text">Your order has been processed.</div>
                                <div class="notification-time">5m</div>
                            </div>
                            <span class="notification-dot"></span>
                        </li>
                        <li class="notification-item">
                            <img src="{{ asset('assets/images/nitficatio9m.svg') }}" alt="">
                            <div>
                                <div class="notification-text">Your order shipped.</div>
                                <div class="notification-time">6d</div>
                            </div>
                        </li>
                        <li class="notification-item">
                            <img src="{{ asset('assets/images/nitficatio9m.svg') }}" alt="">
                            <div>
                                <div class="notification-text">Check out our Summer Sale</div>
                                <div class="notification-time">12d</div>
                            </div>
                        </li>
                        <li class="notification-item">
                            <img src="{{ asset('assets/images/nitficatio9m.svg') }}" alt="">
                            <div>
                                <div class="notification-text">20% off all OKF products!</div>
                                <div class="notification-time">5m</div>
                            </div>
                        </li>
                    </ul>
                </div>
                <!-- Unread Tab -->
                <div class="tab-pane fade" id="unread" role="tabpanel">
                    <ul class="notification-list">
                        <li class="notification-item">
                            <img src="{{ asset('assets/images/nitficatio9m.svg') }}" alt="">
                            <div>
                                <div class="notification-text">Your order has been processed.</div>
                                <div class="notification-time">5m</div>
                            </div>
                            <span class="notification-dot"></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>