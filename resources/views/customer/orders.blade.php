@extends('layouts.customer')

@section('title', 'My Orders')

@section('styles')
<style>
    .order-card { position: relative; cursor: pointer; border-radius: 20px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; background: white; height: 100%; transition: 0.3s; }
    .order-card, .order-card * { cursor: pointer; }
    .order-card a, .order-card button { cursor: pointer; }
    .order-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    .status-badge { font-size: 10px; padding: 4px 10px; border-radius: 30px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
    .payment-badge { font-size: 10px; padding: 4px 10px; border-radius: 30px; font-weight: 700; margin-left: 5px; }
    .orders-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
    @media (max-width: 991px) { .orders-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 576px) { .orders-grid { grid-template-columns: 1fr; } .order-card { margin-bottom: 0; } }
    .loader-dots { display: flex; align-items: center; justify-content: center; padding: 30px 0; display: none; }
    .loader-dots div { width: 8px; height: 8px; margin: 0 4px; background: var(--primary); border-radius: 50%; animation: loader-dots 0.6s infinite alternate; }
    .loader-dots div:nth-child(2) { animation-delay: 0.2s; }
    .loader-dots div:nth-child(3) { animation-delay: 0.4s; }
    @keyframes loader-dots { from { opacity: 1; transform: scale(1); } to { opacity: 0.3; transform: scale(1.5); } }
</style>
@endsection

@section('content')
<div class="mb-4">
    <h2 class="fw-800" style="letter-spacing: -1px;">My Orders</h2>
    <p class="text-muted small">Scroll down to see more history</p>
</div>

<div id="orders-container" class="orders-grid">
    @if($orders->count() > 0)
        @include('customer.partials.order_cards')
    @else
        <div class="text-center py-5 w-100 grid-column-span-all">
            <div class="mb-4"><i class="fas fa-receipt fa-4x text-muted opacity-25"></i></div>
            <h4 class="fw-bold">No orders yet</h4>
            <p class="text-muted">Treat yourself to something delicious!</p>
            <a href="{{ url('/') }}" class="btn btn-primary mt-3">Order Something Now</a>
        </div>
    @endif
</div>

<!-- Infinite Scroll Loader -->
<div class="loader-dots" id="scroll-loader">
    <div></div><div></div><div></div>
</div>

<div id="no-more-history" class="text-center py-4 text-muted small d-none">
    <i class="fas fa-check-circle me-1"></i> You've seen all your orders
</div>
@endsection

@section('scripts')
<script>
    let page = 1;
    let loading = false;
    let hasMore = @if($orders->hasMorePages()) true @else false @endif;

    // Make entire order card clickable (delegated so it works for AJAX-loaded cards too)
    $(document).on('click', '.order-card', function(e) {
        // If clicked element is a link or button, let it handle itself
        if ($(e.target).closest('a, button').length) return;
        var url = $(this).data('order-url');
        if (url) window.location.href = url;
    });

    $(window).on('scroll', function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 400) {
            if (!loading && hasMore) loadMoreOrders();
        }
    });

    function loadMoreOrders() {
        page++;
        loading = true;
        $("#scroll-loader").fadeIn();

        $.ajax({
            url: "?page=" + page,
            type: "get",
            beforeSend: function() { $("#scroll-loader").show(); }
        }).done(function(data) {
            if (data.trim().length === 0) {
                hasMore = false;
                $("#scroll-loader").fadeOut();
                $("#no-more-history").removeClass("d-none");
                return;
            }
            $("#scroll-loader").fadeOut();
            $("#orders-container").append(data);
            loading = false;
        }).fail(function() {
            $("#scroll-loader").fadeOut();
            loading = false;
        });
    }
</script>
@endsection
