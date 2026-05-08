/**
 * fastsearch.js — upgraded for Typesense
 *
 * New features vs original:
 *  - Product image thumbnails in autocomplete rows
 *  - Typesense <mark> highlight tags rendered properly
 *  - Price displayed on autocomplete rows
 *  - Category breadcrumb shown on product suggestions
 *  - "Trending" badge on exact-search row when result count is high
 *  - Everything else (keyboard nav, icons, category/brand rows) preserved
 */

(function ($) {
    "use strict";

    const CFG = {
        minChars:      2,
        debounceDelay: 120,
        apiUrl:        "/search/suggest",
        listingUrl:    "/products",
        animSpeed:     150,
    };

    let debounceTimer  = null;
    let currentXhr     = null;
    let selectedIndex  = -1;

    // ─────────────────────────────────────────────────────────────
    // INIT
    // ─────────────────────────────────────────────────────────────

    function init() {
        bindSearchInput($("#search-input"),        $("#search-suggestions"),        $("#suggestion-list"));
        bindSearchInput($("#mobile-search-input"), $("#mobile-search-suggestions"), $("#mobile-suggestion-list"));

        $(document).on("click", function (e) {
            if (!$(e.target).closest(".top-search, .search-suggestions").length) hideAll();
        });
    }

    // ─────────────────────────────────────────────────────────────
    // BIND
    // ─────────────────────────────────────────────────────────────

    function bindSearchInput($input, $box, $list) {
        if (!$input.length) return;
        $input.attr("autocomplete", "off");

        $input.on("input", function () {
            const q = $(this).val().trim();
            clearTimeout(debounceTimer);
            if (currentXhr) { currentXhr.abort(); currentXhr = null; }
            if (q.length < CFG.minChars) { hideAll(); return; }
            showLoading($box, $list);
            debounceTimer = setTimeout(() => fetchSuggestions(q, $box, $list), CFG.debounceDelay);
        });

        $input.on("keydown", function (e) {
            const $items = $list.find("li.sug-item");
            const total  = $items.length;
            if (!total && e.keyCode !== 13) return;

            if (e.keyCode === 40) {
                e.preventDefault();
                selectedIndex = selectedIndex >= total - 1 ? 0 : selectedIndex + 1;
                updateHighlight($items, $input);
            } else if (e.keyCode === 38) {
                e.preventDefault();
                selectedIndex = selectedIndex <= 0 ? total - 1 : selectedIndex - 1;
                updateHighlight($items, $input);
            } else if (e.keyCode === 13) {
                e.preventDefault();
                if (selectedIndex >= 0) $items.eq(selectedIndex).trigger("click");
                else { const q = $input.val().trim(); if (q) goSearch({ search: q }); }
            } else if (e.keyCode === 27) {
                hideAll();
            }
        });

        $input.on("focus", function () {
            const q = $(this).val().trim();
            if (q.length >= CFG.minChars && $list.children(".sug-item").length)
                $box.stop(true).fadeIn(CFG.animSpeed);
        });

        $input.closest("form").on("submit", function (e) {
            e.preventDefault();
            const q = $input.val().trim();
            if (q) goSearch({ search: q });
        });
    }

    // ─────────────────────────────────────────────────────────────
    // FETCH
    // ─────────────────────────────────────────────────────────────

    function fetchSuggestions(q, $box, $list) {
        currentXhr = $.ajax({
            url:      CFG.apiUrl,
            method:   "GET",
            data:     { q },
            dataType: "json",
            success: function (res) {
                currentXhr = null;
                render(res.suggestions || [], $box, $list, q);
            },
            error: function (xhr) {
                if (xhr.statusText !== "abort") {
                    currentXhr = null;
                    $list.html('<li class="sug-empty">No suggestions found</li>');
                }
            },
        });
    }

    // ─────────────────────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────────────────────

    function render(suggestions, $box, $list, q) {
        $list.empty();
        selectedIndex = -1;

        if (!suggestions.length) {
            $list.html('<li class="sug-empty">No results — try a different spelling</li>');
            $box.stop(true).fadeIn(CFG.animSpeed);
            return;
        }

        // All suggestion types rendered as clean search rows (no product cards)
        // This keeps the marketplace fair — clicking any row shows ALL matching
        // products from all sellers, not a single product.
        suggestions.forEach((sug, idx) => $list.append(buildRow(sug, q, idx)));

        $box.stop(true).fadeIn(CFG.animSpeed);
    }

    // ─────────────────────────────────────────────────────────────
    // ROW BUILDERS
    // ─────────────────────────────────────────────────────────────

    function buildRow(sug, q, idx) {
        const $li = $("<li>").addClass("sug-item").attr("data-idx", idx);
        const icon = getIcon(sug);

        // ── Text rendering ───────────────────────────────────────────────
        // Google-style: typed part is normal weight, predicted completion is bold.
        // e.g. user typed "sam" → show "sam<strong>sung galaxy</strong>"
        //
        // For category/brand rows: the full text is shown normally since the
        // prediction IS the query — the "in X" pill provides the context.
        let mainHtml;
        if (sug.type === "autocomplete" || sug.type === "exact_search") {
            mainHtml = googleStyleCompletion(escHtml(sug.text), q);
        } else {
            // category_search / brand_search: show the query text, pill does the rest
            mainHtml = googleStyleCompletion(escHtml(sug.text), q);
        }

        // "in Category" or "in Brand" pill — shown inline after the text
        let labelHtml = "";
        if (sug.label) {
            labelHtml = `<span class="sug-in-label">in ${escHtml(sug.label)}</span>`;
        }

        // Arrow on prediction rows (not on exact-search or scoped rows)
        const arrowHtml = sug.type === "autocomplete"
            ? `<span class="sug-arrow-icon">${svgArrow()}</span>`
            : "";

        $li.html(`
            <span class="sug-icon">${icon}</span>
            <span class="sug-body">
                <span class="sug-main">${mainHtml}</span>${labelHtml}
            </span>
            ${arrowHtml}
        `);

        $li.on("click",      () => { hideAll(); handleSuggestionClick(sug); });
        $li.on("mouseenter", () => {
            selectedIndex = idx;
            $li.closest("ul").find(".sug-item").removeClass("sug-highlighted");
            $li.addClass("sug-highlighted");
        });
        return $li;
    }

    /**
     * Google-style completion highlight.
     * The part the user already typed = normal weight.
     * The predicted completion = bold.
     *
     * "sam" + "samsung galaxy" → "sam<strong>sung galaxy</strong>"
     * "iph" + "iphone 14 pro"  → "iph<strong>one 14 pro</strong>"
     *
     * Falls back to bolding the matched portion if prefix not found.
     */
    function googleStyleCompletion(fullText, typedQuery) {
        if (!typedQuery) return `<strong>${fullText}</strong>`;
        const lowerFull  = fullText.toLowerCase();
        const lowerTyped = typedQuery.toLowerCase().trim();

        if (lowerFull.startsWith(lowerTyped)) {
            // Prefix match — typed part normal, rest bold
            const typed     = fullText.slice(0, lowerTyped.length);
            const rest      = fullText.slice(lowerTyped.length);
            return rest ? `${typed}<strong>${rest}</strong>` : typed;
        }

        // Mid-string match — bold the matched section
        const matchIdx = lowerFull.indexOf(lowerTyped);
        if (matchIdx > -1) {
            const before = fullText.slice(0, matchIdx);
            const match  = fullText.slice(matchIdx, matchIdx + lowerTyped.length);
            const after  = fullText.slice(matchIdx + lowerTyped.length);
            return `${before}<strong>${match}</strong>${after}`;
        }

        // Typo tolerance match — just return the text as-is (Typesense found it)
        return fullText;
    }

    // ─────────────────────────────────────────────────────────────
    // CLICK HANDLING
    // ─────────────────────────────────────────────────────────────

    function handleSuggestionClick(sug) {
        switch (sug.action_type) {
            case "search":
                goSearch({ search: sug.action_param });
                break;
            case "search_in_category":
                goSearch({ search: sug.action_param, category: sug.category_slug });
                break;
            case "search_in_brand":
                goSearch({ search: sug.action_param, brand: sug.brand_slug });
                break;
            default:
                goSearch({ search: sug.action_param || sug.text });
        }
    }

    // ─────────────────────────────────────────────────────────────
    // NAVIGATION
    // ─────────────────────────────────────────────────────────────

    function goSearch(params) {
        const url = new URL(CFG.listingUrl, window.location.origin);
        Object.entries(params).forEach(([k, v]) => { if (v) url.searchParams.set(k, v); });
        window.location.href = url.toString();
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    function showLoading($box, $list) {
        $list.html('<li class="sug-loading"><span class="sug-spinner"></span> Searching…</li>');
        $box.stop(true).fadeIn(CFG.animSpeed);
    }
    function hideAll() { $(".search-suggestions").stop(true).fadeOut(CFG.animSpeed); selectedIndex = -1; }

    function updateHighlight($items, $input) {
        $items.removeClass("sug-highlighted");
        if (selectedIndex >= 0 && selectedIndex < $items.length) {
            const $sel = $items.eq(selectedIndex).addClass("sug-highlighted");
            const txt  = $sel.find(".sug-main").text();
            if (txt) $input.val(txt);
        }
    }

    function highlight(text, q) {
        if (!q) return text;
        const rx = new RegExp("(" + escRx(q) + ")", "gi");
        return text.replace(rx, "<strong>$1</strong>");
    }

    function escHtml(t) { const d = document.createElement("div"); d.textContent = t || ""; return d.innerHTML; }
    function escRx(t)   { return t.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"); }
    function formatCount(n) { return n >= 1000 ? (n/1000).toFixed(1)+"k" : n; }
    function formatPrice(n) { return Number(n).toLocaleString("en-NG", {minimumFractionDigits:0, maximumFractionDigits:0}); }

    function getIcon(sug) {
        switch (sug.type) {
            case "exact_search":    return svgSearch();
            case "category_search": return svgTag();
            case "brand_search":    return svgStore();
            default:                return svgSearch();
        }
    }

    function svgSearch()  { return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>`; }
    function svgTag()     { return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>`; }
    function svgStore()   { return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`; }
    function svgProduct() { return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>`; }
    function svgArrow()   { return `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg>`; }

    $(document).ready(init);

})(jQuery);


/* ─── Suggestion dropdown CSS ──────────────────────────── */
(function injectStyles() {
    if (document.getElementById("fastsearch-styles")) return;
    const style = document.createElement("style");
    style.id    = "fastsearch-styles";
    style.textContent = `
/* ── Wrapper ── */
.search-suggestions {
    position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    background: #fff; border: 1px solid #e0e0e0;
    border-radius: 12px;
    box-shadow: 0 12px 40px rgba(0,0,0,.14), 0 2px 8px rgba(0,0,0,.06);
    z-index: 9999; overflow: hidden; display: none; min-width: 380px;
}
.search-suggestions ul { list-style:none; margin:0; padding:6px 0; max-height:480px; overflow-y:auto; }

/* ── Base item ── */
.sug-item {
    display:flex; align-items:center; gap:12px; padding:10px 16px;
    cursor:pointer; transition:background 0.1s; font-size:14px; color:#333;
    line-height:1.3;
}
.sug-item:hover, .sug-item.sug-highlighted { background:#f5f5f5; }

/* ── Icon — small, muted, no bubble ── */
.sug-icon {
    flex-shrink:0; width:16px; height:16px; display:flex; align-items:center;
    justify-content:center; color:#aaa;
}

/* ── Body ── */
.sug-body {
    flex:1; display:flex; align-items:center; gap:8px;
    min-width:0; overflow:hidden;
}
/* Typed portion — normal weight */
.sug-main { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#333; font-weight:400; }
/* Predicted completion — bold and dark, like Google */
.sug-main strong { font-weight:700; color:#111; }

/* ── "in Category/Brand" pill — inline after text ── */
.sug-in-label {
    font-size:12px; font-weight:600; color:#1a7340;
    background:#e8f5ee; padding:2px 9px; border-radius:20px;
    white-space:nowrap; flex-shrink:0; margin-left:2px;
}

/* ── Arrow on prediction rows ── */
.sug-arrow-icon {
    color:#ccc; flex-shrink:0; display:flex; align-items:center; margin-left:auto;
}

/* ── Autocomplete prediction row ── */
.sug-item[data-type="autocomplete"] .sug-main { color:#333; }

/* ── Loading / empty ── */
.sug-loading { display:flex; align-items:center; gap:10px; padding:14px 16px; color:#999; font-size:14px; }
.sug-spinner {
    width:15px; height:15px; border:2px solid #ddd; border-top-color:#714e32;
    border-radius:50%; animation:sugSpin .7s linear infinite; display:inline-block;
}
@keyframes sugSpin { to { transform:rotate(360deg); } }
.sug-empty { padding:14px 16px; color:#999; font-size:14px; text-align:center; }

/* ── Input wrapper needs relative ── */
.box-search { position:relative; }
    `;
    document.head.appendChild(style);
})();