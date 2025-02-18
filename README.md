http://social65209010027.infinityfreeapp.com


คู่มือการติดตั้งและใช้งาน XAMPP เพื่อเปิดไฟล์ .php

1. ดาวน์โหลดและติดตั้ง XAMPP

1.1 ดาวน์โหลด XAMPP

1. ไปที่เว็บไซต์ Apache Friends


2. เลือกดาวน์โหลด XAMPP เวอร์ชันที่ต้องการ (แนะนำให้ใช้ PHP เวอร์ชันล่าสุด)


3. รอให้ไฟล์ติดตั้งถูกดาวน์โหลด



1.2 ติดตั้ง XAMPP

1. เปิดไฟล์ติดตั้งที่ดาวน์โหลดมา


2. กด Next → เลือก Apache, MySQL, PHP (หากต้องการใช้ phpMyAdmin ให้เลือกด้วย)


3. เลือกโฟลเดอร์สำหรับติดตั้ง (ค่าเริ่มต้นคือ C:\xampp) แล้วกด Next


4. กด Next จนถึงหน้าติดตั้ง แล้วกด Install


5. รอให้การติดตั้งเสร็จสิ้น แล้วกด Finish




---

2. เปิดใช้งาน XAMPP และเซิร์ฟเวอร์ Apache

1. เปิด XAMPP Control Panel


2. คลิกปุ่ม Start ที่ Apache (และ MySQL หากต้องการใช้ฐานข้อมูล)


3. ตรวจสอบว่า Apache และ MySQL ขึ้น สีเขียว แสดงว่าเริ่มทำงานแล้ว





3. คัดลอกไฟล์งานไปยังโฟลเดอร์ C:\xampp\htdocs


3.2 เปิดไฟล์งาน ผ่านเว็บเบราว์เซอร์

1. เปิดเบราว์เซอร์ (เช่น Chrome, Firefox)


2. พิมพ์ URL:

http://localhost/ชื่อโฟลเดอร์

http://localhost/ชื่อโฟลเดอร์/index.php




---

4. การตั้งค่า phpMyAdmin (ถ้าใช้ฐานข้อมูล MySQL)

1. เปิดเบราว์เซอร์ แล้วไปที่

http://localhost/phpmyadmin/


2. คลิก New ด้านซ้ายเพื่อสร้างฐานข้อมูลใหม่


3. ตั้งชื่อฐานข้อมูล แล้วกด Create


4. สามารถใช้ PHP เชื่อมต่อ MySQL โดยใช้โค้ดตัวอย่างนี้ในไฟล์ .php

<?php
$conn = new mysqli("localhost", "root", "", "ชื่อฐานข้อมูล");
if ($conn->connect_error) {
    die("เชื่อมต่อไม่สำเร็จ: " . $conn->connect_error);
}
echo "เชื่อมต่อสำเร็จ!";
?>




---

5. การแก้ไขปัญหาที่พบบ่อย

❌ Apache หรือ MySQL ไม่สามารถ Start ได้

✅ วิธีแก้ไข:

ตรวจสอบว่าไม่มีโปรแกรมอื่นใช้พอร์ต 80 หรือ 3306 เช่น Skype, IIS

เปลี่ยนพอร์ต Apache:

1. กด Config → เลือก Apache (httpd.conf)


2. ค้นหา Listen 80 แล้วเปลี่ยนเป็น Listen 8080


3. บันทึกไฟล์และ Restart Apache




❌ Object not found! หรือ 404 Not Found

✅ วิธีแก้ไข:

ตรวจสอบว่าไฟล์ .php อยู่ใน htdocs

ตรวจสอบ URL ว่าถูกต้อง (http://localhost/myproject/index.php)



---

6. คำสั่งพื้นฐานที่ควรรู้

แสดงข้อความ:

<?php echo "Hello, World!"; ?>

เชื่อมต่อฐานข้อมูล MySQL:

<?php
$conn = new mysqli("localhost", "root", "", "test_db");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
echo "Connected successfully";
?>

อ่านค่าจากฟอร์ม:

<form method="POST">
    ชื่อ: <input type="text" name="name">
    <button type="submit">ส่ง</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "คุณกรอกชื่อ: " . $_POST["name"];
}
?>

