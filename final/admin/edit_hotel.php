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

$errors = [];
$success = false;

// Define the image upload directory
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/final/src/images/';

// Get hotel ID from URL
$hotel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch hotel data
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
$selectedAmenities = [];
while ($row = mysqli_fetch_assoc($amenitiesResult)) {
    $selectedAmenities[] = $row['amenity'];
}

// Fetch all available amenities
$allAmenitiesQuery = "SELECT DISTINCT amenity FROM hotel_amenities";
$allAmenitiesResult = mysqli_query($conn, $allAmenitiesQuery);
$allAmenities = [];
while ($row = mysqli_fetch_assoc($allAmenitiesResult)) {
    $allAmenities[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $name = sanitize_input($_POST['name']);
    $address = sanitize_input($_POST['address']);
    $city = sanitize_input($_POST['city']);
    $country = sanitize_input($_POST['country']);
    $phone = sanitize_input($_POST['phone']);
    $email = sanitize_input($_POST['email']);
    $amount_per_night = floatval($_POST['amount_per_night']);
    $capacity = intval($_POST['capacity']);
    $min_booking_days = isset($_POST['min_booking_days']) ? intval($_POST['min_booking_days']) : 1;
    $max_booking_days = isset($_POST['max_booking_days']) ? intval($_POST['max_booking_days']) : 365;
    $rating_score = floatval($_POST['rating_score']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $newSelectedAmenities = isset($_POST['amenities']) ? (array)$_POST['amenities'] : [];

    // Validate required fields
    if (empty($name)) $errors[] = "Hotel name is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($country)) $errors[] = "Country is required";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if ($amount_per_night <= 0) $errors[] = "Amount per night must be positive";
    if ($capacity <= 0) $errors[] = "Capacity must be positive";
    if ($min_booking_days <= 0) $errors[] = "Minimum booking days must be positive";
    if ($max_booking_days <= 0) $errors[] = "Maximum booking days must be positive";
    if ($min_booking_days > $max_booking_days) $errors[] = "Minimum booking days cannot exceed maximum";
    if ($rating_score < 0 || $rating_score > 5) $errors[] = "Rating must be between 0 and 5";

    // Handle image upload if new file was provided
    $imagePath = $hotel['image_path']; // Keep existing image by default

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileType = $_FILES['image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Generate a unique file name
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        // Allowed file types
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            if ($fileSize < 5000000) { // 5MB max
                if (is_writable($uploadDir)) {
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        // Delete old image if it exists
                        if (!empty($hotel['image_path']) && file_exists($uploadDir . basename($hotel['image_path']))) {
                            unlink($uploadDir . basename($hotel['image_path']));
                        }
                        // Store new path
                        $imagePath = 'images/' . $newFileName;
                    } else {
                        $errors[] = "There was an error moving the uploaded file.";
                    }
                } else {
                    $errors[] = "Upload directory is not writable";
                }
            } else {
                $errors[] = "Image size is too large (max 5MB)";
            }
        } else {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed";
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle file upload errors except "no file selected"
        switch ($_FILES['image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = "File is too large";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = "File upload was incomplete";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errors[] = "Missing temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errors[] = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $errors[] = "File upload stopped by extension";
                break;
        }
    }

    if (empty($errors)) {
        mysqli_begin_transaction($conn);

        try {
            // Update hotel information
            $escapedName = mysqli_real_escape_string($conn, $name);
            $escapedAddress = mysqli_real_escape_string($conn, $address);
            $escapedCity = mysqli_real_escape_string($conn, $city);
            $escapedCountry = mysqli_real_escape_string($conn, $country);
            $escapedPhone = mysqli_real_escape_string($conn, $phone);
            $escapedEmail = mysqli_real_escape_string($conn, $email);
            $escapedImagePath = mysqli_real_escape_string($conn, $imagePath);

            $updateQuery = "UPDATE hotels SET 
                            name = '$escapedName',
                            address = '$escapedAddress',
                            city = '$escapedCity',
                            country = '$escapedCountry',
                            phone = '$escapedPhone',
                            email = '$escapedEmail',
                            image_path = '$escapedImagePath',
                            amount_per_night = $amount_per_night,
                            capacity = $capacity,
                            min_booking_days = $min_booking_days,
                            max_booking_days = $max_booking_days,
                            rating_score = $rating_score,
                            is_active = $is_active
                          WHERE id = $hotel_id";

            if (!mysqli_query($conn, $updateQuery)) {
                throw new Exception("Failed to update hotel: " . mysqli_error($conn));
            }

            // Update amenities
            // First delete existing amenities
            $deleteAmenitiesQuery = "DELETE FROM hotel_amenities WHERE hotel_id = $hotel_id";
            if (!mysqli_query($conn, $deleteAmenitiesQuery)) {
                throw new Exception("Failed to remove existing amenities: " . mysqli_error($conn));
            }

            // Then add new selected amenities
            if (!empty($newSelectedAmenities)) {
                foreach ($newSelectedAmenities as $amenity) {
                    $cleanAmenity = mysqli_real_escape_string($conn, $amenity);
                    $amenityQuery = "INSERT INTO hotel_amenities (hotel_id, amenity) VALUES ($hotel_id, '$cleanAmenity')";
                    if (!mysqli_query($conn, $amenityQuery)) {
                        throw new Exception("Failed to insert amenities: " . mysqli_error($conn));
                    }
                }
            }

            mysqli_commit($conn);
            $success = true;

            // Refresh the hotel data
            $hotelResult = mysqli_query($conn, $hotelQuery);
            $hotel = mysqli_fetch_assoc($hotelResult);
            $selectedAmenities = $newSelectedAmenities;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hotel - <?= htmlspecialchars($hotel['name']) ?></title>
    <link rel="icon" type="image/png" href="favicon (1).png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

        .hotel-image-preview {
            max-height: 300px;
            width: auto;
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
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Edit Hotel: <?= htmlspecialchars($hotel['name']) ?></h1>
                    <a href="view_hotel.php?id=<?= $hotel_id ?>" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-1"></i> Back to View
                    </a>
                </div>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        Hotel updated successfully!
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="edit_hotel.php?id=<?= $hotel_id ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Hotel Name *</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($hotel['name'] ?? '') ?>" class="form-input" required>
                        </div>

                        <div>
                            <label for="amount_per_night" class="block text-sm font-medium text-gray-700">Price per Night *</label>
                            <input type="number" id="amount_per_night" name="amount_per_night" min="0" step="0.01"
                                value="<?= htmlspecialchars($hotel['amount_per_night'] ?? '') ?>" class="form-input" required>
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address *</label>
                            <textarea id="address" name="address" rows="3" class="form-input" required><?= htmlspecialchars($hotel['address'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity *</label>
                            <input type="number" id="capacity" name="capacity" min="1"
                                value="<?= htmlspecialchars($hotel['capacity'] ?? '') ?>" class="form-input" required>
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">City *</label>
                            <input type="text" id="city" name="city" value="<?= htmlspecialchars($hotel['city'] ?? '') ?>" class="form-input" required>
                        </div>

                        <div>
                            <label for="rating_score" class="block text-sm font-medium text-gray-700">Rating (0-5)</label>
                            <input type="number" id="rating_score" name="rating_score" min="0" max="5" step="0.1"
                                value="<?= htmlspecialchars($hotel['rating_score'] ?? '0') ?>" class="form-input">
                        </div>

                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700">Country *</label>
                            <input type="text" id="country" name="country" value="<?= htmlspecialchars($hotel['country'] ?? '') ?>" class="form-input" required>
                        </div>

                        <div>
                            <label for="min_booking_days" class="block text-sm font-medium text-gray-700">Min Booking Days</label>
                            <input type="number" id="min_booking_days" name="min_booking_days" min="1"
                                value="<?= htmlspecialchars($hotel['min_booking_days'] ?? '1') ?>" class="form-input">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone *</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($hotel['phone'] ?? '') ?>" class="form-input" required>
                        </div>

                        <div>
                            <label for="max_booking_days" class="block text-sm font-medium text-gray-700">Max Booking Days</label>
                            <input type="number" id="max_booking_days" name="max_booking_days" min="1"
                                value="<?= htmlspecialchars($hotel['max_booking_days'] ?? '365') ?>" class="form-input">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($hotel['email'] ?? '') ?>" class="form-input" required>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                <?= ($hotel['is_active'] ?? 0) ? 'checked' : '' ?>
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">Active (available for bookings)</label>
                        </div>
                    </div>

                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Hotel Image</label>
                        <input type="file" id="image" name="image" accept="image/*" class="file-input">
                        <p class="mt-1 text-sm text-gray-500">Leave blank to keep current image. JPEG, PNG or GIF (Max 5MB)</p>

                        <?php if (!empty($hotel['image_path'])): ?>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 mb-2">Current Image:</p>
                                <img src="/final/src/<?= htmlspecialchars($hotel['image_path']) ?>"
                                    alt="Current hotel image"
                                    class="hotel-image-preview border rounded-md p-1">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Amenities:</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <?php if (!empty($allAmenities)): ?>
                                <?php foreach ($allAmenities as $amenity): ?>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="amenity_<?= htmlspecialchars($amenity['amenity']) ?>"
                                            name="amenities[]" value="<?= htmlspecialchars($amenity['amenity']) ?>"
                                            <?= in_array($amenity['amenity'], $selectedAmenities) ? 'checked' : '' ?>
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <label for="amenity_<?= htmlspecialchars($amenity['amenity']) ?>" class="ml-2 block text-sm text-gray-700">
                                            <?= htmlspecialchars($amenity['amenity']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-500">No amenities available</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                        <a href="view_hotel.php?id=<?= $hotel_id ?>" class="ml-4 inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>

<?php
mysqli_close($conn);
?>