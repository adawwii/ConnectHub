/**
 * Formats a timestamp into a human-readable status string.
 */
export function formatTimeAgo(timestamp) {
    if (!timestamp) return 'a long time ago';
    
    const date = new Date(timestamp);
    const now = new Date();
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
 * Initializes a global timer to refresh all elements with the 'last-seen-timer' class.
 */
export function startStatusTimer() {
    setInterval(() => {
        document.querySelectorAll('.last-seen-timer').forEach(el => {
            if (el.innerText !== 'Online' && el.dataset.timestamp) {
                el.innerText = formatTimeAgo(el.dataset.timestamp);
            }
        });
    }, 60000); // Update every minute
}
