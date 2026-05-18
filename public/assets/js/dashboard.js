(() => {
    'use strict';

    const formatter = new Intl.NumberFormat('id-ID');

    function getChartData() {
        const script = document.getElementById('dashboardChartData');

        if (!script) {
            return {};
        }

        try {
            return JSON.parse(script.textContent || '{}');
        } catch (error) {
            console.error('Dashboard chart data invalid:', error);
            return {};
        }
    }

    function hasValues(values) {
        return Array.isArray(values) && values.some((value) => Number(value) > 0);
    }

    function renderChartEmpty(canvas, title) {
        if (!canvas || !canvas.parentElement) {
            return;
        }

        const parent = canvas.parentElement;
        canvas.remove();

        const empty = document.createElement('div');
        empty.className = 'dashboard-empty';
        empty.innerHTML = `
            <i class="ti ti-chart-dots"></i>
            <h4>${title}</h4>
            <p>Grafik akan tampil otomatis setelah data tersedia.</p>
        `;

        parent.appendChild(empty);
    }

    function initSalesChart(data) {
        const canvas = document.getElementById('salesChart');

        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        const labels = data.sales?.labels || [];
        const values = data.sales?.values || [];

        if (!hasValues(values)) {
            renderChartEmpty(canvas, 'Belum ada data penjualan');
            return;
        }

        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 340);

        gradient.addColorStop(0, 'rgba(18, 128, 72, 0.28)');
        gradient.addColorStop(1, 'rgba(18, 128, 72, 0.02)');

        new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Penjualan',
                        data: values,
                        borderColor: '#128048',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.42,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#128048',
                        pointBorderWidth: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart',
                },
                animations: {
                    y: {
                        from: function (ctx) {
                            if (ctx.type === 'data' && ctx.mode === 'default' && !ctx.dropped) {
                                ctx.dropped = true;
                                return ctx.chart.scales.y.getPixelForValue(0);
                            }
                        },
                    },
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return `Penjualan: Rp ${formatter.format(context.parsed.y || 0)}`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#6b7a72',
                            font: {
                                weight: 700,
                            },
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(24, 91, 58, 0.08)',
                        },
                        ticks: {
                            color: '#6b7a72',
                            callback(value) {
                                if (value >= 1000000) {
                                    return `Rp ${formatter.format(value / 1000000)}jt`;
                                }

                                if (value >= 1000) {
                                    return `Rp ${formatter.format(value / 1000)}rb`;
                                }

                                return `Rp ${formatter.format(value)}`;
                            },
                        },
                    },
                },
            },
        });
    }

    function initStockChart(data) {
        const canvas = document.getElementById('stockChart');

        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        const labels = data.stockStatus?.labels || [];
        const values = data.stockStatus?.values || [];

        if (!hasValues(values)) {
            renderChartEmpty(canvas, 'Belum ada data stok');
            return;
        }

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [
                    {
                        data: values,
                        backgroundColor: ['#128048', '#f79009', '#d92d20'],
                        borderColor: '#ffffff',
                        borderWidth: 5,
                        hoverOffset: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1300,
                    easing: 'easeOutBack',
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return `${context.label}: ${context.parsed} barang`;
                            },
                        },
                    },
                },
            },
        });
    }

    function initTopProductChart(data) {
        const canvas = document.getElementById('topProductChart');

        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        const labels = data.topProducts?.labels || [];
        const values = data.topProducts?.values || [];

        if (!hasValues(values)) {
            renderChartEmpty(canvas, 'Belum ada barang terlaris');
            return;
        }

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Terjual',
                        data: values,
                        borderRadius: 12,
                        borderSkipped: false,
                        backgroundColor: [
                            'rgba(18, 128, 72, 0.92)',
                            'rgba(36, 107, 254, 0.86)',
                            'rgba(247, 144, 9, 0.86)',
                            'rgba(122, 90, 248, 0.86)',
                            'rgba(217, 45, 32, 0.82)',
                        ],
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart',
                    delay: function (ctx) {
                        if (ctx.type === 'data' && ctx.mode === 'default') {
                            return ctx.dataIndex * 80;
                        }
                        return 0;
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return `Terjual: ${context.parsed.x} item`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(24, 91, 58, 0.08)',
                        },
                        ticks: {
                            precision: 0,
                            color: '#6b7a72',
                        },
                    },
                    y: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#17231d',
                            font: {
                                weight: 800,
                            },
                        },
                    },
                },
            },
        });
    }

    function initCountUp() {
        // Counter animation di-handle global oleh animations.js (window.AppAnim).
        // Tapi karena dashboard.js dimuat SEBELUM animations.js (urutan di scripts.php),
        // dan dashboard pakai atribut lama "data-count-up", kita cuma map ke data-counter
        // jika ada. Migrasi atribut sudah dilakukan di view, jadi function ini cuma fallback.
        const legacy = document.querySelectorAll('[data-count-up]');
        if (!legacy.length) {
            return;
        }
        legacy.forEach((el) => {
            const target = el.getAttribute('data-count-up') || '0';
            const prefix = el.getAttribute('data-prefix') || '';
            el.setAttribute('data-counter', target);
            if (prefix) {
                el.setAttribute('data-counter-prefix', prefix);
            }
            const format = prefix.toLowerCase().includes('rp') ? 'rupiah' : 'thousand';
            el.setAttribute('data-counter-format', format);
            el.removeAttribute('data-count-up');
        });

        if (window.AppAnim && typeof window.AppAnim.initCounters === 'function') {
            window.AppAnim.initCounters();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const data = getChartData();

        initSalesChart(data);
        initStockChart(data);
        initTopProductChart(data);
        initCountUp();
    });
})();