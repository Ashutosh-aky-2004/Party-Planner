<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900">
<?php require 'essentials/header.php'; ?>

    <div class="flex items-center justify-center min-h-screen bg-gradient-to-b from-gray-800 to-gray-900">
    <div
        class="h-[500px] w-full max-w-2xl p-12 mx-4 text-center transition-all transform bg-gray-800 shadow-lg rounded-xl hover:shadow-xl border border-gray-700">
        <!-- Success Icon -->
        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-8 bg-gray-700 rounded-full border border-green-500">
            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <!-- Main Content -->
        <h1 class="mb-6 text-3xl font-extrabold text-green-500">
            Booking Successful!
        </h1>

        <p class="mb-8 text-lg text-gray-300">
            Thank you for Booking..See you next Time.
        </p>

        <div class="p-3 mb-4 rounded-lg bg-gray-700 border border-green-500/30">
            <p class="text-lg font-medium text-green-400">
                You will be notified Soon
            </p>
        </div>

        <!-- Contact Information -->
        <div class="pt-4 mt-4 border-t border-gray-700">
            <p class="text-lg text-gray-400">
                Have questions? Contact us at:
            </p>
            <a href="mailto:sameerrswami@gmail.com"
                class="inline-block mt-2 text-xl font-medium text-green-400 transition-colors duration-200 hover:text-green-300">
                sameerrswami@gmail.com
            </a>
        </div>

        <!-- Back to Home Button -->
        <div class="mt-6">
            <a href="index.php"
                class="inline-block px-6 py-2 text-lg font-semibold text-gray-900 transition-colors duration-200 bg-green-500 rounded-lg hover:bg-green-400 hover:text-gray-800 border border-green-600">
                Return to Home
            </a>
        </div>
    </div>
</div>
<?php require 'essentials/footer.html'; ?>

</body>
</html>