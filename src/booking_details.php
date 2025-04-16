<?php
session_start();
require 'database/dbConnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Fetch booking details with hotel and user information
$booking = [];
$query = "SELECT b.*, 
          h.name as hotel_name, h.address as hotel_address, h.city as hotel_city, 
          h.country as hotel_country, h.phone as hotel_phone, h.email as hotel_email,
          h.image_path as hotel_image, h.amount_per_night,
          u.full_name, u.email as user_email, u.phone as user_phone
          FROM bookings b
          JOIN hotels h ON b.hotel_id = h.id
          JOIN users u ON b.user_id = u.id
          WHERE b.id = $booking_id AND b.user_id = $user_id";

$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $booking = mysqli_fetch_assoc($result);
} else {
    header("Location: profile.php?tab=bookings");
    exit();
}

// Calculate duration of stay
$check_in = new DateTime($booking['check_in_date']);
$check_out = new DateTime($booking['check_out_date']);
$duration = $check_in->diff($check_out)->days;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - <?= htmlspecialchars($booking['hotel_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <?php require 'essentials/header.php' ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Back button -->
            <a href="profile.php?tab=bookings" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6">
                <i class="fas fa-arrow-left mr-2"></i> Back to My Bookings
            </a>

            <!-- Booking Header -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div>
                            <h1 class="text-2xl font-bold"><?= htmlspecialchars($booking['hotel_name']) ?></h1>
                            <p class="text-blue-100 mt-1">
                                Booking ID: #<?= $booking['id'] ?>
                            </p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold 
                                <?= $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : ($booking['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : ($booking['status'] === 'completed' ? 'bg-blue-100 text-blue-800' :
                                            'bg-yellow-100 text-yellow-800')) ?>">
                                <?= ucfirst($booking['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Hotel Image -->
                <?php if (!empty($booking['hotel_image'])): ?>
                    <img src="<?= htmlspecialchars($booking['hotel_image']) ?>" alt="<?= htmlspecialchars($booking['hotel_name']) ?>" class="w-full h-64 object-cover">
                <?php else: ?>
                    <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-hotel text-6xl text-gray-400"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Booking Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Booking Summary -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">Booking Summary</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-gray-500 text-sm">Booking Date</p>
                            <p><?= date('F j, Y', strtotime($booking['created_at'])) ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Check-in Date</p>
                            <p><?= date('F j, Y', strtotime($booking['check_in_date'])) ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Check-out Date</p>
                            <p><?= date('F j, Y', strtotime($booking['check_out_date'])) ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Duration</p>
                            <p><?= $duration ?> night<?= $duration > 1 ? 's' : '' ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Guests</p>
                            <p><?= $booking['no_of_persons'] ?> person<?= $booking['no_of_persons'] > 1 ? 's' : '' ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Event Type</p>
                            <p><?= htmlspecialchars($booking['event_type']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">Price Breakdown</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?= $duration ?> night<?= $duration > 1 ? 's' : '' ?> × ₹<?= number_format($booking['amount_per_night'], 2) ?></span>
                            <span>₹<?= number_format($booking['amount_per_night'] * $duration, 2) ?></span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="font-semibold">Total</span>
                            <span class="font-bold text-lg">₹<?= number_format($booking['total_cost'], 2) ?></span>
                        </div>
                        <div class="pt-4 text-sm text-gray-500">
                            <p><i class="fas fa-info-circle mr-1"></i> Includes all applicable taxes</p>
                        </div>
                    </div>
                </div>

                <!-- Hotel Contact -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">Hotel Contact</h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-gray-500 text-sm">Address</p>
                            <p><?= htmlspecialchars($booking['hotel_address']) ?><br>
                                <?= htmlspecialchars($booking['hotel_city']) ?>, <?= htmlspecialchars($booking['hotel_country']) ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Phone</p>
                            <p><?= htmlspecialchars($booking['hotel_phone']) ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Email</p>
                            <p><?= htmlspecialchars($booking['hotel_email']) ?></p>
                        </div>
                        <div class="pt-4">
                            <a href="tel:<?= htmlspecialchars($booking['hotel_phone']) ?>" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                <i class="fas fa-phone-alt mr-2"></i> Call Hotel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Information -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2">Your Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-500 text-sm">Full Name</p>
                        <p><?= htmlspecialchars($booking['full_name']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Email</p>
                        <p><?= htmlspecialchars($booking['user_email']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Phone</p>
                        <p><?= htmlspecialchars($booking['user_phone']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Admin Notes (if available) -->
            <?php if (!empty($booking['admin_notes'])): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-yellow-400 mt-1 mr-3"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-yellow-800">Note from Hotel</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p><?= htmlspecialchars($booking['admin_notes']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex flex-col sm:flex-row justify-between items-center">
                    <div class="mb-4 sm:mb-0">
                        <p class="text-gray-500">Need help with your booking?</p>
                        <a href="contact.php" class="text-blue-600 hover:text-blue-800">Contact our support team</a>
                    </div>
                    <?php if ($booking['status'] === 'confirmed'): ?>
                        <button onclick="confirmCancel()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            Cancel Booking
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmCancel() {
            if (confirm("Are you sure you want to cancel this booking?")) {
                window.location.href = "cancel_booking.php?id=<?= $booking['id'] ?>";
            }
        }
    </script>

    <?php require 'essentials/footer.html' ?>
</body>

</html>

<?php mysqli_close($conn); ?>