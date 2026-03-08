<?php
require_once 'api/config.php';

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coach') { 
    header('Location: index.php'); 
    exit; 
}

$faqs = mysqli_query($conn, "SELECT * FROM faqs ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage FAQ - Coach Hannah</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-header {
            background: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        .header-left { display: flex; align-items: center; gap: 10px; }
        .header-logo { height: 40px; }
        .header-title { font-weight: bold; color: #333; font-size: 1.2rem; }
        .btn-back-header {
            text-decoration: none;
            color: #666;
            font-weight: 600;
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .admin-card { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .faq-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .faq-table th, .faq-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .btn-delete { background: #d93025; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #444; }
    </style>
</head>
<body style="background:#f4f7f6; margin: 0; padding: 0;">

    <header class="dashboard-header">
        <div class="header-left">
            <img src="images/phinma-logo.png" alt="Logo" class="header-logo">
            <span class="header-title">FAQ Control Center</span>
        </div>
        <div class="header-right">
            <a href="coach-dashboard.php" class="btn-back-header">← Back to Dashboard</a>
        </div>
    </header>

    <div style="padding: 0 20px;">
        <div class="admin-card">
            <h2>➕ Add New FAQ</h2>
            <form id="addFaqForm">
                <input type="hidden" name="action" value="add">
                <div class="input-group">
                    <label>Category</label>
                    <select name="category" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; margin-bottom:15px;">
                        <option value="Academic">Academic</option>
                        <option value="Enrollment">Enrollment</option>
                        <option value="Financial">Financial</option>
                        <option value="Personal Support">Personal Support</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Question</label>
                    <input type="text" name="question" required placeholder="Unsa ang pangutana?" style="width:100%; padding:12px; border-radius:8px; border:1px solid #ddd; margin-bottom:15px; box-sizing: border-box;">
                </div>
                <div class="input-group">
                    <label>Answer</label>
                    <textarea name="answer" required rows="4" placeholder="I-type ang tubag diri..." style="width:100%; padding:12px; border-radius:8px; border:1px solid #ddd; box-sizing: border-box;"></textarea>
                </div>
                <button type="submit" class="btn-signin" style="background:#4a7c2c; margin-top:15px; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%;">Publish FAQ</button>
            </form>
        </div>

        <div class="admin-card" style="overflow-x: auto;">
            <h2>📋 Current FAQs</h2>
            <table class="faq-table">
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($faqs) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($faqs)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['question']); ?></td>
                            <td><span style="font-size: 0.85rem; background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px;"><?php echo $row['category']; ?></span></td>
                            <td><button class="btn-delete" onclick="deleteFaq(<?php echo $row['id']; ?>)">Delete</button></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center; padding: 20px; color: #999;">Walay sulod ang FAQ list.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // ADD FAQ AJAX
        document.getElementById('addFaqForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const originalText = btn.textContent;
            btn.textContent = 'Publishing...';
            btn.disabled = true;

            const formData = new FormData(e.target);
            try {
                const res = await fetch('api/manage-faq.php', { method: 'POST', body: formData });
                const data = await res.json();
                if(data.success) { 
                    location.reload(); 
                } else { 
                    alert('Error: ' + data.message); 
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            } catch (err) {
                alert('Connection error.');
                btn.textContent = originalText;
                btn.disabled = false;
            }
        });

        // DELETE FAQ AJAX
        async function deleteFaq(id) {
            if(confirm('Sigurado ka i-delete kini? Kini mahanaw sab sa FAQ page sa mga estudyante.')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                try {
                    const res = await fetch('api/manage-faq.php', { method: 'POST', body: formData });
                    const data = await res.json();
                    if(data.success) { 
                        location.reload(); 
                    }
                } catch (err) {
                    alert('Failed to delete.');
                }
            }
        }
    </script>
</body>
</html>