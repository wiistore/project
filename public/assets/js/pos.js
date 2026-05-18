(function () {
    'use strict';

    const state = {
        products: [],
        cart: new Map(),
        activeCategory: 'all'
    };

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
            return;
        }

        callback();
    }

    function number(value) {
        const parsed = Number(value || 0);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function normalize(value) {
        return String(value || '').trim().toLowerCase();
    }

    function money(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(number(value));
    }

    function readProducts() {
        const script = document.querySelector('#posProductData');

        if (!script) {
            state.products = [];
            return;
        }

        try {
            const parsed = JSON.parse(script.textContent || '[]');
            state.products = Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            state.products = [];
        }
    }

    function getProduct(id) {
        return state.products.find(function (product) {
            return Number(product.id) === Number(id);
        });
    }

    function getCartItems() {
        return Array.from(state.cart.values());
    }

    function getTotalItem() {
        return getCartItems().reduce(function (total, item) {
            return total + number(item.qty);
        }, 0);
    }

    function getTotalPay() {
        return getCartItems().reduce(function (total, item) {
            const product = getProduct(item.id_barang);

            if (!product) {
                return total;
            }

            return total + (number(product.harga_jual) * number(item.qty));
        }, 0);
    }

    function addToCart(productId) {
        const product = getProduct(productId);

        if (!product || number(product.stok) <= 0) {
            return;
        }

        const id = Number(product.id);
        const current = state.cart.get(id);

        if (current) {
            if (current.qty >= number(product.stok)) {
                return;
            }

            current.qty += 1;
            state.cart.set(id, current);
        } else {
            state.cart.set(id, {
                id_barang: id,
                qty: 1
            });
        }

        renderCart();
    }

    function updateQty(productId, direction) {
        const id = Number(productId);
        const product = getProduct(id);
        const item = state.cart.get(id);

        if (!product || !item) {
            return;
        }

        const nextQty = item.qty + direction;

        if (nextQty <= 0) {
            state.cart.delete(id);
            renderCart();
            return;
        }

        item.qty = Math.min(nextQty, number(product.stok));
        state.cart.set(id, item);
        renderCart();
    }

    function removeItem(productId) {
        state.cart.delete(Number(productId));
        renderCart();
    }

    function clearCart() {
        state.cart.clear();
        renderCart();
    }

    function renderCart() {
        const list = document.querySelector('[data-cart-items]');
        const empty = document.querySelector('[data-cart-empty]');
        const countTarget = document.querySelector('[data-cart-count]');
        const totalItemTarget = document.querySelector('[data-total-item]');
        const totalPayTarget = document.querySelector('[data-total-pay]');
        const cartJson = document.querySelector('[data-cart-json]');

        if (!list) {
            return;
        }

        list.querySelectorAll('[data-cart-item]').forEach(function (node) {
            node.remove();
        });

        const items = getCartItems();

        if (empty) {
            empty.hidden = items.length > 0;
        }

        items.forEach(function (item) {
            const product = getProduct(item.id_barang);

            if (!product) {
                return;
            }

            const subtotal = number(product.harga_jual) * number(item.qty);
            const row = document.createElement('div');

            row.className = 'pos-cart-item';
            row.dataset.cartItem = String(item.id_barang);

            row.innerHTML = [
                '<div class="pos-cart-item-main">',
                '   <strong></strong>',
                '   <small></small>',
                '   <div class="pos-cart-subtotal"></div>',
                '</div>',
                '<div class="pos-cart-controls">',
                '   <div class="pos-qty-control">',
                '       <button type="button" data-qty-minus aria-label="Kurangi qty"><i class="ti ti-minus"></i></button>',
                '       <span></span>',
                '       <button type="button" data-qty-plus aria-label="Tambah qty"><i class="ti ti-plus"></i></button>',
                '   </div>',
                '   <button type="button" class="pos-remove-item" data-remove-item aria-label="Hapus item"><i class="ti ti-trash"></i></button>',
                '</div>'
            ].join('');

            row.querySelector('strong').textContent = product.nama || '-';
            row.querySelector('small').textContent =
                (product.kode_barang || '-') + ' · ' + money(product.harga_jual) + ' / ' + (product.satuan || 'item');

            row.querySelector('.pos-cart-subtotal').textContent = money(subtotal);
            row.querySelector('.pos-qty-control span').textContent = String(item.qty);

            row.querySelector('[data-qty-minus]').addEventListener('click', function () {
                updateQty(item.id_barang, -1);
            });

            row.querySelector('[data-qty-plus]').addEventListener('click', function () {
                updateQty(item.id_barang, 1);
            });

            row.querySelector('[data-remove-item]').addEventListener('click', function () {
                removeItem(item.id_barang);
            });

            list.appendChild(row);
        });

        const totalItem = getTotalItem();
        const totalPay = getTotalPay();

        if (countTarget) {
            countTarget.textContent = String(items.length);
        }

        if (totalItemTarget) {
            totalItemTarget.textContent = String(totalItem);
        }

        if (totalPayTarget) {
            totalPayTarget.textContent = money(totalPay);
        }

        if (cartJson) {
            cartJson.value = JSON.stringify({
                items: items.map(function (item) {
                    return {
                        id_barang: item.id_barang,
                        qty: item.qty
                    };
                })
            });
        }

        updatePayment();
    }

    function updatePayment() {
        const methodInputs = Array.from(document.querySelectorAll('[data-payment-method]'));
        const activeMethodInput = methodInputs.find(function (input) {
            return input.checked;
        });

        const method = activeMethodInput ? activeMethodInput.value : 'cash';
        const cashBox = document.querySelector('[data-cash-box]');
        const cashInput = document.querySelector('[data-cash-input]');
        const changeTarget = document.querySelector('[data-change]');
        const warning = document.querySelector('[data-payment-warning]');
        const payButton = document.querySelector('[data-pay-button]');

        const totalPay = getTotalPay();
        const isCash = method === 'cash';

        methodInputs.forEach(function (input) {
            const label = input.closest('.pos-payment-method');

            if (label) {
                label.classList.toggle('is-active', input.checked);
            }
        });

        if (cashBox) {
            cashBox.hidden = !isCash;
        }

        if (cashInput && !isCash) {
            cashInput.value = String(totalPay);
        }

        const nominalBayar = isCash ? number(cashInput ? cashInput.value : 0) : totalPay;
        const change = isCash ? nominalBayar - totalPay : 0;
        const hasCart = getCartItems().length > 0;
        const validPayment = !isCash || nominalBayar >= totalPay;

        if (changeTarget) {
            changeTarget.textContent = money(Math.max(change, 0));
        }

        if (warning) {
            warning.hidden = !hasCart || validPayment;
        }

        if (payButton) {
            payButton.disabled = !hasCart || totalPay <= 0 || !validPayment;
        }
    }

    function applyProductFilter() {
        const searchInput = document.querySelector('[data-pos-search]');
        const cards = Array.from(document.querySelectorAll('[data-product-card]'));
        const empty = document.querySelector('[data-product-empty]');

        const keyword = normalize(searchInput ? searchInput.value : '');
        let visibleCount = 0;

        cards.forEach(function (card) {
            const searchText = normalize(card.dataset.search);
            const category = String(card.dataset.category || '');
            const matchKeyword = keyword === '' || searchText.includes(keyword);
            const matchCategory = state.activeCategory === 'all' || category === state.activeCategory;
            const visible = matchKeyword && matchCategory;

            card.hidden = !visible;

            if (visible) {
                visibleCount += 1;
            }
        });

        if (empty) {
            empty.hidden = visibleCount !== 0;
        }
    }

    function initProductCards() {
        document.querySelectorAll('[data-product-card]').forEach(function (card) {
            card.addEventListener('click', function () {
                if (card.disabled) {
                    return;
                }

                addToCart(card.dataset.productId);
            });
        });
    }

    function findByExactBarcode(value) {
        const target = String(value || '').trim();
        if (target === '') {
            return null;
        }
        return state.products.find(function (product) {
            return String(product.barcode || '').trim() === target;
        }) || null;
    }

    function findByExactKode(value) {
        const target = String(value || '').trim();
        if (target === '') {
            return null;
        }
        return state.products.find(function (product) {
            return String(product.kode_barang || '').trim() === target;
        }) || null;
    }

    function flashStockEmpty(product) {
        // Toast singkat pas scan barang yg habis
        if (typeof window.AppToast !== 'undefined' && typeof window.AppToast.show === 'function') {
            window.AppToast.show('Stok ' + (product.nama || 'barang') + ' habis.', 'error');
            return;
        }
        // Fallback console
        console.warn('Stok habis:', product.nama);
    }

    function initSearch() {
        const input = document.querySelector('[data-pos-search]');
        const focusButton = document.querySelector('[data-pos-focus-search]');

        if (!input) {
            return;
        }

        let lastKeyTime = 0;
        let scanBuffer = '';

        input.addEventListener('input', applyProductFilter);

        input.addEventListener('keydown', function (event) {
            const now = Date.now();
            const delta = now - lastKeyTime;
            lastKeyTime = now;

            // Track scan buffer (deteksi input cepat = scanner USB)
            if (event.key.length === 1) {
                if (delta > 100) {
                    scanBuffer = '';
                }
                scanBuffer += event.key;
            }

            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();

            const value = input.value.trim();
            if (value === '') {
                scanBuffer = '';
                return;
            }

            // Prioritas 1: exact match by barcode (paling tepat buat scan kemasan pabrik)
            let target = findByExactBarcode(value);

            // Prioritas 2: exact match by kode_barang
            if (!target) {
                target = findByExactKode(value);
            }

            // Prioritas 3: fallback — first visible product yg match keyword
            if (!target) {
                const firstVisible = Array.from(document.querySelectorAll('[data-product-card]')).find(function (card) {
                    return !card.hidden && !card.disabled;
                });

                if (firstVisible) {
                    target = getProduct(firstVisible.dataset.productId);
                }
            }

            scanBuffer = '';

            if (!target) {
                // Tidak ada match, kasih indikasi visual
                input.classList.add('is-not-found');
                setTimeout(function () { input.classList.remove('is-not-found'); }, 600);
                return;
            }

            if (Number(target.stok) <= 0) {
                flashStockEmpty(target);
                input.value = '';
                applyProductFilter();
                input.focus();
                return;
            }

            addToCart(target.id);
            input.value = '';
            applyProductFilter();
            input.focus();
        });

        if (focusButton) {
            focusButton.addEventListener('click', function () {
                input.focus();
                input.select();
            });
        }

        // Auto-focus saat halaman load (kasir bisa langsung scan)
        // Tunda dikit biar gak konflik sama autofocus elemen lain
        setTimeout(function () {
            if (document.activeElement === document.body || document.activeElement === null) {
                input.focus();
            }
        }, 200);

        // Re-focus search saat user klik area kosong cart panel (UX: tetep siap scan)
        const productsPanel = document.querySelector('.pos-products-panel');
        if (productsPanel) {
            productsPanel.addEventListener('click', function (event) {
                // Skip kalo click ke product card, button, atau input
                const tag = event.target.tagName;
                const isInteractive = event.target.closest('button, input, select, a, label, [data-product-card]');
                if (!isInteractive && tag !== 'INPUT') {
                    input.focus();
                }
            });
        }

        applyProductFilter();
    }

    function initCategories() {
        const buttons = Array.from(document.querySelectorAll('[data-category]'));

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                state.activeCategory = button.dataset.category || 'all';

                buttons.forEach(function (item) {
                    item.classList.toggle('is-active', item === button);
                });

                applyProductFilter();
            });
        });
    }

    function initPayment() {
        document.querySelectorAll('[data-payment-method]').forEach(function (input) {
            input.addEventListener('change', updatePayment);
        });

        const cashInput = document.querySelector('[data-cash-input]');

        if (cashInput) {
            cashInput.addEventListener('input', updatePayment);
        }

        updatePayment();
    }

    function initClearCart() {
        const button = document.querySelector('[data-cart-clear]');

        if (!button) {
            return;
        }

        button.addEventListener('click', clearCart);
    }

    function initKeyboardShortcuts() {
        document.addEventListener('keydown', function (event) {
            if (event.key === 'F2') {
                event.preventDefault();

                const input = document.querySelector('[data-pos-search]');

                if (input) {
                    input.focus();
                    input.select();
                }
            }

            if (event.key === 'F9') {
                event.preventDefault();

                const button = document.querySelector('[data-pay-button]');

                if (button && !button.disabled) {
                    button.click();
                }
            }
        });
    }

    function initSubmit() {
        const form = document.querySelector('[data-pos-form]');
        const payButton = document.querySelector('[data-pay-button]');

        if (!form || !payButton) {
            return;
        }

        form.addEventListener('submit', function (event) {
            updatePayment();

            if (payButton.disabled) {
                event.preventDefault();
                return;
            }

            payButton.disabled = true;
            payButton.innerHTML = '<i class="ti ti-loader-2"></i> Menyimpan... <span>...</span>';
        });
    }

    ready(function () {
        readProducts();
        initProductCards();
        initSearch();
        initCategories();
        initPayment();
        initClearCart();
        initKeyboardShortcuts();
        initSubmit();
        renderCart();
    });
})();