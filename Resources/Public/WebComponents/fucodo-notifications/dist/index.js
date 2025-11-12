// <fucodo-notifications
//     data-endpoint="/api/notifications"
//     data-poll-interval="30000">
// </fucodo-notifications>

class FucodoNotifications extends HTMLElement {
    constructor() {
        super();
        this.endpoint = this.getAttribute('data-endpoint') || '/api/notifications';
        this.pollInterval = parseInt(this.getAttribute('data-poll-interval') || '0', 10) || 0;
        this.markAsReadLabel = this.getAttribute('mark-as-read-label') || 'Mark as read';
        this.markAllAsReadLabel = this.getAttribute('mark-all-as-read-label') || 'All read';
        this.titleLabel = this.getAttribute('title-label') || 'Notifications';
        this.noNotificationLabel = this.getAttribute('no-notifications-label') || 'No notifications ';
        this.notifications = [];
        this.dropdownOpen = false;
        this.rpcId = 1;
        this.pollTimer = null;
    }

    connectedCallback() {
        this.renderBase();
        this.bindEvents();
        this.fetchNotifications();

        if (this.pollInterval > 0) {
            this.pollTimer = setInterval(() => this.fetchNotifications(), this.pollInterval);
        }
    }

    disconnectedCallback() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
    }

    renderBase() {
        this.classList.add('d-inline-block', 'position-relative');

        // Capture any light-DOM children provided with slot="icon" before we overwrite innerHTML
        const slottedIconNodes = Array.from(this.querySelectorAll('[slot="icon"]'));
        const slottedNoNotification = Array.from(this.querySelectorAll('[slot="noNotifications"]'));

        this.innerHTML = `
            <style>
              @keyframes horizontal-shaking {
               0% { transform: translateX(0) }
               25% { transform: translateX(5px) }
               50% { transform: translateX(-5px) }
               75% { transform: translateX(5px) }
               100% { transform: translateX(0) }
              }
              .wooble {
                animation: horizontal-shaking 0.2s ease-in-out;
                animation-iteration-count: 10;
              }
            </style>
            <button type="button"
                    class="btn btn-link text-decoration-none position-relative"
                    data-fucodo-role="toggle" style="height:100%;">
                <span data-fucodo-role="icon"></span>
                <span class="ms-1 badge bg-danger rounded-pill"
                      data-fucodo-role="count"
                      style="display:none;">0</span>
            </button>

            <div class="dropdown-menu dropdown-menu-start p-0 shadow"
                 data-fucodo-role="dropdown"
                 style="min-width:300px; max-height:360px; overflow-y:auto; display:none;right:0">
                <div class="d-flex justify-content-between align-items-center border-bottom p-3" data-fucodo-role="dropdown-title">
                    <span class="fw-semibold">${this.titleLabel}</span>
                    <button type="button"
                            class="ms-3 btn btn-sm"
                            data-fucodo-role="mark-all" title="${this.markAllAsReadLabel}">
                        &times;
                    </button>
                </div>
                <div data-fucodo-role="list" class="list-group list-group-flush"></div>
                <div data-fucodo-role="empty"
                     class="text-muted small text-center py-3"
                     style="display:none;">
                    ${this.noNotificationLabel}
                </div>
                <div data-fucodo-role="error"
                     class="text-danger small text-center py-2"
                     style="display:none;">
                    Error loading notifications
                </div>
            </div>
        `;

        this.$toggle = this.querySelector('[data-fucodo-role="toggle"]');
        this.$count = this.querySelector('[data-fucodo-role="count"]');
        this.$dropdown = this.querySelector('[data-fucodo-role="dropdown"]');
        this.$dropdownTitle = this.querySelector('[data-fucodo-role="dropdown-title"]');
        this.$list = this.querySelector('[data-fucodo-role="list"]');
        this.$empty = this.querySelector('[data-fucodo-role="empty"]');
        this.$error = this.querySelector('[data-fucodo-role="error"]');
        this.$markAll = this.querySelector('[data-fucodo-role="mark-all"]');
        this.$icon = this.querySelector('[data-fucodo-role="icon"]');

        // Re-attach any provided slotted icon nodes into the icon container
        if (this.$icon && slottedIconNodes.length) {
            slottedIconNodes.forEach(node => {
                try { node.removeAttribute('slot'); } catch (e) {}
                this.$icon.appendChild(node);
            });
        }

        if (this.$dropdown && slottedNoNotification.length) {
            this.$dropdown.innerHTML = '';
            slottedNoNotification.forEach(node => {
                try { node.removeAttribute('slot'); } catch (e) {}
                this.$dropdown.appendChild(node);
            });
        }
    }

    bindEvents() {
        this.$toggle.addEventListener('click', (e) => {
            e.preventDefault();
            this.dropdownOpen ? this.closeDropdown() : this.openDropdown();
        });

        document.addEventListener('click', (e) => {
            if (!this.contains(e.target)) {
                this.closeDropdown();
            }
        });

        this.$markAll.addEventListener('click', (e) => {
            e.preventDefault();
            this.markAllRead();
        });

        this.$list.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-fucodo-role="delete"]');
            if (!btn) {
                return;
            }
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            this.markRead(id);
        });

        window.addEventListener('focus', (e) => {
            this.fetchNotifications();
        });
    }

    openDropdown() {
        this.dropdownOpen = true;
        this.$dropdown.style.display = 'block';
        this.fetchNotifications();
    }

    closeDropdown() {
        this.dropdownOpen = false;
        this.$dropdown.style.display = 'none';
    }

    updateBadge() {
        let previousCount = this.count ?? 0;
        this.count = this.notifications.length;
        let badgeContent = this.count > 9 ? '9+' : this.count;
        if (this.count > 0) {
            this.$count.textContent = badgeContent;
            this.$count.style.display = 'inline-block';
        } else {
            this.$count.style.display = 'none';
        }

        if (previousCount !== this.count) {
            this.$count.classList.add('wooble');
            const event = new CustomEvent('fucodo-notifications-count-changed', {detail: {count: this.count}});
            this.dispatchEvent(event);
        }
    }

    renderList() {
        this.$list.innerHTML = '';
        this.$error.style.display = 'none';
        this.$dropdownTitle.classList.remove('d-none');

        if (!this.notifications.length) {
            this.$empty.style.display = 'block';
            this.$dropdownTitle.classList.add('d-none');
            this.updateBadge();
            return;
        }

        this.$empty.style.display = 'none';

        this.notifications.forEach((n) => {
            const item = document.createElement('div');
            item.className = 'list-group-item d-flex justify-content-between align-items-start';

            const subject = this.escapeHtml(n.subject || '(no subject)');
            const message = n.message ? this.escapeHtml(n.message) : '';

            item.innerHTML = `
                <div class="me-2 text-start position-relative flex-grow-1">
                    ${n.link ? `<a href="${n.link}" target="_blank" class="link-offset-2 link-underline link-underline-opacity-0 stretched-link"><div class="fw-semibold">${subject}</div></a>` : `<div class="fw-semibold">${subject}</div>`}
                    ${message ? `<div class="small text-muted">${message}</div>` : ''}
                </div>
                <button type="button"
                        class="btn btn-sm btn-link text-decoration-none text-muted px-1 py-0"
                        data-fucodo-role="delete"
                        data-id="${n.id}" title="${this.markAsReadLabel}">
                    &times;
                </button>
            `;
            this.$list.appendChild(item);
        });

        this.updateBadge();
    }

    async fetchNotifications() {
        try {
            const body = {
                jsonrpc: '2.0',
                method: 'notifications.list',
                params: {limit: 50},
                id: this.rpcId++,
            };

            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: this.buildHeaders(),
                body: JSON.stringify(body),
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const data = await response.json();
            if (data.error) {
                throw new Error(data.error.message || 'JSON-RPC error');
            }

            const list = data.result && Array.isArray(data.result.notifications)
                ? data.result.notifications
                : [];

            this.notifications = list;
            this.renderList();
        } catch (e) {
            console.error('Notification fetch failed', e);
            this.$error.style.display = 'block';
        }
    }

    async markRead(id) {
        try {
            const body = {
                jsonrpc: '2.0',
                method: 'notifications.markRead',
                params: {id},
                id: this.rpcId++,
            };

            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: this.buildHeaders(),
                body: JSON.stringify(body),
            });

            const data = await response.json();
            if (data.error || data.result?.status === 'error') {
                throw new Error(data.error?.message || data.result?.message || 'Unable to mark read');
            }

            this.notifications = this.notifications.filter((n) => String(n.id) !== String(id));
            this.renderList();
        } catch (e) {
            console.error('Mark read failed', e);
        }
    }

    async markAllRead() {
        if (!this.notifications.length) {
            return;
        }

        const ids = this.notifications
            .map((n) => n.id);

        if (!ids.length) {
            return;
        }

        try {
            const body = {
                jsonrpc: '2.0',
                method: 'notifications.markRead',
                params: {ids},
                id: this.rpcId++,
            };

            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: this.buildHeaders(),
                body: JSON.stringify(body),
            });

            const data = await response.json();
            if (data.error || data.result?.status === 'error') {
                throw new Error(data.error?.message || data.result?.message || 'Unable to mark all read');
            }

            this.notifications = [];
            this.renderList();
            this.closeDropdown();
        } catch (e) {
            console.error('Mark all read failed', e);
        }
    }

    buildHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };

        const csrf = document.querySelector('meta[name="X-CSRF-Token"]');
        if (csrf && csrf.content) {
            headers['X-CSRF-Token'] = csrf.content;
        }

        return headers;
    }

    escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
}

customElements.define('fucodo-notifications', FucodoNotifications);
