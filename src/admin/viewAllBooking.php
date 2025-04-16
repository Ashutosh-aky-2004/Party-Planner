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

// Fetch all bookings from database
$query = "SELECT 
            b.id, 
            b.user_id, 
            u.full_name as user_name, 
            b.hotel_id, 
            h.name as hotel_name, 
            b.event_type, 
            b.check_in_date, 
            b.check_out_date, 
            b.no_of_persons, 
            b.total_cost, 
            b.status, 
            b.created_at
          FROM bookings b
          JOIN users u ON b.user_id = u.id
          JOIN hotels h ON b.hotel_id = h.id
          ORDER BY b.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Bookings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="favicon (1).png">
    <style>
        .status-pending {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status-confirmed {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-cancelled {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .status-completed {
            background-color: #E0E7FF;
            color: #3730A3;
        }

        .status-rejected {
            background-color: #F3F4F6;
            color: #6B7280;
        }
    </style>
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
                        <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
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
        <h1 class="text-2xl font-bold mb-6">All Bookings</h1>

        <!-- Bookings Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hotel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($booking = mysqli_fetch_assoc($result)): ?>
                            <?php
                            // Format dates
                            $checkInDate = date('d M Y', strtotime($booking['check_in_date']));
                            $checkOutDate = date('d M Y', strtotime($booking['check_out_date']));

                            // Format currency (Indian format)
                            $formattedAmount = '₹' . number_format($booking['total_cost']);

                            // Status colors
                            $statusColors = [
                                'pending' => 'status-pending',
                                'confirmed' => 'status-confirmed',
                                'cancelled' => 'status-cancelled',
                                'completed' => 'status-completed',
                                'rejected' => 'status-rejected'
                            ];
                            $statusClass = $statusColors[$booking['status']] ?? 'status-pending';
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">#<?= $booking['id'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($booking['user_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($booking['hotel_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($booking['event_type']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= $checkInDate ?> to <?= $checkOutDate ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= $booking['no_of_persons'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= $formattedAmount ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?= $statusClass ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="booking_details.php?id=<?= $booking['id'] ?>" class="text-blue-500 hover:text-blue-700 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_booking.php?id=<?= $booking['id'] ?>" class="text-yellow-500 hover:text-yellow-700 mr-2">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($result) == 0): ?>
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500">No bookings found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>