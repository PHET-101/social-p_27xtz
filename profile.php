<?php include('template/header.php'); ?>
<?php
include 'php/config.php';

$user_id = $_SESSION['user_id'];

// ตรวจสอบว่ามีการส่ง `accoute_id` หรือไม่
if (!isset($_GET['accoute_id'])) {
    die("ไม่พบผู้ใช้ที่ต้องการแสดง");
}

$accoute_id = intval($_GET['accoute_id']); // รับค่า accoute_id จาก URL และป้องกัน SQL Injection

// ดึงข้อมูลโปรไฟล์จากฐานข้อมูล
$sql = "SELECT id, display_name, username, email, description, avatar, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $accoute_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("ไม่พบโปรไฟล์ผู้ใช้");
}

$user = $result->fetch_assoc();

// ตรวจสอบจำนวนผู้ติดตาม
$followerCountQuery = "SELECT COUNT(*) as follower_count FROM followers WHERE followed_id = ?";
$stmt = $conn->prepare($followerCountQuery);
$stmt->bind_param("i", $accoute_id);
$stmt->execute();
$followerCountResult = $stmt->get_result();
$followerCount = $followerCountResult->fetch_assoc()['follower_count'];

// ตรวจสอบจำนวนที่กำลังติดตาม
$followingCountQuery = "SELECT COUNT(*) as following_count FROM followers WHERE follower_id = ?";
$stmt = $conn->prepare($followingCountQuery);
$stmt->bind_param("i", $accoute_id);
$stmt->execute();
$followingCountResult = $stmt->get_result();
$followingCount = $followingCountResult->fetch_assoc()['following_count'];

// ตรวจสอบว่าผู้ใช้กำลังติดตามหรือไม่
$isFollowing = false; // กำหนดค่าเริ่มต้น
if (isset($user_id) && $user_id != $accoute_id) {
    // ตรวจสอบสถานะการติดตามจากฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?");
    $stmt->bind_param("ii", $user_id, $accoute_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $isFollowing = true; // ถ้ามีข้อมูลแสดงว่าผู้ใช้ติดตามกัน
    }
}

function formatNumber($num)
{
    if ($num >= 1000000) {
        return number_format($num / 1000000, 1) . 'M';
    } elseif ($num >= 1000) {
        return number_format($num / 1000, 1) . 'K';
    }
    return $num; // น้อยกว่า 1K แสดงค่าปกติ
}

// ฟังก์ชันแสดงเวลา
function timeAgo($timestamp)
{
    date_default_timezone_set('Asia/Bangkok');
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;

    $seconds = $time_difference;
    $minutes      = round($seconds / 60);
    $hours        = round($seconds / 3600);
    $days         = round($seconds / 86400);
    $weeks        = round($seconds / 604800);
    $months       = round($seconds / 2629440);
    $years        = round($seconds / 31553280);

    if ($seconds <= 60) {
        return "ไม่กี่วินาทีที่แล้ว";
    } else if ($minutes <= 60) {
        if ($minutes == 1) {
            return "1 นาทีที่แล้ว";
        } else {
            return "$minutes นาทีที่แล้ว";
        }
    } else if ($hours <= 24) {
        if ($hours == 1) {
            return "1 ชั่วโมงที่แล้ว";
        } else {
            return "$hours ชั่วโมงที่แล้ว";
        }
    } else if ($days <= 7) {
        if ($days == 1) {
            return "1 วันที่แล้ว";
        } else {
            return "$days วันที่แล้ว";
        }
    } else if ($weeks <= 4.3) { // 4.3 == 30/7
        if ($weeks == 1) {
            return "1 สัปดาห์ที่แล้ว";
        } else {
            return "$weeks สัปดาห์ที่แล้ว";
        }
    } else if ($months <= 12) {
        if ($months == 1) {
            return "1 เดือนที่แล้ว";
        } else {
            return "$months เดือนที่แล้ว";
        }
    } else {
        if ($years == 1) {
            return "1 ปีที่แล้ว";
        } else {
            return "$years ปีที่แล้ว";
        }
    }
}

// ดึงข้อมูลโพสต์
$result = $conn->query("SELECT posts.*, users.display_name, users.username, users.avatar,
                        (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count,
                        (SELECT COUNT(*) FROM likes WHERE post_id = posts.id AND user_id = $accoute_id) AS user_liked
                        FROM posts 
                        JOIN users ON posts.user_id = users.id 
                        WHERE posts.user_id = $accoute_id
                        ORDER BY posts.updated_at DESC");



// จำนวนโพสต์
$postCountQuery = "SELECT COUNT(*) as post_count FROM posts WHERE user_id = ?";
$stmt = $conn->prepare($postCountQuery);
$stmt->bind_param("i", $accoute_id);
$stmt->execute();
$postCountResult = $stmt->get_result();
$postCount = $postCountResult->fetch_assoc()['post_count'];
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ของ <?php echo htmlspecialchars($user['display_name']); ?></title>
    <link rel="stylesheet" href="styles.css"> <!-- เพิ่มไฟล์ CSS ถ้าต้องการ -->
</head>

<body>

    <div class="flex justify-center flex-col md:flex-row items-center bg-neutral-900 p-6 rounded-xl shadow-xl text-left mb-4 w-full max-w-2xl mx-auto">
        <!-- Avatar -->
        <div class="w-48 h-48 rounded-full overflow-hidden border-4 border-neutral-700 flex-shrink-0 mb-3 lg:mr-3">
            <?php if (!empty($user['avatar']) && file_exists($user['avatar'])): ?>
                <img src="<?php echo $user['avatar']; ?>" alt="Profile Picture" class="w-full h-full object-cover">
            <?php else: ?>
                <ion-icon name="person-circle-outline" class="text-gray-400 text-9xl"></ion-icon>
            <?php endif; ?>
        </div>

        <!-- User Info -->
        <div class="flex flex-col flex-grow w-full">
            <!-- ชื่อและ Username -->
            <div class="flex flex-col md:flex-row justify-start items-center mb-2 space-x-2">
                <h1 class="text-xl font-bold text-white mb-2"><?php echo htmlspecialchars($user['display_name']); ?></h1>
                <p class="px-3 py-1 bg-neutral-800 text-gray-300 rounded-full text-sm">
                    @<?php echo htmlspecialchars($user['username']); ?>
                </p>
            </div>

            <!-- Stats -->
            <div class="flex justify-between w-full rounded-lg p-3 text-center text-gray-300 text-sm md:text-lg">
                <div class="flex flex-col">
                    <p class="font-bold text-white">โพสต์</p>
                    <p class="font-semibold"><?php echo formatNumber($postCount); ?></p>
                </div>
                <div class="flex flex-col">
                    <p class="font-bold text-white">ผู้ติดตาม</p>
                    <p id="followerCount" class="font-semibold"><?php echo formatNumber($followerCount); ?></p>
                </div>
                <div class="flex flex-col">
                    <p class="font-bold text-white">กำลังติดตาม</p>
                    <p class="font-semibold"><?php echo formatNumber($followingCount); ?></p>
                </div>
            </div>

            <!-- คำอธิบาย -->
            <p class="py-3 text-gray-300 rounded-lg break-words whitespace-normal w-fit max-w-80">
                <?php echo nl2br(htmlspecialchars($user['description'])); ?>
            </p>

            <!-- ปุ่มแก้ไขโปรไฟล์ -->
            <?php if ($user_id == $accoute_id): ?>
                <button id="openModalEdit" class="flex justify-center items-center mt-4 px-6 py-1 cursor-pointer bg-white text-black font-semibold rounded-lg hover:bg-gray-200 transition-all duration-300 ease-in-out hover:text-gray-800">
                    แก้ไขโปรไฟล์ <ion-icon name="create-outline" class="ml-2"></ion-icon>
                </button>
            <?php endif; ?>

            <!-- ปุ่มติดตาม -->
            <?php if ($user_id && $user_id != $user['id']): ?>
                <button id="followBtn"
                    data-following="<?= $isFollowing ? 'true' : 'false' ?>"
                    data-follower="<?= $user_id ?>"
                    data-followed="<?= $user['id'] ?>"
                    class="flex w-full justify-center items-center mt-4 px-6 py-1 cursor-pointer bg-white text-black font-semibold rounded-lg hover:bg-gray-200 transition-all duration-300 ease-in-out hover:text-gray-800">
                    <?= $isFollowing ? 'ยกเลิกการติดตาม' : 'ติดตาม'; ?>
                </button>
            <?php endif; ?>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const followBtn = document.getElementById("followBtn");
                    const followerCountElem = document.getElementById("followerCount");

                    if (followBtn && followerCountElem) {
                        followBtn.addEventListener("click", async function() {
                            let isFollowing = followBtn.getAttribute("data-following") === "true";
                            let followerId = followBtn.getAttribute("data-follower");
                            let followedId = followBtn.getAttribute("data-followed");
                            let url = isFollowing ? "php/delete_follow.php" : "php/add_follow.php";

                            let formData = new FormData();
                            formData.append("follower_id", followerId);
                            formData.append("followed_id", followedId);

                            try {
                                let response = await fetch(url, {
                                    method: "POST",
                                    body: formData,
                                });

                                let result = await response.text();
                                if (result.trim() === "success") {
                                    isFollowing = !isFollowing;
                                    followBtn.setAttribute("data-following", isFollowing.toString());
                                    followBtn.textContent = isFollowing ? "ยกเลิกการติดตาม" : "ติดตาม";

                                    // อัปเดตจำนวนผู้ติดตาม
                                    let currentCount = parseInt(followerCountElem.textContent.replace("K", "000").replace("M", "000000")) || 0;
                                    let newCount = isFollowing ? currentCount + 1 : currentCount - 1;
                                    followerCountElem.textContent = formatNumber(newCount);
                                } else {
                                    alert("เกิดข้อผิดพลาด ลองใหม่อีกครั้ง!");
                                }
                            } catch (error) {
                                console.error("Error:", error);
                            }
                        });
                    }

                    // ฟังก์ชันแปลงเลขให้เป็น K หรือ M
                    function formatNumber(num) {
                        if (num >= 1000000) {
                            return (num / 1000000).toFixed(1) + "M";
                        } else if (num >= 1000) {
                            return (num / 1000).toFixed(1) + "K";
                        }
                        return num;
                    }
                });
            </script>

        </div>
    </div>


    <?php include('components/sidebar.php'); ?>

    <?php while ($row = $result->fetch_assoc()): ?>

        <div class="post bg-neutral-900 p-6 rounded-lg shadow-lg mb-6 ">

            <div class="flex w-full justify-between">
                <a href="profile.php?accoute_id=<?php echo $row['user_id']; ?>" class="flex items-center">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-700 shadow-lg bg-gray-800 overflow-hidden mr-2">
                        <?php if (!empty($row['avatar']) && file_exists($row['avatar'])): ?>
                            <img src=<?php echo $row['avatar']; ?> alt="Profile Picture" class="w-full h-full object-cover">
                        <?php else: ?>
                            <ion-icon name="person-circle-outline" class="text-gray-400 text-9xl"></ion-icon>
                        <?php endif; ?>
                    </div>

                    <div>
                        <div class="flex items-end">
                            <p class="text-lg font-semibold mr-2"><?php echo $row['display_name']; ?></p>
                        </div>
                        <p class="text-sm text-gray-500"><?php echo timeAgo($row['updated_at']); ?></p>
                    </div>
                </a>
                <?php if ($user_id == $row['user_id']): ?>
                    <div class="relative inline-block text-left">
                        <div>
                            <button type="button" class="menu-button inline-flex w-full justify-center gap-x-1.5 rounded-md px-3 py-2 text-xl font-semibold text-gray-100 cursor-pointer"
                                aria-expanded="false" aria-haspopup="true">
                                <ion-icon name="menu-outline"></ion-icon>
                            </button>
                        </div>

                        <!-- Dropdown menu -->
                        <div class="menu-dropdown absolute right-0 z-10 flex p-2 origin-top-right rounded-md bg-neutral-700 ring-1 shadow-lg ring-black/5 opacity-0 scale-95 transform transition-all duration-200 ease-out pointer-events-none">
                            <div class="flex justify-center items-center w-auto">
                                <p class="text-sm text-gray-300 flex-col justify-between w-full items-center space-x-2">
                                    <a href="#" class="flex items-center text-nowrap text-white hover:bg-neutral-500 edit-post p-2 w-full rounded-lg"
                                        data-id="<?= $row['id'] ?>" data-content="<?= htmlspecialchars($row['content']) ?>">
                                        <ion-icon name="pencil-outline" class="text-white text-xl mr-1"></ion-icon> แก้ไข
                                    </a>
                                    <a href="#" class="flex items-center text-nowrap text-white hover:bg-neutral-500 delete-post p-2 w-full rounded-lg"
                                        data-id="<?= $row['id'] ?>">
                                        <ion-icon name="trash-bin-outline" class="text-white text-xl mr-1"></ion-icon> ลบ
                                    </a>
                                </p>

                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <p class="post-content text-white mt-2"><?= htmlspecialchars($row['content']) ?></p>

            <?php if (!empty($row['image_path'])): ?>
                <div class="flex justify-center">
                    <img src="<?= $row['image_path'] ?>" alt="โพสต์รูปภาพ" class="mt-4 rounded-lg max-w-full h-auto">
                </div>
            <?php endif; ?>

            <?php
            $commentCount = $conn->query("SELECT COUNT(*) as count FROM comments WHERE post_id = {$row['id']}")->fetch_assoc()['count'];
            ?>

            <hr class="border-t border-gray-500 my-2">
            <!-- ปุ่มไลค์ -->
            <div class="w-full flex items-center justify-between text-white gap-2">
                <div class="comment-count text-sm text-gray-300">
                    <?php echo formatNumber($commentCount); ?> ความคิดเห็น
                </div>

                <div class="flex items-center text-white gap-2">
                    <span id="like-count-<?= $row['id'] ?>" class="font-semibold"><?= formatNumber($row['like_count']); ?></span>
                    <a href="#" class="like-button text-blue-500 hover:underline like-icon flex items-center"
                        data-id="<?= $row['id'] ?>"
                        data-action="<?= ($row['user_liked'] > 0) ? 'unlike' : 'like' ?>">
                        <?= ($row['user_liked'] > 0) ? '<ion-icon name="heart" class="text-xl"></ion-icon>' : '<ion-icon name="heart-outline" class="text-xl"></ion-icon>' ?>
                    </a>
                </div>
            </div>

            <hr class="border-t border-gray-500 my-2">
            <!-- ฟอร์มแสดงความคิดเห็น -->
            <?php if ($user_id): ?>
                <form class="comment-form mt-4 flex justify-between items-center w-full">
                    <input type="hidden" name="post_id" value="<?= $row['id'] ?>">
                    <input type="text" name="content" required placeholder="เขียนความคิดเห็น..." class="w-full mr-3 px-4 py-2 border border-neutral-300 rounded-full shadow-sm focus:outline-none focus:ring-1 focus:ring-neutral-300">
                    <button type="submit" class="flex justify-center items-center text-white send-icon cursor-pointer"><ion-icon name="send"></ion-icon></button>
                </form>
            <?php endif; ?>


            <?php if ($commentCount > 0): ?>
                <hr class="border-t border-gray-500 my-4">
            <?php endif; ?>


            <!-- แสดงคอมเมนต์ -->
            <div id="comments-<?= $row['id'] ?>" class="mt-4 space-y-2 rounded-lg max-h-80 cursor-pointer overflow-y-auto rounded-lg custom-scrollbar">
                <?php
                $comments = $conn->query("SELECT comments.*, users.display_name, users.username, users.avatar FROM comments 
              JOIN users ON comments.user_id = users.id 
              WHERE comments.post_id = {$row['id']} ORDER BY comments.updated_at ASC");

                while ($comment = $comments->fetch_assoc()):
                ?>
                    <div id="comment-<?= $comment['id'] ?>" class="flex items-start space-x-2">
                        <div class="flex-col">
                            <div class="flex items-center mb-2">

                                <a href="profile.php?accoute_id=<?php echo $comment['user_id']; ?>" class="flex items-center justify-center ">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-700 shadow-lg bg-gray-800 overflow-hidden mr-2">
                                        <?php if (!empty($comment['avatar']) && file_exists($comment['avatar'])): ?>
                                            <img src="<?= $comment['avatar'] ?>" alt="Profile Picture" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <ion-icon name="person-circle-outline" class="text-gray-400 text-3xl"></ion-icon>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="flex items-end">
                                            <p class="text-lg font-semibold text-white mr-2"><?php echo $comment['display_name']; ?></p>
                                        </div>

                                        <p class="text-sm text-gray-500"><?php echo timeAgo($comment['updated_at']); ?></p>
                                    </div>
                                </a>
                                <?php if ($user_id == $comment['user_id']): ?>
                                    <!-- ปุ่มเมนู dropdown -->
                                    <div class="relative inline-block text-left ml-5">
                                        <button type="button" class="comment-menu-button text-gray-400 hover:text-gray-200 focus:outline-none cursor-pointer">
                                            <ion-icon name="ellipsis-vertical" class="text-xl"></ion-icon>
                                        </button>

                                        <!-- Dropdown menu -->
                                        <div class="comment-menu-dropdown absolute right-0 z-10 hidden w-32 bg-neutral-800 text-white rounded-md shadow-lg ring-1 ring-black/5 transition-all">
                                            <div class="flex justify-center items-center w-auto p-2">
                                                <p class="text-sm text-gray-300 flex-col justify-between w-full items-center space-x-2">
                                                    <a href="#" class="flex items-center text-nowrap text-white hover:bg-neutral-500 edit-comment p-2 w-full rounded-md"
                                                        data-id="<?= $comment['id'] ?>"
                                                        data-content="<?= htmlspecialchars($comment['content']) ?>">
                                                        <ion-icon name="pencil-outline" class="mr-1"></ion-icon> แก้ไข
                                                    </a>
                                                    <a href="#" class="flex items-center text-nowrap text-white hover:bg-neutral-500 delete-post p-2 w-full rounded-md"
                                                        data-id="<?= $comment['id'] ?>">
                                                        <ion-icon name="trash-bin-outline" class="mr-1"></ion-icon> ลบ
                                                    </a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="bg-neutral-700 p-3 text-gray-300 rounded-lg break-words whitespace-normal w-fit ml-12 max-w-96 ">
                                <span class="comment-content "><?= htmlspecialchars($comment['content']) ?></span>
                            </div>

                        </div>


                    </div>
                <?php endwhile; ?>
            </div>


        </div>
    <?php endwhile; ?>


    <script>
        $(document).ready(function() {
            // แก้ไขโพสต์
            $(document).on("click", ".edit-post", function(e) {
                e.preventDefault();

                let button = $(this);
                let postId = button.data("id");
                let postElement = button.closest(".post");
                let contentElement = postElement.find(".post-content");

                if (contentElement.find("textarea").length > 0) return; // ป้องกัน textarea ซ้อนกัน

                let oldContent = contentElement.text().trim();

                let textarea = $(`<textarea class="w-full p-2 bg-neutral-700 text-white rounded">${oldContent}</textarea>`);
                contentElement.html(textarea);
                textarea.focus();

                textarea.on("blur keyup", function(e) {
                    if (e.type === "blur" || (e.type === "keyup" && e.key === "Enter" && !e.shiftKey)) {
                        let newContent = textarea.val().trim();

                        if (newContent === "" || newContent === oldContent) {
                            contentElement.html(oldContent);
                            return;
                        }

                        $.ajax({
                            url: "php/edit_post.php",
                            type: "POST",
                            data: {
                                id: postId,
                                content: newContent
                            },
                            dataType: "json",
                            success: function(response) {
                                if (response.status === "success") {
                                    contentElement.html(response.content);
                                    button.data("content", response.content);
                                } else {
                                    alert(response.message);
                                    contentElement.html(oldContent);
                                }
                            },
                            error: function() {
                                alert("เกิดข้อผิดพลาดในการแก้ไขโพสต์");
                                contentElement.html(oldContent);
                            }
                        });
                    }
                });
            });


            // ลบโพสต์
            $(document).on("click", ".delete-post", function(e) {
                e.preventDefault();
                if (!confirm("คุณแน่ใจหรือไม่ว่าต้องการลบโพสต์นี้?")) return;

                let button = $(this);
                let postId = button.data("id");

                $.ajax({
                    url: "php/delete_post.php",
                    type: "POST",
                    data: {
                        id: postId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            button.closest(".post").remove();
                        }
                    }
                });
            });

            // กดไลค์ / ยกเลิกไลค์
            $(document).on("click", ".like-button", function(e) {
                e.preventDefault();
                let button = $(this);
                let postId = button.data("id");
                let action = button.data("action");

                $.ajax({
                    url: "php/like_post.php",
                    type: "GET",
                    data: {
                        id: postId,
                        action: action
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            let newAction = response.action === "like" ? "unlike" : "like";
                            let newIcon = response.action === "like" ? "heart" : "heart-outline";

                            button.data("action", newAction);
                            button.find("ion-icon").attr("name", newIcon);
                            $("#like-count-" + postId).text(response.like_count);
                        }
                    }
                });
            });

            function timeAgo(dateString) {
                const now = new Date();
                const date = new Date(dateString);
                const seconds = Math.floor((now - date) / 1000);
                const minutes = Math.floor(seconds / 60);
                const hours = Math.floor(minutes / 60);
                const days = Math.floor(hours / 24);
                const weeks = Math.floor(days / 7);
                const months = Math.floor(days / 30.42); // คำนวณเดือนเฉลี่ย
                const years = Math.floor(days / 365);

                if (seconds <= 60) {
                    return "ไม่กี่วินาทีที่แล้ว";
                } else if (minutes <= 60) {
                    return minutes === 1 ? "1 นาทีที่แล้ว" : `${minutes} นาทีที่แล้ว`;
                } else if (hours <= 24) {
                    return hours === 1 ? "1 ชั่วโมงที่แล้ว" : `${hours} ชั่วโมงที่แล้ว`;
                } else if (days <= 7) {
                    return days === 1 ? "1 วันที่แล้ว" : `${days} วันที่แล้ว`;
                } else if (weeks <= 4.3) {
                    return weeks === 1 ? "1 สัปดาห์ที่แล้ว" : `${weeks} สัปดาห์ที่แล้ว`;
                } else if (months <= 12) {
                    return months === 1 ? "1 เดือนที่แล้ว" : `${months} เดือนที่แล้ว`;
                } else {
                    return years === 1 ? "1 ปีที่แล้ว" : `${years} ปีที่แล้ว`;
                }
            }


            // เพิ่มความคิดเห็น
            $(document).on("submit", ".comment-form", function(e) {
                e.preventDefault();
                let form = $(this);
                let postId = form.find("input[name='post_id']").val();
                let content = form.find("input[name='content']").val().trim();

                if (content === "") return;

                $.ajax({
                    url: "php/add_comment.php",
                    type: "POST",
                    data: {
                        post_id: postId,
                        content: content
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            // แปลงเวลาของ comment ด้วย timeAgo
                            const timeAgoText = timeAgo(response.updated_at);

                            const newComment = `
            <div id="comment-${response.comment_id}" class="comment-item flex items-start space-x-2">
                <div class="w-10 h-10 flex items-center justify-center rounded-full border border-gray-700 shadow-lg bg-gray-800 overflow-hidden mr-2">
                    ${response.avatar 
                        ? `<img src="${response.avatar}" alt="Profile Picture" class="w-full h-full object-cover">`
                        : `<ion-icon name="person-circle-outline" class="text-gray-400 text-3xl"></ion-icon>`}
                </div>
                <div class="flex-col w-full">
                    <div class="flex items-center mb-2">
                        <p class="text-lg font-semibold text-white">${response.username}</p>
                        <p class="text-sm text-gray-500 ml-3">${timeAgoText}</p>

                        <!-- ปุ่มเมนู dropdown -->
                        <div class="relative inline-block text-left ml-5">
                            <button type="button" class="comment-menu-button text-gray-400 hover:text-gray-200 focus:outline-none cursor-pointer">
                                <ion-icon name="ellipsis-vertical" class="text-xl"></ion-icon>
                            </button>

                            <div class="comment-menu-dropdown absolute right-0 z-10 hidden w-32 bg-neutral-800 text-white rounded-md shadow-lg ring-1 ring-black/5 transition-all">
                                <a href="#" class="block px-4 py-2 text-sm hover:bg-neutral-700 edit-comment"
                                    data-id="${response.comment_id}"
                                    data-content="${response.content}">
                                    <ion-icon name="pencil-outline" class="mr-1"></ion-icon> แก้ไข
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-red-400 hover:bg-neutral-700 delete-comment"
                                    data-id="${response.comment_id}">
                                    <ion-icon name="trash-bin-outline" class="mr-1"></ion-icon> ลบ
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- คอมเมนต์ -->
                    <div class="bg-neutral-700 p-3 text-gray-300 rounded-lg break-words whitespace-normal w-fit max-w-[75%] comment-content">
                        <span class="comment-text">${response.content}</span>
                    </div>
                </div>
            </div>`;

                            // แสดงคอมเมนต์ใหม่
                            $("#comments-" + postId).append(newComment);

                            // เคลียร์ข้อความในฟอร์ม
                            form.find("input[name='content']").val("");
                        }
                    }
                });
            });


            // แก้ไขความคิดเห็น
            $(document).on("click", ".edit-comment", function(e) {
                e.preventDefault();

                let button = $(this);
                let commentId = button.data("id");
                let commentElement = $("#comment-" + commentId);
                let contentElement = commentElement.find(".comment-content");

                // ถ้ามี textarea อยู่แล้วให้ return
                if (contentElement.find("textarea").length > 0) return;

                // ใช้ data() เก็บค่าคอมเมนต์เดิม เพื่อไม่ให้หายเมื่อกดแก้ไขหลายครั้ง
                let oldContent = button.data("content") || contentElement.find(".comment-text").text().trim();
                button.data("content", oldContent); // อัปเดตค่าเดิมใน data()

                let textarea = $(`<textarea class="w-full p-2 bg-neutral-700 text-white rounded">${oldContent}</textarea>`);
                contentElement.html(textarea);
                textarea.focus();

                textarea.on("blur keyup", function(e) {
                    let newContent = textarea.val().trim();

                    // บันทึกเมื่อกด Enter (แต่ไม่ใช่ Shift+Enter) หรือหลุดโฟกัส
                    if (e.type === "blur" || (e.type === "keyup" && e.key === "Enter" && !e.shiftKey)) {
                        e.preventDefault();

                        // ถ้าข้อมูลเหมือนเดิม หรือเป็นค่าว่าง ให้คืนค่าเดิม
                        if (newContent === "" || newContent === oldContent) {
                            contentElement.html(`<span class="comment-text">${oldContent}</span>`);
                            return;
                        }

                        $.ajax({
                            url: "php/edit_comment.php",
                            type: "POST",
                            data: {
                                id: commentId,
                                content: newContent
                            },
                            dataType: "json",
                            success: function(response) {
                                if (response.status === "success") {
                                    contentElement.html(`<span class="comment-text">${response.content}</span>`);
                                    button.data("content", response.content); // อัปเดตค่าใหม่
                                } else {
                                    alert(response.message);
                                    contentElement.html(`<span class="comment-text">${oldContent}</span>`);
                                }
                            },
                            error: function() {
                                alert("เกิดข้อผิดพลาดในการแก้ไขคอมเมนต์");
                                contentElement.html(`<span class="comment-text">${oldContent}</span>`);
                            }
                        });
                    }

                    // ยกเลิกเมื่อกด Escape
                    else if (e.type === "keyup" && e.key === "Escape") {
                        contentElement.html(`<span class="comment-text">${oldContent}</span>`);
                    }
                });
            });


            // ลบความคิดเห็น
            $(document).on("click", ".delete-comment", function(e) {
                e.preventDefault();
                if (!confirm("คุณแน่ใจหรือไม่ว่าต้องการลบคอมเมนต์นี้?")) return;

                let button = $(this);
                let commentId = button.data("id");
                let commentElement = $("#comment-" + commentId);
                let contentElement = commentElement.next(".comment-content");

                $.ajax({
                    url: "php/delete_comment.php",
                    type: "POST",
                    data: {
                        id: commentId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            commentElement.fadeOut(300).promise().done(function() {
                                $(this).remove();
                            });
                            contentElement.fadeOut(300).promise().done(function() {
                                $(this).remove();
                            });
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("เกิดข้อผิดพลาด:", xhr.responseText);
                        alert("เกิดข้อผิดพลาดในการลบคอมเมนต์");
                    }
                });
            });


        });

        document.addEventListener("DOMContentLoaded", function() {
            document.body.addEventListener("click", function(event) {
                // ตรวจสอบว่าคลิกปุ่มเมนูหรือไม่
                if (event.target.closest(".menu-button")) {
                    let button = event.target.closest(".menu-button");
                    let menu = button.parentElement.nextElementSibling; // หาเมนูที่อยู่ถัดไป

                    if (menu.classList.contains("opacity-0")) {
                        closeAllMenus(); // ปิดเมนูอื่นก่อน
                        menu.classList.remove("opacity-0", "scale-95", "pointer-events-none");
                        menu.classList.add("opacity-100", "scale-100");
                    } else {
                        menu.classList.remove("opacity-100", "scale-100");
                        menu.classList.add("opacity-0", "scale-95", "pointer-events-none");
                    }
                } else {
                    // ถ้าคลิกข้างนอก ให้ปิดเมนูทั้งหมด
                    if (!event.target.closest(".menu-dropdown")) {
                        closeAllMenus();
                    }
                }
            });

            // ฟังก์ชันปิดเมนูทั้งหมด
            function closeAllMenus() {
                document.querySelectorAll(".menu-dropdown").forEach(menu => {
                    menu.classList.remove("opacity-100", "scale-100");
                    menu.classList.add("opacity-0", "scale-95", "pointer-events-none");
                });
            }

        });

        document.addEventListener("DOMContentLoaded", function() {
            document.body.addEventListener("click", function(event) {
                // เปิด/ปิดเมนู
                if (event.target.closest(".comment-menu-button")) {
                    let button = event.target.closest(".comment-menu-button");
                    let menu = button.nextElementSibling;

                    if (menu.classList.contains("hidden")) {
                        closeAllMenus();
                        menu.classList.remove("hidden");
                    } else {
                        menu.classList.add("hidden");
                    }
                } else {
                    // ปิดเมนูถ้าคลิกข้างนอก
                    if (!event.target.closest(".comment-menu-dropdown")) {
                        closeAllMenus();
                    }
                }
            });

            // ฟังก์ชันปิดเมนูทั้งหมด
            function closeAllMenus() {
                document.querySelectorAll(".comment-menu-dropdown").forEach(menu => {
                    menu.classList.add("hidden");
                });
            }
        });
    </script>

</body>

</html>

<?php include('template/footer.php'); ?>