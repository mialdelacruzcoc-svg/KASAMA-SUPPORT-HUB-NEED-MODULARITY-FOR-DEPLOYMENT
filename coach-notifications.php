<?php
require_once 'api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header('Location: index.php');
    exit;
}

$coach_name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];

// Get initials
$words = explode(" ", $coach_name);
$initials = "";
foreach ($words as $w) {
    if (!empty($w))
        $initials .= strtoupper($w[0]);
}
$display_initials = substr($initials, 0, 2);

// Get unread count
$unread_query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = '$user_id' AND is_read = 0";
$unread_result = mysqli_query($conn, $unread_query);
$unread_count = mysqli_fetch_assoc($unread_result)['count'] ?? 0;

// Get total count
$total_query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = '$user_id'";
$total_result = mysqli_query($conn, $total_query);
$total_count = mysqli_fetch_assoc($total_result)['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .notifications-container { max-width: 900px; margin: 0 auto; }
        
        /* Header */
        .notifications-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 25px; 
            flex-wrap: wrap; 
            gap: 15px; 
        }
        .notifications-header h1 { margin: 0; display: flex; align-items: center; gap: 10px; }
        .unread-badge { 
            background: #ef4444; 
            color: white; 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 14px; 
            font-weight: 600; 
        }
        
        /* Controls */
        .controls-row { 
            display: flex; 
            gap: 10px; 
            flex-wrap: wrap; 
            margin-bottom: 20px; 
            align-items: center;
        }
        .filter-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white !important;
            color: #333 !important;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .filter-btn:hover { border-color: #4a7c2c; color: #4a7c2c !important; }
        .filter-btn.active { background: #4a7c2c !important; color: white !important; border-color: #4a7c2c; }
        
        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-mark-all { background: #e0f2fe !important; color: #0369a1 !important; }
        .btn-mark-all:hover { background: #bae6fd !important; }
        .btn-delete-read { background: #fee2e2 !important; color: #dc2626 !important; }
        .btn-delete-read:hover { background: #fecaca !important; }
        
        .controls-right { margin-left: auto; display: flex; gap: 10px; }
        
        /* Stats */
        .stats-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-chip {
            background: #f3f4f6;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            color: #555;
        }
        .stat-chip strong { color: #333; }
        
        /* Notification List */
        .notification-list { 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.08); 
            overflow: hidden;
        }
        
        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 18px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
            position: relative;
        }
        .notification-item:hover { background: #f9fafb; }
        .notification-item:last-child { border-bottom: none; }
        .notification-item.unread { background: #f0f7ec; }
        .notification-item.unread:hover { background: #e5f0dd; }
        
        .notif-checkbox {
            margin-top: 4px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .notif-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .notif-icon.concern { background: #dbeafe; }
        .notif-icon.appointment { background: #fef3c7; }
        .notif-icon.reply { background: #d1fae5; }
        .notif-icon.status { background: #ede9fe; }
        .notif-icon.faq { background: #fce7f3; }
        .notif-icon.default { background: #f3f4f6; }
        
        .notif-content { flex: 1; min-width: 0; }
        .notif-title { 
            font-weight: 600; 
            color: #333; 
            margin-bottom: 4px; 
            display: flex; 
            align-items: center; 
            gap: 8px;
        }
        .notif-title .unread-dot {
            width: 8px;
            height: 8px;
            background: #4a7c2c;
            border-radius: 50%;
        }
        .notif-message { 
            font-size: 14px; 
            color: #666; 
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .notif-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 8px;
            font-size: 12px;
            color: #888;
        }
        .notif-type {
            background: #f3f4f6;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .notif-actions {
            display: flex;
            gap: 8px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .notification-item:hover .notif-actions { opacity: 1; }
        .notif-action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            background: #f3f4f6;
            color: #666;
        }
        .notif-action-btn:hover { background: #e5e7eb; }
        .notif-action-btn.delete { color: #dc2626; }
        .notif-action-btn.delete:hover { background: #fee2e2; }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        .empty-state .icon { font-size: 64px; margin-bottom: 15px; }
        .empty-state h3 { color: #555; margin-bottom: 8px; }
        
        /* Loading */
        .loading-spinner {
            text-align: center;
            padding: 30px;
            color: #888;
            display: none;
        }
        .loading-spinner.active { display: block; }
        
        /* Bulk Actions Bar */
        .bulk-actions {
            background: #1a4a72;
            color: white;
            padding: 12px 20px;
            display: none;
            align-items: center;
            gap: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .bulk-actions.active { display: flex; }
        .bulk-actions .selected-count { font-weight: 600; }
        .bulk-actions button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .bulk-actions button:hover { background: rgba(255,255,255,0.3); }
        .bulk-actions .btn-cancel { margin-left: auto; background: transparent; }
        
        .btn-back { 
            background: white !important; 
            color: #333 !important;
            border: 1px solid #ddd; 
            padding: 8px 16px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 14px; 
            font-weight: 500;
        }
        .btn-back:hover { background: #f5f5f5 !important; }

        /* End of list */
        .end-of-list {
            text-align: center;
            padding: 20px;
            color: #888;
            font-size: 14px;
            display: none;
        }

        @media (max-width: 600px) {
            .controls-right { margin-left: 0; width: 100%; }
            .notif-actions { opacity: 1; }
            .notification-item { padding: 15px; }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-content">
            <div class="nav-left"><span class="nav-title">Kasama Support Hub</span></div>
            <div class="nav-right">
                <a href="api/logout.php" style="color:white; text-decoration:none; font-weight:bold;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <header class="dashboard-header">
            <div class="header-left">
                <img src="images/phinma-logo.png" alt="Logo" class="header-logo">
                <span class="header-title">All Notifications</span>
            </div>
            <div class="header-right">
                <button class="btn-back" onclick="window.location.href='coach-dashboard.php'">← Back to Dashboard</button>
                <?php include 'includes/profile-dropdown.php'; ?>
            </div>
        </header>

        <main class="dashboard-main">
            <div class="notifications-container">
                <!-- Header -->
                <div class="notifications-header">
                    <h1>
                        🔔 Notifications 
                        <?php if ($unread_count > 0): ?>
                        <span class="unread-badge"><?php echo $unread_count; ?> unread</span>
                        <?php
endif; ?>
                    </h1>
                </div>

                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-chip"><strong id="totalCount"><?php echo $total_count; ?></strong> Total</div>
                    <div class="stat-chip"><strong id="unreadCount"><?php echo $unread_count; ?></strong> Unread</div>
                    <div class="stat-chip"><strong id="readCount"><?php echo $total_count - $unread_count; ?></strong> Read</div>
                </div>

                <!-- Controls -->
                <div class="controls-row">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="unread">Unread</button>
                    <button class="filter-btn" data-filter="concern">Concerns</button>
                    <button class="filter-btn" data-filter="appointment">Appointments</button>
                    <button class="filter-btn" data-filter="reply">Replies</button>
                    
                    <div class="controls-right">
                        <button class="action-btn btn-mark-all" onclick="markAllAsRead()">✓ Mark All Read</button>
                        <button class="action-btn btn-delete-read" onclick="deleteAllRead()">🗑️ Delete Read</button>
                    </div>
                </div>

                <!-- Bulk Actions Bar -->
                <div class="bulk-actions" id="bulkActions">
                    <span class="selected-count"><span id="selectedCount">0</span> selected</span>
                    <button onclick="markSelectedAsRead()">✓ Mark Read</button>
                    <button onclick="deleteSelected()">🗑️ Delete</button>
                    <button class="btn-cancel" onclick="clearSelection()">✕ Cancel</button>
                </div>

                <!-- Notification List -->
                <div class="notification-list" id="notificationList">
                    <!-- Notifications will be loaded here -->
                </div>

                <!-- Loading Spinner -->
                <div class="loading-spinner" id="loadingSpinner">
                    ⏳ Loading more notifications...
                </div>

                <!-- End of List -->
                <div class="end-of-list" id="endOfList">
                    ✅ You've seen all notifications
                </div>

                <!-- Empty State -->
                <div class="empty-state" id="emptyState" style="display: none;">
                    <div class="icon">🔔</div>
                    <h3>No notifications</h3>
                    <p>You're all caught up!</p>
                </div>
            </div>
        </main>
    </div>

    <script>
    // State
    let currentFilter = 'all';
    let currentPage = 0;
    let isLoading = false;
    let hasMore = true;
    let selectedIds = new Set();
    const ITEMS_PER_PAGE = 20;

    // Icons for notification types
    const typeIcons = {
        'new_concern': '📩',
        'concern_submitted': '📩',
        'student_reply': '💬',
        'response_added': '✉️',
        'status_changed': '🔄',
        'new_appointment': '📅',
        'appointment_booked': '📅',
        'appointment_confirmed': '✅',
        'appointment_reschedule': '📅',
        'appointment_completed': '✔️',
        'new_faq': '📚',
        'default': '🔔'
    };

    const typeClasses = {
        'new_concern': 'concern',
        'concern_submitted': 'concern',
        'student_reply': 'reply',
        'response_added': 'reply',
        'status_changed': 'status',
        'new_appointment': 'appointment',
        'appointment_booked': 'appointment',
        'appointment_confirmed': 'appointment',
        'appointment_reschedule': 'appointment',
        'appointment_completed': 'appointment',
        'new_faq': 'faq',
        'default': 'default'
    };

    const typeLabels = {
        'new_concern': 'Concern',
        'concern_submitted': 'Concern',
        'student_reply': 'Reply',
        'response_added': 'Reply',
        'status_changed': 'Status',
        'new_appointment': 'Appointment',
        'appointment_booked': 'Appointment',
        'appointment_confirmed': 'Appointment',
        'appointment_reschedule': 'Appointment',
        'appointment_completed': 'Appointment',
        'new_faq': 'FAQ',
        'default': 'Notification'
    };

    // Load notifications
    function loadNotifications(reset = false) {
        if (isLoading || (!hasMore && !reset)) return;
        
        if (reset) {
            currentPage = 0;
            hasMore = true;
            document.getElementById('notificationList').innerHTML = '';
            document.getElementById('endOfList').style.display = 'none';
        }

        isLoading = true;
        document.getElementById('loadingSpinner').classList.add('active');

        const offset = currentPage * ITEMS_PER_PAGE;
        
        fetch(`api/get-notifications-page.php?filter=${currentFilter}&limit=${ITEMS_PER_PAGE}&offset=${offset}`)
            .then(response => response.json())
            .then(data => {
                isLoading = false;
                document.getElementById('loadingSpinner').classList.remove('active');

                if (data.success) {
                    const list = document.getElementById('notificationList');
                    
                    if (data.notifications.length === 0 && currentPage === 0) {
                        document.getElementById('emptyState').style.display = 'block';
                        list.style.display = 'none';
                    } else {
                        document.getElementById('emptyState').style.display = 'none';
                        list.style.display = 'block';
                        
                        data.notifications.forEach(notif => {
                            list.appendChild(createNotificationItem(notif));
                        });

                        if (data.notifications.length < ITEMS_PER_PAGE) {
                            hasMore = false;
                            document.getElementById('endOfList').style.display = 'block';
                        }
                        
                        currentPage++;
                    }
                }
            })
            .catch(err => {
                isLoading = false;
                document.getElementById('loadingSpinner').classList.remove('active');
                console.error('Error loading notifications:', err);
            });
    }

    // Create notification item HTML
    function createNotificationItem(notif) {
        const div = document.createElement('div');
        div.className = 'notification-item' + (notif.is_read == 0 ? ' unread' : '');
        div.dataset.id = notif.id;
        
        const icon = typeIcons[notif.type] || typeIcons['default'];
        const iconClass = typeClasses[notif.type] || typeClasses['default'];
        const typeLabel = typeLabels[notif.type] || typeLabels['default'];
        const timeAgo = formatTimeAgo(notif.created_at);
        
        div.innerHTML = `
            <input type="checkbox" class="notif-checkbox" onchange="toggleSelect(${notif.id}, this.checked)" ${selectedIds.has(notif.id) ? 'checked' : ''}>
            <div class="notif-icon ${iconClass}">${icon}</div>
            <div class="notif-content" onclick="openNotification(${notif.id}, '${notif.link || ''}')">
                <div class="notif-title">
                    ${notif.is_read == 0 ? '<span class="unread-dot"></span>' : ''}
                    ${escapeHtml(notif.title)}
                </div>
                <div class="notif-message">${escapeHtml(notif.message)}</div>
                <div class="notif-meta">
                    <span class="notif-type">${typeLabel}</span>
                    <span>📅 ${timeAgo}</span>
                </div>
            </div>
            <div class="notif-actions">
                ${notif.is_read == 0 ? `<button class="notif-action-btn" onclick="event.stopPropagation(); markAsRead(${notif.id})">✓ Read</button>` : ''}
                <button class="notif-action-btn delete" onclick="event.stopPropagation(); deleteNotification(${notif.id})">🗑️</button>
            </div>
        `;
        
        return div;
    }

    // Format time ago
    function formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
        if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
        if (seconds < 604800) return Math.floor(seconds / 86400) + 'd ago';
        return date.toLocaleDateString();
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Open notification
    function openNotification(id, link) {
        // Mark as read first
        fetch('api/mark-notification-read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        }).then(() => {
            if (link) {
                window.location.href = link;
            } else {
                // Just mark as read visually
                const item = document.querySelector(`.notification-item[data-id="${id}"]`);
                if (item) {
                    item.classList.remove('unread');
                    const dot = item.querySelector('.unread-dot');
                    if (dot) dot.remove();
                    const readBtn = item.querySelector('.notif-action-btn:not(.delete)');
                    if (readBtn) readBtn.remove();
                }
                updateCounts();
            }
        });
    }

    // Mark single as read
    function markAsRead(id) {
        fetch('api/mark-notification-read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`.notification-item[data-id="${id}"]`);
                if (item) {
                    item.classList.remove('unread');
                    const dot = item.querySelector('.unread-dot');
                    if (dot) dot.remove();
                    const readBtn = item.querySelector('.notif-action-btn:not(.delete)');
                    if (readBtn) readBtn.remove();
                }
                updateCounts();
            }
        });
    }

    // Mark all as read
    function markAllAsRead() {
        if (!confirm('Mark all notifications as read?')) return;
        
        fetch('api/mark-all-notifications-read.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                        const dot = item.querySelector('.unread-dot');
                        if (dot) dot.remove();
                        const readBtn = item.querySelector('.notif-action-btn:not(.delete)');
                        if (readBtn) readBtn.remove();
                    });
                    updateCounts();
                }
            });
    }

    // Delete notification
    function deleteNotification(id) {
        if (!confirm('Delete this notification?')) return;
        
        fetch('api/delete-notification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`.notification-item[data-id="${id}"]`);
                if (item) {
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(100px)';
                    setTimeout(() => item.remove(), 300);
                }
                updateCounts();
            }
        });
    }

    // Delete all read
    function deleteAllRead() {
        if (!confirm('Delete all read notifications? This cannot be undone.')) return;
        
        fetch('api/delete-read-notifications.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item:not(.unread)').forEach(item => {
                        item.remove();
                    });
                    updateCounts();
                    alert(`${data.deleted} notifications deleted`);
                }
            });
    }

    // Selection handling
    function toggleSelect(id, checked) {
        if (checked) {
            selectedIds.add(id);
        } else {
            selectedIds.delete(id);
        }
        updateBulkActions();
    }

    function updateBulkActions() {
        const bulkBar = document.getElementById('bulkActions');
        const countSpan = document.getElementById('selectedCount');
        
        if (selectedIds.size > 0) {
            bulkBar.classList.add('active');
            countSpan.textContent = selectedIds.size;
        } else {
            bulkBar.classList.remove('active');
        }
    }

    function clearSelection() {
        selectedIds.clear();
        document.querySelectorAll('.notif-checkbox').forEach(cb => cb.checked = false);
        updateBulkActions();
    }

    function markSelectedAsRead() {
        const ids = Array.from(selectedIds);
        
        fetch('api/mark-notifications-read-bulk.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ids.forEach(id => {
                    const item = document.querySelector(`.notification-item[data-id="${id}"]`);
                    if (item) {
                        item.classList.remove('unread');
                        const dot = item.querySelector('.unread-dot');
                        if (dot) dot.remove();
                    }
                });
                clearSelection();
                updateCounts();
            }
        });
    }

    function deleteSelected() {
        if (!confirm(`Delete ${selectedIds.size} selected notifications?`)) return;
        
        const ids = Array.from(selectedIds);
        
        fetch('api/delete-notifications-bulk.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ids.forEach(id => {
                    const item = document.querySelector(`.notification-item[data-id="${id}"]`);
                    if (item) item.remove();
                });
                clearSelection();
                updateCounts();
            }
        });
    }

    // Update counts
    function updateCounts() {
        fetch('api/get-notification-counts.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('totalCount').textContent = data.total;
                    document.getElementById('unreadCount').textContent = data.unread;
                    document.getElementById('readCount').textContent = data.total - data.unread;
                    
                    const badge = document.querySelector('.unread-badge');
                    if (data.unread > 0) {
                        if (badge) {
                            badge.textContent = data.unread + ' unread';
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                }
            });
    }

    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            loadNotifications(true);
        });
    });

    // Infinite scroll
    window.addEventListener('scroll', () => {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
            loadNotifications();
        }
    });

    // Initial load
    loadNotifications();
    </script>
</body>
</html>