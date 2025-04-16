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


// Get current user ID from session
$user_id = $_SESSION['user_id'] ?? 0;

// Initialize variables
$success_message = '';
$error_message = '';

// Fetch user data
$user = [];
if ($user_id > 0) {
    $query = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $phone = sanitize_input($_POST['phone']);
    $email =  filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $address = sanitize_input($_POST['address']);

    $current_password = filter_var($_POST['current_password']);
    $new_password = filter_var($_POST['new_password']);
    $confirm_password = filter_var($_POST['confirm_password']);

    // Basic validation
    if (empty($full_name)) {
        $error_message = "Full name is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } else {
        // Check if password is being changed
        $password_update = '';
        if (!empty($current_password)) {
            if (!password_verify($current_password, $user['password'])) {
                $error_message = "Current password is incorrect";
            } elseif ($new_password !== $confirm_password) {
                $error_message = "New passwords don't match";
                // } elseif (strlen($new_password) < 8) {
                //     $error_message = "Password must be at least 8 characters";
                // } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_update = ", password = '$hashed_password'";
            }
        }

        if (empty($error_message)) {
            // Update user data
            $update_query = "UPDATE users SET 
                            full_name = '$full_name',
                            last_name = '$last_name',
                            email = '$email',
                            phone = '$phone',
                            address = '$address'
                            $password_update
                            WHERE id = $user_id";

            if (mysqli_query($conn, $update_query)) {
                $success_message = "Profile updated successfully!";
                // Refresh user data
                $query = "SELECT * FROM users WHERE id = $user_id";
                $result = mysqli_query($conn, $query);
                $user = mysqli_fetch_assoc($result);
            } else {
                $error_message = "Error updating profile: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
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
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Welcome, <?= htmlspecialchars($user['full_name'] ?? 'User') ?></h1>
                    <p class="text-gray-500"><?= date('D, d M Y') ?></p>
                </div>

            </div>

            <!-- Profile Card -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <!-- Profile Header -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold"><?= htmlspecialchars($user['full_name'] ?? '') ?></h2>
                            <p class="text-blue-100"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                        </div>
                        <button class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition">
                            Edit Profile
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <?php if ($success_message): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mx-6 mt-4">
                        <p><?= htmlspecialchars($success_message) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mx-6 mt-4">
                        <p><?= htmlspecialchars($error_message) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Profile Form -->
                <form method="POST" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Personal Info -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Personal Information</h3>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <textarea name="address" rows="2" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Password Change -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Change Password</h3>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input type="password" name="current_password"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" name="new_password"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">At least 8 characters</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" name="confirm_password"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition font-medium">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>

<?php mysqli_close($conn); ?>