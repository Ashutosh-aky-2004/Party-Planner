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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $phone = sanitize_input($_POST['phone']);
    $email =  filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $address = sanitize_input($_POST['address']);
    $password = filter_var($_POST['password']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Check if email already exists
    $checkEmail = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkEmail);

    if (mysqli_num_rows($result) > 0) {
        $error = "Email already exists!";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (full_name, last_name, email, phone, address, password, is_admin, is_active) 
                VALUES ('$full_name', '$last_name', '$email', '$phone', '$address', '$hashedPassword', $is_admin, $is_active)";

        if (mysqli_query($conn, $sql)) {
            $success = "User added successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link rel="icon" type="image/png" href="favicon (1).png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Add New User</h1>
                <a href="viewAllUsers.php" class="text-primary hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Users
                </a>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php $_SERVER['PHP_SELF']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="full_name">First Name*</label>
                        <input class="w-full px-3 py-2 border rounded-md" type="text" id="full_name" name="full_name"
                            value="<?= $_POST['full_name'] ?? '' ?>" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="last_name">Last Name*</label>
                        <input class="w-full px-3 py-2 border rounded-md" type="text" id="last_name" name="last_name"
                            value="<?= $_POST['last_name'] ?? '' ?>" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email*</label>
                        <input class="w-full px-3 py-2 border rounded-md" type="email" id="email" name="email"
                            value="<?= $_POST['email'] ?? '' ?>" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone*</label>
                        <input class="w-full px-3 py-2 border rounded-md" type="tel" id="phone" name="phone"
                            value="<?= $_POST['phone'] ?? '' ?>" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="address">Address*</label>
                        <textarea class="w-full px-3 py-2 border rounded-md" id="address" name="address" required><?= $_POST['address'] ?? '' ?></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password*</label>
                        <input class="w-full px-3 py-2 border rounded-md" type="password" id="password" name="password" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">Confirm Password*</label>
                        <input class="w-full px-3 py-2 border rounded-md" type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="is_admin" class="rounded" <?= isset($_POST['is_admin']) ? 'checked' : '' ?>>
                            <span>Admin User</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="is_active" class="rounded" <?= !isset($_POST['is_active']) || $_POST['is_active'] ? 'checked' : '' ?>>
                            <span>Active User</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="bg-black text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>