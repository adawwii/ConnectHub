// Calculate the difference between server time and local browser time once on load
const serverTimeMeta = document.querySelector('meta[name="server-time"]');
const serverTimeOnLoad = serverTimeMeta ? new Date(serverTimeMeta.content) : new Date();
const localTimeOnLoad = new Date();
const timeDrift = serverTimeOnLoad.getTime() - localTimeOnLoad.getTime();

/**
 * Returns a 'now' date adjusted for server clock drift
 */
function getAdjustedNow() {
    return new Date(Date.now() + timeDrift);
}

/**
 * Formats a timestamp into a human-readable status string.
 */
export function formatTimeAgo(timestamp) {
    if (!timestamp) return 'a long time ago';
    
    const date = new Date(timestamp);
    const now = getAdjustedNow();
    const diffInSeconds = Math.floor((now - date) / 1000);
    const diffInMinutes = Math.floor(diffInSeconds / 60);
    const diffInHours = Math.floor(diffInMinutes / 60);
    const diffInDays = Math.floor(diffInHours / 24);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInMinutes < 60) return diffInMinutes + ' minutes ago';
    
    // Check if it's today
    if (date.toDateString() === now.toDateString()) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // Check if it's yesterday
    const yesterday = new Date();
    yesterday.setDate(now.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';

    if (diffInDays < 7) return 'Last seen on ' + date.toLocaleDateString([], { weekday: 'long' });
    if (diffInDays < 14) return 'Last week';
    
    return date.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
}

/**
 * Specialized formatter for message details (always includes time for past dates)
 */
export function formatMessageTime(timestamp) {
    if (!timestamp) return '';
    
    const date = new Date(timestamp);
    const now = getAdjustedNow();
    const diffInSeconds = Math.floor((now - date) / 1000);
    const diffInMinutes = Math.floor(diffInSeconds / 60);
    const timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    if (diffInSeconds < 60) return 'Just now';
    if (diffInMinutes < 60) return diffInMinutes + ' minutes ago';
    
    // Today
    if (date.toDateString() === now.toDateString()) {
        return timeStr;
    }

    // Yesterday
    const yesterday = new Date();
    yesterday.setDate(now.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday at ' + timeStr;
    }

    // Within last 7 days
    const diffInDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
    if (diffInDays < 7) {
        return date.toLocaleDateString([], { weekday: 'long' }) + ' at ' + timeStr;
    }
    
    // Older
    return date.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' at ' + timeStr;
}

/**
 * Initializes a global timer to refresh all elements with the 'last-seen-timer' class.
 */
export function startStatusTimer() {
    // Run once immediately so existing timestamps render correctly on load
    _tickTimestamps();
    // Then keep refreshing every minute
    setInterval(_tickTimestamps, 60000);
}

function _tickTimestamps() {
    // Refresh last seen timers (Sidebar)
    document.querySelectorAll('.last-seen-timer').forEach(el => {
        if (el.innerText !== 'Online' && el.dataset.timestamp) {
            el.innerText = formatTimeAgo(el.dataset.timestamp);
        }
    });
    
    // Refresh message detail timers
    document.querySelectorAll('.message-time-live').forEach(el => {
        if (el.dataset.timestamp) {
            const prefix = el.dataset.prefix || '';
            el.innerText = prefix + formatMessageTime(el.dataset.timestamp);
        }
    });
}
