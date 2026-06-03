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

    if (diffInSeconds < 60) return 'Just now';
    if (diffInMinutes < 60) return diffInMinutes + ' minutes ago';

    const nowMidnight = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const dateMidnight = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const diffInCalendarDays = Math.round((nowMidnight - dateMidnight) / (1000 * 60 * 60 * 24));

    // Check if it's today
    if (diffInCalendarDays === 0) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // Check if it's yesterday
    if (diffInCalendarDays === 1) return 'Yesterday';

    if (diffInCalendarDays < 7) return 'Last seen on ' + date.toLocaleDateString([], { weekday: 'long' });
    if (diffInCalendarDays < 14) return 'Last week';

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

    const nowMidnight = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const dateMidnight = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const diffInCalendarDays = Math.round((nowMidnight - dateMidnight) / (1000 * 60 * 60 * 24));

    // Today
    if (diffInCalendarDays === 0) {
        return timeStr;
    }

    // Yesterday
    if (diffInCalendarDays === 1) {
        return 'Yesterday at ' + timeStr;
    }

    // Within last 7 days
    if (diffInCalendarDays < 7) {
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
