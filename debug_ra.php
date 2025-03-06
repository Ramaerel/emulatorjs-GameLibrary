<?php
/**
 * RetroAchievements Debug Tool
 * This script tests the connection to RetroAchievements API and displays detailed results
 */

// Include necessary files
include 'includes/retroachievements.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to test proxy connection
function testProxyConnection($proxyUrl, $testEndpoint = 'API_GetGameList', $params = ['i' => 1, 'f' => 'mario']) {
    echo "<h3>Testing Proxy Connection</h3>";
    echo "<p>Proxy URL: " . htmlspecialchars($proxyUrl) . "</p>";
    
    try {
        // Build request data
        $data = [
            'endpoint' => $testEndpoint,
            'params' => $params
        ];
        
        // Make request to proxy
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $proxyUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_USERAGENT, 'RetroHub/1.0');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        
        // Create a stream for curl to write verbose information to
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($curl, CURLOPT_STDERR, $verbose);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        // Get verbose information
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        
        curl_close($curl);
        
        // Display results
        echo "<p>HTTP Status Code: " . $httpCode . "</p>";
        
        if ($error) {
            echo "<p>Error: " . htmlspecialchars($error) . "</p>";
        }
        
        echo "<h4>Curl Verbose Log:</h4>";
        echo "<pre>" . htmlspecialchars($verboseLog) . "</pre>";
        
        echo "<h4>Response:</h4>";
        if ($response === false) {
            echo "<p>No response received</p>";
            return false;
        } else {
            // Attempt to parse JSON
            $parsedResponse = json_decode($response, true);
            if ($parsedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
                echo "<p>Invalid JSON response. JSON error: " . json_last_error_msg() . "</p>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "...</pre>";
            } else {
                echo "<pre>" . htmlspecialchars(print_r($parsedResponse, true)) . "</pre>";
                return $parsedResponse;
            }
        }
    } catch (Exception $e) {
        echo "<p>Exception: " . $e->getMessage() . "</p>";
    }
    
    return false;
}

// Function to test direct connection
function testDirectConnection($username, $apiKey, $testEndpoint = 'API_GetGameList', $params = ['i' => 1, 'f' => 'mario']) {
    echo "<h3>Testing Direct Connection</h3>";
    echo "<p>Username: " . htmlspecialchars($username) . "</p>";
    echo "<p>API Key: " . (empty($apiKey) ? "Not provided" : "Provided (hidden)") . "</p>";
    
    try {
        // Add authentication to params
        $params['z'] = $username;
        $params['y'] = $apiKey;
        
        // Build URL
        $url = 'https://retroachievements.org/API/' . $testEndpoint . '?' . http_build_query($params);
        echo "<p>Request URL: " . htmlspecialchars($url) . "</p>";
        
        // Make request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'RetroHub/1.0');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        
        // Create a stream for curl to write verbose information to
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($curl, CURLOPT_STDERR, $verbose);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        // Get verbose information
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        
        curl_close($curl);
        
        // Display results
        echo "<p>HTTP Status Code: " . $httpCode . "</p>";
        
        if ($error) {
            echo "<p>Error: " . htmlspecialchars($error) . "</p>";
        }
        
        echo "<h4>Curl Verbose Log:</h4>";
        echo "<pre>" . htmlspecialchars($verboseLog) . "</pre>";
        
        echo "<h4>Response:</h4>";
        if ($response === false) {
            echo "<p>No response received</p>";
            return false;
        } else {
            // Attempt to parse JSON
            $parsedResponse = json_decode($response, true);
            if ($parsedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
                echo "<p>Invalid JSON response. JSON error: " . json_last_error_msg() . "</p>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "...</pre>";
            } else {
                echo "<pre>" . htmlspecialchars(print_r($parsedResponse, true)) . "</pre>";
                return $parsedResponse;
            }
        }
    } catch (Exception $e) {
        echo "<p>Exception: " . $e->getMessage() . "</p>";
    }
    
    return false;
}

// Function to check file permissions
function checkFilePermissions() {
    echo "<h3>File Permission Check</h3>";
    
    $directories = [
        RA_CACHE_DIR,
        RA_ICONS_CACHE_DIR,
        RA_SCREENSHOTS_CACHE_DIR,
        'config/'
    ];
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Directory</th><th>Exists</th><th>Writable</th><th>Permissions</th></tr>";
    
    foreach ($directories as $dir) {
        $exists = is_dir($dir);
        $writable = $exists && is_writable($dir);
        $permissions = $exists ? substr(sprintf('%o', fileperms($dir)), -4) : 'N/A';
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($dir) . "</td>";
        echo "<td style='color: " . ($exists ? "green" : "red") . ";'>" . ($exists ? "Yes" : "No") . "</td>";
        echo "<td style='color: " . ($writable ? "green" : "red") . ";'>" . ($writable ? "Yes" : "No") . "</td>";
        echo "<td>" . $permissions . "</td>";
        echo "</tr>";
        
        // Try to create directory if it doesn't exist
        if (!$exists) {
            $created = mkdir($dir, 0755, true);
            echo "<tr><td colspan='4'>Attempted to create directory: " . ($created ? "Success" : "Failed") . "</td></tr>";
        }
    }
    
    echo "</table>";
}

// Function to test image download
function testImageDownload($url, $destination) {
    echo "<h3>Testing Image Download</h3>";
    echo "<p>Source URL: " . htmlspecialchars($url) . "</p>";
    echo "<p>Destination: " . htmlspecialchars($destination) . "</p>";
    
    try {
        // Handle full URLs or relative URLs
        if (strpos($url, 'http') !== 0) {
            $url = 'https://retroachievements.org' . $url;
            echo "<p>Converted to full URL: " . htmlspecialchars($url) . "</p>";
        }
        
        // Download image
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'RetroHub/1.0');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        
        $image = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        // Display results
        echo "<p>HTTP Status Code: " . $httpCode . "</p>";
        echo "<p>Content Type: " . htmlspecialchars($contentType) . "</p>";
        echo "<p>Content Size: " . strlen($image) . " bytes</p>";
        
        if ($error) {
            echo "<p>Error: " . htmlspecialchars($error) . "</p>";
            return false;
        }
        
        if ($httpCode !== 200) {
            echo "<p>Failed to download image (HTTP " . $httpCode . ")</p>";
            return false;
        }
        
        // Ensure directory exists
        $dir = dirname($destination);
        if (!is_dir($dir)) {
            $created = mkdir($dir, 0755, true);
            echo "<p>Created directory " . htmlspecialchars($dir) . ": " . ($created ? "Success" : "Failed") . "</p>";
            
            if (!$created) {
                echo "<p>Error: Could not create directory for image</p>";
                return false;
            }
        }
        
        // Save image
        $saved = file_put_contents($destination, $image);
        
        if ($saved === false) {
            echo "<p>Error: Could not save image to " . htmlspecialchars($destination) . "</p>";
            return false;
        }
        
        echo "<p>Image saved successfully (" . $saved . " bytes)</p>";
        
        // Display the image
        echo "<h4>Downloaded Image:</h4>";
        $base64 = base64_encode($image);
        echo "<img src='data:" . $contentType . ";base64," . $base64 . "' style='max-width: 300px; max-height: 300px;' />";
        
        return true;
    } catch (Exception $e) {
        echo "<p>Exception: " . $e->getMessage() . "</p>";
    }
    
    return false;
}

// Get settings
$raSettings = getRetroAchievementsSettings();

// Output page structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroAchievements Debug Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3, h4 {
            margin-top: 20px;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .section {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        table {
            width: 100%;
        }
    </style>
</head>
<body>
    <h1>RetroAchievements Debug Tool</h1>
    
    <div class="section">
        <h2>Current Configuration</h2>
        <table border="1" cellpadding="5" style="border-collapse: collapse;">
            <tr><th>Setting</th><th>Value</th></tr>
            <tr><td>Enabled</td><td><?php echo $raSettings['enabled'] ? "Yes" : "No"; ?></td></tr>
            <tr><td>Mode</td><td><?php echo $raSettings['mode']; ?></td></tr>
            <tr><td>Username</td><td><?php echo htmlspecialchars($raSettings['username']); ?></td></tr>
            <tr><td>API Key</td><td><?php echo empty($raSettings['api_key']) ? "Not provided" : "Provided (hidden)"; ?></td></tr>
            <tr><td>Proxy URL</td><td><?php echo htmlspecialchars($raSettings['proxy_url']); ?></td></tr>
            <tr><td>Override Local Images</td><td><?php echo isset($raSettings['override_local_images']) && $raSettings['override_local_images'] ? "Yes" : "No"; ?></td></tr>
        </table>
    </div>
    
    <div class="section">
        <h2>System Information</h2>
        <p>PHP Version: <?php echo phpversion(); ?></p>
        <p>cURL Enabled: <?php echo function_exists('curl_version') ? "Yes" : "No"; ?></p>
        <?php 
        if (function_exists('curl_version')) {
            $curlVersion = curl_version();
            echo "<p>cURL Version: " . $curlVersion['version'] . "</p>";
            echo "<p>SSL Version: " . $curlVersion['ssl_version'] . "</p>";
        }
        ?>
        <p>allow_url_fopen: <?php echo ini_get('allow_url_fopen') ? "Enabled" : "Disabled"; ?></p>
    </div>
    
    <div class="section">
        <h2>File System Checks</h2>
        <?php checkFilePermissions(); ?>
    </div>
    
    <div class="section">
        <h2>API Connection Tests</h2>
        <?php
        if ($raSettings['mode'] === 'proxy') {
            $proxyResult = testProxyConnection($raSettings['proxy_url']);
            
            if ($proxyResult && !empty($proxyResult)) {
                // Try downloading a sample image from the first game
                if (isset($proxyResult[0]['ImageIcon'])) {
                    $testImageUrl = $proxyResult[0]['ImageIcon'];
                    $testImageDest = RA_ICONS_CACHE_DIR . 'test_icon.png';
                    testImageDownload($testImageUrl, $testImageDest);
                }
            }
        } else {
            $directResult = testDirectConnection($raSettings['username'], $raSettings['api_key']);
            
            if ($directResult && !empty($directResult)) {
                // Try downloading a sample image from the first game
                if (isset($directResult[0]['ImageIcon'])) {
                    $testImageUrl = $directResult[0]['ImageIcon'];
                    $testImageDest = RA_ICONS_CACHE_DIR . 'test_icon.png';
                    testImageDownload($testImageUrl, $testImageDest);
                }
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h2>Game Search Test</h2>
        <form method="post">
            <p>
                <label for="game_title">Game Title:</label>
                <input type="text" name="game_title" id="game_title" value="<?php echo isset($_POST['game_title']) ? htmlspecialchars($_POST['game_title']) : 'Super Mario World'; ?>">
            </p>
            <p>
                <label for="console">Console:</label>
                <select name="console" id="console">
                    <?php
                    global $RA_CONSOLE_IDS;
                    foreach ($RA_CONSOLE_IDS as $consoleKey => $consoleId) {
                        $selected = (isset($_POST['console']) && $_POST['console'] === $consoleKey) ? ' selected' : '';
                        echo "<option value=\"$consoleKey\"$selected>$consoleKey ($consoleId)</option>";
                    }
                    ?>
                </select>
            </p>
            <p>
                <button type="submit" name="search_test">Test Game Search</button>
            </p>
        </form>
        
        <?php
        if (isset($_POST['search_test']) && isset($_POST['game_title']) && isset($_POST['console'])) {
            echo "<h3>Searching for: " . htmlspecialchars($_POST['game_title']) . " on " . htmlspecialchars($_POST['console']) . "</h3>";
            
            // Get game metadata
            $gameMetadata = getGameMetadata($_POST['game_title'], $_POST['console']);
            
            if ($gameMetadata) {
                echo "<h4>Game Metadata Found:</h4>";
                echo "<pre>" . htmlspecialchars(print_r($gameMetadata, true)) . "</pre>";
                
                // Display images if available
                echo "<h4>Images:</h4>";
                echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";
                
                if (isset($gameMetadata['icon']) && $gameMetadata['icon']) {
                    echo "<div>";
                    echo "<p>Icon:</p>";
                    echo "<img src='" . $gameMetadata['icon'] . "' alt='Game Icon' style='max-width: 200px; max-height: 200px;'>";
                    echo "</div>";
                }
                
                if (isset($gameMetadata['screenshot_title']) && $gameMetadata['screenshot_title']) {
                    echo "<div>";
                    echo "<p>Title Screenshot:</p>";
                    echo "<img src='" . $gameMetadata['screenshot_title'] . "' alt='Title Screenshot' style='max-width: 200px; max-height: 200px;'>";
                    echo "</div>";
                }
                
                if (isset($gameMetadata['screenshot_ingame']) && $gameMetadata['screenshot_ingame']) {
                    echo "<div>";
                    echo "<p>Ingame Screenshot:</p>";
                    echo "<img src='" . $gameMetadata['screenshot_ingame'] . "' alt='Ingame Screenshot' style='max-width: 200px; max-height: 200px;'>";
                    echo "</div>";
                }
                
                if (isset($gameMetadata['screenshot_boxart']) && $gameMetadata['screenshot_boxart']) {
                    echo "<div>";
                    echo "<p>Box Art:</p>";
                    echo "<img src='" . $gameMetadata['screenshot_boxart'] . "' alt='Box Art' style='max-width: 200px; max-height: 200px;'>";
                    echo "</div>";
                }
                
                echo "</div>";
            } else {
                echo "<p class='error'>No metadata found. Let's see if we can get the raw API response:</p>";
                
                // Try to get the raw data
                if ($raSettings['mode'] === 'proxy') {
                    $consoleId = $RA_CONSOLE_IDS[$_POST['console']];
                    testProxyConnection($raSettings['proxy_url'], 'API_GetGameList', ['i' => $consoleId, 'f' => $_POST['game_title']]);
                } else {
                    $consoleId = $RA_CONSOLE_IDS[$_POST['console']];
                    testDirectConnection($raSettings['username'], $raSettings['api_key'], 'API_GetGameList', ['i' => $consoleId, 'f' => $_POST['game_title']]);
                }
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h2>Clear Cache</h2>
        <form method="post">
            <button type="submit" name="clear_cache">Clear All RetroAchievements Cache</button>
        </form>
        
        <?php
        if (isset($_POST['clear_cache'])) {
            // Clear cache directories
            $cacheFiles = glob(RA_CACHE_DIR . '*.*');
            $iconFiles = glob(RA_ICONS_CACHE_DIR . '*.*');
            $screenshotFiles = glob(RA_SCREENSHOTS_CACHE_DIR . '*.*');
            
            $totalFiles = count($cacheFiles) + count($iconFiles) + count($screenshotFiles);
            $deletedFiles = 0;
            
            foreach (array_merge($cacheFiles, $iconFiles, $screenshotFiles) as $file) {
                if (unlink($file)) {
                    $deletedFiles++;
                }
            }
            
            echo "<p>Cleared $deletedFiles out of $totalFiles cache files.</p>";
        }
        ?>
    </div>
</body>
</html>