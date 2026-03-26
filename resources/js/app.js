import './bootstrap.js';

// Initialize Lucide icons
import * as lucide from 'lucide';
window.lucide = lucide;

// Initialize icons when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
});

// Auto-hide flash messages after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert-slide');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    });
}, 5000);

// Common AJAX function
async function makeRequest(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                ...options.headers
            },
            ...options
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('Request failed:', error);
        throw error;
    }
}

function updateNotificationCount() {
    const list = document.getElementById('notificationList');
    const countSpan = document.querySelector('#notificationDropdown .border-b span.text-xs');
    const badge = document.querySelector('#notificationBell span');
    const count = list ? list.querySelectorAll('.notification-item').length : 0;
    if (countSpan) countSpan.textContent = count + ' item(s)';
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
}

function dismissNotification(button) {
    event.stopPropagation();
    const item = button.closest('.notification-item');
    if (!item) return;
    const type = item.getAttribute('data-type');
    const id = item.getAttribute('data-id');
    item.remove();
    updateNotificationCount();
    if (type === 'system' && id) {
        fetch('/notifications/dismiss', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: 'id=' + encodeURIComponent(id)
        }).catch(err => console.error('Failed to dismiss:', err));
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const bell = document.getElementById('notificationBell');
    const dropdown = document.getElementById('notificationDropdown');
    if (!bell || !dropdown) return;

    bell.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', () => {
        if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
        }
    });

    updateNotificationCount();
});
