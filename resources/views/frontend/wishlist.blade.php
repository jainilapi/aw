@extends('frontend.layouts.app')

@push('css')
    <style>
        .wishlist-page {
            padding: 40px 0;
        }

        .page-header {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #203A72;
            margin: 0;
        }

        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        .wishlist-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .wishlist-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(32, 58, 114, 0.12);
        }

        .wishlist-image {
            position: relative;
            height: 200px;
            background: #F5FAFF;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .wishlist-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            padding: 20px;
        }

        .product-type-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-simple {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .badge-variable {
            background: #E3F2FD;
            color: #1976D2;
        }

        .badge-bundle {
            background: #FFF3E0;
            color: #E65100;
        }

        .remove-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #fff;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #D30606;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: #D30606;
            color: #fff;
        }

        .wishlist-content {
            padding: 20px;
        }

        .wishlist-content h3 {
            font-size: 16px;
            font-weight: 600;
            color: #203A72;
            margin: 0 0 8px 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .wishlist-content h3 a {
            color: inherit;
            text-decoration: none;
        }

        .wishlist-content h3 a:hover {
            color: #1976D2;
        }

        .variant-name {
            font-size: 13px;
            color: #666;
            margin-bottom: 12px;
        }

        .wishlist-price {
            font-size: 20px;
            font-weight: 700;
            color: #D30606;
        }

        .empty-state {
            text-align: center;
            padding: 80px 40px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .empty-state-icon {
            width: 100px;
            height: 100px;
            background: #F5FAFF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .empty-state-icon i {
            font-size: 48px;
            color: #9CADC0;
        }

        .empty-state h3 {
            font-size: 24px;
            color: #203A72;
            margin-bottom: 12px;
        }

        .empty-state p {
            font-size: 16px;
            color: #666;
            margin-bottom: 24px;
        }

        .btn-shop {
            display: inline-block;
            padding: 12px 32px;
            background: #203A72;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-shop:hover {
            background: #1a2d5a;
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <section>
        <div class="bred-pro">
            <div class="container">
                <div class="breadcrumb-container">
                    <ol class="breadcrumb">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li class="active">Wishlist</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="wishlist-page">
        <div class="container">

            @if($wishlistItems->count() > 0)
                <div class="wishlist-grid">
                    @foreach($wishlistItems as $item)
                        <div class="wishlist-card" id="wishlist-item-{{ $item['id'] }}">
                            <div class="wishlist-image">
                                <img src="{{ $item['image_url'] }}" alt="{{ $item['product_name'] }}"
                                    onerror="this.src='{{ asset('no-image-found.jpg') }}'">
                                <span class="product-type-badge badge-{{ $item['product_type'] }}">
                                    {{ ucfirst($item['product_type']) }}
                                </span>
                                <button type="button" class="remove-btn" onclick="removeFromWishlist({{ $item['id'] }})"
                                    title="Remove from wishlist">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div class="wishlist-content">
                                <h3>
                                    <a
                                        href="{{ route('product.detail', ['id' => $item['product_id'], 'slug' => $item['product_slug']]) }}">
                                        {{ $item['product_name'] }}
                                    </a>
                                </h3>
                                @if($item['variant_name'])
                                    <p class="variant-name">Variant: {{ $item['variant_name'] }}</p>
                                @endif
                                <p class="wishlist-price">{{ currency_format($item['price']) }}</p>
                                @if(!empty($item['tax_slab']))
                                    <p class="variant-name" style="margin-top: 4px;">
                                        Tax: {{ rtrim(rtrim(number_format($item['tax_slab']['percentage'], 2, '.', ''), '0'), '.') }}%
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-heart"></i>
                    </div>
                    <h3>Your Wishlist is Empty</h3>
                    <p>Start adding products you love to your wishlist!</p>
                    <a href="{{ route('products') }}" class="btn-shop">Browse Products</a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('js')
    <script>
        function removeFromWishlist(itemId) {
            if (!confirm('Remove this item from your wishlist?')) {
                return;
            }

            fetch('{{ route("api.wishlist.remove") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ wishlist_id: itemId }),
                credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const card = document.getElementById('wishlist-item-' + itemId);
                        if (card) {
                            card.style.transition = 'opacity 0.3s, transform 0.3s';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.9)';
                            setTimeout(() => {
                                card.remove();
                                // Check if grid is empty
                                const grid = document.querySelector('.wishlist-grid');
                                if (grid && grid.children.length === 0) {
                                    location.reload();
                                }
                            }, 300);
                        }
                    } else {
                        alert(data.message || 'Failed to remove item');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        }
    </script>
@endpush