<?php
session_start();
require_once 'db.php';

// 🔒 Block page if not logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='login.html';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT name, email, phone, birthday, nid, nid_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $birthday, $nid, $nid_photo);
$stmt->fetch();
$stmt->close();


// ✅ Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $birthday = $_POST['birthday'];
    $nid = $_POST['nid'];

    // Handle file upload
    $targetFilePath = $nid_photo; // Keep existing if none uploaded
    if (!empty($_FILES["cv"]["name"])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["cv"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        move_uploaded_file($_FILES["cv"]["tmp_name"], $targetFilePath);
    }

    // Store extra info in DB
    $stmt = $conn->prepare("UPDATE users SET birthday=?, nid=?, nid_photo=? WHERE id=?");
    $stmt->bind_param("sssi", $birthday, $nid, $targetFilePath, $user_id);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Information saved successfully'); window.location.href='pay.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Info Form</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body onload="loadHTML('header.php', 'header'); loadHTML('footer.html', 'footer'); showOverlayWithSpinner();">
    <div class="spinner-box">
        <div class="spinner">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <div id="header"></div>
    <div class="info-container">
        <div></div>
        <form id="form" method="post" enctype="multipart/form-data">
            <label for="user">Enter Your Name</label>
            <br>
            <input type="text" id="user" name="name" value="<?= htmlspecialchars($name) ?>" readonly>
            <br><br>

            <label for="mail">Enter Your Email</label>
            <br>
            <input type="email" id="mail" name="email" value="<?= htmlspecialchars($email) ?>" readonly>
            <br><br>

            <label for="number">Enter Your Phone Number</label>
            <br>
            <input type="tel" id="number" name="phone" value="<?= htmlspecialchars($phone) ?>" readonly>
            <br><br>

            <label for="birthday">Enter Your Birthdate</label>
            <br>
            <input type="date" id="birthday" name="birthday" value="<?= htmlspecialchars($birthday ?? '') ?>" required>
            <br><br>

            <label for="nid">Enter Your NID number</label>
            <br>
            <input type="number" id="nid" name="nid" value="<?= htmlspecialchars($nid ?? '') ?>">
            <br><br>

            <label for="cv">Enter Your NID card photo</label>
            <?php if (!empty($nid_photo)): ?>
                <div style="margin-bottom: 10px;">
                    <p style="font-size: 0.8rem; color: #fff; margin-bottom: 5px;">Currently uploaded photo:</p>
                    <img src="<?= htmlspecialchars($nid_photo) ?>" alt="Not Found!"
                        style="max-width: 150px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            <?php endif; ?>
            <label for="cv" id="click"></label>
            <br>
            <input type="file" id="cv" name="cv">
            <br><br>

            <div class="buttons">
                <br>
                <input id="reset" type="reset">
                <br><br>
                <input id="submit" value="Submit" type="submit"
                    onclick="done.value='Your Information Has Been Stored!\nClick to go to Check Out';">
                <br><br>
                <a href="pay.php"><input type="button" value="" id="done"></a>
            </div>
        </form>
        <div></div>
    </div>
    <div id="footer"></div>
    <script src="script.js"></script>
</body>

</html>