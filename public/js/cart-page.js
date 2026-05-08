/**
 * public/js/cart-page.js
 *
 * Selectors matched to resources/views/cart/index.blade.php (table layout):
 *   Row        → tr[data-product-id]
 *   Qty input  → .quantity-num  (inside the row)
 *   Total cell → .total-cell    (td)
 *   Remove     → .remove-item   data-product-id
 *   Coupon     → input[name="coupon_code"]
 *   Discount   → #discount-row / #discount
 */
$(document).ready(function () {

    let debounceTimer = null;

    /* ── helpers ─────────────────────────────────────────────────── */
    function cartRow(productId) {
        return $('tr[data-product-id="' + productId + '"]');
    }

    /* ── Quantity − button ───────────────────────────────────────── */
    $(document).on('click', '.quantity-arrow-minus', function (e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        const row       = cartRow(productId);
        const input     = row.find('.quantity-num');
        let   qty       = Math.max(1, (parseInt(input.val(), 10) || 1) - 1);
        input.val(qty);
        debounceUpdate(productId, qty);
    });

    /* ── Quantity + button ───────────────────────────────────────── */
    $(document).on('click', '.quantity-arrow-plus', function (e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        const row       = cartRow(productId);
        const input     = row.find('.quantity-num');
        const maxStock  = parseInt(input.attr('max'), 10) || 9999;
        let   qty       = Math.min(maxStock, (parseInt(input.val(), 10) || 1) + 1);
        input.val(qty);
        debounceUpdate(productId, qty);
    });

    /* ── Direct input change ─────────────────────────────────────── */
    $(document).on('change', '.quantity-num', function () {
        const input     = $(this);
        const productId = input.data('product-id');
        const maxStock  = parseInt(input.attr('max'), 10) || 9999;
        let   qty       = Math.max(1, Math.min(maxStock, parseInt(input.val(), 10) || 1));
        input.val(qty);
        debounceUpdate(productId, qty);
    });

    function debounceUpdate(productId, qty) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => updateCartItem(productId, qty), 300);
    }

    /* ── AJAX: update quantity ───────────────────────────────────── */
    function updateCartItem(productId, quantity) {
        $.ajax({
            url:    '/cart/update',
            method: 'POST',
            data: {
                _token:     $('meta[name="csrf-token"]').attr('content'),
                product_id: productId,
                quantity:   quantity,
            },
            success: function (response) {
                cartRow(productId).find('.total-cell').text('₦ ' + response.item_total);

                if (response.subtotal)    $('#subtotal').text('₦ ' + response.subtotal);
                if (response.grand_total) $('#grand-total').text('₦ ' + response.grand_total);

                if (response.delivery_fee !== null && response.delivery_fee !== undefined) {
                    $('#shipping').text(
                        '₦ ' + parseFloat(response.delivery_fee)
                            .toLocaleString('en-NG', { minimumFractionDigits: 2 })
                    );
                }

                if (response.cart_count !== undefined) $('#cart-count').text(response.cart_count);
                if (response.cart_total)               $('#cart-total').text('₦ ' + response.cart_total);

                $(document).trigger('cart:updated', [response]);
                refreshCouponRow();
            },
            error: function () {
                showToastIfAvailable('error', 'Failed to update cart');
            },
        });
    }

    /* ── Remove item ─────────────────────────────────────────────── */
    $(document).on('click', '.remove-item', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const productId = $(this).data('product-id');
        if (!productId) { console.error('[cart] remove-item: missing product-id'); return; }

        const row = cartRow(productId);

        row.fadeOut(200, function () {
            $.ajax({
                url:    '/cart/remove',
                method: 'POST',
                data: {
                    _token:     $('meta[name="csrf-token"]').attr('content'),
                    product_id: productId,
                },
                success: function (response) {
                    row.remove();

                    if (response.cart_count !== undefined) $('#cart-count').text(response.cart_count);

                    if (response.cart_total) {
                        $('#cart-total').text('₦ ' + response.cart_total);
                        $('#subtotal').text('₦ '   + response.cart_total);
                        $('#grand-total').text('₦ ' + response.cart_total);
                    }

                    if (response.cart_count === 0) { location.reload(); return; }

                    refreshCouponRow();
                },
                error: function (xhr) {
                    row.fadeIn(200);
                    showToastIfAvailable('error', 'Failed to remove item');
                    console.error('[cart] remove error', xhr.responseText);
                },
            });
        });
    });

    /* ── Coupon: Apply ───────────────────────────────────────────── */
    $(document).on('click', '#apply-coupon', function (e) {
        e.preventDefault();
        const code = $('input[name="coupon_code"]').val().trim();
        if (!code) { showToastIfAvailable('warning', 'Please enter a coupon code'); return; }

        const btn = $(this).prop('disabled', true).text('Applying...');

        $.ajax({
            url: '/cart/apply-coupon', method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content'), coupon_code: code },
            success: function (response) {
                btn.prop('disabled', false).text('Apply Coupon');
                if (response.success) {
                    showDiscountRow(response.coupon_code, response.discount, response.discount_label);
                    $('#subtotal').text('₦ ' + response.subtotal);
                    $('#grand-total').text('₦ ' + response.grand_total);
                    showToastIfAvailable('success', response.message);
                    $('#apply-coupon').hide();
                    $('#remove-coupon').show();
                    $('input[name="coupon_code"]').prop('readonly', true);
                } else {
                    showToastIfAvailable('error', response.message);
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).text('Apply Coupon');
                showToastIfAvailable('error', xhr.responseJSON?.message || 'Failed to apply coupon.');
            },
        });
    });

    /* ── Coupon: Remove ──────────────────────────────────────────── */
    $(document).on('click', '#remove-coupon', function (e) {
        e.preventDefault();
        $.ajax({
            url: '/cart/remove-coupon', method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    hideDiscountRow();
                    $('#grand-total').text('₦ ' + response.grand_total);
                    $('#subtotal').text('₦ ' + response.subtotal);
                    $('#apply-coupon').show();
                    $('#remove-coupon').hide();
                    $('input[name="coupon_code"]').val('').prop('readonly', false);
                    showToastIfAvailable('info', 'Coupon removed.');
                }
            },
        });
    });

    /* ── Login → Checkout ────────────────────────────────────────── */
    $(document).on('click', '#login-checkout-btn', function (e) {
        e.preventDefault();
        $('.signin-filter-btn').trigger('click');
    });

    /* ── Helpers ─────────────────────────────────────────────────── */
    function showDiscountRow(code, discount, label) {
        $('#discount-row').show();
        $('#discount').html(
            '<span class="text-success fw-medium">' + code +
            (label ? ' <small class="badge bg-success ms-1">' + label + '</small>' : '') +
            '</span><span class="float-end text-success"> -₦ ' + discount + '</span>'
        );
    }
    function hideDiscountRow() {
        $('#discount-row').hide();
        $('#discount').html('-₦ 0.00');
    }

    function refreshCouponRow() {
        const couponInput = $('input[name="coupon_code"]');
        const couponCode  = couponInput.val();
        if (!couponCode || !couponInput.prop('readonly')) return;
        $.post('/cart/apply-coupon', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            coupon_code: couponCode,
        }, function (response) {
            if (response.success) {
                showDiscountRow(response.coupon_code, response.discount, response.discount_label);
                $('#grand-total').text('₦ ' + response.grand_total);
            } else {
                hideDiscountRow();
                $('#apply-coupon').show(); $('#remove-coupon').hide();
                couponInput.prop('readonly', false);
                showToastIfAvailable('warning', 'Coupon removed: ' + response.message);
            }
        });
    }

    function showToastIfAvailable(type, message) {
        if (typeof showToast === 'function') showToast(type, message);
        else console.log('[cart toast]', type, message);
    }
});