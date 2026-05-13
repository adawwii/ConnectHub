/**
 * Manages the Notification System
 */

export function updateBadgeCount(delta = 0) {
    const badge = document.getElementById('notifBadge');
    if (!badge) return;
    let count = parseInt(badge.innerText) || 0;
    
    if (delta !== 0) {
        count += delta;
    } else {
        const container = document.getElementById('notificationsContainer');
        count = container.querySelectorAll('.unread').length;
    }

    badge.innerText = count;
    if (count > 0) {
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

export function addNotificationToUI(notif, isNew = false, updateBadgeCallback) {
    const container = document.getElementById('notificationsContainer');
    const emptyMsg = container.querySelector('.text-gray-500');
    if (emptyMsg) emptyMsg.remove();

    const id = notif.id || 'new';
    const data = notif.data || notif;
    const dataType = data.data_type || notif.data_type;
    const status = data.status || notif.status;
    const message = data.message || notif.message || 'New notification';
    const senderName = data.sender_name || notif.sender_name || 'User';
    const createdAt = notif.created_at || data.created_at;

    const div = document.createElement('div');
    const isUnread = notif.is_unread || isNew;
    div.className = `p-3 border-b hover:bg-gray-50 transition-colors ${isUnread ? 'unread bg-blue-50' : ''}`;
    div.id = `notif-${id}`;
    div.setAttribute('data-sender', senderName);

    let actionHtml = '';
    let displayMessage = message;
    let messageClass = 'text-gray-800';

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
                    <button onclick="window.handleNotifActionWrapper('${id}', 'accept')" class="bg-blue-500 text-white text-xs px-3 py-1 rounded hover:bg-blue-600">Accept</button>
                    <button onclick="window.handleNotifActionWrapper('${id}', 'reject')" class="bg-gray-200 text-gray-700 text-xs px-3 py-1 rounded hover:bg-gray-300">Reject</button>
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
        if (typeof updateBadgeCallback === 'function') {
            updateBadgeCallback(1);
        }
    } else {
        container.appendChild(div);
    }
}

export async function handleNotifAction(id, action, csrfToken, addContactCallback) {
    const notifElement = document.getElementById(`notif-${id}`);
    if (notifElement) notifElement.style.opacity = '0.5';

    try {
        const url = action === 'accept' ? `/notifications/${id}/accept` : `/notifications/${id}/reject`;
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok) {
            const senderName = notifElement.getAttribute('data-sender') || 'User';
            const msgText = notifElement.querySelector('.text-sm');
            if (msgText) {
                msgText.innerText = action === 'accept' ? `${senderName} accepted friend request` : `${senderName} rejected friend request`;
                msgText.classList.remove('text-gray-800');
                msgText.classList.add('text-gray-500');
            }

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
            
            if (action === 'accept' && result.contact && typeof addContactCallback === 'function') {
                addContactCallback(result.contact);
            }
        }
    } catch (error) {
        console.error(`Error during ${action}:`, error);
        if (notifElement) notifElement.style.opacity = '1';
    }
}

export function toggleNotifications(readAllUrl, csrfToken) {
    const dropdown = document.getElementById('notifDropdown');
    const badge = document.getElementById('notifBadge');

    dropdown.classList.toggle('hidden');

    if (!dropdown.classList.contains('hidden') && !badge.classList.contains('hidden')){
        badge.classList.add('hidden');
        badge.innerText = "0";

        document.querySelectorAll('#notificationsContainer .unread').forEach(el => {
            el.classList.remove('unread', 'bg-blue-50');
        });

        fetch(readAllUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        }).catch(error => console.error('Error marking notifications as read:', error));
    }
}

export async function loadNotifications(notificationsRoute, addNotifCallback, updateBadgeCallback) {
    try {
        const response = await fetch(notificationsRoute);
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
                
                addNotifCallback({
                    id: notif.id,
                    ...data,
                    created_at: notif.created_at,
                    is_unread: !notif.read_at 
                }, false, updateBadgeCallback);
            });
        }
        if (typeof updateBadgeCallback === 'function') {
            updateBadgeCallback();
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
    }
}
