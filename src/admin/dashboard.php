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
$sql = "SELECT 
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
            b.admin_notes, 
            b.created_at 
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN hotels h ON b.hotel_id = h.id
        ORDER BY b.created_at DESC";
$result = mysqli_query($conn, $sql);

$i = 1;

$totalBookingsQuery = "SELECT COUNT(*) as total FROM bookings";
$totalResult = mysqli_query($conn, $totalBookingsQuery);
$totalBookings = mysqli_fetch_assoc($totalResult)['total'];

// Query to get bookings from last month
$lastMonthQuery = "SELECT COUNT(*) as last_month_total FROM bookings 
                  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) 
                  AND created_at < DATE_SUB(CURDATE(), INTERVAL 0 MONTH)";
$lastMonthResult = mysqli_query($conn, $lastMonthQuery);
$lastMonthBookings = mysqli_fetch_assoc($lastMonthResult)['last_month_total'];

// Calculate percentage change
$percentageChange = 0;
if ($lastMonthBookings > 0) {
    $percentageChange = (($totalBookings - $lastMonthBookings) / $lastMonthBookings) * 100;
}

// Format numbers
$formattedTotal = number_format($totalBookings);
$formattedPercentage = number_format(abs($percentageChange), 0);
$trendClass = ($percentageChange >= 0) ? 'text-green-500' : 'text-red-500';
$trendSymbol = ($percentageChange >= 0) ? '+' : '-';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" type="image/png" href="favicon (1).png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#6366F1',
                        dark: '#1F2937',
                        darker: '#111827',
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar {
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">
    <!-- Admin Dashboard Layout -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="sidebar bg-black text-white w-64 fixed h-full overflow-y-auto z-50">
            <div class="p-4 flex items-center space-x-2 border-b border-gray-700">
                <svg class="w-8 text-white" viewBox="0 0 24 24" stroke-linejoin="round" stroke-width="2" stroke-linecap="round" stroke-miterlimit="10" stroke="currentColor" fill="none">
                    <rect x="3" y="1" width="7" height="12"></rect>
                    <rect x="3" y="17" width="7" height="6"></rect>
                    <rect x="14" y="1" width="7" height="6"></rect>
                    <rect x="14" y="11" width="7" height="12"></rect>
                </svg>
                <h1 class="text-xl font-bold">Eventify</h1>
            </div>
            <nav class="p-4">
                <div class="mb-8">
                    <h2 class="text-xs uppercase text-gray-400 tracking-wider mb-4">Main</h2>
                    <ul>
                        <li class="mb-2">
                            <a href="#" class="flex items-center space-x-2 p-2 rounded bg-gray-800 text-primary">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="mb-8">
                    <h2 class="text-xs uppercase text-gray-400 tracking-wider mb-4">Management</h2>
                    <ul>
                        <li class="mb-2">
                            <a href="viewAllUsers.php" class="flex items-center space-x-2 p-2 rounded hover:bg-gray-800">
                                <i class="fas fa-users"></i>
                                <span>User Management</span>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="viewAllBooking.php" class="flex items-center space-x-2 p-2 rounded hover:bg-gray-800">
                                <i class="fas fa-calendar-check"></i>
                                <span>Booking Management</span>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="hotelData.php" class="flex items-center space-x-2 p-2 rounded hover:bg-gray-800">
                                <i class="fas fa-hotel"></i>
                                <span>Hotel Management</span>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="items_management.php" class="flex items-center space-x-2 p-2 rounded hover:bg-gray-800">
                                <i class="fas fa-utensils"></i>
                                <span>Food & Services</span>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="admin_contact_messages.php" class="flex items-center space-x-2 p-2 rounded hover:bg-gray-800">
                                <img class="text-white" src="/images/contact-mail_3095583.png"></i>
                                <span>User Feedback</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden ml-64">
            <!-- Top Navigation -->
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

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-100">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Total Bookings</p>
                                <h3 class="text-2xl font-bold"><?= $formattedTotal ?></h3>
                                <?php if ($lastMonthBookings > 0): ?>
                                    <p class="<?= $trendClass ?> text-sm">
                                        <?= $trendSymbol ?><?= $formattedPercentage ?>% from last month
                                    </p>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm">No previous data</p>
                                <?php endif; ?>
                            </div>
                            <div class="p-3 rounded-full bg-blue-100 text-primary">
                                <i class="fas fa-calendar-check text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Active Users</p>
                                <h3 class="text-2xl font-bold">856</h3>
                                <p class="text-green-500 text-sm">+8% from last month</p>
                            </div>
                            <div class="p-3 rounded-full bg-purple-100 text-secondary">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                        </div>
                    </div> -->

                    <div class="bg-white rounded-lg shadow p-6">
                        <?php // Query to get total revenue (sum of all booking costs)
                        $revenueQuery = "SELECT SUM(total_cost) as total_revenue FROM bookings WHERE status != 'cancelled'";
                        $revenueResult = mysqli_query($conn, $revenueQuery);
                        $totalRevenue = mysqli_fetch_assoc($revenueResult)['total_revenue'] ?? 0;

                        // Query to get revenue from last month
                        $lastMonthQuery = "SELECT SUM(total_cost) as last_month_revenue FROM bookings 
                        WHERE status != 'cancelled'
                        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) 
                        AND created_at < DATE_SUB(CURDATE(), INTERVAL 0 MONTH)";
                        $lastMonthResult = mysqli_query($conn, $lastMonthQuery);
                        $lastMonthRevenue = mysqli_fetch_assoc($lastMonthResult)['last_month_revenue'] ?? 0;

                        // Calculate percentage change
                        $percentageChange = 0;
                        if ($lastMonthRevenue > 0) {
                            $percentageChange = (($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
                        }

                        // Format numbers (Indian currency format)
                        $formattedRevenue = '₹' . number_format($totalRevenue);
                        $formattedPercentage = number_format(abs($percentageChange), 0);
                        $trendClass = ($percentageChange >= 0) ? 'text-green-500' : 'text-red-500';
                        $trendSymbol = ($percentageChange >= 0) ? '+' : '-';
                        ?>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Total Revenue</p>
                                <h3 class="text-2xl font-bold"><?= $formattedRevenue ?></h3>
                                <?php if ($lastMonthRevenue > 0): ?>
                                    <p class="<?= $trendClass ?> text-sm">
                                        <?= $trendSymbol ?><?= $formattedPercentage ?>% from last month
                                    </p>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm">No previous data</p>
                                <?php endif; ?>
                            </div>
                            <div class="p-3 rounded-full bg-green-100 text-green-500">
                                <i class="fas fa-rupee-sign text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Occupancy Rate</p>
                                <h3 class="text-2xl font-bold">78%</h3>
                                <p class="text-green-500 text-sm">+5% from last month</p>
                            </div>
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                                <i class="fas fa-percentage text-xl"></i>
                            </div>
                        </div>
                    </div> -->
                </div>




                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-semibold text-lg">Quick Actions</h3>
                    </div>
                    <div class="p-4 grid grid-cols-2 gap-4">
                        <a href="viewAllUsers.php" class="p-4 border rounded-lg text-center hover:bg-gray-50 transition">
                            <i class="fas fa-user text-2xl text-primary mb-2"></i>
                            <p>View Users</p>
                        </a>
                        <a href="add_hotel.php" class="p-4 border rounded-lg text-center hover:bg-gray-50 transition">
                            <i class="fas fa-hotel text-2xl text-secondary mb-2"></i>
                            <p>Add Hotel</p>
                        </a>
                        <a href="items_management.php" class="p-4 border rounded-lg text-center hover:bg-gray-50 transition">
                            <i class="fas fa-utensils text-2xl text-yellow-500 mb-2"></i>
                            <p>Add Menu Item</p>
                        </a>
                        <a href="add_user.php" class="p-4 border rounded-lg text-center hover:bg-gray-50 transition">
                            <i class="fas fa-user-plus text-2xl text-green-500 mb-2"></i>
                            <p>Add User</p>
                        </a>
                    </div>
                </div>




                <!-- Recent Bookings & Charts -->
                <div class="mt-6 mb-6">
                    <!-- Recent Bookings -->
                    <div class="bg-white rounded-lg shadow lg:col-span-2">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-semibold text-lg">Recent Bookings</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hotel</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while ($booking = mysqli_fetch_assoc($result)): ?>
                                        <?php

                                        if ($i == 5) {
                                            break;
                                        }
                                        // Format dates
                                        $checkInDate = date('d M Y', strtotime($booking['check_in_date']));
                                        $checkOutDate = date('d M Y', strtotime($booking['check_out_date']));

                                        // Determine status color
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'completed' => 'bg-blue-100 text-blue-800',
                                            'rejected' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $statusColor = $statusColors[$booking['status'] ?? 'bg-gray-100 text-gray-800'];
                                        ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">#<?= $booking['id'] ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($booking['user_name']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($booking['hotel_name']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?= $checkInDate ?> to <?= $checkOutDate ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full <?= $statusColor ?>">
                                                    <?= ucfirst($booking['status']) ?>
                                                </span>
                                            </td>
                                        
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-gray-200 text-right">
                            <a href="viewAllBooking.php" class="text-primary hover:underline">View All Bookings</a>
                        </div>
                    </div>


                </div>

                <!-- Quick Actions & Recent Users -->
                <div class="">

                    <!-- Recent Users -->
                    <?php $query = "SELECT 
                        id, 
                        full_name, 
                        email, 
                        is_admin, 
                        created_at,
                        is_active
                        FROM users
                        ORDER BY created_at DESC
                        LIMIT 5";

                    $result = mysqli_query($conn, $query);
                    ?>
                    <div class="bg-white rounded-lg shadow lg:col-span-2">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="font-semibold text-lg">Recent Users</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                        <?php
                                        // Format joined date
                                        $joinedDate = date('d M Y', strtotime($user['created_at']));

                                        // Get initials for avatar
                                        $names = explode(' ', $user['full_name']);
                                        $initials = '';
                                        foreach ($names as $name) {
                                            $initials .= strtoupper(substr($name, 0, 1));
                                        }
                                        $initials = substr($initials, 0, 2);

                                        // Set avatar color based on user type
                                        $avatarColors = [
                                            'admin' => 'bg-yellow-100 text-yellow-600',
                                            'user' => 'bg-blue-100 text-blue-600'
                                        ];
                                        $avatarClass = $user['is_admin'] ? $avatarColors['admin'] : $avatarColors['user'];

                                        // User status
                                        $statusText = $user['is_active'] ? 'Active' : 'Inactive';
                                        $statusClass = $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                        ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 rounded-full <?= $avatarClass ?> flex items-center justify-center mr-3">
                                                        <span><?= $initials ?></span>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium"><?= htmlspecialchars($user['full_name']) ?></p>
                                                        <p class="text-gray-500 text-sm"><?= $user['is_admin'] ? 'Admin' : 'User' ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['email']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?= $joinedDate ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full <?= $statusClass ?>">
                                                    <?= $statusText ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="edit_user.php?id=<?= $user['id'] ?>" class="text-blue-500 hover:text-blue-700 mr-2">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="text-gray-500 hover:text-gray-700">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <?php if (mysqli_num_rows($result) == 0): ?>
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No users found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-gray-200 text-right">
                            <a href="viewAllUsers.php" class="text-primary hover:underline">View All Users</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');

            if (window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                event.target !== sidebarToggle &&
                !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });
    </script>
</body>

</html>