// js halaman kategori: search live + counter karakter + konfirmasi hapus
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

    // === Search list kategori ===
    function initKategoriSearch() {
        const searchInput = document.querySelector('[data-kategori-search]');
        const resetButton = document.querySelector('[data-kategori-reset]');
        const rows = Array.from(document.querySelectorAll('[data-kategori-row]'));
        const emptyState = document.querySelector('[data-kategori-filter-empty]');

        if (!rows.length) {
            return;
        }

        function applyFilter() {
            const keyword = normalize(searchInput ? searchInput.value : '');
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

        if (searchInput) {
            searchInput.addEventListener('input', applyFilter);
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                }

                applyFilter();
            });
        }

        applyFilter();
    }

    // counter karakter di field nama, kasih warna merah kalo full
    function initKategoriCounter() {
        const input = document.querySelector('#nama');
        const counter = document.querySelector('[data-kategori-counter]');

        if (!input || !counter) {
            return;
        }

        function updateCounter() {
            const max = Number(input.getAttribute('maxlength') || 100);
            const length = input.value.length;

            counter.textContent = length + '/' + max;
            counter.classList.toggle('is-danger', length >= max);
        }

        input.addEventListener('input', updateCounter);
        updateCounter();
    }

    // modal konfirmasi sekali bikin, di-reuse buat semua form delete kategori
    function createConfirmModal() {
        let activeForm = null;

        const backdrop = document.createElement('div');
        backdrop.className = 'kategori-confirm-backdrop';
        backdrop.innerHTML = [
            '<div class="kategori-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="kategoriConfirmTitle">',
            '   <div class="kategori-confirm-icon">',
            '       <i class="ti ti-trash"></i>',
            '   </div>',
            '   <h3 id="kategoriConfirmTitle">Hapus Kategori</h3>',
            '   <p data-confirm-message>Kategori akan dihapus kalau belum dipakai barang. Lanjut?</p>',
            '   <div class="kategori-confirm-actions">',
            '       <button type="button" class="kategori-confirm-cancel" data-confirm-cancel>Batal</button>',
            '       <button type="button" class="kategori-confirm-submit" data-confirm-submit>Ya, hapus</button>',
            '   </div>',
            '</div>'
        ].join('');

        document.body.appendChild(backdrop);

        const title = backdrop.querySelector('#kategoriConfirmTitle');
        const message = backdrop.querySelector('[data-confirm-message]');
        const cancelButton = backdrop.querySelector('[data-confirm-cancel]');
        const submitButton = backdrop.querySelector('[data-confirm-submit]');

        function open(form) {
            activeForm = form;

            if (title) {
                title.textContent = form.dataset.confirmTitle || 'Hapus Kategori';
            }

            if (message) {
                message.textContent = form.dataset.confirmMessage || 'Kategori akan dihapus kalau belum dipakai barang. Lanjut?';
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
        const forms = Array.from(document.querySelectorAll('[data-kategori-delete-form]'));

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

    // disable tombol submit + ganti label ke 'Menyimpan...' biar gak double click
    function initSubmitState() {
        const form = document.querySelector('[data-kategori-form]');
        const button = document.querySelector('.kategori-submit-btn');

        if (!form || !button) {
            return;
        }

        form.addEventListener('submit', function () {
            button.disabled = true;
            button.innerHTML = '<i class="ti ti-loader-2"></i> Menyimpan...';
        });
    }

    ready(function () {
        initKategoriSearch();
        initKategoriCounter();
        initDeleteConfirm();
        initSubmitState();
    });
})();