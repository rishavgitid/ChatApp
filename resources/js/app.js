import './bootstrap';
import 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('token');
    const guestLinks = document.querySelectorAll('.guest-only');
    const authLinks = document.querySelectorAll('.auth-only');

    if (token) {
        guestLinks.forEach(el => el.classList.add('d-none'));
        authLinks.forEach(el => el.classList.remove('d-none'));
    } else {
        guestLinks.forEach(el => el.classList.remove('d-none'));
        authLinks.forEach(el => el.classList.add('d-none'));
    }

    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                await window.axios.post('/api/logout', {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });
            } catch (error) {
                console.error('Logout error', error);
            }
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        });
    }

    // Global Notification Listener
    const currentUser = JSON.parse(localStorage.getItem('user'));
    if (currentUser && window.Echo) {
        window.Echo.private(`chat.${currentUser.id}`)
            .listen('MessageSent', (e) => {
                console.log('Global Listener: Message received', e.message);

                // Check if we are currently chatting with this user
                if (window.activeChatUserId && window.activeChatUserId == e.message.sender_id) {
                    return; // Do nothing, chat.blade.php handles it (marks read, appends)
                }

                // Update Badge
                const badge = document.getElementById('global-badge');
                if (badge) {
                    let count = parseInt(badge.innerText || 0);
                    badge.innerText = count + 1;
                    badge.classList.remove('d-none');
                }

                // Show Toast
                const toastEl = document.getElementById('liveToast');
                const toastBody = document.getElementById('toast-body');
                if (toastEl && toastBody) {
                    toastBody.innerText = `${e.message.sender_id}: ${e.message.message}`;
                    const toast = new bootstrap.Toast(toastEl);
                    toast.show();
                }
            });

        // Fetch Initial Unread Count
        const fetchUnreadCount = async () => {
            try {
                const res = await window.axios.get('/api/unread-count', {
                    headers: { Authorization: `Bearer ${token}` }
                });
                const badge = document.getElementById('global-badge');
                if (badge && res.data.count > 0) {
                    badge.innerText = res.data.count;
                    badge.classList.remove('d-none');
                }
            } catch (e) { console.error(e); }
        };
        fetchUnreadCount();
    }
});
