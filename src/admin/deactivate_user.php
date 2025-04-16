<?php
require $_SERVER['DOCUMENT_ROOT'] . '/final/src/database/dbConnect.php';
require $_SERVER['DOCUMENT_ROOT'] . '/final/src/formValidation.php';

if (!isset($_GET['id'])) {
    header("Location: viewAllUsers.php");
    exit();
}

$user_id = intval($_GET['id']);

// Deactivate user
$query = "UPDATE users SET is_active = 0 WHERE id = $user_id";
if (mysqli_query($conn, $query)) {
    $_SESSION['success_message'] = "User deactivated successfully!";
} else {
    $_SESSION['error_message'] = "Error deactivating user: " . mysqli_error($conn);
}

header("Location: viewAllUsers.php");
exit();
