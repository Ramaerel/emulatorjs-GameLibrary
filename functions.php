<?php
/**
 * Core functions for RetroHub
 */

// Make sure needed directories exist
function ensureDirectoriesExist() {
    $directories = [
        'roms', 'bios', 'img', 'img/avatars', 'saves', 'profiles'
    ];
    
    foreach($directories as $dir) {
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Initialize application on first run
function initializeApp() {
    ensureDirectoriesExist();
    
    // Copy default avatars if they don't exist
    $avatarSource = 'default_assets/avatars/';
    $avatarDest = 'img/avatars/';
    
    if (is_dir($avatarSource)) {
        $avatars = scandir($avatarSource);
        foreach($avatars as $avatar) {
            if(!in_array($avatar, array('.', '..')) && !file_exists($avatarDest . $avatar)) {
                copy($avatarSource . $avatar, $avatarDest . $avatar);
            }
        }
    }
    
    // Copy placeholder images if they don't exist
    $placeholderSource = 'default_assets/placeholders/';
    $placeholderDest = 'img/';
    
    if (is_dir($placeholderSource)) {
        $placeholders = scandir($placeholderSource);
        foreach($placeholders as $placeholder) {
            if(!in_array($placeholder, array('.', '..')) && !file_exists($placeholderDest . $placeholder)) {
                copy($placeholderSource . $placeholder, $placeholderDest . $placeholder);
            }
        }
    }
}

// Initialize if needed
initializeApp();

/**
 * Determine the console type based on file extension
 */
function getConsoleByExtension($ext) {
    // Nintendo
    $snes = ["smc", "sfc", "fig", "swc", "bs", "st"];
    $gba = ["gba"];
    $gb = ["gb", "gbc", "dmg"];
    $nes = ["fds", "nes", "unif", "unf"];
    $vb = ["vb", "vboy"];
    $nds = ["nds"];
    $n64 = ["n64", "z64", "v64", "u1", "ndd"];
    // Sega
    $sms = ["sms"];
    $smd = ["smd", "md"];
    $gg = ["gg"];
    // Sony
    $psx = ["pbp", "chd"];
    
    // For zip files, try to peek inside
    if ($ext == 'zip') {
        $zip = @new ZipArchive;
        if ($zip->open("roms/".$name) === TRUE) {
            $names = $zip->getNameIndex(0);
            $ext0 = explode(".", $names);
            $ext = strtolower(end($ext0));
            $zip->close();
        }
    }
    
    if (in_array($ext, $nes)) return 'nes';
    if (in_array($ext, $snes)) return 'snes';
    if (in_array($ext, $n64)) return 'n64';
    if (in_array($ext, $gb)) return 'gb';
    if (in_array($ext, $vb)) return 'vb';
    if (in_array($ext, $gba)) return 'gba';
    if (in_array($ext, $nds)) return 'nds';
    if (in_array($ext, $sms)) return 'segaMS';
    if (in_array($ext, $smd)) return 'segaMD';
    if (in_array($ext, $gg)) return 'segaGG';
    if (in_array($ext, $psx)) return 'psx';
    
    return 'unknown';
}

/**
 * Get friendly name for console
 */
function getConsoleFriendlyName($console) {
    $names = [
        'nes' => 'Nintendo NES',
        'snes' => 'Super Nintendo',
        'n64' => 'Nintendo 64',
        'gb' => 'Game Boy',
        'gba' => 'Game Boy Advance',
        'nds' => 'Nintendo DS',
        'vb' => 'Virtual Boy',
        'segaMS' => 'Sega Master System',
        'segaMD' => 'Sega Mega Drive / Genesis',
        'segaGG' => 'Sega Game Gear',
        'psx' => 'PlayStation'
    ];
    
    return isset($names[$console]) ? $names[$console] : 'Unknown System';
}

/**
 * Write INI file
 */
function write_ini_file($assoc_arr, $path, $has_sections=FALSE) { 
    $content = ""; 
    if ($has_sections) { 
        foreach ($assoc_arr as $key=>$elem) { 
            $content .= "[".$key."]\n"; 
            foreach ($elem as $key2=>$elem2) { 
                if(is_array($elem2)) 
                { 
                    for($i=0;$i<count($elem2);$i++) 
                    { 
                        $content .= $key2."[] = \"".$elem2[$i]."\"\n"; 
                    } 
                } 
                else if($elem2=="") $content .= $key2." = \n"; 
                else $content .= $key2." = \"".$elem2."\"\n"; 
            } 
        } 
    } 
    else { 
        foreach ($assoc_arr as $key=>$elem) { 
            if(is_array($elem)) 
            { 
                for($i=0;$i<count($elem);$i++) 
                { 
                    $content .= $key."[] = \"".$elem[$i]."\"\n"; 
                } 
            } 
            else if($elem=="") $content .= $key." = \n"; 
            else $content .= $key." = \"".$elem."\"\n"; 
        } 
    } 

    if (!$handle = fopen($path, 'w')) { 
        return false; 
    }

    $success = fwrite($handle, $content);
    fclose($handle); 

    return $success; 
}

/**
 * Profile Management Functions
 */

// Get all profiles
function getProfiles() {
    if (!is_dir('profiles')) {
        mkdir('profiles', 0755, true);
    }
    
    $profiles = [];
    $files = glob('profiles/*.json');
    
    foreach ($files as $file) {
        $profileData = json_decode(file_get_contents($file), true);
        if ($profileData) {
            $profiles[] = $profileData;
        }
    }
    
    return $profiles;
}

// Get profile by ID
function getProfileById($id) {
    $profileFile = "profiles/$id.json";
    
    if (file_exists($profileFile)) {
        return json_decode(file_get_contents($profileFile), true);
    }
    
    return null;
}

// Create a new profile
function createProfile($name, $avatar) {
    $id = uniqid();
    
    $profile = [
        'id' => $id,
        'name' => $name,
        'avatar' => $avatar,
        'created' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents("profiles/$id.json", json_encode($profile));
    
    return $id;
}

// Update profile
function updateProfile($id, $data) {
    $profile = getProfileById($id);
    
    if (!$profile) {
        return false;
    }
    
    // Update profile with new data
    $profile = array_merge($profile, $data);
    
    file_put_contents("profiles/$id.json", json_encode($profile));
    
    return true;
}

// Delete profile
function deleteProfile($id) {
    $profileFile = "profiles/$id.json";
    
    if (file_exists($profileFile)) {
        return unlink($profileFile);
    }
    
    return false;
}

/**
 * Save State Management
 */

// Get save states for a game and profile
function getSaveStates($gameFile, $profileId) {
    $saveDir = "saves/$profileId";
    
    if (!is_dir($saveDir)) {
        return [];
    }
    
    $saveStates = [];
    $game = pathinfo($gameFile, PATHINFO_FILENAME);
    $files = glob("$saveDir/{$game}_*.state");
    
    foreach ($files as $file) {
        $filename = basename($file);
        preg_match('/(.+)_(\d+)\.state$/', $filename, $matches);
        
        if (count($matches) === 3) {
            $slot = $matches[2];
            $timestamp = filemtime($file);
            
            $saveStates[] = [
                'slot' => $slot,
                'timestamp' => $timestamp,
                'formatted_time' => date('Y-m-d H:i:s', $timestamp),
                'screenshot' => file_exists("img/saves/{$profileId}/{$game}_{$slot}.png") 
                    ? "img/saves/{$profileId}/{$game}_{$slot}.png" 
                    : null
            ];
        }
    }
    
    // Sort by newest first
    usort($saveStates, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    return $saveStates;
}

// Save a game state for a specific profile
function saveGameState($gameFile, $profileId, $slot, $stateData, $screenshotData) {
    $saveDir = "saves/$profileId";
    $screenshotDir = "img/saves/$profileId";
    
    // Create directories if they don't exist
    if (!is_dir($saveDir)) {
        mkdir($saveDir, 0755, true);
    }
    
    if (!is_dir($screenshotDir)) {
        mkdir($screenshotDir, 0755, true);
    }
    
    $game = pathinfo($gameFile, PATHINFO_FILENAME);
    $stateFile = "$saveDir/{$game}_{$slot}.state";
    $screenshotFile = "$screenshotDir/{$game}_{$slot}.png";
    
    // Save state file
    file_put_contents($stateFile, $stateData);
    
    // Save screenshot
    if ($screenshotData) {
        file_put_contents($screenshotFile, $screenshotData);
    }
    
    return true;
}

/**
 * BIOS Management
 */

// Get all installed BIOS files
function getBiosFiles() {
    if (!is_dir('bios')) {
        mkdir('bios', 0755, true);
    }
    
    $biosFiles = [];
    $files = glob('bios/*.{zip,bin}', GLOB_BRACE);
    
    foreach ($files as $file) {
        $filename = basename($file);
        $filesize = filesize($file);
        $console = pathinfo($filename, PATHINFO_FILENAME);
        
        $biosFiles[] = [
            'filename' => $filename,
            'console' => $console,
            'friendly_name' => getConsoleFriendlyName($console),
            'size' => $filesize,
            'formatted_size' => formatFileSize($filesize),
            'upload_date' => filemtime($file),
            'formatted_date' => date('Y-m-d H:i:s', filemtime($file))
        ];
    }
    
    return $biosFiles;
}

// Helper to format file size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}