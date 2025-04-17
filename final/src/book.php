<?php
require 'database/dbConnect.php';
session_start();

$_SESSION['hotel_id'] = intval($_GET['hotel_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['proceed_to_payment'])) {
    $_SESSION['booking_form'] = [
        'check_in' => $_POST['check_in'] ?? '',
        'check_out' => $_POST['check_out'] ?? '',
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'noofperson' => $_POST['noofperson'] ?? 1,
        'event_type' => $_POST['event_type'] ?? 'Wedding',
        'selected_food' => $_POST['selected_food'] ?? '[]',
        'selected_items' => $_POST['selected_items'] ?? '[]'
    ];

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get hotel ID from URL
if (!isset($_GET['hotel_id'])) {
    header("Location: index.php");
    exit();
}

$hotel_id = mysqli_real_escape_string($conn, $_GET['hotel_id']);

// Fetch hotel details
$hotel_query = "SELECT * FROM hotels WHERE id = '$hotel_id'";
$hotel_result = mysqli_query($conn, $hotel_query);

if (mysqli_num_rows($hotel_result) === 0) {
    header("Location: index.php");
    exit();
}

$hotel = mysqli_fetch_assoc($hotel_result);

// Fetch amenities
$amenities_query = "SELECT amenity FROM hotel_amenities WHERE hotel_id = '$hotel_id'";
$amenities_result = mysqli_query($conn, $amenities_query);
$amenities_html = '';
while ($amenity = mysqli_fetch_assoc($amenities_result)) {
    $amenities_html .= '<span class="bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-yellow-900 dark:text-yellow-300">'
        . htmlspecialchars($amenity['amenity'])
        . '</span>';
}

// Fetch food items
$food_query = "SELECT * FROM food_items";
$food_result = mysqli_query($conn, $food_query);

// Fetch additional items
$items_query = "SELECT * FROM additional_items";
$items_result = mysqli_query($conn, $items_query);

$rating_stars = str_repeat('⭐', $hotel['rating_score']);

// Retrieve saved form data from session
$formData = $_SESSION['booking_form'] ?? [
    'check_in' => '',
    'check_out' => '',
    'full_name' => isset($_SESSION['name']) ? $_SESSION['name'] : '',
    'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '',
    'phone' => isset($_SESSION['phone']) ? $_SESSION['phone'] : '',
    'noofperson' => 1,
    'event_type' => 'Wedding',
    'selected_food' => '[]',
    'selected_items' => '[]'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking</title>
    <?php require 'essentials/commonScript.html'; ?>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .scroll-container {
            scrollbar-width: thin;
            scrollbar-color: #4B5563 #1F2937;
        }

        .scroll-container::-webkit-scrollbar {
            height: 8px;
        }

        .scroll-container::-webkit-scrollbar-track {
            background: #1F2937;
        }

        .scroll-container::-webkit-scrollbar-thumb {
            background-color: #4B5563;
            border-radius: 4px;
        }
    </style>
</head>

<body class="w-full h-full bg-black text-white">
    <?php require 'essentials/header.php'; ?>

    <div class="container mx-auto py-10 px-6">
        <h1 class="text-4xl font-bold mb-8 border-b-2 border-white pb-2">Hotel Details</h1>

        <!-- Hotel Details Card -->
        <div class="w-full max-w-7xl mx-auto bg-black shadow-lg rounded-xl overflow-hidden border border-gray-800 mb-10">
            <div class="md:flex">
                <div class="md:w-1/2 overflow-hidden">
                    <img class="w-full h-64 object-cover md:h-full filter grayscale-50 transform origin-top-left transition-all duration-300 hover:scale-[1.05] hover:-translate-x-1 hover:-translate-y-1"
                        src="<?= htmlspecialchars($hotel['image_path']) ?>" alt="Hotel Image">
                </div>
                <div class="p-6 md:w-1/2 text-white">
                    <h2 class="text-3xl font-bold"><?= htmlspecialchars($hotel['name']) ?></h2>
                    <p class="text-gray-300 mt-1"><?= htmlspecialchars($hotel['address']) ?>, <?= htmlspecialchars($hotel['city']) ?>, <?= htmlspecialchars($hotel['country']) ?></p>
                    <p class="text-lg font-semibold mt-3">Rating: <span class="text-gray-400"><?= $rating_stars ?></span></p>
                    <p class="text-lg font-semibold mt-2">Amenities:</p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <?= $amenities_html ?>
                    </div>
                    <p class="text-lg font-semibold mt-2">Capacity: <span class="text-gray-300"><?= htmlspecialchars($hotel['capacity']) ?></span></p>
                    <p class="text-xl font-bold text-white mt-2">Cost: ₹<?= number_format($hotel['amount_per_night'], 2) ?></p>
                </div>
            </div>
        </div>

        <h1 class="text-4xl font-bold mb-6 border-b-2 border-white pb-2">Booking Details</h1>

        <div class="flex flex-col lg:flex-row gap-10">
            <!-- Booking Form -->
            <div class="w-full lg:w-3/5 bg-black text-white p-6 rounded-lg shadow-md border border-gray-300">
                <h2 class="text-2xl font-bold mb-4">Enter Booking Details:</h2>
                <form id="eventForm" action="" method="POST" class="space-y-4">
                    <input type="hidden" name="hotel_id" value="<?php echo $hotel_id ?>">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id'] ?? '' ?>">

                    <!-- Date Selection -->
                    <div>
                        <label class="block font-semibold">Check-in Date</label>
                        <input type="date" name="check_in" id="check_in" value="<?= htmlspecialchars($formData['check_in']) ?>"
                            class="w-full border border-gray-400 rounded-lg p-2 bg-gray-900"
                            min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div>
                        <label class="block font-semibold">Check-out Date</label>
                        <input type="date" name="check_out" id="check_out" value="<?= htmlspecialchars($formData['check_out']) ?>"
                            class="w-full border border-gray-400 rounded-lg p-2 bg-gray-900"
                            min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>

                    <!-- Personal Details -->
                    <div>
                        <label class="block font-semibold">Full Name</label>
                        <input type="text" name="full_name" id="full_name" value="<?= htmlspecialchars($formData['full_name']) ?>"
                            class="w-full border border-gray-400 rounded-lg p-2 bg-gray-900" required>
                    </div>
                    <div>
                        <label class="block font-semibold">Email Address</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($formData['email']) ?>"
                            class="w-full border border-gray-400 rounded-lg p-2 bg-gray-900" required>
                    </div>
                    <div>
                        <label class="block font-semibold">Phone Number</label>
                        <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($formData['phone']) ?>"
                            class="w-full border border-gray-400 rounded-lg p-2 bg-gray-900" required>
                    </div>

                    <!-- Event Details -->
                    <div>
                        <label class="block font-semibold">Number of Guests</label>
                        <input type="number" name="noofperson" id="noofperson" min="1" max="<?= $hotel['capacity'] ?>"
                            value="<?= htmlspecialchars($formData['noofperson']) ?>"
                            class="w-full border border-gray-400 rounded-lg p-2 bg-gray-900" required>
                        <p class="text-sm text-gray-400 mt-1">Maximum capacity: <?= $hotel['capacity'] ?> persons</p>
                    </div>
                    <div>
                        <label class="block font-semibold">Type of Event</label>
                        <select name="event_type" id="event_type"
                            class="w-full border border-gray-400 rounded-lg p-2 bg-gray-900">
                            <?php
                            $event_types = ["Wedding", "Birthday", "Anniversary", "Corporate", "Social", "Other"];
                            foreach ($event_types as $type) {
                                $selected = ($formData['event_type'] === $type) ? 'selected' : '';
                                echo "<option value=\"$type\" $selected>$type</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Hidden fields for selected items -->
                    <input type="hidden" name="selected_food" id="selected_food" value="<?= htmlspecialchars($formData['selected_food']) ?>">
                    <input type="hidden" name="selected_items" id="selected_items" value="<?= htmlspecialchars($formData['selected_items']) ?>">

                    <button type="submit"
                        class="w-full bg-black text-white p-2 rounded-lg border border-white hover:bg-white hover:border-black hover:border-2 hover:text-black transition-all">
                        Confirm Booking
                    </button>
                </form>
            </div>

            <!-- Summary Card -->
            <div class="w-full lg:w-2/5 bg-black text-white p-6 rounded-lg shadow-md border border-gray-300 sticky top-4">
                <h2 class="text-2xl font-bold bg-black text-white p-2 rounded-lg">Booking Summary</h2>

                <!-- Hotel Summary -->
                <div class="py-2 border-b border-gray-400">
                    <h3 class="font-semibold"><?= htmlspecialchars($hotel['name']) ?></h3>
                    <div class="flex justify-between mt-1">
                        <span class="text-gray-300"><?= $hotel['city'] ?>, <?= $hotel['country'] ?></span>
                        <span>₹<?= number_format($hotel['amount_per_night'], 2) ?>/night</span>
                    </div>
                </div>

                <!-- Selected Items -->
                <div id="totalAmountSection" class="py-2 space-y-2">
                    <div class="text-center text-gray-400 py-4" id="emptyMessage">
                        No items selected yet
                    </div>
                </div>

                <!-- Total -->
                <div class="flex justify-between mt-4 p-3 bg-gray-900 text-white rounded-lg border border-gray-700">
                    <span class="font-bold">Total:</span>
                    <span class="font-bold">₹<span id="total">0.00</span></span>
                </div>

                <button id="proceedToPay" onclick="preparePayment()" disabled
                    class="w-full mt-4 bg-gray-700 text-gray-400 p-2 rounded-lg border border-gray-600 cursor-not-allowed">
                    Proceed to Payment
                </button>
            </div>
        </div>

        <!-- Food Items Section -->
        <div class="mt-10">
            <h2 class="text-2xl font-bold mb-4 border-b-2 border-white pb-2">Choose Food:</h2>
            <div class="relative">
                <div class="flex overflow-x-auto py-4 space-x-4 scroll-smooth scroll-container" id="foodQueue">
                    <?php
                    mysqli_data_seek($food_result, 0);
                    while ($food = mysqli_fetch_assoc($food_result)):
                    ?>
                        <div class="flex-shrink-0 w-64 bg-gray-800 rounded-lg p-4 border-l-4 border-yellow-400 transition-all hover:shadow-lg hover:shadow-yellow-400/20">
                            <img src="<?= htmlspecialchars($food['image_path']) ?>"
                                alt="<?= htmlspecialchars($food['name']) ?>"
                                class="w-full h-40 object-cover rounded-lg mb-3">
                            <h3 class="text-lg font-bold"><?= htmlspecialchars($food['name']) ?></h3>
                            <p class="text-yellow-400 mb-3">₹<?= number_format($food['cost_per_head'], 2) ?> per person</p>
                            <button onclick="addFoodItem(<?= $food['id'] ?>, '<?= htmlspecialchars(addslashes($food['name'])) ?>', <?= $food['cost_per_head'] ?>)"
                                class="w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-2 px-4 rounded-lg transition-colors">
                                Add to Booking
                            </button>
                        </div>
                    <?php endwhile; ?>
                </div>
                <button onclick="scrollQueue('foodQueue', -300)"
                    class="absolute left-0 top-1/2 -translate-y-1/2 bg-black bg-opacity-70 text-white p-3 rounded-full hover:bg-opacity-100 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button onclick="scrollQueue('foodQueue', 300)"
                    class="absolute right-0 top-1/2 -translate-y-1/2 bg-black bg-opacity-70 text-white p-3 rounded-full hover:bg-opacity-100 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Additional Items Section -->
        <div class="mt-10">
            <h2 class="text-2xl font-bold mb-4 border-b-2 border-white pb-2">More Items:</h2>
            <div class="relative">
                <div class="flex overflow-x-auto py-4 space-x-4 scroll-smooth scroll-container" id="itemsQueue">
                    <?php
                    mysqli_data_seek($items_result, 0);
                    while ($item = mysqli_fetch_assoc($items_result)):
                    ?>
                        <div class="flex-shrink-0 w-64 bg-gray-800 rounded-lg p-4 border-l-4 border-purple-400 transition-all hover:shadow-lg hover:shadow-purple-400/20">
                            <img src="<?= htmlspecialchars($item['image_path']) ?>"
                                alt="<?= htmlspecialchars($item['name']) ?>"
                                class="w-full h-40 object-cover rounded-lg mb-3">
                            <h3 class="text-lg font-bold"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="text-purple-400 mb-3">₹<?= number_format($item['cost_per_item'], 2) ?></p>
                            <button onclick="addExtraItem(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>', <?= $item['cost_per_item'] ?>)"
                                class="w-full bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                Add to Booking
                            </button>
                        </div>
                    <?php endwhile; ?>
                </div>
                <button onclick="scrollQueue('itemsQueue', -300)"
                    class="absolute left-0 top-1/2 -translate-y-1/2 bg-black bg-opacity-70 text-white p-3 rounded-full hover:bg-opacity-100 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button onclick="scrollQueue('itemsQueue', 300)"
                    class="absolute right-0 top-1/2 -translate-y-1/2 bg-black bg-opacity-70 text-white p-3 rounded-full hover:bg-opacity-100 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        let selectedFoods = <?= $formData['selected_food'] ?: '[]' ?>;
        let selectedExtras = <?= $formData['selected_items'] ?: '[]' ?>;
        let totalAmount = 0;

        // Add this to calculate duration when dates change
        document.getElementById('check_in').addEventListener('change', calculateDuration);
        document.getElementById('check_out').addEventListener('change', calculateDuration);

        function calculateDuration() {
            const checkIn = new Date(document.getElementById('check_in').value);
            const checkOut = new Date(document.getElementById('check_out').value);

            if (checkIn && checkOut && checkOut > checkIn) {
                const duration = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
                return duration;
            }
            return 0;
        }
        // Update when guest count changes
        document.getElementById('noofperson').addEventListener('change', function() {
            const guestCount = parseInt(this.value) || 1;

            // Recalculate prices for all items
            selectedFoods = selectedFoods.map(item => ({
                ...item,
                totalPrice: item.pricePerHead * guestCount
            }));

            selectedExtras = selectedExtras.map(item => ({
                ...item,
                totalPrice: item.costPerUnit * guestCount
            }));

            // Update hidden fields
            document.getElementById('selected_food').value = JSON.stringify(selectedFoods);
            document.getElementById('selected_items').value = JSON.stringify(selectedExtras);

            // Update booking summary
            updateBookingSummary();
        });

        function addFoodItem(id, name, pricePerHead) {
            const guestCount = parseInt(document.getElementById('noofperson').value) || 1;
            const totalPrice = pricePerHead * guestCount;

            const existingIndex = selectedFoods.findIndex(item => item.id === id);
            if (existingIndex === -1) {
                selectedFoods.push({
                    id,
                    name,
                    pricePerHead,
                    totalPrice
                });
            } else {
                selectedFoods[existingIndex].totalPrice = totalPrice;
            }

            document.getElementById('selected_food').value = JSON.stringify(selectedFoods);
            updateBookingSummary();
        }

        function addExtraItem(id, name, costPerUnit) {
            const guestCount = parseInt(document.getElementById('noofperson').value) || 1;
            const totalPrice = costPerUnit * guestCount;

            const existingIndex = selectedExtras.findIndex(item => item.id === id);
            if (existingIndex === -1) {
                selectedExtras.push({
                    id,
                    name,
                    costPerUnit,
                    totalPrice
                });
            } else {
                selectedExtras[existingIndex].totalPrice = totalPrice;
            }

            document.getElementById('selected_items').value = JSON.stringify(selectedExtras);
            updateBookingSummary();
        }

        // In your updateBookingSummary function, remove the button disable logic:
        function updateBookingSummary() {
            const summaryDiv = document.getElementById('totalAmountSection');
            const totalEl = document.getElementById('total');
            const emptyMsg = document.getElementById('emptyMessage');
            const payBtn = document.getElementById('proceedToPay');
            const guestCount = parseInt(document.getElementById('noofperson').value) || 1;
            const duration = calculateDuration();

            summaryDiv.innerHTML = '';

            // Calculate hotel cost based on duration
            const hotelNightlyRate = <?= $hotel['amount_per_night'] ?>;
            const hotelCost = hotelNightlyRate * duration;
            totalAmount = hotelCost;

            // Add hotel cost to summary
            summaryDiv.innerHTML += `
        <div class="flex justify-between bg-gray-800 px-4 py-2 rounded-lg border border-gray-600">
            <span>${duration} night(s) @ ₹${hotelNightlyRate.toFixed(2)}/night</span>
            <span>₹${hotelCost.toFixed(2)}</span>
        </div>
    `;

            // Process food items (existing code)
            selectedFoods.forEach(item => {
                totalAmount += item.totalPrice;
                summaryDiv.innerHTML += `
            <div class="flex justify-between bg-gray-800 px-4 py-2 rounded-lg border border-gray-600">
                <span>${item.name} x ₹${item.pricePerHead} (${guestCount} guests)</span>
                <span>₹${item.totalPrice.toFixed(2)}</span>
            </div>
        `;
            });

            // Process additional items (existing code)
            selectedExtras.forEach(item => {
                totalAmount += item.totalPrice;
                summaryDiv.innerHTML += `
            <div class="flex justify-between bg-gray-800 px-4 py-2 rounded-lg border border-gray-600">
                <span>${item.name} x ₹${item.costPerUnit} (${guestCount} guests)</span>
                <span>₹${item.totalPrice.toFixed(2)}</span>
            </div>
        `;
            });

            // Update UI states
            totalEl.textContent = totalAmount.toFixed(2);

            // Enable/disable payment button based on valid duration
            if (duration > 0) {
                payBtn.disabled = false;
                payBtn.classList.remove("bg-gray-700", "text-gray-400", "cursor-not-allowed");
                payBtn.classList.add("bg-yellow-500", "hover:bg-yellow-600", "text-black");
            } else {
                payBtn.disabled = true;
                payBtn.classList.remove("bg-yellow-500", "hover:bg-yellow-600", "text-black");
                payBtn.classList.add("bg-gray-700", "text-gray-400", "cursor-not-allowed");
            }

            emptyMsg.style.display = (selectedFoods.length + selectedExtras.length) > 0 || duration > 0 ? "none" : "block";
        }

        function preparePayment() {
            // First update the hidden fields with current selections
            const duration = calculateDuration();
            if (duration <= 0) {
                alert("Please select valid check-in and check-out dates");
                return;
            }

            // First update the hidden fields with current selections
            document.getElementById('selected_food').value = JSON.stringify(selectedFoods);
            document.getElementById('selected_items').value = JSON.stringify(selectedExtras);

            // Calculate total amount with duration
            const guestCount = parseInt(document.getElementById('noofperson').value) || 1;
            let total = <?= $hotel['amount_per_night'] ?> * duration; // Base hotel cost

            // Add food items
            selectedFoods.forEach(item => {
                total += item.pricePerHead * guestCount;
            });

            // Add additional items
            selectedExtras.forEach(item => {
                total += item.costPerUnit * guestCount;
            });

            // Create a form to submit to pay.php
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'pay.php';

            // Add all necessary data
            const addHiddenField = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            };

            addHiddenField('check_in', document.getElementById('check_in').value);
            addHiddenField('check_out', document.getElementById('check_out').value);
            addHiddenField('full_name', document.getElementById('full_name').value);
            addHiddenField('email', document.getElementById('email').value);
            addHiddenField('phone', document.getElementById('phone').value);
            addHiddenField('noofperson', guestCount);
            addHiddenField('event_type', document.getElementById('event_type').value);
            addHiddenField('selected_food', document.getElementById('selected_food').value);
            addHiddenField('selected_items', document.getElementById('selected_items').value);
            addHiddenField('total_amount', total.toFixed(2));
            addHiddenField('hotel_id', <?= $hotel_id ?>);
            addHiddenField('hotel_name', '<?= addslashes($hotel['name']) ?>');

            document.body.appendChild(form);
            form.submit();
        }

        function scrollQueue(queueId, scrollAmount) {
            document.getElementById(queueId).scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure selected items have totalPrice calculated
            const guestCount = parseInt(document.getElementById('noofperson').value) || 1;

            selectedFoods = selectedFoods.map(item => ({
                ...item,
                totalPrice: item.totalPrice || (item.pricePerHead * guestCount)
            }));

            selectedExtras = selectedExtras.map(item => ({
                ...item,
                totalPrice: item.totalPrice || (item.costPerUnit * guestCount)
            }));

            updateBookingSummary();
        });
    </script>

    <?php require 'essentials/footer.html'; ?>
</body>

</html>