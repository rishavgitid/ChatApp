@extends('layouts.app')

@section('content')
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #c1c1c1; 
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8; 
    }

    .message-bubble {
        max-width: 75%;
        padding: 10px 15px;
        border-radius: 18px;
        position: relative;
        word-wrap: break-word;
    }
    .message-me {
        background-color: #0d6efd;
        color: white;
        border-bottom-right-radius: 4px;
    }
    .message-them {
        background-color: white;
        color: #212529;
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .list-group-item.active {
        background-color: #f0f8ff;
        color: #000;
        border-color: #dee2e6;
        border-left: 4px solid #0d6efd;
    }
</style>

<div class="container-fluid p-0" style="height: calc(100vh - 60px);">
    <div class="row g-0 h-100">
        <div class="col-12 col-md-4 col-lg-3 h-100 d-flex flex-column border-end bg-white" id="users-sidebar">
            <div class="p-3 bg-light border-bottom">
                <h5 class="fw-bold mb-3">Chats</h5>
                <input type="text" id="user-search" class="form-control form-control-sm rounded-pill" placeholder="Search contacts...">
            </div>
            <div class="flex-grow-1 overflow-y-auto custom-scrollbar">
                <div class="list-group list-group-flush" id="users-list">
                    <div class="text-center mt-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-8 col-lg-9 h-100 d-none d-md-flex flex-column bg-light" id="chat-area">
            <div id="empty-state" class="d-none d-md-flex flex-column align-items-center justify-content-center h-100 text-muted">
                <div class="display-1 mb-3">💬</div>
                <h4>Select a conversation</h4>
                <p>Choose a contact from the left to start chatting.</p>
            </div>
            <div id="chat-interface" class="d-none flex-column h-100">
                
                <div class="p-3 bg-white border-bottom d-flex align-items-center shadow-sm z-1">
                    <button class="btn btn-sm btn-light d-md-none me-3 border rounded-circle" id="back-btn">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    
                    <div class="d-flex align-items-center gap-2">
                        <div id="chat-header-avatar" class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold" id="chat-header-name">User Name</h6>
                        </div>
                    </div>
                </div>
                
                <div class="flex-grow-1 p-3 overflow-y-auto custom-scrollbar" id="chat-messages" style="background-color: #f0f2f5;">
                </div>
                <div class="p-3 bg-white border-top">
                    <form id="chat-form">
                        <div class="input-group">
                            <input type="text" id="message-input" class="form-control border-0 bg-light" placeholder="Type a message..." autocomplete="off" style="border-radius: 20px 0 0 20px; padding-left: 20px;">
                            <button class="btn btn-primary" type="submit" style="border-radius: 0 20px 20px 0; padding-right: 20px;">
                                Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
    let selectedUser = null;
    let currentUser = null;
    let usersData = [];
    
    try {
        currentUser = JSON.parse(localStorage.getItem('user'));
    } catch(e) {}
    
    if (!localStorage.getItem('token') || !currentUser) {
        window.location.href = '/login';
    }

    const token = localStorage.getItem('token');
    const axiosConfig = { headers: { Authorization: `Bearer ${token}` } };

    const dom = {
        usersList: document.getElementById('users-list'),
        chatArea: document.getElementById('chat-area'),
        usersSidebar: document.getElementById('users-sidebar'),
        emptyState: document.getElementById('empty-state'),
        chatInterface: document.getElementById('chat-interface'),
        chatMessages: document.getElementById('chat-messages'),
        chatHeaderName: document.getElementById('chat-header-name'),
        chatHeaderAvatar: document.getElementById('chat-header-avatar'),
        messageInput: document.getElementById('message-input'),
        searchInput: document.getElementById('user-search'),
        backBtn: document.getElementById('back-btn'),
        chatForm: document.getElementById('chat-form')
    };
    async function fetchUsers() {
        try {
            const response = await axios.get('/api/users', axiosConfig);
            usersData = response.data.filter(u => u.id !== currentUser.id);
            renderUserList(usersData);
        } catch(e) { console.error('Error fetching users:', e); }
    }
    function renderUserList(users) {
        dom.usersList.innerHTML = '';
        if(users.length === 0) {
            dom.usersList.innerHTML = '<div class="p-4 text-center text-muted">No contacts found</div>';
            return;
        }

        users.forEach(user => {
            const isActive = selectedUser && selectedUser.id === user.id;
            const item = document.createElement('a');
            item.className = `list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 ${isActive ? 'active' : ''}`;
            item.href = '#';
            item.dataset.userId = user.id;
            
            const initials = user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

            const badgeClass = user.unread_count > 0 ? '' : 'd-none';

            item.innerHTML = `
                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px; min-width: 45px;">
                    ${initials}
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-truncate">${user.name}</h6>
                        <span class="badge bg-danger rounded-pill ${badgeClass}" id="badge-${user.id}">${user.unread_count}</span>
                    </div>
                    <small class="text-muted text-truncate d-block">Click to start conversation</small>
                </div>
            `;

            item.onclick = (e) => {
                e.preventDefault();
                handleUserSelect(user, item);
            };
            dom.usersList.appendChild(item);
        });
    }

    async function handleUserSelect(user, listItem) {
        selectedUser = user;

        document.querySelectorAll('.list-group-item').forEach(el => el.classList.remove('active'));
        listItem.classList.add('active');

        const badge = document.getElementById(`badge-${user.id}`);
        if(badge && !badge.classList.contains('d-none')) {
            badge.innerText = '0';
            badge.classList.add('d-none');
        }

        toggleMobileView('chat');

        const initials = user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        dom.chatHeaderName.innerText = user.name;
        dom.chatHeaderAvatar.innerHTML = initials;
        
        dom.emptyState.classList.remove('d-md-flex');
        dom.emptyState.classList.add('d-none');
        dom.chatInterface.classList.remove('d-none');
        dom.chatInterface.classList.add('d-flex');

        await fetchMessages(user.id);
    }

    async function fetchMessages(userId) {
        dom.chatMessages.innerHTML = '<div class="text-center mt-5"><div class="spinner-border text-primary"></div></div>';
        try {
            const response = await axios.get(`/api/messages/${userId}`, axiosConfig);
            renderMessages(response.data);
            
        } catch(e) { console.error(e); }
    }

    function renderMessages(messages) {
        dom.chatMessages.innerHTML = '';
        
        if (messages.length === 0) {
            dom.chatMessages.innerHTML = '<div class="text-center text-muted mt-5"><small>Say hello! 👋</small></div>';
            return;
        }

        let lastDate = null;

        messages.forEach(msg => {
            const isMe = msg.sender_id === currentUser.id;
            
            const msgDate = new Date(msg.created_at).toLocaleDateString();
            if (msgDate !== lastDate) {
                const dateDiv = document.createElement('div');
                dateDiv.className = 'text-center my-3';
                dateDiv.innerHTML = `<span class="badge bg-light text-secondary border fw-normal">${msgDate}</span>`;
                dom.chatMessages.appendChild(dateDiv);
                lastDate = msgDate;
            }

            const div = document.createElement('div');
            div.className = `d-flex mb-3 ${isMe ? 'justify-content-end' : 'justify-content-start'}`;
            
            const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            div.innerHTML = `
                <div class="message-bubble shadow-sm ${isMe ? 'message-me' : 'message-them'}">
                    <div>${msg.message}</div>
                    <div class="text-end mt-1" style="font-size: 0.65rem; opacity: 0.7;">${time}</div>
                </div>
            `;
            dom.chatMessages.appendChild(div);
        });
        scrollToBottom();
    }

    dom.chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = dom.messageInput.value.trim();
        if (!message || !selectedUser) return;

        dom.messageInput.value = '';

        try {
            const optimisticDiv = document.createElement('div');
            optimisticDiv.className = `d-flex mb-3 justify-content-end opacity-75`;
            optimisticDiv.id = 'temp-msg';
            optimisticDiv.innerHTML = `
                <div class="message-bubble message-me shadow-sm">
                    <div>${message}</div>
                    <div class="text-end mt-1" style="font-size: 0.65rem;">Sending...</div>
                </div>
            `;
            dom.chatMessages.appendChild(optimisticDiv);
            scrollToBottom();

            const res = await axios.post('/api/messages', {
                receiver_id: selectedUser.id,
                message: message
            }, axiosConfig);

            document.getElementById('temp-msg')?.remove();
            
            const realDiv = document.createElement('div');
            realDiv.className = `d-flex mb-3 justify-content-end`;
            realDiv.innerHTML = `
                <div class="message-bubble message-me shadow-sm">
                    <div>${res.data.message.message}</div>
                    <div class="text-end mt-1" style="font-size: 0.65rem;">Now</div>
                </div>
            `;
            dom.chatMessages.appendChild(realDiv);
            scrollToBottom();

        } catch (error) {
            console.error(error);
            alert('Failed to send message');
        }
    });

    if (window.Echo) {
        window.Echo.private(`chat.${currentUser.id}`)
            .listen('MessageSent', (e) => {
                if (selectedUser && e.message.sender_id === selectedUser.id) {
                    const div = document.createElement('div');
                    div.className = `d-flex mb-3 justify-content-start`;
                    div.innerHTML = `
                        <div class="message-bubble message-them shadow-sm">
                            <div>${e.message.message}</div>
                            <div class="text-end mt-1" style="font-size: 0.65rem;">Just now</div>
                        </div>
                    `;
                    dom.chatMessages.appendChild(div);
                    scrollToBottom();
                    
                    axios.post('/api/messages/mark-read', { message_id: e.message.id }, axiosConfig);
                } else {
                    const badge = document.getElementById(`badge-${e.message.sender_id}`);
                    if (badge) {
                        let count = parseInt(badge.innerText || 0);
                        badge.innerText = count + 1;
                        badge.classList.remove('d-none');
                    }
                }
            });
    }

    function toggleMobileView(view) {
        if (view === 'chat') {
            dom.usersSidebar.classList.remove('d-flex');
            dom.usersSidebar.classList.add('d-none', 'd-md-flex');
            
            dom.chatArea.classList.remove('d-none');
            dom.chatArea.classList.add('d-flex');
        } else {
            dom.usersSidebar.classList.remove('d-none');
            dom.usersSidebar.classList.add('d-flex');
            
            dom.chatArea.classList.add('d-none');
            dom.chatArea.classList.remove('d-flex', 'd-md-flex');
            setTimeout(() => dom.chatArea.classList.add('d-md-flex'), 50); 
            selectedUser = null; 
            document.querySelectorAll('.list-group-item').forEach(el => el.classList.remove('active'));
        }
    }

    function scrollToBottom() {
        dom.chatMessages.scrollTop = dom.chatMessages.scrollHeight;
    }

    dom.searchInput.addEventListener('keyup', (e) => {
        const term = e.target.value.toLowerCase();
        const filtered = usersData.filter(u => u.name.toLowerCase().includes(term));
        renderUserList(filtered);
    });

    dom.backBtn.addEventListener('click', () => {
        toggleMobileView('users');
    });
    fetchUsers();
</script>
@endsection