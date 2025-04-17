<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/final/src/database/dbConnect.php';
require $_SERVER['DOCUMENT_ROOT'] . '/final/src/formValidation.php';


// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    // If no user is logged in, redirect to login page
    header('Location: index.php');
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

// Configuration
$image_base_path = '/final/src/images/items/';
$absolute_image_path = $_SERVER['DOCUMENT_ROOT'] . $image_base_path;

// Create directories if they don't exist
if (!file_exists($absolute_image_path . 'food/')) {
    mkdir($absolute_image_path . 'food/', 0777, true);
}
if (!file_exists($absolute_image_path . 'additional/')) {
    mkdir($absolute_image_path . 'additional/', 0777, true);
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add/Update Food Item
    if (isset($_POST['food_submit'])) {
        $id = isset($_POST['food_id']) ? intval($_POST['food_id']) : 0;
        $name = mysqli_real_escape_string($conn, $_POST['food_name']);
        $cost = floatval($_POST['food_cost']);

        // Image handling
        $image_path = '';
        if (!empty($_FILES['food_image']['name'])) {
            $file_ext = pathinfo($_FILES['food_image']['name'], PATHINFO_EXTENSION);
            $image_name = 'food_' . time() . '.' . $file_ext;
            $target_file = $absolute_image_path . 'food/' . $image_name;

            if (move_uploaded_file($_FILES['food_image']['tmp_name'], $target_file)) {
                $image_path = $image_base_path . 'food/' . $image_name;

                // Delete old image if updating
                if ($id > 0) {
                    $old_img_query = "SELECT image_path FROM food_items WHERE id = $id";
                    $old_img_result = mysqli_query($conn, $old_img_query);
                    if ($old_img = mysqli_fetch_assoc($old_img_result)) {
                        if (!empty($old_img['image_path'])) {
                            $old_file = $_SERVER['DOCUMENT_ROOT'] . $old_img['image_path'];
                            if (file_exists($old_file)) {
                                unlink($old_file);
                            }
                        }
                    }
                }
            }
        } elseif ($id > 0) {
            // Keep existing image if not uploading new one
            $existing_query = "SELECT image_path FROM food_items WHERE id = $id";
            $existing_result = mysqli_query($conn, $existing_query);
            if ($existing = mysqli_fetch_assoc($existing_result)) {
                $image_path = $existing['image_path'];
            }
        }

        if ($id > 0) {
            // Update existing food item
            $query = "UPDATE food_items SET 
                      name = '$name', 
                      cost_per_head = $cost, 
                      image_path = " . ($image_path ? "'$image_path'" : "NULL") . "
                      WHERE id = $id";
        } else {
            // Add new food item
            $query = "INSERT INTO food_items (name, cost_per_head, image_path) 
                      VALUES ('$name', $cost, " . ($image_path ? "'$image_path'" : "NULL") . ")";
        }

        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Food item " . ($id > 0 ? "updated" : "added") . " successfully!";
        } else {
            $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        }
    }

    // Add/Update Additional Item
    if (isset($_POST['additional_submit'])) {
        $id = isset($_POST['additional_id']) ? intval($_POST['additional_id']) : 0;
        $name = mysqli_real_escape_string($conn, $_POST['additional_name']);
        $cost = floatval($_POST['additional_cost']);

        // Image handling
        $image_path = '';
        if (!empty($_FILES['additional_image']['name'])) {
            $file_ext = pathinfo($_FILES['additional_image']['name'], PATHINFO_EXTENSION);
            $image_name = 'additional_' . time() . '.' . $file_ext;
            $target_file = $absolute_image_path . 'additional/' . $image_name;

            if (move_uploaded_file($_FILES['additional_image']['tmp_name'], $target_file)) {
                $image_path = $image_base_path . 'additional/' . $image_name;

                // Delete old image if updating
                if ($id > 0) {
                    $old_img_query = "SELECT image_path FROM additional_items WHERE id = $id";
                    $old_img_result = mysqli_query($conn, $old_img_query);
                    if ($old_img = mysqli_fetch_assoc($old_img_result)) {
                        if (!empty($old_img['image_path'])) {
                            $old_file = $_SERVER['DOCUMENT_ROOT'] . $old_img['image_path'];
                            if (file_exists($old_file)) {
                                unlink($old_file);
                            }
                        }
                    }
                }
            }
        } elseif ($id > 0) {
            // Keep existing image if not uploading new one
            $existing_query = "SELECT image_path FROM additional_items WHERE id = $id";
            $existing_result = mysqli_query($conn, $existing_query);
            if ($existing = mysqli_fetch_assoc($existing_result)) {
                $image_path = $existing['image_path'];
            }
        }

        if ($id > 0) {
            // Update existing additional item
            $query = "UPDATE additional_items SET 
                      name = '$name', 
                      cost_per_item = $cost, 
                      image_path = " . ($image_path ? "'$image_path'" : "NULL") . "
                      WHERE id = $id";
        } else {
            // Add new additional item
            $query = "INSERT INTO additional_items (name, cost_per_item, image_path) 
                      VALUES ('$name', $cost, " . ($image_path ? "'$image_path'" : "NULL") . ")";
        }

        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Additional item " . ($id > 0 ? "updated" : "added") . " successfully!";
        } else {
            $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        }
    }

    header("Location: items_management.php");
    exit();
}

// Handle delete operations
if (isset($_GET['delete'])) {
    $type = $_GET['type'] ?? '';
    $id = intval($_GET['delete']);

    if ($type === 'food') {
        // Get image path first
        $img_query = "SELECT image_path FROM food_items WHERE id = $id";
        $img_result = mysqli_query($conn, $img_query);
        $img_data = mysqli_fetch_assoc($img_result);

        // Delete record
        $delete_query = "DELETE FROM food_items WHERE id = $id";
        if (mysqli_query($conn, $delete_query)) {
            // Delete image file if exists
            if (!empty($img_data['image_path'])) {
                $image_file = $_SERVER['DOCUMENT_ROOT'] . $img_data['image_path'];
                if (file_exists($image_file)) {
                    unlink($image_file);
                }
            }
            $_SESSION['success'] = "Food item deleted successfully!";
        }
    } elseif ($type === 'additional') {
        // Get image path first
        $img_query = "SELECT image_path FROM additional_items WHERE id = $id";
        $img_result = mysqli_query($conn, $img_query);
        $img_data = mysqli_fetch_assoc($img_result);

        // Delete record
        $delete_query = "DELETE FROM additional_items WHERE id = $id";
        if (mysqli_query($conn, $delete_query)) {
            // Delete image file if exists
            if (!empty($img_data['image_path'])) {
                $image_file = $_SERVER['DOCUMENT_ROOT'] . $img_data['image_path'];
                if (file_exists($image_file)) {
                    unlink($image_file);
                }
            }
            $_SESSION['success'] = "Additional item deleted successfully!";
        }
    } else {
        $_SESSION['error'] = "Invalid delete request";
    }

    header("Location: items_management.php");
    exit();
}

// Fetch all items
$food_items = mysqli_query($conn, "SELECT * FROM food_items ORDER BY name");
$additional_items = mysqli_query($conn, "SELECT * FROM additional_items ORDER BY name");

// Check if editing
$editing_food = null;
$editing_additional = null;

if (isset($_GET['edit_food'])) {
    $id = intval($_GET['edit_food']);
    $result = mysqli_query($conn, "SELECT * FROM food_items WHERE id = $id");
    $editing_food = mysqli_fetch_assoc($result);
}

if (isset($_GET['edit_additional'])) {
    $id = intval($_GET['edit_additional']);
    $result = mysqli_query($conn, "SELECT * FROM additional_items WHERE id = $id");
    $editing_additional = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items Management</title>
    <link rel="icon" type="image/png" href="favicon (1).png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        <h1 class="text-3xl font-bold mb-8">Items Management</h1>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Food Items Section -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4"><?= $editing_food ? 'Edit' : 'Add' ?> Food Item</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="food_id" value="<?= $editing_food ? $editing_food['id'] : '' ?>">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2" for="food_name">Name</label>
                        <input type="text" id="food_name" name="food_name" required
                            value="<?= $editing_food ? htmlspecialchars($editing_food['name']) : '' ?>"
                            class="w-full px-3 py-2 border rounded-lg">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2" for="food_cost">Cost Per Head</label>
                        <input type="number" step="0.01" id="food_cost" name="food_cost" required
                            value="<?= $editing_food ? htmlspecialchars($editing_food['cost_per_head']) : '' ?>"
                            class="w-full px-3 py-2 border rounded-lg">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2" for="food_image">Image</label>
                        <input type="file" id="food_image" name="food_image" <?= !$editing_food ? 'required' : '' ?>
                            class="w-full px-3 py-2 border rounded-lg">
                        <?php if ($editing_food && !empty($editing_food['image_path'])): ?>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Current Image:</p>
                                <img src="<?= htmlspecialchars($editing_food['image_path']) ?>" alt="Current food image" class="h-20 mt-1">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex justify-end">
                    <?php if ($editing_food): ?>
                        <a href="items_management.php" class="mr-2 px-4 py-2 bg-gray-300 rounded-lg">Cancel</a>
                    <?php endif; ?>
                    <button type="submit" name="food_submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <?= $editing_food ? 'Update' : 'Add' ?> Food Item
                    </button>
                </div>
            </form>
        </div>

        <!-- Additional Items Section -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4"><?= $editing_additional ? 'Edit' : 'Add' ?> Additional Item</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="additional_id" value="<?= $editing_additional ? $editing_additional['id'] : '' ?>">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2" for="additional_name">Name</label>
                        <input type="text" id="additional_name" name="additional_name" required
                            value="<?= $editing_additional ? htmlspecialchars($editing_additional['name']) : '' ?>"
                            class="w-full px-3 py-2 border rounded-lg">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2" for="additional_cost">Cost Per Item</label>
                        <input type="number" step="0.01" id="additional_cost" name="additional_cost" required
                            value="<?= $editing_additional ? htmlspecialchars($editing_additional['cost_per_item']) : '' ?>"
                            class="w-full px-3 py-2 border rounded-lg">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2" for="additional_image">Image</label>
                        <input type="file" id="additional_image" name="additional_image" <?= !$editing_additional ? 'required' : '' ?>
                            class="w-full px-3 py-2 border rounded-lg">
                        <?php if ($editing_additional && !empty($editing_additional['image_path'])): ?>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Current Image:</p>
                                <img src="<?= htmlspecialchars($editing_additional['image_path']) ?>" alt="Current additional item image" class="h-20 mt-1">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex justify-end">
                    <?php if ($editing_additional): ?>
                        <a href="items_management.php" class="mr-2 px-4 py-2 bg-gray-300 rounded-lg">Cancel</a>
                    <?php endif; ?>
                    <button type="submit" name="additional_submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <?= $editing_additional ? 'Update' : 'Add' ?> Additional Item
                    </button>
                </div>
            </form>
        </div>

        <!-- Food Items Table -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Food Items</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 border">Image</th>
                            <th class="py-2 px-4 border">Name</th>
                            <th class="py-2 px-4 border">Cost Per Head</th>
                            <th class="py-2 px-4 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($food_items)): ?>
                            <tr>
                                <td class="py-2 px-4 border text-center">
                                    <?php if (!empty($item['image_path'])): ?>
                                        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="h-12 mx-auto">
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-4 border"><?= htmlspecialchars($item['name']) ?></td>
                                <td class="py-2 px-4 border">₹<?= number_format($item['cost_per_head'], 2) ?></td>
                                <td class="py-2 px-4 border">
                                    <a href="items_management.php?edit_food=<?= $item['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-2">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="items_management.php?delete=<?= $item['id'] ?>&type=food"
                                        onclick="return confirm('Are you sure you want to delete this food item?')"
                                        class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Additional Items Table -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Additional Items</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 border">Image</th>
                            <th class="py-2 px-4 border">Name</th>
                            <th class="py-2 px-4 border">Cost Per Item</th>
                            <th class="py-2 px-4 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($additional_items)): ?>
                            <tr>
                                <td class="py-2 px-4 border text-center">
                                    <?php if (!empty($item['image_path'])): ?>
                                        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="h-12 mx-auto">
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-4 border"><?= htmlspecialchars($item['name']) ?></td>
                                <td class="py-2 px-4 border">₹<?= number_format($item['cost_per_item'], 2) ?></td>
                                <td class="py-2 px-4 border">
                                    <a href="items_management.php?edit_additional=<?= $item['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-2">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="items_management.php?delete=<?= $item['id'] ?>&type=additional"
                                        onclick="return confirm('Are you sure you want to delete this additional item?')"
                                        class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>

<?php mysqli_close($conn); ?>