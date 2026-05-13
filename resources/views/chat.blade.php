<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="server-time" content="{{ now()->toIso8601String() }}">
    @vite(['resources/js/app.js'])
    <style>
        .message-details {
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease-in-out;
            opacity: 0;
            font-size: 0.7rem;
            line-height: 1rem;
        }
        .message-details.show {
            max-height: 50px;
            opacity: 1;
            margin-top: 4px;
        }
        .message-bubble {
            cursor: pointer;
            transition: transform 0.1s ease;
        }
        .message-bubble:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body class="bg-gray-100 h-screen flex flex-col">
<!-- Top Navbar -->
<div class="bg-white shadow px-4 py-3 flex justify-between items-center">

    <h1 class="font-bold text-lg">Chat App</h1>

    <div class="flex items-center gap-4">
        <!-- Notifications -->
        <div class="relative">
            <button onclick="toggleNotificationsWrapper()" class="relative">
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
                        <span id="sidebar-typing-{{ $contact->id }}" class="text-xs italic text-blue-500 hidden animate-pulse">typing...</span>
                    </div>
                </div>

               <!-- Status & Date -->
                <div class="flex flex-col items-end gap-1">
                    <span id="last-msg-time-{{ $contact->id }}" class="text-[10px] text-gray-400 whitespace-nowrap message-time-live" data-timestamp="{{ $contact->last_message_at?->toIso8601String() }}">
                        {{ $contact->formatted_last_message_at }}
                    </span>
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
        // Initialize modular systems
        if (window.loadNotifications) {
            window.loadNotifications(
                "{{ route('notifications.index') }}", 
                window.addNotificationToUI, 
                window.updateBadgeCount
            );
        }

        if (window.receiveFallbackMessages) {
            window.receiveFallbackMessages("{{ route('fallback-messages') }}", '{{ csrf_token() }}');
        }

        if (window.startStatusTimer) window.startStatusTimer();

        Echo.private('user.{{ auth()->id() }}')
            .subscribed(() => {})
            .error((error) => {
                console.error('Subscription error:', error);
            })
            .notification((notification) => {
                window.addNotificationToUI(notification, true, window.updateBadgeCount);

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
                    window.messageDeliveredSuccess(e.senderId, e.messageId, '{{ csrf_token() }}', "{{ route('message-delivered-online') }}");
                } else {
                    // I'm the SENDER — update my sidebar ticks
                    window.updateMessageTicks(e.messageId, e.delivered_at, e.seen_at, myId, e.chatId, activeContactId, window.updateSidebarTicks);
                }
            })
            .listen('MessageSeen', (e) => {
                if (e.senderId == {{ auth()->id() }}) {
                    window.updateMessageTicks(e.messageId, e.delivered_at, e.seen_at, {{ auth()->id() }}, e.chatId, activeContactId, window.updateSidebarTicks);
                }
            })
            .listen('.SidebarUpdated', (e) => {
                moveContactToTop(e.senderId, e.messageText, false, e.chatId, e.unreadCount);
            });
        
        Echo.join('user-status.{{auth()->id() }}')
            .here((users) => {})
            .error((error) =>
            console.error('status channel error:', error))
            .listenForWhisper('typing', (e) => {
                showSidebarTyping(e.userId);
            });
        
        const contactIds = @json($contacts->pluck('id'));

        contactIds.forEach(id => {
            Echo.join(`user-status.${id}`)
                .here((users) => {
                    const isFriendOnline = users.some(u => u.id == id);
                    window.updateStatusUI(id, isFriendOnline, activeContactId);  
                })
                .joining((user) => {
                    if (user.id == id) window.updateStatusUI(id, true, activeContactId);
                })
                .leaving((user) => {
                    if (user.id == id) window.updateStatusUI(id, false, activeContactId);
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

        window.highlightContact(id);

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
                messages.messageData.forEach(msg => window.appendMessageToUI(msg, {{ auth()->id() }}, window.formatMessageTime));
            }

            if (activeChatId) {
                Echo.leave(`chat.${activeChatId}`);
            }

            activeChatId = chatId;


            if (messages.messageData.length > 0) {
                window.markChatAsSeen(chatId, '{{ csrf_token() }}', "{{ route('seen-message-bulk') }}");
                
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
                }
            }

            Echo.private(`chat.${chatId}`)
            .listen('MessageSent', (e) => {
                if (e.senderId !== {{ auth()->id() }}) {
                    window.appendMessageToUI(e.messageData, {{ auth()->id() }}, window.formatMessageTime);
                    window.messageSeen(e.messageData.messageId, '{{ csrf_token() }}', "{{ route('seen-message') }}");
                    setTimeout(window.scrollChatToBottom, 50);
                }
            })
            .listen('MessageSeen', (e) => {
                if (e.senderId == {{ auth()->id() }}) {
                    window.updateMessageTicks(e.messageId, e.delivered_at, e.seen_at, {{ auth()->id() }}, e.chatId || null, activeContactId, window.updateSidebarTicks);
                }
            })
            .listenForWhisper('typing', (e) => {
                showTypingIndicator();
            });
            // Scroll to bottom after all messages are rendered
            setTimeout(window.scrollChatToBottom, 50);
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

    function moveContactToTop(senderId, message, isSender, chatId = null, unreadCount = 0) {
        const chatList = document.getElementById('chatList');
        let contactItem = null;
        
        if (chatId) {
            contactItem = document.querySelector(`.chat-item[data-chat-id="${chatId}"]`);
        }
        
        if (!contactItem) {
            contactItem = document.getElementById(`contact-${senderId}`);
        }

        if (contactItem) {
            const textEl = contactItem.querySelector(`[id^="last-msg-text-"]`);
            const timeEl = contactItem.querySelector(`[id^="last-msg-time-"]`);
            
            if (textEl) {
                textEl.innerText = message;
                textEl.classList.remove('text-gray-400');
                textEl.classList.add('font-bold', 'text-blue-600');
            }

            if (timeEl) {
                const now = new Date().toISOString();
                timeEl.dataset.timestamp = now;
                if (window.formatMessageTime) {
                    timeEl.innerText = window.formatMessageTime(now);
                } else {
                    timeEl.innerText = 'Just now';
                }
            }

            // Move to top
            chatList.prepend(contactItem);

            // Update unread badge if needed
            const nameEl = contactItem.querySelector(`[id^="contact-name-"]`);
            if (unreadCount > 0) {
                if (nameEl) {
                    nameEl.classList.remove('font-medium', 'text-gray-800');
                    nameEl.classList.add('font-bold', 'text-gray-900');
                }
                if (textEl) {
                    textEl.innerText = unreadCount > 1 ? `${unreadCount} new messages` : message;
                }
            }
        }
    }



    function showTypingIndicator() {
        const el = document.getElementById('typing-indicator');
        el.classList.remove('hidden');
        
        if (typingIndicatorTimeout) clearTimeout(typingIndicatorTimeout);
        typingIndicatorTimeout = setTimeout(() => {
            el.classList.add('hidden');
        }, 1500);
    }

    let sidebarTypingTimeouts = {};
    function showSidebarTyping(userId) {
        const textEl = document.getElementById(`last-msg-text-${userId}`);
        const ticksEl = document.getElementById(`last-msg-ticks-${userId}`);
        const typingEl = document.getElementById(`sidebar-typing-${userId}`);
        
        if (!textEl || !typingEl) return;

        textEl.classList.add('hidden');
        if (ticksEl) ticksEl.classList.add('hidden');
        typingEl.classList.remove('hidden');

        if (sidebarTypingTimeouts[userId]) clearTimeout(sidebarTypingTimeouts[userId]);
        sidebarTypingTimeouts[userId] = setTimeout(() => {
            typingEl.classList.add('hidden');
            textEl.classList.remove('hidden');
            if (ticksEl) ticksEl.classList.remove('hidden');
            delete sidebarTypingTimeouts[userId];
        }, 1500);
    }

    let typingTimeout = null;
    let typingIndicatorTimeout = null;

    function handleTyping() {
        if (!activeContactId) return;
        if (typingTimeout) return;
        
        // Whisper to chat channel (for main chat window indicator)
        if (activeChatId) {
            Echo.private(`chat.${activeChatId}`).whisper('typing', {
                userId: {{ auth()->id() }}
            });
        }

        // Whisper to user-status channel (for sidebar indicator)
        Echo.join(`user-status.${activeContactId}`).whisper('typing', {
            userId: {{ auth()->id() }}
        });

        typingTimeout = setTimeout(() => { typingTimeout = null; }, 1000);
    }



    function addContactToSidebar(contact) {
        const chatList = document.getElementById('chatList');
        
        if (!contact || !contact.id || document.getElementById(`contact-${contact.id}`)) return;

        const div = document.createElement('div');
        div.id = `contact-${contact.id}`;
        div.setAttribute('data-chat-id', contact.chat_id || '');
        div.className = 'p-3 border-b cursor-pointer hover:bg-gray-100 chat-item flex items-center justify-between';
        div.onclick = () => openChat(contact.id, contact.name, 'Just now');

        div.innerHTML = `
            <div class="flex flex-col flex-1 overflow-hidden">
                <span id="contact-name-${contact.id}" class="font-medium text-gray-800">
                    ${contact.name}
                </span>
                <div class="flex items-center gap-1">
                    <span id="last-msg-ticks-${contact.id}" class="text-[10px] leading-none"></span>
                    <span id="last-msg-text-${contact.id}" class="text-xs truncate text-gray-400">
                        No messages yet
                    </span>
                    <span id="sidebar-typing-${contact.id}" class="text-xs italic text-blue-500 hidden animate-pulse">typing...</span>
                </div>
            </div>
            <div class="flex flex-col items-end gap-1">
                <span id="last-msg-time-${contact.id}" class="text-[10px] text-gray-400 whitespace-nowrap message-time-live" data-timestamp="${new Date().toISOString()}">
                    Just now
                </span>
                <div id="status-dot-${contact.id}" class="w-3 h-3 rounded-full bg-gray-400 transition-colors duration-300" title="offline"></div>
                <span id="status-text-${contact.id}" class="text-[10px] text-gray-400 whitespace-nowrap last-seen-timer" data-timestamp="${new Date().toISOString()}">
                    Just now
                </span>
            </div>
        `;
        
        chatList.prepend(div);

        Echo.join(`user-status.${contact.id}`)
        .here((users) => {
            const isFriendOnline = users.some(u => u.id == contact.id);
            window.updateStatusUI(contact.id, isFriendOnline, activeContactId);
        })
        .joining((user) => { 
            if (user.id == contact.id) window.updateStatusUI(contact.id, true, activeContactId);
        })
        .leaving((user) => {
            if (user.id == contact.id) window.updateStatusUI(contact.id, false, activeContactId);
        });
    }



    // Wrapper for modular notification actions
    window.handleNotifActionWrapper = (id, action) => {
        window.handleNotifAction(id, action, '{{ csrf_token() }}', addContactToSidebar);
    };

    window.toggleNotificationsWrapper = () => {
        window.toggleNotifications("{{ route('notifications.readAll') }}", '{{ csrf_token() }}');
    };

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

        // Optimistic update
        const tempId = `temp-${Date.now()}`;
        window.appendMessageToUI({ messageId: tempId, message: msgText, is_sender: true }, {{ auth()->id() }}, window.formatMessageTime);
        input.value = '';
        window.scrollChatToBottom();

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

            const saved = await response.json();
            const tempBubble = document.getElementById(`msg-${tempId}`);
            if (tempBubble && saved.id) {
                tempBubble.id = `msg-${saved.id}`;
            }

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