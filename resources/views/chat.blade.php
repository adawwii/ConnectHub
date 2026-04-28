@php
    
use Carbon\Traits\Date;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/js/app.js'])
</head>
<body class="bg-gray-100 h-screen flex flex-col">
<!-- Top Navbar -->
<div class="bg-white shadow px-4 py-3 flex justify-between items-center">

    <h1 class="font-bold text-lg">Chat App</h1>

    <!-- Notifications -->
    <div class="relative">
        <button onclick="toggleNotifications()" class="relative">
            🔔
            {{-- using websockets for notification --}}
            <span id="notifBadge" class="absolute -top-1 -right-2 bg-red-500 text-white text-xs px-1 rounded-full {{ auth()->user()->unreadNotifications->count() == 0 ? 'hidden' : '' }}">
                {{ auth()->user()->unreadNotifications->count() }}
            </span>
        </button>

        <!-- Dropdown -->
        <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white shadow-xl rounded-lg z-50 border">
            <div class="p-3 border-b font-semibold flex justify-between items-center">
                <span>Notifications</span>
            </div>
            <div id="notificationsContainer" class="max-h-60 overflow-y-auto">
                <!-- JS will render notifications here -->
                <div class="p-3 text-sm text-gray-500 text-center">Loading...</div>
            </div>
        </div>
    </div>

</div>

<!-- Main Layout -->
<div class="flex flex-1 overflow-hidden">

    <!-- Sidebar -->
    <div class="w-full md:w-1/3 lg:w-1/4 bg-white border-r flex flex-col">

        <!-- Search Chats -->
        <div class="p-3 border-b">
            <input 
                type="text" 
                placeholder="Search chats..."
                onkeyup="filterChats(this.value)"
                class="w-full border rounded-lg px-3 py-2"
            >
        </div>

        <!-- Add Friend -->
        <div class="p-3 border-b">
            <form action="{{ route('contact-add') }}" method="POST"  onsubmit="searchUser(event)">
                @csrf

            <input 
                id="emailSearch"
                name="email"
                type="text" 
                placeholder="Search by email..."
                class="w-full border rounded-lg px-3 py-2 mb-2"
                required
            >
            <button type="submit" id="addBtn"  class="w-full bg-blue-500 text-white py-2 rounded-lg">
                Add Friend
            </button>
            </form>
        </div>

        <!-- Users List -->
        <div id="chatList" class="flex-1 overflow-y-auto">
            @foreach ($contacts as $contact )    
            <div id="contact-{{ $contact->id }}" 
                 onclick="openChat('{{ $contact->id }}', '{{ $contact->name }}')"
                 class="p-3 border-b cursor-pointer hover:bg-gray-100 chat-item">
                {{ $contact->name }}
            </div>
            @endforeach

            

        </div>
    </div>

    <!-- Chat Area -->
    <div class="hidden md:flex flex-1 flex-col">
@include('components.flash')

        <!-- Header -->
        <div id="chatHeader" class="p-4 bg-white border-b font-semibold text-gray-700">
            Select a contact to start chatting
        </div>

        <!-- Messages -->
        <div id="messages" class="flex-1 p-4 overflow-y-auto space-y-3">
        </div>

        <!-- Input -->
        <form onsubmit="sendMessage(event)">
        <div class="p-3 bg-white border-t flex gap-2">
                <input 
                id="messageInput"
                type="text"
                class="flex-1 border rounded-lg px-3 py-2"
                placeholder="Type message..."
                >
                <button type='submit'  class="bg-blue-500 text-white px-4 rounded-lg">
                    Send
                </button>
            </div>
        </form>

    </div>

</div>

<!-- Mobile Chat Toggle -->
<div class="md:hidden fixed bottom-4 right-4">
    <button onclick="toggleChat()" class="bg-blue-500 text-white px-4 py-2 rounded-full shadow">
        Open Chat
</button>
</div>
    <script>
    let activeContactId = null;
    let activeChatId = null;

    window.addEventListener('DOMContentLoaded', () => {
        loadNotifications();
        receiveFallbackMessages();

        Echo.private('user.{{ auth()->id() }}')
            .subscribed(() => {
                console.log('Subscribed to user.{{ auth()->id()}} channel');
            })
            .error((error) => {
                console.error('Subscription error:', error);
            })
            .notification((notification) => {
                console.log('New notification:', notification);
                addNotificationToUI(notification, true);

                // If friend request was accepted, add the new friend to the sidebar
                if (notification.data_type === 'friend_request_accepted') {
                    addContactToSidebar({
                        id: notification.sender_id,
                        name: notification.sender_name
                    });
                }
            });
    });
        

    // Open a chat and fetch history
    async function openChat(id, name) {
        activeContactId = id;
        
        // UI Updates
        document.getElementById('chatHeader').innerText = name;
        highlightContact(id);

        const container = document.getElementById('messages');
        container.innerHTML = '<div class="text-center text-gray-400 py-10 italic">Loading conversation...</div>';

        try {
            const response = await fetch(`/chat/messages/${id}`,{credentials: 'same-origin'});
            const messages = await response.json();
            console.log(messages.messageData);
            const chatId=messages.chat_id;

            
            container.innerHTML = ''; // Clear loader
            if (messages.messageData.length === 0) {
                container.innerHTML = '<div class="text-center text-gray-400 py-10 italic">No messages yet. Say hi!</div>';
            } else {
                messages.messageData.forEach(msg => appendMessageToUI(msg));
            }

            //cleanning up the channel before resubscribing
            if (activeChatId && activeChatId !== chatId) {
                Echo.leave(`chat.${activeChatId}`);
                console.log(' Left previous chat channel:', activeChatId);
            }

            //defining current active chat
            activeChatId = chatId;


            Echo.private(`chat.${chatId}`)
            .subscribed(() => {
                console.log('Subscribed to chat:', chatId);
            })
            .listen('MessageSent', (e) => {
                if (e.senderId !== {{ auth()->id() }}) {
                    appendMessageToUI(e.messageData);
                    scrollChatToBottom();
                }
            })
            .listen('MessageSeen', (e) => {
                // Update the tick on the sender's message bubble in real-time
                updateMessageTicks(e.messageId, e.delivered_at, e.seen_at);
            });
            scrollChatToBottom();
        } catch (error) {
            console.error('Failed to load messages:', error);
            container.innerHTML = '<div class="text-center text-red-400 py-10 italic">Error loading messages.</div>';
        }
        

        // Show chat area on mobile if hidden
        if (window.innerWidth < 768) {
            toggleChat(); 
        }
    }

    function highlightContact(id) {
        document.querySelectorAll('.chat-item').forEach(el => {
            el.classList.remove('bg-blue-50', 'border-l-4', 'border-blue-500');
        });
        const activeEl = document.getElementById(`contact-${id}`);
        if (activeEl) {
            activeEl.classList.add('bg-blue-50', 'border-l-4', 'border-blue-500');
        }
    }

    function appendMessageToUI(msg) {
        // Only mark as seen if WE received it (not our own messages)
        if (!msg.is_sender) messageSeen(msg.messageId);

        const container = document.getElementById('messages');
        const div = document.createElement('div');
        div.className = `flex ${msg.is_sender ? 'justify-end' : 'justify-start'}`;

        const ticks = msg.is_sender ? getTicksHtml(msg.delivered_at, msg.seen_at) : '';

        div.innerHTML = `
            <div id="msg-${msg.messageId}" class="${msg.is_sender ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'} px-4 py-2 rounded-lg max-w-xs shadow-sm">
                <span>${msg.message}</span>
                <span class="tick-status block text-right text-xs mt-1 leading-none">${ticks}</span>
            </div>
        `;
        container.appendChild(div);
    }

    // Returns tick HTML based on delivered_at / seen_at timestamps
    function getTicksHtml(delivered_at, seen_at) {
        if (seen_at)      return '<span style="color:#00ff00;font-weight:800" title="Seen">✓✓</span>';
        if (delivered_at) return '<span style="color:#dfdfdf; font-weight:800" title="Delivered">✓✓</span>';
        return '<span style="color:#e2e8f0" title="Sent">✓</span>';
    }

    // Updates the tick indicator on a specific message bubble (called on MessageSeen broadcast)
    function updateMessageTicks(messageId, delivered_at, seen_at) {
        const bubble = document.getElementById(`msg-${messageId}`);
        if (!bubble) return;
        const tickSpan = bubble.querySelector('.tick-status');
        if (tickSpan) tickSpan.innerHTML = getTicksHtml(delivered_at, seen_at);
    }

    function scrollChatToBottom() {
        const container = document.getElementById('messages');
        container.scrollTop = container.scrollHeight;
    }

    async function loadNotifications() {
        try {
            const response = await fetch("{{ route('notifications.index') }}");
            const notifications = await response.json();
            const container = document.getElementById('notificationsContainer');
            container.innerHTML = '';

            if (notifications.length === 0) {
                container.innerHTML = '<div class="p-3 text-sm text-gray-500 text-center">No new notifications.</div>';
            } else {
                notifications.forEach(notif => {
                    addNotificationToUI({
                        id: notif.id,
                        ...notif.data,
                        created_at: notif.created_at
                    });
                });
            }
            updateBadgeCount();
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }
    async function receiveFallbackMessages() {
        fetch("{{ route('fallback-messages') }}", {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
        })
        .then(response => response.json())
        .then(data => {
            console.log('received fall back messages');
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    async function messageSeen(msgId) {
         fetch("{{ route('seen-message') }}", {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: msgId })
        })
        .then(response => response.json())
        .then(data => {
            
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function addNotificationToUI(notif, isNew = false) {
        const container = document.getElementById('notificationsContainer');
        const emptyMsg = container.querySelector('.text-gray-500');
        if (emptyMsg) emptyMsg.remove();

        const div = document.createElement('div');
        div.className = `p-3 border-b hover:bg-gray-50 transition-colors ${isNew ? 'bg-blue-50' : ''}`;
        div.id = `notif-${notif.id || 'new'}`;

        let actionHtml = '';
        if (notif.data_type === 'friend_request_received') {
            actionHtml = `
                <div class="mt-2 flex gap-2">
                    <button onclick="handleNotifAction('${notif.id}', 'accept')" class="bg-blue-500 text-white text-xs px-3 py-1 rounded hover:bg-blue-600">Accept</button>
                    <button onclick="handleNotifAction('${notif.id}', 'reject')" class="bg-gray-200 text-gray-700 text-xs px-3 py-1 rounded hover:bg-gray-300">Reject</button>
                </div>
            `;
        }

        div.innerHTML = `
            <div class="text-sm font-medium text-gray-800">${notif.message}</div>
            ${actionHtml}
            <div class="text-xs text-gray-500 mt-1">${notif.created_at ? new Date(notif.created_at).toLocaleString() : 'Just now'}</div>
        `;

        if (isNew) {
            container.insertBefore(div, container.firstChild);
            updateBadgeCount(1);
        } else {
            container.appendChild(div);
        }
    }

    function addContactToSidebar(contact) {
        const chatList = document.getElementById('chatList');
        
        // Check if already in list
        if (document.getElementById(`contact-${contact.id}`)) return;

        const div = document.createElement('div');
        div.className = 'p-3 border-b cursor-pointer hover:bg-gray-100 chat-item';
        div.id = `contact-${contact.id}`;
        div.innerText = contact.name;
        
        // Click listener to open chat
        div.onclick = () => openChat(contact.id, contact.name);

        chatList.appendChild(div);
    }

    async function handleNotifAction(id, action) {
        const notifElement = document.getElementById(`notif-${id}`);
        if (notifElement) notifElement.style.opacity = '0.5';

        try {
            const url = action === 'accept' ? `/notifications/${id}/accept` : `/notifications/${id}/reject`;
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok) {
                if (notifElement) notifElement.remove();
                updateBadgeCount(-1);
                
                if (action === 'accept' && result.contact) {
                    addContactToSidebar(result.contact);
                }

                const container = document.getElementById('notificationsContainer');
                if (container.children.length === 0) {
                    container.innerHTML = '<div class="p-3 text-sm text-gray-500 text-center">No new notifications.</div>';
                }
            }
        } catch (error) {
            console.error(`Error during ${action}:`, error);
            if (notifElement) notifElement.style.opacity = '1';
        }
    }

    function updateBadgeCount(delta = 0) {
        const badge = document.getElementById('notifBadge');
        if (!badge) return;
        let count = parseInt(badge.innerText) || 0;
        
        if (delta !== 0) {
            count += delta;
        } else {
            const container = document.getElementById('notificationsContainer');
            count = container.querySelectorAll('.border-b').length;
        }

        badge.innerText = count;
        if (count > 0) {
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function toggleNotifications() {
        document.getElementById('notifDropdown').classList.toggle('hidden');
    }

    function filterChats(query) {
        let items = document.querySelectorAll('.chat-item');
        items.forEach(item => {
            item.style.display = item.innerText.toLowerCase().includes(query.toLowerCase())
                ? 'block'
                : 'none';
        });
    }

    function searchUser(event) {
        event.preventDefault();
        let form = event.target;
        let email = form.querySelector('#emailSearch').value;
        let btn = form.querySelector('#addBtn');
        btn.disabled = true;
        btn.innerText = 'Pending';
        
        fetch("{{ route('contact-add') }}", {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            btn.innerText = 'Add Friend';
            btn.disabled = false;
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            btn.innerText = 'Add Friend';
            btn.disabled = false;
        });
    }

    async function sendMessage(event) {
        event.preventDefault();
        const input = document.getElementById('messageInput');
        const msgText = input.value.trim();

        if (!msgText || !activeContactId) return;

        // Optimistic update with a temporary ID so the bubble exists immediately
        const tempId = `temp-${Date.now()}`;
        appendMessageToUI({ messageId: tempId, message: msgText, is_sender: true });
        input.value = '';
        scrollChatToBottom();

        try {
            const response = await fetch('/chat/send', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    receiver_id: activeContactId,
                    message: msgText
                })
            });

            // Swap the temp ID for the real message ID so MessageSeen ticks work
            const saved = await response.json();
            const tempBubble = document.getElementById(`msg-${tempId}`);
            if (tempBubble && saved.id) {
                tempBubble.id = `msg-${saved.id}`;
            }
        } catch (error) {
            console.log('Error sending message:', error);
        }
    }

    function toggleChat() {
        document.querySelector('.md\\:flex').classList.toggle('hidden');
    }
</script>

</body>
</html>