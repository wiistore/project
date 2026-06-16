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

    function number(value) {
        const parsed = Number(value || 0);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function formatRupiah(value) {
        const amount = number(value);

        if (amount <= 0) {
            return 'Rp 0';
        }

        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(amount);
    }

    function initRestockSearch() {
        const searchInput = document.querySelector('[data-restock-search]');
        const rows = Array.from(document.querySelectorAll('[data-restock-row]'));
        const emptyState = document.querySelector('[data-restock-filter-empty]');

        if (!rows.length || !searchInput) {
            return;
        }

        function applyFilter() {
            const keyword = normalize(searchInput.value);
            let visibleCount = 0;

            rows.forEach(function (row) {
                const rowSearch = normalize(row.dataset.search);
                const visible = keyword === '' || rowSearch.includes(keyword);

                row.hidden = !visible;

                if (visible) {
                    visibleCount += 1;
                }
            });

            if (emptyState) {
                emptyState.hidden = visibleCount !== 0;
            }
        }

        searchInput.addEventListener('input', applyFilter);
        applyFilter();
    }

    function initRestockCalculation() {
        const qtyInput = document.querySelector('[data-qty-input]');
        const buyPriceInput = document.querySelector('[data-buy-price-input]');
        const buyPricePreview = document.querySelector('[data-buy-price-preview]');
        const totalTarget = document.querySelector('[data-restock-total]');

        if (!qtyInput || !buyPriceInput || !totalTarget) {
            return;
        }

        function update() {
            const qty = number(qtyInput.value);
            const buyPrice = number(buyPriceInput.value);
            const total = qty * buyPrice;

            totalTarget.textContent = formatRupiah(total);

            if (buyPricePreview) {
                buyPricePreview.textContent = buyPrice > 0
                    ? 'Harga beli: ' + formatRupiah(buyPrice) + ' per item.'
                    : 'Preview harga beli akan muncul di sini.';
            }
        }

        qtyInput.addEventListener('input', update);
        buyPriceInput.addEventListener('input', update);
        update();
    }

    function initBarangPreview() {
        const select = document.querySelector('[data-barang-select]');
        const codeTarget = document.querySelector('[data-preview-code]');
        const stockTarget = document.querySelector('[data-preview-stock]');
        const priceTarget = document.querySelector('[data-preview-price]');
        const newPriceInput = document.querySelector('[data-new-price-input]');

        if (!select) {
            return;
        }

        function selectedOption() {
            return select.options[select.selectedIndex] || null;
        }

        function update() {
            const option = selectedOption();

            if (!option || !option.value) {
                if (codeTarget) codeTarget.textContent = 'Kode: -';
                if (stockTarget) stockTarget.textContent = 'Stok saat ini: -';
                if (priceTarget) priceTarget.textContent = 'Harga jual sekarang: -';
                return;
            }

            const code = option.dataset.code || '-';
            const stock = option.dataset.stock || '0';
            const unit = option.dataset.unit || '';
            const price = option.dataset.price || '0';

            if (codeTarget) {
                codeTarget.textContent = 'Kode: ' + code;
            }

            if (stockTarget) {
                stockTarget.textContent = 'Stok saat ini: ' + stock + (unit ? ' ' + unit : '');
            }

            if (priceTarget) {
                priceTarget.textContent = 'Harga jual sekarang: ' + formatRupiah(price);
            }
        }

        select.addEventListener('change', update);

        if (newPriceInput) {
            newPriceInput.addEventListener('input', function () {
                const value = number(newPriceInput.value);

                if (priceTarget && value > 0) {
                    priceTarget.textContent = 'Harga jual baru: ' + formatRupiah(value);
                }

                if (priceTarget && value <= 0) {
                    update();
                }
            });
        }

        update();
    }

    function initSupplierPreview() {
        const select = document.querySelector('[data-supplier-select]');
        const contactTarget = document.querySelector('[data-preview-contact]');
        const phoneTarget = document.querySelector('[data-preview-phone]');

        if (!select) {
            return;
        }

        function selectedOption() {
            return select.options[select.selectedIndex] || null;
        }

        function update() {
            const option = selectedOption();

            if (!option || !option.value) {
                if (contactTarget) contactTarget.textContent = 'Kontak: -';
                if (phoneTarget) phoneTarget.textContent = 'No HP: -';
                return;
            }

            if (contactTarget) {
                contactTarget.textContent = 'Kontak: ' + (option.dataset.contact || '-');
            }

            if (phoneTarget) {
                phoneTarget.textContent = 'No HP: ' + (option.dataset.phone || '-');
            }
        }

        select.addEventListener('change', update);
        update();
    }


    function initBarangFilterBySupplier() {
        const supplierSelect = document.querySelector('[data-supplier-select]');
        const barangSelect = document.querySelector('[data-barang-select]');
        const barangSearch = document.querySelector('[data-barang-search]');

        if (!barangSelect) {
            return;
        }

        const requireSupplier = barangSelect.dataset.requireSupplier === '1';
        const placeholder = barangSelect.querySelector('option[value=""]');
        const options = Array.from(barangSelect.options).filter(function (option) {
            return option.value !== '';
        });

        function visibleByFilter(option, supplierId, keyword) {
            const optionSupplierId = String(option.dataset.supplierId || '');
            const text = normalize([
                option.textContent,
                option.dataset.name,
                option.dataset.code,
                option.dataset.supplierName
            ].join(' '));

            const supplierOk = requireSupplier
                ? supplierId !== '' && optionSupplierId === supplierId
                : supplierId === '' || optionSupplierId === supplierId;

            const keywordOk = keyword === '' || text.includes(keyword);

            return supplierOk && keywordOk;
        }

        function applyFilter() {
            const supplierId = supplierSelect ? String(supplierSelect.value || '') : '';
            const keyword = normalize(barangSearch ? barangSearch.value : '');
            let visibleCount = 0;

            options.forEach(function (option) {
                const visible = visibleByFilter(option, supplierId, keyword);

                option.hidden = !visible;
                option.disabled = !visible;

                if (visible) {
                    visibleCount += 1;
                }
            });

            if (placeholder) {
                if (requireSupplier && supplierId === '') {
                    placeholder.textContent = 'Pilih supplier dulu';
                } else if (visibleCount === 0) {
                    placeholder.textContent = 'Barang tidak ditemukan';
                } else {
                    placeholder.textContent = 'Pilih barang';
                }
            }

            const selected = barangSelect.options[barangSelect.selectedIndex];

            if (selected && selected.value && (selected.hidden || selected.disabled)) {
                barangSelect.value = '';
                barangSelect.dispatchEvent(new Event('change'));
            }
        }

        if (supplierSelect) {
            supplierSelect.addEventListener('change', applyFilter);
        }

        if (barangSearch) {
            barangSearch.addEventListener('input', applyFilter);
        }

        applyFilter();
    }

    function initSubmitState() {
        const form = document.querySelector('[data-restock-form]');
        const button = document.querySelector('.restock-submit-btn');

        if (!form || !button) {
            return;
        }

        form.addEventListener('submit', function () {
            button.disabled = true;
            button.innerHTML = '<i class="ti ti-loader-2"></i> Menyimpan...';
        });
    }

    ready(function () {
        initRestockSearch();
        initRestockCalculation();
        initBarangPreview();
        initSupplierPreview();
        initBarangFilterBySupplier();
        initSubmitState();
    });
})();