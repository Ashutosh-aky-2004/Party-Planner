<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/final/src/database/dbConnect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header("Location: hotelData.php");
    exit();
}

$hotel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($hotel_id <= 0) {
    header("Location: hotelData.php");
    exit();
}

$hotel_query = "SELECT image_path FROM hotels WHERE id = $hotel_id";
$hotel_result = mysqli_query($conn, $hotel_query);
$hotel_data = mysqli_fetch_assoc($hotel_result);
$image_path = $hotel_data['image_path'] ?? '';

// 1. Delete booking food items related to this hotel's bookings
$delete_food_items = "DELETE bfi FROM booking_food_items bfi
                      JOIN bookings b ON bfi.booking_id = b.id
                      WHERE b.hotel_id = $hotel_id";
mysqli_query($conn, $delete_food_items);

// 2. Delete bookings for this hotel
$delete_bookings = "DELETE FROM bookings WHERE hotel_id = $hotel_id";
mysqli_query($conn, $delete_bookings);

// 3. Delete hotel amenities
$delete_amenities = "DELETE FROM hotel_amenities WHERE hotel_id = $hotel_id";
mysqli_query($conn, $delete_amenities);

// 4. Delete hotel
$delete_hotel = "DELETE FROM hotels WHERE id = $hotel_id";
mysqli_query($conn, $delete_hotel);

// 5. Delete image file
if (!empty($image_path)) {
    $full_image_path = $_SERVER['DOCUMENT_ROOT'] . '/final/' . $image_path;
    if (file_exists($full_image_path)) {
        unlink($full_image_path);
    }
}

header("Location: hotelData.php");
exit();
