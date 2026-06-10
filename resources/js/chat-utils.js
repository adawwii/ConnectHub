/**
 * UI Utilities for the Chat Application
 */

export function scrollChatToBottom() {
    const container = document.getElementById('messages');
    if (container) {
        container.scrollTo({
            top: container.scrollHeight,
            behavior: 'smooth'
        });
    }
}

export function getTicksHtml(delivered_at, seen_at) {
    if (seen_at)      return '<span style="color:#00ff00;font-weight:800;pointer-events:none;" title="Seen">✓✓</span>';
    if (delivered_at) return '<span style="color:#dfdfdf; font-weight:800;pointer-events:none;" title="Delivered">✓✓</span>';
    return '<span style="color:#e2e8f0" title="Sent">✓</span>';
}

export function toggleMessageDetails(messageId) {
    const details = document.getElementById(`details-${messageId}`);
    if (details) {
        details.classList.toggle('show');
    }
}

export function highlightContact(id) {
    document.querySelectorAll('.chat-item').forEach(el => {
        el.classList.remove('bg-blue-50', 'dark:bg-blue-950/40', 'border-l-4', 'border-blue-500', 'dark:border-blue-400');
    });
    const activeEl = document.getElementById(`contact-${id}`);
    if (activeEl) {
        activeEl.classList.add('bg-blue-50', 'dark:bg-blue-950/40', 'border-l-4', 'border-blue-500', 'dark:border-blue-400');
    }
}

export function filterChats(query) {
    const items = document.querySelectorAll('.chat-item');
    const lowerQuery = query.toLowerCase();
    items.forEach(item => {
        const name = item.querySelector('span:first-child')?.innerText.toLowerCase() || '';
        if (name.includes(lowerQuery)) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });
}
