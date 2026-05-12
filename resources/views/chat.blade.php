@php
    
use Carbon\Traits\Date;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/js/app.js'])
</head>
<body class="bg-gray-100 h-screen flex flex-col">
<!-- Top Navbar -->
<div class="bg-white shadow px-4 py-3 flex justify-between items-center">

    <h1 class="font-bold text-lg">Chat App</h1>

    <div class="flex items-center gap-4">
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

        <!-- Logout -->
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="text-sm bg-red-500 text-white px-3 py-1.5 rounded-lg hover:bg-red-600 transition">
                Logout
            </button>
        </form>
    </div>

</div>

<!-- Main Layout -->
<div class="flex flex-1 overflow-hidden">

    <!-- Sidebar -->
    <div id="sidebar" class="w-full md:w-1/3 lg:w-1/4 bg-white border-r flex flex-col">

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
                 data-chat-id="{{ $contact->chat_id }}"
                 onclick="openChat('{{ $contact->id }}', '{{ $contact->name }}', '{{ $contact->formatted_last_seen }}')"
                 class="p-3 border-b cursor-pointer hover:bg-gray-100 chat-item flex items-center justify-between">
               
                <div class="flex flex-col flex-1 overflow-hidden">
                    <span id="contact-name-{{ $contact->id }}" class="{{ ($contact->unread_count ?? 0) > 0 ? 'font-bold text-gray-900' : 'font-medium text-gray-800' }}">
                        {{ $contact->name }}
                    </span>
                    
                    <!-- Last Message Preview -->
                    <div class="flex items-center gap-1">
                        <span id="last-msg-ticks-{{ $contact->id }}" class="text-[10px] leading-none">
                             @if($contact->last_message_sender_id == auth()->id())
                                 {!! $contact->last_message_status['seen'] ? '<span class="text-green-500">✓✓</span>' : ($contact->last_message_status['delivered'] ? '<span class="text-gray-400">✓✓</span>' : '✓') !!}
                             @endif
                        </span>
                        <span id="last-msg-text-{{ $contact->id }}" class="text-xs truncate {{ ($contact->unread_count ?? 0) > 0 ? 'font-bold text-blue-600' : 'text-gray-400' }}">
                            @if(($contact->unread_count ?? 0) > 1)
                                {{ $contact->unread_count }} new messages
                            @else
                                {{ $contact->last_message ?? 'No messages yet' }}
                            @endif
                        </span>
                    </div>
                </div>

               <!-- Status & Date -->
               <div class="flex flex-col items-end gap-1">
                    <div 
                    id="status-dot-{{ $contact->id }}"
                    class="w-3 h-3 rounded-full bg-gray-400 transition-colors duration-300" 
                    title="offline">
                    </div>
                    <span id="status-text-{{ $contact->id }}" class="text-[10px] text-gray-400 whitespace-nowrap last-seen-timer" data-timestamp="{{ $contact->last_seen_at?->toIso8601String() }}">
                        {{ $contact->formatted_last_seen }}
                    </span>
               </div>
            </div>
            @endforeach

            

        </div>
    </div>

    <!-- Chat Area -->
    <div id="chatArea" class="hidden md:flex flex-1 flex-col">
@include('components.flash')

        <!-- Header -->
        <div id="chatHeader" class="p-4 bg-white border-b font-semibold text-gray-700 flex items-center gap-2">
            <button id="backBtn" onclick="showSidebar()" class="md:hidden text-blue-500 mr-1">← </button>
            <div class="flex flex-col">
                <span id="chatTitle">Select a contact to start chatting</span>
                <span id="header-status-text" class="text-xs font-normal text-gray-400"></span>
            </div>
            <div id="header-status-dot" class="w-3 h-3 rounded-full bg-gray-400 hidden"></div>
        </div>

        <!-- Messages -->
        <div id="messages" class="flex-1 p-4 overflow-y-auto space-y-3">
        </div>

        <div id="typing-indicator" class="px-4 py-1 text-sm text-gray-400 italic hidden">typing...</div>

        <!-- Input -->
        <form onsubmit="sendMessage(event)">
        <div class="p-3 bg-white border-t flex gap-2">
                <input 
                id="messageInput"
                type="text"
                class="flex-1 border rounded-lg px-3 py-2"
                placeholder="Type message..."
                oninput="handleTyping()"
                >
                <button type='submit'  class="bg-blue-500 text-white px-4 rounded-lg">
                    Send
                </button>
            </div>
        </form>

    </div>

</div>


    <script>
    let activeContactId = null;
    let activeChatId = null;

    window.addEventListener('DOMContentLoaded', () => {
        loadNotifications();
        receiveFallbackMessages();

        Echo.private('user.{{ auth()->id() }}')
            .subscribed(() => {})
            .error((error) => {
                console.error('Subscription error:', error);
            })
            .notification((notification) => {
                addNotificationToUI(notification, true);

                // If friend request was accepted, add the new friend to the sidebar
                if (notification.data_type === 'friend_request_accepted') {
                    addContactToSidebar({
                        id: notification.sender_id,
                        name: notification.sender_name
                    });
                }
            })
            .listen('MessageDelivered', (e) => {
                const myId = {{ auth()->id() }};
                if (e.senderId != myId) {
                    // I'm the RECEIVER — confirm delivery to server
                    messageDeliveredSuccess(e);
                } else {
                    // I'm the SENDER — update my sidebar ticks
                    updateMessageTicks(e.messageId, e.delivered_at, e.seen_at, e.chatId, e.senderId);
                }
            })
            .listen('MessageSeen', (e) => {
                updateMessageTicks(e.messageId, e.delivered_at, e.seen_at, e.chatId, e.senderId);
            })
            .listen('.SidebarUpdated', (e) => {
                moveContactToTop(e.senderId, e.messageText, false, e.chatId, e.unreadCount);
            });
        
        Echo.join('user-status.{{auth()->id() }}')
            .here((users) => {})
            .error((error) =>
            console.error('status channel error:', error));
        
        const contactIds = @json($contacts->pluck('id'));

        contactIds.forEach(id => {
            Echo.join(`user-status.${id}`)
                .here((users) => {
                    const isFriendOnline = users.some(u => u.id == id);
                    updateStatusUI(id, isFriendOnline);  
                })
                .joining((user) => {
                    if (user.id == id) updateStatusUI(id, true);
                })
                .leaving((user) => {
                    if (user.id == id) updateStatusUI(id, false);
                });
        });

        // Initialize the live status timer
        if (typeof window.startStatusTimer === 'function') {
            window.startStatusTimer();
        }
    });
        

    // Open a chat and fetch history
    async function openChat(id, name, lastSeen) {
        activeContactId = id;
        
        // UI Updates
        document.getElementById('chatTitle').innerText = name;
        const headerDot = document.getElementById('header-status-dot');
        const headerText = document.getElementById('header-status-text');
        headerDot.classList.remove('hidden');
        
        const sidebarDot = document.getElementById(`status-dot-${id}`);
        const sidebarText = document.getElementById(`status-text-${id}`);
        
        if (sidebarDot) {
            headerDot.className = sidebarDot.className;
        }

        if (headerText && sidebarText) {
            headerText.innerText = sidebarText.innerText;
            headerText.className = sidebarText.className + " text-xs font-normal";
        }

        highlightContact(id);

        const container = document.getElementById('messages');
        container.innerHTML = '<div class="text-center text-gray-400 py-10 italic">Loading conversation...</div>';

        try {
            const response = await fetch(`/chat/messages/${id}`,{credentials: 'same-origin'});
            const messages = await response.json();
            const chatId=messages.chat_id;

            
            container.innerHTML = ''; // Clear loader
            if (messages.messageData.length === 0) {
                container.innerHTML = '<div id="no-messages-placeholder" class="text-center text-gray-400 py-10 italic">No messages yet. Say hi!</div>';
            } else {
                messages.messageData.forEach(msg => appendMessageToUI(msg));
            }

            if (activeChatId) {
                Echo.leave(`chat.${activeChatId}`);
            }

            activeChatId = chatId;


            if (messages.messageData.length > 0) {
                markChatAsSeen(chatId);
                
                // Reset font weights and preview text
                const nameEl = document.getElementById(`contact-name-${id}`);
                const textEl = document.getElementById(`last-msg-text-${id}`);
                
                if (nameEl) {
                    nameEl.classList.remove('font-bold', 'text-gray-900');
                    nameEl.classList.add('font-medium', 'text-gray-800');
                }
                if (textEl) {
                    textEl.classList.remove('font-bold', 'text-blue-600');
                    textEl.classList.add('text-gray-400');
                    // Restore actual message text if it was showing "X new messages"
                    if (messages.messageData.length > 0) {
                        textEl.innerText = messages.messageData[messages.messageData.length - 1].message;
                    }
                }
            }

            Echo.private(`chat.${chatId}`)
            .subscribed(() => {})
            .listen('MessageSent', (e) => {
                if (e.senderId !== {{ auth()->id() }}) {
                    appendMessageToUI(e.messageData);
                    messageSeen(e.messageData.messageId); // Mark single incoming message as seen
                    setTimeout(scrollChatToBottom, 50);
                }
            })
            .listen('MessageSeen', (e) => {
                updateMessageTicks(e.messageId, e.delivered_at, e.seen_at, e.chatId || null, e.senderId || null);
            })
            .listenForWhisper('typing', (e) => {
                showTypingIndicator();
            });
            // Scroll to bottom after all messages are rendered
            setTimeout(scrollChatToBottom, 50);
        } catch (error) {
            console.error('Failed to load messages:', error);
            container.innerHTML = '<div class="text-center text-red-400 py-10 italic">Error loading messages.</div>';
        }
        

        if (window.innerWidth < 768) {
            document.getElementById('sidebar').classList.add('hidden');
            document.getElementById('chatArea').classList.remove('hidden');
            document.getElementById('chatArea').classList.add('flex');
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

    let typingTimeout = null;
    let typingIndicatorTimeout = null;

    function handleTyping() {
        if (!activeChatId) return;
        if (typingTimeout) return;
        
        Echo.private(`chat.${activeChatId}`).whisper('typing', {
            userId: {{ auth()->id() }}
        });

        typingTimeout = setTimeout(() => { typingTimeout = null; }, 1000);
    }

    function showTypingIndicator() {
        const el = document.getElementById('typing-indicator');
        el.classList.remove('hidden');
        
        if (typingIndicatorTimeout) clearTimeout(typingIndicatorTimeout);
        typingIndicatorTimeout = setTimeout(() => {
            el.classList.add('hidden');
        }, 1500);
    }

    function appendMessageToUI(msg) {
        const container = document.getElementById('messages');

        // Remove placeholder if it exists
        const placeholder = document.getElementById('no-messages-placeholder');
        if (placeholder) placeholder.remove();

        const div = document.createElement('div');
        div.className = `flex ${msg.is_sender ? 'justify-end' : 'justify-start'}`;

        // Only include the tick status container if it's a message WE sent
        const statusHtml = msg.is_sender 
            ? `<span class="tick-status block text-right text-xs mt-1 leading-none">${getTicksHtml(msg.delivered_at, msg.seen_at)}</span>` 
            : '';

        div.innerHTML = `
            <div id="msg-${msg.messageId}" class="${msg.is_sender ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'} px-4 py-2 rounded-lg max-w-[75%] md:max-w-md shadow-sm break-words">
                <span>${msg.message}</span>
                ${statusHtml}
            </div>
        `;
        container.appendChild(div);
        
    }

    // Returns tick HTML based on delivered_at / seen_at timestamps
    function getTicksHtml(delivered_at, seen_at) {
        if (seen_at)      return '<span style="color:#00ff00;font-weight:800;pointer-events:none;" title="Seen">✓✓</span>';
        if (delivered_at) return '<span style="color:#dfdfdf; font-weight:800;pointer-events:none;" title="Delivered">✓✓</span>';
        return '<span style="color:#e2e8f0" title="Sent">✓</span>';
    }

    // Updates the tick indicator on a specific message bubble (called on MessageSeen broadcast)
    function updateMessageTicks(messageId, deliveredAt, seenAt, chatId = null, senderId = null) {
        const myId = {{ auth()->id() }};

        // 1. Update the chat bubble if it's currently on screen
        const msgBubble = document.getElementById(`msg-${messageId}`);
        if (msgBubble) {
            const ticksContainer = msgBubble.querySelector('.tick-status');
            if (ticksContainer) {
                ticksContainer.innerHTML = getTicksHtml(deliveredAt, seenAt);
            }
        }
        
        // 2. Update the sidebar ticks — ONLY if I am the sender of this message
        if (senderId && senderId == myId) {
            let targetContactId = null;

            if (chatId) {
                const contactItem = document.querySelector(`.chat-item[data-chat-id="${chatId}"]`);
                if (contactItem) targetContactId = contactItem.id.split('-')[1];
            } else if (activeContactId) {
                targetContactId = activeContactId;
            }

            if (targetContactId) {
                updateSidebarTicks(targetContactId, deliveredAt, seenAt);
            }
        }
    }

    function updateSidebarTicks(contactId, deliveredAt, seenAt) {
        const ticksSpan = document.getElementById(`last-msg-ticks-${contactId}`);
        if (ticksSpan) {
            ticksSpan.innerHTML = getTicksHtml(deliveredAt, seenAt);
        }
    }

    function scrollChatToBottom() {
        const container = document.getElementById('messages');
        if (container) {
            container.scrollTo({
                top: container.scrollHeight,
                behavior: 'smooth'
            });
        }
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
                    let data = notif.data;
                    if (typeof data === 'string') {
                        try { data = JSON.parse(data); } catch(e) { data = {}; }
                    }
                    
                    addNotificationToUI({
                        id: notif.id,
                        ...data,
                        created_at: notif.created_at,
                        is_unread: !notif.read_at 
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
        .then(data => {})
        .catch(error => {
            console.error('Error:', error);
        });
    }
    async function messageDeliveredSuccess(e) {
         fetch("{{ route('message-delivered-online') }}", {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ userId: e.senderId, messageId: e.messageId })
        })
        .then(response => response.json())
        .catch(error => {
            console.error('Error:', error);
        });
    }

    async function markChatAsSeen(chatId) {
        fetch("{{ route('seen-message-bulk') }}", {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ chat_id: chatId })
        }).catch(error => console.error('Error marking chat as seen:', error));
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

        // 1. Robust data extraction (handles different Laravel/Echo formats)
        const id = notif.id || 'new';
        const data = notif.data || notif; // Fallback to root if 'data' object is missing
        const dataType = data.data_type || notif.data_type;
        const status = data.status || notif.status;
        const message = data.message || notif.message || 'New notification';
        const senderName = data.sender_name || notif.sender_name || 'User';
        const createdAt = notif.created_at || data.created_at;

        const div = document.createElement('div');
        const isUnread = notif.is_unread || isNew;
        div.className = `p-3 border-b hover:bg-gray-50 transition-colors ${isUnread ? 'unread bg-blue-50' : ''}`;
        div.id = `notif-${id}`;
        div.setAttribute('data-sender', senderName); // Store sender name for handleNotifAction

        let actionHtml = '';
        let displayMessage = message;
        let messageClass = 'text-gray-800';

        // 2. Render based on extracted type and status
        if (dataType === 'friend_request_received') {
            if (status === 'accepted') {
                displayMessage = `${senderName} accepted friend request`;
                messageClass = 'text-gray-500';
                actionHtml = '<div class="mt-1 text-xs font-bold text-green-600">✓ Added to friends</div>';
            } else if (status === 'rejected') {
                displayMessage = `${senderName} rejected friend request`;
                messageClass = 'text-gray-500';
                actionHtml = '<div class="mt-1 text-xs font-bold text-red-600">✕ Request declined</div>';
            } else {
                actionHtml = `
                    <div class="mt-2 flex gap-2 action-buttons">
                        <button onclick="handleNotifAction('${id}', 'accept')" class="bg-blue-500 text-white text-xs px-3 py-1 rounded hover:bg-blue-600">Accept</button>
                        <button onclick="handleNotifAction('${id}', 'reject')" class="bg-gray-200 text-gray-700 text-xs px-3 py-1 rounded hover:bg-gray-300">Reject</button>
                    </div>
                `;
            }
        }

        div.innerHTML = `
            <div class="text-sm font-medium ${messageClass}">${displayMessage}</div>
            <div class="notif-action-area">${actionHtml}</div>
            <div class="text-xs text-gray-500 mt-1">${createdAt ? new Date(createdAt).toLocaleString() : 'Just now'}</div>
        `;

        if (isNew) {
            container.insertBefore(div, container.firstChild);
            updateBadgeCount(1);
        } else {
            container.appendChild(div);
        }
    }

    function updateStatusUI(userId, isOnline) {
        const sidebarDot = document.getElementById(`status-dot-${userId}`);
        const sidebarText = document.getElementById(`status-text-${userId}`);
        
        if (sidebarDot) {
            setDotStatus(sidebarDot, isOnline);
        }

        if (sidebarText) {
            if (isOnline) {
                sidebarText.innerText = 'Online';
                sidebarText.classList.add('text-green-500');
                sidebarText.classList.remove('text-gray-500');
            } else if (sidebarText.innerText === 'Online') {
                // User just left: update timestamp to now and set text
                sidebarText.dataset.timestamp = new Date().toISOString();
                sidebarText.innerText = typeof window.formatTimeAgo === 'function' 
                    ? window.formatTimeAgo(sidebarText.dataset.timestamp) 
                    : 'Just now';
                
                sidebarText.classList.remove('text-green-500');
                sidebarText.classList.add('text-gray-500');
            }
        }

        if (activeContactId == userId) {
            const headerDot = document.getElementById('header-status-dot');
            const headerText = document.getElementById('header-status-text');
            
            if (headerDot) {
                setDotStatus(headerDot, isOnline);
            }
            if (headerText) {
                if (isOnline) {
                    headerText.innerText = 'Online';
                    headerText.classList.add('text-green-500');
                    headerText.classList.remove('text-gray-400');
                } else if (headerText.innerText === 'Online') {
                    headerText.innerText = typeof window.formatTimeAgo === 'function' 
                        ? window.formatTimeAgo(new Date().toISOString()) 
                        : 'Just now';
                    headerText.classList.remove('text-green-500');
                    headerText.classList.add('text-gray-400');
                }
            }
        }
    }

    function setDotStatus(el, isOnline) {
        if (isOnline) {
            el.classList.remove('bg-gray-400');
            el.classList.add('bg-green-500');
            el.title = 'online';
        } else {
            el.classList.remove('bg-green-500');
            el.classList.add('bg-gray-400');
            el.title = 'offline';
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

        Echo.join(`user-status.${contact.id}`)
        .here((users) => {
            const isFriendOnline = users.some(u => u.id == contact.id);
            updateStatusUI(contact.id, isFriendOnline);
        })
        .joining((user) =>{ 
            if (user.id == contact.id) updateStatusUI(contact.id, true);
        })
        .leaving((user) =>{
            if (user.id == contact.id) updateStatusUI(contact.id, false);
        });

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
                // 1. Update the main message text using the stored sender name
                const senderName = notifElement.getAttribute('data-sender') || 'User';
                const msgText = notifElement.querySelector('.text-sm');
                if (msgText) {
                    msgText.innerText = action === 'accept' ? `${senderName} accepted friend request` : `${senderName} rejected friend request`;
                    msgText.classList.remove('text-gray-800');
                    msgText.classList.add('text-gray-500');
                }

                // 2. Update the action area text
                const actionArea = notifElement.querySelector('.notif-action-area');
                if (actionArea) {
                    if (action === 'accept') {
                        actionArea.innerHTML = '<div class="mt-1 text-xs font-bold text-green-600">✓ Added to friends</div>';
                    } else {
                        actionArea.innerHTML = '<div class="mt-1 text-xs font-bold text-red-600">✕ Request declined</div>';
                    }
                }
                
                notifElement.style.opacity = '1';
                updateBadgeCount(); 
                
                if (action === 'accept' && result.contact) {
                    addContactToSidebar(result.contact);
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
            count = container.querySelectorAll('.unread').length; // Only count items with 'unread' class
        }

        badge.innerText = count;
        if (count > 0) {
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notifDropdown');
        const badge = document.getElementById('notifBadge');

        dropdown.classList.toggle('hidden');

        if (!dropdown.classList.contains('hidden') && !badge.classList.contains('hidden')){
            badge.classList.add('hidden');
            badge.innerText = "0";

            // Mark all current items as read locally
            document.querySelectorAll('#notificationsContainer .unread').forEach(el => {
                el.classList.remove('unread', 'bg-blue-50');
            });

            fetch("{{ route('notifications.readAll') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).catch(error => console.error('Error marking notifications as read:', error));
        }
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

            // Move to top and update sidebar ticks
            moveContactToTop(activeContactId, msgText, true, activeChatId, 0);
        } catch (error) {
            console.log('Error sending message:', error);
        }
    }

    function moveContactToTop(senderId, message, isSender, chatId = null, unreadCount = 0) {
        const chatList = document.getElementById('chatList');
        
        // Find contact item by chatId (best) or senderId (fallback)
        let contactItem = null;
        if (chatId) {
            contactItem = document.querySelector(`.chat-item[data-chat-id="${chatId}"]`);
        }
        
        if (!contactItem) {
            contactItem = document.getElementById(`contact-${senderId}`);
        }
        
        if (contactItem) {
            const contactId = contactItem.id.split('-')[1];
            const nameEl = document.getElementById(`contact-name-${contactId}`);
            const textPreview = document.getElementById(`last-msg-text-${contactId}`);
            const ticksSpan = document.getElementById(`last-msg-ticks-${contactId}`);
            
            // Update preview text and bolding
            if (textPreview) {
                if (unreadCount > 1 && !isSender && activeContactId != contactId) {
                    textPreview.innerText = `${unreadCount} new messages`;
                } else {
                    textPreview.innerText = message;
                }

                // Handle Bolding
                if (unreadCount > 0 && !isSender && activeContactId != contactId) {
                    textPreview.classList.add('font-bold', 'text-blue-600');
                    textPreview.classList.remove('text-gray-400');
                    if (nameEl) {
                        nameEl.classList.add('font-bold', 'text-gray-900');
                        nameEl.classList.remove('font-medium', 'text-gray-800');
                    }
                } else {
                    textPreview.classList.remove('font-bold', 'text-blue-600');
                    textPreview.classList.add('text-gray-400');
                    if (nameEl) {
                        nameEl.classList.remove('font-bold', 'text-gray-900');
                        nameEl.classList.add('font-medium', 'text-gray-800');
                    }
                }
            }
            
            // Update ticks: show single tick if WE sent it, clear it if THEY sent it
            if (ticksSpan) {
                ticksSpan.innerHTML = isSender ? '<span style="color:#e2e8f0" title="Sent">✓</span>' : '';
            }

            // Move to top
            chatList.prepend(contactItem);
            
            // Subtle flash effect
            contactItem.classList.add('bg-blue-50');
            setTimeout(() => contactItem.classList.remove('bg-blue-50'), 2000);
        }
    }

    function showSidebar() {
        if (window.innerWidth < 768) {
            document.getElementById('chatArea').classList.add('hidden');
            document.getElementById('chatArea').classList.remove('flex');
            document.getElementById('sidebar').classList.remove('hidden');
        }
    }
</script>

</body>
</html>