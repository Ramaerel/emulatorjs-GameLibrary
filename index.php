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

// Get RetroAchievements settings
$raSettings = getRetroAchievementsSettings();

// Get general settings
$generalSettingsPath = 'config/general.json';
$generalSettings = [];

if (file_exists($generalSettingsPath)) {
    $generalSettings = json_decode(file_get_contents($generalSettingsPath), true);
}

$siteName = $generalSettings['site_name'] ?? 'RetroHub';

// Get list of all ROMs
$games = [];
$files = scandir("./roms/");
foreach($files as $file) {
    if(!in_array($file, array('.', '..'))) {
        $name = pathinfo($file, PATHINFO_FILENAME);
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        // Determine console type based on extension
        $console = getConsoleByExtension($ext);
        
        // Default thumbnail path
        $thumbnail = null;
        
        // First check for a local thumbnail
        if (file_exists("./img/$file.png")) {
            $thumbnail = "img/$file.png";
        }
        
        // Check RetroAchievements for thumbnail if enabled and no local thumbnail
        $metadata = null;
        if ($raSettings['enabled'] && (!$thumbnail || $raSettings['override_local_images'])) {
            // Fetch game metadata (this will also cache the images)
            $metadata = getGameMetadata($name, $console);
            
            // Use screenshot as thumbnail if available
            if ($metadata) {
                if ($metadata['screenshot_title']) {
                    $thumbnail = $metadata['screenshot_title'];
                } elseif ($metadata['screenshot_ingame']) {
                    $thumbnail = $metadata['screenshot_ingame'];
                } elseif ($metadata['icon']) {
                    $thumbnail = $metadata['icon'];
                }
            }
        }
        
        // If still no thumbnail, use default placeholder
        if (!$thumbnail) {
            $thumbnail = "img/placeholder_" . $console . ".png";
            
            // If console-specific placeholder doesn't exist, use generic one
            if (!file_exists($thumbnail)) {
                $thumbnail = "img/placeholder_game.png";
            }
        }
        
        $games[] = [
            'file' => $file,
            'name' => $name,
            'console' => $console,
            'thumbnail' => $thumbnail,
            'play_url' => 'play.php?game=' . urlencode($file),
            'metadata' => $metadata
        ];
    }
}

// Sort games alphabetically
usort($games, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

// Get current profile data
$currentProfile = getProfileById($_SESSION['current_profile']);
$allProfiles = getProfiles();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?> - Game Library</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <li><a href="index.php" class="active">Games</a></li>
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
                <h1 class="page-title">Game Library</h1>
                
                <div class="library-actions">
                    <div class="search-bar">
                        <input type="text" id="game-search" placeholder="Search games..." class="search-input">
                        <button class="search-btn"><i class="fas fa-search"></i></button>
                    </div>
                    
                    <div class="view-options">
                        <button class="view-btn active" data-view="grid"><i class="fas fa-th"></i></button>
                        <button class="view-btn" data-view="list"><i class="fas fa-list"></i></button>
                    </div>
                </div>
            </div>
            
            <!-- Console Filter Tabs -->
            <div class="console-filters">
                <button class="console-filter active" data-console="all">All</button>
                <button class="console-filter" data-console="nes">NES</button>
                <button class="console-filter" data-console="snes">SNES</button>
                <button class="console-filter" data-console="n64">N64</button>
                <button class="console-filter" data-console="gb">Game Boy</button>
                <button class="console-filter" data-console="gba">GBA</button>
                <button class="console-filter" data-console="segaMD">Genesis/MD</button>
                <button class="console-filter" data-console="psx">PlayStation</button>
                <button class="console-filter" data-console="other">Other</button>
            </div>
            
            <div class="gallery" id="game-gallery">
                <?php foreach($games as $game): ?>
                <a href="<?php echo $game['play_url']; ?>" class="game-card" data-console="<?php echo $game['console']; ?>" data-name="<?php echo strtolower($game['name']); ?>">
                    <div class="game-image-container">
                        <img src="<?php echo $game['thumbnail']; ?>" alt="<?php echo $game['name']; ?>" class="game-image">
                        <?php if($game['metadata'] && isset($game['metadata']['released'])): ?>
                        <span class="game-year"><?php echo substr($game['metadata']['released'], 0, 4); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="game-info">
                        <h3 class="game-title"><?php echo $game['name']; ?></h3>
                        <div class="game-details">
                            <span class="game-console"><?php echo getConsoleFriendlyName($game['console']); ?></span>
                            <?php if($game['metadata'] && isset($game['metadata']['developer'])): ?>
                            <span class="game-developer"><?php echo $game['metadata']['developer']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
                
                <?php if(count($games) == 0): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>No games found</h3>
                    <p>Upload some ROMs to get started!</p>
                    <a href="upload.php" class="btn">Upload ROMs</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Add Profile Modal -->
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Game search functionality
            const searchInput = document.getElementById('game-search');
            const gameGallery = document.getElementById('game-gallery');
            const gameCards = document.querySelectorAll('.game-card');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                
                gameCards.forEach(card => {
                    const gameName = card.dataset.name;
                    
                    if (gameName.includes(searchTerm)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Check if no games are visible
                checkEmptyState();
            });
            
            // Console filter functionality
            const consoleFilters = document.querySelectorAll('.console-filter');
            
            consoleFilters.forEach(filter => {
                filter.addEventListener('click', function() {
                    // Update active filter
                    consoleFilters.forEach(f => f.classList.remove('active'));
                    this.classList.add('active');
                    
                    const consoleType = this.dataset.console;
                    
                    gameCards.forEach(card => {
                        if (consoleType === 'all' || card.dataset.console === consoleType ||
                            (consoleType === 'other' && !['nes', 'snes', 'n64', 'gb', 'gba', 'segaMD', 'psx'].includes(card.dataset.console))) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    
                    // Check if no games are visible
                    checkEmptyState();
                });
            });
            
            // View toggle functionality
            const viewButtons = document.querySelectorAll('.view-btn');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    viewButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    const viewType = this.dataset.view;
                    gameGallery.className = viewType === 'grid' ? 'gallery' : 'gallery list-view';
                });
            });
            
            // Function to check if no games are visible and show empty state
            function checkEmptyState() {
                let visibleGames = 0;
                
                gameCards.forEach(card => {
                    if (card.style.display !== 'none') {
                        visibleGames++;
                    }
                });
                
                // Create or remove empty state message
                let emptyState = gameGallery.querySelector('.empty-search-state');
                
                if (visibleGames === 0 && gameCards.length > 0) {
                    if (!emptyState) {
                        emptyState = document.createElement('div');
                        emptyState.className = 'empty-search-state';
                        emptyState.innerHTML = `
                            <i class="fas fa-search"></i>
                            <h3>No games found</h3>
                            <p>Try a different search term or filter</p>
                        `;
                        gameGallery.appendChild(emptyState);
                    }
                } else if (emptyState) {
                    emptyState.remove();
                }
            }
        });
    </script>
</body>
</html>