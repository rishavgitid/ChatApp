@extends('layouts.app')

@section('content')
<div class="row" style="height: 80vh;">
    <!-- Users List -->
    <div class="col-md-4 h-100 overflow-y-auto border-end bg-white rounded-start shadow-sm">
        <div class="p-3 bg-light border-bottom sticky-top">
            <h5 class="mb-0 fw-bold">Contacts</h5>
        </div>
        <div class="list-group list-group-flush" id="users-list">
            <!-- Populated by JS -->
        </div>
    </div>

    <!-- Chat Area -->
    <div class="col-md-8 h-100 d-flex flex-column bg-white rounded-end shadow-sm">
        <div class="p-3 border-bottom bg-light d-flex align-items-center">
            <h5 class="mb-0 fw-bold" id="chat-header">Select a user to chat</h5>
        </div>
        
        <div class="flex-grow-1 p-4 overflow-y-auto" id="chat-messages" style="background-color: #f8f9fa;">
            <!-- Messages go here -->
        </div>

        <div class="p-3 border-top bg-light">
            <form id="chat-form" class="d-none">
                <div class="input-group">
                    <input type="text" id="message-input" class="form-control" placeholder="Type a message..." autocomplete="off">
                    <button class="btn btn-primary" type="submit">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="module">
    let selectedUser = null;
    let currentUser = null;
    try {
        currentUser = JSON.parse(localStorage.getItem('user'));
    } catch(e) {}
    
    if (!localStorage.getItem('token') || !currentUser) {
        window.location.href = '/login';
    }

    const token = localStorage.getItem('token');
    
    // Fetch Users
    async function fetchUsers() {
        try {
            const response = await axios.get('/api/users', {
                headers: { Authorization: `Bearer ${token}` }
            });
            const users = response.data;
            const list = document.getElementById('users-list');
            list.innerHTML = '';
            users.forEach(user => {
                if (user.id === currentUser.id) return;
                const item = document.createElement('a');
                item.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2 py-3';
                item.href = '#';
                item.dataset.userId = user.id; // Store ID for easy access
                
                // Badge Logic
                const badgeHtml = user.unread_count > 0 
                    ? `<span class="badge bg-danger rounded-pill ms-auto" id="badge-${user.id}">${user.unread_count}</span>` 
                    : `<span class="badge bg-danger rounded-pill ms-auto d-none" id="badge-${user.id}">0</span>`;

                item.innerHTML = `
                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        ${user.name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                       <div class="fw-bold">${user.name}</div>
                       <small class="text-muted">Click to chat</small>
                    </div>
                    ${badgeHtml}
                `;
                item.onclick = (e) => {
                    e.preventDefault();
                    // Mark active
                    document.querySelectorAll('.list-group-item').forEach(el => el.classList.remove('active'));
                    item.classList.add('active');
                    
                    // Clear Badge and Decrement Global
                    const badge = document.getElementById(`badge-${user.id}`);
                    if(badge && !badge.classList.contains('d-none')) {
                        const count = parseInt(badge.innerText);
                         // Subtract from global
                        const globalBadge = document.getElementById('global-badge');
                        if(globalBadge) {
                             let globalCount = parseInt(globalBadge.innerText || 0);
                             globalCount = Math.max(0, globalCount - count);
                             globalBadge.innerText = globalCount;
                             if(globalCount === 0) globalBadge.classList.add('d-none');
                        }

                        badge.innerText = '0';
                        badge.classList.add('d-none');
                    }
                    
                    selectUser(user);
                };
                list.appendChild(item);
            });
        } catch(e) { console.error(e); }
    }

    // Select User
    async function selectUser(user) {
        selectedUser = user;
        document.getElementById('chat-header').innerText = `Chat with ${user.name}`;
        document.getElementById('chat-form').classList.remove('d-none');
        await fetchMessages(user.id);
    }

    // Fetch Messages
    async function fetchMessages(userId) {
        try {
            const response = await axios.get(`/api/messages/${userId}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            renderMessages(response.data);
            
            // Should also mark messages as read on server? Maybe add an endpoint or modify getMessages to mark read?
            // The prompt didn't explicitly ask for 'read receipts' but implied unread logic.
            // For now, seeing them clears the visual badge.
        } catch(e) { console.error(e); }
    }

    function renderMessages(messages) {
        const container = document.getElementById('chat-messages');
        container.innerHTML = '';
        messages.forEach(msg => {
            const isMe = msg.sender_id === currentUser.id;
            const div = document.createElement('div');
            div.className = `d-flex mb-3 ${isMe ? 'justify-content-end' : 'justify-content-start'}`;
            // Different colors for me vs them
            div.innerHTML = `
                <div class="p-3 rounded-3 shadow-sm ${isMe ? 'bg-primary text-white' : 'bg-white text-dark border'}" style="max-width: 70%;">
                    <div>${msg.message}</div>
                    <small class="opacity-75 d-block text-end mt-1" style="font-size: 0.7em;">
                        ${new Date(msg.created_at).toLocaleTimeString()}
                    </small>
                </div>
            `;
            container.appendChild(div);
        });
        container.scrollTop = container.scrollHeight;
    }

    // Send Message
    document.getElementById('chat-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.getElementById('message-input');
        const message = input.value;
        if (!message || !selectedUser) return;

        input.value = '';

        try {
            const res = await axios.post('/api/messages', {
                receiver_id: selectedUser.id,
                message: message
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });
            // Append immediately
            const container = document.getElementById('chat-messages');
            const div = document.createElement('div');
            div.className = `d-flex mb-3 justify-content-end`;
            div.innerHTML = `
                <div class="p-3 rounded-3 shadow-sm bg-primary text-white" style="max-width: 70%;">
                    <div>${res.data.message.message}</div>
                    <small class="opacity-75 d-block text-end mt-1" style="font-size: 0.7em;">Now</small>
                </div>
            `;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        } catch (error) {
            console.error(error);
        }
    });

    // Real-time Listener (Chat specific stuff)
    if (window.Echo) {
        window.Echo.private(`chat.${currentUser.id}`)
            .listen('MessageSent', (e) => {
                // If chatting with this user, append message
                if (selectedUser && e.message.sender_id === selectedUser.id) {
                    const container = document.getElementById('chat-messages');
                    const div = document.createElement('div');
                    div.className = `d-flex mb-3 justify-content-start`;
                    div.innerHTML = `
                        <div class="p-3 rounded-3 shadow-sm bg-white text-dark border" style="max-width: 70%;">
                            <div>${e.message.message}</div>
                            <small class="opacity-75 d-block text-end mt-1" style="font-size: 0.7em;">Just now</small>
                        </div>
                    `;
                    container.appendChild(div);
                    container.scrollTop = container.scrollHeight;
                    
                    // Mark as read immediately since we saw it
                    axios.post('/api/messages/mark-read', { message_id: e.message.id }, {
                        headers: { Authorization: `Bearer ${token}` }
                    });

                } else {
                    // Update user list badge if not selected
                    const badge = document.getElementById(`badge-${e.message.sender_id}`);
                    if (badge) {
                        let count = parseInt(badge.innerText || 0);
                        badge.innerText = count + 1;
                        badge.classList.remove('d-none');
                    }
                }
            });
    }

    fetchUsers();
</script>
@endsection
