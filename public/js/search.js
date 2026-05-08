/**
 * Ultra-Fast Token-Aware Search Suggestions
 * Prioritizes brands and categories over products
 * Routes users to listing pages with filters applied
 */

(function ($) {
    "use strict";

    // Configuration
    const config = {
        minChars: 2,
        debounceDelay: 200,
        maxSuggestions: 5,
        apiUrl: "/search/suggest",
        animationSpeed: 200,
    };

    // State management
    let debounceTimer = null;
    let currentRequest = null;
    let selectedIndex = -1;

    /**
     * Initialize search suggestions for both desktop and mobile
     */
    function initSearchSuggestions() {
        console.log("🔍 Initializing search suggestions...");

        // Desktop search
        const $desktopInput = $("#search-input");
        const $desktopSuggestions = $("#search-suggestions");
        const $desktopList = $("#suggestion-list");

        if ($desktopInput.length) {
            console.log("✅ Desktop search input found");
            setupSearchInput($desktopInput, $desktopSuggestions, $desktopList);
        } else {
            console.log("❌ Desktop search input NOT found");
        }

        // Mobile search
        const $mobileInput = $("#mobile-search-input");
        const $mobileSuggestions = $("#mobile-search-suggestions");
        const $mobileList = $("#mobile-suggestion-list");

        if ($mobileInput.length) {
            console.log("✅ Mobile search input found");
            setupSearchInput($mobileInput, $mobileSuggestions, $mobileList);
        } else {
            console.log("❌ Mobile search input NOT found");
        }

        // Close suggestions when clicking outside
        $(document).on("click", function (e) {
            console.log("📍 Document clicked", e.target);
            // Check if click is outside search area AND suggestions
            if (
                !$(e.target).closest(".top-search").length &&
                !$(e.target).closest(".search-suggestions").length
            ) {
                console.log(
                    "🚫 Click outside search area - hiding suggestions"
                );
                hideSuggestions();
            } else {
                console.log(
                    "✅ Click inside search area - keeping suggestions open"
                );
            }
        });
    }

    /**
     * Setup search input with suggestion functionality
     */
    function setupSearchInput($input, $suggestionsBox, $list) {
        console.log("⚙️ Setting up search input");

        // Input event handler with debouncing
        $input.on("input", function () {
            const query = $(this).val().trim();
            console.log("⌨️ Input event - Query:", query);

            // Clear previous timer
            clearTimeout(debounceTimer);

            // Cancel ongoing request
            if (currentRequest) {
                currentRequest.abort();
                currentRequest = null;
                console.log("🛑 Aborted previous request");
            }

            if (query.length < config.minChars) {
                console.log("⚠️ Query too short - hiding suggestions");
                hideSuggestions();
                return;
            }

            // Debounce the search
            debounceTimer = setTimeout(function () {
                console.log("⏱️ Debounce complete - fetching suggestions");
                fetchSuggestions(query, $suggestionsBox, $list);
            }, config.debounceDelay);
        });

        // Keyboard navigation
        $input.on("keydown", function (e) {
            const $items = $list.find("li");
            const itemCount = $items.length;

            if (itemCount === 0) return;

            switch (e.keyCode) {
                case 38: // Up arrow
                    e.preventDefault();
                    selectedIndex =
                        selectedIndex <= 0 ? itemCount - 1 : selectedIndex - 1;
                    updateSelection($items);
                    console.log("⬆️ Arrow up - Selected index:", selectedIndex);
                    break;

                case 40: // Down arrow
                    e.preventDefault();
                    selectedIndex =
                        selectedIndex >= itemCount - 1 ? 0 : selectedIndex + 1;
                    updateSelection($items);
                    console.log(
                        "⬇️ Arrow down - Selected index:",
                        selectedIndex
                    );
                    break;

                case 13: // Enter
                    e.preventDefault();
                    console.log(
                        "⏎ Enter pressed - Selected index:",
                        selectedIndex
                    );
                    if (selectedIndex >= 0) {
                        $items.eq(selectedIndex).find("a")[0].click();
                    } else {
                        // Perform full search with query
                        navigateToSearch($(this).val().trim());
                    }
                    break;

                case 27: // Escape
                    console.log("⎋ Escape pressed - hiding suggestions");
                    hideSuggestions();
                    break;
            }
        });

        // Focus event
        $input.on("focus", function () {
            const query = $(this).val().trim();
            console.log("🎯 Input focused - Query:", query);
            if (
                query.length >= config.minChars &&
                $list.children().length > 0
            ) {
                console.log("👁️ Showing suggestions on focus");
                $suggestionsBox.fadeIn(config.animationSpeed).addClass("show");
            }
        });
    }

    /**
     * Fetch suggestions from API
     */
    function fetchSuggestions(query, $suggestionsBox, $list) {
        console.log("🌐 Fetching suggestions for:", query);

        // Show loading state
        $list.html(
            '<li class="loading"><i class="fas fa-spinner fa-spin"></i> Searching...</li>'
        );
        $suggestionsBox.fadeIn(config.animationSpeed).addClass("show");

        // Make AJAX request
        currentRequest = $.ajax({
            url: config.apiUrl,
            method: "GET",
            data: { q: query },
            dataType: "json",
            success: function (response) {
                console.log("✅ Suggestions received:", response);
                currentRequest = null;
                renderSuggestions(
                    response.suggestions,
                    $suggestionsBox,
                    $list,
                    query
                );
            },
            error: function (xhr) {
                if (xhr.statusText !== "abort") {
                    console.error("❌ Error fetching suggestions:", xhr);
                    currentRequest = null;
                    $list.html(
                        '<li class="error">Unable to load suggestions</li>'
                    );
                }
            },
        });
    }

    /**
     * Render suggestions in the dropdown
     */
    function renderSuggestions(suggestions, $suggestionsBox, $list, query) {
        console.log("🎨 Rendering", suggestions?.length || 0, "suggestions");

        $list.empty();
        selectedIndex = -1;

        if (!suggestions || suggestions.length === 0) {
            $list.html('<li class="no-results">No suggestions found</li>');
            return;
        }

        suggestions.forEach(function (suggestion, index) {
            console.log(`📝 Creating suggestion ${index}:`, suggestion);
            const $item = createSuggestionItem(suggestion, query, index);
            $list.append($item);
        });

        $suggestionsBox.fadeIn(config.animationSpeed).addClass("show");
    }

    /**
     * Create a suggestion list item based on type
     */
    function createSuggestionItem(suggestion, query, index) {
        console.log(
            `🏗️ Creating item for type: ${suggestion.type}`,
            suggestion
        );

        const $li = $("<li>")
            .addClass("suggestion-item")
            .attr("data-index", index);
        const $link = $("<a>").attr("href", "#");

        // Render based on suggestion type
        switch (suggestion.type) {
            case "brand":
                console.log("🏷️ Rendering brand suggestion");
                renderBrandSuggestion($link, suggestion, query);
                break;
            case "category":
                console.log("📂 Rendering category suggestion");
                renderCategorySuggestion($link, suggestion, query);
                break;
            case "subcategory":
                console.log("📁 Rendering subcategory suggestion");
                renderSubcategorySuggestion($link, suggestion, query);
                break;
            case "search":
                console.log("🔎 Rendering search suggestion");
                renderSearchSuggestion($link, suggestion, query);
                break;
            default:
                console.log("❓ Rendering default search suggestion");
                renderSearchSuggestion($link, suggestion, query);
        }

        $li.append($link);
        return $li;
    }

    /**
     * Render brand suggestion
     */
    function renderBrandSuggestion($link, suggestion, query) {
        console.log("🏷️ Brand suggestion setup:", suggestion);

        let logoHtml = "";
        if (suggestion.logo) {
            logoHtml = `<img src="${suggestion.logo}" alt="${escapeHtml(
                suggestion.text
            )}" class="suggestion-brand-logo">`;
        }

        $link.html(`
            <div class="suggestion-text-only suggestion-brand">
                ${logoHtml}
                <div class="suggestion-content">
                    <span class="suggestion-main">${highlightQuery(
                        suggestion.text,
                        query
                    )}</span>
                    <span class="suggestion-type"></span>
                </div>
            </div>
        `);

        $link.on("click", function (e) {
            console.log("🖱️ BRAND CLICKED:", suggestion.text);
            e.preventDefault();
            e.stopPropagation();
            console.log("🚀 Navigating with brand filter:", suggestion.slug);
            // Filter search results by brand
            navigateToSearch(query, { brand: suggestion.slug });
        });
    }

    /**
     * Render category suggestion
     */
    function renderCategorySuggestion($link, suggestion, query) {
        console.log("📂 Category suggestion setup:", suggestion);

        $link.html(`
            <div class="suggestion-text-only">
                <div class="suggestion-content">
                    <span class="suggestion-main">${highlightQuery(
                        suggestion.text,
                        query
                    )}</span>
                </div>
            </div>
        `);

        $link.on("click", function (e) {
            console.log("🖱️ CATEGORY CLICKED:", suggestion.text);
            e.preventDefault();
            e.stopPropagation();
            console.log(
                "🚀 Navigating to category page:",
                `/category/${suggestion.slug}`
            );
            // Navigate to category page
            window.location.href = `/category/${suggestion.slug}`;
        });
    }

    /**
     * Render subcategory suggestion
     */
    function renderSubcategorySuggestion($link, suggestion, query) {
        console.log("📁 Subcategory suggestion setup:", suggestion);

        $link.html(`
            <div class="suggestion-text-only">
                <div class="suggestion-content">
                    <span class="suggestion-main">${highlightQuery(
                        suggestion.text,
                        query
                    )}</span>
                </div>
            </div>
        `);

        $link.on("click", function (e) {
            console.log("🖱️ SUBCATEGORY CLICKED:", suggestion.text);
            e.preventDefault();
            e.stopPropagation();
            console.log(
                "🚀 Navigating with subcategory filter:",
                suggestion.slug
            );
            // Filter search results by subcategory
            navigateToSearch(query, { subcategory: suggestion.slug });
        });
    }

    /**
     * Render search suggestion
     */
    function renderSearchSuggestion($link, suggestion, query) {
        console.log("🔎 Search suggestion setup:", suggestion);

        $link.html(`
            <div class="suggestion-text-only">
                <span class="suggestion-main">${highlightQuery(
                    suggestion.text,
                    query
                )}</span>
            </div>
        `);

        $link.on("click", function (e) {
            console.log("🖱️ SEARCH SUGGESTION CLICKED:", suggestion.text);
            e.preventDefault();
            e.stopPropagation();
            console.log("🚀 Navigating to search:", suggestion.text);
            // Perform search with suggestion text
            navigateToSearch(suggestion.text);
        });
    }

    /**
     * Navigate to search results page with optional filters
     */
    function navigateToSearch(query, filters = {}) {
        let url = `/search?q=${encodeURIComponent(query)}`;

        // Add filter parameters
        if (filters.brand) {
            url += `&brand=${encodeURIComponent(filters.brand)}`;
        }
        if (filters.category) {
            url += `&category=${encodeURIComponent(filters.category)}`;
        }
        if (filters.subcategory) {
            url += `&subcategory=${encodeURIComponent(filters.subcategory)}`;
        }

        console.log("🌐 Final navigation URL:", url);
        console.log("⏳ Navigating in 100ms...");

        // Small delay to ensure logs are visible
        setTimeout(function () {
            console.log("🚀 NAVIGATING NOW!");
            window.location.href = url;
        }, 100);
    }

    /**
     * Highlight query text in suggestion
     */
    function highlightQuery(text, query) {
        const escapedText = escapeHtml(text);
        const escapedQuery = escapeRegex(query);
        const regex = new RegExp(`(${escapedQuery})`, "gi");
        return escapedText.replace(regex, "<strong>$1</strong>");
    }

    /**
     * Update keyboard selection
     */
    function updateSelection($items) {
        $items.removeClass("selected");
        if (selectedIndex >= 0 && selectedIndex < $items.length) {
            const $selected = $items.eq(selectedIndex);
            $selected.addClass("selected");

            // Scroll into view if needed
            const $container = $items.parent();
            const itemTop = $selected.position().top;
            const itemBottom = itemTop + $selected.outerHeight();
            const containerHeight = $container.height();

            if (itemBottom > containerHeight) {
                $container.scrollTop(
                    $container.scrollTop() + itemBottom - containerHeight
                );
            } else if (itemTop < 0) {
                $container.scrollTop($container.scrollTop() + itemTop);
            }
        }
    }

    /**
     * Hide suggestions dropdown
     */
    function hideSuggestions() {
        console.log("🙈 Hiding suggestions");
        $(".search-suggestions")
            .fadeOut(config.animationSpeed)
            .removeClass("show");
        selectedIndex = -1;
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Escape regex special characters
     */
    function escapeRegex(text) {
        return text.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    }

    // Initialize on document ready
    $(document).ready(function () {
        console.log("📱 Document ready - initializing search");
        initSearchSuggestions();
    });
})(jQuery);
