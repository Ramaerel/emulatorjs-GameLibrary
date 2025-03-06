<?php
/**
 * RetroAchievements API Proxy Server
 * 
 * This script acts as a proxy for RetroAchievements API requests.
 * It caches responses to reduce API calls and allows sharing a single
 * API key among multiple RetroHub installations.
 * 
 * Updated according to the latest API documentation.
 */

// CORS headers to allow access from different domains
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$config = [
    'api_key' => '** YOUR API KEY **', // Replace with your RetroAchievements API key
    'cache_dir' => 'cache/', // Directory to store cached responses
    'cache_expiration' => 604800, // Cache expiration time in seconds (7 days)
    'rate_limit' => 30, // Maximum requests per minute per IP
    'rate_limit_window' => 60 // Time window for rate limiting in seconds
];

// Create cache directory if it doesn't exist
if (!is_dir($config['cache_dir'])) {
    mkdir($config['cache_dir'], 0755, true);
}

// Basic rate limiting
$clientIP = $_SERVER['REMOTE_ADDR'];
$rateLimitFile = $config['cache_dir'] . 'rate_' . md5($clientIP) . '.json';

$rateData = [
    'count' => 0,
    'timestamp' => time()
];

if (file_exists($rateLimitFile)) {
    $rateData = json_decode(file_get_contents($rateLimitFile), true);
    
    // Reset counter if window has passed
    if (time() - $rateData['timestamp'] > $config['rate_limit_window']) {
        $rateData['count'] = 0;
        $rateData['timestamp'] = time();
    }
}

// Check if rate limit exceeded
if ($rateData['count'] >= $config['rate_limit']) {
    header('HTTP/1.1 429 Too Many Requests');
    echo json_encode([
        'error' => 'Rate limit exceeded. Please try again later.'
    ]);
    exit;
}

// Process only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode([
        'error' => 'Only POST requests are allowed.'
    ]);
    exit;
}

// Verify required parameters
if (!isset($_POST['endpoint']) || empty($_POST['endpoint'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'error' => 'Missing required parameter: endpoint'
    ]);
    exit;
}

$endpoint = $_POST['endpoint'];
$params = isset($_POST['params']) ? $_POST['params'] : [];

// Sanitize endpoint to prevent directory traversal
$endpoint = basename($endpoint);

// Generate cache key based on request
$cacheKey = md5($endpoint . serialize($params));
$cachePath = $config['cache_dir'] . $cacheKey . '.json';

// Check if cached response exists and is still valid
if (file_exists($cachePath) && (time() - filemtime($cachePath) < $config['cache_expiration'])) {
    $cachedResponse = file_get_contents($cachePath);
    
    if ($cachedResponse) {
        header('Content-Type: application/json');
        echo $cachedResponse;
        exit;
    }
}

// Increment rate limit counter and save
$rateData['count']++;
file_put_contents($rateLimitFile, json_encode($rateData));

// Build API URL - Using the correct format: API_EndpointName.php
$baseUrl = 'https://retroachievements.org/API/';
$url = $baseUrl . 'API_' . $endpoint . '.php';

// Add authentication to params
if (is_array($params)) {
    $params['y'] = $config['api_key'];
} else {
    $params = [
        'y' => $config['api_key']
    ];
}

// Build full URL with parameters
$url .= '?' . http_build_query($params);

// Make request to RetroAchievements API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'RetroHub-Proxy/1.0');
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// Return error if request failed
if ($httpCode !== 200 || empty($response)) {
    header('HTTP/1.1 ' . ($httpCode ? $httpCode : 500) . ' Error');
    echo json_encode([
        'error' => 'Error fetching data from RetroAchievements API',
        'http_code' => $httpCode,
        'curl_error' => $error,
        'url' => $url
    ]);
    exit;
}

// Cache the response
file_put_contents($cachePath, $response);

// Return the response
header('Content-Type: application/json');
echo $response;