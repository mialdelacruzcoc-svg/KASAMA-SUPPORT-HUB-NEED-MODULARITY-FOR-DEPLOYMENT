<?php
require_once 'api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header('Location: index.php');
    exit;
}

$coach_name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Calendar - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { overflow-x: hidden; background: #f5f7fa; }
        
        .top-nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; height: 56px; }
        .dashboard-wrapper { padding-top: 56px; }
        
        .calendar-container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        
        .page-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .page-header h1 { font-size: 26px; margin: 0; font-weight: 700; color: #1a2e1a; }
        
        .header-actions { display: flex; gap: 10px; }
        
        .btn-primary {
            background: #4a7c2c !important;
            color: white !important;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.3px;
        }
        
        .btn-secondary {
            background: white !important;
            color: #333 !important;
            border: 1px solid #ddd;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-secondary:hover { background: #f5f5f5 !important; }
        
        /* Calendar Grid */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            background: #dde3dd;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
        }
        
        .calendar-header-cell {
            background: #3a6622;
            color: white !important;
            padding: 16px 8px;
            text-align: center;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .calendar-cell {
            background: white;
            min-height: 150px;
            padding: 14px 12px 36px 12px;
            border: none;
            position: relative;
        }
        
        .calendar-cell.other-month {
            background: #f6f7f9;
            opacity: 0.55;
        }
        
        .calendar-cell.today {
            background: #f0f7ec;
            outline: 2px solid #4a7c2c;
            outline-offset: -2px;
        }
        
        .calendar-cell.weekend {
            background: #fafafa;
        }
        
        .calendar-cell.has-blocked {
            background: #fff8f8;
        }
        
        .day-number {
            font-weight: 700;
            font-size: 15px;
            color: #2c2c2c;
            margin-bottom: 10px;
            line-height: 1;
        }
        
        .day-number.today {
            background: #4a7c2c;
            color: white !important;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        
        /* Time Slots */
        .time-slot-mini {
            font-size: 13px;
            font-weight: 500;
            padding: 5px 9px;
            border-radius: 5px;
            margin-bottom: 5px;
            cursor: pointer;
            transition: all 0.15s;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .time-slot-mini.available {
            background: #d4edda !important;
            color: #1a5c1e !important;
            border-left: 3px solid #4a7c2c;
        }
        
        .time-slot-mini.blocked {
            background: #fde8e8 !important;
            color: #b91c1c !important;
            border-left: 3px solid #ef4444;
            text-decoration: line-through;
        }
        
        .time-slot-mini.booked {
            background: #dbeafe !important;
            color: #1d4ed8 !important;
            border-left: 3px solid #3b82f6;
        }
        
        .time-slot-mini:hover {
            transform: scale(1.02);
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        }
        
        /* Day Actions */
        .day-actions {
            position: absolute;
            bottom: 8px;
            right: 8px;
        }
        
        .btn-block-day {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            background: #fde8e8 !important;
            color: #b91c1c !important;
            border: 1px solid #fca5a5;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-block-day:hover { background: #fee2e2 !important; }
        
        .btn-unblock-day {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            background: #d4edda !important;
            color: #1a5c1e !important;
            border: 1px solid #86efac;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-unblock-day:hover { background: #dcfce7 !important; }
        
        /* Navigation */
        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .month-display {
            font-size: 22px;
            font-weight: 700;
            color: #1a2e1a;
            min-width: 160px;
            text-align: center;
        }
        
        .nav-btn {
            background: white !important;
            color: #333 !important;
            border: 1px solid #ccc;
            padding: 9px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.15s;
            letter-spacing: 0.2px;
        }
        
        .nav-btn:hover { background: #f0f7ec !important; border-color: #4a7c2c; color: #3a6622 !important; }
        
        /* Legend */
        .legend {
            display: flex;
            gap: 20px;
            margin-bottom: 18px;
            flex-wrap: wrap;
            align-items: center;
            padding: 12px 16px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            width: fit-content;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #444;
        }
        
        .legend-dot {
            width: 14px;
            height: 14px;
            border-radius: 3px;
        }
        
        .legend-dot.available { background: #d4edda; border-left: 3px solid #4a7c2c; }
        .legend-dot.blocked { background: #fde8e8; border-left: 3px solid #ef4444; }
        .legend-dot.booked { background: #dbeafe; border-left: 3px solid #3b82f6; }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.45);
        }
        
        .modal-content {
            background: white;
            margin: 8% auto;
            padding: 28px;
            border-radius: 14px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h3 { margin: 0; font-size: 18px; color: #1a2e1a; }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 26px;
            cursor: pointer;
            color: #999;
            line-height: 1;
        }
        .modal-close:hover { color: #555; }
        
        /* Form */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #333; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            color: #333 !important;
            box-sizing: border-box;
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        /* Stats */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
        }
        
        .stat-card .label {
            font-size: 13px;
            color: #666;
            margin-top: 4px;
        }
        
        .stat-card.green .number { color: #4a7c2c; }
        .stat-card.orange .number { color: #ef6c00; }
        .stat-card.blue .number { color: #1565c0; }

        /* Weekend / Past label inside cells */
        .cell-label {
            color: #aaa;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            margin-top: 24px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        /* Mobile */
        @media screen and (max-width: 900px) {
            .calendar-grid {
                grid-template-columns: 1fr;
                background: white;
                gap: 0;
            }
            
            .calendar-header-cell { display: none; }
            
            .calendar-cell {
                min-height: auto;
                padding: 16px 16px 44px 16px;
                border-bottom: 1px solid #eee;
            }

            .calendar-cell.other-month { display: none; }

            .day-number {
                font-size: 17px;
                margin-bottom: 12px;
            }

            .time-slot-mini {
                font-size: 14px;
                padding: 7px 10px;
                margin-bottom: 6px;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
            }

            .calendar-nav {
                gap: 8px;
            }

            .month-display {
                font-size: 18px;
                min-width: 130px;
            }

            .nav-btn {
                padding: 8px 14px;
                font-size: 13px;
            }

            .legend {
                gap: 14px;
                padding: 10px 14px;
            }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-content">
            <div class="nav-left"><span class="nav-title">Kasama Support Hub</span></div>
            <div class="nav-right">
                <a href="api/logout.php" style="color:white; text-decoration:none; font-weight:bold; font-size:13px;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <header class="dashboard-header">
            <div class="header-left">
                <img src="images/phinma-logo.png" alt="Logo" class="header-logo" style="width:36px; height:36px;">
                <span class="header-title" style="font-size:15px; font-weight:600;">Calendar Management</span>
            </div>
            <div class="header-right">
                <button class="btn-secondary" onclick="window.location.href='coach-dashboard.php'">← Back</button>
                <button class="btn-secondary" onclick="window.location.href='coach-appointments.php'">📋 Appointments</button>
            </div>
        </header>

        <main class="calendar-container">
            <div class="page-header">
                <h1>📅 Manage Your Calendar</h1>
            </div>
            
            <!-- Legend -->
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-dot available"></div>
                    <span>Available</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot blocked"></div>
                    <span>Blocked</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot booked"></div>
                    <span>Booked</span>
                </div>
            </div>
            
            <!-- Calendar Navigation -->
            <div class="calendar-nav">
                <button class="nav-btn" onclick="previousMonth()">← Prev</button>
                <span class="month-display" id="monthDisplay">January 2026</span>
                <button class="nav-btn" onclick="nextMonth()">Next →</button>
                <button class="nav-btn" onclick="goToToday()">Today</button>
            </div>
            
            <!-- Calendar Grid -->
            <div class="calendar-grid" id="calendarGrid">
                <!-- Generated by JavaScript -->
            </div>
        </main>
    </div>

    <!-- Slot Modal -->
    <div id="slotModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="slotModalTitle">Manage Time Slot</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div id="slotModalContent">
                <div class="form-group">
                    <label>📅 Date</label>
                    <input type="text" id="slotDate" readonly>
                </div>
                <div class="form-group">
                    <label>🕐 Time</label>
                    <input type="text" id="slotTime" readonly>
                </div>
                <div class="form-group">
                    <label>📝 Notes (optional)</label>
                    <textarea id="slotNotes" placeholder="e.g., Meeting, Training, Leave..."></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button class="btn-primary" id="slotActionBtn" style="background:#c62828;" onclick="toggleSlotBlock()">🚫 Block Slot</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let currentMonth = <?php echo date('n') - 1; ?>;
    let currentYear = <?php echo date('Y'); ?>;
    let availabilityData = {};
    let bookingsData = {};
    const timeSlots = ['8:00 AM', '9:30 AM', '11:00 AM', '1:30 PM', '3:00 PM', '4:30 PM'];
    
    let selectedDate = '';
    let selectedTime = '';
    let selectedStatus = '';
    
    // Month names
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                        'July', 'August', 'September', 'October', 'November', 'December'];
    
    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadAvailability();
    });
    
    // Load availability data from server
    async function loadAvailability() {
        const startDate = new Date(currentYear, currentMonth, 1);
        const endDate = new Date(currentYear, currentMonth + 1, 0);
        
        const start = startDate.toISOString().split('T')[0];
        const end = endDate.toISOString().split('T')[0];
        
        try {
            const response = await fetch(`api/manage-availability.php?action=get&start=${start}&end=${end}`);
            const result = await response.json();
            
            if (result.success) {
                availabilityData = result.availability || {};
                bookingsData = result.bookings || {};
            }
        } catch (e) {
            console.error('Failed to load availability:', e);
        }
        
        generateCalendar();
    }
    
    // Generate calendar
    function generateCalendar() {
        const grid = document.getElementById('calendarGrid');
        document.getElementById('monthDisplay').textContent = monthNames[currentMonth] + ' ' + currentYear;
        
        // Headers
        let html = `
            <div class="calendar-header-cell">Sun</div>
            <div class="calendar-header-cell">Mon</div>
            <div class="calendar-header-cell">Tue</div>
            <div class="calendar-header-cell">Wed</div>
            <div class="calendar-header-cell">Thu</div>
            <div class="calendar-header-cell">Fri</div>
            <div class="calendar-header-cell">Sat</div>
        `;
        
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const today = new Date();
        today.setHours(0,0,0,0);
        
        // Empty cells
        for (let i = 0; i < firstDay; i++) {
            html += '<div class="calendar-cell other-month"></div>';
        }
        
        // Days
        for (let day = 1; day <= daysInMonth; day++) {
            const dateObj = new Date(currentYear, currentMonth, day);
            const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isToday = dateObj.getTime() === today.getTime();
            const isPast = dateObj < today;
            const isWeekend = dateObj.getDay() === 0 || dateObj.getDay() === 6;
            
            let cellClasses = ['calendar-cell'];
            if (isToday) cellClasses.push('today');
            if (isWeekend) cellClasses.push('weekend');
            
            // Count blocked slots
            const dayAvailability = availabilityData[dateStr] || {};
            const dayBookings = bookingsData[dateStr] || {};
            let blockedCount = Object.values(dayAvailability).filter(v => v.status === 'blocked').length;
            if (blockedCount > 0) cellClasses.push('has-blocked');
            
            html += `<div class="${cellClasses.join(' ')}" data-date="${dateStr}">`;
            html += `<div class="day-number ${isToday ? 'today' : ''}">${day}</div>`;
            
            // Time slots (only for non-weekend, non-past)
            if (!isWeekend && !isPast) {
                timeSlots.forEach(slot => {
                    const slotStatus = dayAvailability[slot]?.status || 'available';
                    const booking = dayBookings[slot];
                    
                    let slotClass = 'time-slot-mini';
                    let slotText = slot;
                    
                    if (booking) {
                        slotClass += ' booked';
                        slotText = `${slot} - ${booking.student_name.split(' ')[0]}`;
                    } else if (slotStatus === 'blocked') {
                        slotClass += ' blocked';
                    } else {
                        slotClass += ' available';
                    }
                    
                    html += `<div class="${slotClass}" onclick="openSlotModal('${dateStr}', '${slot}', '${slotStatus}', ${booking ? 'true' : 'false'})">${slotText}</div>`;
                });
                
                // Day action buttons
                if (blockedCount === timeSlots.length) {
                    html += `<div class="day-actions"><button class="btn-unblock-day" onclick="unblockDay('${dateStr}')">Unblock Day</button></div>`;
                } else if (blockedCount === 0 && Object.keys(dayBookings).length === 0) {
                    html += `<div class="day-actions"><button class="btn-block-day" onclick="blockDay('${dateStr}')">Block Day</button></div>`;
                }
            } else if (isWeekend) {
                html += '<div class="cell-label">Weekend</div>';
            } else if (isPast) {
                html += '<div class="cell-label">Past</div>';
            }
            
            html += '</div>';
        }
        
        grid.innerHTML = html;
    }
    
    // Navigation
    function previousMonth() {
        currentMonth--;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        loadAvailability();
    }
    
    function nextMonth() {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        loadAvailability();
    }
    
    function goToToday() {
        currentMonth = new Date().getMonth();
        currentYear = new Date().getFullYear();
        loadAvailability();
    }
    
    // Modal functions
    function openSlotModal(date, time, status, isBooked) {
        if (isBooked) {
            alert('This slot has an active booking. Manage it from the Appointments page.');
            return;
        }
        
        selectedDate = date;
        selectedTime = time;
        selectedStatus = status;
        
        document.getElementById('slotDate').value = date;
        document.getElementById('slotTime').value = time;
        document.getElementById('slotNotes').value = '';
        
        const btn = document.getElementById('slotActionBtn');
        if (status === 'blocked') {
            btn.textContent = '✅ Unblock Slot';
            btn.style.background = '#4a7c2c';
        } else {
            btn.textContent = '🚫 Block Slot';
            btn.style.background = '#c62828';
        }
        
        document.getElementById('slotModal').style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('slotModal').style.display = 'none';
    }
    
    async function toggleSlotBlock() {
        const newStatus = selectedStatus === 'blocked' ? 'available' : 'blocked';
        const notes = document.getElementById('slotNotes').value;
        
        const formData = new FormData();
        formData.append('action', 'block');
        formData.append('date', selectedDate);
        formData.append('time_slot', selectedTime);
        formData.append('status', newStatus);
        formData.append('notes', notes);
        
        try {
            const response = await fetch('api/manage-availability.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                closeModal();
                loadAvailability();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (e) {
            alert('Connection error');
        }
    }
    
    async function blockDay(date) {
        if (!confirm('Block all time slots for ' + date + '?')) return;
        
        const formData = new FormData();
        formData.append('action', 'block_day');
        formData.append('date', date);
        formData.append('notes', 'Day blocked');
        
        try {
            const response = await fetch('api/manage-availability.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                loadAvailability();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (e) {
            alert('Connection error');
        }
    }
    
    async function unblockDay(date) {
        if (!confirm('Unblock all time slots for ' + date + '?')) return;
        
        const formData = new FormData();
        formData.append('action', 'unblock_day');
        formData.append('date', date);
        
        try {
            const response = await fetch('api/manage-availability.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                loadAvailability();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (e) {
            alert('Connection error');
        }
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