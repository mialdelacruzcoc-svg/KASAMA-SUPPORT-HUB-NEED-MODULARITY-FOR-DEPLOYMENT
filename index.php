<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasama Support Hub - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Custom styles for Registration Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        .close-btn {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            cursor: pointer;
            color: #666;
        }
        .register-link-container {
            margin-top: 20px;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .btn-register-open {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        .reg-input-group {
            margin-bottom: 15px;
        }
        .reg-input-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: #333;
        }
        .reg-input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn-submit-reg {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        /* FIXED: Siguraduhon nga ma-click ang forgot password link */
        .forgot-password {
            display: block;
            margin-top: 10px;
            color: #4a7c2c;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            z-index: 10;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
        /* Password toggle eye icon */
        .password-wrapper {
            position: relative;
            width: 100%;
        }
        .password-wrapper input {
            padding-right: 45px;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #888;
            font-size: 18px;
            line-height: 1;
            display: flex;
            align-items: center;
        }
        .toggle-password:hover {
            color: #333;
        }
        /* Math CAPTCHA */
        .captcha-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 15px 0 5px;
            padding: 10px 12px;
            background: #f8f9fa;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
        }
        .captcha-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            height: 40px;
            background: white;
            border: 1.5px solid #d0d0d0;
            border-radius: 6px;
            font-size: 18px;
            font-weight: 700;
            color: #1a4a72;
            letter-spacing: 1px;
            user-select: none;
        }
        .captcha-op {
            font-size: 18px;
            font-weight: 600;
            color: #555;
            user-select: none;
        }
        .captcha-answer {
            width: 60px;
            height: 40px;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            border: 1.5px solid #d0d0d0;
            border-radius: 6px;
            outline: none;
            color: #333;
            transition: border-color 0.2s;
        }
        .captcha-answer:focus {
            border-color: #1a4a72;
            box-shadow: 0 0 0 3px rgba(26, 74, 114, 0.08);
        }
        .captcha-refresh {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #888;
            padding: 4px;
            margin-left: auto;
            transition: color 0.2s, transform 0.3s;
            display: flex;
            align-items: center;
        }
        .captcha-refresh:hover {
            color: #1a4a72;
            transform: rotate(180deg);
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-container">
            
            <div class="login-left">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
                <div class="shape shape-4"></div>
                
                <div class="content-wrapper">
                    <div class="college-header-block">
                        <img src="images/coc-logo.png" alt="COC Logo" class="coc-logo">
                        <h1>Cagayan De Oro College</h1>
                        <p class="address">Max Suniel St. Carmen, Cagayan de Oro City, Misamis Oriental, Philippines 9000</p>
                    </div>
                    <div class="support-info">
                        <h2 class="support-tagline">Your concerns matter. We're here to help.</h2>
                        <ul class="support-features">
                            <li>Submit concerns confidentially</li>
                            <li>Get personalized guidance from coaches</li>
                            <li>Access resources and FAQs</li>
                            <li>Book one-on-one appointments</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="login-right">
                <div class="phinma-header">
                    <img src="images/phinma-logo.png" alt="PHINMA" class="phinma-logo">
                    <div class="phinma-info">
                        <div class="phinma-title">PHINMA EDUCATION</div>
                        <div class="phinma-subtitle">MAKING LIVES BETTER THROUGH EDUCATION</div>
                        <div class="kasama-title">Kasama Support Hub</div>
                    </div>
                </div>

                <div class="signin-section">
                    <h2>Sign In</h2>
                    
                    <form id="loginForm">
                        <div class="input-group">
                            <label>* Username</label>
                            <input type="text" id="username" placeholder="Enter Username" required>
                        </div>
                        
                        <div class="input-group">
                            <label>* Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="password" placeholder="Enter Password" required>
                                <button type="button" class="toggle-password" onclick="togglePass('password', this)" title="Show password">👁</button>
                            </div>
                        </div>

                        <div class="captcha-group">
                            <span class="captcha-num" id="captchaNum1">--</span>
                            <span class="captcha-op">+</span>
                            <span class="captcha-num" id="captchaNum2">-</span>
                            <span class="captcha-op">=</span>
                            <input type="text" class="captcha-answer" id="captchaAnswer" maxlength="3" inputmode="numeric" autocomplete="off" required placeholder="?">
                            <button type="button" class="captcha-refresh" id="captchaRefresh" title="New numbers">🔄</button>
                        </div>
                        
                        <button type="submit" class="btn-signin">Sign In </button>
                        
                        <div class="register-link-container">
                            <p> Still don't have an account? <span id="openRegister" class="btn-register-open">Register Here!</span></p>
                            <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeModal">&times;</span>
            <h2 style="margin-bottom: 20px; color: #1a4a72;">Student Registration</h2>
            <p style="font-size: 14px; color: #666; margin-bottom: 20px;">Step 1: Verify your PHINMA Email</p>
            <form id="registerForm">
                <div class="reg-input-group">
                    <label>Student ID (e.g., 03-2223-012345)</label>
                    <input type="text" name="student_id" id="reg_student_id" required placeholder="Enter ID" maxlength="15">
                </div>
                <div class="reg-input-group">
                    <label>Full Name</label>
                    <input type="text" name="name" id="reg_name" required placeholder="Juan Dela Cruz">
                </div>
                <div class="reg-input-group">
                    <label>PHINMA Email</label>
                    <input type="email" name="email" id="reg_email" required placeholder="name.coc@phinmaed.com">
                </div>
                <div class="reg-input-group">
                    <label>Year Level</label>
                    <select name="year_level" id="reg_year_level" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-size: 14px; background: white; color: #333;">
                        <option value="" disabled selected>-- Select Year Level --</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <button type="submit" id="regSubmitBtn" class="btn-submit-reg">Get Verification Code 📧</button>
            </form>
        </div>
    </div>

    <script>
    // --- MATH CAPTCHA LOGIC ---
    let captchaA, captchaB;
    function generateCaptcha() {
        captchaA = Math.floor(Math.random() * 90) + 10; // 10-99 (2 digits)
        captchaB = Math.floor(Math.random() * 9) + 1;   // 1-9  (1 digit)
        document.getElementById('captchaNum1').textContent = captchaA;
        document.getElementById('captchaNum2').textContent = captchaB;
        document.getElementById('captchaAnswer').value = '';
    }
    generateCaptcha();
    document.getElementById('captchaRefresh').addEventListener('click', generateCaptcha);

    // --- LOGIN LOGIC ---
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate CAPTCHA first
        const userAnswer = parseInt(document.getElementById('captchaAnswer').value, 10);
        if (isNaN(userAnswer) || userAnswer !== (captchaA + captchaB)) {
            alert('Incorrect answer. Please solve the math problem.');
            generateCaptcha();
            return;
        }
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        const submitBtn = this.querySelector('.btn-signin');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Logging in...';
        submitBtn.disabled = true;
        
        fetch('api/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Welcome, ' + data.data.name + '!');
                window.location.href = data.data.redirect;
            } else {
                alert('Error: ' + data.message);
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                generateCaptcha();
            }
        })
        .catch(error => {
            alert('Login failed. Please try again.');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            generateCaptcha();
        });
    });

    // --- REGISTRATION MODAL LOGIC ---
    const modal = document.getElementById('registerModal');
    const openBtn = document.getElementById('openRegister');
    const closeBtn = document.getElementById('closeModal');

    openBtn.onclick = () => modal.style.display = 'block';
    closeBtn.onclick = () => modal.style.display = 'none';
    window.onclick = (e) => { if (e.target == modal) modal.style.display = 'none'; }

    // --- AUTO-FORMAT STUDENT ID ---
    const regStudentId = document.getElementById('reg_student_id');
    regStudentId.addEventListener('input', (e) => {
        let v = e.target.value.replace(/\D/g, ''); // Remove non-digits
        if (v.length > 2) v = v.slice(0,2) + '-' + v.slice(2);
        if (v.length > 7) v = v.slice(0,7) + '-' + v.slice(7);
        if (v.length > 15) v = v.slice(0, 15); // Max: XX-XXXX-XXXXXX
        e.target.value = v;
    });

    // --- REGISTRATION SUBMIT ---
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('regSubmitBtn');
        const email = document.getElementById('reg_email').value;
        const name = document.getElementById('reg_name').value;
        const studentId = document.getElementById('reg_student_id').value;
        const yearLevel = document.getElementById('reg_year_level').value;

        // Validate year level
        if (!yearLevel) {
            alert('Please select your year level.');
            return;
        }

        // Validate Student ID format (5 or 6 digits at end)
        const idPattern = /^\d{2}-\d{4}-\d{5,6}$/;
        if (!idPattern.test(studentId)) {
            alert('Invalid Student ID format. Use format: 03-2223-01234 or 03-2223-012345');
            return;
        }

        btn.textContent = 'Sending Code...';
        btn.disabled = true;

        const formData = new FormData(this);

        fetch('api/send-verification-code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                sessionStorage.setItem('reg_email', email);
                sessionStorage.setItem('reg_name', name);
                sessionStorage.setItem('reg_id', studentId);
                sessionStorage.setItem('reg_year_level', yearLevel);
                
                alert('Verification code sent! Please check your email.');
                window.location.href = 'verify-code.php';
            } else {
                alert(data.message);
                btn.textContent = 'Get Verification Code 📧';
                btn.disabled = false;
            }
        })
        .catch(error => {
            alert('Registration error. Please try again.');
            btn.textContent = 'Get Verification Code 📧';
            btn.disabled = false;
        });
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