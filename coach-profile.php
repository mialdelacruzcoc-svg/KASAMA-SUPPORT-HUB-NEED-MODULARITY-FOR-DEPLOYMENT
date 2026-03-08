<?php
require_once 'api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch full coach data from DB
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$coach = mysqli_fetch_assoc($result);

if (!$coach) {
    header('Location: index.php');
    exit;
}

$coach_name = $coach['name'];
$coach_email = $coach['email'];
$coach_id = $coach['student_id'];
$created_at = isset($coach['created_at']) ? date('F d, Y', strtotime($coach['created_at'])) : 'N/A';

// Get initials
$words = explode(" ", $coach_name);
$initials = "";
foreach ($words as $w) {
    if (!empty($w))
        $initials .= strtoupper($w[0]);
}
$display_initials = substr($initials, 0, 2);

// Parse notification preferences
$default_prefs = [
    'email_new_concern' => true,
    'email_student_reply' => true,
    'email_appointment' => true
];
$notification_prefs = $default_prefs;
if (!empty($coach['notification_prefs'])) {
    $decoded = json_decode($coach['notification_prefs'], true);
    if (is_array($decoded)) {
        $notification_prefs = array_merge($default_prefs, $decoded);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ========== PROFILE PAGE STYLES ========== */
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .profile-hero {
            background: linear-gradient(135deg, #4a7c2c 0%, #2d5a1a 100%);
            border-radius: 16px;
            padding: 40px 30px;
            color: white;
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        .profile-hero::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }
        .profile-hero::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: 30%;
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }

        .profile-avatar-large {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 3px solid rgba(255,255,255,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            flex-shrink: 0;
            z-index: 1;
        }

        .profile-hero-info {
            z-index: 1;
        }
        .profile-hero-info h1 {
            color: white;
            margin: 0 0 4px 0;
            font-size: 24px;
        }
        .profile-hero-info .profile-role {
            opacity: 0.85;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .profile-hero-info .profile-id-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        /* Sections */
        .profile-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .profile-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            user-select: none;
            transition: background 0.2s;
        }
        .profile-section-header:hover {
            background: #fafafa;
        }
        .profile-section-header h2 {
            margin: 0;
            font-size: 17px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-toggle {
            font-size: 18px;
            color: #888;
            transition: transform 0.3s;
        }
        .profile-section.collapsed .section-toggle {
            transform: rotate(-90deg);
        }
        .profile-section.collapsed .profile-section-body {
            display: none;
        }
        .profile-section-body {
            padding: 24px;
        }

        /* Form Fields */
        .profile-field {
            margin-bottom: 20px;
        }
        .profile-field:last-child {
            margin-bottom: 0;
        }
        .profile-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .profile-field input[type="text"],
        .profile-field input[type="email"],
        .profile-field input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
            background: #fff;
        }
        .profile-field input:focus {
            outline: none;
            border-color: #4a7c2c;
            box-shadow: 0 0 0 3px rgba(74, 124, 44, 0.1);
        }
        .profile-field input[readonly] {
            background: #f5f5f5;
            color: #888;
            cursor: not-allowed;
        }
        .field-hint {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }

        /* Read-only Info Row */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-row .info-label {
            font-size: 14px;
            color: #777;
            font-weight: 500;
        }
        .info-row .info-value {
            font-size: 14px;
            color: #333;
            font-weight: 600;
        }
        .info-row .info-badge {
            display: inline-block;
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        /* Toggle Switch */
        .toggle-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .toggle-row:last-child {
            border-bottom: none;
        }
        .toggle-info h4 {
            margin: 0 0 2px 0;
            font-size: 15px;
            color: #333;
        }
        .toggle-info p {
            margin: 0;
            font-size: 13px;
            color: #888;
        }
        .toggle-switch {
            position: relative;
            width: 50px;
            height: 28px;
            flex-shrink: 0;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #ccc;
            border-radius: 28px;
            transition: background 0.3s;
        }
        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 22px;
            height: 22px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .toggle-switch input:checked + .toggle-slider {
            background: #4a7c2c;
        }
        .toggle-switch input:checked + .toggle-slider::before {
            transform: translateX(22px);
        }

        /* Buttons */
        .btn-save-profile {
            background: #4a7c2c;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-save-profile:hover {
            background: #3d6624;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(74, 124, 44, 0.3);
        }
        .btn-save-profile:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 14px 24px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            z-index: 10000;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }
        .toast.success {
            background: linear-gradient(135deg, #4a7c2c, #2e7d32);
        }
        .toast.error {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }

        /* Password Strength */
        .password-strength {
            height: 4px;
            border-radius: 4px;
            background: #eee;
            margin-top: 8px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s, background 0.3s;
            width: 0;
        }
        .password-strength-text {
            font-size: 12px;
            margin-top: 4px;
            font-weight: 500;
        }

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

        /* Responsive */
        @media (max-width: 600px) {
            .profile-hero {
                flex-direction: column;
                text-align: center;
                padding: 30px 20px;
            }
            .profile-section-body {
                padding: 18px;
            }
            .profile-hero-info h1 {
                font-size: 20px;
            }
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
            .toggle-row {
                gap: 15px;
            }
            .toast {
                left: 15px;
                right: 15px;
                bottom: 15px;
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
                <a href="api/logout.php" style="color:white; text-decoration:none; font-weight:bold;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <header class="dashboard-header">
            <div class="header-left">
                <img src="images/phinma-logo.png" alt="Logo" class="header-logo">
                <span class="header-title">My Profile</span>
            </div>
            <div class="header-right">
                <button class="btn-back" onclick="window.location.href='coach-dashboard.php'">← Back to Dashboard</button>
                <?php include 'includes/profile-dropdown.php'; ?>
            </div>
        </header>

        <main class="dashboard-main">
            <div class="profile-container">

                <!-- Hero Card -->
                <div class="profile-hero">
                    <div class="profile-avatar-large"><?php echo $display_initials; ?></div>
                    <div class="profile-hero-info">
                        <h1 id="heroName">Coach <?php echo htmlspecialchars($coach_name); ?></h1>
                        <div class="profile-role">Guidance Coach · Kasama Support Hub</div>
                        <span class="profile-id-badge">🆔 <?php echo htmlspecialchars($coach_id); ?></span>
                    </div>
                </div>

                <!-- Account Details Section -->
                <div class="profile-section" id="accountSection">
                    <div class="profile-section-header" onclick="toggleSection('accountSection')">
                        <h2>👤 Account Details</h2>
                        <span class="section-toggle">▼</span>
                    </div>
                    <div class="profile-section-body">
                        <form id="profileForm" onsubmit="saveProfile(event)">
                            <div class="profile-field">
                                <label for="profileName">Display Name</label>
                                <input type="text" id="profileName" value="<?php echo htmlspecialchars($coach_name); ?>" required minlength="2" maxlength="100">
                            </div>
                            <div class="profile-field">
                                <label for="profileEmail">Email Address</label>
                                <input type="email" id="profileEmail" value="<?php echo htmlspecialchars($coach_email); ?>" required>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Coach ID</span>
                                <span class="info-badge"><?php echo htmlspecialchars($coach_id); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Role</span>
                                <span class="info-badge">Coach</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Member Since</span>
                                <span class="info-value"><?php echo $created_at; ?></span>
                            </div>
                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn-save-profile" id="btnSaveProfile">💾 Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password Section -->
                <div class="profile-section collapsed" id="passwordSection">
                    <div class="profile-section-header" onclick="toggleSection('passwordSection')">
                        <h2>🔒 Change Password</h2>
                        <span class="section-toggle">▼</span>
                    </div>
                    <div class="profile-section-body">
                        <form id="passwordForm" onsubmit="changePassword(event)">
                            <div class="profile-field">
                                <label for="currentPassword">Current Password</label>
                                <input type="password" id="currentPassword" required placeholder="Enter your current password">
                            </div>
                            <div class="profile-field">
                                <label for="newPassword">New Password</label>
                                <input type="password" id="newPassword" required placeholder="Minimum 8 characters" minlength="8" oninput="checkPasswordStrength(this.value)">
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="strengthBar"></div>
                                </div>
                                <div class="password-strength-text" id="strengthText"></div>
                            </div>
                            <div class="profile-field">
                                <label for="confirmPassword">Confirm New Password</label>
                                <input type="password" id="confirmPassword" required placeholder="Re-enter new password" minlength="8">
                            </div>
                            <div style="margin-top: 20px;">
                                <button type="submit" class="btn-save-profile" id="btnChangePassword">🔑 Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Notification Preferences Section -->
                <div class="profile-section" id="notifSection">
                    <div class="profile-section-header" onclick="toggleSection('notifSection')">
                        <h2>🔔 Notification Preferences</h2>
                        <span class="section-toggle">▼</span>
                    </div>
                    <div class="profile-section-body">
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <h4>New Concern Submitted</h4>
                                <p>Get notified when a student submits a new concern</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="prefConcern" <?php echo $notification_prefs['email_new_concern'] ? 'checked' : ''; ?> onchange="saveNotifPrefs()">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <h4>Student Replies</h4>
                                <p>Get notified when a student replies to a concern thread</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="prefReply" <?php echo $notification_prefs['email_student_reply'] ? 'checked' : ''; ?> onchange="saveNotifPrefs()">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <h4>Appointment Updates</h4>
                                <p>Get notified about new bookings and schedule changes</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="prefAppointment" <?php echo $notification_prefs['email_appointment'] ? 'checked' : ''; ?> onchange="saveNotifPrefs()">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="field-hint" style="margin-top: 12px;">Changes are saved automatically when toggled.</div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Toast Container -->
    <div class="toast" id="toast"></div>

    <script>
    // ============================================
    // SECTION TOGGLE
    // ============================================
    function toggleSection(sectionId) {
        const section = document.getElementById(sectionId);
        section.classList.toggle('collapsed');
    }

    // ============================================
    // TOAST NOTIFICATION
    // ============================================
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'toast ' + type;
        
        // Trigger reflow
        void toast.offsetWidth;
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    // ============================================
    // SAVE PROFILE (Name & Email)
    // ============================================
    async function saveProfile(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSaveProfile');
        const name = document.getElementById('profileName').value.trim();
        const email = document.getElementById('profileEmail').value.trim();
        
        if (!name || !email) {
            showToast('Please fill in all fields.', 'error');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Saving...';

        try {
            const formData = new FormData();
            formData.append('name', name);
            formData.append('email', email);

            const response = await fetch('api/update-coach-profile.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            const data = await response.json();

            if (data.success) {
                showToast('✅ Profile updated successfully!');
                // Update hero section
                document.getElementById('heroName').textContent = 'Coach ' + name;
                // Update header name
                document.querySelector('.user-name').textContent = 'Coach ' + name;
                // Update avatar initials
                const words = name.split(' ');
                let initials = '';
                words.forEach(w => { if (w) initials += w[0].toUpperCase(); });
                initials = initials.substring(0, 2);
                document.querySelectorAll('.user-avatar, .profile-avatar-large').forEach(el => {
                    el.textContent = initials;
                });
            } else {
                showToast('❌ ' + (data.message || 'Update failed'), 'error');
            }
        } catch (err) {
            showToast('❌ Network error. Please try again.', 'error');
        }

        btn.disabled = false;
        btn.textContent = '💾 Save Changes';
    }

    // ============================================
    // CHANGE PASSWORD
    // ============================================
    function checkPasswordStrength(password) {
        const bar = document.getElementById('strengthBar');
        const text = document.getElementById('strengthText');
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        const levels = [
            { width: '0%', bg: '#eee', label: '', color: '#999' },
            { width: '20%', bg: '#ef4444', label: 'Very Weak', color: '#ef4444' },
            { width: '40%', bg: '#f97316', label: 'Weak', color: '#f97316' },
            { width: '60%', bg: '#eab308', label: 'Fair', color: '#eab308' },
            { width: '80%', bg: '#22c55e', label: 'Strong', color: '#22c55e' },
            { width: '100%', bg: '#16a34a', label: 'Very Strong', color: '#16a34a' }
        ];

        const level = levels[strength];
        bar.style.width = level.width;
        bar.style.background = level.bg;
        text.textContent = level.label;
        text.style.color = level.color;
    }

    async function changePassword(e) {
        e.preventDefault();
        const btn = document.getElementById('btnChangePassword');
        const current = document.getElementById('currentPassword').value;
        const newPass = document.getElementById('newPassword').value;
        const confirm = document.getElementById('confirmPassword').value;

        if (newPass.length < 8) {
            showToast('❌ New password must be at least 8 characters.', 'error');
            return;
        }
        if (newPass !== confirm) {
            showToast('❌ Passwords do not match.', 'error');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Updating...';

        try {
            const formData = new FormData();
            formData.append('current_password', current);
            formData.append('new_password', newPass);

            const response = await fetch('api/update-coach-password.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            const data = await response.json();

            if (data.success) {
                showToast('✅ Password changed successfully!');
                document.getElementById('passwordForm').reset();
                document.getElementById('strengthBar').style.width = '0';
                document.getElementById('strengthText').textContent = '';
            } else {
                showToast('❌ ' + (data.message || 'Password change failed'), 'error');
            }
        } catch (err) {
            showToast('❌ Network error. Please try again.', 'error');
        }

        btn.disabled = false;
        btn.textContent = '🔑 Change Password';
    }

    // ============================================
    // NOTIFICATION PREFERENCES
    // ============================================
    let prefsTimeout = null;
    async function saveNotifPrefs() {
        // Debounce rapid toggles
        if (prefsTimeout) clearTimeout(prefsTimeout);
        prefsTimeout = setTimeout(async () => {
            const prefs = {
                email_new_concern: document.getElementById('prefConcern').checked,
                email_student_reply: document.getElementById('prefReply').checked,
                email_appointment: document.getElementById('prefAppointment').checked
            };

            try {
                const formData = new FormData();
                formData.append('prefs', JSON.stringify(prefs));

                const response = await fetch('api/update-notification-prefs.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                const data = await response.json();

                if (data.success) {
                    showToast('✅ Preferences saved!');
                } else {
                    showToast('❌ ' + (data.message || 'Failed to save'), 'error');
                }
            } catch (err) {
                showToast('❌ Network error.', 'error');
            }
        }, 300);
    }
    </script>
</body>
</html>
