<?php
require_once 'api/config.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Profile Check
$check_sql = "SELECT is_profile_completed FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $check_sql);

if ($result) {
    $user_data = mysqli_fetch_assoc($result);
    if ($user_data && $user_data['is_profile_completed'] == 0) {
        header('Location: setup-profile.php');
        exit;
    }
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['name'];

// 3. Initials Logic
$words = explode(" ", $student_name);
$initials = "";
foreach ($words as $w) { if(!empty($w)) $initials .= strtoupper($w[0]); }
$display_initials = substr($initials, 0, 2);

// 4. Stats & Data Queries
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved
    FROM concerns WHERE student_id = '$student_id'";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

$concerns_list = mysqli_query($conn, "SELECT * FROM concerns WHERE student_id = '$student_id' ORDER BY created_at DESC");
$appointments_list = mysqli_query($conn, "SELECT * FROM appointments WHERE student_id = '$student_id' ORDER BY appointment_date ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"> 
    <title>Student Dashboard - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
    /* ==================== MOBILE-FIRST STUDENT DASHBOARD ==================== */
    
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
    
    .nav-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .nav-right a {
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        padding: 8px 14px;
        background: rgba(255,255,255,0.15);
        border-radius: 20px;
        transition: background 0.2s;
    }
    
    .nav-right a:hover {
        background: rgba(255,255,255,0.25);
    }

    /* Dashboard Wrapper */
    .dashboard-wrapper { 
        padding-top: 56px; 
        width: 100%;
        min-height: 100vh;
    }
    
    /* Dashboard Header */
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
        font-size: 14px;
        font-weight: 600;
        color: #333;
        letter-spacing: -0.2px;
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
        padding: 16px;
        max-width: 100%;
    }

    /* Stats Container - Mobile Grid */
    .stats-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 24px;
    }

    .stat-card { 
        width: 100%; 
        margin: 0;
        padding: 22px 16px;
        border-radius: 14px;
        text-align: center;
        border: 1px solid #e8ecf0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 6px;
    }
    
    .stat-label {
        font-size: 12px;
        margin-top: 4px;
        color: #777;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }

    /* Action Buttons - Vertical Stack on Mobile */
    .action-bar {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 0;
        margin-bottom: 28px;
    }

    .btn-submit {
        width: 100%;
        padding: 14px 20px;
        font-size: 14px;
        font-weight: 600;
        justify-content: center;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    /* Primary Action (FAQ) - Highlighted */
    .btn-submit.btn-primary-action {
        background: linear-gradient(135deg, #4a7c2c 0%, #3d6824 100%);
        box-shadow: 0 4px 12px rgba(74, 124, 44, 0.3);
    }
    
    /* Secondary Actions */
    .btn-submit.btn-secondary-action {
        background: white;
        color: #4a7c2c;
        border: 2px solid #4a7c2c;
    }
    
    .btn-submit.btn-secondary-action:hover {
        background: #f0f7ec;
    }

    /* Section Headers */
    .section-header {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 28px 0 14px 0;
        padding: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid #eef0f3;
    }

    /* Mobile-Friendly Cards Instead of Tables */
    .mobile-card-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .mobile-card {
        background: white;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        border: 1px solid #e8ecf0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: box-shadow 0.2s;
        cursor: pointer;
    }

    .mobile-card:hover {
        box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    }
    
    .mobile-card-info {
        flex: 1;
        min-width: 0;
    }
    
    .mobile-card-title {
        font-weight: 600;
        color: #333;
        font-size: 14px;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .mobile-card-subtitle {
        font-size: 12px;
        color: #888;
    }
    
    .mobile-card-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        flex-shrink: 0;
        margin-left: 12px;
    }
    
    /* Badge Colors */
    .badge-pending {
        background: #fff3e0;
        color: #f57c00;
    }
    
    .badge-resolved {
        background: #e8f5e9;
        color: #388e3c;
    }
    
    .badge-in-progress {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .badge-confirmed {
        background: #e8f5e9;
        color: #388e3c;
    }
    
    .badge-cancelled {
        background: #ffebee;
        color: #c62828;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #999;
        background: white;
        border-radius: 14px;
        border: 1px solid #e8ecf0;
    }
    
    .empty-state-icon {
        font-size: 40px;
        margin-bottom: 10px;
        opacity: 0.4;
    }
    
    .empty-state p {
        font-size: 14px;
        color: #888;
    }

    /* Hide Table on Mobile, Show Cards */
    .mobile-table-wrapper {
        display: none;
    }
    
    .mobile-card-list {
        display: flex;
    }

    /* Table row inline style replacements */
    .clickable-row {
        cursor: pointer;
        transition: background 0.15s;
    }

    .clickable-row:hover {
        background: #fafcfd;
    }

    .tracking-id {
        color: #4a7c2c;
        font-weight: 600;
    }

    .no-data-cell {
        text-align: center;
        padding: 40px 20px !important;
        color: #999;
        font-style: italic;
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
            padding: 14px 40px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .header-logo {
            width: 40px;
            height: 40px;
        }
        
        .header-title {
            font-size: 17px;
            font-weight: 600;
            letter-spacing: -0.2px;
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
        
        .dashboard-main {
            padding: 32px 40px;
            max-width: 1100px;
            margin: 0 auto;
        }
        
        .stats-container { 
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            padding: 28px 24px;
        }
        
        .stat-number {
            font-size: 36px;
        }
        
        .stat-label {
            font-size: 13px;
        }
        
        .action-bar { 
            flex-direction: row; 
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 36px;
        }
        
        .btn-submit { 
            width: auto;
            flex: 1;
            min-width: 180px;
            padding: 14px 24px;
            font-size: 14px;
        }
        
        .section-header {
            font-size: 20px;
            margin: 36px 0 16px 0;
            padding-bottom: 10px;
        }
        
        /* Show Table on Desktop, Hide Cards */
        .mobile-table-wrapper {
            display: block;
            width: 100%;
            overflow-x: auto;
            background: white;
            border-radius: 14px;
            margin-bottom: 24px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            border: 1px solid #e8ecf0;
        }
        
        .mobile-table-wrapper table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .mobile-table-wrapper th {
            background: #f8fafb;
            padding: 14px 20px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid #e8ecf0;
        }
        
        .mobile-table-wrapper td {
            padding: 16px 20px;
            border-top: 1px solid #f2f4f6;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            vertical-align: middle;
        }

        .mobile-table-wrapper tbody tr:hover {
            background: #fafcfd;
        }
        
        .mobile-card-list {
            display: none;
        }
    }
    
    /* Large Desktop */
    @media screen and (min-width: 1024px) {
        .stats-container {
            grid-template-columns: repeat(4, 1fr);
        }

        .dashboard-main {
            padding: 32px 40px;
            max-width: 1200px;
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
                <?php include 'includes/notification-bell.php'; ?>
                <a href="api/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <header class="dashboard-header">
            <div class="header-left">
                <img src="images/phinma-logo.png" alt="Logo" class="header-logo">
                <span class="header-title">Student Portal</span>
            </div>
            <div class="header-right">
                <div class="user-profile">
                    <div class="user-avatar"><?php echo $display_initials; ?></div>
                    <span class="user-name"><?php echo $student_name; ?></span>
                </div>
            </div>
        </header>

        <main class="dashboard-main">
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number green"><?php echo $stats['total'] ?? 0; ?></div>
                    <div class="stat-label">Total Concerns</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number orange"><?php echo $stats['pending'] ?? 0; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-bar">
                <button class="btn-submit btn-primary-action" onclick="window.location.href='faq.php'">
                    ❓ Frequently Asked Questions
                </button>
                <button class="btn-submit" onclick="window.location.href='submit-concern-form.php'">
                    ➕ Submit New Concern
                </button>
                <button class="btn-submit" onclick="window.location.href='my-concerns.php'">
                    📋 My Concerns
                </button>
                <button class="btn-submit" onclick="window.location.href='book-appointment.php'">
                    📅 Book Appointment
                </button>
                <button class="btn-submit" onclick="window.location.href='existing-concerns.php'">
                     Existing Concerns
                </button>
            </div>

            <!-- Appointments Section -->
            <h2 class="section-header">📅 My Appointments</h2>
            
            <!-- Mobile Cards (shown on mobile) -->
            <div class="mobile-card-list">
                <?php 
                mysqli_data_seek($appointments_list, 0);
                if(mysqli_num_rows($appointments_list) > 0): 
                    while($apt = mysqli_fetch_assoc($appointments_list)): 
                        $status_class = strtolower(str_replace(' ', '-', $apt['status']));
                ?>
                <div class="mobile-card">
                    <div class="mobile-card-info">
                        <div class="mobile-card-title"><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></div>
                        <div class="mobile-card-subtitle"><?php echo $apt['appointment_time']; ?></div>
                    </div>
                    <span class="mobile-card-badge badge-<?php echo $status_class; ?>"><?php echo $apt['status']; ?></span>
                </div>
                <?php endwhile; else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📅</div>
                    <p>No appointments scheduled</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Desktop Table (hidden on mobile) -->
            <div class="mobile-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($appointments_list, 0);
                        if(mysqli_num_rows($appointments_list) > 0): 
                            while($apt = mysqli_fetch_assoc($appointments_list)): 
                        ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></td>
                            <td><?php echo $apt['appointment_time']; ?></td>
                            <td><span class="badge badge-<?php echo strtolower($apt['status']); ?>"><?php echo $apt['status']; ?></span></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="3" class="no-data-cell">No appointments scheduled</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Concerns Section -->
            <h2 class="section-header">📝 Recent Concerns</h2>
            
            <!-- Mobile Cards (shown on mobile) -->
            <div class="mobile-card-list">
                <?php 
                mysqli_data_seek($concerns_list, 0);
                if(mysqli_num_rows($concerns_list) > 0): 
                    while($row = mysqli_fetch_assoc($concerns_list)): 
                        $status_class = strtolower(str_replace(' ', '-', $row['status']));
                ?>
                <div class="mobile-card" onclick="window.location.href='concern-details.php?id=<?php echo $row['tracking_id']; ?>'">
                    <div class="mobile-card-info">
                        <div class="mobile-card-title"><?php echo htmlspecialchars($row['subject']); ?></div>
                        <div class="mobile-card-subtitle">#<?php echo substr($row['tracking_id'], -6); ?> • <?php echo date('M d', strtotime($row['created_at'])); ?></div>
                    </div>
                    <span class="mobile-card-badge badge-<?php echo $status_class; ?>"><?php echo $row['status']; ?></span>
                </div>
                <?php endwhile; else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <p>No concerns submitted yet</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Desktop Table (hidden on mobile) -->
            <div class="mobile-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($concerns_list, 0);
                        if(mysqli_num_rows($concerns_list) > 0): 
                            while($row = mysqli_fetch_assoc($concerns_list)): 
                        ?>
                        <tr onclick="window.location.href='concern-details.php?id=<?php echo $row['tracking_id']; ?>'" class="clickable-row">
                            <td><span class="tracking-id">#<?php echo substr($row['tracking_id'], -6); ?></span></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td><span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>"><?php echo $row['status']; ?></span></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" class="no-data-cell">No concerns submitted yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>