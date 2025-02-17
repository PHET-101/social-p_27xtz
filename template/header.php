<?php
include 'php/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <title>Social Network</title>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>

<body class="bg-[#0f0f0f] text-white font-sans">

    <nav class=" bg-neutral-900 fixed top-0 left-0 w-full bg-gray-900 text-white shadow-lg z-50">
        <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
            <div class="relative flex h-16 items-center justify-between">
                <div class="absolute inset-y-0 left-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                    <!-- Profile dropdown -->
                    <div class="relative ml-3">
                        <div>
                            <?php
                            $user_id = $_SESSION['user_id'] ?? null;
                            ?>

                            <div>
                                <?php if ($user_id): ?>
                                    <a href="index.php" type="button" class="relative flex items-center rounded-full text-sm">
                                        <span class="absolute -inset-1.5"></span>
                                        <span class="sr-only">Open user menu</span>
                                        <div class="w-12 h-12 flex items-center justify-center rounded-full border-1 border-gray-700 shadow-lg overflow-hidden mr-2">
                                            <?php if (!empty($_SESSION['avatar']) && file_exists($_SESSION['avatar'])): ?>
                                                <img src="<?php echo $_SESSION['avatar']; ?>" alt="Profile Picture" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <ion-icon name="person-circle-outline" class="text-gray-400 text-9xl"></ion-icon>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-left">
                                            <p class="text-lg font-semibold"><?php echo $_SESSION['display_name']; ?></p>
                                            <p class="text-sm text-gray-500"><?php echo $_SESSION['username']; ?></p>
                                        </div>
                                    </a>
                                <?php else: ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                    <!-- Notification Button -->

                    <?php if ($user_id): ?>
                        <a href="auth/logout.php" id="logoutLink" type="submit" class="flex items-center justify-center text-red-400 hover:text-red-300 text-2xl cursor-pointer mt-1"><ion-icon name="log-out-outline"></ion-icon></a>
                    <?php else: ?>
                    <?php endif; ?>


                    <!-- JavaScript -->
                    <script>
                        document.getElementById("user-menu-button").addEventListener("click", function() {
                            let menu = document.getElementById("dropdown-menu");
                            menu.classList.toggle("hidden");
                        });

                        document.addEventListener("click", function(event) {
                            let menu = document.getElementById("dropdown-menu");
                            let button = document.getElementById("user-menu-button");

                            if (!menu.contains(event.target) && !button.contains(event.target)) {
                                menu.classList.add("hidden");
                            }
                        });
                    </script>

                </div>
            </div>
    </nav>

    <div class="flex-col justify-center px-2 sm:px-6 w-full lg:px-8 py-8 mt-10 mx-auto w-full lg:max-w-2xl">