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
            <span class="absolute -top-1 -right-2 bg-red-500 text-white text-xs px-1 rounded-full">
                {{ auth()->user()->unreadNotifications->count() }}
            </span>
        </button>

        <!-- Dropdown -->
        <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white shadow rounded-lg">
            <div class="p-3 border-b font-semibold">Notifications</div>
            <div class="max-h-60 overflow-y-auto">
                @forelse (auth()->user()->unreadNotifications as $notification)
                    <div class="p-3 border-b">
                        <div class="text-sm">{{ $notification->data['message'] }}</div>
                        <div class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="p-3 text-sm text-gray-500">No new notifications.</div>
                @endforelse
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
            <div class="p-3 border-b cursor-pointer hover:bg-gray-100 chat-item">
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
    //websocket for notification channel
    window.addEventListener('DOMContentLoaded', () => {
    Echo.private('user.{{ auth()->id() }}')
        .subscribed(() => {
            console.log('Subscribed to user.{{ auth()->id()}} channel');
        })
        .error((error) => {
            console.error('Subscription error:', error);
        })
        .notification((notification) => {
            console.log('New notification:', notification);
           //add to the notification dropdown
           let dropdown = document.getElementById('notifDropdown');
              let div = document.createElement('div');
                div.className = 'p-3 border-b';
                div.innerHTML = `
                    <div class="text-sm">${notification.message}</div>
                    <div class="text-xs text-gray-500">Just now</div>
                `;      
            dropdown.insertBefore(div, dropdown.children[1]);
                //update the badge count
            let badge = document.querySelector('button[onclick="toggleNotifications()"] span');
            let count = parseInt(badge.innerText) || 0;
            badge.innerText = count + 1;
            
            
        });
        });

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

// ➕ Search user by email (backend later)
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
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            email: email
        })
    })
    .then(response => response.json())
    .then(data => {
        btn.innerText = 'Add Friend';
        btn.disabled = false;
        console.log(data.response)
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