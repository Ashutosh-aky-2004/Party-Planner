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

// Initialize variables
$errors = [];
$success = false;
$selectedAmenities = [];

// Define the correct path where images will be stored
$uploadRelativeDir = '/final/src/images/';
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . $uploadRelativeDir;

// Create directory if it doesn't exist
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $errors[] = "Failed to create upload directory";
    }
}

// Fetch all available amenities from hotel_amenities table
$amenitiesQuery = "SELECT DISTINCT amenity FROM hotel_amenities";
$amenitiesResult = mysqli_query($conn, $amenitiesQuery);
$allAmenities = [];
if ($amenitiesResult) {
    while ($row = mysqli_fetch_assoc($amenitiesResult)) {
        $allAmenities[] = $row;
    }
} else {
    $errors[] = "Failed to fetch amenities: " . mysqli_error($conn);
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
    $selectedAmenities = isset($_POST['amenities']) ? (array)$_POST['amenities'] : [];

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

    // Handle image upload
    $imagePath = null;
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
                        // Store path as images/filename.ext (without src/)
                        $imagePath = 'images/' . $newFileName;
                    } else {
                        $errors[] = "There was an error moving the uploaded file. Check directory permissions.";
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
    } else {
        $errors[] = "Hotel image is required";
        if (isset($_FILES['image'])) {
            switch ($_FILES['image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = "File is too large";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = "File upload was incomplete";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = "No file was uploaded";
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
    }

    // If no errors, insert into database
    if (empty($errors)) {
        mysqli_begin_transaction($conn);

        try {
            // Insert hotel
            $escapedName = mysqli_real_escape_string($conn, $name);
            $escapedAddress = mysqli_real_escape_string($conn, $address);
            $escapedCity = mysqli_real_escape_string($conn, $city);
            $escapedCountry = mysqli_real_escape_string($conn, $country);
            $escapedPhone = mysqli_real_escape_string($conn, $phone);
            $escapedEmail = mysqli_real_escape_string($conn, $email);
            $escapedImagePath = $imagePath ? "'" . mysqli_real_escape_string($conn, $imagePath) . "'" : "NULL";

            $query = "INSERT INTO hotels (
                        name, address, city, country, phone, email, image_path, 
                        amount_per_night, capacity, min_booking_days, max_booking_days, 
                        rating_score, is_active, created_at
                      ) VALUES (
                        '$escapedName', '$escapedAddress', '$escapedCity', '$escapedCountry', 
                        '$escapedPhone', '$escapedEmail', $escapedImagePath, 
                        $amount_per_night, $capacity, $min_booking_days, $max_booking_days, 
                        $rating_score, $is_active, NOW()
                      )";

            if (mysqli_query($conn, $query)) {
                $hotelId = mysqli_insert_id($conn);

                // Insert selected amenities into hotel_amenities table
                if (!empty($selectedAmenities)) {
                    foreach ($selectedAmenities as $amenity) {
                        $cleanAmenity = mysqli_real_escape_string($conn, $amenity);
                        $amenityQuery = "INSERT INTO hotel_amenities (hotel_id, amenity) 
                                      VALUES ($hotelId, '$cleanAmenity')";
                        if (!mysqli_query($conn, $amenityQuery)) {
                            throw new Exception("Failed to insert amenities: " . mysqli_error($conn));
                        }
                    }
                }

                mysqli_commit($conn);
                $success = true;

                // Clear form on success
                $name = $address = $city = $country = $phone = $email = '';
                $amount_per_night = $capacity = 0;
                $min_booking_days = 1;
                $max_booking_days = 365;
                $rating_score = 0;
                $is_active = 1;
                $selectedAmenities = [];
            } else {
                throw new Exception("Database error: " . mysqli_error($conn));
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = $e->getMessage();

            if ($imagePath && file_exists($uploadDir . basename($imagePath))) {
                unlink($uploadDir . basename($imagePath));
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
    <title>Add New Hotel</title>
    <link rel="icon" type="image/png" href="favicon (1).png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* .form-input {
            @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500;
        }

        .file-input {
            @apply mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100;
        } */
    </style>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h1 class="text-2xl font-bold mb-6">Add New Hotel</h1>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        Hotel added successfully!
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

                <form action="add_hotel.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Hotel Name *</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" class="form-input" required>
                        </div>

                        <div>
                            <label for="amount_per_night" class="block text-sm font-medium text-gray-700">Price per Night *</label>
                            <input type="number" id="amount_per_night" name="amount_per_night" min="0" step="0.01"
                                value="<?= htmlspecialchars($amount_per_night ?? '') ?>" class="form-input" required>
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address *</label>
                            <textarea id="address" name="address" rows="3" class="form-input" required><?= htmlspecialchars($address ?? '') ?></textarea>
                        </div>

                        <div>
                            <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity *</label>
                            <input type="number" id="capacity" name="capacity" min="1"
                                value="<?= htmlspecialchars($capacity ?? '') ?>" class="form-input" required>
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">City *</label>
                            <input type="text" id="city" name="city" value="<?= htmlspecialchars($city ?? '') ?>" class="form-input" required>
                        </div>

                        <div>
                            <label for="rating_score" class="block text-sm font-medium text-gray-700">Rating (0-5)</label>
                            <input type="number" id="rating_score" name="rating_score" min="0" max="5" step="0.1"
                                value="<?= htmlspecialchars($rating_score ?? '0') ?>" class="form-input">
                        </div>

                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700">Country *</label>
                            <input type="text" id="country" name="country" value="<?= htmlspecialchars($country ?? '') ?>" class="form-input" required>
                        </div>

                        <div>
                            <label for="min_booking_days" class="block text-sm font-medium text-gray-700">Min Booking Days</label>
                            <input type="number" id="min_booking_days" name="min_booking_days" min="1"
                                value="<?= htmlspecialchars($min_booking_days ?? '1') ?>" class="form-input">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone *</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" class="form-input" required>
                        </div>

                        <div>
                            <label for="max_booking_days" class="block text-sm font-medium text-gray-700">Max Booking Days</label>
                            <input type="number" id="max_booking_days" name="max_booking_days" min="1"
                                value="<?= htmlspecialchars($max_booking_days ?? '365') ?>" class="form-input">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" class="form-input" required>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                <?= isset($is_active) && $is_active ? 'checked' : '' ?>
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">Active (available for bookings)</label>
                        </div>
                    </div>

                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Hotel Image *</label>
                        <input type="file" id="image" name="image" accept="image/*" class="file-input" required>
                        <p class="mt-1 text-sm text-gray-500">JPEG, PNG or GIF (Max 5MB)</p>
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
                            Add Hotel
                        </button>
                        <a href="hotels.php" class="ml-4 inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
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