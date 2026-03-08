<?php
// ============================================
// NOTIFICATION BELL COMPONENT
// Include this in any page that needs the bell icon
// ============================================
?>

<!-- Notification Bell Styles -->
<style>
    .notification-wrapper {
        position: relative;
        display: inline-block;
    }
    
    .notification-bell {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: background 0.2s;
        position: relative;
    }
    
    .notification-bell:hover {
        background: rgba(255,255,255,0.1);
    }
    
    .notification-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        background: #ff4444;
        color: white;
        border-radius: 50%;
        min-width: 18px;
        height: 18px;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        animation: pulse 2s infinite;
    }
    
    .notification-badge.hidden {
        display: none;
    }
    
    /* ===== DESKTOP DROPDOWN ===== */
    .notification-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        width: 360px;
        max-height: 450px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        z-index: 9999;
        display: none;
        overflow: hidden;
        animation: fadeIn 0.2s ease-out;
    }
    
    .notification-dropdown.show {
        display: block;
    }
    
    /* ===== MOBILE FULLSCREEN DROPDOWN ===== */
    @media screen and (max-width: 480px) {
        .notification-dropdown {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            max-height: 100%;
            border-radius: 0;
            z-index: 9999;
        }
        
        .notification-header {
            padding: 16px 20px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .notification-header h3 {
            font-size: 18px;
        }
        
        .notification-list {
            max-height: calc(100vh - 120px);
            padding-bottom: 80px;
        }
        
        .notification-item {
            padding: 16px;
        }
        
        /* Close button for mobile */
        .mobile-close-btn {
            display: block;
            position: absolute;
            top: 16px;
            right: 16px;
            background: #f0f0f0;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            z-index: 11;
        }
    }
    
    @media screen and (min-width: 481px) {
        .mobile-close-btn {
            display: none;
        }
    }
    
    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
    }
    
    .notification-header h3 {
        margin: 0;
        font-size: 16px;
        color: #333;
    }
    
    .mark-all-read {
        background: none;
        border: none;
        color: #4a7c2c;
        font-size: 13px;
        cursor: pointer;
        font-weight: 600;
        padding: 8px 12px;
        border-radius: 6px;
        transition: background 0.2s;
    }
    
    .mark-all-read:hover {
        background: #e8f5e9;
    }
    
    .notification-list {
        max-height: 350px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .notification-item {
        display: flex;
        gap: 12px;
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .notification-item:hover {
        background: #f8f9fa;
    }
    
    .notification-item.unread {
        background: #f0f7ec;
        border-left: 4px solid #4a7c2c;
    }
    
    .notification-item.unread:hover {
        background: #e5f2de;
    }
    
    .notification-icon {
        font-size: 24px;
        flex-shrink: 0;
    }
    
    .notification-content {
        flex: 1;
        min-width: 0;
    }
    
    .notification-title {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        margin-bottom: 4px;
    }
    
    .notification-message {
        color: #666;
        font-size: 13px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
    }
    
    .notification-time {
        color: #999;
        font-size: 11px;
        margin-top: 6px;
    }
    
    .notification-empty {
        padding: 60px 20px;
        text-align: center;
        color: #888;
    }
    
    .notification-empty-icon {
        font-size: 56px;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .notification-empty p {
        font-size: 15px;
    }
    
    .see-all-link {
        display: block;
        text-align: center;
        padding: 14px;
        background: #f8f9fa;
        color: #4a7c2c;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        border-top: 1px solid #eee;
    }
    
    .see-all-link:hover {
        background: #f0f0f0;
    }
    
    /* Pulse animation for badge */
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    
    /* Fade in animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<!-- Notification Bell HTML -->
<!-- Notification Bell HTML -->
<div class="notification-wrapper">
    <button class="notification-bell" id="notificationBell" onclick="toggleNotifications()">
        🔔
        <span class="notification-badge hidden" id="notificationBadge">0</span>
    </button>
    
    <div class="notification-dropdown" id="notificationDropdown">
        <button class="mobile-close-btn" onclick="closeNotifications()">✕</button>
        <div class="notification-header">
            <h3>Notifications</h3>
            <button class="mark-all-read" onclick="markAllAsRead()">Mark all as read</button>
        </div>
        <div class="notification-list" id="notificationList">
            <!-- Notifications will be loaded here -->
        </div>
    </div>
</div>

<!-- Notification Bell JavaScript -->
<script>
let notificationsOpen = false;

// Toggle dropdown
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    notificationsOpen = !notificationsOpen;
    
    if (notificationsOpen) {
        dropdown.classList.add('show');
        loadNotifications();
    } else {
        dropdown.classList.remove('show');
    }
}

// Close notifications (for mobile)
function closeNotifications() {
    document.getElementById('notificationDropdown').classList.remove('show');
    notificationsOpen = false;
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const wrapper = document.querySelector('.notification-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('notificationDropdown').classList.remove('show');
        notificationsOpen = false;
    }
});

// Load notifications
async function loadNotifications() {
    try {
        const response = await fetch('api/get-notifications.php?limit=10');
        const result = await response.json();
        
        if (result.success) {
            renderNotifications(result.data);
            updateBadge(result.unread_count);
        }
    } catch (error) {
        console.error('Failed to load notifications:', error);
    }
}

// Render notifications in dropdown
function renderNotifications(notifications) {
    const list = document.getElementById('notificationList');
    
    if (notifications.length === 0) {
        list.innerHTML = `
            <div class="notification-empty">
                <div class="notification-empty-icon">🔔</div>
                <p>No notifications yet</p>
            </div>
        `;
        return;
    }
    
    list.innerHTML = notifications.map(notif => `
        <div class="notification-item ${notif.is_read == 0 ? 'unread' : ''}" 
             onclick="handleNotificationClick(${notif.id}, '${notif.url || ''}', ${notif.is_read})">
            <span class="notification-icon">${notif.icon}</span>
            <div class="notification-content">
                <div class="notification-title">${escapeHtml(notif.title)}</div>
                <div class="notification-message">${escapeHtml(notif.message)}</div>
                <div class="notification-time">${notif.time_ago}</div>
            </div>
        </div>
    `).join('');
}

// Handle notification click
async function handleNotificationClick(id, url, isRead) {
    // Mark as read if unread
    if (!isRead) {
        await markAsRead(id);
    }
    
    // Navigate to URL if provided
    if (url) {
        window.location.href = url;
    }
}

// Mark single notification as read
async function markAsRead(id) {
    try {
        const formData = new FormData();
        formData.append('notification_id', id);
        
        await fetch('api/mark-notification-read.php', {
            method: 'POST',
            body: formData
        });
        
        // Refresh badge count
        fetchUnreadCount();
    } catch (error) {
        console.error('Failed to mark as read:', error);
    }
}

// Mark all as read
async function markAllAsRead() {
    try {
        const formData = new FormData();
        formData.append('mark_all', 'true');
        
        await fetch('api/mark-notification-read.php', {
            method: 'POST',
            body: formData
        });
        
        // Refresh notifications
        loadNotifications();
        updateBadge(0);
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    }
}

// Update badge count
function updateBadge(count) {
    const badge = document.getElementById('notificationBadge');
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

// Fetch unread count only (for badge updates)
async function fetchUnreadCount() {
    try {
        const response = await fetch('api/get-notifications.php?limit=1');
        const result = await response.json();
        
        if (result.success) {
            updateBadge(result.unread_count);
        }
    } catch (error) {
        console.error('Failed to fetch unread count:', error);
    }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initial load - fetch unread count on page load
document.addEventListener('DOMContentLoaded', function() {
    fetchUnreadCount();
    
    // Auto-refresh every 60 seconds
    setInterval(fetchUnreadCount, 60000);
});
</script>