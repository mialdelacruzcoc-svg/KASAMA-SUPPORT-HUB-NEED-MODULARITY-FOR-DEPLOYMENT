<?php
require_once 'api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['name'];

$concerns_query = "SELECT tracking_id, subject FROM concerns WHERE student_id = '$student_id' AND status != 'Resolved'";
$concerns_result = mysqli_query($conn, $concerns_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ==================== MOBILE RESPONSIVE FIX ==================== */
        @media screen and (max-width: 768px) {
            /* Himoon nga one column ang layout imbes nga 2-columns */
            .appointment-container {
                display: flex !important;
                flex-direction: column !important;
                gap: 15px !important;
            }

            /* I-balhin ang Office Hours sidebar sa ubos para tibuok screen ang calendar */
            .help-sidebar {
                order: 2;
                width: 100% !important;
            }

            .booking-section {
                order: 1;
                padding: 15px !important; /* Gamayan ang padding para sa gamay nga screen */
            }

            /* I-stack ang navigation buttons sa header */
            .dashboard-header {
                flex-direction: column;
                gap: 10px;
                padding: 15px !important;
                height: auto !important;
            }

            .header-right {
                width: 100%;
                justify-content: space-between;
            }

            /* I-adjust ang Calendar grid para dili maghuot ang mga numero */
            .calendar {
                gap: 5px !important;
            }

            .calendar-day {
                padding: 4px !important;
                font-size: 12px !important;
            }

            /* Padak-on ang Time Slots para sayon pisliton sa mobile */
            .time-slots {
                grid-template-columns: repeat(2, 1fr) !important;
            }

            .time-slot {
                padding: 15px !important;
                font-size: 16px !important;
            }

            /* Buttons full width sa mobile */
            .btn-submit, .btn-secondary {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        /* ==================== ORIGINAL STYLES ==================== */
        .appointment-container { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; max-width: 1400px; }
        .booking-section { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .calendar-nav { display: flex; gap: 10px; }
        .calendar-nav button { padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 6px; cursor: pointer; transition: all 0.2s; color: #333; }
        .current-month { font-size: 20px; font-weight: 600; color: #333; }
        .calendar { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; margin-bottom: 30px; }
        .calendar-day-header { text-align: center; font-size: 12px; font-weight: 600; color: #666; text-transform: uppercase; padding: 10px 0; }
        .calendar-day { aspect-ratio: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer; transition: all 0.2s; padding: 8px; }
        .calendar-day.selected { background: #4a7c2c; color: white; border-color: #4a7c2c; }
        .calendar-day.past { opacity: 0.3; cursor: not-allowed; }
        .calendar-day.disabled { background: #f5f5f5; cursor: not-allowed; }
        .time-slots { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; margin-top: 15px; }
        .time-slot { padding: 12px; border: 1.5px solid #e0e0e0; border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.2s; font-size: 14px; }
        .time-slot.selected { background: #4a7c2c; color: white; }
        .time-slot.blocked { background: #f5f5f5; color: #bbb; border-color: #e0e0e0; cursor: not-allowed; text-decoration: line-through; }
        .time-slot.booked  { background: #fde8e8; color: #c0392b; border-color: #f5c6cb; cursor: not-allowed; }
        .slot-label { font-size: 10px; margin-top: 3px; display: block; }
        .booking-form { margin-top: 30px; padding-top: 30px; border-top: 2px solid #f0f0f0; }
        .selected-datetime { background: #e8f5e9; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .help-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-content">
            <div class="nav-left">
                <span class="nav-title">Kasama Support Hub</span>
            </div>
            <div class="nav-right">
                <button class="nav-icon">🔔</button>
                <a href="api/logout.php" style="color: white; text-decoration: none; font-weight: bold; margin-left: 15px;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <header class="dashboard-header">
            <div class="header-left">
                <img src="images/phinma-logo.png" alt="Logo" class="header-logo">
                <span class="header-title">Book Appointment</span>
            </div>
            <div class="header-right">
                <button class="btn-back" onclick="window.location.href='student-dashboard.php'">← Back</button>
                <div class="user-profile">
                    <div class="user-avatar" style="background:#4a7c2c; color:white; display:flex; align-items:center; justify-content:center; width:35px; height:35px; border-radius:50%; font-weight:bold;">
                        <?php echo strtoupper(substr($student_name, 0, 1)); ?>
                    </div>
                    <span class="user-name"><?php echo $student_name; ?></span>
                </div>
            </div>
        </header>

        <main class="dashboard-main">
            <h1 class="page-title">📅 Schedule an Appointment</h1>
            <div class="appointment-container">
                <div class="booking-section">
                    <div class="calendar-header">
                        <h2 class="current-month" id="currentMonth">January 2026</h2>
                        <div class="calendar-nav">
                            <button onclick="previousMonth()">← Prev</button>
                            <button onclick="nextMonth()">Next →</button>
                        </div>
                    </div>

                    <div class="calendar" id="calendar">
                        <div class="calendar-day-header">Sun</div>
                        <div class="calendar-day-header">Mon</div>
                        <div class="calendar-day-header">Tue</div>
                        <div class="calendar-day-header">Wed</div>
                        <div class="calendar-day-header">Thu</div>
                        <div class="calendar-day-header">Fri</div>
                        <div class="calendar-day-header">Sat</div>
                    </div>

                    <div class="time-slots-section" id="timeSlotsSection" style="display: none;">
                        <h3>Available Time Slots</h3>
                        <div class="time-slots" id="timeSlots"></div>
                    </div>

                    <div class="booking-form" id="bookingForm" style="display: none;">
                        <h3>Appointment Details</h3>
                        <div class="selected-datetime">
                            <p><strong>Date:</strong> <span id="displayDate">-</span></p>
                            <p><strong>Time:</strong> <span id="displayTime">-</span></p>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display:block; margin-bottom:8px; font-weight:500;">Purpose of Appointment *</label>
                            <textarea id="appointmentPurpose" placeholder="Describe what you'd like to discuss..." style="width:100%; min-height:100px; padding:12px; border:1px solid #ddd; border-radius:8px;"></textarea>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display:block; margin-bottom:8px; font-weight:500;">Link to Existing Concern (Optional)</label>
                            <select id="linkConcern" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                <option value="">None - New appointment</option>
                                <?php mysqli_data_seek($concerns_result, 0); ?>
                                <?php while ($row = mysqli_fetch_assoc($concerns_result)): ?>
                                    <option value="<?php echo $row['tracking_id']; ?>"><?php echo $row['tracking_id'] . " - " . $row['subject']; ?></option>
                                <?php
endwhile; ?>
                            </select>
                        </div>

                        <div style="display:flex; gap:12px; justify-content:flex-end; flex-wrap: wrap;">
                            <button class="btn-secondary" onclick="window.location.href='student-dashboard.php'">Cancel</button>
                            <button class="btn-submit" onclick="confirmBooking()" style="background:#4a7c2c; color:white; border:none; padding:12px 24px; border-radius:8px; cursor:pointer;">Confirm Appointment</button>
                        </div>
                    </div>
                </div>

                <div class="help-sidebar">
                    <div class="help-card">
                        <h3>📋 Office Hours</h3>
                        <ul>
                            <li><strong>Mon-Thu:</strong> 8:00 AM - 6:00 PM</li>
                            <li><strong>Friday:</strong> 8:00 AM - 5:00 PM</li>
                            <li><strong>Lunch:</strong> 12:00 NN - 1:00 PM</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let selectedDate = null;
        let selectedTime = null;
        let currentMonth = 0; // January
        let currentYear = 2026;
        const timeSlots = ['8:00 AM', '9:30 AM', '11:00 AM', '1:30 PM', '3:00 PM', '4:30 PM'];

        function generateCalendar() {
            const calendar = document.getElementById('calendar');
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            document.getElementById('currentMonth').textContent = monthNames[currentMonth] + ' ' + currentYear;
            
            const headers = `<div class="calendar-day-header">Sun</div><div class="calendar-day-header">Mon</div><div class="calendar-day-header">Tue</div><div class="calendar-day-header">Wed</div><div class="calendar-day-header">Thu</div><div class="calendar-day-header">Fri</div><div class="calendar-day-header">Sat</div>`;
            calendar.innerHTML = headers;

            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const today = new Date(); today.setHours(0,0,0,0);

            for (let i = 0; i < firstDay; i++) calendar.appendChild(document.createElement('div'));

            for (let day = 1; day <= daysInMonth; day++) {
                const dayEl = document.createElement('div');
                dayEl.className = 'calendar-day';
                const dateObj = new Date(currentYear, currentMonth, day);
                
                if (dateObj < today) dayEl.classList.add('past');
                else if (dateObj.getDay() === 0 || dateObj.getDay() === 6) dayEl.classList.add('disabled');
                
                dayEl.innerHTML = `<span class="day-number">${day}</span>`;
                
                if (!dayEl.classList.contains('past') && !dayEl.classList.contains('disabled')) {
                    dayEl.onclick = () => {
                        document.querySelectorAll('.calendar-day').forEach(el => el.classList.remove('selected'));
                        dayEl.classList.add('selected');
                        selectedDate = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        showTimeSlots();
                    };
                }
                calendar.appendChild(dayEl);
            }
        }

        function showTimeSlots() {
            const container = document.getElementById('timeSlots');
            container.innerHTML = '<p style="color:#888;">Loading available slots...</p>';
            document.getElementById('timeSlotsSection').style.display = 'block';
            document.getElementById('bookingForm').style.display = 'none';
            selectedTime = null;

            fetch('api/get-blocked-slots.php?date=' + selectedDate)
                .then(r => r.json())
                .then(data => {
                    const blocked = data.blocked || [];
                    const booked  = data.booked  || [];
                    container.innerHTML = '';

                    timeSlots.forEach(time => {
                        const slot = document.createElement('div');
                        const isBlocked = blocked.includes(time);
                        const isBooked  = booked.includes(time);

                        if (isBlocked) {
                            slot.className = 'time-slot blocked';
                            slot.innerHTML = time + '<span class="slot-label">Blocked</span>';
                        } else if (isBooked) {
                            slot.className = 'time-slot booked';
                            slot.innerHTML = time + '<span class="slot-label">Already Booked</span>';
                        } else {
                            slot.className = 'time-slot';
                            slot.textContent = time;
                            slot.onclick = () => {
                                document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('selected'));
                                slot.classList.add('selected');
                                selectedTime = time;
                                document.getElementById('displayDate').textContent = selectedDate;
                                document.getElementById('displayTime').textContent = time;
                                document.getElementById('bookingForm').style.display = 'block';
                                document.getElementById('bookingForm').scrollIntoView({ behavior: 'smooth' });
                            };
                        }
                        container.appendChild(slot);
                    });
                })
                .catch(() => {
                    container.innerHTML = '<p style="color:red;">Failed to load availability. Please try again.</p>';
                });
        }

        async function confirmBooking() {
            const purpose = document.getElementById('appointmentPurpose').value;
            if (!selectedDate || !selectedTime) { alert('Palihug pagpili una og petsa ug oras.'); return; }
            if (!purpose || purpose.trim().length < 5) { alert('Palihug paghatag og rason.'); return; }

            const formData = new FormData();
            formData.append('date', selectedDate);
            formData.append('time', selectedTime);
            formData.append('reason', purpose);
            formData.append('linked_concern', document.getElementById('linkConcern').value);

            try {
                const response = await fetch('api/save-appointment.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    alert('✅ Appointment successfully booked!');
                    window.location.href = 'student-dashboard.php';
                } else { alert('❌ ' + result.message); }
            } catch (e) { alert('System error occurred.'); }
        }

        function previousMonth() { currentMonth--; if(currentMonth < 0) {currentMonth=11; currentYear--;} generateCalendar(); }
        function nextMonth() { currentMonth++; if(currentMonth > 11) {currentMonth=0; currentYear++;} generateCalendar(); }

        generateCalendar();
    </script>
</body>
</html>