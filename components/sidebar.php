<?php
include 'php/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    $content = $_POST["content"];
    $image = "";

    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "php/uploads/";
        $image = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
    }

    $sql = "INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $content, $image);
    $stmt->execute();
    header("Location: feed.php");
}
?>

<div class="">
    <link rel="stylesheet" href="assets/css/style.css">

    <aside class="w-full bg-neutral-900 p-5 rounded-xl shadow-xl text-center flex flex-col items-center mb-4">

        <div class="w-full flex items-center justify-between text-white">
            <a href="profile.php?accoute_id=<?php echo $_SESSION['user_id']; ?>" class="flex items-center">
                <div class="w-10 h-10 flex items-center justify-center rounded-full border-1 border-gray-700 shadow-lg bg-gray-800 overflow-hidden mr-2">
                    <?php if (!empty($_SESSION['avatar']) && file_exists($_SESSION['avatar'])): ?>
                        <img src="<?php echo $_SESSION['avatar']; ?>" alt="Profile Picture" class="w-full h-full object-cover">
                    <?php else: ?>
                        <ion-icon name="person-circle-outline" class="text-gray-400 text-9xl"></ion-icon>
                    <?php endif; ?>
                </div>
            </a>

            <button id="openModalPost" type="button" class="w-full cursor-pointer text-left text-gray-500 font-semibold points px-4 py-2 bg-neutral-900 border border-2 border-neutral-700 rounded-full">คุณคิดอะไรอยู่ <?php echo $_SESSION['username']; ?></button>
        </div>


    </aside>


    <!-- Modal -->
    <div id="modalPost" class="fixed inset-0 z-10 hidden flex items-center justify-center">
        <!-- ฉากหลัง -->
        <div id="modalPostBackdrop" class="absolute inset-0 bg-neutral-900/75 opacity-0 transition-opacity"></div>

        <!-- Modal Content -->
        <div class="relative bg-neutral-900 w-full max-w-lg rounded-lg shadow-xl transform opacity-0 scale-95 transition-all">
            <div class="p-6">
                <div class="w-full flex items-center justify-between text-white mb-5">
                    <div class="flex items-center">
                        <div class="w-10 h-10 flex items-center justify-center rounded-full border-1 border-gray-700 shadow-lg bg-gray-800 overflow-hidden mr-2">
                            <?php if (!empty($_SESSION['avatar']) && file_exists($_SESSION['avatar'])): ?>
                                <img src="<?php echo $_SESSION['avatar']; ?>" alt="Profile Picture" class="w-full h-full object-cover">
                            <?php else: ?>
                                <ion-icon name="person-circle-outline" class="text-gray-400 text-9xl"></ion-icon>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-lg font-semibold"><?php echo $_SESSION['display_name']; ?></p>
                            <p class="text-sm text-gray-500"><?php echo $_SESSION['username']; ?></p>
                        </div>
                    </div>
                    <!-- ปุ่มปิด Modal -->
                    <button id="closeModalPost" class="text-gray-400 hover:text-white text-2xl cursor-pointer"><ion-icon name="close-outline"></ion-icon></button>
                </div>

                <form action="php/upload_post.php" method="POST" enctype="multipart/form-data">
                    <div class="rounded-md bg-neutral-700 mb-3 p-3">
                        <textarea name="post_text" placeholder="เขียนอะไรบางอย่าง..."
                            class="mt-1 w-full custom-scrollbar h-32 resize-none border border-neutral-900 text-white min-h-[100px] text-white rounded-lg border-2 border-transparent focus:outline-none"
                            rows="3" id="post"></textarea>

                        <div id="previewContainer" class="mt-2 hidden ring-1 ring-neutral-500 rounded-lg p-2 flex justify-center">
                            <img id="imagePreview" class="w-auto h-100 object-cover rounded-lg">
                        </div>
                        <input type="file" name="post_image" id="fileInput" accept="image/*" class="hidden">
                        <label for="fileInput" class="cursor-pointer flex items-center space-x-2 mt-2">
                            <ion-icon name="image-outline" class="text-gray-400 text-3xl"></ion-icon>
                            <span id="fileName" class="text-gray-300">เพิ่มรูปภาพ</span>
                        </label>
                    </div>
                    <button type="submit" class="w-full flex p-2 rounded-lg bg-white text-black justify-center font-semibold cursor-pointer">โพสต์</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modalEdit" class="fixed inset-0 z-100 hidden flex items-center justify-center">
        <!-- ฉากหลัง -->
        <div id="modalEditBackdrop" class="absolute inset-0 bg-neutral-900/75 opacity-0 transition-opacity"></div>

        <!-- Modal Content -->
        <div class="relative bg-neutral-900 w-full max-w-lg sm:max-w-md md:max-w-sm lg:max-w-md h-full sm:h-auto rounded-lg shadow-xl transform opacity-0 scale-95 transition-all overflow-y-auto">
            <div class="p-6">
                <div class="w-full flex items-center justify-between text-white mb-5">
                    <div class="flex items-center">
                        <div class="w-10 h-10 flex items-center justify-center rounded-full border-1 border-gray-700 shadow-lg bg-gray-800 overflow-hidden mr-2">
                            <?php if (!empty($_SESSION['avatar']) && file_exists($_SESSION['avatar'])): ?>
                                <img src="<?php echo $_SESSION['avatar']; ?>" alt="Profile Picture" class="w-full h-full object-cover">
                            <?php else: ?>
                                <ion-icon name="person-circle-outline" class="text-gray-400 text-9xl"></ion-icon>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-lg font-semibold"><?php echo $_SESSION['display_name']; ?></p>
                            <p class="text-sm text-gray-500"><?php echo $_SESSION['username']; ?></p>
                        </div>
                    </div>
                    <!-- ปุ่มปิด Modal -->
                    <button id="closeModalEdit" class="text-gray-400 hover:text-white text-2xl cursor-pointer"><ion-icon name="close-outline"></ion-icon></button>
                </div>

                <form action="php/update_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="rounded-md mb-3 p-3 space-y-6">

                        <div id="previewProfile" class="mt-2 hidden ring-1 ring-neutral-500 rounded-lg p-2 flex justify-center">
                            <img id="ProfilePreview" class="w-auto h-50 object-cover rounded-lg">
                        </div>
                        <input type="file" name="avatar" id="profileInput" accept="image/*" class="hidden">
                        <label for="profileInput" class="cursor-pointer flex items-center space-x-2 mt-2">
                            <ion-icon name="image-outline" class="text-gray-400 text-3xl"></ion-icon>
                            <span id="fileProfileName" class="text-gray-300">เปลี่ยนรูปโปรไฟล์</span>
                        </label>

                        <div>
                            <label for="display_name" class="block text-sm font-medium text-white">ชื่อที่แสดง</label>
                            <div class="mt-2">
                                <input type="text" name="display_name" id="display_name" value="<?php echo $_SESSION['display_name']; ?>" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 outline-gray-300 placeholder-gray-400 focus:outline-gray-600 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-white">ชื่อผู้ใช้</label>
                            <div class="mt-2">
                                <input type="text" name="username" id="username" value=<?php echo $_SESSION['username']; ?> class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 outline-gray-300 placeholder-gray-400 focus:outline-gray-600 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-white">คำอธิบาย</label>
                            <div class="mt-2">
                                <input type="text" name="description" id="description" maxlength="250" value="<?php echo $_SESSION['description']; ?>" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 outline-gray-300 placeholder-gray-400 focus:outline-gray-600 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-white">อีเมล (จำเป็นต้องกรอกรหัสผ่าน)</label>
                            <div class="mt-2">
                                <input type="email" name="email" id="email" value=<?php echo $_SESSION['email']; ?> required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 outline-gray-300 placeholder-gray-400 focus:outline-gray-600 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-white">รหัสผ่าน (เพื่อยืนยันการเปลี่ยนแปลงอีเมล):</label>
                            <div class="mt-2">
                                <input type="password" name="password" id="password" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 outline-gray-300 placeholder-gray-400 focus:outline-gray-600 sm:text-sm">
                            </div>
                            <button class="flex justify-center my-2 text-right hover:underline cursor-pointer" id="openModalPassword" type="button">เปลี่ยนรหัสผ่าน</button>
                        </div>
                    </div>
                    <button type="submit" class="w-full flex p-2 rounded-lg bg-white text-black justify-center font-semibold cursor-pointer">บันทึก</button>
                </form>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div id="modalPassword" class="fixed inset-0 z-100 hidden flex items-center justify-center">
        <!-- ฉากหลัง -->
        <div id="modalPasswordBackdrop" class="absolute inset-0 bg-neutral-900/75 opacity-0 transition-opacity"></div>

        <!-- Modal Content -->
        <div class="relative bg-neutral-900 w-full max-w-lg sm:max-w-md md:max-w-sm lg:max-w-md h-full sm:h-auto rounded-lg shadow-xl transform opacity-0 scale-95 transition-all overflow-y-auto">
            <div class="p-6">
                <div class="w-full flex items-center justify-between text-white mb-5">
                    <div class="flex items-center">
                        <div class="w-10 h-10 flex items-center justify-center rounded-full border-1 border-gray-700 shadow-lg bg-gray-800 overflow-hidden mr-2">
                            <?php if (!empty($_SESSION['avatar']) && file_exists($_SESSION['avatar'])): ?>
                                <img src="<?php echo $_SESSION['avatar']; ?>" alt="Profile Picture" class="w-full h-full object-cover">
                            <?php else: ?>
                                <ion-icon name="person-circle-outline" class="text-gray-400 text-9xl"></ion-icon>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-lg font-semibold"><?php echo $_SESSION['username']; ?></p>
                            <p class="text-sm text-gray-500"><?php echo $_SESSION['email']; ?></p>
                        </div>
                    </div>
                    <!-- ปุ่มปิด Modal -->
                    <button id="closeModalPassword" class="text-gray-400 hover:text-white text-2xl cursor-pointer"><ion-icon name="close-outline"></ion-icon></button>
                </div>

                <form action="php/update_password.php" method="POST" enctype="multipart/form-data">
                    <div class="rounded-md mb-3 p-3 space-y-6">

                        <div>
                            <label for="passwordOld" class="block text-sm font-medium text-white">รหัสผ่านปัจจุบัน</label>
                            <div class="mt-2">
                                <input type="password" name="passwordOld" id="passwordOld" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 outline-gray-300 placeholder-gray-400 focus:outline-gray-600 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="passwordNew" class="block text-sm font-medium text-white">รหัสผ่านใหม่</label>
                            <div class="mt-2">
                                <input type="password" name="passwordNew" id="passwordNew" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 outline-gray-300 placeholder-gray-400 focus:outline-gray-600 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="passwordConfirm" class="block text-sm font-medium text-white">ยืนยันรหัสผ่านใหม่</label>
                            <div class="mt-2">
                                <input type="password" name="passwordConfirm" id="passwordConfirm" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-black outline-1 outline-gray-300 placeholder-gray-400 focus:outline-gray-600 sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full flex p-2 rounded-lg bg-white text-black justify-center font-semibold cursor-pointer">ยืนยัน</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modalPost = document.getElementById("modalPost");
        const modalPostBackdrop = document.getElementById("modalPostBackdrop");
        const openModalPostBtn = document.getElementById("openModalPost");
        const closeModalPostBtn = document.getElementById("closeModalPost");

        const modalEdit = document.getElementById("modalEdit");
        const modalEditBackdrop = document.getElementById("modalEditBackdrop");
        const openModalEditBtn = document.getElementById("openModalEdit");
        const closeModalEditBtn = document.getElementById("closeModalEdit");

        const modalPassword = document.getElementById("modalPassword");
        const modalPasswordBackdrop = document.getElementById("modalPasswordBackdrop");
        const openModalPasswordBtn = document.getElementById("openModalPassword");
        const closeModalPasswordBtn = document.getElementById("closeModalPassword");

        function openModal(modal, backdrop) {
            modal.classList.remove("hidden");
            setTimeout(() => {
                backdrop.classList.remove("opacity-0");
                modal.children[1].classList.remove("opacity-0", "scale-95");
                modal.children[1].classList.add("opacity-100", "scale-100");
            }, 10);
        }

        function closeModal(modal, backdrop) {
            backdrop.classList.add("opacity-0");
            modal.children[1].classList.add("opacity-0", "scale-95");
            modal.children[1].classList.remove("opacity-100", "scale-100");
            setTimeout(() => modal.classList.add("hidden"), 200);
        }

        if (openModalPostBtn && closeModalPostBtn) {
            openModalPostBtn.addEventListener("click", () => openModal(modalPost, modalPostBackdrop));
            closeModalPostBtn.addEventListener("click", () => closeModal(modalPost, modalPostBackdrop));
        }

        if (openModalEditBtn && closeModalEditBtn) {
            openModalEditBtn.addEventListener("click", () => openModal(modalEdit, modalEditBackdrop));
            closeModalEditBtn.addEventListener("click", () => closeModal(modalEdit, modalEditBackdrop));
        }

        if (openModalPasswordBtn && closeModalPasswordBtn) {
            openModalPasswordBtn.addEventListener("click", () => openModal(modalPassword, modalPasswordBackdrop));
            closeModalPasswordBtn.addEventListener("click", () => closeModal(modalPassword, modalPasswordBackdrop));
        }

        // ปิด Modal เมื่อกดที่พื้นหลัง
        modalPostBackdrop.addEventListener("click", () => closeModal(modalPost, modalPostBackdrop));
        modalEditBackdrop.addEventListener("click", () => closeModal(modalEdit, modalEditBackdrop));
        modalPasswordBackdrop.addEventListener("click", () => closeModal(modalPassword, modalEditBackdrop));

        // อัปเดตการแสดงตัวอย่างรูป
        const fileInput = document.getElementById("fileInput");
        const fileName = document.getElementById("fileName");
        const previewContainer = document.getElementById("previewContainer");
        const imagePreview = document.getElementById("imagePreview");

        fileInput.addEventListener("change", function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    previewContainer.classList.remove("hidden");
                    fileName.textContent = "เปลี่ยนรูปภาพ";
                };

                reader.readAsDataURL(file);
            }
        });

        // อัปเดตการแสดงตัวอย่างรูป
        const profileInput = document.getElementById("profileInput");
        const fileProfileName = document.getElementById("fileProfileName");
        const previewProfile = document.getElementById("previewProfile");
        const ProfilePreview = document.getElementById("ProfilePreview");

        profileInput.addEventListener("change", function() {
            const profile = this.files[0];
            if (profile) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    ProfilePreview.src = e.target.result; // ใช้ ProfilePreview แทน imagePreview
                    previewProfile.classList.remove("hidden");
                    fileName.textContent = "เปลี่ยนรูปภาพ";
                };

                reader.readAsDataURL(profile); // ใช้ profile แทน file
            }
        });

        document.getElementById("logoutLink").addEventListener("click", function(event) {
            event.preventDefault(); // ป้องกันการไปที่ลิงก์ทันที
            let confirmLogout = confirm("คุณแน่ใจหรือไม่ว่าต้องการออกจากระบบ?");
            if (confirmLogout) {
                window.location.href = this.href; // ไปที่ logout.php ถ้ายืนยัน
            }
        });
    </script>


</div>