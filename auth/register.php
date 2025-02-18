 <?php
include '../php/config.php';

$message = '';
$username = $email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["c_password"];

    // ตรวจสอบว่า username เป็นไปตามเงื่อนไขหรือไม่
    if (!preg_match("/^[a-z0-9_.]{3,20}$/", $username)) {
        $message = "ชื่อผู้ใช้ต้องมีความยาว 3-20 ตัวอักษร และประกอบด้วย a-z, _ และ . เท่านั้น!";
    } else {
        // ตรวจสอบว่า username ซ้ำหรือไม่
        $sql_check_username = "SELECT * FROM users WHERE username = ?";
        $stmt_check_username = $conn->prepare($sql_check_username);
        $stmt_check_username->bind_param("s", $username);
        $stmt_check_username->execute();
        $result_username = $stmt_check_username->get_result();

        if ($result_username->num_rows > 0) {
            $message = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว!";
        } else {
            // ตรวจสอบว่าอีเมลถูกใช้ไปหรือยัง
            $sql_check_email = "SELECT * FROM users WHERE email = ?";
            $stmt_check_email = $conn->prepare($sql_check_email);
            $stmt_check_email->bind_param("s", $email);
            $stmt_check_email->execute();
            $result_email = $stmt_check_email->get_result();

            if ($result_email->num_rows > 0) {
                $message = "อีเมลนี้ถูกใช้งานแล้ว!";
            } else {
                // ตรวจสอบความแข็งแรงของรหัสผ่าน
                if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
                    $message = "รหัสผ่านต้องมีอย่างน้อย 8 ตัว, มีตัวพิมพ์ใหญ่ 1 ตัว, ตัวพิมพ์เล็ก 1 ตัว, ตัวเลข 1 ตัว และอักขระพิเศษ 1 ตัว!";
                } elseif ($password !== $confirm_password) {
                    $message = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน!";
                } else {
                    // แฮชรหัสผ่านและบันทึกลงฐานข้อมูล
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $sql = "INSERT INTO users (display_name, username, email, password) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssss" ,$username, $username, $email, $hashed_password);

                    if ($stmt->execute()) {
                        $message = "สมัครสมาชิกสำเร็จ!";
                        header("Location: login.php");
                        exit;
                    } else {
                        $message = "เกิดข้อผิดพลาด: " . $conn->error;
                    }
                }
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
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <title>Social Network</title>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>

<body class="bg-[#0f0f0f] text-white font-sans">

    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8 bg" data-aos="fade-up">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-white">สมัครสมาชิก</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm min-w-80">
            <form class="space-y-6" action="#" method="POST">
                <div>
                    <label for="username" class="block text-sm font-medium text-white">ชื่อผู้ใช้</label>
                    <div class="mt-2">
                        <input type="text" name="username" id="username" required
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 outline-gray-300 placeholder-gray-400 focus:outline-gray-600 sm:text-sm">
                        <p id="username_status" class="text-sm mt-1"></p>
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-white">อีเมล</label>
                    <div class="mt-2">
                        <input type="email" name="email" id="email" required
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 outline-gray-300 placeholder-gray-400 focus:outline-gray-600 sm:text-sm">
                        <p id="email_status" class="text-sm mt-1"></p>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-white">รหัสผ่าน</label>
                    <div class="mt-2">
                        <input type="password" name="password" id="password" required
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 outline-gray-300 placeholder-gray-400 focus:outline-gray-600 sm:text-sm">
                        <p id="password_status" class="text-sm mt-1"></p>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="c_password" class="block text-sm font-medium text-white">ยืนยันรหัสผ่าน</label>
                    <div class="mt-2">
                        <input type="password" name="c_password" id="c_password" required
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 outline-gray-300 placeholder-gray-400 focus:outline-gray-600 sm:text-sm">
                        <p id="confirm_password_status" class="text-sm mt-1"></p>
                    </div>
                </div>

                <button id="submitBtn" type="submit" class="flex cursor-pointer w-full justify-center rounded-md bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white shadow-xs hover:bg-gray-500 focus-visible:outline-gray-600 disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                    สมัครสมาชิก
                </button>
            </form>

            <p class="mt-10 text-center text-sm text-gray-500">
                มีบัญชีอยู่แล้ว?
                <a href="login.php" class="font-semibold text-gray-600 hover:text-gray-500">เข้าสู่ระบบ</a>
            </p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let debounceTimer;

            function debounce(callback, delay) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(callback, delay);
            }

            function validateForm() {
                let usernameValid = $("#username_status").css("color") === "rgb(81, 255, 84)"; // สีเขียว
                let emailValid = $("#email_status").css("color") === "rgb(81, 255, 84)"; // สีเขียว
                let passwordValid = $("#password_status").css("color") === "rgb(81, 255, 84)"; // สีเขียว
                let confirmPasswordValid = $("#confirm_password_status").css("color") === "rgb(81, 255, 84)"; // สีเขียว

                $("#submitBtn").prop("disabled", !(usernameValid && emailValid && passwordValid && confirmPasswordValid));
            }

            $("#username, #email, #password, #c_password").on("keyup change", function() {
                validateForm();
            });

            $("#username").on("keyup", function() {
                let username = $(this).val().trim();
                if (username.length >= 3) {
                    debounce(() => {
                        $.post("check_user.php", {
                            username: username
                        }, function(response) {
                            let data = JSON.parse(response);
                            $("#username_status")
                                .text(data.message)
                                .css("color", data.status === "error" ? "#ff5151" : "#51ff54");
                            validateForm();
                        });
                    }, 500);
                } else {
                    $("#username_status").text("");
                }
            });

            $("#email").on("keyup", function() {
                let email = $(this).val().trim();
                let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (email === "") {
                    $("#email_status").text("").css("color", "");
                    return;
                }

                if (emailRegex.test(email)) {
                    debounce(() => {
                        $.post("check_user.php", {
                            email: email
                        }, function(response) {
                            let data = JSON.parse(response);
                            $("#email_status")
                                .text(data.message)
                                .css("color", data.status === "error" ? "#ff5151" : "#51ff54");
                            validateForm();
                        });
                    }, 500);
                } else {
                    $("#email_status").text("❌ อีเมลไม่ถูกต้อง").css("color", "#ff5151");
                }
            });

            $("#password").on("keyup", function() {
                let password = $(this).val();
                let password_status = $("#password_status");

                let conditions = [{
                        regex: /.{8,}/,
                        message: "🔹 อย่างน้อย 8 ตัวอักษร"
                    },
                    {
                        regex: /[A-Z]/,
                        message: "🔹 ตัวพิมพ์ใหญ่ 1 ตัว (A-Z)"
                    },
                    {
                        regex: /[a-z]/,
                        message: "🔹 ตัวพิมพ์เล็ก 1 ตัว (a-z)"
                    },
                    {
                        regex: /\d/,
                        message: "🔹 ตัวเลข 1 ตัว (0-9)"
                    },
                    {
                        regex: /[@_$!%*?&]/,
                        message: "🔹 อักขระพิเศษ 1 ตัว (@, _, #, $, % ฯลฯ)"
                    }
                ];

                let errorMessages = conditions
                    .filter(cond => !cond.regex.test(password))
                    .map(cond => cond.message);

                if (errorMessages.length > 0) {
                    password_status.html(`<span style="color: #ff5151;">❌ รหัสผ่านต้องมี:</span><br>${errorMessages.join("<br>")}`);
                } else {
                    password_status.text("✅ รหัสผ่านปลอดภัย!").css("color", "#51ff54");
                }
                validateForm();
            });

            $("#c_password").on("keyup", function() {
                let password = $("#password").val();
                let confirmPassword = $(this).val();
                let confirm_status = $("#confirm_password_status");

                confirm_status
                    .text(password === confirmPassword && confirmPassword !== "" ? "✅ รหัสผ่านตรงกัน!" : "❌ รหัสผ่านไม่ตรงกัน!")
                    .css("color", password === confirmPassword && confirmPassword !== "" ? "#51ff54" : "#ff5151");

                validateForm();
            });
        });
    </script>



    <?php include('../template/footer.php'); ?>
