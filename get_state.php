<?php
session_start();
include 'functions.php';

// Check if we have the required data
if (!isset($_GET["profile_id"]) || !isset($_GET["game"]) || !isset($_GET["slot"])) {
    http_response_code(400);
    echo "Missing required parameters";
    exit;
}

$profileId = $_GET["profile_id"];
$game = $_GET["game"];
$slot = (int)$_GET["slot"];

// Validate the profile exists
$profile = getProfileById($profileId);
if (!$profile) {
    http_response_code(404);
    echo "Profile not found";
    exit;
}

// Construct the save state file path
$gameName = pathinfo($game, PATHINFO_FILENAME);
$saveStatePath = "saves/$profileId/{$gameName}_{$slot}.state";

// Check if the save state exists
if (!file_exists($saveStatePath)) {
    http_response_code(404);
    echo "Save state not found";
    exit;
}

// Read the save state data
$stateData = file_get_contents($saveStatePath);
if ($stateData === false) {
    http_response_code(500);
    echo "Failed to read save state";
    exit;
}

// Set appropriate headers
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($saveStatePath) . '"');
header('Content-Length: ' . filesize($saveStatePath));

// Output the save state data
echo $stateData;