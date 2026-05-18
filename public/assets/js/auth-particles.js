// js buat halaman login: reveal animation, toggle password, sama bg particle canvas
(() => {
    'use strict';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // reveal pake IntersectionObserver, fallback langsung visible kalo reduced motion
    function initRevealAnimation() {
        document.documentElement.classList.add('auth-ready');

        const items = document.querySelectorAll('[data-auth-animate]');

        items.forEach((item) => {
            const delay = Number.parseInt(item.getAttribute('data-delay') || '0', 10);
            item.style.setProperty('--auth-delay', `${Number.isNaN(delay) ? 0 : delay}ms`);
        });

        if (prefersReducedMotion) {
            items.forEach((item) => item.classList.add('is-visible'));
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

        items.forEach((item) => observer.observe(item));
    }

    // toggle mata buat show/hide password di field login
    function initPasswordToggle() {
        const buttons = document.querySelectorAll('[data-password-toggle]');

        buttons.forEach((button) => {
            const targetId = button.getAttribute('data-password-toggle');
            const input = targetId ? document.getElementById(targetId) : null;
            const icon = button.querySelector('i');

            if (!input || !icon) {
                return;
            }

            button.addEventListener('click', () => {
                const isPassword = input.getAttribute('type') === 'password';

                input.setAttribute('type', isPassword ? 'text' : 'password');
                button.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');

                icon.classList.toggle('ti-eye', !isPassword);
                icon.classList.toggle('ti-eye-off', isPassword);

                input.focus();
            });
        });
    }

    // === Particle background ===
    // canvas animasi titik-titik nyambung, di-pause kalo tab hidden biar hemat cpu
    function initParticles() {
        const canvas = document.getElementById('authParticles');

        if (!canvas || prefersReducedMotion) {
            return;
        }

        const context = canvas.getContext('2d', { alpha: true });

        if (!context) {
            return;
        }

        let width = 0;
        let height = 0;
        let ratio = 1;
        let particles = [];
        let animationFrame = null;
        let isRunning = true;

        const pointer = {
            x: null,
            y: null,
            active: false,
        };

        // jumlah partikel adaptif sesuai lebar layar biar gak berat di hp
        function getParticleCount() {
            const viewportWidth = window.innerWidth;

            if (viewportWidth <= 480) {
                return 18;
            }

            if (viewportWidth <= 768) {
                return 24;
            }

            if (viewportWidth <= 1180) {
                return 36;
            }

            return 48;
        }

        function randomBetween(min, max) {
            return Math.random() * (max - min) + min;
        }

        function createParticle() {
            const leftBias = Math.random() < 0.72;

            return {
                x: leftBias ? randomBetween(0, width * 0.58) : randomBetween(0, width),
                y: randomBetween(0, height),
                radius: randomBetween(1.1, 2.7),
                vx: randomBetween(-0.18, 0.22),
                vy: randomBetween(-0.16, 0.20),
                opacity: randomBetween(0.22, 0.72),
                pulse: randomBetween(0, Math.PI * 2),
            };
        }

        function resetParticles() {
            const total = getParticleCount();
            particles = Array.from({ length: total }, createParticle);
        }

        function resizeCanvas() {
            ratio = Math.min(window.devicePixelRatio || 1, 2);
            width = window.innerWidth;
            height = window.innerHeight;

            canvas.width = Math.floor(width * ratio);
            canvas.height = Math.floor(height * ratio);
            canvas.style.width = `${width}px`;
            canvas.style.height = `${height}px`;

            context.setTransform(ratio, 0, 0, ratio, 0, 0);

            resetParticles();
        }

        function drawBackgroundGlow() {
            const gradient = context.createRadialGradient(
                width * 0.22,
                height * 0.35,
                0,
                width * 0.22,
                height * 0.35,
                Math.max(width, height) * 0.45
            );

            gradient.addColorStop(0, 'rgba(31, 181, 106, 0.12)');
            gradient.addColorStop(0.5, 'rgba(31, 181, 106, 0.04)');
            gradient.addColorStop(1, 'rgba(31, 181, 106, 0)');

            context.fillStyle = gradient;
            context.fillRect(0, 0, width, height);
        }

        // gerakin partikel + sedikit reaksi sama posisi cursor
        function updateParticle(particle) {
            particle.x += particle.vx;
            particle.y += particle.vy;
            particle.pulse += 0.015;

            if (pointer.active && pointer.x !== null && pointer.y !== null) {
                const dx = pointer.x - particle.x;
                const dy = pointer.y - particle.y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < 140) {
                    particle.x -= dx * 0.0018;
                    particle.y -= dy * 0.0018;
                }
            }

            if (particle.x < -20) {
                particle.x = width + 20;
            }

            if (particle.x > width + 20) {
                particle.x = -20;
            }

            if (particle.y < -20) {
                particle.y = height + 20;
            }

            if (particle.y > height + 20) {
                particle.y = -20;
            }
        }

        function drawParticle(particle) {
            const pulseOpacity = particle.opacity + Math.sin(particle.pulse) * 0.08;

            context.beginPath();
            context.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            context.fillStyle = `rgba(255, 255, 255, ${Math.max(0.12, pulseOpacity)})`;
            context.fill();

            context.beginPath();
            context.arc(particle.x, particle.y, particle.radius * 2.7, 0, Math.PI * 2);
            context.fillStyle = `rgba(31, 181, 106, ${Math.max(0.04, pulseOpacity * 0.12)})`;
            context.fill();
        }

        // tarik garis antar partikel yg cukup deket biar keliatan kayak network
        function drawConnections() {
            const maxDistance = window.innerWidth <= 680 ? 92 : 128;

            for (let i = 0; i < particles.length; i += 1) {
                for (let j = i + 1; j < particles.length; j += 1) {
                    const first = particles[i];
                    const second = particles[j];

                    const dx = first.x - second.x;
                    const dy = first.y - second.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance > maxDistance) {
                        continue;
                    }

                    const opacity = (1 - distance / maxDistance) * 0.18;

                    context.beginPath();
                    context.moveTo(first.x, first.y);
                    context.lineTo(second.x, second.y);
                    context.strokeStyle = `rgba(255, 255, 255, ${opacity})`;
                    context.lineWidth = 1;
                    context.stroke();
                }
            }
        }

        function render() {
            if (!isRunning) {
                return;
            }

            context.clearRect(0, 0, width, height);
            drawBackgroundGlow();

            particles.forEach(updateParticle);
            drawConnections();
            particles.forEach(drawParticle);

            animationFrame = window.requestAnimationFrame(render);
        }

        function start() {
            if (isRunning) {
                return;
            }

            isRunning = true;
            animationFrame = window.requestAnimationFrame(render);
        }

        function stop() {
            isRunning = false;

            if (animationFrame !== null) {
                window.cancelAnimationFrame(animationFrame);
                animationFrame = null;
            }
        }

        // debounce resize biar gak ngitung ulang partikel tiap pixel
        let resizeTimer = null;

        window.addEventListener('resize', () => {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(() => {
                resizeCanvas();
            }, 160);
        });

        window.addEventListener('pointermove', (event) => {
            pointer.x = event.clientX;
            pointer.y = event.clientY;
            pointer.active = true;
        }, { passive: true });

        window.addEventListener('pointerleave', () => {
            pointer.active = false;
            pointer.x = null;
            pointer.y = null;
        });

        // tab di background -> stop animasi, balik fokus -> jalan lagi
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stop();
                return;
            }

            start();
        });

        resizeCanvas();
        render();
    }

    document.addEventListener('DOMContentLoaded', () => {
        initRevealAnimation();
        initPasswordToggle();
        initParticles();
    });
})();