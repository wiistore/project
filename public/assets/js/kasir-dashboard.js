(function () {
    'use strict';

    /**
     * Counter animation di kasir dashboard udah di-handle global oleh animations.js
     * (data-counter). File ini cuma fallback buat backward compatibility kalau
     * view masih pake atribut lama [data-count-up].
     */
    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
            return;
        }

        callback();
    }

    function migrateLegacyCounters() {
        const legacy = document.querySelectorAll('[data-count-up]');
        if (!legacy.length) {
            return;
        }

        legacy.forEach(function (el) {
            const target = el.getAttribute('data-count-up') || '0';
            const prefix = el.getAttribute('data-prefix') || '';
            const format = prefix.toLowerCase().includes('rp') ? 'rupiah' : 'thousand';

            el.setAttribute('data-counter', target);
            if (prefix) {
                el.setAttribute('data-counter-prefix', prefix);
            }
            el.setAttribute('data-counter-format', format);
            el.removeAttribute('data-count-up');
        });

        if (window.AppAnim && typeof window.AppAnim.initCounters === 'function') {
            window.AppAnim.initCounters();
        }
    }

    ready(migrateLegacyCounters);
})();
