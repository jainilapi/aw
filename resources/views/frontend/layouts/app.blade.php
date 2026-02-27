<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/x-icon" href="{{ Helper::favicon() }}">

    @if(isset($metaInfo))
        <meta name="title" content="{{ $metaInfo['title'] ?? '' }}">
        <meta name="description" content="{{ $metaInfo['content'] ?? '' }}">
        <meta name="keywords" content="{{ $metaInfo['keywords'] ?? '' }}">
        <link rel="canonical" href="{{ $metaInfo['url'] ?? '' }}">
        <meta name="robots" content="index, follow">
    @endif

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/front.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/swal.min.css') }}">

    <style>
        .wish-life {
            position: absolute;
            left: 0;
            right: 0;
            top: 100%;
            margin-top: 4px;
            width: 100%;
            max-height: 320px;
            overflow-y: auto;
            opacity: 1;
            z-index: 99999 !important;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
        }

        .wish-life .search-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            cursor: pointer;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .wish-life .search-item:hover,
        .wish-life .search-item.active {
            background-color: #f3f4f6;
        }

        .wish-life .search-icon {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9fafb;
            font-size: 14px;
            color: #6b7280;
        }

        .wish-life .search-text {
            flex: 1;
            min-width: 0;
        }

        .wish-life .search-title {
            font-size: 14px;
            font-weight: 500;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wish-life .search-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .wish-life .search-empty {
            padding: 10px 14px;
            font-size: 13px;
            color: #6b7280;
        }

        .wish-life .search-section-title {
            padding: 8px 14px 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9ca3af;
        }

        /* Currency Selector Styles */
        .currency-selector .nav-link {
            font-weight: 500;
            color: #333;
            padding: 8px 12px;
            border-radius: 6px;
            background: #f3f4f6;
            font-size: 14px;
        }

        .currency-selector .nav-link:hover {
            background: #e5e7eb;
        }

        .currency-selector .currency-symbol {
            font-weight: 600;
        }

        .currency-selector .dropdown-menu {
            min-width: 220px;
            padding: 8px 0;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .currency-selector .dropdown-item {
            padding: 10px 16px;
            font-size: 14px;
        }

        .currency-selector .dropdown-item.active,
        .currency-selector .dropdown-item:active {
            background-color: #203a7221;
            color: #2e447d;
        }

        .currency-selector .dropdown-item:hover {
            background-color: #f5f5f5;
        }
    </style>

    <script>
        window.CURRENCY_CONFIG = @json($currencyConfig);
        
        window.formatCurrency = function (amount) {
            var config = window.CURRENCY_CONFIG;
            var formatted = parseFloat(amount * config.exchange_rate).toLocaleString('en-US', {
                minimumFractionDigits: config.decimal_places,
                maximumFractionDigits: config.decimal_places
            });
            return config.symbol_position === 'before'
                ? config.selected_currency_symbol + formatted
                : formatted + config.selected_currency_symbol;
        };
    </script>

    @stack('css')
</head>

<body>
    @include('frontend.layouts.header')

    @include('frontend.layouts.side-cart')

    <div class="main-wrappper">
        @yield('content')
    </div>

    @include('frontend.layouts.footer')
    @include('frontend.layouts.script')

    @stack('js')
</body>

</html>