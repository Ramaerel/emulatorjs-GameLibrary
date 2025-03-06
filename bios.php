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

// Process BIOS file uploads
$uploadMessage = '';
$uploadStatus = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_type'])) {
    if ($_POST['upload_type'] == 'bios' && isset($_FILES['bios_file'])) {
        if ($_FILES['bios_file']['error'] == UPLOAD_ERR_OK) {
            // Create the bios directory if it doesn't exist
            if (!is_dir('bios')) {
                mkdir('bios', 0755, true);
            }
            
            $tmp_name = $_FILES['bios_file']['tmp_name'];
            $name = basename($_FILES['bios_file']['name']);
            $console = isset($_POST['console_type']) ? $_POST['console_type'] : pathinfo($name, PATHINFO_FILENAME);
            
            // If console type is specified, rename the file
            if (isset($_POST['console_type']) && !empty($_POST['console_type'])) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $name = $console . '.' . $ext;
            }
            
            // Move the uploaded file
            if (move_uploaded_file($tmp_name, "bios/$name")) {
                $uploadMessage = "BIOS file uploaded successfully!";
                $uploadStatus = 'success';
            } else {
                $uploadMessage = "Error uploading BIOS file.";
                $uploadStatus = 'error';
            }
        } else {
            $uploadMessage = "Error uploading BIOS file: " . $_FILES['bios_file']['error'];
            $uploadStatus = 'error';
        }
    }
}

// Get current profile data
$currentProfile = getProfileById($_SESSION['current_profile']);
$allProfiles = getProfiles();

// Get list of BIOS files
$biosFiles = getBiosFiles();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroHub - BIOS Files</title>
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
                    <li><a href="upload.php">Upload ROMs</a></li>
                    <li><a href="bios.php" class="active">BIOS Files</a></li>
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
                <h1 class="page-title">BIOS Files</h1>
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
                        <h2 class="upload-title">Upload BIOS Files</h2>
                        <p>Some systems require BIOS files to function correctly. Upload your BIOS files here.</p>
                        
                        <div class="bios-upload-form">
                            <form action="bios.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="upload_type" value="bios">
                                
                                <div class="form-group">
                                    <label for="console-type" class="form-label">Console Type</label>
                                    <select id="console-type" name="console_type" class="form-input">
                                        <option value="">Auto-detect from filename</option>
                                        <option value="psx">PlayStation (PSX)</option>
                                        <option value="gba">Game Boy Advance</option>
                                        <option value="nds">Nintendo DS</option>
                                        <option value="segaMD">Sega Mega Drive / Genesis</option>
                                        <option value="segaCD">Sega CD</option>
                                        <option value="segaSaturn">Sega Saturn</option>
                                        <option value="pcengine">PC Engine / TurboGrafx-16</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="bios-file" class="form-label">BIOS File</label>
                                    <div class="file-input-wrapper">
                                        <input type="file" id="bios-file" name="bios_file" class="form-input file-input" required>
                                        <div class="file-input-button">
                                            <i class="fas fa-upload"></i>
                                            <span>Select BIOS File</span>
                                        </div>
                                        <span class="selected-file-name">No file selected</span>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn">Upload BIOS</button>
                            </form>
                        </div>
                        
                        <div class="bios-info">
                            <h3><i class="fas fa-info-circle"></i> About BIOS Files</h3>
                            <p>BIOS files are required for some systems to run correctly. They contain low-level system code that the emulator needs to properly emulate the original hardware.</p>
                            <p>For copyright reasons, BIOS files are not included with RetroHub and must be provided by you.</p>
                            <p>Once uploaded, BIOS files will be automatically detected and used by the emulator.</p>
                        </div>
                    </div>
                    
                    <div>
                        <h2 class="upload-title">Installed BIOS Files</h2>
                        
                        <div class="bios-list">
                            <div class="bios-list-header">
                                <span class="bios-list-console">Console</span>
                                <span class="bios-list-filename">Filename</span>
                                <span class="bios-list-size">Size</span>
                                <span class="bios-list-date">Uploaded</span>
                                <span class="bios-list-actions">Actions</span>
                            </div>
                            
                            <?php if (count($biosFiles) > 0): ?>
                                <?php foreach ($biosFiles as $bios): ?>
                                <div class="bios-item">
                                    <span class="bios-item-console"><?php echo $bios['friendly_name']; ?></span>
                                    <span class="bios-item-filename"><?php echo $bios['filename']; ?></span>
                                    <span class="bios-item-size"><?php echo $bios['formatted_size']; ?></span>
                                    <span class="bios-item-date"><?php echo date('M j, Y', $bios['upload_date']); ?></span>
                                    <div class="bios-item-actions">
                                        <a href="delete_bios.php?file=<?php echo urlencode($bios['filename']); ?>" class="bios-delete-btn" 
                                           onclick="return confirm('Are you sure you want to delete this BIOS file?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-bios">
                                    <p>No BIOS files installed yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="bios-required">
                            <h3>Required BIOS Files by System</h3>
                            <div class="bios-required-list">
                                <div class="bios-required-item">
                                    <div class="bios-required-console">PlayStation (PSX)</div>
                                    <div class="bios-required-files">scph5500.bin, scph5501.bin, scph5502.bin</div>
                                </div>
                                <div class="bios-required-item">
                                    <div class="bios-required-console">Game Boy Advance</div>
                                    <div class="bios-required-files">gba_bios.bin</div>
                                </div>
                                <div class="bios-required-item">
                                    <div class="bios-required-console">Nintendo DS</div>
                                    <div class="bios-required-files">bios7.bin, bios9.bin, firmware.bin</div>
                                </div>
                                <div class="bios-required-item">
                                    <div class="bios-required-console">Sega CD</div>
                                    <div class="bios-required-files">bios_CD_U.bin, bios_CD_J.bin, bios_CD_E.bin</div>
                                </div>
                                <!-- Add more systems as needed -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/profiles.js"></script>
    <script src="js/bios.js"></script>
</body>
</html></main>
    
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