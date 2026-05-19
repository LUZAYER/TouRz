<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Admin check
if (!isset($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ===== DASHBOARD STATS =====
    case 'dashboard_stats':
        $users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
        $bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM bookings"))['c'];
        $revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_price),0) as c FROM bookings"))['c'];
        $pending = 0;
        $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM tour_requests WHERE status='pending'");
        if ($r) $pending = mysqli_fetch_assoc($r)['c'];
        $tours_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tours"))['c'];

        // Recent bookings
        $recent = [];
        $rb = mysqli_query($conn, "SELECT b.id, b.name, b.destination, b.total_price, b.booked_at, COALESCE(t.next_tour_datetime, b.tour_date) as tour_date FROM bookings b LEFT JOIN tours t ON TRIM(b.destination) = TRIM(t.name) ORDER BY b.booked_at DESC LIMIT 5");
        if ($rb) while ($row = mysqli_fetch_assoc($rb)) $recent[] = $row;

        echo json_encode([
            'users' => (int)$users,
            'bookings' => (int)$bookings,
            'revenue' => (float)$revenue,
            'pending_requests' => (int)$pending,
            'tours' => (int)$tours_count,
            'recent_bookings' => $recent
        ]);
        break;

    case 'update_tour_date':
        $id = (int)($_POST['id'] ?? 0);
        $date = $_POST['date'] ?? NULL;
        if ($id) {
            $stmt = $conn->prepare("UPDATE tours SET next_tour_datetime = ? WHERE id = ?");
            $stmt->bind_param("si", $date, $id);
            if ($stmt->execute()) echo json_encode(['status' => 'success']);
            else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;

    // ===== USERS LIST =====
    case 'get_users':
        $result = mysqli_query($conn, "SELECT id, name, email, phone, birthday, nid, is_admin FROM users ORDER BY id ASC");
        $users = [];
        if ($result) while ($row = mysqli_fetch_assoc($result)) $users[] = $row;
        echo json_encode($users);
        break;

    // ===== BOOKINGS LIST =====
    case 'get_bookings':
        $result = mysqli_query($conn, "SELECT b.id, b.user_id, b.name, b.email, b.phone, b.destination, b.team_size, b.total_price, b.booked_at, b.status, COALESCE(t.next_tour_datetime, b.tour_date) as tour_date FROM bookings b LEFT JOIN tours t ON TRIM(b.destination) = TRIM(t.name) ORDER BY b.booked_at DESC");
        $bookings = [];
        if ($result) while ($row = mysqli_fetch_assoc($result)) $bookings[] = $row;
        echo json_encode($bookings);
        break;

    // ===== TOUR REQUESTS =====
    case 'get_requests':
        $result = mysqli_query($conn, "SELECT r.*, u.name as user_name, u.email as user_email FROM tour_requests r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC");
        $requests = [];
        if ($result) while ($row = mysqli_fetch_assoc($result)) $requests[] = $row;
        echo json_encode($requests);
        break;

    // ===== APPROVE REQUEST =====
    case 'approve_request':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['error' => 'Invalid ID']); break; }
        $stmt = $conn->prepare("UPDATE tour_requests SET status='approved' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        // Fetch request data to return
        $r = mysqli_query($conn, "SELECT destination_name, description FROM tour_requests WHERE id=$id");
        $data = mysqli_fetch_assoc($r);
        echo json_encode(['success' => true, 'destination_name' => $data['destination_name'], 'description' => $data['description']]);
        break;

    // ===== REJECT REQUEST =====
    case 'reject_request':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['error' => 'Invalid ID']); break; }
        $stmt = $conn->prepare("UPDATE tour_requests SET status='rejected' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    // ===== ADD TOUR + AUTO-GENERATE PAGE =====
    case 'add_tour':
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? 'domestic');
        $image = trim($_POST['image'] ?? '');
        $cost = (int)($_POST['cost'] ?? 0);
        $starting_point = trim($_POST['starting_point'] ?? 'Kamalapur Railway Station, Dhaka');
        $travel_duration = trim($_POST['travel_duration'] ?? '');
        $tour_duration = trim($_POST['tour_duration'] ?? '');
        $transportation = trim($_POST['transportation'] ?? '');
        $hotel = trim($_POST['hotel'] ?? '');
        $map_embed = trim($_POST['map_embed'] ?? '');
        $next_tour = trim($_POST['next_tour'] ?? '');
        $carousel_img1 = trim($_POST['carousel_img1'] ?? '');
        $carousel_img2 = trim($_POST['carousel_img2'] ?? '');
        $carousel_img3 = trim($_POST['carousel_img3'] ?? '');

        if (!$name || !$category || !$image) {
            echo json_encode(['error' => 'Name, image and category are required']);
            break;
        }

        // Generate slug for filename
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $link = $slug . '.html';
        $carousel_images = json_encode([$carousel_img1 ?: $image, $carousel_img2 ?: $image, $carousel_img3 ?: $image]);

        // Insert into tours table
        $stmt = $conn->prepare("INSERT INTO tours (name, description, image, link, category, starting_point, cost, travel_duration, tour_duration, transportation, hotel, map_embed, next_tour_datetime, bookings_count, carousel_images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)");
        $stmt->bind_param("ssssssississss", $name, $description, $image, $link, $category, $starting_point, $cost, $travel_duration, $tour_duration, $transportation, $hotel, $map_embed, $next_tour, $carousel_images);

        if (!$stmt->execute()) {
            echo json_encode(['error' => 'DB insert failed: ' . $stmt->error]);
            break;
        }

        // Auto-generate HTML page
        $c1 = htmlspecialchars($carousel_img1 ?: $image);
        $c2 = htmlspecialchars($carousel_img2 ?: $image);
        $c3 = htmlspecialchars($carousel_img3 ?: $image);
        $safeName = htmlspecialchars($name);
        $safeDesc = htmlspecialchars($description);
        $safeCost = (int)$cost;
        $safeSP = htmlspecialchars($starting_point);
        $safeTD = htmlspecialchars($travel_duration);
        $safeTourD = htmlspecialchars($tour_duration);
        $safeTrans = htmlspecialchars($transportation);
        $safeHotel = htmlspecialchars($hotel);
        $safeMap = $map_embed;
        $safeNextTour = htmlspecialchars($next_tour);

        $mapSection = '';
        if ($safeMap) {
            $mapSection = <<<MAP
      <div class="map">
        <div></div>
        <div>
          <h6 id="route">Transportation Route</h6>
          <iframe src="{$safeMap}" width="100%" height="450" style="border: 2px solid black;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <div id="hide">
          <input id="cost" type="button" value="{$safeCost}">
        </div>
      </div>
MAP;
        } else {
            $mapSection = <<<MAP
      <div class="map">
        <div></div>
        <div>
          <h6 id="route">Transportation Route</h6>
          <p style="padding:2vw; color:gray;">Map not available yet.</p>
        </div>
        <div id="hide">
          <input id="cost" type="button" value="{$safeCost}">
        </div>
      </div>
MAP;
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="logo2.png" type="image/x-icon">
    <title>TouRz@{$safeName}</title>
</head>
<body onload="loadHTML('header.php', 'header'); loadHTML('footer.html', 'footer'); showOverlayWithSpinner(); loadTourCountdown('{$slug}');">
  <div class="spinner-box">
    <div class="spinner">
      <div></div><div></div><div></div><div></div><div></div>
      <div></div><div></div><div></div><div></div><div></div>
    </div>
  </div>

  <div id="header"></div>
  <h1 id="placename">{$safeName}</h1>

  <div id="carouselExampleDark" class="carousel carousel-dark slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
      <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="1" aria-label="Slide 2"></button>
      <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
      <div class="carousel-item active" data-bs-interval="4000">
        <img src="{$c1}" class="d-block w-100" alt="...">
      </div>
      <div class="carousel-item" data-bs-interval="4000">
        <img src="{$c2}" class="d-block w-100" alt="...">
      </div>
      <div class="carousel-item" data-bs-interval="4000">
        <img src="{$c3}" class="d-block w-100" alt="...">
      </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleDark" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleDark" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>

      <div class="destinations-container">
        <div></div>
        <div>
          <h1>Destination Details</h1>
          <h3><span id="place">{$safeName}</span> {$safeDesc}</h3>
          <br><br>
          <h1>Tour Details</h1>
          <section id="tour-details">
            <section>
              <h6>Starting Point</h6>
              <h5>{$safeSP}</h5>
            </section>
            <section>
              <h6>Cost</h6>
              <h5>{$safeCost}/- Per Person</h5>
            </section>
            <section>
              <h6>Estimate Travel Duration</h6>
              <h5>{$safeTD}</h5>
            </section>
            <section>
              <h6>Tour Duration</h6>
              <h5>{$safeTourD}</h5>
            </section>
            <section>
              <h6>Transportation</h6>
              <h5>{$safeTrans}</h5>
            </section>
            <section>
              <h6>Staying At</h6>
              <h5>{$safeHotel}</h5>
            </section>
            <section>
              <h6>Next Tour Leaves In</h6>
              <h5 id="tour-countdown">Loading...</h5>
            </section>
            <section>
              <h6>Total Bookings</h6>
              <h5 id="tour-bookings">0</h5>
            </section>
            <section>
              <input type="button" value="Book Now!" id="book" onclick="booknow(); openHtmlFile('info.php');">
            </section>
          </section>
          <marquee behavior="" direction="">N.B: Changes can occur in timing due to unavoidable circumstances</marquee>
          <br> <br>
        </div>
        <div></div>
      </div>

{$mapSection}

    <div id="footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="script.js"></script>
</body>
</html>
HTML;

        $filePath = __DIR__ . '/' . $link;
        file_put_contents($filePath, $html);

        // Also update tour_schedules.json
        $jsonPath = __DIR__ . '/tour_schedules.json';
        $schedules = json_decode(file_get_contents($jsonPath), true) ?: [];
        $schedules[$slug] = [
            'name' => $name,
            'slug' => $slug,
            'next_tour' => $next_tour,
            'cost' => $cost,
            'bookings_count' => 0
        ];
        file_put_contents($jsonPath, json_encode($schedules, JSON_PRETTY_PRINT));

        echo json_encode(['success' => true, 'link' => $link, 'slug' => $slug]);
        break;

    // ===== GET ALL TOURS =====
    case 'get_tours':
        $result = mysqli_query($conn, "SELECT id, name, category, link, cost, bookings_count, next_tour_datetime FROM tours ORDER BY name ASC");
        $tours = [];
        if ($result) while ($row = mysqli_fetch_assoc($result)) $tours[] = $row;
        echo json_encode($tours);
        break;

    // ===== CHATBOT LOGS =====
    case 'get_chatbot_logs':
        $result = mysqli_query($conn, "SELECT c.id, c.user_id, u.name as user_name, c.query_text, c.response_text, c.queried_at FROM chatbot_logs c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.queried_at DESC LIMIT 200");
        $logs = [];
        if ($result) while ($row = mysqli_fetch_assoc($result)) $logs[] = $row;
        echo json_encode($logs);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}

$conn->close();
?>
