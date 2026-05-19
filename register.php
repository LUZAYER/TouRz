<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $phone = trim($_POST["phone"]);

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        die("Please fill in all fields correctly.");
    }

    // Hash the password securely
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into DB
    $is_admin = 0; // force normal user

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, is_admin) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $name, $email, $hashed_password, $phone, $is_admin);

    if ($stmt->execute()) {
        echo "✅ Registration successful!";
        header("Location: login.html");
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
