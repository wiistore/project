// js halaman profil kasir: counter username, toggle password, password meter
(function () {
    'use strict';

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
            return;
        }

        callback();
    }

    // counter karakter buat field username
    function initUsernameCounter() {
        const input = document.querySelector('[data-username-input]');
        const counter = document.querySelector('[data-username-counter]');

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

    // tombol mata buat show/hide tiap field password di form profil
    function initPasswordToggle() {
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                const wrapper = button.closest('.profil-input-wrap');

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

    // hitung skor password (panjang + huruf besar + angka + simbol) terus update bar
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

            if (value.length === 0) {
                meter.style.width = '0';
                meter.style.background = '#d92d20';

                if (hint) {
                    hint.textContent = 'Minimal 8 karakter.';
                }

                return;
            }

            if (score <= 1) {
                meter.style.width = '35%';
                meter.style.background = '#d92d20';

                if (hint) {
                    hint.textContent = 'Lemah. Tambah panjang password.';
                }

                return;
            }

            if (score <= 3) {
                meter.style.width = '65%';
                meter.style.background = '#f79009';

                if (hint) {
                    hint.textContent = 'Cukup. Lebih aman kalau pakai angka/simbol.';
                }

                return;
            }

            meter.style.width = '100%';
            meter.style.background = '#128048';

            if (hint) {
                hint.textContent = 'Kuat. Akhirnya password yang nggak memalukan.';
            }
        }

        input.addEventListener('input', updateMeter);
        updateMeter();
    }

    // disable tombol submit waktu lagi proses biar gak ke-klik 2x
    function initSubmitState() {
        document.querySelectorAll('[data-profil-form]').forEach(function (form) {
            form.addEventListener('submit', function () {
                const button = form.querySelector('button[type="submit"]');

                if (!button) {
                    return;
                }

                const label = button.dataset.submitLabel || 'Menyimpan';

                button.disabled = true;
                button.innerHTML = '<i class="ti ti-loader-2"></i> ' + label + '...';
            });
        });
    }

    ready(function () {
        initUsernameCounter();
        initPasswordToggle();
        initPasswordMeter();
        initSubmitState();
    });
})();