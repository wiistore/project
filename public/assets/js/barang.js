// js halaman barang: search + filter status/stok + konfirmasi hapus + preview harga
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

    // === Filter list barang (search + status + stok) ===
    function initBarangFilter() {
        const searchInput = document.querySelector('[data-barang-search]');
        const statusFilter = document.querySelector('[data-barang-status-filter]');
        const stockFilter = document.querySelector('[data-barang-stock-filter]');
        const resetButton = document.querySelector('[data-barang-reset]');
        const rows = Array.from(document.querySelectorAll('[data-barang-row]'));
        const emptyState = document.querySelector('[data-barang-filter-empty]');

        if (!rows.length) {
            return;
        }

        function applyFilter() {
            const keyword = normalize(searchInput ? searchInput.value : '');
            const status = normalize(statusFilter ? statusFilter.value : '');
            const stock = normalize(stockFilter ? stockFilter.value : '');

            let visibleCount = 0;

            rows.forEach(function (row) {
                const rowSearch = normalize(row.dataset.search);
                const rowStatus = normalize(row.dataset.status);
                const rowStock = normalize(row.dataset.stock);

                const matchKeyword = keyword === '' || rowSearch.includes(keyword);
                const matchStatus = status === '' || rowStatus === status;
                const matchStock = stock === '' || rowStock === stock;

                const visible = matchKeyword && matchStatus && matchStock;

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

        if (statusFilter) {
            statusFilter.addEventListener('change', applyFilter);
        }

        if (stockFilter) {
            stockFilter.addEventListener('change', applyFilter);
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                if (statusFilter) statusFilter.value = '';
                if (stockFilter) stockFilter.value = '';

                applyFilter();

                if (searchInput) {
                    searchInput.focus();
                }
            });
        }

        applyFilter();
    }

    function formatRupiah(value) {
        const number = Number(value || 0);

        if (!Number.isFinite(number) || number <= 0) {
            return 'Preview harga akan muncul di sini.';
        }

        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(number);
    }

    // preview format rupiah live waktu user ngetik harga
    function initPricePreview() {
        const input = document.querySelector('[data-price-preview-input]');
        const preview = document.querySelector('[data-price-preview]');

        if (!input || !preview) {
            return;
        }

        function updatePreview() {
            preview.textContent = formatRupiah(input.value);
        }

        input.addEventListener('input', updatePreview);
        updatePreview();
    }

    // bikin modal konfirmasi sekali aja, dipake bareng buat semua form delete
    function createConfirmModal() {
        let activeForm = null;

        const backdrop = document.createElement('div');
        backdrop.className = 'barang-confirm-backdrop';
        backdrop.innerHTML = [
            '<div class="barang-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="barangConfirmTitle">',
            '   <div class="barang-confirm-icon">',
            '       <i class="ti ti-trash"></i>',
            '   </div>',
            '   <h3 id="barangConfirmTitle">Hapus / Nonaktifkan Barang</h3>',
            '   <p data-confirm-message>Barang akan diproses. Lanjut?</p>',
            '   <div class="barang-confirm-actions">',
            '       <button type="button" class="barang-confirm-cancel" data-confirm-cancel>Batal</button>',
            '       <button type="button" class="barang-confirm-submit" data-confirm-submit>Ya, proses</button>',
            '   </div>',
            '</div>'
        ].join('');

        document.body.appendChild(backdrop);

        const title = backdrop.querySelector('#barangConfirmTitle');
        const message = backdrop.querySelector('[data-confirm-message]');
        const cancelButton = backdrop.querySelector('[data-confirm-cancel]');
        const submitButton = backdrop.querySelector('[data-confirm-submit]');

        function open(form) {
            activeForm = form;

            if (title) {
                title.textContent = form.dataset.confirmTitle || 'Hapus / Nonaktifkan Barang';
            }

            if (message) {
                message.textContent = form.dataset.confirmMessage || 'Barang akan diproses. Lanjut?';
            }

            backdrop.classList.add('is-open');
            document.body.style.overflow = 'hidden';

            if (cancelButton) {
                cancelButton.focus();
            }
        }

        function close() {
            activeForm = null;
            backdrop.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        if (cancelButton) {
            cancelButton.addEventListener('click', close);
        }

        if (submitButton) {
            submitButton.addEventListener('click', function () {
                if (!activeForm) {
                    close();
                    return;
                }

                const form = activeForm;
                activeForm = null;
                form.dataset.confirmed = 'true';
                form.submit();
            });
        }

        backdrop.addEventListener('click', function (event) {
            if (event.target === backdrop) {
                close();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && backdrop.classList.contains('is-open')) {
                close();
            }
        });

        return {
            open: open
        };
    }

    // intercept submit form hapus, tampilin modal dulu sebelum bener-bener submit
    function initDeleteConfirm() {
        const forms = Array.from(document.querySelectorAll('[data-barang-delete-form]'));

        if (!forms.length) {
            return;
        }

        const modal = createConfirmModal();

        forms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmed === 'true') {
                    return;
                }

                event.preventDefault();
                modal.open(form);
            });
        });
    }

    ready(function () {
        initBarangFilter();
        initPricePreview();
        initDeleteConfirm();
    });
})();