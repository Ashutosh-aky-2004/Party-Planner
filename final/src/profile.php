<?php
session_start();
require 'database/dbConnect.php';
require 'formValidation.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get current user ID from session
$user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);

// Initialize variables
$success_message = '';
$error_message = '';
$bookings = [];
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile'; // Default to profile tab

// Fetch user data
$user = [];
if ($user_id > 0) {
    $query = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    } else {
        $error_message = "User not found";
    }

    // Fetch user's bookings with hotel details
    $bookings_query = "SELECT b.*, h.name as hotel_name, h.address as hotel_address, 
                      h.city as hotel_city, h.country as hotel_country, h.image_path as hotel_image,
                      h.amount_per_night as hotel_rate
                      FROM bookings b
                      JOIN hotels h ON b.hotel_id = h.id
                      WHERE b.user_id = $user_id
                      ORDER BY b.check_in_date DESC";
    $bookings_result = mysqli_query($conn, $bookings_query);

    if ($bookings_result && mysqli_num_rows($bookings_result) > 0) {
        while ($row = mysqli_fetch_assoc($bookings_result)) {
            $bookings[] = $row;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $active_tab == 'profile') {
    // Sanitize inputs
    $full_name = mysqli_real_escape_string($conn, sanitize_input($_POST['full_name']));
    $last_name = mysqli_real_escape_string($conn, sanitize_input($_POST['last_name']));
    $phone = mysqli_real_escape_string($conn, sanitize_input($_POST['phone']));
    $email = mysqli_real_escape_string($conn, filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $address = mysqli_real_escape_string($conn, sanitize_input($_POST['address']));

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

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
            } elseif (strlen($new_password) < 8) {
                $error_message = "Password must be at least 8 characters";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_update = ", password = '" . mysqli_real_escape_string($conn, $hashed_password) . "'";
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        function switchTab(tabName) {
            // Update URL
            window.history.pushState(null, null, '?tab=' + tabName);

            // Hide all sections
            document.querySelectorAll('.tab-content').forEach(section => {
                section.classList.add('hidden');
            });

            // Show selected section
            document.getElementById(tabName + '-section').classList.remove('hidden');

            // Update active tab styling
            document.querySelectorAll('.tab-button').forEach(button => {
                if (button.getAttribute('data-tab') === tabName) {
                    button.classList.add('bg-blue-600', 'text-white');
                    button.classList.remove('bg-gray-200', 'text-gray-700');
                } else {
                    button.classList.remove('bg-blue-600', 'text-white');
                    button.classList.add('bg-gray-200', 'text-gray-700');
                }
            });
        }

        // Initialize tab on page load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            const defaultTab = tabParam || 'profile';
            switchTab(defaultTab);
        });
    </script>
</head>

<body class="bg-gray-100">
    <?php require 'essentials/header.php' ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Welcome, <?= htmlspecialchars($user['full_name'] ?? 'User') ?></h1>
                    <p class="text-gray-500"><?= date('D, d M Y') ?></p>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex mb-6">
                <button data-tab="profile" onclick="switchTab('profile')"
                    class="tab-button px-6 py-3 rounded-l-lg font-medium transition-all <?= $active_tab == 'profile' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' ?>">
                    <i class="fas fa-user mr-2"></i>My Profile
                </button>
                <button data-tab="bookings" onclick="switchTab('bookings')"
                    class="tab-button px-6 py-3 rounded-r-lg font-medium transition-all <?= $active_tab == 'bookings' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' ?>">
                    <i class="fas fa-hotel mr-2"></i>My Bookings
                </button>
            </div>

            <!-- Profile Section -->
            <div id="profile-section" class="tab-content bg-white rounded-xl shadow-md overflow-hidden <?= $active_tab != 'profile' ? 'hidden' : '' ?>">
                <!-- Profile Header -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold">Profile Information</h2>
                            <p class="text-blue-100">Update your personal details</p>
                        </div>
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Contact Information</h3>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                                <textarea name="address" rows="2" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Password Change -->
                    <div class="mt-8 space-y-4">
                        <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Change Password</h3>
                        <p class="text-sm text-gray-500">Leave blank to keep current password</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input type="password" name="current_password"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" name="new_password"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                <input type="password" name="confirm_password"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition font-medium">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bookings Section -->
            <div id="bookings-section" class="tab-content bg-white rounded-xl shadow-md overflow-hidden <?= $active_tab != 'bookings' ? 'hidden' : '' ?>">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                    <h2 class="text-xl font-semibold">Your Booking History</h2>
                </div>

                <div class="p-6">
                    <?php if (empty($bookings)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-hotel text-5xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg">You haven't made any bookings yet.</p>
                            <a href="index.php" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                Browse Hotels
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($bookings as $booking): ?>
                                <div class="booking-card border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition transform hover:-translate-y-1">
                                    <?php if (!empty($booking['hotel_image'])): ?>
                                        <img src="<?= htmlspecialchars($booking['hotel_image']) ?>" alt="<?= htmlspecialchars($booking['hotel_name']) ?>" class="w-full h-48 object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-hotel text-4xl text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="p-4">
                                        <h3 class="font-bold text-lg mb-2 truncate"><?= htmlspecialchars($booking['hotel_name']) ?></h3>

                                        <div class="flex items-center text-gray-600 mb-2">
                                            <i class="fas fa-calendar-day text-blue-500 mr-2"></i>
                                            <span>
                                                <?= date('M j, Y', strtotime($booking['check_in_date'])) ?> -
                                                <?= date('M j, Y', strtotime($booking['check_out_date'])) ?>
                                            </span>
                                        </div>

                                        <div class="flex justify-between text-sm text-gray-600 mb-3">
                                            <div class="flex items-center">
                                                <i class="fas fa-users text-blue-500 mr-2"></i>
                                                <span><?= htmlspecialchars($booking['no_of_persons']) ?> persons</span>
                                            </div>
                                            <div class="flex items-center">
                                                ₹
                                                <span><?= number_format($booking['total_cost'], 2) ?></span>
                                            </div>
                                        </div>

                                        <div class="flex justify-between items-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                                <?= $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : ($booking['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : ($booking['status'] === 'completed' ? 'bg-blue-100 text-blue-800' :
                                                    'bg-yellow-100 text-yellow-800')) ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>

                                            <a href="booking_details.php?id=<?= $booking['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                View Details <i class="fas fa-chevron-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php require 'essentials/footer.html' ?>
</body>

</html>

<?php mysqli_close($conn); ?>