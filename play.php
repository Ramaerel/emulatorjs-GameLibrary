<?php
session_start();
include 'functions.php';
include 'includes/retroachievements.php';

// Ensure a game has been specified
if (!isset($_GET['game'])) {
    header('Location: index.php');
    exit;
}

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

// Get RetroAchievements settings
$raSettings = getRetroAchievementsSettings();

// Get general settings
$generalSettingsPath = 'config/general.json';
$generalSettings = [];

if (file_exists($generalSettingsPath)) {
    $generalSettings = json_decode(file_get_contents($generalSettingsPath), true);
}

$siteName = $generalSettings['site_name'] ?? 'RetroHub';

// Get profile and game information
$currentProfile = getProfileById($_SESSION['current_profile']);
$allProfiles = getProfiles();
$gameFile = $_GET['game'];

// Find console based on file extension
$name = basename($gameFile);
$ext = explode(".", $name);
$ext = strtolower(end($ext));

// For zipfile
if ($ext == 'zip') {
    $zip = new ZipArchive;
    if ($zip->open("roms/".$name)) {
        $names = $zip->getNameIndex(0);
        $ext0 = explode(".", $names);
        $ext = strtolower(end($ext0));
    }
}

// Determine console type
$console = getConsoleByExtension($ext);
$consoleName = getConsoleFriendlyName($console);

// Get save states for this game and profile
$saveStates = getSaveStates($gameFile, $_SESSION['current_profile']);

// Get game metadata from RetroAchievements if enabled
$gameMetadata = null;
$gameScreenshots = [];

if ($raSettings['enabled']) {
    $gameName = pathinfo($gameFile, PATHINFO_FILENAME);
    $gameMetadata = getGameMetadata($gameName, $console);
    
    if ($gameMetadata) {
        if ($gameMetadata['screenshot_title']) {
            $gameScreenshots[] = $gameMetadata['screenshot_title'];
        }
        if ($gameMetadata['screenshot_ingame']) {
            $gameScreenshots[] = $gameMetadata['screenshot_ingame'];
        }
    }
}

// Parse settings
$settings = parse_ini_file("./settings.ini");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($name); ?> - <?php echo htmlspecialchars($siteName); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional styles specific to the game player */
        .game-container {
            position: relative;
            overflow: hidden;
            background-color: #000;
            border-radius: var(--border-radius);
        }
        
        #game {
            width: 100%;
            height: 100%;
            aspect-ratio: 16/9;
        }
        
        .game-details-section {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 2rem;
        }
        
        @media (max-width: 991px) {
            .game-details-section {
                grid-template-columns: 1fr;
            }
        }
        
        .game-info-panel {
            background-color: var(--secondary-bg);
            border-radius: var(--border-radius);
            padding: 1.5rem;
        }
        
        .game-info-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .game-info-icon {
            width: 48px;
            height: 48px;
            margin-right: 1rem;
            border-radius: 8px;
        }
        
        .game-info-title {
            flex: 1;
        }
        
        .game-info-title h2 {
            margin: 0 0 0.3rem 0;
        }
        
        .game-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        
        .game-info-table td {
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--nav-bg);
        }
        
        .game-info-table td:first-child {
            font-weight: 500;
            width: 120px;
            color: var(--accent-color);
        }
        
        .game-screenshots {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .game-screenshot {
            border-radius: var(--border-radius);
            overflow: hidden;
            cursor: pointer;
        }
        
        .game-screenshot img {
            width: 100%;
            height: auto;
            transition: transform 0.3s ease;
        }
        
        .game-screenshot:hover img {
            transform: scale(1.05);
        }
        
        .save-states {
            background-color: var(--secondary-bg);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            height: fit-content;
        }
        
        .save-states-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .save-states-title {
            font-size: 1.2rem;
            color: var(--text-primary);
        }
        
        .save-slots {
            display: grid;
            gap: 1rem;
        }
        
        .save-slot {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .save-slot:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .save-slot-preview {
            position: relative;
            width: 100%;
            aspect-ratio: 4/3;
            background-color: #000;
            overflow: hidden;
        }
        
        .save-slot-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .save-slot-empty {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            background-color: var(--primary-bg);
        }
        
        .save-slot-empty i {
            font-size: 2rem;
            color: var(--nav-bg);
            margin-bottom: 0.5rem;
        }
        
        .save-slot-info {
            padding: 0.8rem;
        }
        
        .save-slot-title {
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .save-slot-time {
            font-size: 0.8rem;
            color: var(--accent-color);
        }
        
        .save-slot-actions {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0.8rem;
            border-top: 1px solid var(--primary-bg);
        }
        
        .save-slot-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 0.3rem;
            border-radius: 4px;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        
        .save-slot-btn:hover {
            background-color: var(--nav-bg);
            color: var(--accent-color);
        }
        
        .empty-states {
            padding: 2rem;
            text-align: center;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
        }
        
        .empty-states i {
            font-size: 3rem;
            color: var(--nav-bg);
            margin-bottom: 1rem;
        }
        
        .empty-states h3 {
            margin-bottom: 0.5rem;
        }
        
        .empty-states p {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }
        
        /* Screenshot modal */
        .screenshot-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .screenshot-modal.active {
            opacity: 1;
            visibility: visible;
        }
        
        .screenshot-modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }
        
        .screenshot-modal-image {
            max-width: 90%;
            max-height: 90%;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">
                    <i class="fas fa-gamepad"></i>
                    <span><?php echo htmlspecialchars($siteName); ?></span>
                </a>
                
                <ul class="nav-menu">
                    <li><a href="index.php">Games</a></li>
                    <li><a href="upload.php">Upload ROMs</a></li>
                    <li><a href="bios.php">BIOS Files</a></li>
                    <li><a href="settings.php">Settings</a></li>
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
                <div>
                    <h1 class="page-title">
                        <?php if ($gameMetadata && isset($gameMetadata['title'])): ?>
                            <?php echo htmlspecialchars($gameMetadata['title']); ?>
                        <?php else: ?>
                            <?php echo htmlspecialchars($name); ?>
                        <?php endif; ?>
                    </h1>
                    <span class="game-console-badge"><?php echo $consoleName; ?></span>
                </div>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Back to Library
                </a>
            </div>
            
            <div class="game-container">
                <div id="game"></div>
            </div>
            
            <div class="game-details-section">
                <div class="game-info-panel">
                    <?php if ($gameMetadata && $gameMetadata['icon']): ?>
                    <div class="game-info-header">
                        <img src="<?php echo $gameMetadata['icon']; ?>" alt="Game Icon" class="game-info-icon">
                        <div class="game-info-title">
                            <h2><?php echo htmlspecialchars($gameMetadata['title'] ?? $name); ?></h2>
                            <span class="game-console"><?php echo $consoleName; ?></span>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="game-info-header">
                        <div class="game-info-title">
                            <h2><?php echo htmlspecialchars($name); ?></h2>
                            <span class="game-console"><?php echo $consoleName; ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($gameMetadata): ?>
                    <table class="game-info-table">
                        <?php if (isset($gameMetadata['developer'])): ?>
                        <tr>
                            <td>Developer</td>
                            <td><?php echo htmlspecialchars($gameMetadata['developer']); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($gameMetadata['publisher'])): ?>
                        <tr>
                            <td>Publisher</td>
                            <td><?php echo htmlspecialchars($gameMetadata['publisher']); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($gameMetadata['genre'])): ?>
                        <tr>
                            <td>Genre</td>
                            <td><?php echo htmlspecialchars($gameMetadata['genre']); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($gameMetadata['released'])): ?>
                        <tr>
                            <td>Released</td>
                            <td><?php echo htmlspecialchars($gameMetadata['released']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    <?php endif; ?>
                    
                    <div class="game-player-controls">
                        <button class="btn" id="save-state-btn">
                            <i class="fas fa-save"></i>
                            Save State
                        </button>
                        
                        <button class="btn btn-secondary" id="fullscreen-btn">
                            <i class="fas fa-expand"></i>
                            Fullscreen
                        </button>
                    </div>
                    
                    <?php if (!empty($gameScreenshots)): ?>
                    <div class="game-screenshots-section">
                        <h3>Screenshots</h3>
                        <div class="game-screenshots">
                            <?php foreach($gameScreenshots as $index => $screenshot): ?>
                            <div class="game-screenshot" data-index="<?php echo $index; ?>">
                                <img src="<?php echo $screenshot; ?>" alt="Game Screenshot">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="save-states">
                    <div class="save-states-header">
                        <h2 class="save-states-title">Save States</h2>
                    </div>
                    
                    <?php if(count($saveStates) > 0): ?>
                    <div class="save-slots">
                        <?php foreach($saveStates as $saveState): ?>
                        <div class="save-slot" data-slot="<?php echo $saveState['slot']; ?>">
                            <div class="save-slot-preview">
                                <?php if($saveState['screenshot']): ?>
                                <img src="<?php echo $saveState['screenshot']; ?>" alt="Save State <?php echo $saveState['slot']; ?>">
                                <?php else: ?>
                                <div class="save-slot-empty">
                                    <i class="fas fa-save"></i>
                                    <span>No Preview</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="save-slot-info">
                                <h3 class="save-slot-title">Save Slot <?php echo $saveState['slot']; ?></h3>
                                <span class="save-slot-time"><?php echo date('M j, Y g:i A', $saveState['timestamp']); ?></span>
                            </div>
                            <div class="save-slot-actions">
                                <button class="save-slot-btn load-state" data-slot="<?php echo $saveState['slot']; ?>">
                                    <i class="fas fa-play"></i>
                                    Load
                                </button>
                                <button class="save-slot-btn delete-state" data-slot="<?php echo $saveState['slot']; ?>">
                                    <i class="fas fa-trash"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-states">
                        <i class="fas fa-save"></i>
                        <h3>No Save States Yet</h3>
                        <p>Use the Save State button to save your progress.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Save State Modal -->
    <div class="modal-overlay" id="save-state-modal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Save Game State</h2>
                <button class="close-modal" id="close-save-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="save-state-form">
                    <div class="form-group">
                        <label for="save-slot" class="form-label">Select Save Slot</label>
                        <select id="save-slot" name="slot" class="form-input">
                            <?php for($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo $i; ?>">Slot <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <div class="save-slot-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>If a save already exists in this slot, it will be overwritten.</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancel-save">Cancel</button>
                <button class="btn" id="save-game-state">Save Game</button>
            </div>
        </div>
    </div>
    
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
    
    <!-- Screenshot Modal -->
    <div class="screenshot-modal" id="screenshot-modal">
        <button class="screenshot-modal-close" id="close-screenshot-modal">×</button>
        <img src="" alt="Game Screenshot" class="screenshot-modal-image" id="screenshot-modal-image">
    </div>
    
    <script type="text/javascript">
        // EmulatorJS setup
        EJS_player = '#game';
        EJS_core = '<?php echo $console; ?>';
        <?php
            if (file_exists("./bios/$console.zip")) {
                echo "EJS_biosUrl = './bios/$console.zip';";
            }
        ?>
        EJS_gameUrl = './roms/<?php echo $gameFile; ?>';
        EJS_pathtodata = 'https://cdn.emulatorjs.org/stable/data/';
        
        // Profile ID for save states
        const profileId = '<?php echo $_SESSION['current_profile']; ?>';
        const gameName = '<?php echo $gameFile; ?>';
        
        // Save state handling
        EJS_onSaveState = function(data) {
            const stateBlob = new Blob([data.state], { type: "application/octet-stream" });
            const screenshotBlob = new Blob([data.screenshot], { type: "image/png" });
            
            const slotNumber = document.getElementById('save-slot').value;
            
            const formData = new FormData();
            formData.append("profile_id", profileId);
            formData.append("game_name", gameName);
            formData.append("slot", slotNumber);
            formData.append("state", stateBlob, `${gameName}_${slotNumber}.state`);
            formData.append("screenshot", screenshotBlob, `${gameName}_${slotNumber}.png`);
            
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "save_state.php", true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Close modal and refresh page to show new save
                    document.getElementById('save-state-modal').classList.remove('active');
                    window.location.reload();
                } else {
                    alert("Error saving game state: " + xhr.responseText);
                }
            };
            xhr.send(formData);
        };
        
        // Load state handling (will be wired up in the UI)
        function loadState(slot) {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", `get_state.php?profile_id=${profileId}&game=${gameName}&slot=${slot}`, true);
            xhr.responseType = "arraybuffer";
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const loadedState = new Uint8Array(xhr.response);
                    EJS_emulator.gameManager.loadState(loadedState);
                } else {
                    alert("Error loading save state");
                }
            };
            
            xhr.onerror = function() {
                alert("Request failed");
            };
            
            xhr.send();
        }
    </script>
    <script src="https://cdn.emulatorjs.org/stable/data/loader.js"></script>
    <script src="js/profiles.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Save state modal
            const saveStateBtn = document.getElementById('save-state-btn');
            const saveStateModal = document.getElementById('save-state-modal');
            const closeSaveModal = document.getElementById('close-save-modal');
            const cancelSave = document.getElementById('cancel-save');
            const saveGameState = document.getElementById('save-game-state');
            
            if (saveStateBtn && saveStateModal) {
                // Open modal
                saveStateBtn.addEventListener('click', function() {
                    saveStateModal.classList.add('active');
                });
                
                // Close modal
                function closeSaveStateModal() {
                    saveStateModal.classList.remove('active');
                }
                
                if (closeSaveModal) closeSaveModal.addEventListener('click', closeSaveStateModal);
                if (cancelSave) cancelSave.addEventListener('click', closeSaveStateModal);
                
                // Save game state
                if (saveGameState) {
                    saveGameState.addEventListener('click', function() {
                        // The actual saving is handled by EJS_onSaveState
                        // This just triggers the emulator's save state function
                        if (typeof EJS_emulator !== 'undefined') {
                            try {
                                EJS_emulator.gameManager.saveState();
                            } catch (e) {
                                alert('Error triggering save state: ' + e.message);
                            }
                        } else {
                            alert('Emulator not ready. Please wait and try again.');
                        }
                    });
                }
            }
            
            // Load save state
            const loadStateButtons = document.querySelectorAll('.load-state');
            
            if (loadStateButtons.length > 0) {
                loadStateButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const slot = this.dataset.slot;
                        
                        if (typeof loadState === 'function') {
                            loadState(slot);
                        } else {
                            alert('Load state function not available');
                        }
                    });
                });
            }
            
            // Delete save state
            const deleteStateButtons = document.querySelectorAll('.delete-state');
            
            if (deleteStateButtons.length > 0) {
                deleteStateButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const slot = this.dataset.slot;
                        
                        if (confirm('Are you sure you want to delete this save state?')) {
                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', 'delete_state.php', true);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            xhr.onload = function() {
                                if (xhr.status === 200) {
                                    // Reload the page to update the save states list
                                    window.location.reload();
                                } else {
                                    alert('Error deleting save state: ' + xhr.responseText);
                                }
                            };
                            xhr.send(`profile_id=${profileId}&game=${gameName}&slot=${slot}`);
                        }
                    });
                });
            }
            
            // Fullscreen button
            const fullscreenBtn = document.getElementById('fullscreen-btn');
            
            if (fullscreenBtn) {
                fullscreenBtn.addEventListener('click', function() {
                    if (typeof EJS_emulator !== 'undefined') {
                        try {
                            EJS_emulator.setFullscreen(true);
                        } catch (e) {
                            alert('Error activating fullscreen: ' + e.message);
                        }
                    } else {
                        alert('Emulator not ready. Please wait and try again.');
                    }
                });
            }
            
            // Screenshots modal
            const screenshotItems = document.querySelectorAll('.game-screenshot');
            const screenshotModal = document.getElementById('screenshot-modal');
            const closeScreenshotModal = document.getElementById('close-screenshot-modal');
            const screenshotModalImage = document.getElementById('screenshot-modal-image');
            
            if (screenshotItems.length > 0 && screenshotModal) {
                screenshotItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const imgSrc = this.querySelector('img').src;
                        screenshotModalImage.src = imgSrc;
                        screenshotModal.classList.add('active');
                    });
                });
                
                // Close screenshot modal
                function closeScreenshotModalFn() {
                    screenshotModal.classList.remove('active');
                }
                
                if (closeScreenshotModal) {
                    closeScreenshotModal.addEventListener('click', closeScreenshotModalFn);
                }
                
                // Close modal when clicking outside the image
                screenshotModal.addEventListener('click', function(e) {
                    if (e.target === screenshotModal) {
                        closeScreenshotModalFn();
                    }
                });
            }
        });
    </script>
</body>
</html>