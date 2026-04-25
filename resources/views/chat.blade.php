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
<!-- 🔝 Top Navbar -->
<div class="bg-white shadow px-4 py-3 flex justify-between items-center">

    <h1 class="font-bold text-lg">Chat App</h1>

    <!-- 🔔 Notifications -->
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

<!-- 📱 Main Layout -->
<div class="flex flex-1 overflow-hidden">

    <!-- Sidebar -->
    <div class="w-full md:w-1/3 lg:w-1/4 bg-white border-r flex flex-col">

        <!-- 🔍 Search Chats -->
        <div class="p-3 border-b">
            <input 
                type="text" 
                placeholder="Search chats..."
                onkeyup="filterChats(this.value)"
                class="w-full border rounded-lg px-3 py-2"
            >
        </div>

        <!-- ➕ Add Friend -->
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
            <div id="contact-{{ $contact->id }}" class="p-3 border-b cursor-pointer hover:bg-gray-100 chat-item">
                {{ $contact->name }}
            </div>
            @endforeach

            

        </div>
    </div>

    <!-- Chat Area -->
    <div class="hidden md:flex flex-1 flex-col">
@include('components.flash')

        <!-- Header -->
        <div class="p-4 bg-white border-b font-semibold">
            Chat
        </div>

        <!-- Messages -->
        <div id="messages" class="flex-1 p-4 overflow-y-auto space-y-3">
        </div>

        <!-- Input -->
        <div class="p-3 bg-white border-t flex gap-2">
            <input 
                id="messageInput"
                type="text"
                class="flex-1 border rounded-lg px-3 py-2"
                placeholder="Type message..."
            >
            <button onclick="sendMessage()" class="bg-blue-500 text-white px-4 rounded-lg">
                Send
            </button>
        </div>

    </div>

</div>

<!-- 📱 Mobile Chat Toggle -->
<div class="md:hidden fixed bottom-4 right-4">
    <button onclick="toggleChat()" class="bg-blue-500 text-white px-4 py-2 rounded-full shadow">
        Open Chat
    </button>
</div>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        loadNotifications();

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
        
        // Add click listener if needed (to open chat)
        div.onclick = () => {
            // Logic to open chat
            console.log(`Opening chat with ${contact.name}`);
        };

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
        let count = parseInt(badge.innerText) || 0;
        
        if (delta !== 0) {
            count += delta;
        } else {
            // If delta is 0, we just want to set it based on current container children (excluding messages)
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

    // 🔍 Filter Chats
    function filterChats(query) {
        let items = document.querySelectorAll('.chat-item');
        items.forEach(item => {
            item.style.display = item.innerText.toLowerCase().includes(query.toLowerCase())
                ? 'block'
                : 'none';
        });
    }

    // ➕ Search user by email
    function searchUser(event) {
        event.preventDefault();
        let form = event.target;
        let email = form.querySelector('#emailSearch').value;
        let btn = form.querySelector('#addBtn');
        btn.disabled = true;
        btn.innerText = 'Pending';
        
        fetch("{{ route('contact-add') }}", {
            method: 'POST',
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
            // Show some feedback or reload
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            btn.innerText = 'Add Friend';
            btn.disabled = false;
        });
    }

    // 💬 Send Message
    function sendMessage() {
        let input = document.getElementById('messageInput');
        let msg = input.value.trim();

        if (!msg) return;

        let div = document.createElement('div');
        div.className = 'flex justify-end';
        div.innerHTML = `
            <div class="bg-blue-500 text-white px-4 py-2 rounded-lg max-w-xs">
                ${msg}
            </div>
        `;

        document.getElementById('messages').appendChild(div);
        input.value = '';
    }

    // 📱 Mobile toggle
    function toggleChat() {
        document.querySelector('.md\\:flex').classList.toggle('hidden');
    }
</script>

</body>
</html>