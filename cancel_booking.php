<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$current_user_id = (int)$_SESSION['user_id'];
$booking_id = (int)($_POST['booking_id'] ?? ($_GET['id'] ?? 0));
$is_admin = ($_SESSION['username'] === 'admin');

if ($booking_id > 0) {
    // 1. Verify access
    $stmt = $conn->prepare("SELECT user_id, destination FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    
    if ($booking && ($is_admin || $booking['user_id'] == $current_user_id)) {
        // 2. Update status
        $update = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $update->bind_param("i", $booking_id);
        
        if ($update->execute()) {
            // 3. Create Notification for the OTHER party
            $dest = $booking['destination'];
            if ($is_admin) {
                // Notify User
                $target_user_id = $booking['user_id'];
                $msg = "Important: The administrator has cancelled your booking for '$dest'. Please contact support for more details.";
            } else {
                // Notify Admin
                $admin_res = $conn->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
                $admin = $admin_res->fetch_assoc();
                $target_user_id = $admin['id'] ?? 1; // Default to 1 if not found
                $msg = "Alert: User '{$_SESSION['username']}' has cancelled their booking for '$dest' (Booking ID: $booking_id).";
            }
            
            $notify = $conn->prepare("INSERT INTO notifications (user_id, booking_id, message) VALUES (?, ?, ?)");
            $notify->bind_param("iis", $target_user_id, $booking_id, $msg);
            $notify->execute();

            // Redirect back
            if ($is_admin) {
                header("Location: admin-panel.php?cancelled=1");
            } else {
                header("Location: profile.php?cancelled=1");
            }
            exit();
        }
    }
}

header("Location: index.php");
?>
