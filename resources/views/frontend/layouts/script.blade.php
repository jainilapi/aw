<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('assets/js/swal.min.js') }}"></script>
<script>
    $(function () {

        const products = [{
            price: "$299",
            images: [
                "images/food-img2.png",
                "images/food-img3.png",
                "images/food-img4.png",
                "images/food-img4.png"
            ]
        }];

        const $productsGrid = $('#productsGrid');
        const slideshowIntervals = {};

        $.each(products, function (index, product) {

            const $productCard = $('<div>', {
                class: 'product-card',
                'data-product-index': index
            });

            const $imageContainer = $('<div>', { class: 'product-image-container' });

            $.each(product.images, function (imgIndex, imgSrc) {
                $('<img>', {
                    src: imgSrc,
                    class: 'product-image ' + (imgIndex === 0 ? 'active' : ''),
                    alt: product.name || ''
                }).appendTo($imageContainer);
            });

            const $indicatorContainer = $('<div>', { class: 'image-indicator' });

            $.each(product.images, function (imgIndex) {
                $('<div>', {
                    class: 'indicator-dot ' + (imgIndex === 0 ? 'active' : '')
                }).appendTo($indicatorContainer);
            });

            $imageContainer.append($indicatorContainer);

            const $productInfo = $(`
            <div class="product-info">
                <div class="wish-list">
                    <button class="btn">
                        <img src="images/menuicon-3.svg" alt="">
                        Wishlist
                    </button>
                </div>
            </div>
        `);

            $productCard.append($imageContainer, $productInfo);
            $productsGrid.append($productCard);

            let currentImageIndex = 0;
            let hoverTimeout = null;

            $productCard.on('mouseenter', function () {
                const $images = $imageContainer.find('.product-image');
                const $dots = $indicatorContainer.find('.indicator-dot');

                function changeImage() {
                    $images.eq(currentImageIndex).removeClass('active');
                    $dots.eq(currentImageIndex).removeClass('active');

                    currentImageIndex = (currentImageIndex + 1) % $images.length;

                    $images.eq(currentImageIndex).addClass('active');
                    $dots.eq(currentImageIndex).addClass('active');
                }

                hoverTimeout = setTimeout(function () {
                    changeImage();
                    slideshowIntervals[index] = setInterval(changeImage, 1000);
                }, 400);
            });

            $productCard.on('mouseleave', function () {
                clearTimeout(hoverTimeout);
                clearInterval(slideshowIntervals[index]);

                const $images = $imageContainer.find('.product-image');
                const $dots = $indicatorContainer.find('.indicator-dot');

                $images.eq(currentImageIndex).removeClass('active');
                $dots.eq(currentImageIndex).removeClass('active');

                currentImageIndex = 0;

                $images.eq(0).addClass('active');
                $dots.eq(0).addClass('active');
            });
        });

        $('.user-admn').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('.menu-account').toggleClass('show');
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('.menu-account, .user-admn').length) {
                $('.menu-account').removeClass('show');
            }
        });

        $(document).on('mouseenter', '.product-card', function () {
            const $wishlist = $(this).find('.wish-list');
            $wishlist.addClass('show');
            setTimeout(() => $wishlist.addClass('animate'), 10);
        });

        $(document).on('mouseleave', '.product-card', function () {
            const $wishlist = $(this).find('.wish-list');
            $wishlist.removeClass('animate')
                .one('transitionend', function () {
                    $wishlist.removeClass('show');
                });
        });

        $('.notification-clk').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('.notification').toggleClass('show');
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('.notification, .notification-clk').length) {
                $('.notification').removeClass('show');
            }
        });

        const $cartPanel = $('#cartPanel');
        const $closeCart = $('#closeCart');

        if (!$cartPanel.hasClass('hidden') && !$cartPanel.hasClass('active')) {
            $cartPanel.addClass('hidden');
        }

        function showPanel() {
            $cartPanel.removeClass('hidden')[0].offsetWidth;
            $cartPanel.addClass('active');
        }

        function hidePanel() {
            if ($cartPanel.hasClass('hidden') || !$cartPanel.hasClass('active')) return;

            $cartPanel.removeClass('active')
                .one('transitionend', function (e) {
                    if (e.target === this) {
                        $cartPanel.addClass('hidden');
                    }
                });
        }

        $(document).on('click', '.cart-btn', function (e) {
            e.preventDefault();
            showPanel();
        });

        $closeCart.on('click', function (e) {
            e.preventDefault();
            hidePanel();
        });

        $(document).on('click', function (e) {
            if ($cartPanel.hasClass('hidden')) return;
            if (!$(e.target).closest('#cartPanel, .cart-btn').length) {
                hidePanel();
            }
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && !$cartPanel.hasClass('hidden')) {
                hidePanel();
            }
        });

        $('.cart-toggle-wrapper').each(function () {
            const $wrapper = $(this);
            const $addBtn = $wrapper.find('.cart-btn');
            const $cartHome = $wrapper.find('.cart-home');
            const $deleteBtn = $wrapper.find('.cart-delete');
            const $btnPlus = $wrapper.find('.btn-plus');
            const $btnMinus = $wrapper.find('.btn-minus');
            const $qtyInput = $wrapper.find('.quantity-value');

            if (!$addBtn.length || !$cartHome.length) return;

            $addBtn.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $wrapper.addClass('active');
                $cartHome.attr('aria-hidden', 'false');
                $btnPlus.trigger('focus');
            });

            $deleteBtn.on('click', function (e) {
                e.stopPropagation();
                $wrapper.removeClass('active');
                $cartHome.attr('aria-hidden', 'true');
            });

            $(document).on('click', function (e) {
                if (!$wrapper.hasClass('active')) return;
                if (!$(e.target).closest($wrapper).length) {
                    $wrapper.removeClass('active');
                    $cartHome.attr('aria-hidden', 'true');
                }
            });

            $(document).on('keydown', function (e) {
                if (e.key === 'Escape' && $wrapper.hasClass('active')) {
                    $wrapper.removeClass('active');
                    $cartHome.attr('aria-hidden', 'true');
                }
            });

            $btnPlus.on('click', function (e) {
                e.stopPropagation();
                let v = parseInt($qtyInput.val() || '1', 10);
                $qtyInput.val(v + 1);
            });

            $btnMinus.on('click', function (e) {
                e.stopPropagation();
                let v = parseInt($qtyInput.val() || '1', 10);
                if (v > 1) $qtyInput.val(v - 1);
            });
        });

        (function () {
            const $input = $('#global-search-input');
            const $dropdown = $('#global-search-results');
            const $list = $('#global-search-list');
            let debounceTimer = null;
            let currentIndex = -1;
            var SEARCH_URL = "{{ route('search') }}";

            function hideDropdown() {
                $dropdown.addClass('d-none');
                currentIndex = -1;
                $list.empty();
            }

            function showDropdown() {
                if ($list.children().length === 0) {
                    hideDropdown();
                    return;
                }
                $dropdown.removeClass('d-none');
            }

            function renderResults(data) {
                data = data || {};
                $list.empty();

                const hasProducts = (data.products || []).length > 0;
                const hasCategories = (data.categories || []).length > 0;

                if (!hasProducts && !hasCategories) {
                    $list.append(
                        '<li class="search-empty">No matching products or categories</li>'
                    );
                    showDropdown();
                    return;
                }

                if (hasProducts) {
                    $list.append('<li class="search-section-title">Products</li>');
                    data.products.forEach(function (item) {
                        const meta = item.sku ? 'SKU: ' + item.sku : 'Product';
                        const li =
                            '<li class="search-item" data-url="' + item.url + '">' +
                            '  <div class="search-icon"><i class="fa fa-cube"></i></div>' +
                            '  <div class="search-text">' +
                            '    <div class="search-title">' + $('<div>').text(item.name).html() + '</div>' +
                            '    <div class="search-meta">' + meta + '</div>' +
                            '  </div>' +
                            '</li>';
                        $list.append(li);
                    });
                }

                if (hasCategories) {
                    $list.append('<li class="search-section-title">Categories</li>');
                    data.categories.forEach(function (item) {
                        const li =
                            '<li class="search-item" data-url="' + item.url + '">' +
                            '  <div class="search-icon"><i class="fa fa-folder-open"></i></div>' +
                            '  <div class="search-text">' +
                            '    <div class="search-title">' + $('<div>').text(item.name).html() + '</div>' +
                            '    <div class="search-meta">Category</div>' +
                            '  </div>' +
                            '</li>';
                        $list.append(li);
                    });
                }

                showDropdown();
            }

            function performSearch(term) {
                if (!term || term.length < 2) {
                    hideDropdown();
                    return;
                }

                var payload = {};
                payload.q = term;
                payload.ajax = 1;

                $.get(SEARCH_URL, payload, function (response) {
                    renderResults(response);
                }, 'json').fail(function () {
                    hideDropdown();
                });
            }

            if ($input.length) {
                $input.on('input', function () {
                    const term = $(this).val();
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function () {
                        performSearch(term);
                    }, 250);
                });

                $input.on('keydown', function (e) {
                    const items = $list.find('.search-item');
                    if (items.length === 0) {
                        return;
                    }

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        currentIndex = (currentIndex + 1) % items.length;
                        items.removeClass('active');
                        $(items[currentIndex]).addClass('active');
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        currentIndex = currentIndex <= 0 ? items.length - 1 : currentIndex - 1;
                        items.removeClass('active');
                        $(items[currentIndex]).addClass('active');
                    } else if (e.key === 'Enter') {
                        if (currentIndex >= 0 && currentIndex < items.length) {
                            e.preventDefault();
                            const url = $(items[currentIndex]).data('url');
                            if (url) {
                                window.location.href = url;
                            }
                        }
                    } else if (e.key === 'Escape') {
                        hideDropdown();
                    }
                });

                $list.on('click', '.search-item', function (e) {
                    e.preventDefault();
                    const url = $(this).data('url');
                    if (url) {
                        window.location.href = url;
                    }
                });

                $(document).on('click', function (e) {
                    if (!$(e.target).closest('.header__srh-box').length) {
                        hideDropdown();
                    }
                });
            }
        })();

        // Currency selector handler
        $(document).on('click', '.currency-option', function (e) {
            e.preventDefault();
            var currencyId = $(this).data('currency-id');

            $.ajax({
                url: '/api/set-currency',
                type: 'POST',
                data: { currency_id: currencyId },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        // Update global config
                        window.CURRENCY_CONFIG = response.currency;
                        // Reload page to reflect changes
                        window.location.reload();
                    }
                },
                error: function (xhr) {
                    console.error('Currency change failed:', xhr);
                }
            });
        });

    });
</script>