// js komponen global: toast notif + modal konfirmasi reusable
// dipake lewat window.AppToast & window.AppConfirm dari layout manapun
(() => {
    'use strict';

    const toastIcons = {
        success: 'ti ti-circle-check',
        error: 'ti ti-alert-circle',
        warning: 'ti ti-alert-triangle',
        info: 'ti ti-info-circle',
    };

    const toastTitles = {
        success: 'Berhasil',
        error: 'Gagal',
        warning: 'Perhatian',
        info: 'Info',
    };

    // bikin satu element toast lengkap sama tombol close-nya
    function createToastElement({ type = 'info', title = '', message = '', duration = 4200 }) {
        const toast = document.createElement('div');
        const safeType = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';

        toast.className = `app-toast app-toast-${safeType}`;
        toast.setAttribute('role', safeType === 'error' ? 'alert' : 'status');

        const icon = toastIcons[safeType] || toastIcons.info;
        const toastTitle = title || toastTitles[safeType] || toastTitles.info;

        toast.innerHTML = `
            <div class="app-toast-icon">
                <i class="${icon}"></i>
            </div>
            <div class="app-toast-content">
                <strong class="app-toast-title"></strong>
                <span class="app-toast-message"></span>
            </div>
            <button type="button" class="app-toast-close" aria-label="Tutup notifikasi">
                <i class="ti ti-x"></i>
            </button>
        `;

        toast.querySelector('.app-toast-title').textContent = toastTitle;
        toast.querySelector('.app-toast-message').textContent = message || '';

        const closeButton = toast.querySelector('.app-toast-close');

        const close = () => {
            toast.classList.add('is-hiding');

            window.setTimeout(() => {
                toast.remove();
            }, 240);
        };

        closeButton.addEventListener('click', close);

        if (duration > 0) {
            window.setTimeout(close, duration);
        }

        return toast;
    }

    // === Toast notifikasi ===
    // expose AppToast.success/error/warning/info ke global, plus auto-render flash dari server
    function initToast() {
        const container = document.getElementById('appToastContainer');

        if (!container) {
            return;
        }

        window.AppToast = {
            show(options = {}) {
                const toast = createToastElement(options);
                container.appendChild(toast);
                return toast;
            },

            success(message, title = 'Berhasil') {
                return this.show({ type: 'success', title, message });
            },

            error(message, title = 'Gagal') {
                return this.show({ type: 'error', title, message, duration: 5200 });
            },

            warning(message, title = 'Perhatian') {
                return this.show({ type: 'warning', title, message, duration: 5000 });
            },

            info(message, title = 'Info') {
                return this.show({ type: 'info', title, message });
            },
        };

        const flashScript = document.getElementById('appFlashMessages');

        if (!flashScript) {
            return;
        }

        try {
            const messages = JSON.parse(flashScript.textContent || '[]');

            if (Array.isArray(messages)) {
                messages.forEach((item) => {
                    window.AppToast.show({
                        type: item.type || 'info',
                        message: item.message || '',
                    });
                });
            }
        } catch (error) {
            console.error('Gagal membaca flash message:', error);
        }
    }

    // === Modal konfirmasi global ===
    // intercept form/link yg punya data-confirm="true", baru submit kalo user setuju
    function initConfirmModal() {
        const modal = document.getElementById('appConfirm');

        if (!modal) {
            return;
        }

        const titleElement = modal.querySelector('[data-confirm-title]');
        const messageElement = modal.querySelector('[data-confirm-message]');
        const submitButton = modal.querySelector('[data-confirm-submit]');
        const cancelButtons = modal.querySelectorAll('[data-confirm-cancel]');
        const iconBox = modal.querySelector('[data-confirm-icon]');
        const icon = iconBox?.querySelector('i');

        let pendingAction = null;

        const configMap = {
            danger: {
                iconClass: 'ti ti-alert-triangle',
                iconState: '',
                buttonState: '',
                defaultAction: 'Ya, Hapus',
            },
            warning: {
                iconClass: 'ti ti-alert-circle',
                iconState: 'is-warning',
                buttonState: 'is-warning',
                defaultAction: 'Ya, Lanjutkan',
            },
            success: {
                iconClass: 'ti ti-circle-check',
                iconState: 'is-success',
                buttonState: 'is-success',
                defaultAction: 'Ya, Simpan',
            },
        };

        function openConfirm(options = {}, callback) {
            const type = options.type || 'danger';
            const config = configMap[type] || configMap.danger;

            pendingAction = typeof callback === 'function' ? callback : null;

            titleElement.textContent = options.title || 'Konfirmasi Aksi';
            messageElement.textContent = options.message || 'Apakah kamu yakin ingin melanjutkan aksi ini?';
            submitButton.textContent = options.action || config.defaultAction;

            iconBox.classList.remove('is-warning', 'is-success');
            submitButton.classList.remove('is-warning', 'is-success');

            if (config.iconState) {
                iconBox.classList.add(config.iconState);
            }

            if (config.buttonState) {
                submitButton.classList.add(config.buttonState);
            }

            if (icon) {
                icon.className = config.iconClass;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');

            window.setTimeout(() => {
                submitButton.focus();
            }, 40);
        }

        function closeConfirm() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            pendingAction = null;
        }

        submitButton.addEventListener('click', () => {
            const action = pendingAction;
            closeConfirm();

            if (typeof action === 'function') {
                action();
            }
        });

        cancelButtons.forEach((button) => {
            button.addEventListener('click', closeConfirm);
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeConfirm();
            }
        });

        document.addEventListener('submit', (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (form.dataset.confirm !== 'true') {
                return;
            }

            if (form.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();

            openConfirm(
                {
                    type: form.dataset.confirmType || 'danger',
                    title: form.dataset.confirmTitle || 'Konfirmasi Aksi',
                    message: form.dataset.confirmMessage || 'Apakah kamu yakin ingin melanjutkan aksi ini?',
                    action: form.dataset.confirmAction || 'Ya, Lanjutkan',
                },
                () => {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            );
        });

        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-confirm-link="true"]');

            if (!trigger) {
                return;
            }

            event.preventDefault();

            const href = trigger.getAttribute('href');

            if (!href) {
                return;
            }

            openConfirm(
                {
                    type: trigger.dataset.confirmType || 'danger',
                    title: trigger.dataset.confirmTitle || 'Konfirmasi Aksi',
                    message: trigger.dataset.confirmMessage || 'Apakah kamu yakin ingin membuka link ini?',
                    action: trigger.dataset.confirmAction || 'Ya, Lanjutkan',
                },
                () => {
                    window.location.href = href;
                }
            );
        });

        window.AppConfirm = {
            open: openConfirm,
            close: closeConfirm,
        };
    }

    document.addEventListener('DOMContentLoaded', () => {
        initToast();
        initConfirmModal();
    });
})();