// public/js/cart.js
//
// Event handlers only.
//
// These functions are global, defined in layouts/app.blade.php:
//   openCartDrawer()    — slide drawer in + reload items
//   closeCartDrawer()   — slide drawer out
//   loadCartSidebar()   — AJAX fetch → #gh-cart-items
//   updateCartUI(data)  — sync all count/total elements
//   showToast(t, msg)   — bottom-right notification
//
// Drawer / sidebar selectors (match cart-sidebar.blade.php):
//   Qty buttons  → .update-cart-quantity  [data-id]  [data-action]
//   Qty display  → .quantity-input-{cartKey}   (<span> — use .text())
//   Item total   → .item-total-{cartKey}
//   Remove btn   → .remove-from-cart      [data-id]
//   Cart count   → #cart-count  /  #gh-cart-drawer-count
//   Cart total   → #cart-total  /  #gh-cart-drawer-total  /  .total_price

$(document).ready(function () {

    /* ── Add to Cart ─────────────────────────────────────────────────
       Single handler for all pages.
       • Listing / category pages: no variants, standard quantity input
       • Show page: reads selectedVariants + TOTAL_VARIANT_GROUPS
         globals declared in show.blade.php @push('scripts')
    ─────────────────────────────────────────────────────────────── */
    $(document).on('click', '.add-to-cart', function (e) {
        e.preventDefault();

        var btn       = $(this);
        var productId = btn.data('product-id');

        // Quantity — show page uses .quantity-num2, listing pages use .quantity-input
        var quantity =
            parseInt(btn.closest('.product-details').find('.quantity-input').val(), 10) ||
            parseInt($('.quantity-num2').val(), 10) ||
            1;

        // Variant guard — only active on show page where these globals exist
var hasVariants =
    typeof TOTAL_VARIANT_GROUPS !== 'undefined' &&
    typeof selectedVariants     !== 'undefined';

if (hasVariants) {
    var missingAny = false;

    document.querySelectorAll('.ssp-variant-group').forEach(function (group) {
        var name = group.querySelector('.ssp-variant-btn') &&
                   group.querySelector('.ssp-variant-btn').dataset.variantName;
        if (name && !selectedVariants[name]) {
                    group.classList.remove('needs-selection');
                    void group.offsetWidth; // force reflow to restart animation
                    group.classList.add('needs-selection');
                    missingAny = true;
                }
            });

            if (missingAny) return; // bail before touching the button
        }

        // Build variant payload (empty object on non-show pages)
        var variantsPayload = {};
        if (hasVariants) {
            Object.keys(selectedVariants).forEach(function (name) {
                variantsPayload[name] = selectedVariants[name].value;
            });
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Adding…');

        $.ajax({
            url:    '/cart/add',
            method: 'POST',
            data: {
                _token:            $('meta[name="csrf-token"]').attr('content'),
                product_id:        productId,
                quantity:          quantity,
                selected_variants: variantsPayload,
            },
            success: function (response) {
                updateCartUI(response);
                showToast('success', response.message);
                openCartDrawer(); // loads sidebar + slides in
                btn.html('<i class="fas fa-check me-1"></i> Added!');
                setTimeout(function () {
                    btn.html('Add to Cart').prop('disabled', false);
                }, 2000);
            },
            error: function (xhr) {
                showToast('error', xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Failed to add product to cart');
                btn.html('Add to Cart').prop('disabled', false);
            },
        });
    });

    /* ── Sidebar Quantity +/- ────────────────────────────────────── */
    $(document).on('click', '.update-cart-quantity', function (e) {
    e.preventDefault();
    e.stopPropagation();

    var btn     = $(this);
    var cartKey = btn.data('id');
    var action  = btn.data('action');

    if (!cartKey) return;

    var qtySpan = $('#gh-cart-drawer .quantity-input-' + cartKey);
    
    // DEBUG
    console.log('--- QTY BTN CLICK ---');
    console.log('cartKey:', cartKey);
    console.log('action:', action);
    console.log('qtySpan found:', qtySpan.length);
    console.log('qtySpan raw text:', JSON.stringify(qtySpan.text()));
    console.log('qtySpan HTML:', qtySpan[0] ? qtySpan[0].outerHTML : 'NOT FOUND');
    
    var current = parseInt(qtySpan.text().trim(), 10);
    console.log('current (parsed):', current);
    console.log('typeof current:', typeof current);

    if (isNaN(current) || current < 1) current = 1;

    var newQty;
    if (action === 'increase') {
        newQty = current + 1;
    } else if (action === 'decrease') {
        if (current <= 1) {
            if (confirm('Remove this item from cart?')) {
                removeFromCart(cartKey);
            }
            return;
        }
        newQty = current - 1;
    } else {
        return;
    }

    console.log('newQty:', newQty);
    console.log('typeof newQty:', typeof newQty);
    console.log('--- END DEBUG ---');

    qtySpan.text(newQty);
    var rawTotal = parseFloat($('#gh-cart-drawer .item-total-' + cartKey).text().replace(/[^\d.]/g, ''));
    var unitPrice = rawTotal / current;
    var newTotal  = (unitPrice * newQty).toFixed(2);
    $('.item-total-' + cartKey).text('₦' + parseFloat(newTotal).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));

    updateCartQuantity(cartKey, newQty);
});

    /* ── Sidebar Remove ──────────────────────────────────────────── */
    $(document).on('click', '.remove-from-cart', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var cartKey = $(this).data('id');
        if (!cartKey) return;

        removeFromCart(cartKey);
    });

    /* ── Clear Entire Cart ───────────────────────────────────────── */
    $(document).on('click', '.clear-cart', function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to clear your cart?')) return;

        $.ajax({
            url:    '/cart/clear',
            method: 'POST',
            data:   { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                updateCartUI(response);
                loadCartSidebar();
                showToast('success', response.message);
                if (window.location.pathname === '/cart') location.reload();
            },
            error: function () {
                showToast('error', 'Failed to clear cart');
            },
        });
    });

    /* ── updateCartQuantity (private) ───────────────────────────── */
function updateCartQuantity(cartKey, quantity) {
    $.ajax({
        url:    '/cart/update',
        method: 'POST',
        data: {
            _token:     $('meta[name="csrf-token"]').attr('content'),
            product_id: cartKey,
            quantity:   parseInt(quantity, 10),  // explicit cast
        },
        success: function (response) {
            updateCartUI(response);
            // Server gives us the authoritative item total — correct it silently
            $('#gh-cart-drawer .item-total-' + cartKey).text('₦' + response.item_total);
        },
        error: function (xhr) {
            showToast('error', xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Failed to update cart');
            loadCartSidebar();
        },
    });
}

    /* ── removeFromCart (private) ────────────────────────────────── */
    function removeFromCart(cartKey) {
        $.ajax({
            url:    '/cart/remove',
            method: 'POST',
            data: {
                _token:     $('meta[name="csrf-token"]').attr('content'),
                product_id: cartKey,
            },
            success: function (response) {
                // Fade out the removed row
                $(
                    'li.gh-csi[data-product-id="' + cartKey + '"],' +
                    '#gh-cart-items [data-product-id="' + cartKey + '"]'
                ).fadeOut(300, function () { $(this).remove(); });

                updateCartUI(response);
                showToast('success', response.message || 'Item removed');

                // If cart is now empty, reload to show the empty state
                if (response.cart_count === 0) {
                    loadCartSidebar();
                    if (window.location.pathname === '/cart') location.reload();
                }
            },
            error: function () {
                showToast('error', 'Failed to remove product');
                loadCartSidebar();
            },
        });
    }

    /* ── Init: populate sidebar on page load ─────────────────────── */
    loadCartSidebar();

});