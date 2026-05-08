@extends('rider.layouts.app')

@section('title', 'Available Deliveries')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bx bx-package me-2"></i>Available Deliveries
            </h4>
            <p class="text-muted mb-0">First to accept gets the delivery!</p>
        </div>
        <span class="badge bg-label-warning fs-6" id="available-count">
            {{ $broadcasts->count() }} Available
        </span>
    </div>

    <!-- Urgency alert — toggled by JS on each poll -->
    <div class="alert alert-info d-flex align-items-center mb-4 {{ $broadcasts->count() > 0 ? '' : 'd-none' }}"
         id="urgency-alert">
        <i class="bx bx-info-circle me-2"></i>
        <div>
            <strong>Quick Action Required!</strong> These deliveries are available to all companies.
            Accept quickly before another company does.
        </div>
    </div>

    <!-- Poll indicator -->
    <div class="d-flex justify-content-end mb-2">
        <small id="poll-status" class="text-muted">
            <i class="bx bx-refresh me-1"></i>Checking for new deliveries every 30s
        </small>
    </div>

    <!-- Cards grid — innerHTML replaced by AJAX poll when the broadcast set changes -->
    <div id="broadcasts-grid">
        @include('rider.deliveries._available_cards', ['broadcasts' => $broadcasts, 'fees' => $fees])
    </div>

</div>
@endsection

@push('styles')
<style>
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50%       { transform: scale(1.05); }
}
.pulse-btn       { animation: pulse 1.5s infinite; }
.pulse-btn:hover { animation: none; }

/* Brief fade-in when new cards are injected */
@keyframes grid-refresh { from { opacity: 0.5; } to { opacity: 1; } }
.grid-refreshed { animation: grid-refresh 0.3s ease-out; }

#poll-status.is-polling { color: #696cff; }
#poll-status.is-error   { color: #ff3e1d; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Accept form confirmation (delegated — survives innerHTML swaps) ──
    document.addEventListener('submit', function (e) {
        if (!e.target.classList.contains('accept-form')) return;
        if (!confirm(e.target.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });

    // ── Silent AJAX poll ────────────────────────────────────────────────
    var POLL_URL   = '{{ route("rider.deliveries.available.poll") }}';
    var POLL_MS    = 30000;
    var lastIds    = {!! json_encode($broadcasts->pluck('id')->toArray()) !!};
    var statusEl   = document.getElementById('poll-status');
    var countEl    = document.getElementById('available-count');
    var alertEl    = document.getElementById('urgency-alert');
    var gridEl     = document.getElementById('broadcasts-grid');
    var pollTimer;

    function sameIds(a, b) {
        if (a.length !== b.length) return false;
        var sa = a.slice().sort(), sb = b.slice().sort();
        return sa.every(function (v, i) { return v === sb[i]; });
    }

    function setStatus(cls, iconClass, text) {
        statusEl.className = 'text-muted ' + cls;
        statusEl.innerHTML = '<i class="bx ' + iconClass + ' me-1"></i>' + text;
    }

    function poll() {
        setStatus('is-polling', 'bx-loader-alt bx-spin', 'Checking for new deliveries\u2026');

        fetch(POLL_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (data) {
            // Update count badge
            countEl.textContent = data.count + ' Available';

            // Show/hide urgency alert
            alertEl.classList.toggle('d-none', data.count === 0);

            // Only swap the grid when the set of broadcast ids actually changed.
            // This prevents modals being dismissed and the page flickering when
            // nothing has changed — the rider gets a completely stable UI.
            if (!sameIds(lastIds, data.ids)) {
                lastIds = data.ids;
                gridEl.innerHTML = data.html;
                gridEl.classList.remove('grid-refreshed');
                void gridEl.offsetWidth; // force reflow so animation retriggers
                gridEl.classList.add('grid-refreshed');
            }

            setStatus('', 'bx-refresh', 'Checking for new deliveries every 30s');
        })
        .catch(function (err) {
            setStatus('is-error', 'bx-error-circle', 'Auto-refresh failed \u2014 will retry');
            console.warn('[available poll]', err);
        })
        .finally(function () {
            pollTimer = setTimeout(poll, POLL_MS);
        });
    }

    // Start the first poll after one full interval
    pollTimer = setTimeout(poll, POLL_MS);

    // Cancel if the rider navigates away before the timer fires
    window.addEventListener('beforeunload', function () { clearTimeout(pollTimer); });

}());
</script>
@endpush