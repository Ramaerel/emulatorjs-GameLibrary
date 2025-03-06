<?php
session_start();
include 'functions.php';

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

// Process ROM file uploads
$uploadMessage = '';
$uploadStatus = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_type'])) {
    if ($_POST['upload_type'] == 'rom' && isset($_FILES['rom_files'])) {
        $uploadCount = 0;
        $errorCount = 0;
        
        // Create the roms directory if it doesn't exist
        if (!is_dir('roms')) {
            mkdir('roms', 0755, true);
        }
        
        foreach ($_FILES['rom_files']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['rom_files']['error'][$key] == UPLOAD_ERR_OK) {
                $name = basename($_FILES['rom_files']['name'][$key]);
                
                // Move the uploaded file
                if (move_uploaded_file($tmp_name, "roms/$name")) {
                    $uploadCount++;
                } else {
                    $errorCount++;
                }
            } else {
                $errorCount++;
            }
        }
        
        if ($uploadCount > 0) {
            $uploadMessage = "$uploadCount ROM file" . ($uploadCount != 1 ? "s" : "") . " uploaded successfully!";
            $uploadStatus = 'success';
        }
        
        if ($errorCount > 0) {
            $uploadMessage .= ($uploadMessage ? " However, " : "") . "$errorCount file" . ($errorCount != 1 ? "s" : "") . " failed to upload.";
            $uploadStatus = $uploadCount > 0 ? 'warning' : 'error';
        }
    }
}

// Get current profile data
$currentProfile = getProfileById($_SESSION['current_profile']);
$allProfiles = getProfiles();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroHub - Upload ROMs</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">
                    <i class="fas fa-gamepad"></i>
                    <span>RetroHub</span>
                </a>
                
                <ul class="nav-menu">
                    <li><a href="index.php">Games</a></li>
                    <li><a href="upload.php" class="active">Upload ROMs</a></li>
                    <li><a href="bios.php">BIOS Files</a></li>
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
                <h1 class="page-title">Upload ROMs</h1>
            </div>
            
            <?php if($uploadMessage): ?>
            <div class="alert alert-<?php echo $uploadStatus; ?>">
                <i class="fas fa-info-circle"></i>
                <span><?php echo $uploadMessage; ?></span>
            </div>
            <?php endif; ?>
            
            <div class="upload-section">
                <div class="upload-container">
                    <div>
                        <h2 class="upload-title">ROM Files</h2>
                        <p>Upload ROM files for various game consoles. Supported formats include .nes, .smc, .gba, .n64, and more.</p>
                        
                        <div class="upload-box" id="rom-upload-box">
                            <i class="fas fa-upload"></i>
                            <h3>Drag & Drop ROM Files Here</h3>
                            <p>or click to browse your files</p>
                            <form action="upload.php" method="post" enctype="multipart/form-data" id="rom-upload-form">
                                <input type="hidden" name="upload_type" value="rom">
                                <input type="file" name="rom_files[]" id="rom-files" class="upload-input" multiple accept=".nes,.smc,.sfc,.n64,.z64,.gb,.gbc,.gba,.psx,.md,.smd,.zip">
                            </form>
                        </div>
                        
                        <div class="supported-formats">
                            <h4>Supported Systems:</h4>
                            <div class="format-list">
                                <span class="format-tag">NES</span>
                                <span class="format-tag">SNES</span>
                                <span class="format-tag">N64</span>
                                <span class="format-tag">GameBoy</span>
                                <span class="format-tag">GBA</span>
                                <span class="format-tag">Genesis/MD</span>
                                <span class="format-tag">PSX</span>
                                <span class="format-tag">and more!</span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h2 class="upload-title">Recently Uploaded</h2>
                        <div class="upload-list" id="recent-uploads">
                            <?php
                            $recentUploads = [];
                            $files = glob('roms/*');
                            usort($files, function($a, $b) {
                                return filemtime($b) - filemtime($a);
                            });
                            
                            $files = array_slice($files, 0, 5);
                            
                            foreach ($files as $file) {
                                $filename = basename($file);
                                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                                $console = getConsoleByExtension($ext);
                                $uploadDate = filemtime($file);
                                
                                echo '<div class="upload-item">';
                                echo '<i class="fas fa-gamepad upload-item-icon"></i>';
                                echo '<div class="upload-item-info">';
                                echo '<span class="upload-item-name">' . $filename . '</span>';
                                echo '<span class="upload-item-meta">' . getConsoleFriendlyName($console) . ' • ' . date('M j, Y g:i A', $uploadDate) . '</span>';
                                echo '</div>';
                                echo '</div>';
                            }
                            
                            if (count($files) == 0) {
                                echo '<div class="empty-uploads">No ROMs uploaded yet</div>';
                            }
                            ?>
                        </div>
                        
                        <div class="upload-tips">
                            <h4><i class="fas fa-lightbulb"></i> Upload Tips</h4>
                            <ul>
                                <li>You can upload multiple files at once</li>
                                <li>ZIP files are supported and will be automatically detected</li>
                                <li>Maximum upload size: <?php echo ini_get('upload_max_filesize'); ?></li>
                                <li>After uploading, your games will appear in the Game Library</li>
                            </ul>
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
    
    <script src="js/profiles.js"></script>
    <script src="js/upload.js"></script>
</body>
</html>