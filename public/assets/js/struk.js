(function () {
    'use strict';

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
            return;
        }

        callback();
    }

    function getReceiptText() {
        const script = document.querySelector('#strukReceiptText');

        if (!script) {
            return '';
        }

        try {
            return JSON.parse(script.textContent || '""');
        } catch (error) {
            return script.textContent || '';
        }
    }

    function showCopyStatus(message) {
        const status = document.querySelector('[data-copy-status]');

        if (!status) {
            return;
        }

        const text = status.querySelector('span');

        if (text) {
            text.textContent = message;
        }

        status.hidden = false;

        window.setTimeout(function () {
            status.hidden = true;
        }, 3500);
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');

        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        textarea.style.top = '-9999px';

        document.body.appendChild(textarea);
        textarea.select();

        try {
            document.execCommand('copy');
            showCopyStatus('Struk berhasil disalin. Tempel ke WhatsApp/chat.');
        } catch (error) {
            showCopyStatus('Browser tidak mengizinkan salin otomatis. Blok teks struk lalu salin manual.');
        }

        document.body.removeChild(textarea);
    }

    function initCopyReceipt() {
        const button = document.querySelector('[data-copy-receipt]');
        const receiptText = getReceiptText();

        if (!button || !receiptText) {
            return;
        }

        button.addEventListener('click', function () {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(receiptText).then(function () {
                    showCopyStatus('Struk berhasil disalin. Tempel ke WhatsApp/chat.');
                }).catch(function () {
                    fallbackCopy(receiptText);
                });

                return;
            }

            fallbackCopy(receiptText);
        });
    }

    function initPrintReceipt() {
        const button = document.querySelector('[data-print-receipt]');

        if (!button) {
            return;
        }

        button.addEventListener('click', function () {
            window.print();
        });
    }

    ready(function () {
        initCopyReceipt();
        initPrintReceipt();
    });
})();