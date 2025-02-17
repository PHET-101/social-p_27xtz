<?php
session_start();
include '../php/config.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["display_name"] = $user["display_name"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["avatar"] = $user["avatar"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["description"] = $user["description"];
        $_SESSION["created_at"] = $user["created_at"];
        $_SESSION["updated_at"] = $user["updated_at"];
        header("Location: ../index.php");
        exit;
    } else {
        $message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
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

<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8" data-aos="fade-up">

    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-white">เข้าสู่ระบบ</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm min-w-80">
        <form class="space-y-6" action="#" method="POST">
            <div>
                <label for="username" class="block text-sm/6 font-medium text-white">ชื่อผู้ใช้</label>
                <div class="mt-2">
                    <input type="username" name="username" id="username" autocomplete="username" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-gray-600 sm:text-sm/6">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm/6 font-medium text-white">รหัสผ่าน</label>
                    <div class="text-sm">
                        <a href="#" class="font-semibold text-gray-600 hover:text-gray-500">ลืมรหัสผ่าน?</a>
                    </div>
                </div>
                <div class="mt-2">
                    <input type="password" name="password" id="password" autocomplete="current-password" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-gray-600 sm:text-sm/6">
                </div>
            </div>

            <?php if ($message): ?>
                <div class="text-center">
                    <p class=" text-red-400"><?php echo $message; ?></p>
                </div>
            <?php endif; ?>

            <div>
                <button type="submit" class="flex w-full justify-center rounded-md bg-gray-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-gray-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">เข้าสู่ระบบ</button>
            </div>
        </form>

        <p class="mt-10 text-center text-sm/6 text-gray-500">
            ยังไม่ได้ลงทะเบียน?
            <a href="register.php" class="font-semibold text-gray-600 hover:text-gray-500">ลงทะเบียนฟรี</a>
        </p>
    </div>
</div>

<?php include('../template/footer.php'); ?>