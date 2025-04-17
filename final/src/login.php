<?php
// Start session to store user info after login
session_start();

require 'database/dbConnect.php';
require 'formValidation.php';
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $password = filter_var($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = '$email'";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Correct email and password, start the session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];

            header("Location: index.php");
            exit();
        } else {
            echo "<p class='text-red-500 text-center'>Invalid password!</p>";  // Password error
        }
    } else {
        echo "<p class='text-red-500 text-center'>No user found with that email!</p>";  // No email found error
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php require 'essentials/commonScript.html'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>

<body class="bg-black">

    <!-- Header -->
    <?php require 'essentials/header.php'; ?>

    <!-- Main container with full-screen background -->
    <div class="flex min-h-screen justify-end bg-[url('images/bg.avif')] bg-cover bg-no-repeat bg-center">

        <!-- Form container aligned to the right -->
        <div class="flex items-center justify-center w-full sm:w-[500px] px-6 py-12 mr-40 w-[600px]">
            <div class="bg-white p-6 rounded-xl shadow-lg w-[600px] h-[400px] animate__animated animate__fadeInUp animate__delay-.5s">
                <h2 class="text-center text-3xl font-bold text-black mb-6">Sign in to your account</h2>
                
                <form class="space-y-6" action="login.php" method="POST">
                    <div>
                        <label for="email" class="block text-lg font-medium text-black">Email address</label>
                        <div class="mt-2">
                            <input type="email" name="email" id="email" autocomplete="email" required class="block w-full bg-white text-black border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black focus:border-black text-base placeholder:text-gray-400">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-lg font-medium text-black">Password</label>
                            <div class="text-sm">
                                <a href="forgetPassword.php" class="text-black hover:text-gray-700">Forgot password?</a>
                            </div>
                        </div>
                        <div class="mt-2">
                            <input type="password" name="password" id="password" autocomplete="current-password" required class="block w-full bg-white text-black border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black focus:border-black text-base placeholder:text-gray-400">
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="w-full py-3 px-4 text-white font-semibold bg-black rounded-md shadow-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-black transition ease-in-out duration-200">
                            Sign in
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>
