/**
 * Core Chat Logic and Messaging Services
 */

import { scrollChatToBottom, getTicksHtml, highlightContact } from './chat-utils';

export function appendMessageToUI(msg, authId, formatMessageTimeCallback) {
    const container = document.getElementById('messages');

    // Remove placeholder if it exists
    const placeholder = document.getElementById('no-messages-placeholder');
    if (placeholder) placeholder.remove();

    const div = document.createElement('div');
    div.className = `flex flex-col ${msg.is_sender ? 'items-end' : 'items-start'}`;

    // Only include the tick status container if it's a message WE sent
    const statusHtml = msg.is_sender
        ? `<span class="tick-status block text-right text-[10px] mt-0.5 leading-none opacity-80">${getTicksHtml(msg.delivered_at, msg.seen_at)}</span>`
        : '';

    const detailsHtml = msg.is_sender
        ? `<div id="details-${msg.messageId}" class="message-details text-gray-500 pr-2 text-right">
            <div class="delivered-at message-time-live" data-timestamp="${msg.delivered_at || ''}" data-prefix="Delivered at: ">
                Delivered at: ${msg.delivered_at ? formatMessageTimeCallback(msg.delivered_at) : 'Pending...'}
            </div>
            <div class="seen-at message-time-live" data-timestamp="${msg.seen_at || ''}" data-prefix="Seen at: ">
                Seen at: ${msg.seen_at ? formatMessageTimeCallback(msg.seen_at) : 'Unread'}
            </div>
           </div>`
        : `<div id="details-${msg.messageId}" class="message-details text-gray-500 pl-2 text-left">
            <div class="message-time-live" data-timestamp="${msg.created_at || new Date().toISOString()}" data-prefix="Received at: ">
                Received at: ${formatMessageTimeCallback(msg.created_at || new Date().toISOString())}
            </div>
           </div>`;

    div.innerHTML = `
        <div id="msg-${msg.messageId}" 
             onclick="window.toggleMessageDetails('${msg.messageId}')"
             class="message-bubble ${msg.is_sender ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'} px-4 py-2 rounded-lg max-w-[75%] md:max-w-md shadow-sm break-words">
            <span>${msg.message}</span>
            ${statusHtml}
        </div>
        ${detailsHtml}
    `;
    container.appendChild(div);
}

export function prependMessageToUI(msg, authId, formatMessageTimeCallback) {
    const container = document.getElementById('messages');

    // Remove placeholder if it exists
    const placeholder = document.getElementById('no-messages-placeholder');
    if (placeholder) placeholder.remove();

    const div = document.createElement('div');
    div.className = `flex flex-col ${msg.is_sender ? 'items-end' : 'items-start'}`;

    // Only include the tick status container if it's a message WE sent
    const statusHtml = msg.is_sender
        ? `<span class="tick-status block text-right text-[10px] mt-0.5 leading-none opacity-80">${getTicksHtml(msg.delivered_at, msg.seen_at)}</span>`
        : '';

    const detailsHtml = msg.is_sender
        ? `<div id="details-${msg.messageId}" class="message-details text-gray-500 pr-2 text-right">
            <div class="delivered-at message-time-live" data-timestamp="${msg.delivered_at || ''}" data-prefix="Delivered at: ">
                Delivered at: ${msg.delivered_at ? formatMessageTimeCallback(msg.delivered_at) : 'Pending...'}
            </div>
            <div class="seen-at message-time-live" data-timestamp="${msg.seen_at || ''}" data-prefix="Seen at: ">
                Seen at: ${msg.seen_at ? formatMessageTimeCallback(msg.seen_at) : 'Unread'}
            </div>
           </div>`
        : `<div id="details-${msg.messageId}" class="message-details text-gray-500 pl-2 text-left">
            <div class="message-time-live" data-timestamp="${msg.created_at || new Date().toISOString()}" data-prefix="Received at: ">
                Received at: ${formatMessageTimeCallback(msg.created_at || new Date().toISOString())}
            </div>
           </div>`;

    div.innerHTML = `
        <div id="msg-${msg.messageId}" 
             onclick="window.toggleMessageDetails('${msg.messageId}')"
             class="message-bubble ${msg.is_sender ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'} px-4 py-2 rounded-lg max-w-[75%] md:max-w-md shadow-sm break-words">
            <span>${msg.message}</span>
            ${statusHtml}
        </div>
        ${detailsHtml}
    `;
    container.prepend(div);
}

export function updateMessageTicks(messageId, deliveredAt, seenAt, authId, chatId, activeContactId, updateSidebarTicksCallback) {
    const msgBubble = document.getElementById(`msg-${messageId}`);
    const details = document.getElementById(`details-${messageId}`);

    if (msgBubble) {
        const ticksContainer = msgBubble.querySelector('.tick-status');
        if (ticksContainer) {
            ticksContainer.innerHTML = getTicksHtml(deliveredAt, seenAt);
        }
    }

    if (details) {
        if (deliveredAt && typeof window.formatMessageTime === 'function') {
            const delEl = details.querySelector('.delivered-at');
            if (delEl) {
                delEl.dataset.timestamp = deliveredAt;
                const prefix = delEl.dataset.prefix || 'Delivered at: ';
                delEl.innerText = prefix + window.formatMessageTime(deliveredAt);
            }
        }
        if (seenAt && typeof window.formatMessageTime === 'function') {
            const seenEl = details.querySelector('.seen-at');
            if (seenEl) {
                seenEl.dataset.timestamp = seenAt;
                const prefix = seenEl.dataset.prefix || 'Seen at: ';
                seenEl.innerText = prefix + window.formatMessageTime(seenAt);
            }
        }
    }

    // Since someone just saw/delivered a message, we know they are active
    // If we have a way to identify the other party, we can update their status
    const chatItem = document.getElementById(`contact-${activeContactId}`);
    if (chatItem && seenAt) {
        window.updateStatusUI(activeContactId, true, activeContactId);
    }

    // Update sidebar ticks if needed
    if (updateSidebarTicksCallback) {
        let targetContactId = null;
        if (chatId) {
            const contactItem = document.querySelector(`.chat-item[data-chat-id="${chatId}"]`);
            if (contactItem) targetContactId = contactItem.id.split('-')[1];
        } else if (activeContactId) {
            targetContactId = activeContactId;
        }

        if (targetContactId) {
            updateSidebarTicksCallback(targetContactId, deliveredAt, seenAt);
        }
    }
}

export function updateSidebarTicks(contactId, deliveredAt, seenAt) {
    const ticksSpan = document.getElementById(`last-msg-ticks-${contactId}`);
    if (ticksSpan) {
        ticksSpan.innerHTML = getTicksHtml(deliveredAt, seenAt);
    }
}

export async function messageSeen(msgId, csrfToken, seenRoute) {
    fetch(seenRoute, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ message: msgId })
    })
        .catch(error => console.error('Error in messageSeen:', error));
}

export async function messageDeliveredSuccess(senderId, messageId, csrfToken, deliveredRoute) {
    fetch(deliveredRoute, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ userId: senderId, messageId: messageId })
    }).catch(error => console.error('Error in messageDeliveredSuccess:', error));
}

export async function receiveFallbackMessages(fallbackRoute, csrfToken) {
    fetch(fallbackRoute, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
    })
        .catch(error => console.error('Error in receiveFallbackMessages:', error));
}

export async function markChatAsSeen(chatId, csrfToken, seenRoute) {
    fetch(seenRoute, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ chat_id: chatId })
    }).catch(error => console.error('Error in markChatAsSeen:', error));
}
