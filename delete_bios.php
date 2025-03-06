<?php
session_start();
include 'functions.php';

// Check if we have the required data
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

if (!isset($_POST["profile_id"]) || !isset($_POST["game"]) || !isset($_POST["slot"])) {
    http_response_code(400);
    echo "Missing required data";
    exit;
}

$profileId = $_POST["profile_id"];
$game = $_POST["game"];
$slot = (int)$_POST["slot"];

// Validate the profile exists
$profile = getProfileById($profileId);
if (!$profile) {
    http_response_code(404);
    echo "Profile not found";
    exit;
}

// Construct file paths
$gameName = pathinfo($game, PATHINFO_FILENAME);
$saveStatePath = "saves/$profileId/{$gameName}_{$slot}.state";
$screenshotPath = "img/saves/$profileId/{$gameName}_{$slot}.png";

// Delete save state file
$stateDeleted = false;
if (file_exists($saveStatePath)) {
    $stateDeleted = unlink($saveStatePath);
}

// Delete screenshot file
$screenshotDeleted = false;
if (file_exists($screenshotPath)) {
    $screenshotDeleted = unlink($screenshotPath);
}

if ($stateDeleted || $screenshotDeleted) {
    echo "Save state deleted successfully";
} else {
    http_response_code(404);
    echo "Save state not found or could not be deleted";
}