@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-0" style="height: 90vh;">
        
        <!-- Users List -->
        <div class="col-12 col-md-4 h-100 border-end bg-white shadow-sm"
             id="users-panel">
             
            <div class="p-3 bg-light border-bottom sticky-top">
                <h5 class="mb-0 fw-bold">Contacts</h5>
            </div>

            <div class="list-group list-group-flush overflow-auto"
                 id="users-list">
            </div>
        </div>

        <!-- Chat Area -->
        <div class="col-12 col-md-8 h-100 d-flex flex-column bg-white shadow-sm d-none d-md-flex"
             id="chat-panel">

            <div class="p-3 border-bottom bg-light d-flex align-items-center">
                <button class="btn btn-sm btn-outline-secondary me-2 d-md-none"
                        id="back-btn">
                    ←
                </button>
                <h5 class="mb-0 fw-bold" id="chat-header">
                    Select a user to chat
                </h5>
            </div>

            <div class="flex-grow-1 p-3 p-md-4 overflow-auto"
                 id="chat-messages"
                 style="background-color: #f8f9fa;">
            </div>

            <div class="p-3 border-top bg-light">
                <form id="chat-form" class="d-none">
                    <div class="input-group">
                        <input type="text"
                               id="message-input"
                               class="form-control"
                               placeholder="Type a message..."
                               autocomplete="off">
                        <button class="btn btn-primary" type="submit">
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>


<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        const usersPanel = document.getElementById('users-panel');
        const chatPanel = document.getElementById('chat-panel');
        const backBtn = document.getElementById('back-btn');
        const usersList = document.getElementById('users-list');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');
        const messagesContainer = document.getElementById('chat-messages');
        const globalBadge = document.getElementById('global-badge');

        let selectedUser = null;
        let currentUser = null;
        let token = localStorage.getItem('token');

        try {
            currentUser = JSON.parse(localStorage.getItem('user'));
        } catch (e) {
            currentUser = null;
        }

        if (!token || !currentUser) {
            window.location.href = '/login';
            return;
        }
        function showChatMobile() {
            if (window.innerWidth < 768) {
                usersPanel?.classList.add('d-none');
                chatPanel?.classList.remove('d-none');
                chatPanel?.classList.add('d-flex');
            }
        }

        function showUsersMobile() {
            if (window.innerWidth < 768) {
                chatPanel?.classList.add('d-none');
                usersPanel?.classList.remove('d-none');
            }
        }

        backBtn?.addEventListener('click', showUsersMobile);
        async function fetchUsers() {
            try {
                const { data: users } = await axios.get('/api/users', {
                    headers: { Authorization: `Bearer ${token}` }
                });

                usersList.innerHTML = '';

                let totalUnread = 0;

                users.forEach(user => {
                    if (user.id === currentUser.id) return;

                    totalUnread += user.unread_count || 0;

                    const item = document.createElement('a');
                    item.href = '#';
                    item.dataset.userId = user.id;
                    item.className =
                        'list-group-item list-group-item-action d-flex align-items-center gap-2 py-3';

                    item.innerHTML = `
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                            style="width:40px;height:40px;">
                            ${escapeHtml(user.name.charAt(0).toUpperCase())}
                        </div>
                        <div>
                            <div class="fw-bold">${escapeHtml(user.name)}</div>
                            <small class="text-muted">Click to chat</small>
                        </div>
                        <span class="badge bg-danger rounded-pill ms-auto ${user.unread_count ? '' : 'd-none'}"
                            id="badge-${user.id}">
                            ${user.unread_count || 0}
                        </span>
                    `;

                    item.addEventListener('click', (e) => {
                        e.preventDefault();
                        setActiveUser(item);
                        clearUserBadge(user.id);
                        selectUser(user);
                    });

                    usersList.appendChild(item);
                });

                updateGlobalBadge(totalUnread);

            } catch (e) {
                console.error('Fetch users error:', e);
            }
        }

        function setActiveUser(item) {
            document.querySelectorAll('.list-group-item')
                .forEach(el => el.classList.remove('active'));
            item.classList.add('active');
        }

        function updateGlobalBadge(count) {
            if (!globalBadge) return;
            globalBadge.innerText = count;
            globalBadge.classList.toggle('d-none', count === 0);
        }

        function clearUserBadge(userId) {
            const badge = document.getElementById(`badge-${userId}`);
            if (!badge || badge.classList.contains('d-none')) return;

            const count = parseInt(badge.innerText) || 0;
            badge.innerText = '0';
            badge.classList.add('d-none');

            if (globalBadge) {
                let globalCount = parseInt(globalBadge.innerText) || 0;
                globalCount = Math.max(0, globalCount - count);
                updateGlobalBadge(globalCount);
            }
        }

        async function selectUser(user) {
            selectedUser = user;

            document.getElementById('chat-header').innerText =
                `Chat with ${user.name}`;

            chatForm.classList.remove('d-none');

            showChatMobile();

            await fetchMessages(user.id);

            await axios.post('/api/messages/mark-all-read', {
                sender_id: user.id
            }, {
                headers: { Authorization: `Bearer ${token}` }
            }).catch(() => {});
        }

        async function fetchMessages(userId) {
            try {
                const { data } = await axios.get(`/api/messages/${userId}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                renderMessages(data);

            } catch (e) {
                console.error('Fetch messages error:', e);
            }
        }

        function renderMessages(messages) {
            messagesContainer.innerHTML = '';

            messages.forEach(msg => {
                appendMessage(msg, msg.sender_id === currentUser.id);
            });

            scrollToBottom();
        }

        function appendMessage(msg, isMe) {
            const div = document.createElement('div');
            div.className = `d-flex mb-3 ${isMe ? 'justify-content-end' : 'justify-content-start'}`;

            div.innerHTML = `
                <div class="p-3 rounded-3 shadow-sm ${isMe ? 'bg-primary text-white' : 'bg-white border'}"
                    style="max-width:70%;">
                    <div>${escapeHtml(msg.message)}</div>
                    <small class="opacity-75 d-block text-end mt-1" style="font-size:0.7em;">
                        ${new Date(msg.created_at).toLocaleTimeString()}
                    </small>
                </div>
            `;

            messagesContainer.appendChild(div);
        }

        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        chatForm?.addEventListener('submit', async (e) => {
            e.preventDefault();

            const message = messageInput.value.trim();
            if (!message || !selectedUser) return;

            messageInput.value = '';

            try {
                const { data } = await axios.post('/api/messages', {
                    receiver_id: selectedUser.id,
                    message
                }, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                appendMessage(data.message, true);
                scrollToBottom();

            } catch (error) {
                console.error('Send message error:', error);
            }
        });

        if (window.Echo && currentUser?.id) {
            window.Echo.private(`chat.${currentUser.id}`)
                .listen('MessageSent', (e) => {

                    const senderId = e.message.sender_id;

                    if (selectedUser && senderId === selectedUser.id) {
                        appendMessage(e.message, false);
                        scrollToBottom();
                    } else {
                        incrementBadge(senderId);
                    }
                });
        }

        function incrementBadge(userId) {
            const badge = document.getElementById(`badge-${userId}`);
            if (!badge) return;

            let count = parseInt(badge.innerText) || 0;
            badge.innerText = count + 1;
            badge.classList.remove('d-none');

            let globalCount = parseInt(globalBadge?.innerText) || 0;
            updateGlobalBadge(globalCount + 1);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.innerText = text;
            return div.innerHTML;
        }
        fetchUsers();
    });
</script>
@endsection
