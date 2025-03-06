<?php
/**
 * Simple RetroAchievements Test Script
 * Test the RetroAchievements integration with the official API
 */

// Include the RetroAchievements integration
include 'includes/retroachievements.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to prettify JSON for display
function prettyJson($json) {
    if (is_string($json)) {
        $json = json_decode($json, true);
    }
    return '<pre>' . htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT)) . '</pre>';
}

// Get current settings
$settings = getRetroAchievementsSettings();

// Output page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroAchievements API Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        h2 {
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        img {
            max-width: 300px;
            border: 1px solid #ddd;
            margin: 10px 0;
        }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .note { background-color: #ffffcc; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>RetroAchievements API Test</h1>
    
    <div class="note">
        <p><strong>Note:</strong> This test script uses the official RetroAchievements API at <code>api.retroachievements.org</code> which should bypass Cloudflare protection.</p>
    </div>
    
    <div class="test-section">
        <h2>Current Configuration</h2>
        <p>Enabled: <?php echo $settings['enabled'] ? 'Yes' : 'No'; ?></p>
        <p>Mode: <?php echo $settings['mode']; ?></p>
        <p>Username: <?php echo htmlspecialchars($settings['username']); ?></p>
        <p>API Key: <?php echo empty($settings['api_key']) ? 'Not set' : 'Set (hidden)'; ?></p>
        <p>Proxy URL: <?php echo htmlspecialchars($settings['proxy_url']); ?></p>
        
        <form method="post">
            <h3>Update Settings</h3>
            <p>
                <label>
                    <input type="checkbox" name="ra_enabled" <?php echo $settings['enabled'] ? 'checked' : ''; ?>>
                    Enable RetroAchievements
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" name="ra_mode" value="direct" <?php echo $settings['mode'] === 'direct' ? 'checked' : ''; ?>>
                    Direct API Access
                </label>
                <br>
                <label>
                    <input type="radio" name="ra_mode" value="proxy" <?php echo $settings['mode'] === 'proxy' ? 'checked' : ''; ?>>
                    Proxy Server
                </label>
            </p>
            <p>
                <label>Username: <input type="text" name="ra_username" value="<?php echo htmlspecialchars($settings['username']); ?>"></label>
            </p>
            <p>
                <label>API Key: <input type="password" name="ra_api_key" value="<?php echo htmlspecialchars($settings['api_key']); ?>"></label>
            </p>
            <p>
                <label>Proxy URL: <input type="text" name="ra_proxy_url" value="<?php echo htmlspecialchars($settings['proxy_url']); ?>" size="40"></label>
            </p>
            <p>
                <button type="submit" name="save_settings">Save Settings</button>
            </p>
        </form>
        
        <?php
        // Handle settings update
        if (isset($_POST['save_settings'])) {
            $newSettings = [
                'enabled' => isset($_POST['ra_enabled']),
                'mode' => $_POST['ra_mode'],
                'username' => $_POST['ra_username'],
                'api_key' => $_POST['ra_api_key'],
                'proxy_url' => $_POST['ra_proxy_url'],
                'override_local_images' => $settings['override_local_images'] ?? true
            ];
            
            if (saveRetroAchievementsSettings($newSettings)) {
                echo '<p class="success">Settings saved successfully! Refresh the page to continue testing.</p>';
            } else {
                echo '<p class="error">Failed to save settings. Check file permissions.</p>';
            }
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>Test API Connection</h2>
        <form method="post">
            <button type="submit" name="test_api">Test API Connection</button>
        </form>
        
        <?php
        if (isset($_POST['test_api'])) {
            if (!$settings['enabled']) {
                echo '<p class="error">RetroAchievements integration is not enabled. Enable it in the settings above.</p>';
            } else {
                echo '<h3>Testing API Connection...</h3>';
                
                // Test with a simple console list API call
                $endpoint = 'GetConsoleIDs';  // Official endpoint for console list
                $params = [];
                
                $result = ($settings['mode'] === 'direct') 
                    ? raApiRequest($endpoint, $params) 
                    : raProxyRequest($endpoint, $params);
                
                if ($result) {
                    echo '<p class="success">API Connection successful!</p>';
                    echo '<p>Retrieved console data:</p>';
                    echo prettyJson($result);
                } else {
                    echo '<p class="error">API Connection failed. Check your settings and try again.</p>';
                    
                    // Try to show detailed error information
                    if ($settings['mode'] === 'direct') {
                        $url = RA_API_BASE_URL . $endpoint . '.php?' . http_build_query([
                            'z' => $settings['username'],
                            'y' => $settings['api_key']
                        ]);
                        
                        echo '<p>Attempted URL: ' . htmlspecialchars($url) . '</p>';
                        
                        // Try a manual curl request for debugging
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($curl, CURLOPT_USERAGENT, 'RetroHub/1.0');
                        curl_setopt($curl, CURLOPT_VERBOSE, true);
                        
                        $verboseLog = fopen('php://temp', 'w+');
                        curl_setopt($curl, CURLOPT_STDERR, $verboseLog);
                        
                        $response = curl_exec($curl);
                        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                        $error = curl_error($curl);
                        
                        rewind($verboseLog);
                        $verboseOutput = stream_get_contents($verboseLog);
                        
                        curl_close($curl);
                        
                        echo '<p>HTTP Status Code: ' . $httpCode . '</p>';
                        if ($error) {
                            echo '<p>Error: ' . htmlspecialchars($error) . '</p>';
                        }
                        
                        echo '<h4>Verbose Log:</h4>';
                        echo '<pre>' . htmlspecialchars($verboseOutput) . '</pre>';
                        
                        if (!empty($response)) {
                            echo '<h4>Response:</h4>';
                            echo '<pre>' . htmlspecialchars($response) . '</pre>';
                        }
                    } else {
                        // For proxy mode, show the proxy URL
                        echo '<p>Proxy URL: ' . htmlspecialchars($settings['proxy_url']) . '</p>';
                        echo '<p>Check that your proxy server is correctly configured.</p>';
                    }
                }
            }
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>Search for Game Metadata</h2>
        <form method="post">
            <p>
                <label>Game Title: 
                    <input type="text" name="game_title" value="<?php echo isset($_POST['game_title']) ? htmlspecialchars($_POST['game_title']) : 'Super Mario World'; ?>" size="40">
                </label>
            </p>
            <p>
                <label>Console: 
                    <select name="console">
                        <?php foreach ($RA_CONSOLE_IDS as $consoleKey => $consoleId): ?>
                            <option value="<?php echo $consoleKey; ?>" <?php echo (isset($_POST['console']) && $_POST['console'] === $consoleKey) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($consoleKey); ?> (ID: <?php echo $consoleId; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </p>
            <p>
                <button type="submit" name="search_game">Search Game</button>
            </p>
        </form>
        
        <?php
        if (isset($_POST['search_game'])) {
            if (!$settings['enabled']) {
                echo '<p class="error">RetroAchievements integration is not enabled. Enable it in the settings above.</p>';
            } else {
                $gameTitle = $_POST['game_title'];
                $console = $_POST['console'];
                
                echo '<h3>Searching for: ' . htmlspecialchars($gameTitle) . ' (' . htmlspecialchars($console) . ')</h3>';
                
                // Get game metadata
                $metadata = getGameMetadata($gameTitle, $console);
                
                if ($metadata) {
                    echo '<p class="success">Game metadata found!</p>';
                    
                    echo '<h4>Metadata:</h4>';
                    echo '<ul>';
                    if (isset($metadata['title'])) echo '<li>Title: ' . htmlspecialchars($metadata['title']) . '</li>';
                    if (isset($metadata['developer'])) echo '<li>Developer: ' . htmlspecialchars($metadata['developer']) . '</li>';
                    if (isset($metadata['publisher'])) echo '<li>Publisher: ' . htmlspecialchars($metadata['publisher']) . '</li>';
                    if (isset($metadata['genre'])) echo '<li>Genre: ' . htmlspecialchars($metadata['genre']) . '</li>';
                    if (isset($metadata['released'])) echo '<li>Released: ' . htmlspecialchars($metadata['released']) . '</li>';
                    echo '</ul>';
                    
                    echo '<h4>Images:</h4>';
                    echo '<div style="display: flex; flex-wrap: wrap; gap: 20px;">';
                    
                    if (isset($metadata['icon']) && $metadata['icon']) {
                        echo '<div>';
                        echo '<p>Icon:</p>';
                        echo '<img src="' . $metadata['icon'] . '" alt="Game Icon">';
                        echo '</div>';
                    }
                    
                    if (isset($metadata['screenshot_title']) && $metadata['screenshot_title']) {
                        echo '<div>';
                        echo '<p>Title Screenshot:</p>';
                        echo '<img src="' . $metadata['screenshot_title'] . '" alt="Title Screenshot">';
                        echo '</div>';
                    }
                    
                    if (isset($metadata['screenshot_ingame']) && $metadata['screenshot_ingame']) {
                        echo '<div>';
                        echo '<p>Ingame Screenshot:</p>';
                        echo '<img src="' . $metadata['screenshot_ingame'] . '" alt="Ingame Screenshot">';
                        echo '</div>';
                    }
                    
                    if (isset($metadata['screenshot_boxart']) && $metadata['screenshot_boxart']) {
                        echo '<div>';
                        echo '<p>Box Art:</p>';
                        echo '<img src="' . $metadata['screenshot_boxart'] . '" alt="Box Art">';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                    
                    echo '<h4>Raw Data:</h4>';
                    $gameData = getGameData($gameTitle, $console);
                    echo prettyJson($gameData);
                } else {
                    echo '<p class="error">No metadata found for this game.</p>';
                    
                    // Try to display more debugging information
                    echo '<h4>Debug Information:</h4>';
                    
                    echo '<p>Game title being searched: ' . htmlspecialchars($gameTitle) . '</p>';
                    echo '<p>Cleaned title: ' . htmlspecialchars(cleanGameTitle($gameTitle)) . '</p>';
                    
                    // Try a direct API call to show available games
                    $consoleId = $RA_CONSOLE_IDS[$console];
                    
                    echo '<p>Attempting direct search using ConsoleID: ' . $consoleId . '</p>';
                    
                    if ($settings['mode'] === 'direct') {
                        $endpoint = 'GetGameList';
                        $params = [
                            'i' => $consoleId,
                            'f' => cleanGameTitle($gameTitle),
                            'z' => $settings['username'],
                            'y' => $settings['api_key']
                        ];
                        
                        $url = RA_API_BASE_URL . $endpoint . '.php?' . http_build_query($params);
                        echo '<p>URL being requested: ' . htmlspecialchars($url) . '</p>';
                        
                        $response = @file_get_contents($url);
                        if ($response) {
                            echo '<p class="success">Direct API search returned data:</p>';
                            echo prettyJson($response);
                        } else {
                            echo '<p class="error">Direct API search failed. Error: ' . error_get_last()['message'] . '</p>';
                            
                            // Try curl as a fallback
                            $curl = curl_init();
                            curl_setopt($curl, CURLOPT_URL, $url);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($curl, CURLOPT_USERAGENT, 'RetroHub/1.0');
                            
                            $response = curl_exec($curl);
                            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                            curl_close($curl);
                            
                            if ($response) {
                                echo '<p class="success">Direct API search with curl returned data:</p>';
                                echo prettyJson($response);
                            } else {
                                echo '<p class="error">Direct API search with curl failed. HTTP Status: ' . $httpCode . '</p>';
                            }
                        }
                    } else {
                        echo '<p>Using proxy mode. Cannot show direct API call.</p>';
                    }
                }
            }
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>Clear Cache</h2>
        <form method="post">
            <button type="submit" name="clear_cache">Clear RetroAchievements Cache</button>
        </form>
        
        <?php
        if (isset($_POST['clear_cache'])) {
            $cacheDirs = [
                RA_CACHE_DIR,
                RA_ICONS_CACHE_DIR,
                RA_SCREENSHOTS_CACHE_DIR
            ];
            
            $totalFiles = 0;
            $deletedFiles = 0;
            
            foreach ($cacheDirs as $dir) {
                if (is_dir($dir)) {
                    $files = glob($dir . '*');
                    $totalFiles += count($files);
                    
                    foreach ($files as $file) {
                        if (is_file($file) && unlink($file)) {
                            $deletedFiles++;
                        }
                    }
                }
            }
            
            echo '<p>Deleted ' . $deletedFiles . ' out of ' . $totalFiles . ' cache files.</p>';
            echo '<p>Cache has been cleared. Try searching for a game again.</p>';
        }
        ?>
    </div>
</body>
</html>