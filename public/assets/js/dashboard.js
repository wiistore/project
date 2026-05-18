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
                    duration: 900,
                    easing: 'easeOutQuart',
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
                    duration: 900,
                    easing: 'easeOutQuart',
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
                    duration: 900,
                    easing: 'easeOutQuart',
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
        const counters = document.querySelectorAll('[data-count-up]');

        counters.forEach((counter) => {
            const target = Number(counter.dataset.countUp || '0');
            const prefix = counter.dataset.prefix || '';
            const duration = 850;
            const start = performance.now();

            function tick(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 4);
                const current = Math.round(target * eased);

                counter.textContent = `${prefix}${formatter.format(current)}`;

                if (progress < 1) {
                    requestAnimationFrame(tick);
                }
            }

            requestAnimationFrame(tick);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const data = getChartData();

        initSalesChart(data);
        initStockChart(data);
        initTopProductChart(data);
        initCountUp();
    });
})();