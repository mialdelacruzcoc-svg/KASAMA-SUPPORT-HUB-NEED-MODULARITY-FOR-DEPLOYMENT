<?php
require_once 'api/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_role = $_SESSION['role'];
$user_name = $_SESSION['name'];

// Get initials for avatar
$words = explode(" ", $user_name);
$initials = "";
foreach ($words as $w) { if(!empty($w)) $initials .= strtoupper($w[0]); }
$display_initials = substr($initials, 0, 2);

// Get all public concerns
$query = "SELECT c.*, u.name as student_name,
          (SELECT COUNT(*) FROM concern_responses WHERE tracking_id = c.tracking_id) as response_count
          FROM concerns c 
          LEFT JOIN users u ON c.student_id = u.student_id 
          WHERE c.is_public = 1 
          ORDER BY c.created_at DESC";
$result = mysqli_query($conn, $query);

// Get categories for filter
$categories_query = "SELECT DISTINCT category FROM concerns WHERE is_public = 1 ORDER BY category";
$categories_result = mysqli_query($conn, $categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Existing Concerns - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .concerns-container { max-width: 1200px; margin: 0 auto; }
        
        /* Header Section */
        .page-header { margin-bottom: 30px; }
        .page-header h1 { margin: 0 0 10px; }
        .page-header p { color: #666; margin: 0; }
        
        /* Search & Filter */
        .filter-section { display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; align-items: center; }
        .search-box { flex: 1; min-width: 250px; position: relative; }
        .search-box input { width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .search-box::before { content: "🔍"; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); }
        .filter-select { padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; min-width: 150px; }
        
        /* Concerns Grid */
        .concerns-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }
        
        /* Concern Card */
        .concern-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
        .concern-card:hover { transform: translateY(-3px); box-shadow: 0 5px 20px rgba(0,0,0,0.12); }
        
        .card-header { padding: 20px 20px 15px; border-bottom: 1px solid #f0f0f0; }
        .card-category { display: inline-block; background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: 600; margin-bottom: 10px; }
        .card-title { font-size: 16px; font-weight: 600; color: #333; margin: 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        
        .card-body { padding: 15px 20px; }
        .card-description { color: #666; font-size: 14px; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 15px; }
        
        .card-meta { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .meta-left { display: flex; align-items: center; gap: 8px; }
        .meta-avatar { width: 28px; height: 28px; border-radius: 50%; background: #4a7c2c; color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; }
        .meta-name { font-size: 13px; color: #888; }
        .meta-right { font-size: 12px; color: #999; }
        
        .card-footer { padding: 15px 20px; background: #f8f9fa; display: flex; justify-content: space-between; align-items: center; }
        .status-badge { padding: 5px 12px; border-radius: 15px; font-size: 11px; font-weight: 600; }
        .status-pending { background: #fff3e0; color: #ef6c00; }
        .status-in-progress { background: #e3f2fd; color: #1565c0; }
        .status-resolved { background: #e8f5e9; color: #2e7d32; }
        
        .response-count { display: flex; align-items: center; gap: 5px; font-size: 13px; color: #666; }
        
        .btn-view { padding: 8px 16px; background: #4a7c2c; color: white; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; text-decoration: none; }
        .btn-view:hover { background: #3d6624; }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 12px; }
        .empty-state .icon { font-size: 64px; margin-bottom: 15px; }
        .empty-state h3 { color: #333; margin-bottom: 10px; }
        .empty-state p { color: #888; }
        
        /* Info Banner */
        .info-banner { background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); padding: 20px 25px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; }
        .info-banner .icon { font-size: 32px; }
        .info-banner .text h3 { margin: 0 0 5px; color: #2e7d32; font-size: 16px; }
        .info-banner .text p { margin: 0; color: #555; font-size: 14px; }
        
        /* Back Button */
        .btn-back { background: white; border: 1px solid #ddd; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-back:hover { background: #f5f5f5; }

        @media (max-width: 768px) {
            .concerns-grid { grid-template-columns: 1fr; }
            .filter-section { flex-direction: column; }
            .search-box { width: 100%; }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-content">
            <div class="nav-left"><span class="nav-title">Kasama Support Hub</span></div>
            <div class="nav-right">
                <a href="api/logout.php" style="color:white; text-decoration:none; font-weight:bold;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <header class="dashboard-header">
            <div class="header-left">
                <img src="images/phinma-logo.png" alt="Logo" class="header-logo">
                <span class="header-title">Existing Concerns</span>
            </div>
            <div class="header-right">
                <?php if ($user_role === 'student'): ?>
                    <button class="btn-back" onclick="window.location.href='student-dashboard.php'">← Back to Dashboard</button>
                <?php else: ?>
                    <button class="btn-back" onclick="window.location.href='coach-dashboard.php'">← Back to Dashboard</button>
                <?php endif; ?>
                <?php include 'includes/notification-bell.php'; ?>
                <div class="user-profile">
                    <div class="user-avatar"><?php echo $display_initials; ?></div>
                    <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                </div>
            </div>
        </header>

        <main class="dashboard-main">
            <div class="concerns-container">
                <div class="page-header">
                    <h1>🌐 Existing Concerns</h1>
                    <p>Browse concerns shared by other students. You might find answers to similar issues!</p>
                </div>
                
                <!-- Info Banner -->
                <div class="info-banner">
                    <span class="icon">💡</span>
                    <div class="text">
                        <h3>Learn from Others</h3>
                        <p>These are concerns that students chose to share publicly. Names are hidden for privacy unless the student opted to show theirs.</p>
                    </div>
                </div>
                
                <!-- Search & Filter -->
                <div class="filter-section">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search concerns..." onkeyup="filterConcerns()">
                    </div>
                    <select class="filter-select" id="categoryFilter" onchange="filterConcerns()">
                        <option value="">All Categories</option>
                        <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>"><?php echo htmlspecialchars($cat['category']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select class="filter-select" id="statusFilter" onchange="filterConcerns()">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                </div>
                
                <!-- Concerns Grid -->
                <div class="concerns-grid" id="concernsGrid">
                    <?php if($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($concern = mysqli_fetch_assoc($result)): 
                            // Determine display name
                            $display_name = ($concern['is_anonymous'] == 1) ? "Anonymous Student" : $concern['student_name'];
                            $avatar_text = ($concern['is_anonymous'] == 1) ? "?" : strtoupper(substr($concern['student_name'], 0, 1));
                            
                            // Status class
                            $status_class = 'status-' . strtolower(str_replace(' ', '-', $concern['status']));
                            
                            // Time ago
                            $created = new DateTime($concern['created_at']);
                            $now = new DateTime();
                            $diff = $now->diff($created);
                            if ($diff->days > 30) {
                                $time_ago = date('M d, Y', strtotime($concern['created_at']));
                            } elseif ($diff->days > 0) {
                                $time_ago = $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
                            } elseif ($diff->h > 0) {
                                $time_ago = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
                            } else {
                                $time_ago = 'Just now';
                            }
                        ?>
                        <div class="concern-card" 
                             data-category="<?php echo htmlspecialchars($concern['category']); ?>"
                             data-status="<?php echo $concern['status']; ?>"
                             data-search="<?php echo htmlspecialchars(strtolower($concern['subject'] . ' ' . $concern['description'] . ' ' . $concern['category'])); ?>">
                            <div class="card-header">
                                <span class="card-category"><?php echo htmlspecialchars($concern['category']); ?></span>
                                <h3 class="card-title"><?php echo htmlspecialchars($concern['subject']); ?></h3>
                            </div>
                            <div class="card-body">
                                <p class="card-description"><?php echo htmlspecialchars($concern['description']); ?></p>
                                <div class="card-meta">
                                    <div class="meta-left">
                                        <div class="meta-avatar"><?php echo $avatar_text; ?></div>
                                        <span class="meta-name"><?php echo htmlspecialchars($display_name); ?></span>
                                    </div>
                                    <div class="meta-right"><?php echo $time_ago; ?></div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <span class="status-badge <?php echo $status_class; ?>"><?php echo $concern['status']; ?></span>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <span class="response-count">💬 <?php echo $concern['response_count']; ?> response<?php echo $concern['response_count'] != 1 ? 's' : ''; ?></span>
                                    <a href="view-public-concern.php?id=<?php echo $concern['tracking_id']; ?>" class="btn-view">View</a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state" style="grid-column: 1 / -1;">
                            <div class="icon">📭</div>
                            <h3>No Shared Concerns Yet</h3>
                            <p>When students choose to share their concerns publicly, they will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
    function filterConcerns() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const category = document.getElementById('categoryFilter').value;
        const status = document.getElementById('statusFilter').value;
        
        document.querySelectorAll('.concern-card').forEach(card => {
            const matchSearch = card.dataset.search.includes(search);
            const matchCategory = !category || card.dataset.category === category;
            const matchStatus = !status || card.dataset.status === status;
            
            if (matchSearch && matchCategory && matchStatus) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Check if any visible
        const visibleCards = document.querySelectorAll('.concern-card[style=""], .concern-card:not([style])');
        const grid = document.getElementById('concernsGrid');
        const existingEmpty = grid.querySelector('.empty-state');
        
        if (visibleCards.length === 0 && !existingEmpty) {
            // All filtered out - could add "no results" message
        }
    }
    </script>
</body>
</html>