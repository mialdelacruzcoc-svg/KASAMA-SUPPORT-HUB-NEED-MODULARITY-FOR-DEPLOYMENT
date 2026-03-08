<?php
require_once 'api/config.php';

// 1. Siguraduhon nga naka-login gyud ang student
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error_msg = "";

// 2. Pag-handle sa Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {
    $full_name = sanitize_input($_POST['full_name']);
    
    // I-update ang name ug is_profile_completed base sa ID column nimo
    // Note: Gigamit nato ang 'id' diri base sa imong miaging error
    $sql = "UPDATE users SET name = '$full_name', is_profile_completed = 1 WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['name'] = $full_name; // I-update ang session para sa header initials
        header('Location: student-dashboard.php');
        exit;
    } else {
        $error_msg = "Error updating profile: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Your Profile - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .setup-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .setup-card h2 { color: #4a7c2c; margin-bottom: 10px; }
        .setup-card p { color: #666; font-size: 14px; margin-bottom: 30px; }
        .input-group { text-align: left; margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .input-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; box-sizing: border-box; }
        .btn-save { background: #4a7c2c; color: white; border: none; padding: 14px; width: 100%; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        .btn-save:hover { background: #3d6624; }
        .error { color: #d32f2f; font-size: 13px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="setup-card">
        <h2>Hapit na ta, Kasama! 🌿</h2>
        <p>Palihug isulat ang imong tibuok ngalan para makasugod na ta sa imong portal.</p>
        
        <?php if($error_msg): ?>
            <div class="error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="setup-profile.php" method="POST">
            <div class="input-group">
                <label for="full_name">Imong Tibuok Ngalan</label>
                <input type="text" id="full_name" name="full_name" placeholder="Juan Dela Cruz" required autofocus>
            </div>
            <button type="submit" class="btn-save">I-save ug Padayon</button>
        </form>
    </div>
</body>
</html>