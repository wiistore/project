(() => {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function initSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const toggles = document.querySelectorAll('[data-sidebar-toggle]');
        const closers = document.querySelectorAll('[data-sidebar-close]');
        const backdrop = document.querySelector('.app-sidebar-backdrop');

        if (!sidebar) {
            return;
        }

        const openSidebar = () => {
            sidebar.classList.add('is-open');
            backdrop?.classList.add('is-open');
            document.body.classList.add('sidebar-open');
        };

        const closeSidebar = () => {
            sidebar.classList.remove('is-open');
            backdrop?.classList.remove('is-open');
            document.body.classList.remove('sidebar-open');
        };

        toggles.forEach((button) => {
            button.addEventListener('click', openSidebar);
        });

        closers.forEach((button) => {
            button.addEventListener('click', closeSidebar);
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 991) {
                closeSidebar();
            }
        });
    }

    function initProfileDropdown() {
        const profile = document.querySelector('[data-profile-menu]');
        const toggle = document.querySelector('[data-profile-toggle]');

        if (!profile || !toggle) {
            return;
        }

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            profile.classList.toggle('is-open');
        });

        document.addEventListener('click', (event) => {
            if (!profile.contains(event.target)) {
                profile.classList.remove('is-open');
            }
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                profile.classList.remove('is-open');
            }
        });
    }

    function initActiveMenuFallback() {
        const links = document.querySelectorAll('.app-sidebar-link[href]');
        const currentPath = window.location.pathname.replace(/\/+$/, '');

        if (!links.length) {
            return;
        }

        const hasActive = Array.from(links).some((link) => link.classList.contains('is-active'));

        if (hasActive) {
            return;
        }

        links.forEach((link) => {
            const url = new URL(link.href, window.location.origin);
            const linkPath = url.pathname.replace(/\/+$/, '');

            if (currentPath === linkPath || currentPath.startsWith(`${linkPath}/`)) {
                link.classList.add('is-active');
            }
        });
    }

    function initRevealAnimation() {
        const elements = document.querySelectorAll('[data-animate]');

        if (!elements.length) {
            return;
        }

        elements.forEach((element) => {
            const delay = Number.parseInt(element.getAttribute('data-delay') || '0', 10);
            element.style.setProperty('--animate-delay', `${Number.isNaN(delay) ? 0 : delay}ms`);
        });

        if (prefersReducedMotion) {
            elements.forEach((element) => element.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            {
                threshold: 0.12,
                rootMargin: '0px 0px -24px 0px',
            }
        );

        elements.forEach((element) => observer.observe(element));
    }

    function initGlobalSearch() {
        const input = document.querySelector('[data-global-search]');

        if (!input) {
            return;
        }

        const menuLinks = Array.from(document.querySelectorAll('.app-sidebar-link[href]'));

        input.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();

            const keyword = input.value.trim().toLowerCase();

            if (!keyword) {
                return;
            }

            const target = menuLinks.find((link) => link.textContent.trim().toLowerCase().includes(keyword));

            if (target) {
                window.location.href = target.href;
            }
        });
    }

    function initThemeButton() {
        const button = document.querySelector('[data-theme-toggle]');

        if (!button) {
            return;
        }

        button.addEventListener('click', () => {
            document.body.classList.toggle('is-compact-mode');

            const icon = button.querySelector('i');

            if (icon) {
                icon.classList.toggle('ti-sun');
                icon.classList.toggle('ti-layout-sidebar-left-collapse');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initSidebar();
        initProfileDropdown();
        initActiveMenuFallback();
        initRevealAnimation();
        initGlobalSearch();
        initThemeButton();
    });
})();