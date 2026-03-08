<?php
session_start();
// Opsyonal: I-check kung naay registration_email sa session/sessionStorage 
// para dili ma-access sa bisan kinsa ang page nga walay request.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .verify-container {
            max-width: 450px;
            width: 100%;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .verify-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .verify-header h1 {
            font-size: 24px;
            color: var(--primary-green);
            margin-bottom: 10px;
        }

        .verify-header p {
            color: var(--medium-gray);
            font-size: 14px;
        }

        .email-display {
            font-weight: 600;
            color: #333;
            display: block;
            margin-top: 5px;
        }

        /* OTP Input Styling */
        .otp-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 30px 0;
        }

        .otp-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            border: 2px solid #ececec;
            border-radius: 8px;
            color: var(--primary-green);
            transition: all 0.3s ease;
        }

        .otp-input:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(74, 124, 44, 0.08);
        }

        .resend-text {
            text-align: center;
            font-size: 13px;
            color: var(--medium-gray);
            margin-top: 20px;
        }

        .resend-link {
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .resend-link.disabled {
            color: #ccc;
            cursor: not-allowed;
            text-decoration: none;
        }

        .btn-verify {
            width: 100%;
            padding: 16px;
            background: var(--primary-green);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-verify:hover {
            background: var(--dark-green);
            transform: translateY(-2px);
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
        }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef5350; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #66bb6a; }
    </style>
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f0f2f5; padding: 20px;">

    <div class="verify-container">
        <div class="verify-header">
            <img src="images/phinma-logo.png" alt="Logo" style="width: 60px; margin-bottom: 15px;">
            <h1>Verify Your Email</h1>
            <p>We've sent a 6-digit code to: <br>
               <span id="displayEmail" class="email-display">---</span>
            </p>
        </div>

        <div id="alertBox"></div>

        <form id="verifyForm">
            <div class="otp-container">
                <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                <input type="text" class="otp-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
            </div>

            <button type="submit" class="btn-verify" id="verifyBtn">Verify Account</button>
        </form>

        <p class="resend-text">
            Didn't receive the code? <br>
            <span id="resendTimer">Resend in 60s</span>
            <a id="resendLink" class="resend-link disabled" style="display:none">Resend Code</a>
        </p>

        <div style="text-align: center; margin-top: 20px;">
            <a href="register.php" style="color: #666; font-size: 13px; text-decoration: none;">← Back to Registration</a>
        </div>
    </div>

    <script>
    // FIX: Use correct sessionStorage keys
    const email = sessionStorage.getItem('reg_email');
    const studentId = sessionStorage.getItem('reg_id');
    const studentName = sessionStorage.getItem('reg_name');
    
    const displayEmail = document.getElementById('displayEmail');
    const otpInputs = document.querySelectorAll('.otp-input');
    const verifyForm = document.getElementById('verifyForm');
    const alertBox = document.getElementById('alertBox');

    // Redirect if no email found
    if (!email) {
        window.location.href = 'register.php';
    } else {
        displayEmail.textContent = email;
    }

    // Auto-focus and move to next input
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1 && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });
    });

    // Handle Verification
    verifyForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const otp = Array.from(otpInputs).map(input => input.value).join('');
        const verifyBtn = document.getElementById('verifyBtn');
        
        if (otp.length !== 6) {
            showAlert('Please enter the complete 6-digit code', 'error');
            return;
        }
        
        verifyBtn.disabled = true;
        verifyBtn.textContent = 'Verifying...';

        try {
            const response = await fetch('api/verify-otp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}&otp=${encodeURIComponent(otp)}`
            });

            const data = await response.json();

            if (data.success) {
                showAlert('Email verified! Setting up your password...', 'success');
                setTimeout(() => {
                    window.location.href = 'setup-password.php';
                }, 1500);
            } else {
                showAlert(data.message || 'Invalid code. Please try again.', 'error');
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'Verify Account';
            }
        } catch (error) {
            showAlert('Network error. Please try again.', 'error');
            verifyBtn.disabled = false;
            verifyBtn.textContent = 'Verify Account';
        }
    });

    function showAlert(message, type) {
        alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    }

    // Resend Timer Logic
    let timeLeft = 60;
    const resendTimer = document.getElementById('resendTimer');
    const resendLink = document.getElementById('resendLink');

    const timer = setInterval(() => {
        timeLeft--;
        resendTimer.textContent = `Resend in ${timeLeft}s`;
        if (timeLeft <= 0) {
            clearInterval(timer);
            resendTimer.style.display = 'none';
            resendLink.style.display = 'inline';
            resendLink.classList.remove('disabled');
        }
    }, 1000);

    // Resend Code Handler
    resendLink.addEventListener('click', async () => {
        if (resendLink.classList.contains('disabled')) return;
        
        resendLink.textContent = 'Sending...';
        resendLink.classList.add('disabled');
        
        try {
            const response = await fetch('api/send-verification-code.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}&student_id=${encodeURIComponent(studentId)}&name=${encodeURIComponent(studentName)}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('New code sent! Check your email.', 'success');
                // Reset timer
                timeLeft = 60;
                resendLink.style.display = 'none';
                resendTimer.style.display = 'inline';
            } else {
                showAlert(data.message || 'Failed to resend code.', 'error');
                resendLink.classList.remove('disabled');
                resendLink.textContent = 'Resend Code';
            }
        } catch (error) {
            showAlert('Network error.', 'error');
            resendLink.classList.remove('disabled');
            resendLink.textContent = 'Resend Code';
        }
    });
</script>
</body>
</html>