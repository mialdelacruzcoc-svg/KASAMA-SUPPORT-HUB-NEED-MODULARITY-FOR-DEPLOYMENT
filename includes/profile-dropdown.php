<!-- Profile Dropdown CSS -->
<style>
    .user-profile-wrapper {
        position: relative;
    }
    .user-profile-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 8px;
        border: none;
        background: none;
        transition: background 0.2s;
    }
    .user-profile-btn:hover {
        background: rgba(0,0,0,0.04);
    }
    .profile-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        min-width: 200px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        z-index: 9999;
        overflow: hidden;
        animation: dropdownFade 0.2s ease;
    }
    @keyframes dropdownFade {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .profile-dropdown.show {
        display: block;
    }
    .profile-dropdown-header {
        padding: 14px 16px;
        border-bottom: 1px solid #f0f0f0;
        background: #f8faf8;
    }
    .profile-dropdown-header .dropdown-name {
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }
    .profile-dropdown-header .dropdown-role {
        font-size: 12px;
        color: #888;
        margin-top: 2px;
    }
    .profile-dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        color: #333;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: background 0.15s;
        border: none;
        background: none;
        width: 100%;
        cursor: pointer;
        text-align: left;
    }
    .profile-dropdown-item:hover {
        background: #f0f7ec;
    }
    .profile-dropdown-item.logout {
        color: #dc2626;
        border-top: 1px solid #f0f0f0;
    }
    .profile-dropdown-item.logout:hover {
        background: #fef2f2;
    }
    .dropdown-caret {
        font-size: 10px;
        color: #888;
        margin-left: -4px;
        transition: transform 0.2s;
    }
    .user-profile-wrapper.open .dropdown-caret {
        transform: rotate(180deg);
    }
</style>

<!-- Profile Dropdown HTML -->
<?php
// Get coach display initials if not already defined
if (!isset($display_initials)) {
    $pdi_name = $_SESSION['name'] ?? 'CH';
    $pdi_words = explode(" ", $pdi_name);
    $pdi_initials = "";
    foreach ($pdi_words as $w) {
        if (!empty($w))
            $pdi_initials .= strtoupper($w[0]);
    }
    $display_initials = substr($pdi_initials, 0, 2);
}
$pdi_coach_name = $_SESSION['name'] ?? 'Coach';
?>
<div class="user-profile-wrapper" id="profileWrapper">
    <button class="user-profile-btn" id="profileToggle" type="button">
        <div class="user-avatar"><?php echo $display_initials; ?></div>
        <span class="user-name">Coach <?php echo htmlspecialchars($pdi_coach_name); ?></span>
        <span class="dropdown-caret">▼</span>
    </button>
    <div class="profile-dropdown" id="profileDropdown">
        <div class="profile-dropdown-header">
            <div class="dropdown-name">Coach <?php echo htmlspecialchars($pdi_coach_name); ?></div>
            <div class="dropdown-role">Guidance Coach</div>
        </div>
        <a href="coach-profile.php" class="profile-dropdown-item">👤 My Profile</a>
        <a href="api/logout.php" class="profile-dropdown-item logout">🚪 Logout</a>
    </div>
</div>

<!-- Profile Dropdown JS -->
<script>
(function() {
    var wrapper = document.getElementById('profileWrapper');
    var toggle = document.getElementById('profileToggle');
    var dropdown = document.getElementById('profileDropdown');

    if (toggle && dropdown && wrapper) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            wrapper.classList.toggle('open');
            dropdown.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#profileWrapper')) {
                wrapper.classList.remove('open');
                dropdown.classList.remove('show');
            }
        });
    }
})();
</script>
