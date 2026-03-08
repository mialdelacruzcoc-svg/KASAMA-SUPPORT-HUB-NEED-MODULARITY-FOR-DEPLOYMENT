<?php
session_start();
// Redirect kung naka-login na
if (isset($_SESSION['user_id'])) {
    $redirect = ($_SESSION['role'] === 'coach') ? 'coach-dashboard.php' : 'student-dashboard.php';
    header('Location: ' . $redirect);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary-green: #4a7c2c;
            --dark-green: #365c20;
            --phinma-blue: #1a4a72;
        }
        body {
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-container {
            max-width: 450px;
            width: 100%;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header img {
            width: 120px;
            margin-bottom: 15px;
        }
        .register-header h1 {
            font-size: 24px;
            color: var(--phinma-blue);
            margin-bottom: 8px;
        }
        .input-group {
            margin-bottom: 20px;
        }
        .input-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            box-sizing: border-box;
        }
        .btn-register {
            width: 100%;
            padding: 14px;
            background: var(--primary-green);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-register:hover { background: var(--dark-green); }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
        }
        .alert-error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .register-footer { text-align: center; margin-top: 20px; font-size: 14px; }
        .register-footer a { color: var(--primary-green); text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <img src="images/phinma-logo.png" alt="PHINMA Logo">
            <h1>Student Access</h1>
            <p>Step 1: Verify your PHINMA Email</p>
        </div>

        <div id="alertBox"></div>

        <form id="registerForm">
            <div class="input-group">
                <label>Full Name *</label>
                <input type="text" id="fullname" placeholder="Juan Dela Cruz" required>
            </div>

            <div class="input-group">
                <label>COC Email Address *</label>
                <input type="email" id="email" placeholder="name.coc@phinmaed.com" required>
            </div>

            <div class="input-group">
    <label>Student ID * (e.g., 03-2223-012345)</label>
    <input type="text" id="studentId" placeholder="00-0000-000000" required maxlength="15">
</div>

<div class="input-group">
    <label>Year Level *</label>
    <select id="yearLevel" required style="width: 100%; padding: 12px 15px; border: 1.5px solid #e0e0e0; border-radius: 8px; box-sizing: border-box; font-size: 14px; background: white; color: #333;">
        <option value="" disabled selected>-- Select Year Level --</option>
        <option value="1st Year">1st Year</option>
        <option value="2nd Year">2nd Year</option>
        <option value="3rd Year">3rd Year</option>
        <option value="4th Year">4th Year</option>
    </select>
</div>

<button type="submit" class="btn-register" id="submitBtn">Get Verification Code 📧</button>
        </form>

        <div class="register-footer">
            Already have an account? <a href="index.php">Sign In</a>
        </div>
    </div>

    <script>
        const form = document.getElementById('registerForm');
        const studentIdInput = document.getElementById('studentId');
        const submitBtn = document.getElementById('submitBtn');

        // Auto-format Student ID (supports 5 or 6 digits at end)
        studentIdInput.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, ''); // Remove non-digits
            if (v.length > 2) v = v.slice(0,2) + '-' + v.slice(2);
            if (v.length > 7) v = v.slice(0,7) + '-' + v.slice(7);
            if (v.length > 15) v = v.slice(0, 15); // Max: XX-XXXX-XXXXXX
            e.target.value = v;
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value.trim();
            const studentId = studentIdInput.value.trim();
            const name = document.getElementById('fullname').value.trim();
            const yearLevel = document.getElementById('yearLevel').value;

            if (!yearLevel) {
                 showAlert('Please select your year level.', 'error');
                 return;
}


            if (!email.endsWith('@phinmaed.com')) {
                showAlert('Please use your official @phinmaed.com email', 'error');
                return;
            }

            // Validate Student ID format (5 or 6 digits at end)
            const idPattern = /^\d{2}-\d{4}-\d{5,6}$/;
            if (!idPattern.test(studentId)) {
                showAlert('Invalid Student ID format. Use: 03-2223-01234 or 03-2223-012345', 'error');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending Code...';

            try {
                const response = await fetch('api/send-verification-code.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `email=${encodeURIComponent(email)}&student_id=${encodeURIComponent(studentId)}&name=${encodeURIComponent(name)}&year_level=${encodeURIComponent(yearLevel)}`
                });

                const data = await response.json();

                if (data.success) {
                sessionStorage.setItem('reg_email', email);
                sessionStorage.setItem('reg_id', studentId);
                sessionStorage.setItem('reg_name', name);
                sessionStorage.setItem('reg_year_level', yearLevel);
                    
                    window.location.href = 'verify-code.php';
                } else {
                    showAlert(data.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Get Verification Code 📧';
                }
            } catch (error) {
                showAlert('Network error. Please try again.', 'error');
                submitBtn.disabled = false;
            }
        });

        function showAlert(msg, type) {
            document.getElementById('alertBox').innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
        }
    </script>
</body>
</html>