<?php
session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST['email'], $_POST['password'])) {
        header("Location: login.html");
        exit();
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['name'];
            $_SESSION['is_admin'] = $row['is_admin'];

            if ((int)$row['is_admin'] === 1) {
                header("Location: admin-panel.php");
            } else {
                header("Location: index.php");
            }
            exit();
        }
    }

    header("Location: login.html");
    exit();
}
?>