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

    function initDeleteConfirm() {
        const triggers = Array.from(document.querySelectorAll('[data-barang-delete-trigger]'));
        const legacyForms = Array.from(document.querySelectorAll('[data-barang-delete-form][data-confirm-title]'));

        if (!triggers.length && !legacyForms.length) {
            return;
        }

        const modal = createConfirmModal();

        // Pattern baru: tombol trigger + hidden form (karena tabel di dalam form bulk)
        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();

                const url = trigger.getAttribute('data-delete-url') || '';
                if (!url) {
                    return;
                }

                // Cari hidden form by URL match (action attribute)
                const matchingId = url.split('/').pop();
                const targetForm = document.querySelector('[data-barang-delete-form][data-delete-id="' + matchingId + '"]');

                if (!targetForm) {
                    console.warn('Delete form tidak ditemukan untuk URL:', url);
                    return;
                }

                // Tempel meta confirm dari trigger ke form, lalu open modal
                targetForm.dataset.confirmTitle = trigger.dataset.confirmTitle || 'Hapus / Nonaktifkan Barang';
                targetForm.dataset.confirmMessage = trigger.dataset.confirmMessage || 'Barang akan diproses. Lanjut?';
                modal.open(targetForm);
            });
        });

        // Pattern lama (backward compat): submit form langsung di-intercept
        legacyForms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmed === 'true') {
                    return;
                }

                event.preventDefault();
                modal.open(form);
            });
        });
    }

    /* =========================================================
     * BARCODE: Live preview + Generate button + Scanner support
     * ========================================================= */

    function renderBarcodePreview(value) {
        const wrap = document.querySelector('[data-barang-barcode-preview]');
        const svg = document.getElementById('barangBarcodePreview');

        if (!svg || !wrap) {
            return;
        }

        const trimmed = String(value || '').trim();

        // Reset SVG biar render ulang clean
        svg.innerHTML = '';
        svg.removeAttribute('width');
        svg.removeAttribute('height');
        svg.removeAttribute('viewBox');

        if (trimmed === '') {
            wrap.classList.add('is-empty');
            return;
        }

        if (typeof window.JsBarcode === 'undefined') {
            // Fallback: tampilin teks polos
            wrap.classList.remove('is-empty');
            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', '10');
            text.setAttribute('y', '24');
            text.setAttribute('font-family', 'monospace');
            text.setAttribute('font-size', '14');
            text.textContent = trimmed;
            svg.appendChild(text);
            return;
        }

        wrap.classList.remove('is-empty');

        try {
            window.JsBarcode(svg, trimmed, {
                format: 'CODE128',
                width: 2,
                height: 60,
                displayValue: true,
                fontSize: 14,
                margin: 4,
                background: '#ffffff',
                lineColor: '#0f172a',
            });
        } catch (e) {
            // JsBarcode kadang reject value invalid (mis. terlalu pendek / karakter aneh).
            // Tampilin teks fallback aja.
            console.warn('Barcode preview gagal:', e);
            wrap.classList.add('is-empty');
        }
    }

    function initBarcodeForm() {
        const input = document.querySelector('[data-barang-barcode-input]');
        const generateBtn = document.querySelector('[data-barang-generate-barcode]');

        if (!input) {
            return;
        }

        // Live preview saat user ngetik / scan / paste
        input.addEventListener('input', function () {
            renderBarcodePreview(input.value);
        });

        // Initial render kalau value udah ada (misal mode edit)
        renderBarcodePreview(input.value);

        // Tombol Generate -> AJAX ke server
        if (generateBtn) {
            generateBtn.addEventListener('click', async function () {
                const url = generateBtn.getAttribute('data-generate-url');

                if (!url) {
                    return;
                }

                generateBtn.disabled = true;
                const originalHtml = generateBtn.innerHTML;
                generateBtn.innerHTML = '<i class="ti ti-loader-2"></i> <span>Generating...</span>';

                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }

                    const data = await response.json();

                    if (data && data.success && data.barcode) {
                        input.value = data.barcode;
                        renderBarcodePreview(data.barcode);
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    } else {
                        alert('Gagal generate barcode. Coba refresh halaman.');
                    }
                } catch (error) {
                    console.error('Generate barcode error:', error);
                    alert('Tidak bisa terhubung ke server untuk generate barcode.');
                } finally {
                    generateBtn.disabled = false;
                    generateBtn.innerHTML = originalHtml;
                }
            });
        }

        // Scanner USB detection: kalo input dapet sequence cepat + Enter, anggap udah dari scanner
        // Auto blur Enter biar form gak ke-submit cuma karena scan
        let lastKeyTime = 0;
        input.addEventListener('keydown', function (event) {
            const now = Date.now();
            const delta = now - lastKeyTime;
            lastKeyTime = now;

            if (event.key === 'Enter') {
                // Kalau Enter dateng dari scanner (interval cepat), prevent submit form
                // tapi tetep biarin user manual ngetik + tab/click submit
                if (delta < 60 && input.value.length >= 4) {
                    event.preventDefault();
                    // Pindahkan focus ke field berikutnya (nama)
                    const nextField = document.getElementById('nama');
                    if (nextField) {
                        nextField.focus();
                    }
                }
            }
        });
    }

    /* =========================================================
     * BARCODE: List page - bulk select + cetak label
     * ========================================================= */
    function initBarangBulkLabel() {
        const checkboxes = Array.from(document.querySelectorAll('[data-barang-checkbox]'));
        const selectAll = document.querySelector('[data-barang-select-all]');
        const bulkForm = document.querySelector('[data-barang-bulk-label-form]');
        const bulkButton = document.querySelector('[data-barang-bulk-label-btn]');
        const bulkCount = document.querySelector('[data-barang-bulk-count]');
        const bulkBar = document.querySelector('[data-barang-bulk-bar]');

        if (!checkboxes.length || !bulkForm) {
            return;
        }

        function refreshBulkState() {
            const selected = checkboxes.filter(function (cb) { return cb.checked; });
            const count = selected.length;

            if (bulkCount) {
                bulkCount.textContent = String(count);
            }

            if (bulkButton) {
                bulkButton.disabled = count === 0;
            }

            if (bulkBar) {
                bulkBar.classList.toggle('is-visible', count > 0);
            }

            if (selectAll) {
                selectAll.checked = count > 0 && count === checkboxes.length;
                selectAll.indeterminate = count > 0 && count < checkboxes.length;
            }
        }

        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', refreshBulkState);
        });

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(function (cb) {
                    if (!cb.disabled) {
                        cb.checked = selectAll.checked;
                    }
                });
                refreshBulkState();
            });
        }

        bulkForm.addEventListener('submit', function (event) {
            // Form di-submit ke /admin/barang/label-bulk dengan ids[] = selected ids
            const selected = checkboxes.filter(function (cb) { return cb.checked; });

            if (selected.length === 0) {
                event.preventDefault();
                alert('Pilih minimal 1 barang dulu.');
                return;
            }
        });

        refreshBulkState();
    }

    ready(function () {
        initBarangFilter();
        initPricePreview();
        initDeleteConfirm();
        initBarcodeForm();
        initBarangBulkLabel();
    });
})();