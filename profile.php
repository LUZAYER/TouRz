<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit();
}

$user_id = (int) $_SESSION['user_id'];
$success = "";
$error = "";

// ==========================
// HANDLE UPDATE
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $phone = $_POST['phone'] ?? '';
  $birthday = $_POST['birthday'] ?? NULL;
  $nid = $_POST['nid'] ?? '';

  $profile_pic = NULL;

  if (!empty($_FILES['profile_pic']['name'])) {
    $target_dir = "uploads/";
    $file_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
    $target_file = $target_dir . $file_name;

    $allowed = ['image/jpeg', 'image/png', 'image/jpg'];

    if (!in_array($_FILES['profile_pic']['type'], $allowed)) {
      $error = "Invalid file type.";
    } else {
      if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
        $profile_pic = $target_file;
      } else {
        $error = "Upload failed.";
      }
    }
  }

  if (!$error) {
    if ($profile_pic) {
      $stmt = $conn->prepare("UPDATE users SET phone=?, birthday=?, nid=?, profile_photo=? WHERE id=?");
      $stmt->bind_param("ssssi", $phone, $birthday, $nid, $profile_pic, $user_id);
    } else {
      $stmt = $conn->prepare("UPDATE users SET phone=?, birthday=?, nid=? WHERE id=?");
      $stmt->bind_param("sssi", $phone, $birthday, $nid, $user_id);
    }

    if ($stmt->execute()) {
      $success = "Profile updated.";
    } else {
      $error = "Update failed.";
    }
  }
}


// ==========================
// FETCH USER
// ==========================
$stmt = $conn->prepare("SELECT name, email, phone, birthday, nid, profile_photo FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();


// ==========================
// FETCH FUTURE BOOKINGS (Prioritizes tours table, falls back to booking date)
// ==========================
$stmt_future = $conn->prepare("
    SELECT b.id, b.destination, b.team_size, b.total_price, 
           COALESCE(t.next_tour_datetime, b.tour_date) as tour_date, 
           b.status 
    FROM bookings b 
    LEFT JOIN tours t ON TRIM(b.destination) = TRIM(t.name)
    WHERE b.user_id=? 
    AND (COALESCE(t.next_tour_datetime, b.tour_date) >= NOW() OR (COALESCE(t.next_tour_datetime, b.tour_date) IS NULL AND b.booked_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)))
    ORDER BY tour_date ASC
");
$stmt_future->bind_param("i", $user_id);
$stmt_future->execute();
$future_bookings = $stmt_future->get_result();

// ==========================
// FETCH PAST BOOKINGS
// ==========================
$stmt_past = $conn->prepare("
    SELECT destination, team_size, total_price, tour_date 
    FROM bookings 
    WHERE user_id=? 
    AND (tour_date < NOW() OR (tour_date IS NULL AND booked_at < DATE_SUB(NOW(), INTERVAL 1 DAY))) 
    ORDER BY COALESCE(tour_date, booked_at) DESC
");
$stmt_past->bind_param("i", $user_id);
$stmt_past->execute();
$past_bookings = $stmt_past->get_result();

// ==========================
// FETCH ELIGIBLE TOURS FOR REVIEW (Only Past Ones)
// ==========================
$stmt3 = $conn->prepare("
    SELECT DISTINCT destination 
    FROM bookings 
    WHERE user_id=? 
    AND (tour_date < NOW() OR (tour_date IS NULL AND booked_at < DATE_SUB(NOW(), INTERVAL 1 DAY)))
");
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$destinations_result = $stmt3->get_result();
$eligible_tours = [];
while ($d = $destinations_result->fetch_assoc()) {
  $eligible_tours[] = $d['destination'];
}

// ==========================
// HANDLE REVIEW SUBMISSION
// ==========================
if (isset($_POST['submit_review'])) {
  $tour_name = $_POST['tour_name'] ?? '';
  $rating = (int) ($_POST['rating'] ?? 5);
  $comment = $_POST['comment'] ?? '';

  if (!empty($tour_name)) {
    // Find tour_id based on name
    $t_stmt = $conn->prepare("SELECT id FROM tours WHERE name = ?");
    $t_stmt->bind_param("s", $tour_name);
    $t_stmt->execute();
    $t_res = $t_stmt->get_result()->fetch_assoc();
    $tour_id = $t_res ? $t_res['id'] : NULL;

    $r_stmt = $conn->prepare("INSERT INTO reviews (user_id, tour_id, rating, comment) VALUES (?, ?, ?, ?)");
    $r_stmt->bind_param("iiis", $user_id, $tour_id, $rating, $comment);
    if ($r_stmt->execute()) {
      $success = "Thank you for your review!";
    } else {
      $error = "Could not submit review: " . $conn->error;
    }
  } else {
    $error = "Please select a tour to review.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="styles.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
  <link rel="shortcut icon" href="logo2.png" type="image/x-icon" />
  <title>TouRz@Profile</title>

  <style>
    p {
      font-size: 1vw !important;
    }

    #profile {
      margin-top: -15vh;
    }

    #out {
      margin-left: 1vw;
      padding: 1vh 2vh;
      border: 1px solid green;
    }

    #out:hover {
      background-color: lightseagreen;
      color: white;
    }
  </style>
</head>

<body onload="loadHTML('header.php', 'header'); loadHTML('footer.html', 'footer'); showOverlayWithSpinner();">

  <div id="header"></div>
  <br><br><br><br>

  <section id="profile" class="vh-60" style="background-color: #f4f5f7; ">
    <div class="container py-5 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col col-lg-10 mb-4 mb-lg-0">
          <div class="card mb-3" style="border-radius: .5rem;">
            <div class="row g-0">

              <div class="col-md-4 gradient-custom text-center text-white"
                style="border-top-left-radius: .5rem; border-bottom-left-radius: .5rem;">

                <img
                  src="<?php echo $user['profile_photo'] ? $user['profile_photo'] : 'https://static.vecteezy.com/system/resources/previews/026/632/544/non_2x/profile-avatar-icon-symbol-design-illustration-vector.jpg'; ?>"
                  alt="Avatar" class="img-fluid my-5" style="width: 200px; border-radius: 30%; margin-left: 1vw" ; />
                <br><br><br><br>
                <a style="float:bottom;" id="out" href="logout.php">Logout</a>
              </div>

              <div class="col-md-8">
                <div class="card-body p-4">

                  <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                  <?php endif; ?>

                  <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                  <?php endif; ?>

                  <!-- DISPLAY INFO (UNCHANGED STYLE) -->
                  <h6>Information</h6>
                  <hr class="mt-0 mb-4">

                  <h2 class="text-dark"><?php echo htmlspecialchars($user['name']); ?></h2>

                  <div class="row pt-1">
                    <div class="col-6 mb-3">
                      <h6>Email</h6>
                      <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="col-6 mb-3">
                      <h6>Phone</h6>
                      <p class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></p>
                    </div>
                  </div>

                  <div class="row pt-1">
                    <div class="col-6 mb-3">
                      <h6>Birthday</h6>
                      <p class="text-muted">
                        <?php echo $user['birthday'] ? htmlspecialchars($user['birthday']) : 'Not set'; ?>
                      </p>
                    </div>
                    <div class="col-6 mb-3">
                      <h6>NID</h6>
                      <p class="text-muted"><?php echo $user['nid'] ? htmlspecialchars($user['nid']) : 'Not set'; ?></p>
                    </div>
                  </div>

                  <!-- BUTTON -->
                  <button class="btn btn-sm btn-primary" onclick="toggleEdit()"
                    style="padding: 0.7vw; color: green; background-color: white; border: 1px solid green; box-shadow: none; float: right; ">Update
                    Info</button>

                  <!-- HIDDEN FORM -->
                  <form id="editForm" method="POST" enctype="multipart/form-data" style="display:none; margin-top:5vh;">

                    <div class="mb-2">
                      <label for="phone">Phone</label>
                      <input type="text" name="phone" class="form-control"
                        value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>

                    <div class="mb-2">
                      <label for="birthday">Birthday</label>
                      <input type="date" name="birthday" class="form-control"
                        value="<?php echo htmlspecialchars($user['birthday']); ?>">
                    </div>

                    <div class="mb-2">
                      <label for="nid">NID</label>
                      <input type="text" name="nid" class="form-control"
                        value="<?php echo htmlspecialchars($user['nid']); ?>">
                    </div>

                    <div class="mb-2">

                      <label for="profile_pic">Profile Picture</label>
                      <input type="file" name="profile_pic" class="form-control">
                    </div>

                    <button class="btn btn-success btn-sm"
                      style="padding: 0.7vw; color: green; background-color: white; border: 1px solid green; box-shadow: none; float: right; margin-top: 2vh; ">Save</button>
                  </form>

                  <!-- FUTURE BOOKINGS -->
                  <br><br>
                  <h6 class="mt-4">Upcoming Adventures</h6>
                  <hr class="mt-0 mb-4">

                  <?php if ($future_bookings->num_rows > 0): ?>
                    <table class="table table-hover table-borderless shadow-sm rounded overflow-hidden"
                      style="background: white;">
                      <thead class="table-light">
                        <tr>
                          <th>Destination</th>
                          <th>Team</th>
                          <th>Tour Date</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while ($row = $future_bookings->fetch_assoc()): ?>
                          <tr>
                            <td><strong><?php echo htmlspecialchars($row['destination']); ?></strong></td>
                            <td><?php echo (int) $row['team_size']; ?></td>
                            <td class="text-primary" style="font-size: 0.8vw !important;">
                              <?php
                              if ($row['tour_date']) {
                                echo date('M d, Y h:i A', strtotime($row['tour_date']));
                              } else {
                                echo 'Pending';
                              }
                              ?>
                            </td>
                            <td>
                              <?php if ($row['status'] === 'active'): ?>
                                <form action="cancel_booking.php" method="POST"
                                  onsubmit="return confirm('Are you sure you want to cancel this booking?');"
                                  style="display:inline;">
                                  <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                  <button type="submit" class="btn btn-sm btn-outline-danger"
                                    style="font-size: 0.6rem; padding: 2px 6px;">Cancel</button>
                                </form>
                              <?php else: ?>
                                <span class="badge bg-secondary" style="font-size: 0.6rem;">Cancelled</span>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  <?php else: ?>
                    <p class="text-muted">No upcoming tours. <a href="index.php#tour">Book one now!</a></p>
                  <?php endif; ?>

                  <!-- PAST BOOKINGS -->
                  <br><br>
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mt-4">My Travel History</h6>
                    <?php if ($past_bookings->num_rows > 0): ?>
                      <button class="btn btn-sm btn-outline-success"
                        onclick="document.getElementById('reviewSection').scrollIntoView({behavior: 'smooth'})">Rate a
                        Tour</button>
                    <?php endif; ?>
                  </div>
                  <hr class="mt-0 mb-4">

                  <?php if ($past_bookings->num_rows > 0): ?>
                    <table class="table table-hover table-borderless shadow-sm rounded overflow-hidden"
                      style="background: white;">
                      <thead class="table-light">
                        <tr>
                          <th>Destination</th>
                          <th>Team</th>
                          <th>Date Completed</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while ($row = $past_bookings->fetch_assoc()): ?>
                          <tr>
                            <td><strong><?php echo htmlspecialchars($row['destination']); ?></strong></td>
                            <td><?php echo (int) $row['team_size']; ?></td>
                            <td class="text-muted" style="font-size: 0.8vw !important;">
                              <?php
                              $disp_date = $row['tour_date'] ? $row['tour_date'] : $row['booked_at'];
                              echo date('M d, Y', strtotime($disp_date));
                              ?>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  <?php else: ?>
                    <p class="text-muted">You haven't completed any tours yet.</p>
                  <?php endif; ?>

                  <!-- REVIEW SECTION -->
                  <?php if (!empty($eligible_tours)): ?>
                    <div id="reviewSection" class="mt-5 p-4 rounded shadow-sm"
                      style="background-color: white; border-top: 4px solid var(--sooth-accent);">
                      <h6>Share Your Experience</h6>
                      <p class="text-muted" style="font-size: 0.85rem !important;">Rate your past tours and help other
                        travelers!</p>

                      <form method="POST">
                        <div class="mb-3">
                          <label class="form-label">Select Tour</label>
                          <select name="tour_name" class="form-select form-select-sm" required>
                            <option value="">Choose a tour you've visited...</option>
                            <?php foreach ($eligible_tours as $t_name): ?>
                              <option value="<?php echo htmlspecialchars($t_name); ?>">
                                <?php echo htmlspecialchars($t_name); ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Rating</label>
                          <div class="d-flex gap-3">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                              <div class="form-check">
                                <input class="form-check-input" type="radio" name="rating" value="<?php echo $i; ?>"
                                  id="r<?php echo $i; ?>" <?php if ($i == 5)
                                       echo 'checked'; ?>>
                                <label class="form-check-label" for="r<?php echo $i; ?>">
                                  <?php echo $i; ?> <i class="fas fa-star text-warning"></i>
                                </label>
                              </div>
                            <?php endfor; ?>
                          </div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Your Feedback</label>
                          <textarea name="comment" class="form-control form-control-sm" rows="3"
                            placeholder="Tell us about your experience..." required></textarea>
                        </div>

                        <button type="submit" name="submit_review" class="btn btn-success w-100 btn-sm">Submit
                          Review</button>
                      </form>
                    </div>
                  <?php endif; ?>

                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <br><br><br><br><br><br>


  <div id="footer"></div>

  <script>
    function toggleEdit() {
      var form = document.getElementById("editForm");
      form.style.display = (form.style.display === "none") ? "block" : "none";
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>

</html>