@extends('frontend.layouts.app')

@section('content')
    @forelse ($sections as $section)
        @if ($section?->key == 'banner_carousel' && $section?->value?->visible && !empty($section?->value?->slides ?? []))
            <section class="hero">
                <div class="hero-block">
                    <div class="hero-box">
                        <div id="carouselExampleControls" class="carousel slide carousel-fade" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                @foreach ($section?->value?->slides as $slide)
                                    <button type="button" data-bs-target="#carouselExampleControls" data-bs-slide-to="{{ $loop->iteration }}" @if($loop->first) class="active" @endif aria-current="true" aria-label="Slide {{ $loop->iteration }}"></button>
                                @endforeach
                            </div>
                            <div class="carousel-inner">
                                @foreach ($section?->value?->slides as $slide)
                                    <div class="carousel-item @if($loop->first) active @endif">
                                        <div class="hero-caro firt-hr">
                                            <img src="{{ asset('storage/' . $slide?->image) }}" class="d-block w-100" alt="Banner">
                                            <div class="hero-content">
                                                <h2>{{ $slide?->heading }}</h2>
                                                <p>{{ $slide?->description }}</p>
                                                @if($slide?->has_button)
                                                <a href="{{ $slide?->redirect }}" class="btn hero-btn">{{ $slide?->button_title }}</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls"
                                data-bs-slide="prev">
                                <span class="arrow-bg">
                                    <img src="{{ asset('assets/images/arro-right.svg') }}" alt="">
                                </span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls"
                                data-bs-slide="next">
                                <span class="arrow-bg">
                                    <img src="{{ asset('assets/images/arrow-left.svg') }}" alt="">
                                </span>
                            </button>
                        </div>
                    </div>
                    {{-- <div class="hero-bottom">
                        <div class="container">
                            <div class="hero-bttom-bx">
                                <div class="her-bx-left">
                                    <h3>Top Categories</h3>
                                </div>
                                <div class="her-bx-right">
                                    <a href="">
                                        View All
                                        <img src="{{ asset('assets/images/right-arrow-view.png') }}" alt="">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </section>
        @endif

        @if (($section?->key == 'top_categories_grid' && $section?->value?->visible) || ($section?->key == 'top_categories_linear' && $section?->value?->visible))
        <section class="food">
            <div class="food-block">
                @if($section?->key == 'top_categories_grid' && $section?->value?->visible)
                <div class="food-bevrage">
                    <div class="container">
                        <div class="row">

                            <div class="col-lg-12 col-xl-6 col-xxl-3 col-md-12 col-sm-12">
                                <div class="food-box">
                                    <h3 class="h-30">
                                        <span>Food & Beverage</span>
                                        <a href="">View All</a>
                                    </h3>
                                    <div class="row">
                                        <div class="col-lg-6 col-xl-6 col-md-6 col-sm-6">
                                            <div class="f-inbx">
                                                <div class="hover-slider">
                                                    <div class="products-grid" id="productsGrid">
                                                    </div>
                                                    <p class="slow-mn">OKF Sparkling Peach D...</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-xl-6 col-md-6 col-sm-6 ">
                                            <div class="f-inbx">
                                                <img src="{{ asset('assets/images/food-img2.png') }}" alt="">
                                                <p>Excelsior Cheese Krun...</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-xl-6 col-md-6 col-sm-6 ">
                                            <div class="f-inbx">
                                                <img src="{{ asset('assets/images/food-img3.png') }}" alt="">
                                                <p>Maretti Bruschette Chi...</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-xl-6 col-md-6 col-sm-6 ">
                                            <div class="f-inbx">
                                                <img src="{{ asset('assets/images/food-img4.png') }}" alt="">
                                                <p>Gatorade Cool Blue 828..</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                @endif

                @if($section?->key == 'top_categories_linear' && $section?->value?->visible)
                <div class="food-rum">
                    <div class="container">
                        <h3 class="h-30">Rum</h3>
                        <div class="rum-block">
                            <div class="rum-box">
                                <img src="{{ asset('assets/images/rumf1.png') }}" alt="">
                                <p class="p-12">Barcelo Anejo 700ml</p>
                            </div>
                            <div class="rum-box">
                                <img src="{{ asset('assets/images/rumf2.png') }}" alt="">
                                <p class="p-12">Barcelo Imperial Mizunara Cask</p>
                            </div>
                            <div class="rum-box">
                                <img src="{{ asset('assets/images/rumf3.png') }}" alt="">
                                <p class="p-12">Barcelo Imperial</p>
                            </div>
                            <div class="rum-box">
                                <img src="{{ asset('assets/images/rumf4.png') }}" alt="">
                                <p class="p-12">Barcelo Dark 700ml</p>
                            </div>
                            <div class="rum-box">
                                <img src="{{ asset('assets/images/rumf5.png') }}" alt="">
                                <p class="p-12">Barcelo Gran Anejo 700ml</p>
                            </div>
                            <div class="rum-box view-rm">
                                <a href="">
                                    View All
                                    <img src="{{ asset('assets/images/arrow-blue-vew.png') }}" alt="">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </section>
        @endif

        @if ($section?->key == 'top_selling_products' && $section?->value?->visible)
        <section class="product-section">
            <div class="product-block pd-y50">
                <div class="container">
                    <h2 class="h-30">
                        Top Selling Products
                        <a href="" class="view-white">
                            View All
                            <img src="{{ asset('assets/images/view-all-white.png') }}" alt="">
                        </a>
                    </h2>
                    <div class="row">
                        <div class="col-lg-6 col-xl-6 col-xxl-3 col-md-6 col-sm-6">
                            <div class="product-box">
                                <img src="{{ asset('assets/images/product-1.png') }}" class="w-100" alt="">
                                <h3 class="h-20 mt-3 mb-3 text-truncate">OKF Sparkling Strawberry Drink</h3>
                                <div class="price-bxm">
                                    <span class="text-offer">$24.99</span>
                                    <div class="bulk-div">
                                        <span>$89.99</span>
                                        <a href="" class="bulk-pr btn">Bulk Pricing</a>
                                    </div>
                                    <p class="p-18 mt-2 mb-3">Min Order: 5 boxes</p>
                                </div>
                                <div class="cart-toggle-wrapper">
                                    <a href="" class="btn cart-btn d-block" id="addToCartBtn">Add to Cart</a>
                                    <div class="cart-home" aria-hidden="true">
                                        <div class="cart-all-dtl">
                                            <div class="col-auto">
                                                <div class="input-group quantity-group">
                                                    <button class="btn btn-outline-secondary btn-minus"
                                                        type="button">−</button>
                                                    <input type="text" class="form-control text-center quantity-value"
                                                        value="1" id="quantity" readonly>
                                                    <button class="btn btn-outline-secondary btn-plus"
                                                        type="button">+</button>
                                                </div>
                                            </div>
                                            <div class="cart-pra">
                                                <p class="h-24 price-value">$89.99</p>
                                                <p class="p-18">Total</p>
                                            </div>
                                            <div class="cart-delete" role="button" title="Remove from cart">
                                                <img src="{{ asset('assets/images/cart-delete.png') }}" alt="Delete"
                                                    class="cart-delete-img">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @if ($section?->key == 'recently_viewed' && $section?->value?->visible)
        <section class="recent-ml">
            <div class="recent-block pd-y50">
                <div class="container">
                    <h2 class="h-30">
                        Recently Viewed
                        <a href="" class="view-black">
                            View All
                            <img src="{{ asset('assets/images/view-blue-arrow.svg') }}" alt="">
                        </a>
                    </h2>
                    <div class="row">
                        <div class="col-lg-6 col-xl-6 col-xxl-3 col-md-6 col-sm-6">
                            <div class="recent-box">
                                <img class="w-100" src="images/recent-p1.png" alt="">
                                <div class="rc-bx-in">
                                    <h3 class="h-20 mb-3">True Adult Dog Food Dried Pebbles</h3>
                                    <p class="pr-bold mb-3">$25.99</p>
                                    <a href="" class="btn cart-btn d-block">Add to Cart</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @if ($section?->key == 'newsletter_subscription' && $section?->value?->visible)
        <section class="sub-section">
            <div class="sub-block">
                <div class="container">
                    <div class="sub-box text-center">
                        <h2 class="h-30">Stay Updated</h2>
                        <p class="p-20 py-3">Sign up for updates and exclusive wholesale offers</p>
                        <div class="sub-serch-box">
                            <input type="text" placeholder="Your email address">
                            <button class="btn-sub btn">Subscribe</button>
                        </div>
                        <p class="p-18">By subscribing , you agree to receive marketing communications from Anjo
                            Wholesale
                        </p>
                    </div>
                </div>
            </div>
        </section>
        @endif

    @empty
    @endforelse
@endsection
