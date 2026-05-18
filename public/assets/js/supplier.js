// js halaman supplier: filter search + status, counter nama, konfirmasi hapus
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

    // === Filter list supplier ===
    function initSupplierFilter() {
        const searchInput = document.querySelector('[data-supplier-search]');
        const statusFilter = document.querySelector('[data-supplier-status-filter]');
        const resetButton = document.querySelector('[data-supplier-reset]');
        const rows = Array.from(document.querySelectorAll('[data-supplier-row]'));
        const emptyState = document.querySelector('[data-supplier-filter-empty]');

        if (!rows.length) {
            return;
        }

        function applyFilter() {
            const keyword = normalize(searchInput ? searchInput.value : '');
            const status = normalize(statusFilter ? statusFilter.value : '');

            let visibleCount = 0;

            rows.forEach(function (row) {
                const rowSearch = normalize(row.dataset.search);
                const rowStatus = normalize(row.dataset.status);

                const matchKeyword = keyword === '' || rowSearch.includes(keyword);
                const matchStatus = status === '' || rowStatus === status;
                const visible = matchKeyword && matchStatus;

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

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                }

                if (statusFilter) {
                    statusFilter.value = '';
                }

                applyFilter();
            });
        }

        applyFilter();
    }

    // counter karakter field nama supplier
    function initSupplierCounter() {
        const input = document.querySelector('#nama');
        const counter = document.querySelector('[data-supplier-counter]');

        if (!input || !counter) {
            return;
        }

        function updateCounter() {
            const max = Number(input.getAttribute('maxlength') || 150);
            const length = input.value.length;

            counter.textContent = length + '/' + max;
            counter.classList.toggle('is-danger', length >= max);
        }

        input.addEventListener('input', updateCounter);
        updateCounter();
    }

    // modal konfirmasi shared buat semua form proses supplier
    function createConfirmModal() {
        let activeForm = null;

        const backdrop = document.createElement('div');
        backdrop.className = 'supplier-confirm-backdrop';
        backdrop.innerHTML = [
            '<div class="supplier-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="supplierConfirmTitle">',
            '   <div class="supplier-confirm-icon">',
            '       <i class="ti ti-alert-triangle"></i>',
            '   </div>',
            '   <h3 id="supplierConfirmTitle">Proses Supplier</h3>',
            '   <p data-confirm-message>Supplier akan diproses. Lanjut?</p>',
            '   <div class="supplier-confirm-actions">',
            '       <button type="button" class="supplier-confirm-cancel" data-confirm-cancel>Batal</button>',
            '       <button type="button" class="supplier-confirm-submit" data-confirm-submit>Ya, proses</button>',
            '   </div>',
            '</div>'
        ].join('');

        document.body.appendChild(backdrop);

        const title = backdrop.querySelector('#supplierConfirmTitle');
        const message = backdrop.querySelector('[data-confirm-message]');
        const cancelButton = backdrop.querySelector('[data-confirm-cancel]');
        const submitButton = backdrop.querySelector('[data-confirm-submit]');

        function open(form) {
            activeForm = form;

            if (title) {
                title.textContent = form.dataset.confirmTitle || 'Proses Supplier';
            }

            if (message) {
                message.textContent = form.dataset.confirmMessage || 'Supplier akan diproses. Lanjut?';
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

    function initDeleteConfirm() {
        const forms = Array.from(document.querySelectorAll('[data-supplier-delete-form]'));

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

    function initSubmitState() {
        const form = document.querySelector('[data-supplier-form]');
        const button = document.querySelector('.supplier-submit-btn');

        if (!form || !button) {
            return;
        }

        form.addEventListener('submit', function () {
            button.disabled = true;
            button.innerHTML = '<i class="ti ti-loader-2"></i> Menyimpan...';
        });
    }

    ready(function () {
        initSupplierFilter();
        initSupplierCounter();
        initDeleteConfirm();
        initSubmitState();
    });
})();