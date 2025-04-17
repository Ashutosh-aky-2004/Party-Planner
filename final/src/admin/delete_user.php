

<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/final/src/database/dbConnect.php';
require $_SERVER['DOCUMENT_ROOT'] . '/final/src/formValidation.php';

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "No user specified for deletion";
    header("Location: viewAllUsers.php");
    exit();
}

$user_id = intval($_GET['id']);

// Verify user exists before attempting deletion
$checkQuery = "SELECT id FROM users WHERE id = $user_id";
$checkResult = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($checkResult) === 0) {
    $_SESSION['error_message'] = "User not found";
    header("Location: viewAllUsers.php");
    exit();
}

// Check if this is the last admin (prevent deleting last admin)
$adminCheck = "SELECT COUNT(*) as admin_count FROM users WHERE is_admin = 1";
$adminResult = mysqli_query($conn, $adminCheck);
$adminData = mysqli_fetch_assoc($adminResult);

$isAdmin = false;
$userQuery = "SELECT is_admin FROM users WHERE id = $user_id";
$userResult = mysqli_query($conn, $userQuery);
if ($userData = mysqli_fetch_assoc($userResult)) {
    $isAdmin = (bool)$userData['is_admin'];
}

if ($isAdmin && $adminData['admin_count'] <= 1) {
    $_SESSION['error_message'] = "Cannot delete the last admin user";
    header("Location: viewAllUsers.php");
    exit();
}

// Proceed with deletion
$deleteQuery = "DELETE FROM users WHERE id = $user_id";
if (mysqli_query($conn, $deleteQuery)) {
    $_SESSION['success_message'] = "User deleted successfully";
} else {
    $_SESSION['error_message'] = "Error deleting user: " . mysqli_error($conn);
}

header("Location: viewAllUsers.php");
exit();
