@extends('layouts.app')

@section('title', 'Order #' . $order->order_number . ' - ' . config('app.name'))

@section('content')
<style>
/* ════════════════════════════════════════════════════════
   ORDER DETAIL PAGE — scoped with .od- prefix
   
   CRITICAL: Prevent horizontal overflow on mobile
════════════════════════════════════════════════════════ */

/* ── Global overflow fix ─────────────────────────────── */
.od-page {
  background: #f5f0eb;
  min-height: 100vh;
  overflow-x: hidden;      /* prevent ANY horizontal scroll */
  width: 100%;
  max-width: 100vw;
}

.od-page .body_content_wrapper {
  overflow-x: hidden;
  width: 100%;
  max-width: 100%;
}

.od-page .container {
  width: 100%;
  max-width: 100%;
  padding-left: 15px;
  padding-right: 15px;
  box-sizing: border-box;
}

@media (min-width: 576px)  { .od-page .container { max-width: 540px; } }
@media (min-width: 768px)  { .od-page .container { max-width: 720px; } }
@media (min-width: 992px)  { .od-page .container { max-width: 960px; } }
@media (min-width: 1200px) { .od-page .container { max-width: 1140px; } }
@media (min-width: 1400px) { .od-page .container { max-width: 1320px; } }

/* ── Force all children to respect container width ───── */
.od-page *,
.od-page *::before,
.od-page *::after {
  box-sizing: border-box;
}

.od-layout,
.od-panel,
.od-panel-header,
.od-panel-body,
.od-info-grid,
.od-totals,
.od-timeline,
.od-address-body,
.od-notes-body,
.od-refund-notice,
.od-items-table,
.od-header-inner,
.od-header-actions {
  max-width: 100%;
  overflow-wrap: break-word;
  word-wrap: break-word;
}

/* ── Page header strip ───────────────────────────────── */
.od-page-header {
  background: #fff;
  border-bottom: 1px solid #f0ebe5;
  padding: 18px 0;
  margin-bottom: 28px;
  overflow: hidden;
}
.od-page-header .od-header-inner {
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 14px;
}
.od-back-link {
  display: inline-flex; align-items: center; gap: 8px;
  color: #714e32; font-size: 13.5px; font-weight: 600;
  text-decoration: none; transition: gap .2s, color .2s;
}
.od-back-link:hover { color: #5a3c24; gap: 12px; }
.od-back-link i { font-size: 13px; }

.od-header-title { text-align: center; min-width: 0; }
.od-header-title h1 {
  font-size: 20px; font-weight: 700; color: #1a1209; margin: 0 0 3px;
  overflow: hidden; text-overflow: ellipsis;
}
.od-header-title p { font-size: 12.5px; color: #7a6655; margin: 0; }

.od-header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

/* ── Layout ──────────────────────────────────────────── */
.od-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 22px;
  align-items: start;
  padding-bottom: 60px;
  min-width: 0;           /* prevent grid blowout */
}

/* ── Generic panel ───────────────────────────────────── */
.od-panel {
  background: #fff;
  border: 1px solid #f0ebe5;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 16px rgba(113,78,50,.05);
  margin-bottom: 20px;
  min-width: 0;           /* prevent panel blowout */
}
.od-panel-header {
  display: flex; align-items: center; justify-content: space-between;
  gap: 10px; padding: 16px 22px;
  border-bottom: 1px solid #f5f0eb;
  background: #fdf8f4;
  flex-wrap: wrap;
  min-width: 0;
}
.od-panel-title {
  display: flex; align-items: center; gap: 10px;
  min-width: 0;
}
.od-panel-icon {
  width: 32px; height: 32px; border-radius: 8px;
  background: linear-gradient(135deg, #f5ede5, #e8d5c4);
  display: flex; align-items: center; justify-content: center;
  color: #714e32; font-size: 13px; flex-shrink: 0;
}
.od-panel-title h3 { font-size: 15px; font-weight: 700; color: #1a1209; margin: 0; }
.od-panel-body { padding: 22px; min-width: 0; }
.od-panel-body.p0 { padding: 0; }

/* ── Status progress bar ─────────────────────────────── */
.od-progress-wrap {
  padding: 24px 28px 20px;
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
}

/* Hide scrollbar but allow scrolling */
.od-progress-wrap::-webkit-scrollbar { height: 3px; }
.od-progress-wrap::-webkit-scrollbar-track { background: transparent; }
.od-progress-wrap::-webkit-scrollbar-thumb { background: #e8ddd5; border-radius: 3px; }

.od-progress-steps {
  display: flex; align-items: flex-start; position: relative;
  min-width: 420px;
}
.od-progress-track {
  position: absolute;
  top: 18px; left: 0; right: 0; height: 2px;
  background: #f0ebe5; z-index: 0;
}
.od-progress-fill {
  position: absolute;
  top: 18px; left: 0; height: 2px;
  background: linear-gradient(90deg, #714e32, #c4956a);
  z-index: 1; transition: width .6s ease;
}
.od-step {
  flex: 1; display: flex; flex-direction: column;
  align-items: center; text-align: center;
  position: relative; z-index: 2;
}
.od-step-dot {
  width: 36px; height: 36px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  border: 2.5px solid #e8ddd5; background: #fff;
  color: #b9a99a; font-size: 14px; margin-bottom: 10px;
  transition: all .3s; flex-shrink: 0;
}
.od-step.done .od-step-dot {
  background: #714e32; border-color: #714e32; color: #fff;
  box-shadow: 0 0 0 4px rgba(113,78,50,.15);
}
.od-step.active .od-step-dot {
  background: #fff; border-color: #714e32; color: #714e32;
  box-shadow: 0 0 0 4px rgba(113,78,50,.15);
}
.od-step.cancelled .od-step-dot {
  background: #dc2626; border-color: #dc2626; color: #fff;
  box-shadow: 0 0 0 4px rgba(220,38,38,.15);
}
.od-step-label {
  font-size: 11.5px; font-weight: 700; color: #b9a99a;
  text-transform: uppercase; letter-spacing: .3px; line-height: 1.3;
}
.od-step.done .od-step-label,
.od-step.active .od-step-label { color: #714e32; }
.od-step.cancelled .od-step-label { color: #dc2626; }
.od-step-date {
  font-size: 10.5px; color: #9ca3af; margin-top: 3px; line-height: 1.3;
}

/* ── Info grid (order summary) ───────────────────────── */
.od-info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0;
  min-width: 0;
}
.od-info-cell {
  padding: 18px 22px;
  border-bottom: 1px solid #f5f0eb;
  border-right: 1px solid #f5f0eb;
  overflow: hidden;
  word-break: break-word;
  min-width: 0;
}
.od-info-cell:nth-child(even) { border-right: none; }
.od-info-cell:nth-last-child(-n+2) { border-bottom: none; }
.od-info-label {
  font-size: 10.5px; font-weight: 700; color: #c4956a;
  text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px;
}
.od-info-value { font-size: 14px; font-weight: 600; color: #1a1209; }

/* Status pills */
.od-pill {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 11px; border-radius: 20px;
  font-size: 11.5px; font-weight: 700;
  text-transform: uppercase; letter-spacing: .3px;
  white-space: nowrap;
}
.od-pill-pending    { background: #fff3cd; color: #92400e; }
.od-pill-processing { background: #dbeafe; color: #1e40af; }
.od-pill-shipped    { background: #e0f2fe; color: #0369a1; }
.od-pill-delivered  { background: #dcfce7; color: #15803d; }
.od-pill-cancelled  { background: #fef2f2; color: #dc2626; }
.od-pill-paid       { background: #dcfce7; color: #15803d; }
.od-pill-unpaid     { background: #fff3cd; color: #92400e; }
.od-pill-failed          { background: #fef2f2; color: #dc2626; }
.od-pill-refund_pending  { background: #fef9c3; color: #713f12; }
.od-pill-refunded        { background: #dcfce7; color: #15803d; }
.od-pill-refund_rejected { background: #fef2f2; color: #dc2626; }

/* ── Refund pending notice ───────────────────────────── */
.od-refund-notice {
  background: #fef9c3; border: 1px solid #fef08a; border-radius: 10px;
  padding: 14px 18px; font-size: 13.5px; color: #713f12;
  display: flex; align-items: flex-start; gap: 10px;
  word-break: break-word;
}
.od-refund-notice i { margin-top: 2px; flex-shrink: 0; color: #ca8a04; }

/* ── Items table ─────────────────────────────────────── */
/* ══════════════════════════════════════════════════════
   ITEMS TABLE — Desktop
══════════════════════════════════════════════════════ */
.od-items-desktop { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.od-items-mobile  { display: none; }

.od-items-table { width: 100%; border-collapse: collapse; }
.od-items-table thead th {
  padding: 12px 18px; background: #fdf8f4;
  font-size: 10.5px; font-weight: 700; color: #4a3728;
  text-transform: uppercase; letter-spacing: .4px;
  border-bottom: 1px solid #f0ebe5;
  white-space: nowrap;
}
.od-items-table tbody td {
  padding: 18px; border-bottom: 1px solid #f5f0eb;
  vertical-align: middle; color: #1a1209;
}
.od-items-table tbody tr:last-child td { border-bottom: none; }
.od-items-table tbody tr:hover td { background: #fdfaf7; }

.od-product-cell { display: flex; align-items: center; gap: 14px; min-width: 0; }
.od-product-img {
  width: 72px; height: 72px; border-radius: 10px; object-fit: cover;
  border: 1px solid #f0ebe5; flex-shrink: 0;
}
.od-product-info { min-width: 0; flex: 1; }
.od-product-name {
  font-size: 14px; font-weight: 700; color: #1a1209; margin-bottom: 3px;
  line-height: 1.35; word-break: break-word;
  overflow: hidden; text-overflow: ellipsis;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}
.od-product-meta {
  font-size: 12px; color: #9ca3af; margin-top: 2px;
  display: flex; align-items: center; gap: 4px;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.od-qty-badge {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 8px;
  background: #f5ede5; color: #714e32;
  font-size: 14px; font-weight: 700;
  margin: 0 auto;
}

/* ══════════════════════════════════════════════════════
   ITEMS — Mobile Card Layout
══════════════════════════════════════════════════════ */
.od-mitem {
  padding: 16px;
  border-bottom: 1px solid #f0ebe5;
}
.od-mitem:last-child { border-bottom: none; }

/* Top row: image + name */
.od-mitem-top {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  margin-bottom: 14px;
}

.od-mitem-img {
  width: 64px; height: 64px;
  border-radius: 10px;
  object-fit: cover;
  border: 1px solid #f0ebe5;
  flex-shrink: 0;
}

.od-mitem-info {
  flex: 1;
  min-width: 0;
}

.od-mitem-name {
  font-size: 13.5px;
  font-weight: 700;
  color: #1a1209;
  line-height: 1.4;
  margin-bottom: 4px;
  word-break: break-word;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.od-mitem-meta {
  font-size: 11.5px;
  color: #9ca3af;
  display: flex;
  align-items: center;
  gap: 4px;
  margin-top: 3px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.od-mitem-meta i {
  font-size: 10px;
  color: #c4956a;
  flex-shrink: 0;
}

/* Bottom row: price details in a clean grid */
.od-mitem-bottom {
  display: flex;
  align-items: stretch;
  background: #fdf8f4;
  border-radius: 10px;
  border: 1px solid #f5f0eb;
  overflow: hidden;
}

.od-mitem-detail {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 10px 8px;
  border-right: 1px solid #f0ebe5;
  min-width: 0;
}

.od-mitem-detail:last-child {
  border-right: none;
}

.od-mitem-label {
  font-size: 9.5px;
  font-weight: 700;
  color: #c4956a;
  text-transform: uppercase;
  letter-spacing: .4px;
  margin-bottom: 4px;
  white-space: nowrap;
}

.od-mitem-value {
  font-size: 13px;
  font-weight: 600;
  color: #1a1209;
  white-space: nowrap;
}

.od-mitem-qty {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px; height: 28px;
  background: #fff;
  border: 1.5px solid #e8ddd5;
  border-radius: 7px;
  font-size: 13px;
  font-weight: 700;
  color: #714e32;
}

.od-mitem-total-col {
  background: rgba(113, 78, 50, 0.04);
}

.od-mitem-total {
  font-size: 14px !important;
  font-weight: 700 !important;
  color: #714e32 !important;
}

.od-mitem-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  color: #9ca3af;
  text-align: center;
}
.od-mitem-empty i {
  font-size: 28px;
  margin-bottom: 10px;
  color: #d4c4b0;
}
.od-mitem-empty p {
  margin: 0;
  font-size: 13px;
}

/* ══════════════════════════════════════════════════════
   RESPONSIVE — Show/hide desktop vs mobile items
══════════════════════════════════════════════════════ */
@media (max-width: 767px) {
  .od-items-desktop { display: none !important; }
  .od-items-mobile  { display: block !important; }

  .od-totals { padding: 0 16px; }
  .od-totals-row { padding: 11px 0; font-size: 13px; }
  .od-totals-row:last-child .od-totals-label,
  .od-totals-row:last-child .od-totals-value { font-size: 15px; }
}

@media (max-width: 420px) {
  .od-mitem { padding: 14px; }

  .od-mitem-img {
    width: 52px; height: 52px;
    border-radius: 8px;
  }

  .od-mitem-top { gap: 10px; margin-bottom: 12px; }
  .od-mitem-name { font-size: 12.5px; }
  .od-mitem-meta { font-size: 11px; }

  .od-mitem-bottom { border-radius: 8px; }
  .od-mitem-detail { padding: 8px 6px; }
  .od-mitem-label { font-size: 9px; }
  .od-mitem-value { font-size: 12px; }
  .od-mitem-total { font-size: 13px !important; }

  .od-mitem-qty {
    width: 24px; height: 24px;
    font-size: 11px;
  }

  .od-totals { padding: 0 14px; }
  .od-totals-row { font-size: 12.5px; gap: 8px; }
  .od-totals-row:last-child .od-totals-label,
  .od-totals-row:last-child .od-totals-value { font-size: 14px; }
}

@media (max-width: 350px) {
  .od-mitem-img {
    width: 44px; height: 44px;
  }

  .od-mitem-name { font-size: 12px; }

  .od-mitem-detail { padding: 7px 4px; }
  .od-mitem-label { font-size: 8.5px; letter-spacing: .2px; }
  .od-mitem-value { font-size: 11.5px; }
  .od-mitem-total { font-size: 12.5px !important; }
}

/* ── Totals block ────────────────────────────────────── */
.od-totals { padding: 0 22px; }
.od-totals-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 13px 0; border-bottom: 1px solid #f5f0eb;
  font-size: 14px; gap: 12px;
}
.od-totals-row:last-child {
  border-bottom: none; padding-top: 16px; margin-top: 4px;
  border-top: 2px solid #f0ebe5;
}
.od-totals-label { color: #7a6655; white-space: nowrap; flex-shrink: 0; }
.od-totals-value { font-weight: 700; color: #1a1209; text-align: right; word-break: break-all; }
.od-totals-row:last-child .od-totals-label,
.od-totals-row:last-child .od-totals-value {
  font-size: 16px; color: #714e32;
}
.od-totals-discount { color: #15803d !important; }

/* ── Timeline (sidebar) ──────────────────────────────── */
.od-timeline { padding: 22px 22px 18px; }
.od-timeline-item {
  display: flex; gap: 14px; position: relative;
  padding-bottom: 22px;
}
.od-timeline-item:last-child { padding-bottom: 0; }
.od-timeline-left { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; }
.od-timeline-dot {
  width: 32px; height: 32px; border-radius: 50%;
  background: #f0ebe5; border: 2px solid #e8ddd5;
  display: flex; align-items: center; justify-content: center;
  color: #b9a99a; font-size: 12px; flex-shrink: 0; z-index: 1;
}
.od-timeline-item.done .od-timeline-dot {
  background: #714e32; border-color: #714e32; color: #fff;
}
.od-timeline-item.active .od-timeline-dot {
  background: #fff; border-color: #714e32; color: #714e32;
  box-shadow: 0 0 0 3px rgba(113,78,50,.15);
}
.od-timeline-item.cancelled-item .od-timeline-dot {
  background: #dc2626; border-color: #dc2626; color: #fff;
}
.od-timeline-line {
  width: 2px; flex: 1; background: #f0ebe5;
  margin: 4px 0; min-height: 20px;
}
.od-timeline-item.done .od-timeline-line { background: #c4956a; }
.od-timeline-body { padding-top: 4px; flex: 1; min-width: 0; word-break: break-word; }
.od-timeline-status { font-size: 13.5px; font-weight: 700; color: #1a1209; margin-bottom: 2px; }
.od-timeline-item.inactive .od-timeline-status { color: #b9a99a; }
.od-timeline-desc { font-size: 12.5px; color: #7a6655; margin-bottom: 3px; }
.od-timeline-date { font-size: 11.5px; color: #9ca3af; }

/* ── Shipping address card ───────────────────────────── */
.od-address-body { padding: 18px 22px; }
.od-address-name { font-size: 14px; font-weight: 700; color: #1a1209; margin-bottom: 8px; word-break: break-word; }
.od-address-row {
  display: flex; align-items: flex-start; gap: 9px;
  font-size: 13px; color: #4a3728; margin-bottom: 6px; line-height: 1.5;
  word-break: break-word;
}
.od-address-row i { color: #c4956a; font-size: 12px; margin-top: 2px; flex-shrink: 0; }
.od-address-row:last-child { margin-bottom: 0; }
.od-address-divider { height: 1px; background: #f5f0eb; margin: 12px 0; }

/* ── Notes card ──────────────────────────────────────── */
.od-notes-body {
  padding: 16px 22px;
  font-size: 13.5px; color: #4a3728; line-height: 1.6;
  word-break: break-word;
}

/* ── Buttons ─────────────────────────────────────────── */
.od-btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 20px; border-radius: 9px;
  font-size: 13.5px; font-weight: 600; font-family: inherit;
  cursor: pointer; border: none; transition: all .18s;
  text-decoration: none;
}
.od-btn-secondary {
  background: #f5f0eb; color: #4a3728; border: 1.5px solid #e8ddd5;
}
.od-btn-secondary:hover { background: #ede6dd; color: #1a1209; }
.od-btn-print {
  background: #fff; color: #4a3728; border: 1.5px solid #e8ddd5;
}
.od-btn-print:hover { background: #f5f0eb; }
.od-btn-cancel {
  background: #fef2f2; color: #dc2626; border: 1.5px solid #fecaca;
}
.od-btn-cancel:hover { background: #dc2626; color: #fff; }

/* ── Sidebar quick-action stack ──────────────────────── */
.od-sidebar-actions {
  display: flex; flex-direction: column; gap: 10px;
}
.od-sidebar-actions .od-btn {
  justify-content: center;
  width: 100%;
  text-align: center;
  white-space: normal;
  word-break: break-word;
}

/* ══════════════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════════════ */

@media (max-width: 991px) {
  .od-layout {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  /* Grid children must not overflow */
  .od-layout > div {
    min-width: 0;
    max-width: 100%;
  }
}

@media (max-width: 767px) {

  .od-page-header {
    padding: 14px 0;
    margin-bottom: 16px;
  }

  .od-page-header .od-header-inner {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }

  .od-back-link { font-size: 13px; }

  .od-header-title {
    display: block !important;
    text-align: left;
  }
  .od-header-title h1 { font-size: 17px; }
  .od-header-title p { font-size: 11.5px; }

  .od-header-actions {
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }
  .od-header-actions .od-btn {
    justify-content: center;
    padding: 10px 12px;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  /* If only 1 button, span full width */
  .od-header-actions .od-btn:only-child {
    grid-column: 1 / -1;
  }

  /* Panels */
  .od-panel { border-radius: 10px; margin-bottom: 14px; }
  .od-panel-header { padding: 14px 16px; gap: 8px; }
  .od-panel-icon { width: 28px; height: 28px; font-size: 12px; }
  .od-panel-title h3 { font-size: 14px; }
  .od-panel-body { padding: 16px; }

  /* Progress tracker */
  .od-progress-wrap { padding: 16px 14px 12px; }
  .od-progress-steps { min-width: 400px; }
  .od-step-dot { width: 30px; height: 30px; font-size: 12px; margin-bottom: 8px; }
  .od-step-label { font-size: 10px; }
  .od-step-date { font-size: 9.5px; }

  /* Info grid — single column */
  .od-info-grid { grid-template-columns: 1fr; }
  .od-info-cell {
    padding: 14px 16px;
    border-right: none !important;
  }
  .od-info-cell:nth-last-child(-n+2) {
    border-bottom: 1px solid #f5f0eb;
  }
  .od-info-cell:last-child { border-bottom: none; }
  .od-info-value { font-size: 13.5px; }


  .od-product-cell { gap: 12px; }
  .od-product-img { width: 56px; height: 56px; border-radius: 8px; }
  .od-product-name { font-size: 13px; -webkit-line-clamp: 2; }

  /* Qty / Price / Total — inline row below product */
  .od-item-detail-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    padding-top: 8px;
    border-top: 1px dashed #f0ebe5;
    margin-top: 4px;
  }



  /* Totals */
  .od-totals { padding: 0 16px; }
  .od-totals-row { padding: 11px 0; font-size: 13px; }
  .od-totals-row:last-child .od-totals-label,
  .od-totals-row:last-child .od-totals-value { font-size: 15px; }

  /* Timeline */
  .od-timeline { padding: 18px 16px 14px; }
  .od-timeline-item { gap: 12px; padding-bottom: 18px; }
  .od-timeline-dot { width: 28px; height: 28px; font-size: 11px; }
  .od-timeline-status { font-size: 13px; }
  .od-timeline-desc { font-size: 12px; }
  .od-timeline-date { font-size: 11px; }

  /* Address */
  .od-address-body { padding: 16px; }
  .od-address-name { font-size: 13.5px; }
  .od-address-row { font-size: 12.5px; gap: 8px; }

  /* Notes */
  .od-notes-body { padding: 14px 16px; font-size: 13px; }

  /* Refund notice */
  .od-refund-notice {
    padding: 12px 14px; font-size: 12.5px; border-radius: 8px;
  }

  /* Sidebar actions */
  .od-sidebar-actions { gap: 8px; }
  .od-sidebar-actions .od-btn {
    padding: 11px 16px; font-size: 13px;
  }
}

/* ── Small phones (≤420px) ─────────────────────────── */
@media (max-width: 420px) {

  .od-page .container {
    padding-left: 12px;
    padding-right: 12px;
  }

  .od-header-actions {
    grid-template-columns: 1fr;
  }
  .od-header-actions .od-btn {
    font-size: 12px;
    padding: 10px;
  }

  .od-panel { border-radius: 8px; margin-bottom: 12px; }
  .od-panel-header { padding: 12px 14px; }
  .od-panel-icon { width: 26px; height: 26px; border-radius: 6px; font-size: 11px; }
  .od-panel-title h3 { font-size: 13px; }
  .od-panel-body { padding: 14px; }

  .od-pill { font-size: 10px; padding: 3px 8px; }

  /* Progress */
  .od-progress-wrap { padding: 12px 10px 8px; }
  .od-step-dot { width: 26px; height: 26px; font-size: 10px; }
  .od-step-label { font-size: 9px; }

  /* Info cells */
  .od-info-cell { padding: 12px 14px; }
  .od-info-label { font-size: 10px; }
  .od-info-value { font-size: 13px; }

  /* Product card */
  .od-product-img { width: 48px; height: 48px; }
  .od-product-name { font-size: 12px; }
  .od-product-meta { font-size: 11px; }
  .od-items-table tbody tr { padding: 12px 14px; }

  /* Totals */
  .od-totals { padding: 0 14px; }
  .od-totals-row { font-size: 12.5px; gap: 8px; }
  .od-totals-row:last-child .od-totals-label,
  .od-totals-row:last-child .od-totals-value { font-size: 14px; }

  /* Timeline */
  .od-timeline { padding: 14px 14px 12px; }
  .od-timeline-dot { width: 26px; height: 26px; font-size: 10px; }
  .od-timeline-status { font-size: 12.5px; }
  .od-timeline-desc { font-size: 11.5px; }

  /* Address */
  .od-address-body { padding: 14px; }
  .od-address-name { font-size: 13px; }
  .od-address-row { font-size: 12px; }

  /* Sidebar buttons */
  .od-sidebar-actions .od-btn {
    padding: 10px 12px; font-size: 12px; border-radius: 8px;
  }
}

/* ── Print ───────────────────────────────────────────── */
@media print {
  .od-page-header .od-header-actions,
  .od-back-link { display: none !important; }
  .od-page { background: #fff !important; }
  .od-panel { box-shadow: none !important; border: 1px solid #ddd !important; }
  .od-layout { grid-template-columns: 1fr !important; }
}
</style>

<div class="od-page">

  @include('partials.header')
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])

  <div class="body_content_wrapper">

    {{-- ── Page header strip ── --}}
    <div class="od-page-header">
      <div class="container">
        <div class="od-header-inner">
          <a href="{{ route('account.index') }}#orders" class="od-back-link">
            <i class="fas fa-arrow-left"></i> Back to Orders
          </a>
          <div class="od-header-title">
            <h1>Order #{{ $order->order_number }}</h1>
            <p>Placed {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
          </div>
          <div class="od-header-actions">
            <button class="od-btn od-btn-print" onclick="window.print()">
              <i class="fas fa-print"></i> Print
            </button>
            @if(!$order->cancelled_at && $order->status != 'shipped' && $order->status != 'delivered' && $order->payment_status !== 'refund_pending')
            <button class="od-btn od-btn-cancel" onclick="cancelOrder({{ $order->id }}, {{ $order->isPaid() ? 'true' : 'false' }})">
              <i class="fas fa-times"></i>
              @if($order->isPaid()) Request Cancellation @else Cancel Order @endif
            </button>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="od-layout">

        {{-- ══════════════════════════════
             LEFT COLUMN
        ══════════════════════════════ --}}
        <div>

          {{-- ── Refund state notices ── --}}
          @if($order->payment_status === 'refund_pending')
          <div class="od-refund-notice" style="margin-bottom:20px;">
            <i class="fas fa-clock"></i>
            <div>
              <strong>Refund Under Review</strong><br>
              Your cancellation request has been received. Our team will review and process
              your refund within <strong>3–5 business days</strong>.
            </div>
          </div>
          @elseif($order->payment_status === 'refunded')
          <div class="od-refund-notice" style="background:#dcfce7;border-color:#86efac;color:#15803d;margin-bottom:20px;">
            <i class="fas fa-check-circle" style="color:#15803d;"></i>
            <div>
              <strong>Refund Processed</strong><br>
              Your refund of <strong>₦{{ number_format($order->refund_amount ?? $order->total, 2) }}</strong>
              has been processed. Please allow 5–10 business days for it to appear in your account.
            </div>
          </div>
          @elseif($order->payment_status === 'refund_rejected')
          <div class="od-refund-notice" style="background:#fef2f2;border-color:#fecaca;color:#dc2626;margin-bottom:20px;">
            <i class="fas fa-times-circle" style="color:#dc2626;"></i>
            <div>
              <strong>Refund Request Declined</strong><br>
              Unfortunately your refund request was not approved. Please contact our support
              team for more information.
            </div>
          </div>
          @endif

          {{-- ── Progress tracker ── --}}
          <div class="od-panel">
            <div class="od-panel-header">
              <div class="od-panel-title">
                <div class="od-panel-icon"><i class="fas fa-route"></i></div>
                <h3>Order Status</h3>
              </div>
              <span class="od-pill od-pill-{{ $order->status }}">
                {{ ucfirst($order->status) }}
              </span>
            </div>

            @php
              $steps = [
                ['key' => 'placed',     'label' => 'Placed',     'icon' => 'fa-shopping-bag', 'date' => $order->created_at],
                ['key' => 'paid',       'label' => 'Paid',       'icon' => 'fa-credit-card',  'date' => $order->paid_at],
                ['key' => 'processing', 'label' => 'Processing', 'icon' => 'fa-cog',           'date' => null],
                ['key' => 'shipped',    'label' => 'Shipped',    'icon' => 'fa-truck',         'date' => $order->shipped_at],
                ['key' => 'delivered',  'label' => 'Delivered',  'icon' => 'fa-check-circle',  'date' => $order->delivered_at],
              ];

              $orderFlow  = ['placed' => 0, 'paid' => 1, 'processing' => 2, 'processing' => 2, 'shipped' => 3, 'delivered' => 4];
              $currentIdx = $orderFlow[$order->status] ?? 0;
              $isCancelled = !!$order->cancelled_at;
              $fillPct = $isCancelled ? 0 : ($currentIdx / (count($steps) - 1)) * 100;
            @endphp

            <div class="od-progress-wrap">
              <div class="od-progress-steps">
                <div class="od-progress-track"></div>
                <div class="od-progress-fill" style="width:{{ $fillPct }}%;"></div>

                @foreach($steps as $i => $step)
                  @php
                    if ($isCancelled) {
                      $cls = 'inactive';
                    } elseif ($i < $currentIdx) {
                      $cls = 'done';
                    } elseif ($i === $currentIdx) {
                      $cls = 'active';
                    } else {
                      $cls = 'inactive';
                    }
                  @endphp
                  <div class="od-step {{ $cls }}">
                    <div class="od-step-dot">
                      <i class="fas {{ $step['icon'] }}"></i>
                    </div>
                    <div class="od-step-label">{{ $step['label'] }}</div>
                    @if($step['date'])
                    <div class="od-step-date">{{ $step['date']->format('d M') }}</div>
                    @endif
                  </div>
                @endforeach

                @if($isCancelled)
                <div class="od-step cancelled">
                  <div class="od-step-dot"><i class="fas fa-ban"></i></div>
                  <div class="od-step-label">Cancelled</div>
                  <div class="od-step-date">{{ $order->cancelled_at->format('d M') }}</div>
                </div>
                @endif
              </div>
            </div>
          </div>

          {{-- ── Order summary info grid ── --}}
          <div class="od-panel">
            <div class="od-panel-header">
              <div class="od-panel-title">
                <div class="od-panel-icon"><i class="fas fa-receipt"></i></div>
                <h3>Order Summary</h3>
              </div>
            </div>
            <div class="od-info-grid">
              <div class="od-info-cell">
                <div class="od-info-label">Order Status</div>
                <div class="od-info-value">
                  <span class="od-pill od-pill-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                </div>
              </div>
              <div class="od-info-cell">
                <div class="od-info-label">Payment Status</div>
                <div class="od-info-value">
                  @php
                    $paymentLabel = match($order->payment_status) {
                      'refund_pending'  => 'Refund Pending',
                      'refunded'        => 'Refunded',
                      'refund_rejected' => 'Refund Declined',
                      default           => ucfirst($order->payment_status),
                    };
                  @endphp
                  <span class="od-pill od-pill-{{ $order->payment_status }}">{{ $paymentLabel }}</span>
                </div>
              </div>
              <div class="od-info-cell">
                <div class="od-info-label">Payment Method</div>
                <div class="od-info-value">{{ ucfirst($order->payment_method ?? 'N/A') }}</div>
              </div>
              <div class="od-info-cell">
                <div class="od-info-label">Order Total</div>
                <div class="od-info-value" style="color:#714e32;font-size:16px;">
                  ₦{{ number_format($order->total, 2) }}
                </div>
              </div>
            </div>
          </div>

          {{-- ── Order items table ── --}}
          {{-- ── Order items table ── --}}
<div class="od-panel">
  <div class="od-panel-header">
    <div class="od-panel-title">
      <div class="od-panel-icon"><i class="fas fa-box-open"></i></div>
      <h3>Items Ordered</h3>
    </div>
    <span style="font-size:12px;color:#9ca3af;font-weight:600;">
      {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
    </span>
  </div>
  <div class="od-panel-body p0">

    {{-- ═══ Desktop table ═══ --}}
    <div class="od-items-desktop">
      <table class="od-items-table">
        <thead>
          <tr>
            <th>Product</th>
            <th style="text-align:center;">Qty</th>
            <th style="text-align:right;">Unit Price</th>
            <th style="text-align:right;">Total</th>
          </tr>
        </thead>
        <tbody>
          @forelse($order->items as $item)
          <tr>
            <td>
              <div class="od-product-cell">
                @if($item->product && $item->product->main_image)
                  <img src="{{ asset('public/storage/' . $item->product->main_image) }}"
                       alt="{{ $item->product_name }}" class="od-product-img">
                @else
                  <img src="{{ asset('images/placeholder.png') }}"
                       alt="{{ $item->product_name }}" class="od-product-img">
                @endif
                <div class="od-product-info">
                  <div class="od-product-name">{{ $item->product_name }}</div>
                  @if($item->product_sku)
                  <div class="od-product-meta">
                    <i class="fas fa-barcode"></i> SKU: {{ $item->product_sku }}
                  </div>
                  @endif
                  @if($item->seller)
                  <div class="od-product-meta">
                    <i class="fas fa-store"></i> {{ $item->seller->name }}
                  </div>
                  @endif
                </div>
              </div>
            </td>
            <td style="text-align:center;">
              <div class="od-qty-badge">{{ $item->quantity }}</div>
            </td>
            <td style="text-align:right; font-size:14px; color:#4a3728;">
              ₦{{ number_format($item->price, 2) }}
            </td>
            <td style="text-align:right; font-size:14px; font-weight:700; color:#1a1209;">
              ₦{{ number_format($item->total_price, 2) }}
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" style="text-align:center;padding:40px;color:#9ca3af;">
              No items found
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ═══ Mobile cards ═══ --}}
    <div class="od-items-mobile">
      @forelse($order->items as $item)
      <div class="od-mitem">
        <div class="od-mitem-top">
          @if($item->product && $item->product->main_image)
            <img src="{{ asset('public/storage/' . $item->product->main_image) }}"
                 alt="{{ $item->product_name }}" class="od-mitem-img">
          @else
            <img src="{{ asset('images/placeholder.png') }}"
                 alt="{{ $item->product_name }}" class="od-mitem-img">
          @endif
          <div class="od-mitem-info">
            <div class="od-mitem-name">{{ $item->product_name }}</div>
            @if($item->product_sku)
            <div class="od-mitem-meta">
              <i class="fas fa-barcode"></i> {{ $item->product_sku }}
            </div>
            @endif
            @if($item->seller)
            <div class="od-mitem-meta">
              <i class="fas fa-store"></i> {{ $item->seller->name }}
            </div>
            @endif
          </div>
        </div>
        <div class="od-mitem-bottom">
          <div class="od-mitem-detail">
            <span class="od-mitem-label">Unit Price</span>
            <span class="od-mitem-value">₦{{ number_format($item->price, 2) }}</span>
          </div>
          <div class="od-mitem-detail">
            <span class="od-mitem-label">Qty</span>
            <span class="od-mitem-value">
              <span class="od-mitem-qty">{{ $item->quantity }}</span>
            </span>
          </div>
          <div class="od-mitem-detail od-mitem-total-col">
            <span class="od-mitem-label">Total</span>
            <span class="od-mitem-value od-mitem-total">₦{{ number_format($item->total_price, 2) }}</span>
          </div>
        </div>
      </div>
      @empty
      <div class="od-mitem-empty">
        <i class="fas fa-box-open"></i>
        <p>No items found</p>
      </div>
      @endforelse
    </div>

    {{-- Totals --}}
    <div class="od-totals" style="padding-top:4px;">
      <div class="od-totals-row">
        <span class="od-totals-label">Subtotal</span>
        <span class="od-totals-value">₦{{ number_format($order->subtotal, 2) }}</span>
      </div>
      @if($order->shipping_fee > 0)
      <div class="od-totals-row">
        <span class="od-totals-label"><i class="fas fa-truck" style="font-size:12px;color:#c4956a;margin-right:5px;"></i>Shipping Fee</span>
        <span class="od-totals-value">₦{{ number_format($order->shipping_fee, 2) }}</span>
      </div>
      @endif
      @if($order->tax > 0)
      <div class="od-totals-row">
        <span class="od-totals-label">Tax</span>
        <span class="od-totals-value">₦{{ number_format($order->tax, 2) }}</span>
      </div>
      @endif
      @if($order->discount > 0)
      <div class="od-totals-row">
        <span class="od-totals-label"><i class="fas fa-tag" style="font-size:12px;color:#15803d;margin-right:5px;"></i>Discount</span>
        <span class="od-totals-value od-totals-discount">−₦{{ number_format($order->discount, 2) }}</span>
      </div>
      @endif
      <div class="od-totals-row">
        <span class="od-totals-label">Order Total</span>
        <span class="od-totals-value">₦{{ number_format($order->total, 2) }}</span>
      </div>
    </div>
    <div style="height:20px;"></div>
  </div>
</div>

        </div>{{-- /left column --}}

        {{-- ══════════════════════════════
             RIGHT COLUMN (sidebar)
        ══════════════════════════════ --}}
        <div>

          {{-- ── Shipping Address ── --}}
          <div class="od-panel">
            <div class="od-panel-header">
              <div class="od-panel-title">
                <div class="od-panel-icon"><i class="fas fa-map-marker-alt"></i></div>
                <h3>Shipping Address</h3>
              </div>
            </div>
            <div class="od-address-body">
              <div class="od-address-name">{{ $order->customer_name }}</div>
              @if($order->customer_phone)
              <div class="od-address-row">
                <i class="fas fa-phone"></i>
                <span>{{ $order->customer_phone }}</span>
              </div>
              @endif
              @if($order->customer_email)
              <div class="od-address-row">
                <i class="fas fa-envelope"></i>
                <span>{{ $order->customer_email }}</span>
              </div>
              @endif
              <div class="od-address-divider"></div>
              <div class="od-address-row">
                <i class="fas fa-home"></i>
                <span>
                  {{ $order->shipping_address }}<br>
                  {{ $order->shipping_city }}@if($order->shipping_state), {{ $order->shipping_state }}@endif
                  {{ $order->shipping_postal_code }}<br>
                  {{ $order->shipping_country }}
                </span>
              </div>
            </div>
          </div>

          {{-- ── Order Timeline ── --}}
          <div class="od-panel">
            <div class="od-panel-header">
              <div class="od-panel-title">
                <div class="od-panel-icon"><i class="fas fa-history"></i></div>
                <h3>Order Timeline</h3>
              </div>
            </div>
            <div class="od-timeline">

              @php
                $timelineSteps = [
                  [
                    'icon'    => 'fa-shopping-bag',
                    'status'  => 'Order Placed',
                    'desc'    => 'Your order has been received',
                    'date'    => $order->created_at,
                    'active'  => !!$order->created_at,
                  ],
                  [
                    'icon'    => 'fa-credit-card',
                    'status'  => $order->isPaid() ? 'Payment Confirmed' : 'Payment Pending',
                    'desc'    => $order->isPaid() ? 'Payment has been received' : 'Waiting for payment',
                    'date'    => $order->paid_at,
                    'active'  => $order->isPaid(),
                  ],
                  [
                    'icon'    => 'fa-cog',
                    'status'  => 'Processing',
                    'desc'    => 'Your order is being prepared',
                    'date'    => null,
                    'active'  => in_array($order->status, ['processing', 'shipped', 'delivered']),
                  ],
                  [
                    'icon'    => 'fa-truck',
                    'status'  => 'Shipped',
                    'desc'    => 'Your order is on its way',
                    'date'    => $order->shipped_at,
                    'active'  => !!$order->shipped_at,
                  ],
                  [
                    'icon'    => 'fa-check-circle',
                    'status'  => 'Delivered',
                    'desc'    => 'Order has been delivered',
                    'date'    => $order->delivered_at,
                    'active'  => !!$order->delivered_at,
                  ],
                ];
              @endphp

              @foreach($timelineSteps as $i => $ts)
               @php
                  $firstActiveIndex = array_search(true, array_column($timelineSteps, 'active'), true);
                
                  $isLast = $i === count($timelineSteps) - 1 && !$order->cancelled_at;
                  $isDone = $ts['active'] && $firstActiveIndex !== false && $i === $firstActiveIndex;
                  $cls    = $ts['active'] ? 'done' : 'inactive';
                @endphp
                <div class="od-timeline-item {{ $cls }}">
                  <div class="od-timeline-left">
                    <div class="od-timeline-dot"><i class="fas {{ $ts['icon'] }}"></i></div>
                    @if(!$isLast)<div class="od-timeline-line"></div>@endif
                  </div>
                  <div class="od-timeline-body">
                    <div class="od-timeline-status">{{ $ts['status'] }}</div>
                    <div class="od-timeline-desc">{{ $ts['desc'] }}</div>
                    @if($ts['date'])
                    <div class="od-timeline-date">{{ $ts['date']->format('M d, Y · h:i A') }}</div>
                    @endif
                  </div>
                </div>
              @endforeach

            @if($order->cancelled_at)
              @if(in_array($order->payment_status, ['refund_pending', 'refunded', 'refund_rejected']))
              {{-- Cancelled + refund --}}
              @endif
            @endif

              @if($order->cancelled_at)
              <div class="od-timeline-item cancelled-item">
                <div class="od-timeline-left">
                  <div class="od-timeline-dot"><i class="fas fa-ban"></i></div>
                  @if(in_array($order->payment_status, ['refund_pending','refunded','refund_rejected']))
                  <div class="od-timeline-line"></div>
                  @endif
                </div>
                <div class="od-timeline-body">
                  <div class="od-timeline-status" style="color:#dc2626;">Cancelled</div>
                  <div class="od-timeline-desc">Order was cancelled</div>
                  <div class="od-timeline-date">{{ $order->cancelled_at->format('M d, Y · h:i A') }}</div>
                </div>
              </div>
              @endif

              {{-- Refund states in the timeline --}}
              @if($order->payment_status === 'refund_pending')
              <div class="od-timeline-item active">
                <div class="od-timeline-left">
                  <div class="od-timeline-dot" style="border-color:#ca8a04;color:#ca8a04;">
                    <i class="fas fa-clock"></i>
                  </div>
                </div>
                <div class="od-timeline-body">
                  <div class="od-timeline-status">Refund Pending</div>
                  <div class="od-timeline-desc">Under admin review (3–5 business days)</div>
                </div>
              </div>
              @elseif($order->payment_status === 'refunded')
              <div class="od-timeline-item done">
                <div class="od-timeline-left">
                  <div class="od-timeline-dot"><i class="fas fa-rotate-left"></i></div>
                </div>
                <div class="od-timeline-body">
                  <div class="od-timeline-status">Refund Processed</div>
                  <div class="od-timeline-desc">
                    ₦{{ number_format($order->refund_amount ?? $order->total, 2) }} returned
                  </div>
                  @if(isset($order->refunded_at))
                  <div class="od-timeline-date">{{ $order->refunded_at->format('M d, Y · h:i A') }}</div>
                  @endif
                </div>
              </div>
              @elseif($order->payment_status === 'refund_rejected')
              <div class="od-timeline-item cancelled-item">
                <div class="od-timeline-left">
                  <div class="od-timeline-dot"><i class="fas fa-times"></i></div>
                </div>
                <div class="od-timeline-body">
                  <div class="od-timeline-status" style="color:#dc2626;">Refund Declined</div>
                  <div class="od-timeline-desc">Please contact support for details</div>
                </div>
              </div>
              @endif

            </div>
          </div>

          {{-- ── Order Notes ── --}}
          @if($order->notes)
          <div class="od-panel">
            <div class="od-panel-header">
              <div class="od-panel-title">
                <div class="od-panel-icon"><i class="fas fa-sticky-note"></i></div>
                <h3>Order Notes</h3>
              </div>
            </div>
            <div class="od-notes-body">{{ $order->notes }}</div>
          </div>
          @endif

          {{-- ── Quick action buttons ── --}}
          <div style="display:flex;flex-direction:column;gap:10px;">
            <a href="{{ route('account.index') }}#orders" class="od-btn od-btn-secondary" style="justify-content:center;">
              <i class="fas fa-arrow-left"></i> Back to My Orders
            </a>
            <button class="od-btn od-btn-print" style="justify-content:center;" onclick="window.print()">
              <i class="fas fa-print"></i> Print Order Receipt
            </button>
            @if(!$order->cancelled_at && $order->status != 'shipped' && $order->status != 'delivered')
            <button class="od-btn od-btn-cancel" style="justify-content:center;" onclick="cancelOrder({{ $order->id }}, {{ $order->isPaid() ? 'true' : 'false' }})">
              <i class="fas fa-times"></i>
              @if($order->isPaid()) Request Cancellation & Refund @else Cancel This Order @endif
            </button>
            @endif
          </div>

        </div>{{-- /right column --}}
      </div>{{-- /od-layout --}}
    </div>{{-- /container --}}

  </div>

  @include('partials.footer')
  <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
</div>

@push('scripts')
<script>
/**
 * @param {number}  orderId
 * @param {boolean} isPaid  true when the order has already been paid
 */
async function cancelOrder(orderId, isPaid = false) {
  const confirmMsg = isPaid
    ? 'Are you sure you want to cancel this order? Since it has been paid, your refund request will be reviewed and processed within 3–5 business days.'
    : 'Are you sure you want to cancel this order? This action cannot be undone.';

  if (!confirm(confirmMsg)) return;

  const btn = document.querySelector('.od-btn-cancel');
  if (btn) {
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing…';
    btn.disabled = true;
  }

  try {
    const response = await fetch(`/account/api/orders/${orderId}/cancel`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept':       'application/json',
      },
    });

    const data = await response.json();

    if (data.success) {
      showOdToast(data.message, 'success');
      // Reload so the refund_pending notice renders
      setTimeout(() => location.reload(), 1800);
    } else {
      showOdToast('Error: ' + (data.message || 'Unable to cancel order'), 'error');
      if (btn) {
        btn.innerHTML = '<i class="fas fa-times"></i> Cancel This Order';
        btn.disabled = false;
      }
    }
  } catch (error) {
    showOdToast('An error occurred while cancelling your order', 'error');
    if (btn) {
      btn.innerHTML = '<i class="fas fa-times"></i> Cancel This Order';
      btn.disabled = false;
    }
  }
}

function showOdToast(message, type = 'success') {
  const existing = document.getElementById('od-toast');
  if (existing) existing.remove();

  const bg    = type === 'success' ? '#dcfce7' : '#fef2f2';
  const color = type === 'success' ? '#15803d' : '#dc2626';
  const icon  = type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark';

  const toast = document.createElement('div');
  toast.id = 'od-toast';
  toast.style.cssText = [
    'position:fixed', 'bottom:24px', 'right:24px', 'z-index:9999',
    `background:${bg}`, `color:${color}`,
    `border:1px solid ${color}30`, 'border-radius:10px',
    'padding:14px 18px', 'display:flex', 'align-items:center', 'gap:10px',
    'font-size:14px', 'font-weight:600',
    'box-shadow:0 8px 24px rgba(0,0,0,.12)',
    'animation:odToastIn .3s ease', 'max-width:360px',
  ].join(';');
  toast.innerHTML = `<i class="fas ${icon}" style="font-size:16px;"></i>${message}`;
  document.body.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity .3s';
    setTimeout(() => toast.remove(), 300);
  }, 4500);
}

const _s = document.createElement('style');
_s.textContent = '@keyframes odToastIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}';
document.head.appendChild(_s);

// Print: hide action buttons
window.addEventListener('beforeprint', () => {
  document.querySelectorAll('.od-btn-cancel, .od-btn-print, .od-btn-secondary')
          .forEach(b => b.style.display = 'none');
});
window.addEventListener('afterprint', () => {
  document.querySelectorAll('.od-btn-cancel, .od-btn-print, .od-btn-secondary')
          .forEach(b => b.style.display = '');
});
</script>
@endpush

@endsection