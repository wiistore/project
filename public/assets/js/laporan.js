// js halaman laporan: render 3 chart (penjualan+laba, metode bayar, top produk)
(function () {
    'use strict';

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
            return;
        }

        callback();
    }

    // baca data chart dari script tag, kalo gagal parse balikin object kosong biar gak error
    function readChartData() {
        const script = document.querySelector('#laporanChartData');

        if (!script) {
            return {
                sales: { labels: [], penjualan: [], laba: [] },
                payments: { labels: [], values: [] },
                products: { labels: [], values: [] }
            };
        }

        try {
            return JSON.parse(script.textContent || '{}');
        } catch (error) {
            return {
                sales: { labels: [], penjualan: [], laba: [] },
                payments: { labels: [], values: [] },
                products: { labels: [], values: [] }
            };
        }
    }

    function money(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(Number(value || 0));
    }

    function defaultOptions(extra) {
        return Object.assign({
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.dataset.label || '';
                            return label + ': ' + money(context.raw || 0);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 0
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return money(value);
                        }
                    }
                }
            }
        }, extra || {});
    }

    // line chart: penjualan vs laba per periode
    function initSalesChart(data) {
        const canvas = document.querySelector('#laporanSalesChart');

        if (!canvas || !window.Chart || !data.sales || !data.sales.labels.length) {
            return;
        }

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.sales.labels,
                datasets: [
                    {
                        label: 'Penjualan',
                        data: data.sales.penjualan,
                        tension: 0.35,
                        fill: false,
                        borderWidth: 3,
                        pointRadius: 4
                    },
                    {
                        label: 'Laba',
                        data: data.sales.laba,
                        tension: 0.35,
                        fill: false,
                        borderWidth: 3,
                        pointRadius: 4
                    }
                ]
            },
            options: defaultOptions()
        });
    }

    // donut chart: breakdown nominal per metode pembayaran
    function initPaymentChart(data) {
        const canvas = document.querySelector('#laporanPaymentChart');

        if (!canvas || !window.Chart || !data.payments || !data.payments.labels.length) {
            return;
        }

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: data.payments.labels,
                datasets: [
                    {
                        label: 'Penjualan',
                        data: data.payments.values,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.label + ': ' + money(context.raw || 0);
                            }
                        }
                    }
                }
            }
        });
    }

 // bar chart horizontal: top produk, bisa mode jumlah qty atau nominal rupiah
 function initTopProductChart(data) {
    const canvas = document.querySelector('#laporanTopProductChart');

    if (!canvas || !window.Chart || !data.products || !data.products.labels.length) {
        return;
    }

    const datasetLabel = data.products.datasetLabel || 'Qty Terjual';
    const tooltipMode = data.products.tooltipMode || 'number';

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.products.labels,
            datasets: [
                {
                    label: datasetLabel,
                    data: data.products.values,
                    borderWidth: 1,
                    borderRadius: 10
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            if (tooltipMode === 'money') {
                                return datasetLabel + ': ' + money(context.raw || 0);
                            }

                            return datasetLabel + ': ' + context.raw;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            if (tooltipMode === 'money') {
                                return money(value);
                            }

                            return value;
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

    ready(function () {
        const data = readChartData();

        initSalesChart(data);
        initPaymentChart(data);
        initTopProductChart(data);
    });
})();