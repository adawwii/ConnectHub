import './bootstrap';
import { formatTimeAgo, startStatusTimer, formatMessageTime } from './status-tracker';
import * as ChatUtils from './chat-utils';
import * as Presence from './presence-manager';
import * as Notifications from './notification-manager';
import * as ChatCore from './chat-core';

// Time Tracking
window.formatTimeAgo = formatTimeAgo;
window.formatMessageTime = formatMessageTime;
window.startStatusTimer = startStatusTimer;

// UI Utilities
window.scrollChatToBottom = ChatUtils.scrollChatToBottom;
window.getTicksHtml = ChatUtils.getTicksHtml;
window.toggleMessageDetails = ChatUtils.toggleMessageDetails;
window.highlightContact = ChatUtils.highlightContact;
window.filterChats = ChatUtils.filterChats;

// Presence
window.updateStatusUI = Presence.updateStatusUI;
window.setDotStatus = Presence.setDotStatus;

// Notifications
window.loadNotifications = Notifications.loadNotifications;
window.updateBadgeCount = Notifications.updateBadgeCount;
window.addNotificationToUI = Notifications.addNotificationToUI;
window.handleNotifAction = Notifications.handleNotifAction;
window.toggleNotifications = Notifications.toggleNotifications;

// Chat Core
window.receiveFallbackMessages = ChatCore.receiveFallbackMessages;
window.appendMessageToUI = ChatCore.appendMessageToUI;
window.updateMessageTicks = ChatCore.updateMessageTicks;
window.updateSidebarTicks = ChatCore.updateSidebarTicks;
window.messageSeen = ChatCore.messageSeen;
window.messageDeliveredSuccess = ChatCore.messageDeliveredSuccess;
window.markChatAsSeen = ChatCore.markChatAsSeen;
