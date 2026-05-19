<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link rel="stylesheet" href="styles.css">
  <style>
    .showcase,
    video,
    .overlay,
    .toggle {
      height: 13vh !important;
      position: sticky;
    }

    section {
      min-height: 12vh !important;
      padding: 0 !important;
    }
  </style>
  <title>Document</title>
</head>

<body>
  <section class="showcase" id="navbar">
    <?php
    session_start();
    require_once 'db.php';
    $unread_notifications = [];
    if (isset($_SESSION['user_id'])) {
        $u_id = (int)$_SESSION['user_id'];
        $n_stmt = $conn->query("SELECT * FROM notifications WHERE user_id = $u_id AND is_read = 0 ORDER BY created_at DESC");
        if ($n_stmt) {
            while ($row = $n_stmt->fetch_assoc()) {
                $unread_notifications[] = $row;
                // Mark as read immediately so it doesn't pop up again
                $conn->query("UPDATE notifications SET is_read = 1 WHERE id = " . $row['id']);
            }
        }
    }
    ?>
    <header>
      <a href="index.php"><img id="logo" src="log.png" alt=""></a>
      <div class="menu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="about.php">About Us</a></li>
          <li><a href="index.php#tour">Destinations</a></li>
          <li><a href="upcoming.php">Upcoming</a></li>
          <li><a href="offers.html">Offers</a></li>
          <li><a href="#foot">Contact</a></li>
          <?php if (isset($_SESSION['username'])): ?>
            <?php if ($_SESSION['username'] === 'admin'): ?>
              <li><a href="admin-panel.php">Admin</a></li>
            <?php else: ?>
              <li><a href="request_tour.php">Request Tour</a></li>
              <li><a href="profile.php"><?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
            <?php endif; ?>
          <?php else: ?>
            <li><a href="login.html">LogIn</a></li>
          <?php endif; ?>
          <li id="log"><a href="pay.html"><i class="bi bi-luggage-fill"></i></a></li>
        </ul>
      </div>
      <div class="toggle"></div>
    </header>

    <video style="height: 15vh;" id="stick"
      src="y2mate.com - Forest with trees in the morning sunlight 4K Premium Stock Video Without watermarkCinematic shot_1080.mp4"
      muted loop autoplay></video>
    <div class="overlay"></div>
  </section>

  <!-- Notification Toast Logic -->
  <?php if (!empty($unread_notifications)): ?>
    <div id="notification-overlay"
      style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; max-width: 350px;">
      <?php foreach ($unread_notifications as $notif): ?>
        <div class="notif-toast"
          style="background: white; border-left: 5px solid #ff4757; padding: 15px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: notifSlideIn 0.5s ease forwards;">
          <div style="font-weight: 700; color: #ff4757; margin-bottom: 5px;"><i class="bi bi-bell-fill"></i> New Alert</div>
          <div style="font-size: 0.9rem; color: #333;"><?php echo htmlspecialchars($notif['message']); ?></div>
          <div style="text-align: right; margin-top: 10px;">
            <button onclick="this.parentElement.parentElement.remove()"
              style="background: #eee; border: none; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">Dismiss</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <style>
      @keyframes notifSlideIn {
        from {
          transform: translateX(100%);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
    </style>
  <?php endif; ?>
</body>

</html>