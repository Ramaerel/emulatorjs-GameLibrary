<?php
/**
 * Profile data endpoint
 * This file handles getting profile data for editing
 */

session_start();
include 'functions.php';

// Check if we're getting a profile
if (isset($_GET['action']) && $_GET['action'] == 'get' && isset($_GET['profile_id'])) {
    $profileId = $_GET['profile_id'];
    
    // Get the profile data
    $profile = getProfileById($profileId);
    
    // Return JSON response
    header('Content-Type: application/json');
    
    if ($profile) {
        echo json_encode($profile);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Profile not found']);
    }
    exit;
}

// Redirect to index if accessed directly
header('Location: index.php');