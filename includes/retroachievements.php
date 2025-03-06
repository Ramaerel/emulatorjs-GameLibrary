<?php
/**
 * RetroAchievements API integration
 * Updated according to the latest API documentation
 */

// Base API URL - This is the correct official endpoint
define('RA_API_BASE_URL', 'https://retroachievements.org/API/');

// Cache directory for RetroAchievements data
define('RA_CACHE_DIR', 'cache/retroachievements/');

// Cache directory for game icons
define('RA_ICONS_CACHE_DIR', 'img/cache/icons/');

// Cache directory for game screenshots
define('RA_SCREENSHOTS_CACHE_DIR', 'img/cache/screenshots/');

// Cache expiration time (in seconds)
define('RA_CACHE_EXPIRATION', 7 * 24 * 60 * 60); // 1 week

// Console ID mapping for RetroAchievements
$RA_CONSOLE_IDS = [
    'nes' => 7,
    'snes' => 3,
    'n64' => 2,
    'gb' => 4,
    'gbc' => 5,
    'gba' => 6,
    'nds' => 10,
    'vb' => 28,
    'segaMS' => 11,
    'segaMD' => 1,
    'segaGG' => 9,
    'psx' => 27,
    'pce' => 8, // TurboGrafx-16/PC Engine
    'segaSaturn' => 22,
    'segaCD' => 21,
    '32x' => 23,
    'atari2600' => 25,
    'atari7800' => 51
];

/**
 * Initialize RetroAchievements by creating necessary directories
 */
function initRetroAchievements() {
    // Create cache directories if they don't exist
    $directories = [
        RA_CACHE_DIR,
        RA_ICONS_CACHE_DIR,
        RA_SCREENSHOTS_CACHE_DIR
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

/**
 * Get RetroAchievements settings
 */
function getRetroAchievementsSettings() {
    $settingsPath = 'config/retroachievements.json';
    
    if (file_exists($settingsPath)) {
        return json_decode(file_get_contents($settingsPath), true);
    }
    
    return [
        'enabled' => false,
        'mode' => 'direct', // 'direct' or 'proxy'
        'username' => '',
        'api_key' => '',
        'proxy_url' => 'https://temporus.one/backend/raproxy.php',
        'override_local_images' => true // Whether RA images should override local images
    ];
}

/**
 * Save RetroAchievements settings
 */
function saveRetroAchievementsSettings($settings) {
    $settingsPath = 'config/retroachievements.json';
    
    // Create config directory if it doesn't exist
    if (!is_dir('config')) {
        mkdir('config', 0755, true);
    }
    
    return file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT));
}

/**
 * Make a RetroAchievements API request (direct method)
 * Updated according to the latest API documentation
 */
function raApiRequest($endpoint, $params = []) {
    $settings = getRetroAchievementsSettings();
    
    if (!$settings['enabled'] || $settings['mode'] !== 'direct' || empty($settings['api_key'])) {
        return false;
    }
    
    // Add authentication to params - only need API key according to docs
    $params['y'] = $settings['api_key'];
    
    // Build URL - API_EndpointName.php format with query params
    $url = RA_API_BASE_URL . 'API_' . $endpoint . '.php?' . http_build_query($params);
    
    // Make request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'RetroHub/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    // Check for valid response
    if ($httpCode !== 200 || empty($response)) {
        return false;
    }
    
    return json_decode($response, true);
}

/**
 * Make a RetroAchievements API request through proxy
 * Updated according to the latest API documentation
 */
function raProxyRequest($endpoint, $params = []) {
    $settings = getRetroAchievementsSettings();
    
    if (!$settings['enabled'] || $settings['mode'] !== 'proxy' || empty($settings['proxy_url'])) {
        return false;
    }
    
    // Build request data
    $data = [
        'endpoint' => $endpoint,
        'params' => $params
    ];
    
    // Make request to proxy
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $settings['proxy_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERAGENT, 'RetroHub/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    // Check for valid response
    if ($httpCode !== 200 || empty($response)) {
        return false;
    }
    
    return json_decode($response, true);
}

/**
 * Clean a game title for better searching
 */
function cleanGameTitle($title) {
    // Remove file extension
    $title = preg_replace('/\.(zip|nes|smc|rom|md|gb|gba|n64)$/i', '', $title);
    
    // Remove tags like (USA), [!], etc.
    $title = preg_replace('/\([^\)]+\)|\[[^\]]+\]/', '', $title);
    
    // Remove version numbers and other common patterns
    $title = preg_replace('/ v[\d\.]+| rev[\d\.]+| \d+in\d+| hack/i', '', $title);
    
    // Convert underscores to spaces
    $title = str_replace('_', ' ', $title);
    
    // Remove extra spaces
    $title = preg_replace('/\s+/', ' ', $title);
    
    return trim($title);
}

/**
 * Get game data from RetroAchievements
 */
function getGameData($gameTitle, $console) {
    global $RA_CONSOLE_IDS;
    
    // Skip if RetroAchievements is disabled or console not supported
    $settings = getRetroAchievementsSettings();
    if (!$settings['enabled'] || !isset($RA_CONSOLE_IDS[$console])) {
        return false;
    }
    
    // Clean the game title
    $cleanTitle = cleanGameTitle($gameTitle);
    
    // Generate cache key
    $cacheKey = md5($cleanTitle . '_' . $console);
    $cachePath = RA_CACHE_DIR . $cacheKey . '.json';
    
    // Check cache
    if (file_exists($cachePath) && (time() - filemtime($cachePath) < RA_CACHE_EXPIRATION)) {
        return json_decode(file_get_contents($cachePath), true);
    }
    
    // Prepare API request
    $consoleId = $RA_CONSOLE_IDS[$console];
    
    // Search for games by console and title
    $endpoint = 'GetGameList';
    $params = [
        'i' => $consoleId,
        'f' => $cleanTitle
    ];
    
    // Make API request
    $response = ($settings['mode'] === 'direct') 
        ? raApiRequest($endpoint, $params) 
        : raProxyRequest($endpoint, $params);
    
    if (!$response || empty($response)) {
        return false;
    }
    
    // Find the best match game
    $bestMatch = null;
    $bestScore = 0;
    
    foreach ($response as $game) {
        // Skip games that don't match the console or have no ID
        if (!isset($game['ID']) || !isset($game['ConsoleID']) || (int)$game['ConsoleID'] !== $consoleId) {
            continue;
        }
        
        // Calculate similarity
        $gameTitle = isset($game['Title']) ? cleanGameTitle($game['Title']) : '';
        $similarity = 0;
        
        // Exact match
        if (strtolower($gameTitle) === strtolower($cleanTitle)) {
            $similarity = 1;
        } else {
            // Use similar_text for a better match score
            similar_text(strtolower($gameTitle), strtolower($cleanTitle), $similarity);
            $similarity = $similarity / 100; // Convert to 0-1 scale
        }
        
        if ($similarity > $bestScore) {
            $bestScore = $similarity;
            $bestMatch = $game;
        }
    }
    
    // If no good match found, return the first result if available
    if (!$bestMatch && !empty($response) && is_array($response) && count($response) > 0) {
        $bestMatch = $response[0];
    }
    
    // If still no match, return false
    if (!$bestMatch) {
        return false;
    }
    
    // Get additional game details using the game ID
    if (isset($bestMatch['ID'])) {
        $gameId = $bestMatch['ID'];
        
        // Use extended game info endpoint
        $extendedEndpoint = 'GetGameExtended';
        $extendedParams = ['i' => $gameId];
        
        $additionalDetails = ($settings['mode'] === 'direct') 
            ? raApiRequest($extendedEndpoint, $extendedParams) 
            : raProxyRequest($extendedEndpoint, $extendedParams);
        
        if ($additionalDetails) {
            $bestMatch = array_merge($bestMatch, $additionalDetails);
        }
        
        // Fix image URLs if they are relative paths
        foreach (['ImageIcon', 'ImageTitle', 'ImageIngame', 'ImageBoxArt'] as $imageKey) {
            if (isset($bestMatch[$imageKey]) && !empty($bestMatch[$imageKey])) {
                if (strpos($bestMatch[$imageKey], 'http') !== 0) {
                    $bestMatch[$imageKey] = 'https://retroachievements.org' . $bestMatch[$imageKey];
                }
            }
        }
    }
    
    // Save to cache
    file_put_contents($cachePath, json_encode($bestMatch));
    
    return $bestMatch;
}

/**
 * Download an image from RetroAchievements
 */
function downloadRAImage($url, $cachePath) {
    if (empty($url)) {
        return false;
    }
    
    // Create directory if it doesn't exist
    $dir = dirname($cachePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Ensure URL starts with http
    if (strpos($url, 'http') !== 0) {
        $url = 'https://retroachievements.org' . $url;
    }
    
    // Check if the file already exists in cache
    if (file_exists($cachePath)) {
        return $cachePath;
    }
    
    // Download image with curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'RetroHub/1.0');
    
    $image = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    if ($httpCode !== 200 || empty($image)) {
        return false;
    }
    
    // Save to cache
    if (file_put_contents($cachePath, $image)) {
        return $cachePath;
    }
    
    return false;
}

/**
 * Get game icon from RetroAchievements
 */
function getGameIcon($gameTitle, $console) {
    // Get game data
    $gameData = getGameData($gameTitle, $console);
    
    if (!$gameData || empty($gameData['ImageIcon'])) {
        return false;
    }
    
    // Generate cache path
    $cacheKey = md5(cleanGameTitle($gameTitle) . '_' . $console);
    $cachePath = RA_ICONS_CACHE_DIR . $cacheKey . '.png';
    
    // Download icon
    return downloadRAImage($gameData['ImageIcon'], $cachePath);
}

/**
 * Get game screenshots from RetroAchievements
 */
function getGameScreenshots($gameTitle, $console) {
    // Get game data
    $gameData = getGameData($gameTitle, $console);
    
    if (!$gameData) {
        return false;
    }
    
    $result = [];
    
    // Generate cache paths
    $cacheKey = md5(cleanGameTitle($gameTitle) . '_' . $console);
    
    // Title screenshot
    if (!empty($gameData['ImageTitle'])) {
        $titlePath = RA_SCREENSHOTS_CACHE_DIR . $cacheKey . '_title.png';
        $downloaded = downloadRAImage($gameData['ImageTitle'], $titlePath);
        if ($downloaded) {
            $result['title'] = $downloaded;
        }
    }
    
    // Ingame screenshot
    if (!empty($gameData['ImageIngame'])) {
        $ingamePath = RA_SCREENSHOTS_CACHE_DIR . $cacheKey . '_ingame.png';
        $downloaded = downloadRAImage($gameData['ImageIngame'], $ingamePath);
        if ($downloaded) {
            $result['ingame'] = $downloaded;
        }
    }
    
    // Box art
    if (!empty($gameData['ImageBoxArt'])) {
        $boxPath = RA_SCREENSHOTS_CACHE_DIR . $cacheKey . '_boxart.png';
        $downloaded = downloadRAImage($gameData['ImageBoxArt'], $boxPath);
        if ($downloaded) {
            $result['boxart'] = $downloaded;
        }
    }
    
    return empty($result) ? false : $result;
}

/**
 * Get all available RetroAchievements game metadata for a game
 */
function getGameMetadata($gameTitle, $console) {
    // Get game data
    $gameData = getGameData($gameTitle, $console);
    
    if (!$gameData) {
        return false;
    }
    
    // Get icon and screenshots
    $icon = getGameIcon($gameTitle, $console);
    $screenshots = getGameScreenshots($gameTitle, $console);
    
    return [
        'title' => $gameData['Title'] ?? null,
        'developer' => $gameData['Developer'] ?? null,
        'publisher' => $gameData['Publisher'] ?? null,
        'genre' => $gameData['Genre'] ?? null,
        'released' => $gameData['Released'] ?? null,
        'icon' => $icon,
        'screenshot_title' => $screenshots && isset($screenshots['title']) ? $screenshots['title'] : null,
        'screenshot_ingame' => $screenshots && isset($screenshots['ingame']) ? $screenshots['ingame'] : null,
        'screenshot_boxart' => $screenshots && isset($screenshots['boxart']) ? $screenshots['boxart'] : null
    ];
}

// Initialize RetroAchievements
initRetroAchievements();