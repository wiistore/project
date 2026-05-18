// js halaman user/kasir: filter, counter username, toggle pw, password meter, konfirmasi nonaktif
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

    // === Filter list user (search + role + status) ===
    function initUserFilter() {
        const searchInput = document.querySelector('[data-user-search]');
        const roleFilter = document.querySelector('[data-user-role-filter]');
        const statusFilter = document.querySelector('[data-user-status-filter]');
        const resetButton = document.querySelector('[data-user-reset]');
        const rows = Array.from(document.querySelectorAll('[data-user-row]'));
        const emptyState = document.querySelector('[data-user-filter-empty]');

        if (!rows.length) {
            return;
        }

        function applyFilter() {
            const keyword = normalize(searchInput ? searchInput.value : '');
            const role = normalize(roleFilter ? roleFilter.value : '');
            const status = normalize(statusFilter ? statusFilter.value : '');

            let visibleCount = 0;

            rows.forEach(function (row) {
                const rowSearch = normalize(row.dataset.search);
                const rowRole = normalize(row.dataset.role);
                const rowStatus = normalize(row.dataset.status);

                const matchKeyword = keyword === '' || rowSearch.includes(keyword);
                const matchRole = role === '' || rowRole === role;
                const matchStatus = status === '' || rowStatus === status;
                const visible = matchKeyword && matchRole && matchStatus;

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

        if (roleFilter) {
            roleFilter.addEventListener('change', applyFilter);
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

                if (roleFilter) {
                    roleFilter.value = '';
                }

                if (statusFilter) {
                    statusFilter.value = '';
                }

                applyFilter();
            });
        }

        applyFilter();
    }

    // counter karakter field username
    function initUsernameCounter() {
        const input = document.querySelector('#username');
        const counter = document.querySelector('[data-user-counter]');

        if (!input || !counter) {
            return;
        }

        function updateCounter() {
            const max = Number(input.getAttribute('maxlength') || 50);
            const length = input.value.length;

            counter.textContent = length + '/' + max;
            counter.classList.toggle('is-danger', length >= max);
        }

        input.addEventListener('input', updateCounter);
        updateCounter();
    }

    function initPasswordToggle() {
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                const wrapper = button.closest('.user-input-wrap');

                if (!wrapper) {
                    return;
                }

                const input = wrapper.querySelector('input');

                if (!input) {
                    return;
                }

                const visible = input.type === 'text';

                input.type = visible ? 'password' : 'text';
                button.innerHTML = visible
                    ? '<i class="ti ti-eye"></i>'
                    : '<i class="ti ti-eye-off"></i>';
            });
        });
    }

    // skor password sederhana, update bar warna merah/oranye/ijo
    function initPasswordMeter() {
        const input = document.querySelector('[data-password-input]');
        const meter = document.querySelector('[data-password-meter]');
        const hint = document.querySelector('[data-password-hint]');

        if (!input || !meter) {
            return;
        }

        function scorePassword(value) {
            let score = 0;

            if (value.length >= 8) score += 1;
            if (/[A-Z]/.test(value)) score += 1;
            if (/[0-9]/.test(value)) score += 1;
            if (/[^A-Za-z0-9]/.test(value)) score += 1;

            return score;
        }

        function updateMeter() {
            const value = input.value;
            const score = scorePassword(value);

            meter.classList.remove('is-medium', 'is-strong');

            if (value.length === 0) {
                meter.style.width = '0';
                if (hint) hint.textContent = 'Minimal 8 karakter.';
                return;
            }

            if (score <= 1) {
                meter.style.width = '35%';
                meter.style.background = '#d92d20';
                if (hint) hint.textContent = 'Lemah. Tambah panjang password.';
                return;
            }

            if (score <= 3) {
                meter.style.width = '65%';
                meter.style.background = '#f79009';
                if (hint) hint.textContent = 'Cukup. Lebih aman kalau pakai angka/simbol.';
                return;
            }

            meter.style.width = '100%';
            meter.style.background = '#128048';
            if (hint) hint.textContent = 'Kuat. Ini baru password, bukan teka-teki murahan.';
        }

        input.addEventListener('input', updateMeter);
        updateMeter();
    }

    // modal konfirmasi nonaktifkan kasir, di-reuse buat semua row
    function createConfirmModal() {
        let activeForm = null;

        const backdrop = document.createElement('div');
        backdrop.className = 'user-confirm-backdrop';
        backdrop.innerHTML = [
            '<div class="user-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="userConfirmTitle">',
            '   <div class="user-confirm-icon">',
            '       <i class="ti ti-alert-triangle"></i>',
            '   </div>',
            '   <h3 id="userConfirmTitle">Nonaktifkan Kasir</h3>',
            '   <p data-confirm-message>Kasir akan dinonaktifkan. Lanjut?</p>',
            '   <div class="user-confirm-actions">',
            '       <button type="button" class="user-confirm-cancel" data-confirm-cancel>Batal</button>',
            '       <button type="button" class="user-confirm-submit" data-confirm-submit>Ya, nonaktifkan</button>',
            '   </div>',
            '</div>'
        ].join('');

        document.body.appendChild(backdrop);

        const title = backdrop.querySelector('#userConfirmTitle');
        const message = backdrop.querySelector('[data-confirm-message]');
        const cancelButton = backdrop.querySelector('[data-confirm-cancel]');
        const submitButton = backdrop.querySelector('[data-confirm-submit]');

        function open(form) {
            activeForm = form;

            if (title) {
                title.textContent = form.dataset.confirmTitle || 'Nonaktifkan Kasir';
            }

           if (message) {
    message.textContent = form.dataset.confirmMessage || 'Kasir akan dinonaktifkan. Lanjut?';
}

if (submitButton) {
    submitButton.textContent = form.dataset.confirmSubmit || 'Ya, nonaktifkan';
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
        const forms = Array.from(document.querySelectorAll('[data-user-delete-form]'));

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
        const form = document.querySelector('[data-user-form]');
        const button = document.querySelector('.user-submit-btn');

        if (!form || !button) {
            return;
        }

        form.addEventListener('submit', function () {
            button.disabled = true;
            button.innerHTML = '<i class="ti ti-loader-2"></i> Menyimpan...';
        });
    }

    ready(function () {
        initUserFilter();
        initUsernameCounter();
        initPasswordToggle();
        initPasswordMeter();
        initDeleteConfirm();
        initSubmitState();
    });
})();