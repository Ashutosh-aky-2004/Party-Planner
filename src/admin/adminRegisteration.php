<?php
// Start the session
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/final/src/database/dbConnect.php';
require $_SERVER['DOCUMENT_ROOT'] . '/final/src/formValidation.php';

$errorMessage = '';
$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $full_name = sanitize_input($_POST['name']);
    $last_name = sanitize_input($_POST['lastname']);
    $phone = sanitize_input($_POST['phone']);
    $email =  filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $address = sanitize_input($_POST['address']);
    $password = filter_var($_POST['password']);
    $confirmPassword = filter_var($_POST['confirm-password']);

    // Validate the inputs
    if (empty($full_name) || empty($email) || empty($password) || empty($confirmPassword) || empty($phone) || empty($last_name) || empty($address)) {
        $errorMessage = "All fields are required!";
    } elseif ($password !== $confirmPassword) {
        $errorMessage = "Passwords do not match!";
    } else {
        // Check if the email already exists
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $errorMessage = "Email is already taken!";
        } else {
            // Hash the password for storage
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert the user into the database
            $sql = "INSERT INTO users (full_name, last_name, email, phone, address, password, is_admin) 
                    VALUES ('$full_name', '$last_name', '$email', '$phone', '$address', '$hashedPassword', 1)";
            if (mysqli_query($conn, $sql)) {
                // Set the success message with HTML for the login button
                $successMessage = "Registration successful! You can now Login.";
            } else {
                $errorMessage = "Error: " . $sql . "<br>" . mysqli_error($conn);
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
    <title>Register Admin</title>
    <link rel="icon" type="image/png" href="favicon (1).png">
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/final/src/essentials/commonScript.html'; ?>

    <link rel="stylesheet" href="css/style.css">
    <script>
        // Display the error message in an alert
        <?php if (!empty($errorMessage)) { ?>
            window.onload = function() {
                alert("<?php echo $errorMessage; ?>");
            };
        <?php } ?>

        // Display the success message in an alert
        <?php if (!empty($successMessage)) { ?>
            window.onload = function() {
                alert("<?php echo $successMessage; ?>");
                window.location.href = "index.php";
            };
        <?php } ?>
    </script>
</head>

<body class="bg-black text-white bg-cover bg-center" style="background-image: url('images/bg2.jpg');">

    <!-- Header -->
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/final/src/essentials/header.php'; ?>

    <div class="container mx-auto py-8 px-2 sm:px-4">
        <h1 class="text-3xl font-bold mb-3 text-center">Registration Form</h1>
        <form class="w-full max-w-lg mx-auto bg-white p-6 rounded-md shadow-lg space-y-4" action="adminRegisteration.php" method="post">
            <!-- First Name & Last Name in the same row -->
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-black text-sm font-bold mb-2" for="name">First Name</label>
                    <input class="w-full px-3 py-2 border text-black border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-black text-sm"
                        type="text" id="name" name="name" placeholder="John" required>
                </div>
                <div class="mb-4">
                    <label class="block text-black text-sm font-bold mb-2" for="lastname">Last Name</label>
                    <input class="w-full px-3 py-2 border  text-black  border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-black text-sm"
                        type="text" id="lastname" name="lastname" placeholder="Doe" required>
                </div>
            </div>

            <!-- Phone Number & Email in the same row -->
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-black text-sm font-bold mb-2" for="phone">Phone Number</label>
                    <input class="w-full px-3 py-2  text-black  border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-black text-sm"
                        type="tel" id="phone" name="phone" placeholder="+91 1111111111" required>
                </div>
                <div class="mb-4">
                    <label class="block text-black text-sm font-bold mb-2" for="email">Email</label>
                    <input class="w-full px-3 py-2  text-black  border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-black text-sm"
                        type="email" id="email" name="email" placeholder="john@example.com" required>
                </div>
            </div>

            <!-- Address field takes full width -->
            <div class="mb-4">
                <label class="block text-black text-sm font-bold mb-2" for="address">Address</label>
                <textarea class="w-full px-3 py-2   text-black border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-black text-sm"
                    id="address" name="address" placeholder="Your address here..." required></textarea>
            </div>

            <!-- Password & Confirm Password in the same row -->
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-black text-sm font-bold mb-2" for="password">Password</label>
                    <input class="w-full px-3 py-2 text-black border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-black text-sm"
                        type="password" id="password" name="password" placeholder="********" required>
                </div>
                <div class="mb-4">
                    <label class="block text-black text-sm font-bold mb-2" for="confirm-password">Confirm Password</label>
                    <input class="w-full px-3 py-2 border text-black border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-black text-sm"
                        type="password" id="confirm-password" name="confirm-password" placeholder="********" required>
                </div>
            </div>

            <button class="w-full bg-black text-white text-sm font-bold py-2 px-4 rounded-md hover:bg-gray-800 transition duration-300" type="submit">Register</button>
        </form>
    </div>

</body>

</html>