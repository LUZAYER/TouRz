<?php
session_start();
require_once 'db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='login.html';</script>";
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$message = '';
$msg_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dest = trim($_POST['destination_name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $dates = trim($_POST['preferred_dates'] ?? '');

    if ($dest === '') {
        $message = 'Please enter a destination name.';
        $msg_type = 'danger';
    } else {
        $stmt = $conn->prepare("INSERT INTO tour_requests (user_id, destination_name, description, preferred_dates) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $dest, $desc, $dates);
        if ($stmt->execute()) {
            $message = 'Your tour request has been submitted! The admin will review it soon.';
            $msg_type = 'success';
        } else {
            $message = 'Something went wrong. Please try again.';
            $msg_type = 'danger';
        }
        $stmt->close();
    }
}

// Fetch user's previous requests
$stmt2 = $conn->prepare("SELECT destination_name, status, created_at FROM tour_requests WHERE user_id=? ORDER BY created_at DESC");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$my_requests = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="logo2.png" type="image/x-icon">
    <title>TouRz - Request a Tour</title>
    <style>
        .request-container {
            max-width: 700px;
            margin: 5vh auto;
            padding: 4vh 3vw;
        }
        .request-container h2 {
            color: darkslategray;
            font-size: 2.5vw;
            margin-bottom: 3vh;
        }
        .request-container label {
            font-size: 1.1vw;
            color: #333;
            font-weight: 500;
        }
        .request-container input,
        .request-container textarea {
            width: 100%;
            padding: 1vh 1vw;
            border: 1px solid #ccc;
            border-radius: 0.5vw;
            font-size: 1vw;
            margin-bottom: 2vh;
        }
        .request-container textarea {
            min-height: 12vh;
            resize: vertical;
        }
        .request-container button {
            padding: 1.5vh 3vw;
            background-color: darkslategray;
            color: white;
            border: none;
            border-radius: 0.5vw;
            font-size: 1.2vw;
            cursor: pointer;
            transition: background 0.3s;
        }
        .request-container button:hover {
            background-color: #1a4a4a;
        }
        .status-badge {
            padding: 0.3vh 1vw;
            border-radius: 1vw;
            font-size: 0.85vw;
            font-weight: 600;
            display: inline-block;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .my-requests { margin-top: 5vh; }
        .my-requests table { font-size: 1vw; }
    </style>
</head>
<body onload="loadHTML('header.php', 'header'); loadHTML('footer.html', 'footer'); showOverlayWithSpinner();">
    <div class="spinner-box">
        <div class="spinner">
            <div></div><div></div><div></div><div></div><div></div>
            <div></div><div></div><div></div><div></div><div></div>
        </div>
    </div>

    <div id="header"></div>

    <div class="request-container">
        <h2>🗺️ Request a New Tour Location</h2>
        <p style="color:#555; font-size:1.1vw;">Can't find your dream destination? Request it and our team will make it happen!</p>

        <?php if ($message): ?>
            <div class="alert alert-<?= $msg_type ?>" style="font-size:1vw;"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="destination_name">Destination Name *</label>
            <input type="text" name="destination_name" id="destination_name" required placeholder="e.g., Rangamati, Sylhet, Nepal...">

            <label for="description">Why do you want this tour?</label>
            <textarea name="description" id="description" placeholder="Tell us about the destination and why you'd love to visit..."></textarea>

            <label for="preferred_dates">Preferred Dates</label>
            <input type="text" name="preferred_dates" id="preferred_dates" placeholder="e.g., June 2026, Winter Season, Any time...">

            <button type="submit">Submit Request</button>
        </form>

        <div class="my-requests">
            <h4 style="color:darkslategray;">Your Previous Requests</h4>
            <?php if ($my_requests->num_rows > 0): ?>
                <table class="table table-bordered" style="margin-top:2vh;">
                    <thead>
                        <tr><th>Destination</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($req = $my_requests->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($req['destination_name']) ?></td>
                            <td><span class="status-badge status-<?= $req['status'] ?>"><?= ucfirst($req['status']) ?></span></td>
                            <td><?= htmlspecialchars($req['created_at']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#888; font-size:1vw;">No requests yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="footer"></div>
    <script src="script.js"></script>
</body>
</html>
