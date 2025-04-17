<?php
require 'database/dbConnect.php'; // Include database connection

// Fetch hotels data
$hotelQuery = "SELECT * FROM hotels";
$hotelResult = mysqli_query($conn, $hotelQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventify</title>
    <?php require 'essentials/commonScript.html'; ?>
    <link rel="stylesheet" href="style.css">
</head>

<body class="w-full h-full bg-[url(bg8.png)]">
    <!-- Header -->
    <?php require 'essentials/header.php'; ?>

    <!-- Body for Party Type -->
    <div class="w-[90%] h-auto ml-16 mt-10 flex-col justify-around pr-7 pl-7">
        <h1 class="text-5xl font-bold text-white pt-2">Book Venue</h1>
        <div class="grid grid-cols-3 gap-10 justify-around pt-12 ml-2" id="hotelCards">
            <?php while ($hotel = mysqli_fetch_assoc($hotelResult)):
                $ratingStars = str_repeat('⭐', $hotel['rating_score']);
            ?>
                <div class="mb-3 h-60 w-full border-2 border-white rounded-lg overflow-hidden transition-all relative shadow-[0px_0px_15px_0px_rgba(0,0,0,1)] hover:-translate-y-2 duration-300">
                    <div class="relative h-3/4 overflow-hidden z-10">
                        <img class="w-full h-full object-cover transition-transform duration-500 ease-in-out hover:scale-110" src="<?= $hotel['image_path'] ?>" alt="<?= $hotel['name'] ?>">
                        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-500">
                            <div>
                                <div class="text-center">
                                    <h3 class="text-white text-2xl font-bold"><?= $hotel['name'] ?></h3>
                                </div>
                                <div class="p-2">
                                    <h3 class="text-white text-sm font-medium">Price: ₹<?= number_format($hotel['amount_per_night'], 2) ?></h3>
                                    <h3 class="text-white text-sm font-medium">Capacity: <?= $hotel['capacity'] ?></h3>
                                    <h3 class="text-white text-sm font-medium">Rating: <?= $ratingStars ?></h3>
                                    <h3 class="text-white text-sm font-medium">Location: <?= $hotel['address'] ?>, <?= $hotel['city'] ?>, <?= $hotel['country'] ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center px-4 py-2">
                        <a href="book.php?hotel_id=<?= $hotel['id'] ?>" class="p-1 mt-1 relative inline-flex items-center justify-center overflow-hidden font-medium text-white transition duration-300 ease-out border-2 rounded-full shadow-md group">
                            <span class="absolute inset-0 flex items-center justify-center w-full h-full text-black duration-300 -translate-x-full bg-white group-hover:translate-x-0 ease">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </span>
                            <span class="absolute flex items-center justify-center w-full h-full text-white transition-all duration-300 transform group-hover:translate-x-full ease">Book Now</span>
                            <span class="relative invisible">Button Text</span>
                        </a>
                        <h4 class="text-white font-bold text-2xl">₹<?= number_format($hotel['amount_per_night'], 2) ?></h4>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php require 'essentials/footer.html'; ?>
</body>

</html>