<?php
session_start();
include 'functions.php';

// Check if we have the required data
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

if (!isset($_POST["profile_id"]) || !isset($_POST["game_name"]) || !isset($_POST["slot"]) || 
    !isset($_FILES["state"]) || !isset($_FILES["screenshot"])) {
    http_response_code(400);
    echo "Missing required data";
    exit;
}

$profileId = $_POST["profile_id"];
$gameName = $_POST["game_name"];
$slot = (int)$_POST["slot"];

// Validate the profile exists
$profile = getProfileById($profileId);
if (!$profile) {
    http_response_code(404);
    echo "Profile not found";
    exit;
}

// Check for upload errors
if ($_FILES["state"]["error"] !== UPLOAD_ERR_OK || $_FILES["screenshot"]["error"] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo "File upload error";
    exit;
}

// Read state data
$stateData = file_get_contents($_FILES["state"]["tmp_name"]);
if ($stateData === false) {
    http_response_code(500);
    echo "Failed to read state data";
    exit;
}

// Read screenshot data
$screenshotData = file_get_contents($_FILES["screenshot"]["tmp_name"]);
if ($screenshotData === false) {
    http_response_code(500);
    echo "Failed to read screenshot data";
    exit;
}

// Save the state and screenshot
$success = saveGameState($gameName, $profileId, $slot, $stateData, $screenshotData);

if ($success) {
    echo "Save state created successfully";
} else {
    http_response_code(500);
    echo "Failed to save game state";
}