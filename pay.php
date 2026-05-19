<?php
session_start();
require_once 'db.php';
require_once 'phpqrcode/qrlib.php';

/* =====================================================
   1. LOGIN CHECK
===================================================== */
if (!isset($_SESSION['user_id'])) {
  echo "<script>alert('Please login first'); window.location.href='login.html';</script>";
  exit();
}

$user_id = $_SESSION['user_id'];

/* =====================================================
   2. GET USER INFO (TRUST DB ONLY)
===================================================== */
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone);
$stmt->fetch();
$stmt->close();

/* =====================================================
   PROM0 VALIDATION (AJAX POST)
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'validate_promo') {
  header('Content-Type: application/json');
  $code = trim($_POST['code'] ?? '');
  $destination = trim($_POST['destination'] ?? '');

  $stmt = $conn->prepare("SELECT discount_type, discount_value FROM promos WHERE code = ? AND (destination_name IS NULL OR destination_name = '' OR ? LIKE CONCAT('%', destination_name, '%')) AND (expiry_date IS NULL OR expiry_date >= CURDATE())");
  $stmt->bind_param("ss", $code, $destination);
  $stmt->execute();
  $result = $stmt->get_result();
  $promo = $result->fetch_assoc();
  $stmt->close();

  if ($promo) {
    echo json_encode(['status' => 'success', 'type' => $promo['discount_type'], 'value' => (float) $promo['discount_value']]);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or expired code']);
  }
  exit;
}


/* =====================================================
   3. SAVE BOOKING (AJAX POST)
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_booking') {

  $destination = trim($_POST['destination'] ?? '');
  $team_size = (int) ($_POST['team_size'] ?? 0);
  $total_price = floatval(str_replace(['/-', ',', ' '], '', $_POST['total_price'] ?? '0'));

  if ($destination === '' || $team_size <= 0 || $total_price <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
  }

  // Fetch tour_date from tours table
  $t_stmt = $conn->prepare("SELECT next_tour_datetime FROM tours WHERE name = ?");
  $t_stmt->bind_param("s", $destination);
  $t_stmt->execute();
  $t_stmt->bind_result($tour_date);
  $t_stmt->fetch();
  $t_stmt->close();

  $stmt = $conn->prepare("
        INSERT INTO bookings (user_id, name, email, phone, destination, tour_date, team_size, total_price, member_names)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
  $stmt->bind_param("isssssids", $user_id, $name, $email, $phone, $destination, $tour_date, $team_size, $total_price, $_POST['member_names']);

  if ($stmt->execute()) {
    $booking_id = $stmt->insert_id;

    // Update bookings_count in tour_schedules.json
    $jsonPath = __DIR__ . '/tour_schedules.json';
    if (file_exists($jsonPath)) {
      $schedules = json_decode(file_get_contents($jsonPath), true) ?: [];
      foreach ($schedules as $slug => &$t) {
        if (strcasecmp(trim($t['name']), trim($destination)) === 0) {
          $t['bookings_count'] = ($t['bookings_count'] ?? 0) + 1;
          break;
        }
      }
      unset($t);
      file_put_contents($jsonPath, json_encode($schedules, JSON_PRETTY_PRINT));
    }

    echo json_encode([
      'status' => 'success',
      'booking_id' => $booking_id
    ]);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'DB insert failed']);
  }

  $stmt->close();
  exit;
}

/* =====================================================
   4. GENERATE TICKET QR (SECURE + UNIQUE)
===================================================== */
if (isset($_GET['generate_qr'])) {

  $booking_id = (int) ($_GET['booking_id'] ?? 0);

  if ($booking_id <= 0) {
    exit("Invalid booking");
  }

  // Get booking from DB (ANTI-TAMPER)
  $stmt = $conn->prepare("SELECT destination, team_size, total_price, member_names FROM bookings WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ii", $booking_id, $user_id);
  $stmt->execute();
  $stmt->bind_result($destination, $team_size, $total_price, $member_names);
  $stmt->fetch();
  $stmt->close();

  if (!$destination) {
    exit("Booking not found");
  }

  // ===== Ticket ID FORMAT =====
  $destPrefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $destination), 0, 3));
  if ($destPrefix === '')
    $destPrefix = 'TRZ';

  $ticket_id = "TKT_{$destPrefix}_{$team_size}";

  // ===== QR DATA (NO USER INPUT TRUST) =====
  $qrData =
    "TICKET ID: $ticket_id\n" .
    "NAME: $name\n" .
    "EMAIL: $email\n" .
    "PHONE: $phone\n" .
    "DESTINATION: $destination\n" .
    "MEMBERS: $team_size\n" .
    "NAMES: $member_names\n" .
    "TOTAL: $total_price\n" .
    "STATUS: NOT PAID";

  // Unique file per booking
  $file = "qr_" . $booking_id . ".png";

  QRcode::png($qrData, $file);

  echo $file;
  exit;
}

/* =====================================================
   5. DOWNLOAD QR
===================================================== */
if (isset($_GET['download_qr'])) {

  $file = $_GET['download_qr'];

  if (file_exists($file)) {
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="TouRz_Ticket_QR.png"');
    readfile($file);
    exit;
  }

  exit("File not found");
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>TouRz@Order</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    /* Your styles here */
    #ticketPopup {
      background: #1a1a1a url(b.png) no-repeat center center;
      background-size: cover;
      height: auto;
      min-height: 80vh;
      width: 35vw;
      padding: 40px !important;
      color: white;
      border-radius: 1.5vw;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      display: none;
      z-index: 1000;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    #ticketContent {
      text-align: left;
    }

    #ticketPopup img#logo-img {
      width: 150px;
      margin-bottom: 20px;
    }

    #ticketPopup h4 {
      margin: 10px 0;
      font-weight: 400;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      padding-bottom: 5px;
    }

    #ticketPopup h4 span {
      font-weight: 600;
      color: #00ffd5;
    }

    #member-names-list {
      margin-top: 5px;
      font-size: 14px;
      color: #00ffd5;
      list-style-position: inside;
      padding-left: 0;
    }

    #member-names-list li {
      margin-bottom: 2px;
      font-weight: 500;
    }

    #qr-img {
      width: 150px !important;
      height: 150px !important;
      margin: 20px auto;
      display: block;
      border: 5px solid white;
      border-radius: 10px;
    }

    .ticket-btns {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }

    .ticket-btns button {
      flex: 1;
      padding: 10px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    #close {
      background: #ff4757;
      color: white;
    }

    #downloadPDF {
      background: #2ed573;
      color: white;
    }

    #confirm-ticket-btn,
    #proceed-payment-btn {
      margin-top: 4vh;
      padding: 2vh 2vw;
      border: none;
      background-color: darkcyan;
      border-radius: 1vw;
      font-size: 1.4vw;
      color: white;
      width: 100%;
      cursor: pointer;
    }

    #confirm-ticket-btn:hover,
    #proceed-payment-btn:hover {
      filter: brightness(1.2);
    }

    .member-name-input {
      background: white !important;
      border: 1px solid darkcyan !important;
      border-radius: 0.5vw !important;
      padding: 10px !important;
      width: 100% !important;
      margin-top: 10px !important;
      text-align: left !important;
      font-size: 1.2vw !important;
      color: black !important;
    }

    #member-names-list {
      margin-top: 5px;
      font-size: 14px;
      color: #00ffd5;
      list-style-position: inside;
      padding-left: 0;
    }

    #member-names-list li {
      margin-bottom: 2px;
      font-weight: 500;
    }
  </style>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>

<body
  onload="loadHTML('header.php', 'header'); loadHTML('footer.html', 'footer'); loadValues(); showOverlayWithSpinner();">
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

  <div class="order-container">
    <div class="order-left">
      <label for="pack">Destination Chosen</label><br />
      <input type="button" value="Cox's Bazar" id="pack" readonly /><br />

      <label for="price">Price - Per Person</label>
      <input type="button" value="0" id="price" readonly />

      <label for="price">Members</label>
      <section id="mem">
        <input type="button" value="-"
          onclick="member.value=Math.max(1, parseInt(member.value)-1); updatePrices(); generateNameInputs();" />
        <input type="button" value="1" id="member" />
        <input type="button" value="+"
          onclick="member.value=parseInt(member.value)+1; updatePrices(); generateNameInputs();" />
      </section>

      <div id="memberNamesContainer" style="margin-top: 20px;">
        <!-- Dynamic inputs will appear here -->
      </div>

      <button type="button" id="confirm-ticket-btn" onclick="showTicket()">Confirm Ticket</button>
    </div>

    <!-- Ticket Popup -->
    <div id="ticketPopup">
      <div id="ticketContent">
        <img src="log.png" id="logo-img" alt="Logo" /><br />
        <h4>Name: <span><?= htmlspecialchars($name) ?></span></h4>
        <h4>Email: <span><?= htmlspecialchars($email) ?></span></h4>
        <h4>Contact No: <span><?= htmlspecialchars($phone) ?></span></h4>
        <h4>Destination: <span id="ticket-dest"></span></h4>
        <h4>Team Size: <span id="ticket-size"></span></h4>
        <h4>Total Price: <span id="ticket-total"></span></h4>
        <h4>Members List:</h4>
        <ul id="member-names-list"></ul>
        <h4>Payment Status: <span style="color: #ff9f43;">Not Paid</span></h4>
        <img src="" id="qr-img" alt="QR Code" />
        <h5 style="text-align: center; margin-top: 10px;">Thanks for choosing TourZ</h5>
      </div>
      <div class="ticket-btns">
        <button id="downloadPDF" onclick="downloadPDF()">Download PDF</button>
        <button id="downloadQR">Download QR</button>
        <button id="close" onclick="closeTicket()">Close</button>
      </div>
    </div>

    <div class="order-right">
      <label for="total">Total Price</label>
      <input type="button" value="" id="total" />

      <label for="total">Service Charge</label>
      <input type="button" value="" id="service" />

      <label for="coupon">Promo Code (If Any)</label>
      <input type="text" placeholder="Enter code" id="coupon"
        style="background: white; border: 1px solid darkcyan; border-radius: 0.5vw; padding: 5px; width: 100%; text-align: center;"
        oninput="updatePrices()" />

      <label for="final">Final Price</label>
      <input type="button" value="" id="final" />
      <button type="button" id="proceed-payment-btn" onclick="proceedToPayment()">Proceed to Payment</button>
      <br /><br /><br />
    </div>
  </div>

  <div id="footer"></div>

  <script>
    function proceedToPayment() {
      const finalVal = document.getElementById('final').value;
      const numericVal = finalVal.replace(/[^\d]/g, '');
      localStorage.setItem('final_price', numericVal);
      window.location.href = 'final.html';
    }

    let currentDiscount = 0;
    let currentDiscountType = 'fixed';

    async function updatePrices() {
      const member = parseInt(document.getElementById('member').value);
      const price = parseInt(document.getElementById('price').value) || 0;
      const couponCode = document.getElementById('coupon').value.trim();
      const dest = document.getElementById('pack').value;

      let baseTotal = price * member;
      const service = 270 * member;

      // If coupon changed, validate it
      if (couponCode !== "") {
        try {
          const res = await fetch('pay.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
              action: 'validate_promo',
              code: couponCode,
              destination: dest
            })
          });
          const data = await res.json();
          if (data.status === 'success') {
            currentDiscount = data.value;
            currentDiscountType = data.type;
            document.getElementById('coupon').style.borderColor = 'green';
          } else {
            currentDiscount = 0;
            document.getElementById('coupon').style.borderColor = 'red';
          }
        } catch (e) { console.error(e); }
      } else {
        currentDiscount = 0;
        document.getElementById('coupon').style.borderColor = 'darkcyan';
      }

      let discountAmount = 0;
      if (currentDiscount > 0) {
        if (currentDiscountType === 'percentage') {
          discountAmount = baseTotal * (currentDiscount / 100);
        } else {
          discountAmount = currentDiscount;
        }
      }

      let finalTotal = baseTotal - discountAmount;

      document.getElementById('total').value = baseTotal + (discountAmount > 0 ? ' (-' + Math.round(discountAmount) + ')' : '') + '/-';
      document.getElementById('service').value = '+' + service + '/-';
      document.getElementById('final').value = Math.round(finalTotal + service) + '/-';
    }

    function generateNameInputs() {
      const container = document.getElementById('memberNamesContainer');
      const count = parseInt(document.getElementById('member').value);

      if (count <= 1) {
        container.innerHTML = '';
        return;
      }

      const existingInputs = container.querySelectorAll('input');
      const existingValues = Array.from(existingInputs).map(i => i.value);

      container.innerHTML = '<label style="font-size: 1.2vw; color: darkcyan;">Additional Member Names (Required)</label>';
      for (let i = 1; i < count; i++) {
        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = `Member ${i + 1} Name`;
        input.className = 'member-name-input';
        input.required = true;
        if (existingValues[i - 1]) input.value = existingValues[i - 1];
        container.appendChild(input);
      }
    }

    async function showTicket() {
      const dest = document.getElementById('pack').value;
      const size = document.getElementById('member').value;
      const total = document.getElementById('final').value;

      // Primary user name from PHP
      const userName = "<?= htmlspecialchars($name) ?>";

      // Get additional member names
      const nameInputs = document.querySelectorAll('.member-name-input');
      const additionalNames = Array.from(nameInputs).map(i => i.value.trim());

      // Validation for additional members
      if (additionalNames.some(n => n === "")) {
        alert("Please fill in all additional member names.");
        return;
      }

      // Combine primary user + additional members
      const allNames = [userName, ...additionalNames];

      // Update spans in popup
      document.getElementById('ticket-dest').innerText = dest;
      document.getElementById('ticket-size').innerText = size;
      document.getElementById('ticket-total').innerText = total;

      const list = document.getElementById('member-names-list');
      list.innerHTML = "";
      allNames.forEach(n => {
        const li = document.createElement('li');
        li.innerText = n;
        list.appendChild(li);
      });

      // 1. Save booking
      const res = await fetch('pay.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'save_booking',
          destination: dest,
          team_size: size,
          total_price: total,
          member_names: allNames.join(', ')
        })
      });

      const data = await res.json();
      if (data.status !== 'success') {
        alert(data.message);
        return;
      }

      const booking_id = data.booking_id;

      // 2. Load QR
      const qrPath = await fetch(`pay.php?generate_qr=1&booking_id=${booking_id}`)
        .then(res => res.text());

      document.getElementById('qr-img').src = qrPath;

      // 3. Download QR button logic
      document.getElementById('downloadQR').onclick = function () {
        window.location.href = `pay.php?download_qr=${qrPath}`;
      };

      // 4. Show popup
      document.getElementById('ticketPopup').style.display = 'block';
    }

    function downloadPDF() {
      // 1. Target the content specifically to avoid shell issues
      const element = document.getElementById('ticketPopup');

      // Store original styles
      const originalStyle = element.getAttribute('style') || "";
      const btns = element.querySelector('.ticket-btns');

      // 2. Prepare element for capture
      btns.style.display = 'none';
      element.style.position = 'relative';
      element.style.top = '0';
      element.style.left = '0';
      element.style.transform = 'none';
      element.style.margin = '0 auto';
      element.style.width = '450px';

      // Ensure we are at the top for capture
      const scrollPos = window.scrollY;
      window.scrollTo(0, 0);

      const opt = {
        margin: 10,
        filename: 'TouRz_Ticket.pdf',
        image: { type: 'jpeg', quality: 1.0 },
        html2canvas: {
          scale: 2,
          useCORS: true,
          backgroundColor: '#1a1a1a',
          scrollY: 0,
          scrollX: 0
        },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };

      // 3. Generate PDF
      html2pdf().set(opt).from(element).save()
        .then(() => {
          // Success: Restore everything
          btns.style.display = 'flex';
          element.setAttribute('style', originalStyle);
          window.scrollTo(0, scrollPos);
        })
        .catch(err => {
          console.error('PDF Error:', err);
          btns.style.display = 'flex';
          element.setAttribute('style', originalStyle);
          alert("Ticket generated! If download didn't start, please try again.");
        });
    }

    function closeTicket() {
      document.getElementById('ticketPopup').style.display = 'none';
    }

    generateNameInputs();
    updatePrices(); // call on load
  </script>
  <script src="script.js"></script>
</body>

</html>