<div class="cart-panel" id="cartPanel">
    <div class="cart-content">
        <div class="cart-header"
            style="padding: 20px; border-bottom: 1px solid #EEEEEE; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 20px; font-weight: 600; color: #203A72;">Shopping Cart</h3>
            <button type="button" class="btn-close" id="closeCartPanel"
                style="background: none; border: none; font-size: 24px; color: #666; cursor: pointer;">&times;</button>
        </div>
        <div class="cart-body" id="cartBody" style="max-height: calc(100vh - 200px); overflow-y: auto; padding: 20px;">
            <div id="cartItemsContainer">
                <div class="text-center py-5">
                    <p style="color: #666;">Loading cart...</p>
                </div>
            </div>
        </div>
        <div class="cart-footer" style="padding: 20px; border-top: 1px solid #EEEEEE; background: #F5FAFF;">
            <div class="cart-item" style="margin-bottom: 15px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #203A72;">Subtotal</h3>
                    <p class="inr-red" id="cartSubtotal"
                        style="margin: 0; font-size: 20px; font-weight: 600; color: #D30606;">$0.00</p>
                </div>
                <a href="{{ route('cart') }}" class="btn cart-btn-css d-block"
                    style="width: 100%; padding: 12px; text-align: center; border-radius: 8px;">Go to Cart</a>
            </div>
        </div>
    </div>
</div>

<script>
    function loadCartData() {
        fetch('{{ route("api.cart.data") }}', {
            credentials: 'include'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartPanel(data.items, data.subtotal);
                    if (typeof updateCartCount === 'function') {
                        updateCartCount(data.item_count);
                    }
                } else {
                    updateCartPanel([], 0);
                }
            })
            .catch(error => {
                console.error('Error loading cart:', error);
                updateCartPanel([], 0);
            });
    }

    function updateCartPanel(items, subtotal) {
        const container = document.getElementById('cartItemsContainer');

        if (items.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-cart-x" style="font-size: 48px; color: #9CADC0; margin-bottom: 15px;"></i>
                    <p style="color: #666; font-size: 16px;">Your cart is empty</p>
                </div>
            `;
            document.getElementById('cartSubtotal').textContent = '$0.00';
            return;
        }

        let html = '';
        const noImageUrl = '{{ asset("no-image-found.jpg") }}';

        items.forEach(item => {
            // Build bundle items HTML if this is a bundle
            let bundleItemsHtml = '';
            if (item.is_bundle && item.bundle_items && item.bundle_items.length > 0) {
                bundleItemsHtml = `
                    <div class="bundle-items-list" style="margin-top: 10px; padding: 10px; background: #F5FAFF; border-radius: 6px;">
                        <p style="font-size: 11px; font-weight: 600; color: #666; margin: 0 0 8px 0; text-transform: uppercase;">Bundle Contains:</p>
                        ${item.bundle_items.map(bundleItem => `
                            <div class="bundle-item d-flex align-items-center gap-2 mb-2" style="font-size: 12px;">
                                <img src="${bundleItem.image_url || noImageUrl}" alt="${bundleItem.product_name}" onerror="this.src='${noImageUrl}'" style="width: 30px; height: 30px; object-fit: contain; border-radius: 4px; background: #fff; padding: 2px;">
                                <span style="flex: 1; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${bundleItem.product_name}</span>
                                <span style="color: #666; white-space: nowrap;">x${bundleItem.quantity}${bundleItem.unit_name ? ' ' + bundleItem.unit_name : ''}</span>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            html += `
                <div class="border-cart mb-3" style="border: 1px solid #EEEEEE; border-radius: 8px; padding: 15px; background: #fff;">
                    <div class="cart-product d-flex align-items-center gap-3 mb-3">
                        <img src="${item.image_url || noImageUrl}" alt="${item.product_name}" onerror="this.src='${noImageUrl}'" style="width: 60px; height: 60px; object-fit: contain; border-radius: 6px; background: #F5FAFF; padding: 5px;">
                        <div style="flex: 1; min-width: 0;">
                            <h5 style="font-size: 14px; font-weight: 600; color: #203A72; margin: 0 0 5px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${item.product_name}</h5>
                            ${item.is_bundle ? `<span style="font-size: 10px; background: #E3F2FD; color: #1976D2; padding: 2px 6px; border-radius: 4px; font-weight: 500;">BUNDLE</span>` : ''}
                            ${item.variant_name ? `<p style="font-size: 12px; color: #666; margin: 0 0 5px 0;">${item.variant_name}</p>` : ''}
                            <p class="inr-blck" style="font-size: 14px; font-weight: 600; color: #203A72; margin: 0;">$${parseFloat(item.total).toFixed(2)}</p>
                        </div>
                    </div>
                    ${bundleItemsHtml}
                    <div class="d-flex align-items-center justify-content-between" style="margin-top: 10px;">
                        <div class="input-group quantity-group" style="max-width: 140px;">
                            <button class="btn btn-outline-secondary btn-minus" type="button" onclick="updateCartItemQty(${item.id}, ${item.quantity - 1})" style="width: 36px; height: 36px; border-color: #D9D9D9; background: #F5F5F5; color: #203A72; font-size: 18px; padding: 0;">−</button>
                            <input type="text" class="form-control text-center quantity-value" value="${item.quantity}" id="cartQty_${item.id}" readonly style="border: 1px solid #D9D9D9; height: 36px; font-size: 14px; font-weight: 600;">
                            <button class="btn btn-outline-secondary btn-plus" type="button" onclick="updateCartItemQty(${item.id}, ${item.quantity + 1})" style="width: 36px; height: 36px; border-color: #D9D9D9; background: #F5F5F5; color: #203A72; font-size: 18px; padding: 0;">+</button>
                        </div>
                        <button type="button" class="btn btn-sm" onclick="removeCartItem(${item.id})" style="color: #D30606; background: none; border: none; padding: 5px 10px;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        document.getElementById('cartSubtotal').textContent = '$' + parseFloat(subtotal).toFixed(2);
    }

    function updateCartItemQty(itemId, newQty) {
        if (newQty < 1) {
            removeCartItem(itemId);
            return;
        }

        fetch('{{ route("api.cart.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                item_id: itemId,
                quantity: newQty
            }),
            credentials: 'include'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCartData();
                }
            })
            .catch(error => {
                console.error('Error updating cart:', error);
            });
    }

    function removeCartItem(itemId) {
        fetch('{{ route("api.cart.remove") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                item_id: itemId
            }),
            credentials: 'include'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCartData();
                }
            })
            .catch(error => {
                console.error('Error removing item:', error);
            });
    }

    // Load cart on page load
    document.addEventListener('DOMContentLoaded', function () {
        loadCartData();

        // Close cart panel
        const closeBtn = document.getElementById('closeCartPanel');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                const cartPanel = document.getElementById('cartPanel');
                if (cartPanel) {
                    cartPanel.classList.remove('active');
                    setTimeout(() => {
                        cartPanel.classList.add('hidden');
                    }, 300);
                }
            });
        }
    });

    // Reload cart when items are added/updated
    document.addEventListener('cartUpdated', function () {
        loadCartData();
    });
</script>