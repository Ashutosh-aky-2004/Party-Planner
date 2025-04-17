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

// Get hotel ID from URL
$hotel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch hotel details
$hotelQuery = "SELECT * FROM hotels WHERE id = $hotel_id";
$hotelResult = mysqli_query($conn, $hotelQuery);
$hotel = mysqli_fetch_assoc($hotelResult);

if (!$hotel) {
    header("Location: hotels.php");
    exit();
}

// Fetch hotel amenities
$amenitiesQuery = "SELECT amenity FROM hotel_amenities WHERE hotel_id = $hotel_id";
$amenitiesResult = mysqli_query($conn, $amenitiesQuery);
$amenities = [];
while ($row = mysqli_fetch_assoc($amenitiesResult)) {
    $amenities[] = $row['amenity'];
}

// Generate star rating display
$stars = str_repeat('★', $hotel['rating_score']);
$emptyStars = str_repeat('☆', 5 - $hotel['rating_score']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($hotel['name']) ?> - Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="favicon (1).png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .amenity-tag {
            display: inline-block;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .hotel-image {
            height: 400px;
            object-fit: cover;
            width: 100%;
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
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Hotel Details</h1>
                <a href="hotelData.php" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Hotels
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Hotel Image -->
                <div class="relative">
                    <img src="/final/src/<?= htmlspecialchars($hotel['image_path']) ?>"
                        alt="<?= htmlspecialchars($hotel['name']) ?>"
                        class="hotel-image">
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6">
                        <h2 class="text-3xl font-bold text-white"><?= htmlspecialchars($hotel['name']) ?></h2>
                        <div class="flex items-center mt-2">
                            <div class="text-yellow-300 text-xl"><?= $stars . $emptyStars ?></div>
                            <span class="ml-2 text-white">(<?= number_format($hotel['rating_score'], 1) ?>/5)</span>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Left Column -->
                        <div>
                            <div class="mb-6">
                                <h3 class="text-xl font-semibold mb-4">Basic Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-gray-500">Address</p>
                                        <p class="text-gray-800"><?= htmlspecialchars($hotel['address']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Location</p>
                                        <p class="text-gray-800"><?= htmlspecialchars($hotel['city']) ?>, <?= htmlspecialchars($hotel['country']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Contact</p>
                                        <p class="text-gray-800"><?= htmlspecialchars($hotel['phone']) ?></p>
                                        <p class="text-gray-800"><?= htmlspecialchars($hotel['email']) ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h3 class="text-xl font-semibold mb-4">Pricing & Capacity</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-gray-500">Price per night</p>
                                        <p class="text-2xl font-bold text-blue-600">₹<?= number_format($hotel['amount_per_night'], 2) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Capacity</p>
                                        <p class="text-2xl font-bold text-blue-600"><?= $hotel['capacity'] ?> persons</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Min booking days</p>
                                        <p class="text-xl font-semibold"><?= $hotel['min_booking_days'] ?> days</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Max booking days</p>
                                        <p class="text-xl font-semibold"><?= $hotel['max_booking_days'] ?> days</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div>
                            <div class="mb-6">
                                <h3 class="text-xl font-semibold mb-4">Amenities</h3>
                                <?php if (!empty($amenities)): ?>
                                    <div class="flex flex-wrap">
                                        <?php foreach ($amenities as $amenity): ?>
                                            <span class="amenity-tag bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                <?= htmlspecialchars($amenity) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-gray-500">No amenities listed for this hotel.</p>
                                <?php endif; ?>
                            </div>

                            <div class="mb-6">
                                <h3 class="text-xl font-semibold mb-4">Status</h3>
                                <div class="flex items-center">
                                    <?php if ($hotel['is_active']): ?>
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                            <i class="fas fa-check-circle mr-1"></i> Active (Accepting bookings)
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                                            <i class="fas fa-times-circle mr-1"></i> Inactive (Not accepting bookings)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h3 class="text-xl font-semibold mb-4">Additional Information</h3>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-gray-500">Created on</p>
                                    <p><?= date('F j, Y, g:i a', strtotime($hotel['created_at'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-between">
                        <a href="edit_hotel.php?id=<?= $hotel['id'] ?>"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                            <i class="fas fa-edit mr-2"></i> Edit Hotel
                        </a>
                        <button onclick="confirmDelete(<?= $hotel['id'] ?>)"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md">
                            <i class="fas fa-trash mr-2"></i> Delete Hotel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(hotelId) {
            if (confirm('Are you sure you want to delete this hotel? All related bookings and amenities will also be deleted.')) {
                window.location.href = 'delete_hotel.php?id=' + hotelId;
            }
        }
    </script>
</body>

</html>

<?php
mysqli_close($conn);
?>