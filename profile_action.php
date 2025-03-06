<?php
session_start();
include 'functions.php';

// Ensure we have an action
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    header('Location: index.php');
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

switch ($action) {
    case 'create':
        // Create a new profile
        if (!isset($_POST['name']) || empty($_POST['name'])) {
            $_SESSION['profile_error'] = "Profile name is required";
            header('Location: settings.php#profiles-panel');
            exit;
        }
        
        $name = trim($_POST['name']);
        $avatar = isset($_POST['avatar']) ? $_POST['avatar'] : 'avatar1.png';
        
        // Validate avatar file exists
        if (!file_exists("img/avatars/$avatar")) {
            $avatar = 'avatar1.png'; // Default to first avatar if missing
        }
        
        // Create the profile
        $profileId = createProfile($name, $avatar);
        
        if ($profileId) {
            // Set as current profile
            $_SESSION['current_profile'] = $profileId;
            $_SESSION['profile_success'] = "Profile created successfully";
        } else {
            $_SESSION['profile_error'] = "Failed to create profile";
        }
        
        // Redirect back to previous page or settings
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'settings.php#profiles-panel';
        header("Location: $redirect");
        break;
        
    case 'switch':
        // Switch to a different profile
        if (!isset($_POST['profile_id']) || empty($_POST['profile_id'])) {
            $_SESSION['profile_error'] = "Profile ID is required";
            header('Location: index.php');
            exit;
        }
        
        $profileId = $_POST['profile_id'];
        
        // Validate the profile exists
        $profile = getProfileById($profileId);
        
        if ($profile) {
            $_SESSION['current_profile'] = $profileId;
            echo "Profile switched successfully";
        } else {
            http_response_code(404);
            echo "Profile not found";
        }
        break;
        
    case 'update':
        // Update profile information
        if (!isset($_POST['profile_id']) || empty($_POST['profile_id'])) {
            $_SESSION['profile_error'] = "Profile ID is required";
            header('Location: settings.php#profiles-panel');
            exit;
        }
        
        $profileId = $_POST['profile_id'];
        
        // Gather the update data
        $updateData = [];
        
        if (isset($_POST['name']) && !empty($_POST['name'])) {
            $updateData['name'] = trim($_POST['name']);
        }
        
        if (isset($_POST['avatar']) && !empty($_POST['avatar'])) {
            // Validate avatar file exists
            if (file_exists("img/avatars/{$_POST['avatar']}")) {
                $updateData['avatar'] = $_POST['avatar'];
            }
        }
        
        if (empty($updateData)) {
            $_SESSION['profile_error'] = "No update data provided";
            header('Location: settings.php#profiles-panel');
            exit;
        }
        
        // Update the profile
        $success = updateProfile($profileId, $updateData);
        
        if ($success) {
            $_SESSION['profile_success'] = "Profile updated successfully";
        } else {
            $_SESSION['profile_error'] = "Failed to update profile";
        }
        
        // Redirect back to settings
        header('Location: settings.php#profiles-panel');
        break;
        
    case 'delete':
        // Delete a profile
        if (!isset($_POST['profile_id']) || empty($_POST['profile_id'])) {
            $_SESSION['profile_error'] = "Profile ID is required";
            header('Location: settings.php#profiles-panel');
            exit;
        }
        
        $profileId = $_POST['profile_id'];
        
        // Don't delete the currently active profile
        if ($_SESSION['current_profile'] === $profileId) {
            $_SESSION['profile_error'] = "Cannot delete the currently active profile";
            header('Location: settings.php#profiles-panel');
            exit;
        }
        
        // Delete the profile
        $success = deleteProfile($profileId);
        
        if ($success) {
            $_SESSION['profile_success'] = "Profile deleted successfully";
        } else {
            $_SESSION['profile_error'] = "Failed to delete profile";
        }
        
        // Redirect back to settings
        header('Location: settings.php#profiles-panel');
        break;
        
    case 'get':
        // Get profile data - handled by profile_get.php
        header('Location: profile_get.php?action=get&profile_id=' . $_GET['profile_id']);
        break;
        
    default:
        header('Location: index.php');
        break;
}