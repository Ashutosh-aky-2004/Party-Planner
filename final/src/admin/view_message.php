<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/final/src/database/dbConnect.php';

// Make sure user is logged in
// if (!isset($_SESSION['user_id'])) {
//     // If no user is logged in, redirect to login page
//     header('Location: login.php');
//     exit();
// }

// Fetch user details from the database
$user_id = $_SESSION['user_id'];  // Assuming user ID is stored in session
$query = "SELECT full_name, LEFT(full_name, 2) AS initials FROM users WHERE id = $user_id LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
    $user_name = $user_data['full_name'];
    $user_initials = $user_data['initials'];
} else {
    // If no user found, handle accordingly
    $user_name = 'Guest';
    $user_initials = 'GU';
}
?>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/final/src/database/dbConnect.php';


// Check if admin is logged in
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: admin_login.php");
//     exit();
// }

// Check if message ID is provided
if (!isset($_GET['id'])) {
    header("Location: admin_contact_messages.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Get the message
$query = "SELECT * FROM contact_messages WHERE id = '$id'";
$result = mysqli_query($conn, $query);
$message = mysqli_fetch_assoc($result);

if (!$message) {
    header("Location: admin_contact_messages.php");
    exit();
}

// Mark as read when viewed
if (!$message['status']) {
    $update_query = "UPDATE contact_messages SET status = 1 WHERE id = '$id'";
    mysqli_query($conn, $update_query);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message</title>
    <link rel="icon" type="image/png" href="favicon (1).png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <header class="bg-black shadow-sm text-white z-10">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center">
                <button id="sidebarToggle" class="text-gray-500 mr-4 md:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <a href="dashboard.php">
                    <h2 class="text-xl font-semibold">Dashboard</h2>
                </a>
            </div>
            <div class="flex items-center space-x-4">

                <div class="relative">
                    <button id="userMenuBtn" class="flex items-center space-x-2 focus:outline-none">
                        <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-white">
                            <span>
                                <?php echo htmlspecialchars($user_initials); ?>
                            </span>
                        </div>
                        <span class="hidden md:inline text-sm font-medium">
                            <?php echo htmlspecialchars($user_name); ?>
                        </span>
                        <i class="fas fa-chevron-down text-xs hidden md:inline"></i>
                    </button>

                    <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden z-50">
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>

                        <div class="border-t"></div>
                        <form action="logout.php" method="POST">
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-100">Logout</button>
                        </form>
                    </div>
                </div>

                <script>
                    const userMenuBtn = document.getElementById('userMenuBtn');
                    const userDropdown = document.getElementById('userDropdown');

                    userMenuBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        userDropdown.classList.toggle('hidden');
                    });

                    document.addEventListener('click', function(e) {
                        if (!userDropdown.classList.contains('hidden')) {
                            userDropdown.classList.add('hidden');
                        }
                    });
                </script>



            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">View Message</h1>
            <a href="admin_contact_messages.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                <i class="fas fa-arrow-left mr-2"></i> Back to Messages
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h2 class="text-sm font-medium text-gray-500">From</h2>
                    <p class="mt-1 text-lg font-medium"><?= htmlspecialchars($message['name']) ?></p>
                </div>
                <div>
                    <h2 class="text-sm font-medium text-gray-500">Email</h2>
                    <p class="mt-1 text-lg font-medium"><?= htmlspecialchars($message['email']) ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h2 class="text-sm font-medium text-gray-500">Date Received</h2>
                    <p class="mt-1 text-lg font-medium">
                        <?= date('F j, Y \a\t g:i A', strtotime($message['created_at'])) ?>
                    </p>
                </div>
                <div>
                    <h2 class="text-sm font-medium text-gray-500">Status</h2>
                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $message['status'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <?= $message['status'] ? 'Read' : 'Unread' ?>
                    </span>
                </div>
            </div>

            <div class="mb-6">
                <h2 class="text-sm font-medium text-gray-500">Message</h2>
                <div class="mt-1 p-4 bg-gray-50 rounded-md">
                    <p class="whitespace-pre-line"><?= htmlspecialchars($message['message']) ?></p>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="admin_contact_messages.php?mark=<?= $message['status'] ? 'unread' : 'read' ?>&id=<?= $message['id'] ?>"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-<?= $message['status'] ? 'envelope' : 'envelope-open' ?> mr-2"></i>
                    Mark as <?= $message['status'] ? 'Unread' : 'Read' ?>
                </a>
                <a href="admin_contact_messages.php?delete=1&id=<?= $message['id'] ?>"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md"
                    onclick="return confirm('Are you sure you want to delete this message?')">
                    <i class="fas fa-trash mr-2"></i> Delete Message
                </a>
            </div>
        </div>
    </div>
</body>

</html>