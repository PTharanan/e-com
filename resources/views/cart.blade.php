@extends('layouts.master')

@section('title', 'Shopping Cart')

@section('content')
<script src="https://js.stripe.com/v3/"></script>
<style>
    .cart-section {
        padding: 60px 5%;
        max-width: 1200px;
        margin: 0 auto;
    }

    .cart-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 40px;
        color: var(--color-text-dark);
    }

    .cart-container {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 40px;
    }

    @media (max-width: 992px) {
        .cart-container {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .cart-item {
            grid-template-columns: 80px 1fr;
            padding: 15px;
            gap: 15px;
        }

        .item-image {
            width: 80px;
            height: 80px;
            align-self: flex-start;
        }

        .item-actions {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            grid-column: 1 / -1;
            margin-top: 10px;
            padding-top: 15px;
            border-top: 1px dashed var(--color-border);
        }

        .item-price {
            font-size: 1.1rem;
        }
    }

    .cart-items {
        background: var(--color-white);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--color-border);
        overflow: hidden;
    }

    .cart-item {
        display: grid;
        grid-template-columns: 120px 1fr auto;
        gap: 25px;
        padding: 25px;
        border-bottom: 1px solid var(--color-border);
        align-items: center;
        transition: var(--transition-fast);
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .cart-item:hover {
        background: #FAFAFA;
    }

    .item-image {
        width: 120px;
        height: 120px;
        background: #F8F9FA;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .item-image img {
        max-width: 80%;
        max-height: 80%;
        object-fit: contain;
    }

    .item-details h3 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--color-text-dark);
    }

    .item-category {
        font-size: 0.8rem;
        color: var(--color-text-light);
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 12px;
        display: block;
    }

    .item-price {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--color-primary);
    }

    .item-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 15px;
    }

    .qty-control {
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--color-bg-light);
        padding: 5px;
        border-radius: var(--radius-pill);
    }

    .qty-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        background: var(--color-white);
        color: var(--color-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1.2rem;
    }

    .qty-btn:hover:not(:disabled) {
        background: var(--color-primary);
        color: var(--color-white);
    }

    .qty-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .qty-val {
        font-weight: 700;
        min-width: 20px;
        text-align: center;
    }

    .item-total {
        font-weight: 700;
        font-size: 1.1rem;
    }

    .cart-summary {
        background: var(--color-white);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--color-border);
        padding: 30px;
        height: fit-content;
        position: sticky;
        top: 100px;
    }

    .summary-title {
        font-size: 1.3rem;
        font-weight: 800;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--color-border);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        color: var(--color-text-medium);
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--color-border);
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--color-text-dark);
    }

    .btn-checkout-page {
        width: 100%;
        background: var(--color-primary);
        color: var(--color-white);
        border: none;
        padding: 16px;
        border-radius: var(--radius-sm);
        font-size: 1rem;
        font-weight: 700;
        margin-top: 30px;
        cursor: pointer;
        transition: var(--transition-fast);
    }

    .btn-checkout-page:hover {
        background: var(--color-primary-hover);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .empty-cart-container {
        text-align: center;
        padding: 100px 0;
        background: var(--color-white);
        border-radius: var(--radius-md);
        border: 1px solid var(--color-border);
    }

    .empty-icon {
        width: 150px;
        height: 150px;
        margin: 0 auto 30px;
        background: var(--color-bg-light);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: var(--color-primary);
    }

    /* PAYMENT MODAL STYLES */
    .payment-modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.85);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(8px);
    }

    .payment-card {
        background: var(--color-white);
        width: 100%;
        max-width: 550px;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        color: var(--color-text-dark);
        border: 1px solid var(--color-border);
    }

    .payment-header {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .payment-header h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--color-primary);
    }

    .payment-form-group {
        margin-bottom: 25px;
    }

    .payment-label {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: var(--color-text-medium);
    }

    #card-element {
        background: #F8F9FA;
        padding: 15px;
        border-radius: 12px;
        border: 1px solid var(--color-border);
    }

    .payment-footer {
        display: flex;
        gap: 15px;
        margin-top: 40px;
    }

    .btn-payment {
        flex: 1;
        padding: 16px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-pay-confirm {
        background: var(--color-primary);
        color: white;
        box-shadow: 0 4px 15px rgba(242, 92, 59, 0.3);
    }

    .btn-pay-confirm:hover {
        background: var(--color-primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(242, 92, 59, 0.4);
    }

    .btn-pay-back {
        background: #F0F0F0;
        color: var(--color-text-dark);
        max-width: 120px;
    }

    .btn-pay-back:hover {
        background: #E0E0E0;
    }

    .stripe-error {
        color: #EF4444;
        font-size: 0.85rem;
        margin-top: 10px;
    }
    
    #payment-total-display {
        font-weight: 700;
        color: var(--color-text-dark);
        font-size: 1.2rem;
    }

    .payment-timer {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--color-text-medium);
        padding: 6px 14px;
        background: #F8F9FA;
        border-radius: var(--radius-pill);
        border: 1px solid var(--color-border);
    }

    .payment-timer svg {
        flex-shrink: 0;
    }

    .payment-timer.warning {
        color: #EF4444;
        background: #FEF2F2;
        border-color: #FECACA;
        animation: timerPulse 1s ease-in-out infinite;
    }

    @keyframes timerPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    /* Mobile Responsive Payment Modal */
    @media (max-width: 768px) {
        .payment-card {
            padding: 25px;
            margin: 0 15px;
            border-radius: 15px;
        }

        .payment-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }

        .payment-header h2 {
            font-size: 1.25rem;
        }

        .payment-footer {
            flex-direction: column-reverse;
            margin-top: 25px;
            gap: 10px;
        }

        .btn-pay-back {
            max-width: 100%;
        }
        
        .btn-payment {
            padding: 14px;
        }
    }
</style>

<div class="payment-modal-overlay" id="payment-modal">
    <div class="payment-card">
        <div class="payment-header">
            <h2>Secure Payment</h2>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="payment-timer" id="payment-timer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span id="timer-display">05:00</span>
                </div>
                <div id="payment-total-display" style="font-weight: 700;"></div>
            </div>
        </div>

        <form id="payment-form">
            <div class="payment-form-group">
                <label class="payment-label">Card Details *</label>
                <div id="card-element"></div>
                <div id="card-errors" class="stripe-error"></div>
            </div>

            <div class="payment-form-group">
                <label class="payment-label">Name on Card *</label>
                <input type="text" id="card-name" placeholder="Name as on card" 
                    style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--color-border); background: #F8F9FA; color: var(--color-text-dark);">
            </div>

            <div class="payment-footer">
                <button type="button" class="btn-payment btn-pay-back" id="close-payment">← Back</button>
                <button type="submit" class="btn-payment btn-pay-confirm" id="submit-payment">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Pay Now
                </button>
            </div>
        </form>
    </div>
</div>

<section class="cart-section">
    <h1 class="cart-title">Your Cart</h1>

    <div id="cart-content">
        <!-- JS will render content here -->
        <div class="cart-container" id="filled-cart" style="display: none;">
            <div class="cart-items" id="cart-items-list">
                <!-- Items go here -->
            </div>

            <div class="cart-summary">
                <h2 class="summary-title">Order Summary</h2>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotal-val">{{ currency_symbol() }}0.00</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span id="total-val">{{ currency_symbol() }}0.00</span>
                </div>
                <button class="btn-checkout-page">Proceed to Checkout</button>
            </div>
        </div>

        <div class="empty-cart-container" id="empty-cart" style="display: none;">
            <div class="empty-icon">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </div>
            <h2>Your cart is empty</h2>
            <p style="margin: 15px 0 30px; color: var(--color-text-medium);">Looks like you haven't added anything to your cart yet.</p>
            <a href="{{ route('products') }}" class="btn-checkout-page" style="display: inline-block; width: auto; padding: 15px 40px; text-decoration: none;">Start Shopping</a>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const currencySymbol = @json(currency_symbol());
        const productsData = @json($products);
        const cartItemsList = document.getElementById('cart-items-list');
        const filledCart = document.getElementById('filled-cart');
        const emptyCart = document.getElementById('empty-cart');
        const subtotalVal = document.getElementById('subtotal-val');
        const totalVal = document.getElementById('total-val');

        const renderCart = () => {
            const items = window.cartItems || {};
            const productIds = Object.keys(items);

            if (productIds.length === 0) {
                filledCart.style.display = 'none';
                emptyCart.style.display = 'block';
                return;
            }

            filledCart.style.display = 'grid';
            emptyCart.style.display = 'none';
            cartItemsList.innerHTML = '';

            let total = 0;

            productIds.forEach(id => {
                const product = productsData.find(p => p.id == id);
                if (!product) return;

                const qty = items[id].qty;
                const itemTotal = product.final_price * qty;
                total += itemTotal;

                const itemHtml = `
                    <div class="cart-item" data-id="${id}">
                        <div class="item-image">
                            <img src="{{ asset('') }}${product.main_image_url}" alt="${product.name}">
                        </div>
                        <div class="item-details">
                            <span class="item-category">${product.category.name}</span>
                            <h3>${product.name}</h3>
                            <div class="item-price">
                                ${product.discount_percentage ? `
                                    <span style="text-decoration: line-through; font-size: 0.8rem; color: #9CA3AF; margin-right: 8px;">${currencySymbol}${parseFloat(product.price).toFixed(2)}</span>
                                    <span style="color: #10B981;">${currencySymbol}${parseFloat(product.final_price).toFixed(2)}</span>
                                ` : `${currencySymbol}${parseFloat(product.price).toFixed(2)}`}
                            </div>
                        </div>
                        <div class="item-actions">
                            <div class="qty-control">
                                <button class="qty-btn dec" data-id="${id}">−</button>
                                <span class="qty-val">${qty}</span>
                                <button class="qty-btn inc" data-id="${id}">+</button>
                            </div>
                            <div class="item-total">${currencySymbol}${itemTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}</div>
                        </div>
                    </div>
                `;
                cartItemsList.insertAdjacentHTML('beforeend', itemHtml);
            });

            subtotalVal.innerText = `${currencySymbol}${total.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
            totalVal.innerText = `${currencySymbol}${total.toLocaleString(undefined, {minimumFractionDigits: 2})}`;

            attachListeners();
        };

        const attachListeners = () => {
            document.querySelectorAll('.qty-btn.inc').forEach(btn => {
                btn.onclick = async (e) => {
                    const id = e.target.dataset.id;
                    const btnConfirm = e.target;
                    btnConfirm.disabled = true;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                        const response = await fetch('{{ route("products.add-to-cart") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                _token: csrfToken,
                                product_id: id,
                                quantity: 1
                            })
                        });

                        const result = await response.json();
                        if (result.success) {
                            const current = window.cartItems[id] || { qty: 0, t: Date.now() };
                            window.cartItems[id] = { qty: current.qty + 1, t: Date.now() };
                            localStorage.setItem(`cart_user_{{ Auth::id() }}`, JSON.stringify(window.cartItems));
                            // Update global badges
                            const totalItems = Object.values(window.cartItems).reduce((a, b) => a + (b.qty || 0), 0);
                            document.querySelectorAll('.cart-badge').forEach(b => b.innerText = totalItems);
                            renderCart();
                        } else {
                            alert(result.message || 'Limit reached');
                        }
                    } catch (err) { console.error(err); }
                    btnConfirm.disabled = false;
                };
            });

            document.querySelectorAll('.qty-btn.dec').forEach(btn => {
                btn.onclick = async (e) => {
                    const id = e.target.dataset.id;
                    const btnConfirm = e.target;
                    btnConfirm.disabled = true;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                        const response = await fetch('{{ route("products.remove-from-cart") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                _token: csrfToken,
                                product_id: id,
                                quantity: 1
                            })
                        });

                        const result = await response.json();
                        if (result.success) {
                            window.cartItems[id].qty--;
                            if (window.cartItems[id].qty <= 0) delete window.cartItems[id];
                            
                            localStorage.setItem(`cart_user_{{ Auth::id() }}`, JSON.stringify(window.cartItems));
                            // Update global badges
                            const totalItems = Object.values(window.cartItems).reduce((a, b) => a + (b.qty || 0), 0);
                            document.querySelectorAll('.cart-badge').forEach(b => {
                                b.innerText = totalItems;
                                if (totalItems === 0) b.style.display = 'none';
                            });
                            renderCart();
                        }
                    } catch (err) { console.error(err); }
                    btnConfirm.disabled = false;
                };
            });
        };

        // Checkout & Payment Modal Logic
        const stripe = Stripe('{{ env("STRIPE_KEY") }}');
        const elements = stripe.elements();
        
        // Custom styling for Stripe Elements (matching the site theme)
        const style = {
            base: {
                color: '#1A1A1A',
                fontFamily: '"Poppins", sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': { color: '#888888' },
                iconColor: '#F25C3B'
            },
            invalid: {
                color: '#EF4444',
                iconColor: '#EF4444'
            }
        };

        const card = elements.create('card', { style: style, hidePostalCode: true });
        card.mount('#card-element');

        const paymentModal = document.getElementById('payment-modal');
        const btnCheckout = document.querySelector('.btn-checkout-page');
        const closePayment = document.getElementById('close-payment');
        const paymentForm = document.getElementById('payment-form');
        const totalDisplay = document.getElementById('payment-total-display');
        let clientSecret = '';

        btnCheckout.addEventListener('click', async () => {
            const items = window.cartItems || {};
            if (Object.keys(items).length === 0) return;

            btnCheckout.disabled = true;
            btnCheckout.innerText = 'Initializing...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const response = await fetch('{{ route("checkout") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        _token: csrfToken,
                        cart_items: items
                    })
                });

                const data = await response.json();
                
                if (data.clientSecret) {
                    clientSecret = data.clientSecret;
                    totalDisplay.innerText = `Total: ${currencySymbol}${parseFloat(data.total).toLocaleString(undefined, {minimumFractionDigits: 2})}`;
                    document.getElementById('submit-payment').innerText = `Pay ${currencySymbol}${parseFloat(data.total).toLocaleString(undefined, {minimumFractionDigits: 2})}`;
                    paymentModal.style.display = 'flex';
                    document.body.classList.add('modal-open');
                    
                    // Start 5-minute countdown timer
                    startPaymentTimer();
                    
                    // Auto-focus the card field
                    setTimeout(() => card.focus(), 100);
                } else {
                    alert('Error: ' + (data.error || 'Could not initialize payment'));
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred.');
            }
            btnCheckout.disabled = false;
            btnCheckout.innerText = 'Proceed to Checkout';
        });

        closePayment.addEventListener('click', () => {
            closePaymentModal();
        });

        // Payment Timer Logic
        let paymentTimerInterval = null;

        function startPaymentTimer() {
            clearInterval(paymentTimerInterval);
            let timeLeft = 300; // 5 minutes in seconds
            const timerDisplay = document.getElementById('timer-display');
            const timerContainer = document.getElementById('payment-timer');
            timerContainer.classList.remove('warning');

            updateTimerDisplay(timeLeft, timerDisplay);

            paymentTimerInterval = setInterval(() => {
                timeLeft--;
                updateTimerDisplay(timeLeft, timerDisplay);

                // Add warning style when less than 60 seconds
                if (timeLeft <= 60) {
                    timerContainer.classList.add('warning');
                }

                if (timeLeft <= 0) {
                    clearInterval(paymentTimerInterval);
                    closePaymentModal();
                    alert('Payment session expired. Please try checkout again.');
                }
            }, 1000);
        }

        function updateTimerDisplay(seconds, el) {
            const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
            const secs = (seconds % 60).toString().padStart(2, '0');
            el.textContent = `${mins}:${secs}`;
        }

        function closePaymentModal() {
            paymentModal.style.display = 'none';
            document.body.classList.remove('modal-open');
            clearInterval(paymentTimerInterval);
        }

        paymentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const cardName = document.getElementById('card-name').value;
            if (!cardName) {
                alert('Please enter the name on the card.');
                document.getElementById('card-name').focus();
                return;
            }

            const submitBtn = document.getElementById('submit-payment');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Processing...';

            const { paymentIntent, error } = await stripe.confirmCardPayment(clientSecret, {
                payment_method: {
                    card: card,
                    billing_details: {
                        name: cardName
                    }
                }
            });

            if (error) {
                document.getElementById('card-errors').textContent = error.message;
                submitBtn.disabled = false;
                submitBtn.innerText = 'Pay Now';
            } else {
                if (paymentIntent.status === 'succeeded') {
                    window.location.href = '{{ route("checkout.success") }}';
                }
            }
        });

        // Focus Name field when Enter is pressed in Card field
        card.on('change', (event) => {
            if (event.complete) {
                // Optional: Auto-focus name when card is finished? 
                // Let's stick to the Enter request.
            }
        });

        // The card element is in an iframe, but Stripe allows listening to escape/etc.
        // For custom Enter behavior, we listen on the input field for name:
        document.getElementById('card-name').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                paymentForm.requestSubmit(); // Triggers the submit event
            }
        });

        // Listen for Enter inside the Stripe Element container
        document.getElementById('card-element').addEventListener('keydown', (e) => {
            // Note: This might only catch keys that bubble out of the iframe
            if (e.key === 'Enter') {
                document.getElementById('card-name').focus();
            }
        });

        // Initial render
        setTimeout(renderCart, 100);
    });
</script>
@endsection
