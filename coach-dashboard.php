    <?php
require_once 'api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header('Location: index.php');
    exit;
}

$coach_name = $_SESSION['name'];

$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'student'"))['count'];
$total_concerns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM concerns"))['count'];
$pending_concerns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM concerns WHERE status = 'Pending'"))['count'];
$resolved_concerns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM concerns WHERE status = 'Resolved'"))['count'];

$query_risk = "SELECT u.name, c.student_id, COUNT(c.id) as pending_count, MAX(c.created_at) as last_date 
                FROM concerns c 
                LEFT JOIN users u ON c.student_id = u.student_id 
                WHERE c.status = 'Pending' 
                GROUP BY c.student_id 
                ORDER BY last_date DESC";
$risk_result = mysqli_query($conn, $query_risk);

$apt_query = "SELECT a.*, u.name as student_name 
              FROM appointments a 
              JOIN users u ON a.student_id = u.student_id
              WHERE a.status IN ('Scheduled', 'Confirmed', 'Reschedule Requested') 
              ORDER BY a.appointment_date ASC";
$appointments_result = mysqli_query($conn, $apt_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .modal { display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 10% auto; padding: 25px; border-radius: 12px; width: 450px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-scheduled { background: #e3f2fd; color: #1565c0; }
        .status-confirmed { background: #e8f5e9; color: #2e7d32; }
        .status-reschedule { background: #fff3e0; color: #ef6c00; }
        .btn-confirm { background: #2e7d32; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .btn-resched { background: #ef6c00; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        
        /* Notification Styles */
        .notif-wrapper {
            position: relative;
            display: inline-block;
        }
        .notif-bell {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            padding: 5px 10px;
            position: relative;
        }
        .notif-badge {
            position: absolute;
            top: -2px;
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
        }
        .notif-badge.hidden { display: none; }
        .notif-dropdown {
            display: none;
            position: absolute;
            top: 40px;
            right: 0;
            width: 340px;
            max-height: 450px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            z-index: 9999;
            overflow: hidden;
        }
        .notif-dropdown.show { display: block; }
        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }
        .notif-header h4 { margin: 0; font-size: 14px; color: #333; }
        .mark-read-btn {
            background: none;
            border: none;
            color: #4a7c2c;
            font-size: 12px;
            cursor: pointer;
        }
        .notif-list {
            max-height: 320px;
            overflow-y: auto;
        }
        .notif-item {
            display: flex;
            gap: 10px;
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }
        .notif-item:hover { background: #f5f5f5; }
        .notif-item.unread { background: #f0f7ec; }
        .notif-item.unread:hover { background: #e5f2de; }
        .notif-empty {
            padding: 30px;
            text-align: center;
            color: #888;
        }
        .notif-footer {
            display: block;
            text-align: center;
            padding: 12px;
            background: #f8f9fa;
            color: #4a7c2c;
            font-weight: 600;
            text-decoration: none;
            border-top: 1px solid #eee;
            font-size: 13px;
            transition: background 0.2s;
        }
        .notif-footer:hover {
            background: #e8f5e9;
        }
        /* Profile Dropdown */
        .user-profile-wrapper {
            position: relative;
        }
        .user-profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 8px;
            border: none;
            background: none;
            transition: background 0.2s;
        }
        .user-profile-btn:hover {
            background: rgba(0,0,0,0.04);
        }
        .profile-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 200px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            z-index: 9999;
            overflow: hidden;
            animation: dropdownFade 0.2s ease;
        }
        @keyframes dropdownFade {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .profile-dropdown.show {
            display: block;
        }
        .profile-dropdown-header {
            padding: 14px 16px;
            border-bottom: 1px solid #f0f0f0;
            background: #f8faf8;
        }
        .profile-dropdown-header .dropdown-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .profile-dropdown-header .dropdown-role {
            font-size: 12px;
            color: #888;
            margin-top: 2px;
        }
        .profile-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: #333;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.15s;
            border: none;
            background: none;
            width: 100%;
            cursor: pointer;
            text-align: left;
        }
        .profile-dropdown-item:hover {
            background: #f0f7ec;
        }
        .profile-dropdown-item.logout {
            color: #dc2626;
            border-top: 1px solid #f0f0f0;
        }
        .profile-dropdown-item.logout:hover {
            background: #fef2f2;
        }
        .dropdown-caret {
            font-size: 10px;
            color: #888;
            margin-left: -4px;
            transition: transform 0.2s;
        }
        .user-profile-wrapper.open .dropdown-caret {
            transform: rotate(180deg);
        }

        /* ===== COACH DASHBOARD LAYOUT IMPROVEMENTS ===== */

        /* Page title refinement */
        .page-title {
            font-size: 26px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 28px;
            letter-spacing: -0.3px;
        }

        /* Stats cards - tighter grid */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 36px;
        }

        .stat-card {
            padding: 28px 20px;
            border-radius: 14px;
            border: 1px solid #e8ecf0;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            font-size: 28px;
            margin-bottom: 12px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 6px;
            line-height: 1;
        }

        .stat-label {
            color: #777;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Quick action buttons grid */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            max-width: 780px;
            margin-bottom: 36px;
        }

        .quick-actions-grid .btn-submit {
            justify-content: center;
            padding: 14px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 10px;
            white-space: nowrap;
        }

        /* Analytics sections spacing */
        .analytics-section {
            margin-top: 0;
            margin-bottom: 36px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eef0f3;
        }

        /* Table improvements */
        .risk-students-table {
            border-radius: 14px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06);
            border: 1px solid #e8ecf0;
        }

        .risk-students-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .risk-students-table thead {
            background: #f8fafb;
        }

        .risk-students-table th {
            padding: 14px 20px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid #e8ecf0;
        }

        .risk-students-table td {
            padding: 16px 20px;
            border-top: 1px solid #f2f4f6;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            vertical-align: middle;
        }

        .risk-students-table tbody tr:hover {
            background: #fafcfd;
        }

        /* Appointment table specifics */
        .apt-date {
            color: #666;
            font-size: 13px;
        }

        .apt-time {
            margin-left: 8px;
            color: #4a7c2c;
            font-weight: 600;
            font-size: 13px;
        }

        .apt-actions {
            display: flex;
            gap: 8px;
            flex-wrap: nowrap;
        }

        .apt-actions button {
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 6px;
            white-space: nowrap;
        }

        .btn-done {
            border: 1px solid #4a7c2c;
            background: white;
            color: #4a7c2c;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-done:hover {
            background: #4a7c2c;
            color: white;
        }

        .no-data-row {
            text-align: center;
            padding: 40px 20px !important;
            color: #999;
            font-style: italic;
        }

        /* Modal refinement */
        .modal-content {
            border-radius: 14px;
            padding: 28px;
            width: 460px;
            max-width: 92vw;
        }

        .modal-content h3 {
            margin-bottom: 6px;
        }

        .modal-hint {
            font-size: 14px;
            color: #666;
            margin-bottom: 16px;
        }

        .modal-textarea {
            width: 100%;
            height: 120px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }

        .modal-textarea:focus {
            outline: none;
            border-color: #4a7c2c;
            box-shadow: 0 0 0 3px rgba(74, 124, 44, 0.1);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-cancel {
            background: none;
            border: 1px solid #ddd;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            color: #555;
            font-size: 14px;
        }

        .modal-cancel:hover {
            background: #f5f5f5;
        }

        .modal-submit {
            background: #ef6c00;
            color: white;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }

        .modal-submit:hover {
            opacity: 0.9;
        }

        /* Dashboard main spacing override */
        .dashboard-main {
            padding: 32px 40px;
            max-width: 1200px;
        }

        /* Dashboard header refinement */
        .dashboard-header {
            padding: 14px 40px;
            border-bottom: 1px solid #e5e7eb;
        }

        .header-title {
            font-size: 17px;
            font-weight: 600;
            letter-spacing: -0.2px;
        }

        /* Logout link in nav */
        .nav-logout-link {
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }

        .nav-logout-link:hover {
            text-decoration: underline;
        }

        /* Status badge refinement */
        .status-badge {
            display: inline-block;
            min-width: 90px;
            text-align: center;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ===== RESPONSIVE - COACH DASHBOARD ===== */
        @media (max-width: 1024px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            .quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
                max-width: 100%;
            }
            .dashboard-main {
                padding: 24px 24px;
            }
            .dashboard-header {
                padding: 14px 24px;
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            .stat-card {
                padding: 20px 14px;
            }
            .stat-number {
                font-size: 28px;
            }
            .stat-label {
                font-size: 11px;
            }
            .quick-actions-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
            .quick-actions-grid .btn-submit {
                padding: 12px 14px;
                font-size: 13px;
            }
            .dashboard-main {
                padding: 18px 14px;
            }
            .dashboard-header {
                padding: 12px 14px;
            }
            .page-title {
                font-size: 22px;
                margin-bottom: 20px;
            }
            .section-title {
                font-size: 17px;
            }
            .risk-students-table {
                overflow-x: auto;
            }
            .risk-students-table th,
            .risk-students-table td {
                padding: 12px 14px;
                font-size: 13px;
            }
            .modal-content {
                margin: 5% auto;
                width: 92vw;
            }
        }

        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            .quick-actions-grid {
                grid-template-columns: 1fr;
            }
            .apt-actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-content">
            <div class="nav-left">
                <span class="hamburger">☰</span>
                <span class="nav-title">Kasama Support Hub</span>
            </div>
            <div class="nav-right">
                <a href="api/logout.php" class="nav-logout-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <header class="dashboard-header">
            <div class="header-left">
                <img src="images/phinma-logo.png" alt="Logo" class="header-logo">
                <span class="header-title">Coach Dashboard</span>
            </div>
            <div class="header-right">
                <!-- NOTIFICATION BELL -->
                <div class="notif-wrapper">
                    <button class="notif-bell" id="notifBell" type="button">
                        🔔
                        <span class="notif-badge hidden" id="notifBadge">0</span>
                    </button>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <h4>🔔 Notifications</h4>
                            <button class="mark-read-btn" id="markAllBtn" type="button">Mark all read</button>
                        </div>
                        <div class="notif-list" id="notifList">
                            <div class="notif-empty">Loading...</div>
                        </div>
                        <!-- VIEW ALL LINK -->
                        <a href="coach-notifications.php" class="notif-footer">
                            View All Notifications →
                        </a>
                    </div>
                </div>
                <!-- END NOTIFICATION BELL -->
                
                <div class="user-profile-wrapper" id="profileWrapper">
                    <?php include 'includes/profile-dropdown.php'; ?>
                </div>
            </div>
        </header>

        <main class="dashboard-main">
            <h1 class="page-title">Dashboard Overview</h1>

            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number green"><?php echo $total_students; ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-number blue"><?php echo $total_concerns; ?></div>
                    <div class="stat-label">Total Concerns</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-number orange"><?php echo $pending_concerns; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number green"><?php echo $resolved_concerns; ?></div>
                    <div class="stat-label">Resolved</div>
                </div>
            </div>

            <div class="quick-actions-grid">
                <button class="btn-submit" onclick="window.location.href='coach-appointments.php'">Manage Appointments</button>
                <button class="btn-submit" onclick="window.location.href='coach-faq-manager.php'">Manage FAQ Hub</button>
                <button class="btn-submit" onclick="window.location.href='concerns-table.php'">View All Concerns</button>
                <button class="btn-submit" onclick="window.location.href='analytics.php'">View Detailed Analytics</button>
                <button class="btn-submit" onclick="window.location.href='coach-notifications.php'">All Notifications</button>
                <button class="btn-submit" onclick="window.location.href='coach-calendar.php'">Manage Calendar</button>
            </div>

            <div class="analytics-section">
                <h2 class="section-title">📅 Upcoming Appointments</h2>
                <div class="risk-students-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($appointments_result && mysqli_num_rows($appointments_result) > 0): ?>
                                <?php while ($apt = mysqli_fetch_assoc($appointments_result)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($apt['student_name']); ?></strong></td>
                                    <td>
                                        <span class="apt-date"><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></span>
                                        <strong class="apt-time">| <?php echo $apt['appointment_time']; ?></strong>
                                    </td>
                                    <td>
                                        <?php
        $status_class = ($apt['status'] == 'Confirmed') ? 'status-confirmed' : (($apt['status'] == 'Reschedule Requested') ? 'status-reschedule' : 'status-scheduled');
?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo $apt['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="apt-actions">
                                            <?php if ($apt['status'] !== 'Confirmed'): ?>
                                                <button class="btn-confirm" onclick="updateAptStatus(<?php echo $apt['id']; ?>, 'Confirmed')">Confirm</button>
                                            <?php
        endif; ?>
                                            <button class="btn-resched" onclick="openReschedModal(<?php echo $apt['id']; ?>)">Reschedule</button>
                                            <button class="btn-done" onclick="updateAptStatus(<?php echo $apt['id']; ?>, 'Completed')">Done</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
    endwhile; ?>
                            <?php
else: ?>
                                <tr><td colspan="4" class="no-data-row">No scheduled appointments found.</td></tr>
                            <?php
endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="analytics-section">
                <h2 class="section-title">⚠️ Students Needing Attention</h2>
                <div class="risk-students-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Pending Concerns</th>
                                <th>Last Submission</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($risk_result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($risk_result)): ?>
                                <tr>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['student_id']; ?></td>
                                    <td><?php echo $row['pending_count']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['last_date'])); ?></td>
                                    <td><button class="btn-action" onclick="window.location.href='concerns-table.php'">Review</button></td>
                                </tr>
                                <?php
    endwhile; ?>
                            <?php
else: ?>
                                <tr><td colspan="5" class="no-data-row">No students requiring attention.</td></tr>
                            <?php
endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="reschedModal" class="modal">
        <div class="modal-content">
            <h3>💬 Reschedule Request</h3>
            <p class="modal-hint">Send a message to the student regarding the schedule change.</p>
            <input type="hidden" id="modal_apt_id">
            <textarea id="modal_message" class="modal-textarea" placeholder="Hi! I cannot make it today, can we move it to..."></textarea>
            <div class="modal-footer">
                <button class="modal-cancel" onclick="closeModal()">Cancel</button>
                <button class="modal-submit" onclick="submitReschedule()">Send & Request Reschedule</button>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // NOTIFICATION SYSTEM
        // ============================================
        (function() {
            const bell = document.getElementById('notifBell');
            const dropdown = document.getElementById('notifDropdown');
            const badge = document.getElementById('notifBadge');
            const list = document.getElementById('notifList');
            const markAllBtn = document.getElementById('markAllBtn');
            
            let isOpen = false;
            
            // Toggle dropdown
            bell.addEventListener('click', function(e) {
                e.stopPropagation();
                isOpen = !isOpen;
                
                if (isOpen) {
                    dropdown.classList.add('show');
                    loadNotifications();
                } else {
                    dropdown.classList.remove('show');
                }
            });
            
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.notif-wrapper')) {
                    dropdown.classList.remove('show');
                    isOpen = false;
                }
            });
            
            // Mark all read
            markAllBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                markAllAsRead();
            });
            
            // Load notifications
            async function loadNotifications() {
                list.innerHTML = '<div class="notif-empty">Loading...</div>';
                
                try {
                    const response = await fetch('api/get-notifications.php?limit=10', {
                        method: 'GET',
                        credentials: 'same-origin'
                    });
                    
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        renderNotifications(data.data);
                        updateBadge(data.unread_count);
                    } else {
                        list.innerHTML = '<div class="notif-empty">Error: ' + (data.message || 'Unknown error') + '</div>';
                    }
                } catch (err) {
                    console.error('Fetch error:', err);
                    list.innerHTML = '<div class="notif-empty">Failed to load notifications</div>';
                }
            }
            
            // Render notifications
            function renderNotifications(notifications) {
                if (!notifications || notifications.length === 0) {
                    list.innerHTML = '<div class="notif-empty">🔔 No notifications yet</div>';
                    return;
                }
                
                let html = '';
                notifications.forEach(function(n) {
                    const unreadClass = n.is_read == 0 ? 'unread' : '';
                    const safeUrl = n.url ? n.url.replace(/'/g, "\\'") : '';
                    
                    html += '<div class="notif-item ' + unreadClass + '" onclick="openNotification(' + n.id + ', \'' + safeUrl + '\', ' + n.is_read + ')">';
                    html += '<span style="font-size: 20px;">' + n.icon + '</span>';
                    html += '<div style="flex: 1; min-width: 0;">';
                    html += '<div style="font-weight: 600; font-size: 13px; color: #333;">' + escapeHtml(n.title) + '</div>';
                    html += '<div style="font-size: 12px; color: #666; margin-top: 2px;">' + escapeHtml(n.message) + '</div>';
                    html += '<div style="font-size: 11px; color: #999; margin-top: 4px;">' + n.time_ago + '</div>';
                    html += '</div></div>';
                });
                
                list.innerHTML = html;
            }
            
            // Update badge
            function updateBadge(count) {
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
            
            // Mark all as read
            async function markAllAsRead() {
                try {
                    const formData = new FormData();
                    formData.append('mark_all', 'true');
                    
                    await fetch('api/mark-notification-read.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });
                    
                    loadNotifications();
                    updateBadge(0);
                } catch (err) {
                    console.error('Error:', err);
                }
            }
            
            // Escape HTML
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            // Initial badge count
            async function fetchBadgeCount() {
                try {
                    const response = await fetch('api/get-notifications.php?limit=1', {
                        credentials: 'same-origin'
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        updateBadge(data.unread_count);
                    }
                } catch (err) {
                    console.error('Badge fetch error:', err);
                }
            }
            
            // Load badge on page load
            fetchBadgeCount();
            
            // Refresh every 60 seconds
            setInterval(fetchBadgeCount, 60000);
        })();
        
        // Global function for notification click
        async function openNotification(id, url, isRead) {
            if (isRead == 0) {
                const formData = new FormData();
                formData.append('notification_id', id);
                await fetch('api/mark-notification-read.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
            }
            
            if (url) {
                window.location.href = url;
            }
        }

        // ============================================
        // APPOINTMENT FUNCTIONS
        // ============================================
        function openReschedModal(id) {
            document.getElementById('modal_apt_id').value = id;
            document.getElementById('reschedModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('reschedModal').style.display = 'none';
        }

        async function updateAptStatus(id, status) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', status);
            
            try {
                const response = await fetch('api/update-appointment-status.php', { method: 'POST', body: formData });
                const res = await response.json();
                if(res.success) { 
                    alert('Status updated to ' + status); 
                    location.reload(); 
                } else {
                    alert('Error: ' + res.message);
                }
            } catch (e) { alert('System error occurred.'); }
        }

        async function submitReschedule() {
            const id = document.getElementById('modal_apt_id').value;
            const msg = document.getElementById('modal_message').value;
            
            if(!msg) return alert('Please provide a message for the student.');

            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', 'Reschedule Requested');
            formData.append('message', msg);
            
            try {
                const response = await fetch('api/update-appointment-status.php', { method: 'POST', body: formData });
                const res = await response.json();
                if(res.success) { 
                    alert('Reschedule request sent!'); 
                    location.reload(); 
                } else {
                    alert('Error: ' + res.message);
                }
            } catch (e) { alert('System error occurred.'); }
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('reschedModal')) closeModal();
        }

        // ============================================
        // PROFILE DROPDOWN
        // ============================================
        (function() {
            const wrapper = document.getElementById('profileWrapper');
            const toggle = document.getElementById('profileToggle');
            const dropdown = document.getElementById('profileDropdown');

            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                wrapper.classList.toggle('open');
                dropdown.classList.toggle('show');
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('#profileWrapper')) {
                    wrapper.classList.remove('open');
                    dropdown.classList.remove('show');
                }
            });
        })();
    </script>
</body>
</html>