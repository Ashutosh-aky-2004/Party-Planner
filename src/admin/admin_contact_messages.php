<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/final/src/database/dbConnect.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    // If no user is logged in, redirect to login page
    header('Location: login.php');
    exit();
}

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
require $_SERVER['DOCUMENT_ROOT'] . '/final/src/formValidation.php';


// Handle mark as read/unread
if (isset($_GET['mark'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $status = $_GET['mark'] === 'read' ? 1 : 0;

    $query = "UPDATE contact_messages SET status = $status WHERE id = '$id'";
    mysqli_query($conn, $query);
    header("Location: admin_contact_messages.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $query = "DELETE FROM contact_messages WHERE id = '$id'";
    mysqli_query($conn, $query);
    header("Location: admin_contact_messages.php");
    exit();
}

// Get all messages
$query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages</title>
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
        <h1 class="text-3xl font-bold mb-8">Contact Messages</h1>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($messages as $message): ?>
                            <tr class="<?= $message['status'] ? 'bg-green-300' : 'bg-red-300' ?>">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $message['id'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($message['name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($message['email']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate"><?= htmlspecialchars($message['message']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M j, Y g:i A', strtotime($message['created_at'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $message['status'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $message['status'] ? 'Read' : 'Unread' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="view_message.php?id=<?= $message['id'] ?>"
                                        class="text-blue-600 hover:text-blue-900 mr-3"
                                        title="View Full Message">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="?mark=<?= $message['status'] ? 'unread' : 'read' ?>&id=<?= $message['id'] ?>"
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-<?= $message['status'] ? 'envelope' : 'envelope-open' ?>"></i>
                                        <?= $message['status'] ? 'Unread' : 'Read' ?>
                                    </a>
                                    <a href="?delete=1&id=<?= $message['id'] ?>"
                                        class="text-red-600 hover:text-red-900"
                                        onclick="return confirm('Are you sure you want to delete this message?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>