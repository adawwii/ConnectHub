/**
 * Manages User Presence and Online Status Indicators
 */

export function updateStatusUI(userId, isOnline, activeContactId) {
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

    // Update Header if this user is the one we are currently chatting with
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

export function setDotStatus(el, isOnline) {
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
