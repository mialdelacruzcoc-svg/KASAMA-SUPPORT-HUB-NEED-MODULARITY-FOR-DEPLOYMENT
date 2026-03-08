<?php
require_once 'api/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Security Check: Siguraduhon nga naka-login ang student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['name'];

// I-fetch ang email automatically gikan sa database base sa user_id
$email_query = "SELECT email FROM users WHERE id = '$user_id'";
$email_result = mysqli_query($conn, $email_query);
$student_data = mysqli_fetch_assoc($email_result);
$student_email = $student_data['email'] ?? 'No email found';

$words = explode(" ", $student_name);
$initials = "";
foreach ($words as $w) { if(!empty($w)) $initials .= strtoupper($w[0]); }
$display_initials = substr($initials, 0, 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Concern - Kasama Support Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* File Upload Styles - Matches your existing design */
        .file-upload-area {
            border: 2px dashed #ccc;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #fafafa;
        }
        .file-upload-area:hover {
            border-color: #4a7c2c;
            background: #f0f7ec;
        }
        .file-upload-area.dragover {
            border-color: #4a7c2c;
            background: #e8f5e9;
        }
        .upload-placeholder p {
            margin: 10px 0 5px 0;
            color: #555;
            font-weight: 500;
        }
        .upload-placeholder small {
            color: #888;
        }
        .file-preview-list {
            margin-top: 15px;
        }
        .file-preview-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 15px;
            background: #f5f5f5;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .file-preview-item .file-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .file-preview-item .file-name {
            font-weight: 500;
            color: #333;
        }
        .file-preview-item .file-size {
            color: #888;
            font-size: 12px;
        }
        .file-preview-item .remove-file {
            background: #ff5252;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .file-preview-item .remove-file:hover {
            background: #d32f2f;
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
            <div class="nav-right">
                <button class="nav-icon">🔔</button>
                <button class="btn-share">Share</button>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <header class="dashboard-header">
            <div class="header-left">
                <img src="images/phinma-logo.png" alt="Logo" class="header-logo">
                <span class="header-title">Submit a Concern</span>
            </div>
            <div class="header-right">
                <button class="btn-back" onclick="window.location.href='student-dashboard.php'">
                    ← Back to Dashboard
                </button>
                <div class="user-profile">
                    <div class="user-avatar" style="background:#4a7c2c; color:white; display:flex; align-items:center; justify-content:center; width:35px; height:35px; border-radius:50%; font-weight:bold;">
                        <?php echo substr($student_name, 0, 1); ?>
                    </div>
                    <span class="user-name"><?php echo $student_name; ?></span>
                    <span class="dropdown-arrow">▼</span>
                </div>
            </div>
        </header>

        <main class="dashboard-main">
            <div class="submit-concern-container">
                <div class="concern-form-section">
                    <h1 class="page-title">📝 Submit a Concern</h1>
                    <p class="page-subtitle">Tell us what's on your mind. We're here to help.</p>

                    <form id="concernForm" class="concern-form">
                        <div class="form-section">
                            <h3 class="section-title">Student Information</h3>
                            <div class="form-row">
                                <div class="input-group">
                                    <label>Your Name</label>
                                    <input type="text" value="<?php echo $student_name; ?>" readonly style="background-color: #f9f9f9;">
                                </div>
                                <div class="input-group">
                                    <label>Student ID</label>
                                    <input type="text" value="<?php echo $student_id; ?>" readonly style="background-color: #f9f9f9;">
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Email Address</label>
                                <input type="email" value="<?php echo $student_email; ?>" readonly style="background-color: #f9f9f9;">
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3 class="section-title">Concern Details</h3>
                            <div class="input-group">
                                <label>Category *</label>
                                <select name="category" required>
                                    <option value="">Select a category</option>
                                    <option value="Academic">📚 Academic</option>
                                    <option value="Personal">👤 Personal</option>
                                    <option value="Financial">💰 Financial</option>
                                    <option value="Mental Health">🧠 Mental Health</option>
                                    <option value="Others">📌 Other</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Subject *</label>
                                <input type="text" name="subject" placeholder="Brief summary" maxlength="100" required>
                                <span class="char-count">0/100</span>
                            </div>
                            <div class="input-group">
                                <label>Description *</label>
                                <textarea name="description" placeholder="Describe in detail..." rows="8" maxlength="1000" required></textarea>
                                <span class="char-count">0/1000</span>
                            </div>
                            <div class="input-group">
                                <label>Urgency Level *</label>
                                <div class="urgency-buttons">
                                    <label class="urgency-option"><input type="radio" name="urgency" value="Low" required><span class="urgency-badge low">🟢 Low</span></label>
                                    <label class="urgency-option"><input type="radio" name="urgency" value="Medium" required><span class="urgency-badge medium">🟡 Medium</span></label>
                                    <label class="urgency-option"><input type="radio" name="urgency" value="High" required><span class="urgency-badge high">🔴 High</span></label>
                                </div>
                            </div>
                            <div class="input-group">
                                <label class="checkbox-label"><input type="checkbox" name="anonymous" value="1"> Submit anonymously</label>
                            </div>
                        </div>

                        <div style="margin-top: 15px;">
    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
        <input type="checkbox" id="isPublic" name="is_public" value="1">
        <span>🌐 Share this concern publicly (others can learn from it)</span>
    </label>
    <p style="font-size: 12px; color: #888; margin-top: 5px; margin-left: 25px;">
        If checked, your concern will appear in "Existing Concerns" for other students to see. Your name will still be hidden if you chose anonymous.
    </p>
</div>

                        <!-- FILE ATTACHMENT SECTION -->
                        <div class="form-section">
                            <h3 class="section-title">📎 Attachments (Optional)</h3>
                            <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
                                You can attach supporting documents. Allowed: Images, PDF, Word, Excel (Max 5MB each)
                            </p>
                            
                            <div class="file-upload-area" id="fileUploadArea">
                                <input type="file" id="attachmentInput" multiple 
                                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx"
                                       style="display: none;">
                                <div class="upload-placeholder" onclick="document.getElementById('attachmentInput').click();">
                                    <span style="font-size: 40px;">📁</span>
                                    <p>Click to select files or drag & drop</p>
                                    <small>JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX (Max 5MB each)</small>
                                </div>
                            </div>
                            
                            <div id="filePreviewList" class="file-preview-list"></div>
                        </div>
                        <!-- END FILE ATTACHMENT SECTION -->

                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="window.location.href='student-dashboard.php'">Cancel</button>
                            <button type="submit" id="submitBtn" class="btn-submit">Submit Concern</button>
                        </div>
                    </form>
                </div>

                <div class="concern-help-section">
                    <div class="help-card">
                        <h3>💡 Before You Submit</h3>
                        <ul>
                            <li>Check the FAQ page!</li>
                            <li>Be specific.</li>
                            <li>Choose correct category.</li>
                        </ul>
                    </div>
                    <div class="help-card emergency">
                        <h3>🚨 Emergency?</h3>
                        <p>Contact:</p>
                        <div class="emergency-contact"><strong>Guidance:</strong> (088) 123-4568<br><strong>Crisis:</strong> 1553</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Character counter (your existing code)
        document.querySelectorAll('input[maxlength], textarea[maxlength]').forEach(field => {
            const counter = field.nextElementSibling;
            if (counter && counter.classList.contains('char-count')) {
                field.addEventListener('input', () => {
                    counter.textContent = `${field.value.length}/${field.maxLength}`;
                });
            }
        });

        // ============================================
        // FILE UPLOAD HANDLING
        // ============================================
        let selectedFiles = [];
        const maxFileSize = 5 * 1024 * 1024; // 5MB
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];

        const fileInput = document.getElementById('attachmentInput');
        const uploadArea = document.getElementById('fileUploadArea');
        const previewList = document.getElementById('filePreviewList');

        // File input change
        fileInput.addEventListener('change', function(e) {
            handleFiles(e.target.files);
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        function handleFiles(files) {
            for (let file of files) {
                // Check extension
                const ext = file.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(ext)) {
                    alert(`❌ "${file.name}" - File type not allowed`);
                    continue;
                }
                
                // Check size
                if (file.size > maxFileSize) {
                    alert(`❌ "${file.name}" - File too large (Max 5MB)`);
                    continue;
                }
                
                // Check duplicates
                if (selectedFiles.some(f => f.name === file.name)) {
                    alert(`⚠️ "${file.name}" - Already added`);
                    continue;
                }
                
                selectedFiles.push(file);
            }
            
            renderFileList();
        }

        function renderFileList() {
            previewList.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                const size = file.size >= 1048576 
                    ? (file.size / 1048576).toFixed(2) + ' MB' 
                    : (file.size / 1024).toFixed(2) + ' KB';
                
                const icons = {
                    'pdf': '📄', 'doc': '📝', 'docx': '📝',
                    'xls': '📊', 'xlsx': '📊',
                    'jpg': '🖼️', 'jpeg': '🖼️', 'png': '🖼️', 'gif': '🖼️'
                };
                const ext = file.name.split('.').pop().toLowerCase();
                const icon = icons[ext] || '📎';
                
                previewList.innerHTML += `
                    <div class="file-preview-item">
                        <div class="file-info">
                            <span style="font-size: 24px;">${icon}</span>
                            <div>
                                <div class="file-name">${file.name}</div>
                                <div class="file-size">${size}</div>
                            </div>
                        </div>
                        <button type="button" class="remove-file" onclick="removeFile(${index})">✕</button>
                    </div>
                `;
            });
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            renderFileList();
        }

        // ============================================
        // FORM SUBMISSION (Updated with file upload)
        // ============================================
        document.getElementById('concernForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = 'Submitting...';
            
            const formData = new FormData(this);
            
            try {
                // Step 1: Submit the concern first
                const response = await fetch('api/submit-concern.php', { 
                    method: 'POST', 
                    body: formData 
                });
                const rawText = await response.text();
                const result = JSON.parse(rawText);
                
                if (result.success) {
                    const trackingId = result.data.tracking_id;
                    const concernId = result.data.concern_id;
                    
                    // Step 2: Upload attachments if any
                    if (selectedFiles.length > 0) {
                        btn.textContent = 'Uploading files...';
                        
                        let uploadSuccess = 0;
                        let uploadFailed = 0;
                        
                        for (let file of selectedFiles) {
                            const fileFormData = new FormData();
                            fileFormData.append('attachment', file);
                            fileFormData.append('tracking_id', trackingId);
                            fileFormData.append('concern_id', concernId);
                            
                            try {
                                const uploadResponse = await fetch('api/upload-attachment.php', {
                                    method: 'POST',
                                    body: fileFormData
                                });
                                const uploadResult = await uploadResponse.json();
                                
                                if (uploadResult.success) {
                                    uploadSuccess++;
                                } else {
                                    uploadFailed++;
                                    console.error('Upload failed:', file.name, uploadResult.message);
                                }
                            } catch (err) {
                                uploadFailed++;
                                console.error('Upload error:', file.name, err);
                            }
                        }
                        
                        if (uploadFailed > 0) {
                            alert(`✅ Concern submitted!\n\nTracking ID: ${trackingId}\n\n📎 Files: ${uploadSuccess} uploaded, ${uploadFailed} failed`);
                        } else {
                            alert(`✅ Success!\n\nTracking ID: ${trackingId}\n📎 ${uploadSuccess} file(s) attached`);
                        }
                    } else {
                        alert(`✅ Success!\n\nTracking ID: ${trackingId}`);
                    }
                    
                    window.location.href = 'student-dashboard.php';
                    
                } else {
                    alert('❌ Error: ' + result.message);
                    btn.disabled = false;
                    btn.textContent = 'Submit Concern';
                }
            } catch (error) {
                alert('⚠️ Connection error.');
                btn.disabled = false;
                btn.textContent = 'Submit Concern';
            }
        });
    </script>
</body>
</html>