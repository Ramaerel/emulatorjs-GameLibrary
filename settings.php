<?php
session_start();
include 'functions.php';
include 'includes/retroachievements.php';

// Get current profile or set default
if (!isset($_SESSION['current_profile'])) {
    $profiles = getProfiles();
    if (count($profiles) > 0) {
        $_SESSION['current_profile'] = $profiles[0]['id'];
    } else {
        // Create default profile if none exists
        $defaultProfileId = createProfile("Player 1", "avatar1.png");
        $_SESSION['current_profile'] = $defaultProfileId;
    }
}

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ra_settings'])) {
        // Update RetroAchievements settings
        $raSettings = [
            'enabled' => isset($_POST['ra_enabled']) ? true : false,
            'override_local_images' => isset($_POST['ra_override_local']) ? true : false,
            'mode' => $_POST['ra_mode'],
            'username' => trim($_POST['ra_username']),
            'api_key' => trim($_POST['ra_api_key']),
            'proxy_url' => trim($_POST['ra_proxy_url'])
        ];
        
        if (saveRetroAchievementsSettings($raSettings)) {
            $message = 'RetroAchievements settings saved successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to save RetroAchievements settings.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['general_settings'])) {
        // Update general settings
        $generalSettings = [
            'site_name' => trim($_POST['site_name']),
            'theme' => $_POST['theme'],
            // Add more general settings as needed
        ];
        
        // Save general settings to a file
        if (file_put_contents('config/general.json', json_encode($generalSettings, JSON_PRETTY_PRINT))) {
            $message = 'General settings saved successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to save general settings.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['clear_cache'])) {
        // Clear RetroAchievements cache
        $cacheTypes = $_POST['cache_type'] ?? [];
        
        $cleared = false;
        
        if (in_array('all', $cacheTypes) || in_array('metadata', $cacheTypes)) {
            array_map('unlink', glob(RA_CACHE_DIR . '*.json'));
            $cleared = true;
        }
        
        if (in_array('all', $cacheTypes) || in_array('icons', $cacheTypes)) {
            array_map('unlink', glob(RA_ICONS_CACHE_DIR . '*.png'));
            $cleared = true;
        }
        
        if (in_array('all', $cacheTypes) || in_array('screenshots', $cacheTypes)) {
            array_map('unlink', glob(RA_SCREENSHOTS_CACHE_DIR . '*.png'));
            $cleared = true;
        }
        
        if ($cleared) {
            $message = 'Cache cleared successfully.';
            $messageType = 'success';
        } else {
            $message = 'No cache files were selected to clear.';
            $messageType = 'warning';
        }
    }
}

// Get current settings
$raSettings = getRetroAchievementsSettings();

// General settings
$generalSettingsPath = 'config/general.json';
$generalSettings = [];

if (file_exists($generalSettingsPath)) {
    $generalSettings = json_decode(file_get_contents($generalSettingsPath), true);
}

// Default values
$generalSettings['site_name'] = $generalSettings['site_name'] ?? 'RetroHub';
$generalSettings['theme'] = $generalSettings['theme'] ?? 'dark';

// Get current profile data
$currentProfile = getProfileById($_SESSION['current_profile']);
$allProfiles = getProfiles();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo htmlspecialchars($generalSettings['site_name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
        }
        
        .settings-nav {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .settings-nav-item {
            padding: 1rem;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .settings-nav-item:hover {
            background-color: var(--nav-bg);
        }
        
        .settings-nav-item.active {
            background-color: var(--nav-bg);
            border-left-color: var(--accent-color);
            color: var(--accent-color);
        }
        
        .settings-nav-item i {
            width: 24px;
            text-align: center;
            margin-right: 0.5rem;
        }
        
        .settings-panel {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            display: none;
        }
        
        .settings-panel.active {
            display: block;
        }
        
        .settings-panel h2 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--nav-bg);
        }
        
        .form-row {
            margin-bottom: 1.5rem;
        }
        
        .form-row.inline {
            display: flex;
            align-items: center;
        }
        
        .form-row.inline label {
            margin-bottom: 0;
            margin-right: 1rem;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .checkbox-label input {
            margin-right: 0.5rem;
        }
        
        .setting-description {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-top: 0.3rem;
        }
        
        .radio-options {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
        }
        
        .radio-option input {
            margin-right: 0.5rem;
        }
        
        .conditional-section {
            margin-top: 1rem;
            padding: 1rem;
            background-color: var(--primary-bg);
            border-radius: var(--border-radius);
            border-left: 3px solid var(--accent-color);
        }
        
        .cache-stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .cache-stat-item {
            background-color: var(--primary-bg);
            padding: 1rem;
            border-radius: var(--border-radius);
            text-align: center;
        }
        
        .cache-stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0.5rem 0;
            color: var(--accent-color);
        }
        
        .cache-stat-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .cache-options {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .cache-option {
            display: flex;
            align-items: center;
        }
        
        .cache-option input {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">
                    <i class="fas fa-gamepad"></i>
                    <span><?php echo htmlspecialchars($generalSettings['site_name']); ?></span>
                </a>
                
                <ul class="nav-menu">
                    <li><a href="index.php">Games</a></li>
                    <li><a href="upload.php">Upload ROMs</a></li>
                    <li><a href="bios.php">BIOS Files</a></li>
                    <li><a href="settings.php" class="active">Settings</a></li>
                </ul>
                
                <div class="profile-menu">
                    <div class="current-profile" id="profile-toggle">
                        <img src="img/avatars/<?php echo $currentProfile['avatar']; ?>" alt="Profile">
                        <span><?php echo $currentProfile['name']; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    
                    <div class="profile-dropdown" id="profile-dropdown">
                        <ul class="profile-list">
                            <?php foreach($allProfiles as $profile): ?>
                            <li class="profile-item <?php echo ($profile['id'] == $_SESSION['current_profile']) ? 'active' : ''; ?>" 
                                data-profile-id="<?php echo $profile['id']; ?>">
                                <img src="img/avatars/<?php echo $profile['avatar']; ?>" alt="<?php echo $profile['name']; ?>">
                                <span class="profile-name"><?php echo $profile['name']; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="profile-actions">
                            <button class="add-profile-btn" id="add-profile-btn">
                                <i class="fas fa-plus-circle"></i>
                                <span>Add New Profile</span>
                            </button>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </header>
    
    <main>
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Settings</h1>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-info-circle"></i>
                <span><?php echo $message; ?></span>
            </div>
            <?php endif; ?>
            
            <div class="settings-container">
                <div class="settings-nav">
                    <div class="settings-nav-item active" data-target="general">
                        <i class="fas fa-cog"></i> General
                    </div>
                    <div class="settings-nav-item" data-target="retroachievements">
                        <i class="fas fa-trophy"></i> RetroAchievements
                    </div>
                    <div class="settings-nav-item" data-target="cache">
                        <i class="fas fa-database"></i> Cache Management
                    </div>
                    <div class="settings-nav-item" data-target="profiles">
                        <i class="fas fa-users"></i> Profiles
                    </div>
                    <div class="settings-nav-item" data-target="about">
                        <i class="fas fa-info-circle"></i> About
                    </div>
                </div>
                
                <div class="settings-content">
                    <!-- General Settings -->
                    <div class="settings-panel active" id="general-panel">
                        <h2>General Settings</h2>
                        
                        <form method="post">
                            <div class="form-row">
                                <label for="site-name" class="form-label">Site Name</label>
                                <input type="text" id="site-name" name="site_name" class="form-input" 
                                       value="<?php echo htmlspecialchars($generalSettings['site_name']); ?>">
                                <div class="setting-description">Name displayed in the header and browser title.</div>
                            </div>
                            
                            <div class="form-row">
                                <label class="form-label">Theme</label>
                                <div class="radio-options">
                                    <label class="radio-option">
                                        <input type="radio" name="theme" value="dark" <?php echo $generalSettings['theme'] === 'dark' ? 'checked' : ''; ?>>
                                        Dark (Default)
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="theme" value="light" <?php echo $generalSettings['theme'] === 'light' ? 'checked' : ''; ?>>
                                        Light
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" name="general_settings" class="btn">Save Settings</button>
                        </form>
                    </div>
                    
                    <!-- RetroAchievements Settings -->
                    <div class="settings-panel" id="retroachievements-panel">
                        <h2>RetroAchievements Integration</h2>
                        
                        <form method="post">
                            <div class="form-row inline">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="ra_enabled" id="ra-enabled" <?php echo $raSettings['enabled'] ? 'checked' : ''; ?>>
                                    Enable RetroAchievements Integration
                                </label>
                            </div>
                            
                            <div class="setting-description">
                                RetroAchievements integration allows automatic fetching of game icons and screenshots to enhance your game library.
                                <a href="https://retroachievements.org" target="_blank">Learn more about RetroAchievements</a>
                            </div>
                            
                            <div class="form-row">
                                <label class="form-label">Integration Mode</label>
                                <div class="radio-options">
                                    <label class="radio-option">
                                        <input type="radio" name="ra_mode" value="direct" id="ra-mode-direct" <?php echo $raSettings['mode'] === 'direct' ? 'checked' : ''; ?>>
                                        Direct API Access (requires your own RetroAchievements account)
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="ra_mode" value="proxy" id="ra-mode-proxy" <?php echo $raSettings['mode'] === 'proxy' ? 'checked' : ''; ?>>
                                        Proxy Server (uses a shared server for API requests)
                                    </label>
                                </div>
                            </div>
                            
                            <div class="conditional-section" id="direct-settings" style="display: <?php echo $raSettings['mode'] === 'direct' ? 'block' : 'none'; ?>">
                                <div class="form-row">
                                    <label for="ra-username" class="form-label">RetroAchievements Username</label>
                                    <input type="text" id="ra-username" name="ra_username" class="form-input" value="<?php echo htmlspecialchars($raSettings['username']); ?>">
                                </div>
                                
                                <div class="form-row">
                                    <label for="ra-api-key" class="form-label">RetroAchievements API Key</label>
                                    <input type="password" id="ra-api-key" name="ra_api_key" class="form-input" value="<?php echo htmlspecialchars($raSettings['api_key']); ?>">
                                    <div class="setting-description">
                                        Your API key can be found in your <a href="https://retroachievements.org/controlpanel.php" target="_blank">RetroAchievements control panel</a>.
                                        This is kept private and only used for API requests.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="conditional-section" id="proxy-settings" style="display: <?php echo $raSettings['mode'] === 'proxy' ? 'block' : 'none'; ?>">
                                <div class="form-row">
                                    <label for="ra-proxy-url" class="form-label">Proxy Server URL</label>
                                    <input type="text" id="ra-proxy-url" name="ra_proxy_url" class="form-input" value="<?php echo htmlspecialchars($raSettings['proxy_url']); ?>">
                                    <div class="setting-description">
                                        The URL of your proxy server that handles RetroAchievements API requests.
                                        Leave as default if using the shared server.
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="ra_settings" class="btn">Save RetroAchievements Settings</button>
                        </form>
                    </div>
                    
                    <!-- Cache Management -->
                    <div class="settings-panel" id="cache-panel">
                        <h2>Cache Management</h2>
                        
                        <div class="cache-stats">
                            <div class="cache-stat-item">
                                <div class="cache-stat-value">
                                    <?php echo count(glob(RA_CACHE_DIR . '*.json')); ?>
                                </div>
                                <div class="cache-stat-label">Game Metadata Files</div>
                            </div>
                            
                            <div class="cache-stat-item">
                                <div class="cache-stat-value">
                                    <?php echo count(glob(RA_ICONS_CACHE_DIR . '*.png')); ?>
                                </div>
                                <div class="cache-stat-label">Game Icons</div>
                            </div>
                            
                            <div class="cache-stat-item">
                                <div class="cache-stat-value">
                                    <?php echo count(glob(RA_SCREENSHOTS_CACHE_DIR . '*.png')); ?>
                                </div>
                                <div class="cache-stat-label">Game Screenshots</div>
                            </div>
                            
                            <div class="cache-stat-item">
                                <div class="cache-stat-value">
                                    <?php 
                                        $totalSize = 0;
                                        foreach (glob(RA_CACHE_DIR . '*.json') as $file) {
                                            $totalSize += filesize($file);
                                        }
                                        foreach (glob(RA_ICONS_CACHE_DIR . '*.png') as $file) {
                                            $totalSize += filesize($file);
                                        }
                                        foreach (glob(RA_SCREENSHOTS_CACHE_DIR . '*.png') as $file) {
                                            $totalSize += filesize($file);
                                        }
                                        echo formatFileSize($totalSize);
                                    ?>
                                </div>
                                <div class="cache-stat-label">Total Cache Size</div>
                            </div>
                        </div>
                        
                        <form method="post">
                            <div class="form-row">
                                <label class="form-label">Clear Cache</label>
                                <div class="cache-options">
                                    <label class="cache-option">
                                        <input type="checkbox" name="cache_type[]" value="all">
                                        All Cache
                                    </label>
                                    <label class="cache-option">
                                        <input type="checkbox" name="cache_type[]" value="metadata">
                                        Game Metadata
                                    </label>
                                    <label class="cache-option">
                                        <input type="checkbox" name="cache_type[]" value="icons">
                                        Game Icons
                                    </label>
                                    <label class="cache-option">
                                        <input type="checkbox" name="cache_type[]" value="screenshots">
                                        Game Screenshots
                                    </label>
                                </div>
                                <div class="setting-description">
                                    Warning: Clearing the cache will remove all downloaded game data. It will be re-downloaded as needed.
                                </div>
                            </div>
                            
                            <button type="submit" name="clear_cache" class="btn btn-secondary">Clear Selected Cache</button>
                        </form>
                    </div>
                    
                    <!-- Profiles -->
                    <div class="settings-panel" id="profiles-panel">
                        <h2>Profile Management</h2>

                        <?php if (isset($_SESSION['profile_success'])): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <span><?php echo $_SESSION['profile_success']; ?></span>
                            </div>
                            <?php unset($_SESSION['profile_success']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['profile_error'])): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?php echo $_SESSION['profile_error']; ?></span>
                            </div>
                            <?php unset($_SESSION['profile_error']); ?>
                        <?php endif; ?>

                        <div class="profile-list-settings">
                            <?php foreach($allProfiles as $profile): ?>
                            <div class="profile-card">
                                <div class="profile-card-header">
                                    <img src="img/avatars/<?php echo $profile['avatar']; ?>" alt="<?php echo $profile['name']; ?>" class="profile-avatar">
                                    <div class="profile-info">
                                        <h3 class="profile-name"><?php echo $profile['name']; ?></h3>
                                        <div class="profile-created">Created: <?php echo date('M j, Y', strtotime($profile['created'])); ?></div>
                                    </div>
                                </div>
                                
                                <div class="profile-actions">
                                    <button class="btn btn-secondary edit-profile-btn" data-profile-id="<?php echo $profile['id']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    
                                    <?php if($profile['id'] !== $_SESSION['current_profile']): ?>
                                    <button class="btn btn-secondary delete-profile-btn" data-profile-id="<?php echo $profile['id']; ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="add-profile-section">
                            <button class="btn" id="settings-add-profile-btn">
                                <i class="fas fa-plus-circle"></i> Add New Profile
                            </button>
                        </div>
                    </div>
                    
                    <!-- About -->
                    <div class="settings-panel" id="about-panel">
                        <h2>About RetroHub</h2>
                        
                        <div class="about-content">
                            <div class="about-section">
                                <h3>RetroHub</h3>
                                <p>An enhanced game library extension for EmulatorJS with multi-profile support, BIOS management, and a modern user interface.</p>
                                <p>Version: 2.0</p>
                            </div>
                            
                            <div class="about-section">
                                <h3>Credits</h3>
                                <ul>
                                    <li>EmulatorJS: <a href="https://github.com/EmulatorJS/emulatorjs" target="_blank">https://github.com/EmulatorJS/emulatorjs</a></li>
                                    <li>RetroAchievements: <a href="https://retroachievements.org" target="_blank">https://retroachievements.org</a></li>
                                </ul>
                            </div>
                            
                            <div class="about-section">
                                <h3>License</h3>
                                <p>RetroHub is released under the GPL license, the same as the original EmulatorJS Game Library extension.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Add Profile Modal (same as in index.php) -->
    <div class="modal-overlay" id="profile-modal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Create New Profile</h2>
                <button class="close-modal" id="close-profile-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="profile-form" action="profile_action.php" method="post">
                    <div class="form-group">
                        <label for="profile-name" class="form-label">Profile Name</label>
                        <input type="text" id="profile-name" name="name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Select Avatar</label>
                        <div class="avatar-selector">
                            <?php for($i = 1; $i <= 8; $i++): ?>
                            <img src="img/avatars/avatar<?php echo $i; ?>.png" 
                                 class="avatar-option <?php echo ($i == 1) ? 'selected' : ''; ?>" 
                                 data-avatar="avatar<?php echo $i; ?>.png">
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" id="selected-avatar" name="avatar" value="avatar1.png">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancel-profile">Cancel</button>
                <button class="btn" id="save-profile">Create Profile</button>
            </div>
        </div>
    </div>
    
    <!-- Edit Profile Modal -->
    <div class="modal-overlay" id="edit-profile-modal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Edit Profile</h2>
                <button class="close-modal" id="close-edit-profile-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-profile-form" action="profile_action.php" method="post">
                    <input type="hidden" id="edit-profile-id" name="profile_id">
                    <input type="hidden" name="action" value="update">
                    
                    <div class="form-group">
                        <label for="edit-profile-name" class="form-label">Profile Name</label>
                        <input type="text" id="edit-profile-name" name="name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Select Avatar</label>
                        <div class="avatar-selector" id="edit-avatar-selector">
                            <?php for($i = 1; $i <= 8; $i++): ?>
                            <img src="img/avatars/avatar<?php echo $i; ?>.png" 
                                 class="avatar-option" 
                                 data-avatar="avatar<?php echo $i; ?>.png">
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" id="edit-selected-avatar" name="avatar">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancel-edit-profile">Cancel</button>
                <button class="btn" id="update-profile">Update Profile</button>
            </div>
        </div>
    </div>
    
    <script src="js/profiles.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Settings navigation
            const navItems = document.querySelectorAll('.settings-nav-item');
            const panels = document.querySelectorAll('.settings-panel');
            
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    const target = this.dataset.target;
                    
                    // Update active nav item
                    navItems.forEach(navItem => navItem.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show target panel
                    panels.forEach(panel => panel.classList.remove('active'));
                    document.getElementById(target + '-panel').classList.add('active');
                });
            });
            
            // RetroAchievements mode toggle
            const directModeRadio = document.getElementById('ra-mode-direct');
            const proxyModeRadio = document.getElementById('ra-mode-proxy');
            const directSettings = document.getElementById('direct-settings');
            const proxySettings = document.getElementById('proxy-settings');
            
            directModeRadio.addEventListener('change', function() {
                directSettings.style.display = this.checked ? 'block' : 'none';
                proxySettings.style.display = this.checked ? 'none' : 'block';
            });
            
            proxyModeRadio.addEventListener('change', function() {
                directSettings.style.display = this.checked ? 'none' : 'block';
                proxySettings.style.display = this.checked ? 'block' : 'none';
            });
            
            // Edit profile functionality
            const editProfileBtns = document.querySelectorAll('.edit-profile-btn');
            const editProfileModal = document.getElementById('edit-profile-modal');
            const closeEditProfileModal = document.getElementById('close-edit-profile-modal');
            const cancelEditProfile = document.getElementById('cancel-edit-profile');
            const updateProfile = document.getElementById('update-profile');
            const editProfileForm = document.getElementById('edit-profile-form');
            const editProfileId = document.getElementById('edit-profile-id');
            const editProfileName = document.getElementById('edit-profile-name');
            const editAvatarSelector = document.getElementById('edit-avatar-selector');
            const editSelectedAvatar = document.getElementById('edit-selected-avatar');
            
            editProfileBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const profileId = this.dataset.profileId;
                    
                    // Fetch profile data
                    fetch('profile_action.php?action=get&profile_id=' + profileId)
                        .then(response => response.json())
                        .then(profile => {
                            editProfileId.value = profile.id;
                            editProfileName.value = profile.name;
                            
                            // Set selected avatar
                            const avatarOptions = editAvatarSelector.querySelectorAll('.avatar-option');
                            avatarOptions.forEach(option => {
                                option.classList.remove('selected');
                                if (option.dataset.avatar === profile.avatar) {
                                    option.classList.add('selected');
                                    editSelectedAvatar.value = profile.avatar;
                                }
                            });
                            
                            // Show modal
                            editProfileModal.classList.add('active');
                        })
                        .catch(error => {
                            console.error('Error fetching profile:', error);
                        });
                });
            });
            
            // Edit profile avatar selection
            const editAvatarOptions = editAvatarSelector.querySelectorAll('.avatar-option');
            editAvatarOptions.forEach(option => {
                option.addEventListener('click', function() {
                    editAvatarOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    editSelectedAvatar.value = this.dataset.avatar;
                });
            });
            
            // Close edit profile modal
            function closeEditProfileModalFn() {
                editProfileModal.classList.remove('active');
            }
            
            if (closeEditProfileModal) closeEditProfileModal.addEventListener('click', closeEditProfileModalFn);
            if (cancelEditProfile) cancelEditProfile.addEventListener('click', closeEditProfileModalFn);
            
            // Update profile
            if (updateProfile) {
                updateProfile.addEventListener('click', function() {
                    editProfileForm.submit();
                });
            }
            
            // Delete profile functionality
            const deleteProfileBtns = document.querySelectorAll('.delete-profile-btn');
            
            deleteProfileBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const profileId = this.dataset.profileId;
                    
                    if (confirm('Are you sure you want to delete this profile? All save states will be lost.')) {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('profile_id', profileId);
                        
                        fetch('profile_action.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (response.ok) {
                                // Reload the page to reflect changes
                                window.location.reload();
                            } else {
                                alert('Failed to delete profile.');
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting profile:', error);
                        });
                    }
                });
            });
            
            // Settings Add Profile button
            const settingsAddProfileBtn = document.getElementById('settings-add-profile-btn');
            if (settingsAddProfileBtn) {
                settingsAddProfileBtn.addEventListener('click', function() {
                    document.getElementById('profile-modal').classList.add('active');
                });
            }
        });
    </script>
</body>
</html>