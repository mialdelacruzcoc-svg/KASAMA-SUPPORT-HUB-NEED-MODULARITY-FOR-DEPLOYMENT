<?php
require_once 'api/config.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header('Location: index.php');
    exit;
}

$coach_name = $_SESSION['name'] ?? 'Coach';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Concerns - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .concerns-table tbody tr {
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .concerns-table tbody tr:hover {
            background: #f8fffe;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 25px;
            padding: 20px;
        }
        
        .pagination button {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .pagination button:hover:not(:disabled) {
            background: var(--primary-green);
            color: white;
            border-color: var(--primary-green);
        }
        
        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        /* Notification Bell Styles */
        .notif-wrapper {
            position: relative;
            display: inline-block;
        }
        .notif-bell {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            padding: 5px 10px;
            position: relative;
        }
        .notif-badge {
            position: absolute;
            top: -2px;
            right: 2px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            min-width: 18px;
            height: 18px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .notif-badge.hidden { display: none; }
        .notif-dropdown {
            display: none;
            position: absolute;
            top: 40px;
            right: 0;
            width: 340px;
            max-height: 400px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            z-index: 9999;
            overflow: hidden;
        }
        .notif-dropdown.show { display: block; }
        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }
        .notif-header h4 { margin: 0; font-size: 14px; color: #333; }
        .mark-read-btn {
            background: none;
            border: none;
            color: #4a7c2c;
            font-size: 12px;
            cursor: pointer;
        }
        .notif-list {
            max-height: 320px;
            overflow-y: auto;
        }
        .notif-item {
            display: flex;
            gap: 10px;
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }
        .notif-item:hover { background: #f5f5f5; }
        .notif-item.unread { background: #f0f7ec; }
        .notif-empty {
            padding: 30px;
            text-align: center;
            color: #888;
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-content">
            <div class="nav-left">
                <span class="hamburger">☰</span>
                <span class="nav-title">Kasama Support Hub</span>
            </div>
            <div class="nav-right" style="display: flex; align-items: center; gap: 15px;">
                <a href="api/logout.php" style="color: #ffffff; text-decoration: none; font-size: 14px; font-weight: bold;">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <header class="dashboard-header">
            <div class="header-left">
                <img src="images/phinma-logo.png" alt="Logo" class="header-logo">
                <span class="header-title">All Student Concerns</span>
            </div>
            <div class="header-right">
                <button class="btn-back" onclick="window.location.href='coach-dashboard.php'">← Back to Dashboard</button>
                
                <!-- NOTIFICATION BELL -->
                <div class="notif-wrapper">
                    <button class="notif-bell" id="notifBell" type="button">
                        🔔
                        <span class="notif-badge hidden" id="notifBadge">0</span>
                    </button>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <h4>🔔 Notifications</h4>
                            <button class="mark-read-btn" id="markAllBtn" type="button">Mark all read</button>
                        </div>
                        <div class="notif-list" id="notifList">
                            <div class="notif-empty">Loading...</div>
                        </div>
                    </div>
                </div>
                <!-- END NOTIFICATION BELL -->
                
                <?php include 'includes/profile-dropdown.php'; ?>
            </div>
        </header>

        <main class="dashboard-main">
            <h1 class="page-title">📋 All Concerns (<span id="concernCount">0</span>)</h1>

            <div class="table-controls">
                <div class="filter-group">
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                    
                    <select class="filter-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="academic">Academic</option>
                        <option value="personal">Personal</option>
                        <option value="financial">Financial</option>
                        <option value="mental-health">Mental Health</option>
                        <option value="facilities">Facilities</option>
                    </select>
                    
                    <select class="filter-select" id="sortBy">
    <option value="date-desc">Newest First</option>
    <option value="date-asc">Oldest First</option>
    <option value="urgency">By Urgency</option>
</select>

<select class="filter-select" id="yearLevelFilter">
    <option value="">All Year Levels</option>
    <option value="1st Year">1st Year</option>
    <option value="2nd Year">2nd Year</option>
    <option value="3rd Year">3rd Year</option>
    <option value="4th Year">4th Year</option>
</select>

<select class="filter-select" id="urgencyFilter">
    <option value="">All Urgency Levels</option>
    <option value="Low">Low</option>
    <option value="Medium">Medium</option>
    <option value="High">High</option>
</select>
                </div>
                
                <input type="text" class="search-input" id="searchInput" placeholder="🔍 Search by ID, name, or subject..." style="max-width: 400px;">
            </div>

            <div class="concerns-table">
                <table>
                    <thead>
                        <tr>
                            <th>Concern ID</th>
<th>Student Name</th>
<th>Student ID</th>
<th>Year Level</th>
<th>Subject</th>
<th>Category</th>
<th>Status</th>
<th>Date Submitted</th>
<th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody id="concernsTableBody">
                        <tr><td colspan="8" style="text-align:center; padding:30px;">Loading concerns...</td></tr>
                    </tbody>
                </table>
                
                <div id="noResults" class="no-results" style="display: none;">
                    <p>No concerns found matching your filters.</p>
                </div>
            </div>

            <div class="pagination" id="pagination">
                <button id="prevBtn" onclick="changePage(-1)">← Previous</button>
                <span id="pageInfo">Page 1 of 1</span>
                <button id="nextBtn" onclick="changePage(1)">Next →</button>
            </div>
        </main>
    </div>

    <script>
    // ============================================
    // NOTIFICATION SYSTEM
    // ============================================
    var notifOpen = false;

    document.getElementById('notifBell').onclick = function() {
        notifOpen = !notifOpen;
        var dropdown = document.getElementById('notifDropdown');
        
        if (notifOpen) {
            dropdown.classList.add('show');
            
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'api/get-notifications.php?limit=10', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        
                        if (data.success && data.data.length > 0) {
                            var html = '';
                            data.data.forEach(function(n) {
                                var bgColor = n.is_read == 0 ? '#f0f7ec' : '#fff';
                                html += '<div style="padding:12px 15px; border-bottom:1px solid #eee; cursor:pointer; background:' + bgColor + ';" onclick="openNotification(' + n.id + ',\'' + (n.url || '') + '\',' + n.is_read + ')">';
                                html += '<div style="display:flex; gap:10px;">';
                                html += '<span style="font-size:20px;">' + n.icon + '</span>';
                                html += '<div>';
                                html += '<div style="font-weight:600; font-size:13px;">' + n.title + '</div>';
                                html += '<div style="font-size:12px; color:#666;">' + n.message + '</div>';
                                html += '<div style="font-size:11px; color:#999; margin-top:4px;">' + n.time_ago + '</div>';
                                html += '</div></div></div>';
                            });
                            document.getElementById('notifList').innerHTML = html;
                        } else {
                            document.getElementById('notifList').innerHTML = '<div style="padding:30px; text-align:center; color:#888;">No notifications</div>';
                        }
                        
                        var badge = document.getElementById('notifBadge');
                        if (data.unread_count > 0) {
                            badge.textContent = data.unread_count;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    } catch(e) {
                        document.getElementById('notifList').innerHTML = '<div style="padding:30px; text-align:center; color:#888;">Error loading</div>';
                    }
                }
            };
            xhr.send();
        } else {
            dropdown.classList.remove('show');
        }
    };

    document.onclick = function(e) {
        if (!e.target.closest('.notif-wrapper')) {
            document.getElementById('notifDropdown').classList.remove('show');
            notifOpen = false;
        }
    };

    document.getElementById('markAllBtn').onclick = function(e) {
        e.stopPropagation();
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'api/mark-notification-read.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            document.getElementById('notifBadge').classList.add('hidden');
            document.getElementById('notifBell').click();
            document.getElementById('notifBell').click();
        };
        xhr.send('mark_all=true');
    };

    function openNotification(id, url, isRead) {
        if (isRead == 0) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'api/mark-notification-read.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('notification_id=' + id);
        }
        if (url) window.location.href = url;
    }

    (function() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'api/get-notifications.php?limit=1', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    var badge = document.getElementById('notifBadge');
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count;
                        badge.classList.remove('hidden');
                    }
                } catch(e) {}
            }
        };
        xhr.send();
    })();

    // ============================================
    // CONCERNS TABLE SYSTEM
    // ============================================
    let allConcerns = [];
    let currentPage = 1;
    const itemsPerPage = 10;
    let filteredConcerns = [];

    function viewConcern(trackingId) {
        window.location.href = 'concern-details.php?id=' + trackingId;
    }

    function renderTable() {
        const tbody = document.getElementById('concernsTableBody');
        const noResults = document.getElementById('noResults');
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageData = filteredConcerns.slice(start, end);

        if (pageData.length === 0) {
            tbody.innerHTML = '';
            noResults.style.display = 'block';
            return;
        }

        noResults.style.display = 'none';
        tbody.innerHTML = pageData.map(concern => {
            const statusStr = concern.status || 'Pending';
            const categoryStr = concern.category || 'Others';
            const statusClass = statusStr.toLowerCase().replace(/\s+/g, '-');
            const categoryClass = categoryStr.toLowerCase().replace(/\s+/g, '-');
            
            return `
    <tr onclick="viewConcern('${concern.tracking_id}')">
        <td class="concern-id">${concern.tracking_id}</td>
        <td>${concern.student_name || 'Anonymous'}</td> 
        <td>${concern.is_anonymous == 1 ? '---' : concern.student_id}</td>
        <td>${concern.year_level || '---'}</td>
        <td>${concern.subject}</td>
                    <td><span class="badge badge-${categoryClass}">${categoryStr}</span></td>
                    <td><span class="badge badge-${statusClass}">${statusStr}</span></td>
                    <td>${concern.created_at_formatted || concern.created_at}</td>
                    <td>${concern.updated_at_formatted || concern.updated_at || '-'}</td>
                </tr>
            `;
        }).join('');
    }

    function filterConcerns() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
const categoryFilter = document.getElementById('categoryFilter').value.toLowerCase();
const yearLevelFilter = document.getElementById('yearLevelFilter').value;
const urgencyFilter = document.getElementById('urgencyFilter').value;

filteredConcerns = allConcerns.filter(concern => {
    const matchSearch = 
        (concern.tracking_id || '').toLowerCase().includes(searchTerm) ||
        (concern.student_name || '').toLowerCase().includes(searchTerm) ||
        (concern.subject || '').toLowerCase().includes(searchTerm);
    
    const concernStatus = (concern.status || '').toLowerCase();
    const concernCategory = (concern.category || '').toLowerCase();
    
    const matchStatus = !statusFilter || concernStatus === statusFilter;
    const matchCategory = !categoryFilter || concernCategory === categoryFilter;
    const matchYearLevel = !yearLevelFilter || concern.year_level === yearLevelFilter;
    const matchUrgency = !urgencyFilter || (concern.urgency || '') === urgencyFilter;

    return matchSearch && matchStatus && matchCategory && matchYearLevel && matchUrgency;
});

        currentPage = 1;
        renderTable();
        updatePagination();
    }

    function updatePagination() {
        const totalPages = Math.ceil(filteredConcerns.length / itemsPerPage) || 1;
        document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages} (${filteredConcerns.length} concerns)`;
        document.getElementById('prevBtn').disabled = currentPage === 1;
        document.getElementById('nextBtn').disabled = currentPage === totalPages || filteredConcerns.length === 0;
    }

    function changePage(direction) {
        currentPage += direction;
        renderTable();
        updatePagination();
    }

    function loadConcerns() {
        fetch('api/get-concerns.php')
            .then(response => response.json())
            .then(data => {
                console.log('Concerns API response:', data);
                
                if (data.success) {
                    allConcerns = data.data.concerns || data.data || [];
                    filteredConcerns = [...allConcerns];
                    
                    document.getElementById('concernCount').textContent = allConcerns.length;
                    
                    renderTable();
                    updatePagination();
                } else {
                    console.error('API Error:', data.message);
                    document.getElementById('concernsTableBody').innerHTML = '<tr><td colspan="8" style="text-align:center; padding:30px; color:red;">Error: ' + data.message + '</td></tr>';
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                document.getElementById('concernsTableBody').innerHTML = '<tr><td colspan="8" style="text-align:center; padding:30px; color:red;">Failed to load concerns. Check console.</td></tr>';
            });
    }

    window.addEventListener('DOMContentLoaded', () => {
        loadConcerns();
        document.getElementById('searchInput').addEventListener('input', filterConcerns);
document.getElementById('statusFilter').addEventListener('change', filterConcerns);
document.getElementById('categoryFilter').addEventListener('change', filterConcerns);
document.getElementById('yearLevelFilter').addEventListener('change', filterConcerns);
document.getElementById('urgencyFilter').addEventListener('change', filterConcerns);
    });
    </script>
</body>
</html>