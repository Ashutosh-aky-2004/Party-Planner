<?php

require 'database/dbConnect.php';
require 'formValidation.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize the form data
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $message = sanitize_input($_POST['message']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit();
    }

    // Prepare the SQL query to insert data into the contact_messages table
    $sql = "INSERT INTO contact_messages (name, email, message) VALUES ('$name', '$email', '$message')";

    // Execute the query
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Message sent successfully!');</script>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    // Close the connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <?php require 'essentials/commonScript.html'; ?>
    <link rel="stylesheet" href="style.css">
</head>

<body class="w-full h-full">
    <!-- Header -->
    <?php require 'essentials/header.php'; ?>

    <!-- Body for Party Type -->
    <div class="bg-gray-100">
        <div class="container mx-auto py-12">
            <div class="max-w-lg mx-auto px-4">
                <h2 class="text-3xl font-semibold text-gray-900 mb-4">
                    How can we help you
                </h2>
                <p class="text-gray-700 mb-8">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce sagittis velit
                    eget nisi lobortis dignissim.
                </p>
                <form class="bg-white rounded-lg px-6 py-8 shadow-md">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="name">Name</label>
                        <input
                            class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="name" name="name" type="text" placeholder="Enter your name">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="email">Email</label>
                        <input
                            class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="email" type="email" name="email" placeholder="Enter your email">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="message">Message</label>
                        <textarea
                            class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="message" rows="6" name="message" placeholder="Enter your message"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                            type="submit">
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Footer -->
    <?php require 'essentials/footer.html'; ?>
</body>

</html>