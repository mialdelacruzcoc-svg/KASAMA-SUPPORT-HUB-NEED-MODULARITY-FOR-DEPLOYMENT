<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* I-siguro nga ang body dili blanko ang background */
        .forgot-body {
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: sans-serif;
        }
        
        .forgot-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .forgot-card h2 {
            color: #1a4a72;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .forgot-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .forgot-card .input-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .forgot-card label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .forgot-card input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .btn-reset {
            width: 100%;
            padding: 14px;
            background: #4a7c2c; /* Imong PHINMA Green */
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        .btn-reset:hover {
            background: #3d6824;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            color: #4a7c2c;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body class="forgot-body">
    <div class="forgot-card">
        <h2>Forgot Password?</h2>
        <p>I-enter ang imong email aron makadawat og reset link.</p>
        
        <form action="api/process-forgot-password.php" method="POST">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="name.coc@phinmaed.com">
            </div>
            <button type="submit" class="btn-reset">Send Reset Link 📧</button>
        </form>
        
        <a href="index.php" class="back-link">← Back to Login</a>
    </div>
</body>
</html>