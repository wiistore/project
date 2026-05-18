(function () {
    'use strict';

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
            return;
        }

        callback();
    }

    function normalize(value) {
        return String(value || '').trim().toLowerCase();
    }

    function initRiwayatFilter() {
        const searchInput = document.querySelector('[data-riwayat-search]');
        const methodFilter = document.querySelector('[data-riwayat-method-filter]');
        const rows = Array.from(document.querySelectorAll('[data-riwayat-row]'));
        const emptyState = document.querySelector('[data-riwayat-filter-empty]');

        if (!rows.length) {
            return;
        }

        function applyFilter() {
            const keyword = normalize(searchInput ? searchInput.value : '');
            const method = normalize(methodFilter ? methodFilter.value : '');

            let visibleCount = 0;

            rows.forEach(function (row) {
                const rowSearch = normalize(row.dataset.search);
                const rowMethod = normalize(row.dataset.method);

                const matchKeyword = keyword === '' || rowSearch.includes(keyword);
                const matchMethod = method === '' || rowMethod === method;
                const visible = matchKeyword && matchMethod;

                row.hidden = !visible;

                if (visible) {
                    visibleCount += 1;
                }
            });

            if (emptyState) {
                emptyState.hidden = visibleCount !== 0;
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilter);
        }

        if (methodFilter) {
            methodFilter.addEventListener('change', applyFilter);
        }

        applyFilter();
    }

    ready(function () {
        initRiwayatFilter();
    });
})();