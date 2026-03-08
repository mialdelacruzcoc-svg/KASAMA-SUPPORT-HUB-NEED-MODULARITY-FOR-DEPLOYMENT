<?php
session_start();
// Siguruha nga naay email sa session para dili ma-bypass ang security
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Password - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .password-container { max-width: 450px; width: 100%; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin: auto; }
        .password-header { text-align: center; margin-bottom: 25px; }
        .password-header h1 { font-size: 24px; color: var(--primary-green); }
        .btn-finish { width: 100%; padding: 16px; background: var(--primary-green); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px; }
        .btn-finish:hover { background: var(--dark-green); }
        .password-wrapper { position: relative; width: 100%; }
        .password-wrapper input { padding-right: 45px; }
        .toggle-password { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; color: #888; font-size: 18px; line-height: 1; display: flex; align-items: center; }
        .toggle-password:hover { color: #333; }
    </style>
</head>
<body style="display: flex; align-items: center; min-height: 100vh; background: #f0f2f5;">
    <div class="password-container">
        <div class="password-header">
            <h1>Create Password</h1>
            <p>Set a strong password for your account</p>
        </div>
        <div id="alertBox"></div>
        <form id="passwordForm">
            <div class="input-group">
                <label>New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" required minlength="8" placeholder="Minimum 8 characters">
                    <button type="button" class="toggle-password" onclick="togglePass('password', this)" title="Show password">👁</button>
                </div>
            </div>
            <div class="input-group">
                <label>Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirmPassword" required placeholder="Repeat your password">
                    <button type="button" class="toggle-password" onclick="togglePass('confirmPassword', this)" title="Show password">👁</button>
                </div>
            </div>
            <button type="submit" class="btn-finish" id="finishBtn">Complete Registration</button>
        </form>
    </div>

    <script>
        const email = sessionStorage.getItem('reg_email');
        const student_id = sessionStorage.getItem('reg_id');
        const student_name = sessionStorage.getItem('reg_name') || '';
        const year_level = sessionStorage.getItem('reg_year_level') || '';

        if (!email) window.location.href = 'register.php';

        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirmPassword').value;

            if (pass !== confirm) {
                alert('Passwords do not match!');
                return;
            }

            const btn = document.getElementById('finishBtn');
            btn.disabled = true;
            btn.textContent = 'Saving account...';

            try {
                const response = await fetch('api/complete-registration.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `email=${encodeURIComponent(email)}&student_id=${encodeURIComponent(student_id)}&password=${encodeURIComponent(pass)}&name=${encodeURIComponent(student_name)}&year_level=${encodeURIComponent(year_level)}`
                });
                const data = await response.json();
                if (data.success) {
                    alert('Registration complete! Redirecting to login...');
                    sessionStorage.clear();
                    window.location.href = 'index.php';
                } else {
                    alert(data.message);
                    btn.disabled = false;
                    btn.textContent = 'Complete Registration';
                }
            } catch (error) {
                alert('Connection error. Please try again.');
                btn.disabled = false;
                btn.textContent = 'Complete Registration';
            }
        });
    // Password visibility toggle
    function togglePass(inputId, btn) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
            btn.title = 'Hide password';
        } else {
            input.type = 'password';
            btn.textContent = '👁';
            btn.title = 'Show password';
        }
    }
    </script>
</body>
</html>