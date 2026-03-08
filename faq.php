<?php
require_once 'api/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$student_name = $_SESSION['name'] ?? 'Student';
$display_initials = substr(strtoupper($student_name), 0, 2);

// Get all categories for filter
$categories_query = "SELECT DISTINCT category FROM faqs WHERE category IS NOT NULL AND category != '' ORDER BY category";
$categories_result = mysqli_query($conn, $categories_query);

// Fetch FAQs
$query = "SELECT * FROM faqs ORDER BY is_pinned DESC, view_count DESC, created_at DESC";
$faqs_result = mysqli_query($conn, $query);
$faqs_array = [];
while ($row = mysqli_fetch_assoc($faqs_result)) {
    $faqs_array[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ==================== FAQ PAGE STYLES ==================== */
        body {
            overflow-x: hidden;
            background: #f5f7fa;
        }
        
        .top-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 56px;
        }
        
        .dashboard-wrapper {
            padding-top: 56px;
        }
        
        .dashboard-header {
            background: white;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 56px;
            z-index: 100;
        }
        
        .faq-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px 16px;
        }
        
        .page-header {
            margin-bottom: 24px;
        }
        
        .page-header h1 {
            font-size: 24px;
            margin-bottom: 8px;
            color: #333;
        }
        
        .page-header p {
            color: #666;
            font-size: 14px;
        }
        
        /* Search Bar */
        .search-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .search-input-wrapper {
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 14px 20px 14px 48px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: border-color 0.2s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #4a7c2c;
        }
        
        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
        }
        
        .search-results-count {
            margin-top: 10px;
            font-size: 13px;
            color: #666;
        }
        
        /* Category Filter */
        .category-filter {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        
        .category-btn {
            padding: 8px 16px;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 20px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
            color: #333;
        }
        
        .category-btn:hover {
            border-color: #4a7c2c;
            color: #4a7c2c;
        }
        
        .category-btn.active {
            background: #4a7c2c;
            color: white;
            border-color: #4a7c2c;
        }
        
        /* FAQ Items */
        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .faq-item {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .faq-item.active {
            border-color: #4a7c2c;
            box-shadow: 0 4px 16px rgba(74, 124, 44, 0.15);
        }
        
        .faq-item.hidden {
            display: none;
        }
        
        .faq-question {
            width: 100%;
            padding: 18px 20px;
            text-align: left;
            background: none;
            border: none;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            color: #333;
        }
        
        .faq-question:hover {
            background: #f9fafb;
        }
        
        .faq-question-text {
            flex: 1;
        }
        
        .faq-meta {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .faq-category-tag {
            font-size: 11px;
            padding: 4px 10px;
            background: #e8f5e9;
            color: #4a7c2c;
            border-radius: 12px;
            font-weight: 600;
        }
        
        .faq-toggle {
            font-size: 18px;
            color: #4a7c2c;
            transition: transform 0.3s;
        }
        
        .faq-item.active .faq-toggle {
            transform: rotate(45deg);
        }
        
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
            padding: 0 20px;
            color: #555;
            font-size: 14px;
            line-height: 1.7;
            background: #f9fafb;
        }
        
        .faq-item.active .faq-answer {
            max-height: 500px;
            padding: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
            background: white;
            border-radius: 12px;
        }
        
        .empty-state-icon {
            font-size: 56px;
            margin-bottom: 16px;
        }
        
        /* Back Button */
        .btn-back {
            background: #f5f7fa;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #4a7c2c;
        }
        
        .btn-back:hover {
            background: #e8f5e9;
        }
        
        /* Mobile */
        @media screen and (max-width: 768px) {
            .faq-container {
                padding: 16px;
            }
            
            .page-header h1 {
                font-size: 20px;
            }
            
            .faq-question {
                padding: 16px;
                font-size: 14px;
            }
            
            .faq-meta {
                flex-direction: column;
                align-items: flex-end;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-content">
            <div class="nav-left"><span class="nav-title">Kasama Support Hub</span></div>
            <div class="nav-right">
                <a href="api/logout.php" style="color:white; text-decoration:none; font-weight:bold; font-size:13px;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <header class="dashboard-header">
            <div class="header-left">
                <img src="images/phinma-logo.png" alt="Logo" class="header-logo" style="width:36px; height:36px;">
                <span class="header-title" style="font-size:15px; font-weight:600;">Help Center</span>
            </div>
            <div class="header-right">
                <button class="btn-back" onclick="window.location.href='student-dashboard.php'">← Back</button>
            </div>
        </header>

        <main class="faq-container">
            <div class="page-header">
                <h1>❓ Frequently Asked Questions</h1>
                <p>Find answers to common questions. Search by keyword or filter by category.</p>
            </div>
            
            <!-- Search Section -->
            <div class="search-section">
                <div class="search-input-wrapper">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="faqSearch" class="search-input" placeholder="Search FAQs..." autocomplete="off">
                </div>
                <div class="search-results-count" id="resultsCount"></div>
                
                <!-- Category Filter -->
                <div class="category-filter">
                    <button class="category-btn active" data-category="all">All</button>
                    <?php
mysqli_data_seek($categories_result, 0);
while ($cat = mysqli_fetch_assoc($categories_result)):
?>
                    <button class="category-btn" data-category="<?php echo htmlspecialchars($cat['category']); ?>">
                        <?php echo htmlspecialchars($cat['category']); ?>
                    </button>
                    <?php
endwhile; ?>
                </div>
            </div>
            
            <!-- FAQ List -->
            <div class="faq-list" id="faqList">
                <?php if (count($faqs_array) > 0): ?>
                    <?php foreach ($faqs_array as $row): ?>
                        <div class="faq-item" data-id="<?php echo $row['id']; ?>" data-category="<?php echo htmlspecialchars($row['category']); ?>" data-question="<?php echo htmlspecialchars(strtolower($row['question'])); ?>" data-answer="<?php echo htmlspecialchars(strtolower($row['answer'])); ?>">
                            <button class="faq-question" onclick="toggleFaq(this, <?php echo $row['id']; ?>)">
                                <span class="faq-question-text"><?php echo htmlspecialchars($row['question']); ?></span>
                                <div class="faq-meta">
                                    <span class="faq-category-tag"><?php echo htmlspecialchars($row['category']); ?></span>
                                    <span class="faq-toggle">+</span>
                                </div>
                            </button>
                            <div class="faq-answer">
                                <?php echo nl2br(htmlspecialchars($row['answer'])); ?>
                            </div>
                        </div>
                    <?php
    endforeach; ?>
                <?php
else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <h3>No FAQs Available</h3>
                        <p>Check back later for answers to common questions.</p>
                    </div>
                <?php
endif; ?>
            </div>
            
            <!-- No Results State -->
            <div class="empty-state" id="noResults" style="display: none;">
                <div class="empty-state-icon">🔍</div>
                <h3>No Results Found</h3>
                <p>Try different keywords or select a different category.</p>
            </div>
        </main>
    </div>

    <script>
    // Store total FAQ count
    const totalFaqs = <?php echo count($faqs_array); ?>;
    let currentCategory = 'all';
    
    // Toggle FAQ accordion
    function toggleFaq(button, faqId) {
        const item = button.parentElement;
        const wasActive = item.classList.contains('active');
        
        // Close all others
        document.querySelectorAll('.faq-item').forEach(el => el.classList.remove('active'));
        
        // Toggle current
        if (!wasActive) {
            item.classList.add('active');
            
            // Track view (only when opening)
            trackFaqView(faqId);
        }
    }
    
    // Track FAQ view count (FR3 requirement)
    async function trackFaqView(faqId) {
        try {
            const formData = new FormData();
            formData.append('faq_id', faqId);
            await fetch('api/track-faq-view.php', { method: 'POST', body: formData });
        } catch (e) {
            console.log('View tracking failed');
        }
    }
    
    // Search functionality
    const searchInput = document.getElementById('faqSearch');
    const resultsCount = document.getElementById('resultsCount');
    const noResults = document.getElementById('noResults');
    const faqList = document.getElementById('faqList');
    
    searchInput.addEventListener('input', filterFaqs);
    
    // Category filter
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.dataset.category;
            filterFaqs();
        });
    });
    
    function filterFaqs() {
        const query = searchInput.value.toLowerCase().trim();
        const items = document.querySelectorAll('.faq-item');
        let visibleCount = 0;
        
        items.forEach(item => {
            const question = item.dataset.question || '';
            const answer = item.dataset.answer || '';
            const category = item.dataset.category || '';
            
            // Check category match
            const categoryMatch = (currentCategory === 'all' || category === currentCategory);
            
            // Check search match
            const searchMatch = (query === '' || question.includes(query) || answer.includes(query));
            
            if (categoryMatch && searchMatch) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
                item.classList.remove('active');
            }
        });
        
        // Update results count
        if (query || currentCategory !== 'all') {
            resultsCount.textContent = `Showing ${visibleCount} of ${totalFaqs} FAQs`;
        } else {
            resultsCount.textContent = '';
        }
        
        // Show/hide no results message
        if (visibleCount === 0 && totalFaqs > 0) {
            noResults.style.display = 'block';
            faqList.style.display = 'none';
        } else {
            noResults.style.display = 'none';
            faqList.style.display = 'flex';
        }
    }
    </script>
</body>
</html>