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

// Fetch all hotels with their amenities
$query = "SELECT 
            h.id,
            h.name,
            h.address,
            h.city,
            h.country,
            h.capacity,
            h.amount_per_night,
            h.image_path,
            h.rating_score,
            GROUP_CONCAT(ha.amenity SEPARATOR ', ') as amenities
          FROM hotels h
          LEFT JOIN hotel_amenities ha ON h.id = ha.hotel_id
          GROUP BY h.id
          ORDER BY h.name";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Hotels</title>
    <link rel="icon" type="image/png" href="favicon (1).png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .amenity-tag {
            display: inline-block;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
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
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">All Hotels</h1>
            <a href="add_hotel.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i> Add New Hotel
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['success']);
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($hotel = mysqli_fetch_assoc($result)): ?>
                <?php
                // Generate star rating
                $stars = str_repeat('★', $hotel['rating_score']);
                $emptyStars = str_repeat('☆', 5 - $hotel['rating_score']);
                ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hotel-card transition-all duration-300">
                    <div class="relative h-48 overflow-hidden">
                        <img src="<?php echo "/final/src/" . htmlspecialchars($hotel['image_path']); ?>"
                            alt="<?= htmlspecialchars($hotel['name']) ?>"
                            class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                            <h3 class="text-xl font-bold text-white"><?= htmlspecialchars($hotel['name']) ?></h3>
                            <div class="text-yellow-300"><?= $stars . $emptyStars ?></div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-gray-600">
                                    <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                    <?= htmlspecialchars($hotel['city']) ?>, <?= htmlspecialchars($hotel['country']) ?>
                                </p>
                                <p class="text-gray-500 text-sm mt-1">
                                    <i class="fas fa-users mr-2"></i>
                                    Capacity: <?= $hotel['capacity'] ?> persons
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-blue-600">
                                    ₹<?= number_format($hotel['amount_per_night']) ?>
                                </p>
                                <p class="text-gray-500 text-sm">per night</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4 class="font-semibold mb-2">Address:</h4>
                            <p class="text-gray-600"><?= htmlspecialchars($hotel['address']) ?></p>
                        </div>

                        <?php if (!empty($hotel['amenities'])): ?>
                            <div class="mb-4">
                                <h4 class="font-semibold mb-2">Amenities:</h4>
                                <div>
                                    <?php
                                    $amenities = explode(', ', $hotel['amenities']);
                                    foreach ($amenities as $amenity):
                                    ?>
                                        <span class="amenity-tag bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                            <?= htmlspecialchars($amenity) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between pt-4 border-t border-gray-200">
                            <a href="edit_hotel.php?id=<?= $hotel['id'] ?>"
                                class="text-blue-500 hover:text-blue-700">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <a href="view_hotel.php?id=<?= $hotel['id'] ?>"
                                class="text-green-500 hover:text-green-700">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                            <a href="delete_hotel.php?id=<?= $hotel['id'] ?>"
                                onclick="return confirm('Are you sure you want to delete this hotel? All related data will be deleted.') || event.preventDefault()"
                                class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if (mysqli_num_rows($result) == 0): ?>
                <div class="col-span-full text-center py-8">
                    <i class="fas fa-hotel text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No hotels found</p>
                    <a href="add_hotel.php" class="text-blue-500 hover:underline mt-2 inline-block">
                        Add your first hotel
                    </a>
                </div>
            <?php endif; ?>
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
// Close database connection
mysqli_close($conn);
?>