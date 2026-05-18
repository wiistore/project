/**
 * App JS — sidebar, navbar, profile, custom reveal animation, count-up.
 *
 * Animasi reveal pakai IntersectionObserver inline (bukan AOS CDN).
 * Alasan: AOS dari CDN sering race condition + ke-cache gak stabil
 * di reload terus-menerus. Custom version ini zero deps, zero blocking,
 * dan jalan walaupun jaringan ke CDN gagal.
 */
(() => {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const numberFormatter = new Intl.NumberFormat('id-ID');

    /* ============================================================
       SIDEBAR
       ============================================================ */
    function initSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const toggles = document.querySelectorAll('[data-sidebar-toggle]');
        const closers = document.querySelectorAll('[data-sidebar-close]');
        const backdrop = document.querySelector('.app-sidebar-backdrop');

        if (!sidebar) return;

        const open = () => {
            sidebar.classList.add('is-open');
            backdrop?.classList.add('is-open');
            document.body.classList.add('sidebar-open');
        };

        const close = () => {
            sidebar.classList.remove('is-open');
            backdrop?.classList.remove('is-open');
            document.body.classList.remove('sidebar-open');
        };

        toggles.forEach((b) => b.addEventListener('click', open));
        closers.forEach((b) => b.addEventListener('click', close));

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') close();
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 991) close();
        });
    }

    /* ============================================================
       PROFILE DROPDOWN
       ============================================================ */
    function initProfileDropdown() {
        const profile = document.querySelector('[data-profile-menu]');
        const toggle = document.querySelector('[data-profile-toggle]');
        if (!profile || !toggle) return;

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            profile.classList.toggle('is-open');
        });

        document.addEventListener('click', (e) => {
            if (!profile.contains(e.target)) profile.classList.remove('is-open');
        });

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') profile.classList.remove('is-open');
        });
    }

    /* ============================================================
       ACTIVE MENU FALLBACK
       ============================================================ */
    function initActiveMenuFallback() {
        const links = document.querySelectorAll('.app-sidebar-link[href]');
        const currentPath = window.location.pathname.replace(/\/+$/, '');
        if (!links.length) return;

        const hasActive = Array.from(links).some((l) => l.classList.contains('is-active'));
        if (hasActive) return;

        links.forEach((link) => {
            const url = new URL(link.href, window.location.origin);
            const linkPath = url.pathname.replace(/\/+$/, '');
            if (currentPath === linkPath || currentPath.startsWith(`${linkPath}/`)) {
                link.classList.add('is-active');
            }
        });
    }

    /* ============================================================
       REVEAL ANIMATION (custom AOS-like)
       Mapping selector lengkap untuk semua halaman project.
       ============================================================ */
    const ANIMATION_MAP = [
        // === HERO sections ===
        {
            selector: [
                '.dashboard-hero',
                '.barang-hero', '.kategori-hero', '.supplier-hero',
                '.restock-hero', '.riwayat-hero', '.user-hero',
                '.laporan-hero', '.kasir-hero', '.profil-hero',
                '.pos-hero', '.struk-hero',
            ].join(','),
            anim: 'fade-down',
            delay: 0,
        },
        // === SUMMARY / STAT CARDS (staggered) ===
        {
            selector: [
                '.dashboard-stat-card',
                '.barang-summary > *',
                '.kategori-summary > *',
                '.supplier-summary > *',
                '.restock-summary > *',
                '.user-summary > *',
                '.user-info-card',
                '.riwayat-summary > *',
                '.riwayat-summary-card',
                '.laporan-summary > *',
                '.laporan-summary-card',
                '.kasir-summary > *',
                '.kasir-summary-card',
                '.profil-overview > *',
                '.profil-mini-card',
                '.pos-summary > *',
                '.pos-summary-card',
                '.pos-mini-summary > *',
                '.struk-summary-card',
            ].join(','),
            anim: 'fade-up',
            stagger: 70,
        },
        // === FILTER / TOOLBAR PANELS ===
        {
            selector: [
                '.barang-filter', '.kategori-filter', '.supplier-filter',
                '.restock-filter', '.user-filter-bar',
                '.riwayat-filter', '.riwayat-date-filter',
                '.laporan-filter-panel', '.laporan-nav',
                '.kasir-filter', '.user-page > .user-tools',
            ].join(','),
            anim: 'fade-up',
            delay: 80,
        },
        // === MAIN PANELS / CARDS / CHARTS ===
        {
            selector: [
                '.dashboard-card',
                '.barang-panel',
                '.kategori-panel',
                '.supplier-panel',
                '.restock-panel',
                '.riwayat-panel',
                '.user-panel',
                '.laporan-panel',
                '.laporan-chart-grid > *',
                '.laporan-chart-card',
                '.kasir-panel', '.kasir-transaction-panel',
                '.kasir-profile-card', '.kasir-tips-card',
                '.profil-card',
                '.pos-products-panel',
                '.pos-cart-panel',
                '.pos-payment-section',
                '.struk-preview-card',
                '.struk-action-card',
                '.user-form-card',
                '.user-form-aside',
                '.user-info-section',
                '.barang-detail-panel',
                '.riwayat-detail-action-card',
            ].join(','),
            anim: 'fade-up',
            stagger: 90,
            max: 14,
        },
        // === EMPTY STATES ===
        {
            selector: [
                '.barang-empty', '.kategori-empty', '.supplier-empty',
                '.restock-empty', '.user-empty', '.riwayat-empty',
                '.laporan-empty', '.kasir-empty',
                '.pos-empty', '.pos-cart-empty', '.pos-filter-empty',
                '.dashboard-empty', '.empty-state',
            ].join(','),
            anim: 'zoom-in',
            delay: 100,
        },
        // === TABLE ROWS (max 15 biar gak lag kalau ratusan) ===
        {
            selector: [
                '.app-table tbody tr',
                'table.app-table tbody tr',
                '.user-table tbody tr',
                '.barang-table tbody tr',
                '.riwayat-table tbody tr',
            ].join(','),
            anim: 'fade-right',
            stagger: 25,
            max: 15,
        },
        // === SHORTCUTS / FOOTER ACTIONS ===
        {
            selector: '.dashboard-shortcuts > *',
            anim: 'fade-up',
            stagger: 50,
        },
    ];

    function autoTagAnimations() {
        ANIMATION_MAP.forEach(({ selector, anim, delay = 0, stagger = 0, max = Infinity }) => {
            const items = document.querySelectorAll(selector);
            let counter = 0;

            items.forEach((el) => {
                if (el.hasAttribute('data-reveal')) return; // udah ditandai
                if (counter >= max) return;

                el.setAttribute('data-reveal', anim);
                const total = delay + stagger * counter;
                if (total > 0) el.setAttribute('data-reveal-delay', String(total));
                counter++;
            });
        });

        // Konversi atribut legacy data-animate / data-aos -> data-reveal
        document.querySelectorAll('[data-animate], [data-aos]').forEach((el) => {
            if (el.hasAttribute('data-reveal')) return;

            const raw = el.getAttribute('data-aos') || el.getAttribute('data-animate') || 'fade-up';
            const valid = ['fade-up', 'fade-down', 'fade-left', 'fade-right', 'zoom-in', 'zoom-out'];
            el.setAttribute('data-reveal', valid.includes(raw) ? raw : 'fade-up');

            const d = el.getAttribute('data-aos-delay') || el.getAttribute('data-delay');
            if (d) el.setAttribute('data-reveal-delay', d);
        });
    }

    function initRevealAnimation() {
        autoTagAnimations();

        const elements = document.querySelectorAll('[data-reveal]');
        if (!elements.length) return;

        // Pasang inline style awal supaya gak kedip
        elements.forEach((el) => {
            const delay = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
            el.style.setProperty('--reveal-delay', `${Number.isNaN(delay) ? 0 : delay}ms`);
        });

        // Reduced motion -> langsung visible, skip animasi
        if (prefersReducedMotion) {
            elements.forEach((el) => el.classList.add('is-revealed'));
            return;
        }

        // IntersectionObserver fallback kalau gak didukung browser tua
        if (typeof IntersectionObserver === 'undefined') {
            elements.forEach((el) => el.classList.add('is-revealed'));
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                });
            },
            {
                threshold: 0.08,
                rootMargin: '0px 0px -10px 0px',
            }
        );

        // Elemen yang udah masuk viewport saat load -> langsung reveal
        // dengan delay sesuai stagger biar tetap nyusul satu per satu.
        elements.forEach((el) => {
            const rect = el.getBoundingClientRect();
            const inViewport = rect.top < window.innerHeight && rect.bottom > 0;

            if (inViewport) {
                // Trigger dgn next frame supaya transition kebaca
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => el.classList.add('is-revealed'));
                });
            } else {
                observer.observe(el);
            }
        });
    }

    /* ============================================================
       COUNT-UP UNIVERSAL
       Manual: data-count-up="123" data-prefix="Rp "
       Auto-detect: <strong> di summary card varian apa pun.
       ============================================================ */
    function initCountUp() {
        if (prefersReducedMotion) return;

        // Manual
        document.querySelectorAll('[data-count-up]').forEach((el) => {
            const target = Number(el.dataset.countUp || '0');
            const prefix = el.dataset.prefix || '';
            animateNumber(el, target, prefix);
        });

        // Auto-detect berdasarkan parent class
        const autoSelectors = [
            '.dashboard-stat-card strong',
            '.summary-card strong', '.stat-card strong', '.metric-card strong',
            '.dashboard-summary strong',
            '.barang-summary strong', '.kategori-summary strong',
            '.supplier-summary strong', '.restock-summary strong',
            '.riwayat-summary-card strong',
            '.user-info-card strong',
            '.user-summary-card strong',
            '.laporan-summary-card strong',
            '.kasir-summary-card strong',
            '.profil-mini-card strong',
            '.profil-overview strong',
            '.pos-mini-summary strong',
            '.pos-summary-card strong',
            '.struk-summary-card strong',
            '[data-auto-countup]',
        ];

        document.querySelectorAll(autoSelectors.join(',')).forEach((el) => {
            if (el.hasAttribute('data-count-up')) return;
            if (el.dataset.autoCountupDone === '1') return;

            const raw = (el.textContent || '').trim();
            const parsed = parseRupiah(raw);
            if (parsed === null) return;
            if (parsed.value === 0 && !/^[Rp\s.,0-]+$/i.test(raw)) return;

            el.dataset.autoCountupDone = '1';
            const isRupiah = /Rp/i.test(raw);
            animateNumber(el, parsed.value, isRupiah ? 'Rp ' : '', parsed.decimals);
        });
    }

    function parseRupiah(text) {
        if (!text) return null;
        const cleaned = text.replace(/[^0-9,.\-]/g, '');
        if (!cleaned) return null;

        const lastComma = cleaned.lastIndexOf(',');
        let normalized;
        let decimals = 0;

        if (lastComma >= 0) {
            const intPart = cleaned.slice(0, lastComma).replace(/\./g, '');
            const decPart = cleaned.slice(lastComma + 1);
            normalized = `${intPart}.${decPart}`;
            decimals = decPart.length;
        } else {
            normalized = cleaned.replace(/\./g, '');
        }

        const num = Number(normalized);
        if (Number.isNaN(num)) return null;
        return { value: num, decimals };
    }

    function animateNumber(el, target, prefix = '', decimals = 0) {
        const duration = 850;
        const start = performance.now();
        const safeTarget = Number.isFinite(target) ? target : 0;

        function tick(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 4);
            const current = safeTarget * eased;
            el.textContent = `${prefix}${formatNumber(current, decimals)}`;
            if (progress < 1) requestAnimationFrame(tick);
            else el.textContent = `${prefix}${formatNumber(safeTarget, decimals)}`;
        }

        requestAnimationFrame(tick);
    }

    function formatNumber(value, decimals) {
        if (decimals > 0) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }).format(value);
        }
        return numberFormatter.format(Math.round(value));
    }

    /* ============================================================
       GLOBAL SEARCH (sidebar quick search)
       ============================================================ */
    function initGlobalSearch() {
        const input = document.querySelector('[data-global-search]');
        if (!input) return;

        const menuLinks = Array.from(document.querySelectorAll('.app-sidebar-link[href]'));

        input.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const keyword = input.value.trim().toLowerCase();
            if (!keyword) return;
            const target = menuLinks.find((l) => l.textContent.trim().toLowerCase().includes(keyword));
            if (target) window.location.href = target.href;
        });
    }

    /* ============================================================
       THEME / COMPACT TOGGLE
       ============================================================ */
    function initThemeButton() {
        const button = document.querySelector('[data-theme-toggle]');
        if (!button) return;

        button.addEventListener('click', () => {
            document.body.classList.toggle('is-compact-mode');
            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('ti-sun');
                icon.classList.toggle('ti-layout-sidebar-left-collapse');
            }
        });
    }

    /* ============================================================
       BOOT
       ============================================================ */
    function boot() {
        initSidebar();
        initProfileDropdown();
        initActiveMenuFallback();
        initRevealAnimation();
        initCountUp();
        initGlobalSearch();
        initThemeButton();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        // Script dimuat setelah DOM ready -> langsung jalan
        boot();
    }

    // Expose untuk dipanggil ulang setelah AJAX render dynamic content
    window.AppUI = {
        refreshAnimations: () => {
            autoTagAnimations();
            initRevealAnimation();
        },
        countUp: animateNumber,
    };
})();
