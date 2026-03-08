<?php
require_once 'api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit;
}

$student_name = $_SESSION['name'];
$student_id = $_SESSION['student_id'];

$words = explode(" ", $student_name);
$initials = "";
foreach ($words as $w) {
    if (!empty($w))
        $initials .= strtoupper($w[0]);
}
$display_initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Concerns - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
    /* ==================== MOBILE-FIRST MY CONCERNS PAGE ==================== */
    
    * {
        box-sizing: border-box;
    }
    
    body {
        overflow-x: hidden;
        background: #f5f7fa;
    }
    
    /* Top Navigation */
    .top-nav {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        padding: 0 16px;
        height: 56px;
    }
    
    .nav-content {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .nav-title {
        font-size: 15px;
        font-weight: 600;
    }
    
    .nav-right a {
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        padding: 8px 16px;
        background: rgba(255,255,255,0.15);
        border-radius: 20px;
    }
    
    /* Dashboard Wrapper */
    .dashboard-wrapper {
        padding-top: 56px;
        min-height: 100vh;
    }
    
    /* Simplified Header for Mobile */
    .dashboard-header {
        background: white;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e5e7eb;
        position: sticky;
        top: 56px;
        z-index: 100;
    }
    
    .header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .header-logo {
        width: 36px;
        height: 36px;
    }
    
    .header-title {
        font-size: 15px;
        font-weight: 600;
        color: #333;
    }
    
    .header-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .btn-back {
        background: #f5f7fa;
        border: none;
        padding: 8px 14px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        color: #4a7c2c;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .btn-back:hover {
        background: #e8f5e9;
    }
    
    .user-profile {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        background: #f5f7fa;
        border-radius: 20px;
    }
    
    .user-avatar {
        width: 32px;
        height: 32px;
        font-size: 12px;
    }
    
    .user-name {
        display: none;
    }
    
    /* Main Content */
    .dashboard-main {
        padding: 0;
    }
    
    .concerns-container {
        padding: 16px;
        max-width: 100%;
    }
    
    /* Page Title */
    .page-title {
        font-size: 20px;
        font-weight: 700;
        color: #333;
        margin: 0 0 16px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* ===== STATS BAR - 2x2 Grid on Mobile ===== */
    .stats-bar {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .stat-item {
        background: white;
        padding: 16px 12px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        text-align: center;
    }
    
    .stat-item .number {
        font-size: 28px;
        font-weight: 700;
        color: #4a7c2c;
    }
    
    .stat-item .label {
        font-size: 11px;
        color: #666;
        margin-top: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-item.pending .number { color: #f59e0b; }
    .stat-item.in-progress .number { color: #3b82f6; }
    .stat-item.resolved .number { color: #10b981; }
    
    /* ===== FILTER BAR - Horizontal Scroll ===== */
    .filter-bar {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
        overflow-x: auto;
        padding-bottom: 8px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    
    .filter-bar::-webkit-scrollbar {
        display: none;
    }
    
    .filter-btn {
        padding: 10px 16px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 25px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
        transition: all 0.2s ease;
        color: #333;
    }
    
    .filter-btn:hover {
        border-color: #4a7c2c;
        color: #4a7c2c;
    }
    
    .filter-btn.active {
        background: #4a7c2c;
        color: white;
        border-color: #4a7c2c;
    }
    
    /* ===== CONCERN CARDS ===== */
    .concern-card {
        background: white;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-left: 4px solid #4a7c2c;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .concern-card:active {
        transform: scale(0.98);
    }
    
    .concern-card.status-pending { border-left-color: #f59e0b; }
    .concern-card.status-in-progress { border-left-color: #3b82f6; }
    .concern-card.status-resolved { border-left-color: #10b981; }
    
    .concern-header {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 10px;
    }
    
    .concern-title {
        font-size: 15px;
        font-weight: 600;
        color: #333;
        margin: 0;
        line-height: 1.4;
    }
    
    .concern-id {
        font-size: 11px;
        color: #666;
        background: #f0f0f0;
        padding: 4px 10px;
        border-radius: 20px;
        align-self: flex-start;
        font-family: monospace;
    }
    
    .concern-meta {
        display: flex;
        gap: 12px;
        margin-bottom: 10px;
        flex-wrap: wrap;
        font-size: 12px;
        color: #666;
    }
    
    .concern-meta span {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .concern-description {
        font-size: 13px;
        color: #555;
        line-height: 1.5;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .concern-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }
    
    /* Badges */
    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .badge-pending { background: #fef3c7; color: #b45309; }
    .badge-in-progress { background: #dbeafe; color: #1d4ed8; }
    .badge-resolved { background: #d1fae5; color: #059669; }
    
    /* Action Buttons */
    .concern-actions {
        display: flex;
        gap: 8px;
    }
    
    .btn-view {
        background: #4a7c2c;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .btn-view:hover {
        background: #3d6624;
    }
    
    .btn-delete {
        background: white;
        color: #dc2626;
        border: 2px solid #dc2626;
        padding: 6px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
    }
    
    .btn-delete:hover {
        background: #dc2626;
        color: white;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #888;
    }
    
    .empty-state h3 {
        font-size: 48px;
        margin-bottom: 10px;
    }
    
    .empty-state p {
        font-size: 14px;
        color: #666;
    }
    
    /* ===== MODAL ===== */
    .modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
    }
    
    .modal-content {
        background: white;
        margin: auto;
        padding: 24px;
        border-radius: 16px;
        width: 90%;
        max-width: 360px;
        text-align: center;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        animation: modalSlideIn 0.3s ease;
    }
    
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
    }
    
    .modal-content h3 {
        font-size: 18px;
        margin-bottom: 12px;
        color: #333;
    }
    
    .modal-content p {
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
        line-height: 1.5;
    }
    
    .modal-buttons {
        display: flex;
        gap: 12px;
        justify-content: center;
    }
    
    .btn-cancel {
        background: #e5e7eb;
        color: #374151;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        flex: 1;
    }
    
    .btn-confirm-delete {
        background: #dc2626;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        flex: 1;
    }
    
    .btn-confirm-delete:hover {
        background: #b91c1c;
    }
    
    /* ==================== TABLET & DESKTOP ==================== */
    @media screen and (min-width: 768px) {
        .top-nav {
            height: 60px;
            padding: 0 24px;
        }
        
        .dashboard-wrapper {
            padding-top: 60px;
        }
        
        .dashboard-header {
            top: 60px;
            padding: 16px 24px;
        }
        
        .header-logo {
            width: 40px;
            height: 40px;
        }
        
        .header-title {
            font-size: 16px;
        }
        
        .user-name {
            display: inline;
            font-size: 13px;
            font-weight: 600;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            font-size: 13px;
        }
        
        .concerns-container {
            padding: 24px;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .page-title {
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        /* Stats Bar - 4 columns on desktop */
        .stats-bar {
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
        
        .stat-item {
            padding: 20px;
        }
        
        .stat-item .number {
            font-size: 36px;
        }
        
        .stat-item .label {
            font-size: 12px;
        }
        
        /* Filter bar no scroll on desktop */
        .filter-bar {
            flex-wrap: wrap;
            overflow-x: visible;
        }
        
        /* Cards - Better layout */
        .concern-card {
            padding: 20px;
        }
        
        .concern-header {
            flex-direction: row;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .concern-title {
            font-size: 17px;
        }
        
        .concern-description {
            font-size: 14px;
            -webkit-line-clamp: 3;
        }
    }
    
    /* Large Desktop */
    @media screen and (min-width: 1024px) {
        .concerns-container {
            max-width: 1000px;
        }
    }
</style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-content">
            <div class="nav-left">
                <span class="nav-title">Kasama Support Hub</span>
            </div>
            <div class="nav-right">
                <a href="api/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <header class="dashboard-header">
            <div class="header-left">
                <img src="images/phinma-logo.png" alt="Logo" class="header-logo">
                <span class="header-title">My Concerns</span>
            </div>
            <div class="header-right">
                <button class="btn-back" onclick="window.location.href='student-dashboard.php'">
                    ← Back
                </button>
                <div class="user-profile">
                    <div class="user-avatar"><?php echo $display_initials; ?></div>
                    <span class="user-name"><?php echo htmlspecialchars($student_name); ?></span>
                </div>
            </div>
        </header>

        <main class="dashboard-main">
            <div class="concerns-container">
                <h1 class="page-title">📋 My Submitted Concerns</h1>
                
                <div class="stats-bar">
                    <div class="stat-item"><div class="number" id="totalCount">0</div><div class="label">Total</div></div>
                    <div class="stat-item pending"><div class="number" id="pendingCount">0</div><div class="label">Pending</div></div>
                    <div class="stat-item in-progress"><div class="number" id="progressCount">0</div><div class="label">In Progress</div></div>
                    <div class="stat-item resolved"><div class="number" id="resolvedCount">0</div><div class="label">Resolved</div></div>
                </div>
                
                <div class="filter-bar">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="Pending">🟡 Pending</button>
                    <button class="filter-btn" data-filter="In Progress">🔵 In Progress</button>
                    <button class="filter-btn" data-filter="Resolved">🟢 Resolved</button>
                </div>
                
                <div id="concernsList"><div class="empty-state"><p>Loading your concerns...</p></div></div>
            </div>
        </main>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>🗑️ Delete Concern?</h3>
            <p>Are you sure you want to delete this concern? This action cannot be undone.</p>
            <input type="hidden" id="deleteTrackingId">
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="btn-confirm-delete" onclick="confirmDelete()">Yes, Delete</button>
            </div>
        </div>
    </div>

    <script>
    var allConcerns = [];
    var currentFilter = 'all';

    document.addEventListener('DOMContentLoaded', function() {
        loadMyConcerns();
        
        document.querySelectorAll('.filter-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                currentFilter = this.getAttribute('data-filter');
                renderConcerns();
            });
        });
    });

    function loadMyConcerns() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'api/get-my-concerns.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        allConcerns = data.data;
                        updateStats();
                        renderConcerns();
                    } else {
                        document.getElementById('concernsList').innerHTML = '<div class="empty-state"><p>Error: ' + data.message + '</p></div>';
                    }
                } catch(e) {
                    document.getElementById('concernsList').innerHTML = '<div class="empty-state"><p>Error loading concerns</p></div>';
                }
            }
        };
        xhr.send();
    }

    function updateStats() {
        document.getElementById('totalCount').textContent = allConcerns.length;
        document.getElementById('pendingCount').textContent = allConcerns.filter(function(c) { return c.status === 'Pending'; }).length;
        document.getElementById('progressCount').textContent = allConcerns.filter(function(c) { return c.status === 'In Progress'; }).length;
        document.getElementById('resolvedCount').textContent = allConcerns.filter(function(c) { return c.status === 'Resolved'; }).length;
    }

    function renderConcerns() {
        var concerns = currentFilter === 'all' ? allConcerns : allConcerns.filter(function(c) { return c.status === currentFilter; });
        var container = document.getElementById('concernsList');
        
        if (concerns.length === 0) {
            container.innerHTML = '<div class="empty-state"><h3>📭 No Concerns Found</h3><p>No concerns match this filter.</p></div>';
            return;
        }
        
        var html = '';
        concerns.forEach(function(concern) {
            var statusClass = concern.status.toLowerCase().replace(/\s+/g, '-');
            html += '<div class="concern-card status-' + statusClass + '">';
            html += '<div class="concern-header"><h3 class="concern-title">' + escapeHtml(concern.subject) + '</h3><span class="concern-id">' + concern.tracking_id + '</span></div>';
            html += '<div class="concern-meta"><span>📁 ' + concern.category + '</span><span>📅 ' + concern.created_at_formatted + '</span>';
            if (concern.is_anonymous == 1) html += '<span>🔒 Anonymous</span>';
            html += '</div>';
            html += '<div class="concern-description">' + escapeHtml(concern.description).substring(0, 150) + '...</div>';
            html += '<div class="concern-footer"><span class="badge badge-' + statusClass + '">' + concern.status + '</span>';
            html += '<div class="concern-actions"><button class="btn-view" onclick="viewConcern(\'' + concern.tracking_id + '\')">👁️ View</button>';
            html += '<button class="btn-delete" onclick="openDeleteModal(\'' + concern.tracking_id + '\')">🗑️ Delete</button></div></div></div>';
        });
        container.innerHTML = html;
    }

    function escapeHtml(text) { if (!text) return ''; var div = document.createElement('div'); div.textContent = text; return div.innerHTML; }
    function viewConcern(trackingId) { window.location.href = 'concern-details.php?id=' + trackingId; }
    function openDeleteModal(trackingId) { document.getElementById('deleteTrackingId').value = trackingId; document.getElementById('deleteModal').style.display = 'block'; }
    function closeDeleteModal() { document.getElementById('deleteModal').style.display = 'none'; }

    function confirmDelete() {
        var trackingId = document.getElementById('deleteTrackingId').value;
        var formData = new FormData();
        formData.append('tracking_id', trackingId);
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'api/delete-concern.php', true);
        xhr.onload = function() {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    closeDeleteModal();
                    allConcerns = allConcerns.filter(function(c) { return c.tracking_id !== trackingId; });
                    updateStats();
                    renderConcerns();
                    alert('✅ Concern deleted successfully!');
                } else {
                    alert('❌ Error: ' + data.message);
                }
            } catch(e) { alert('❌ Error deleting concern'); }
        };
        xhr.send(formData);
    }

    window.onclick = function(event) { if (event.target == document.getElementById('deleteModal')) closeDeleteModal(); };
    </script>
</body>
</html>