<?php
require_once 'api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header('Location: index.php');
    exit;
}

$coach_name = $_SESSION['name'];

// Get all appointments
$apt_query = "SELECT a.*, u.name as student_name, u.email as student_email 
              FROM appointments a 
              JOIN users u ON a.student_id = u.student_id
              ORDER BY 
                CASE 
                    WHEN a.status = 'Scheduled' THEN 1
                    WHEN a.status = 'Reschedule Requested' THEN 2
                    WHEN a.status = 'Confirmed' THEN 3
                    WHEN a.status = 'Completed' THEN 4
                    ELSE 5
                END,
                a.appointment_date ASC";
$appointments_result = mysqli_query($conn, $apt_query);

// Stats
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments"))['count'];
$scheduled = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments WHERE status = 'Scheduled'"))['count'];
$confirmed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments WHERE status = 'Confirmed'"))['count'];
$completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments WHERE status = 'Completed'"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .appointments-container { max-width: 1400px; margin: 0 auto; }
        
        /* Stats Cards */
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: white; padding: 20px 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center; }
        .stat-box .number { font-size: 32px; font-weight: 700; }
        .stat-box .label { font-size: 13px; color: #666; margin-top: 5px; }
        .stat-box.orange .number { color: #ef6c00; }
        .stat-box.blue .number { color: #1565c0; }
        .stat-box.green .number { color: #2e7d32; }
        .stat-box.gray .number { color: #666; }
        
        /* Filter Tabs */
        .filter-tabs { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-tab { padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 25px; cursor: pointer; font-size: 14px; transition: all 0.2s; }
        .filter-tab:hover { border-color: #4a7c2c; }
        .filter-tab.active { background: #4a7c2c; color: white; border-color: #4a7c2c; }
        
        /* Appointments Table */
        .appointments-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; }
        .appointments-table { width: 100%; border-collapse: collapse; }
        .appointments-table th { background: #f8f9fa; padding: 15px; text-align: left; font-size: 12px; text-transform: uppercase; color: #666; border-bottom: 2px solid #eee; }
        .appointments-table td { padding: 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        .appointments-table tr:hover { background: #fafafa; }
        
        /* Status Badges */
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .status-scheduled { background: #fff3e0; color: #ef6c00; }
        .status-confirmed { background: #e8f5e9; color: #2e7d32; }
        .status-completed { background: #e3f2fd; color: #1565c0; }
        .status-reschedule { background: #fce4ec; color: #c2185b; }
        .status-cancelled { background: #ffebee; color: #c62828; }
        
        /* Action Buttons */
        .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; margin-right: 5px; margin-bottom: 5px; }
        .btn-confirm { background: #2e7d32; color: white; }
        .btn-confirm:hover { background: #1b5e20; }
        .btn-resched { background: #ef6c00; color: white; }
        .btn-resched:hover { background: #e65100; }
        .btn-complete { background: #1565c0; color: white; }
        .btn-complete:hover { background: #0d47a1; }
        .btn-cancel { background: #c62828; color: white; }
        .btn-cancel:hover { background: #b71c1c; }
        .btn-view { background: white; color: #333; border: 1px solid #ddd; }
        .btn-view:hover { background: #f5f5f5; }
        
        /* Student Info */
        .student-info { display: flex; align-items: center; gap: 12px; }
        .student-avatar { width: 40px; height: 40px; border-radius: 50%; background: #4a7c2c; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; }
        .student-name { font-weight: 600; color: #333; }
        .student-email { font-size: 12px; color: #888; }
        
        /* Date/Time */
        .datetime { white-space: nowrap; }
        .datetime .date { font-weight: 600; color: #333; }
        .datetime .time { color: #4a7c2c; font-weight: 500; }
        
        /* Modal */
        .modal { display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 8% auto; padding: 30px; border-radius: 12px; width: 500px; max-width: 90%; box-shadow: 0 5px 30px rgba(0,0,0,0.3); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h3 { margin: 0; }
        .modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #999; }
        .modal-close:hover { color: #333; }
        
        /* Reason Box */
        .reason-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #4a7c2c; }
        .reason-label { font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .reason-text { color: #333; line-height: 1.6; }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 60px 20px; color: #888; }
        .empty-state .icon { font-size: 64px; margin-bottom: 15px; }
        
        /* Back Button */
        .btn-back { background: white; border: 1px solid #ddd; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-back:hover { background: #f5f5f5; }

        @media (max-width: 900px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .appointments-table { font-size: 13px; }
            .appointments-table th, .appointments-table td { padding: 10px; }
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
                <span class="header-title">Manage Appointments</span>
            </div>
            <div class="header-right">
                <button class="btn-back" onclick="window.location.href='coach-dashboard.php'">← Back to Dashboard</button>
                <?php include 'includes/notification-bell.php'; ?>
                <?php include 'includes/profile-dropdown.php'; ?>
            </div>
        </header>

        <main class="dashboard-main">
            <div class="appointments-container">
                <h1 class="page-title">📅 Appointment Management</h1>
                
                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-box orange">
                        <div class="number"><?php echo $scheduled; ?></div>
                        <div class="label">⏳ Pending/Scheduled</div>
                    </div>
                    <div class="stat-box green">
                        <div class="number"><?php echo $confirmed; ?></div>
                        <div class="label">✅ Confirmed</div>
                    </div>
                    <div class="stat-box blue">
                        <div class="number"><?php echo $completed; ?></div>
                        <div class="label">✔️ Completed</div>
                    </div>
                    <div class="stat-box gray">
                        <div class="number"><?php echo $total; ?></div>
                        <div class="label">📊 Total</div>
                    </div>
                </div>
                
                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterAppointments('all')">All</button>
                    <button class="filter-tab" onclick="filterAppointments('Scheduled')">⏳ Scheduled</button>
                    <button class="filter-tab" onclick="filterAppointments('Confirmed')">✅ Confirmed</button>
                    <button class="filter-tab" onclick="filterAppointments('Reschedule Requested')">📅 Reschedule</button>
                    <button class="filter-tab" onclick="filterAppointments('Completed')">✔️ Completed</button>
                </div>
                
                <!-- Appointments Table -->
                <div class="appointments-card">
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Date & Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="appointmentsTable">
                            <?php if ($appointments_result && mysqli_num_rows($appointments_result) > 0): ?>
                                <?php while ($apt = mysqli_fetch_assoc($appointments_result)):
        $initials = strtoupper(substr($apt['student_name'], 0, 2));
        $status_class = strtolower(str_replace(' ', '-', $apt['status']));
        $short_reason = strlen($apt['reason']) > 40 ? substr($apt['reason'], 0, 40) . '...' : $apt['reason'];
?>
                                <tr data-status="<?php echo $apt['status']; ?>">
                                    <td>
                                        <div class="student-info">
                                            <div class="student-avatar"><?php echo $initials; ?></div>
                                            <div>
                                                <div class="student-name"><?php echo htmlspecialchars($apt['student_name']); ?></div>
                                                <div class="student-email"><?php echo htmlspecialchars($apt['student_email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="datetime">
                                        <div class="date"><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></div>
                                        <div class="time"><?php echo $apt['appointment_time']; ?></div>
                                    </td>
                                    <td>
                                        <span title="<?php echo htmlspecialchars($apt['reason']); ?>"><?php echo htmlspecialchars($short_reason); ?></span>
                                        <?php if (strlen($apt['reason']) > 40): ?>
                                            <button class="btn-action btn-view" onclick="viewDetails(<?php echo $apt['id']; ?>, '<?php echo htmlspecialchars(addslashes($apt['student_name'])); ?>', '<?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?>', '<?php echo $apt['appointment_time']; ?>', '<?php echo htmlspecialchars(addslashes($apt['reason'])); ?>', '<?php echo $apt['status']; ?>')">View</button>
                                        <?php
        endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $status_class; ?>"><?php echo $apt['status']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($apt['status'] === 'Scheduled'): ?>
                                            <button class="btn-action btn-confirm" onclick="updateStatus(<?php echo $apt['id']; ?>, 'Confirmed')">✅ Confirm</button>
                                            <button class="btn-action btn-resched" onclick="openReschedModal(<?php echo $apt['id']; ?>)">📅 Reschedule</button>
                                        <?php
        elseif ($apt['status'] === 'Confirmed'): ?>
                                            <button class="btn-action btn-complete" onclick="updateStatus(<?php echo $apt['id']; ?>, 'Completed')">✔️ Complete</button>
                                            <button class="btn-action btn-resched" onclick="openReschedModal(<?php echo $apt['id']; ?>)">📅 Reschedule</button>
                                        <?php
        elseif ($apt['status'] === 'Reschedule Requested'): ?>
                                            <button class="btn-action btn-confirm" onclick="updateStatus(<?php echo $apt['id']; ?>, 'Confirmed')">✅ Confirm New</button>
                                            <button class="btn-action btn-cancel" onclick="updateStatus(<?php echo $apt['id']; ?>, 'Cancelled')">❌ Cancel</button>
                                        <?php
        elseif ($apt['status'] === 'Completed'): ?>
                                            <span style="color: #888; font-size: 12px;">—</span>
                                        <?php
        endif; ?>
                                    </td>
                                </tr>
                                <?php
    endwhile; ?>
                            <?php
else: ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="icon">📅</div>
                                            <p>No appointments found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php
endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- View Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📅 Appointment Details</h3>
                <button class="modal-close" onclick="closeModal('detailsModal')">&times;</button>
            </div>
            <div id="detailsContent"></div>
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div id="reschedModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📅 Request Reschedule</h3>
                <button class="modal-close" onclick="closeModal('reschedModal')">&times;</button>
            </div>
            <p style="color: #666; margin-bottom: 15px;">Send a message to the student explaining why you need to reschedule.</p>
            <input type="hidden" id="resched_apt_id">
            <textarea id="resched_message" style="width:100%; height:120px; padding:12px; border:1px solid #ddd; border-radius:8px; font-family:inherit; font-size:14px;" placeholder="Hi! I need to reschedule our appointment because..."></textarea>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button onclick="closeModal('reschedModal')" style="padding:10px 20px; border:1px solid #ddd; background:white; border-radius:6px; cursor:pointer;">Cancel</button>
                <button onclick="submitReschedule()" style="padding:10px 20px; background:#ef6c00; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600;">📅 Send Request</button>
            </div>
        </div>
    </div>

    <script>
    // Filter appointments
    function filterAppointments(status) {
        document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
        
        document.querySelectorAll('#appointmentsTable tr').forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Update status
    async function updateStatus(id, status) {
        if (!confirm('Update this appointment to "' + status + '"?')) return;
        
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', status);
        
        try {
            const response = await fetch('api/update-appointment-status.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                alert('✅ ' + result.message);
                location.reload();
            } else {
                alert('❌ Error: ' + result.message);
            }
        } catch (e) {
            alert('❌ Connection error');
        }
    }
    
    // View details
    function viewDetails(id, name, date, time, reason, status) {
        const statusClass = status.toLowerCase().replace(' ', '-');
        document.getElementById('detailsContent').innerHTML = `
            <div class="student-info" style="margin-bottom:20px;">
                <div class="student-avatar">${name.substring(0,2).toUpperCase()}</div>
                <div>
                    <div class="student-name">${name}</div>
                    <span class="status-badge status-${statusClass}">${status}</span>
                </div>
            </div>
            <p><strong>📅 Date:</strong> ${date}</p>
            <p><strong>🕐 Time:</strong> ${time}</p>
            <div class="reason-box">
                <div class="reason-label">📝 Reason for Appointment</div>
                <div class="reason-text">${reason}</div>
            </div>
        `;
        document.getElementById('detailsModal').style.display = 'block';
    }
    
    // Reschedule modal
    function openReschedModal(id) {
        document.getElementById('resched_apt_id').value = id;
        document.getElementById('resched_message').value = '';
        document.getElementById('reschedModal').style.display = 'block';
    }
    
    async function submitReschedule() {
        const id = document.getElementById('resched_apt_id').value;
        const message = document.getElementById('resched_message').value.trim();
        
        if (!message) {
            alert('Please write a message to the student.');
            return;
        }
        
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', 'Reschedule Requested');
        formData.append('message', message);
        
        try {
            const response = await fetch('api/update-appointment-status.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                alert('✅ Reschedule request sent!');
                location.reload();
            } else {
                alert('❌ Error: ' + result.message);
            }
        } catch (e) {
            alert('❌ Connection error');
        }
    }
    
    // Close modal
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    // Close modal on outside click
    window.onclick = function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    }
    </script>
</body>
</html>