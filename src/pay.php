<?php
session_start();
require 'database/dbConnect.php';
$hotel_id = $_SESSION['hotel_id'];
// Get all booking data from session
$booking = $_SESSION['booking_form'] ?? [];
$total_amount = $_POST['total_amount'] ?? $booking['total_amount'] ?? 0;

// Process payment when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    // Insert into bookings table
    $query = "INSERT INTO bookings (
        user_id, 
        hotel_id, 
        event_type, 
        check_in_date, 
        check_out_date, 
        no_of_persons, 
        total_cost, 
        status
    ) VALUES (
        '{$_SESSION['user_id']}',
        '{$hotel_id}',
        '{$booking['event_type']}',
        '{$booking['check_in']}',
        '{$booking['check_out']}',
        '{$booking['noofperson']}',
        '$total_amount',
        'pending'
    )";

    mysqli_query($conn, $query);

    // Redirect to success page
    header("Location: success.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black text-white">
    <?php require 'essentials/header.php'; ?>

    <div class="container mx-auto mt-10">
        <form method="POST" class="max-w-xl mx-auto bg-gray-900 p-8 rounded-lg shadow-lg border border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Billing Address</h3>
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-400">Full Name:</label>
                        <input type="text" name="name" id="name" value="<?= htmlspecialchars($booking['full_name'] ?? '') ?>" required
                            class="mt-1 block w-full bg-gray-800 text-white border-gray-600 rounded-md shadow-sm focus:ring-white focus:border-white">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-400">Email:</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($booking['email'] ?? '') ?>" required
                            class="mt-1 block w-full bg-gray-800 text-white border-gray-600 rounded-md shadow-sm focus:ring-white focus:border-white">
                    </div>
                    <div class="mb-4">
                        <label for="address" class="block text-sm font-medium text-gray-400">Address:</label>
                        <input type="text" name="address" id="address" placeholder="Enter your address" required
                            class="mt-1 block w-full bg-gray-800 text-white border-gray-600 rounded-md shadow-sm focus:ring-white focus:border-white">
                    </div>
                    <div class="mb-4">
                        <label for="city" class="block text-sm font-medium text-gray-400">City:</label>
                        <input type="text" name="city" id="city" placeholder="Enter your city" required
                            class="mt-1 block w-full bg-gray-800 text-white border-gray-600 rounded-md shadow-sm focus:ring-white focus:border-white">
                    </div>
                    <div class="mb-4">
                        <label for="state" class="block text-sm font-medium text-gray-400">State:</label>
                        <input type="text" name="state" id="state" placeholder="Enter your state" required
                            class="mt-1 block w-full bg-gray-800 text-white border-gray-600 rounded-md shadow-sm focus:ring-white focus:border-white">
                    </div>
                    <div class="mb-4">
                        <label for="zip" class="block text-sm font-medium text-gray-400">Zip Code:</label>
                        <input type="text" name="zip" id="zip" placeholder="Enter your zip code" required
                            class="mt-1 block w-full bg-gray-800 text-white border-gray-600 rounded-md shadow-sm focus:ring-white focus:border-white">
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Payment</h3>
                    <div class="mb-4">
                        <label for="cardName" class="block text-sm font-medium text-gray-400">Name on Card:</label>
                        <input type="text" name="cardName" id="cardName" placeholder="Enter card name" required
                            class="mt-1 block w-full bg-gray-800 text-white border-gray-600 rounded-md shadow-sm focus:ring-white focus:border-white">
                    </div>
                    <div class="mb-4">
                        <label for="cardNumber" class="block text-sm font-medium text-gray-400">Credit Card Number:</label>
                        <input type="text" name="cardNumber" id="cardNumber" placeholder="Enter card number" required
                            class="mt-1 block w-full bg-gray-800 text-white border-gray-600 rounded-md shadow-sm focus:ring-white focus:border-white">
                    </div>
                    <div class="flex justify-between mb-4">
                        <div class="w-1/2 mr-2">
                            <label for="expMonth" class="block text-sm font-medium text-gray-400">Expiration Month:</label>
                            <input type="text" name="expMonth" id="expMonth" placeholder="MM" required
                                class="mt-1 block w-full bg-gray-800 text-white border-gray-600 rounded-md shadow-sm focus:ring-white focus:border-white">
                        </div>
                        <div class="w-1/2 ml-2">
                            <label for="expYear" class="block text-sm font-medium text-gray-400">Expiration Year:</label>
                            <input type="text" name="expYear" id="expYear" placeholder="YYYY" required
                                class="mt-1 block w-full bg-gray-800 text-white border-gray-600 rounded-md shadow-sm focus:ring-white focus:border-white">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="cvv" class="block text-sm font-medium text-gray-400">CVV:</label>
                        <input type="text" name="cvv" id="cvv" placeholder="Enter CVV" required
                            class="mt-1 block w-full bg-gray-800 text-white border-gray-600 rounded-md shadow-sm focus:ring-white focus:border-white">
                    </div>

                    <!-- Display total amount -->
                    <div class="mb-4 p-4 bg-gray-800 rounded-lg">
                        <p class="text-lg font-semibold">Total Amount: <span class="text-yellow-400">₹<?= number_format($total_amount, 2) ?></span></p>
                        <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
                    </div>
                </div>
            </div>
            <button type="submit" name="process_payment" id="checkoutBtn"
                class="mt-6 px-4 py-2 w-full bg-white text-black font-semibold rounded-md hover:bg-gray-300 transition">
                Proceed to Checkout
            </button>
        </form>
    </div>
    <?php require 'essentials/footer.html'; ?>
</body>

</html>