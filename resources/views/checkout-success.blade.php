@extends('layouts.master')

@section('title', 'Payment Success')

@section('content')
<div style="text-align: center; padding: 100px 5%;">
    <div style="width: 150px; height: 150px; background: #ECFDF5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; color: #10B981;">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
    </div>
    <h1 style="font-size: 2.5rem; font-weight: 800; color: var(--color-text-dark); margin-bottom: 20px;">Payment Successful!</h1>
    <p style="font-size: 1.1rem; color: var(--color-text-medium); margin-bottom: 40px;">Thank you for your purchase. Your order is being processed and you will receive an email confirmation shortly.</p>
    
    <style>
        .btn-continue {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #F25C3B; /* Brand Orange */
            color: white;
            padding: 16px 40px;
            border-radius: 50px; /* Pill shape like home page */
            font-size: 1.05rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(242, 92, 59, 0.3);
        }

        .btn-continue:hover {
            background: #E04A2A; /* Primary Hover */
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(242, 92, 59, 0.4);
            color: white;
        }

        .btn-continue svg {
            transition: transform 0.3s ease;
        }

        .btn-continue:hover svg {
            transform: translateX(-5px);
        }
    </style>

    <a href="{{ route('home') }}" class="btn-continue">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Continue Shopping
    </a>
</div>

<!-- Success Sound -->
<audio id="success-sound" preload="auto">
    <source src="{{ asset('sounds/success.mp3') }}" type="audio/mpeg">
</audio>

<script>
    // Play success sound
    window.addEventListener('DOMContentLoaded', () => {
        const sound = document.getElementById('success-sound');
        if (sound) {
            sound.play().catch(e => console.log("Autoplay blocked by browser. User interaction needed."));
        }
    });

    // Clear cart on success
    const userId = "{{ Auth::id() }}";
    localStorage.removeItem(`cart_user_${userId}`);
</script>
@endsection
