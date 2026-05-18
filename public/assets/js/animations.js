/**
 * animations.js
 *
 * Modul animasi global untuk Kopsis POS:
 * 1. Init AOS (Animate On Scroll) v2.3.4
 * 2. Counter animation untuk angka 0 -> target (stat cards, summary)
 * 3. Helper Chart.js animation defaults + re-animate on filter change
 *
 * Auto-init saat DOMContentLoaded.
 */

(function () {
    'use strict';

    /* =====================================================================
     * 1) AOS INITIALIZATION
     * ===================================================================== */
    function initAOS() {
        if (typeof window.AOS === 'undefined') {
            return;
        }

        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        window.AOS.init({
            // false = animasi muncul tiap scroll naik-turun (sesuai request user)
            once: false,

            // mirror = animate-out saat element scroll keluar viewport (atas)
            mirror: false,

            // Disable di mobile (request user) + kalau prefer-reduced-motion
            disable: function () {
                return isMobile || prefersReducedMotion;
            },

            // Default settings
            duration: 600,
            easing: 'ease-out-cubic',
            offset: 80,
            delay: 0,
            anchorPlacement: 'top-bottom',
        });

        // Refresh AOS kalau ada konten dynamic ditambah (contoh: search filter)
        window.addEventListener('load', function () {
            window.AOS.refresh();
        });
    }

    /* =====================================================================
     * 2) COUNTER ANIMATION (0 -> target dengan easing)
     *    Pakai data attribute:
     *    <span data-counter="125000" data-counter-prefix="Rp " data-counter-format="rupiah"></span>
     *    <span data-counter="42" data-counter-format="integer"></span>
     * ===================================================================== */

    const COUNTER_DURATION = 1500; // 1.5 detik (sesuai request)
    const counterObserved = new WeakSet();

    function easeOutExpo(t) {
        return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
    }

    function formatNumber(value, format) {
        const rounded = Math.round(value);

        if (format === 'rupiah' || format === 'thousand') {
            return rounded.toLocaleString('id-ID');
        }

        return String(rounded);
    }

    function animateCounter(el) {
        const target = parseFloat(el.getAttribute('data-counter') || '0');
        const prefix = el.getAttribute('data-counter-prefix') || '';
        const suffix = el.getAttribute('data-counter-suffix') || '';
        const format = el.getAttribute('data-counter-format') || 'integer';
        const duration = parseInt(el.getAttribute('data-counter-duration') || COUNTER_DURATION, 10);

        if (isNaN(target) || target === 0) {
            el.textContent = prefix + formatNumber(target, format) + suffix;
            return;
        }

        const startTime = performance.now();

        function tick(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = easeOutExpo(progress);
            const current = target * eased;

            el.textContent = prefix + formatNumber(current, format) + suffix;

            if (progress < 1) {
                requestAnimationFrame(tick);
            } else {
                el.textContent = prefix + formatNumber(target, format) + suffix;
            }
        }

        requestAnimationFrame(tick);
    }

    function initCounters() {
        const counters = document.querySelectorAll('[data-counter]');

        if (counters.length === 0) {
            return;
        }

        // Pakai IntersectionObserver biar animasi cuma jalan saat masuk viewport
        if (typeof IntersectionObserver === 'undefined') {
            // Fallback: animate semua langsung
            counters.forEach(animateCounter);
            return;
        }

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting && !counterObserved.has(entry.target)) {
                    counterObserved.add(entry.target);
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.2,
            rootMargin: '0px 0px -50px 0px',
        });

        counters.forEach(function (counter) {
            // Set initial 0 supaya gak nge-flash angka final dulu
            const prefix = counter.getAttribute('data-counter-prefix') || '';
            const suffix = counter.getAttribute('data-counter-suffix') || '';
            const format = counter.getAttribute('data-counter-format') || 'integer';
            counter.textContent = prefix + formatNumber(0, format) + suffix;

            observer.observe(counter);
        });
    }

    /* =====================================================================
     * 3) CHART.JS ANIMATION DEFAULTS + RE-ANIMATE HELPER
     * ===================================================================== */

    function setupChartDefaults() {
        if (typeof window.Chart === 'undefined') {
            return;
        }

        // Set default animasi global Chart.js
        window.Chart.defaults.animation = {
            duration: 1200,
            easing: 'easeOutQuart',
        };

        window.Chart.defaults.animations = {
            tension: {
                duration: 1500,
                easing: 'easeOutQuart',
                from: 0.4,
                to: 0,
                loop: false,
            },
        };

        // Hover animation lebih responsif
        window.Chart.defaults.transitions = window.Chart.defaults.transitions || {};
        window.Chart.defaults.transitions.active = {
            animation: {
                duration: 400,
                easing: 'easeOutQuart',
            },
        };
    }

    /**
     * Helper buat re-animate chart (dipakai di laporan.js, dashboard.js).
     * Caller cukup panggil: window.AppAnim.reanimateChart(chartInstance);
     */
    function reanimateChart(chart) {
        if (!chart || typeof chart.update !== 'function') {
            return;
        }

        try {
            chart.reset();
            chart.update();
        } catch (e) {
            // Fallback aman
            chart.update();
        }
    }

    /* =====================================================================
     * 4) PUBLIC API
     * ===================================================================== */
    window.AppAnim = {
        initAOS: initAOS,
        initCounters: initCounters,
        animateCounter: animateCounter,
        reanimateChart: reanimateChart,
        refreshAOS: function () {
            if (window.AOS) {
                window.AOS.refresh();
            }
        },
    };

    /* =====================================================================
     * 5) AUTO-INIT
     * ===================================================================== */
    function bootstrap() {
        setupChartDefaults();
        initAOS();
        initCounters();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        bootstrap();
    }
})();
