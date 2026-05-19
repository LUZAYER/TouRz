<?php
session_start();
require_once 'db.php';

// Admin guard
if (!isset($_SESSION['is_admin']) || (int) $_SESSION['is_admin'] !== 1) {
  header("Location: login.html");
  exit();
}
$admin_name = htmlspecialchars($_SESSION['username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TouRz Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
  <link rel="shortcut icon" href="logo1.png" type="image/x-icon">
</head>

<body>
  <div class="admin-wrapper">
    <!-- Sidebar -->
    <nav class="admin-sidebar" id="sidebar">
      <div class="sidebar-brand">
        <a href="index.php"><img src="logo2.png" alt="TouRz" class="sidebar-logo"></a>
        <span class="brand-text">TouRz Admin</span>
      </div>
      <div class="sidebar-menu">
        <a class="menu-item active" onclick="showSection('dashboard')" id="menu-dashboard">
          <i class="bi bi-grid-1x2-fill"></i> <span>Dashboard</span>
        </a>
        <a class="menu-item" onclick="showSection('users')" id="menu-users">
          <i class="bi bi-people-fill"></i> <span>Users</span>
        </a>
        <a class="menu-item" onclick="showSection('bookings')" id="menu-bookings">
          <i class="bi bi-journal-bookmark-fill"></i> <span>Bookings</span>
        </a>
        <a class="menu-item" onclick="showSection('requests')" id="menu-requests">
          <i class="bi bi-envelope-paper-fill"></i> <span>Tour Requests</span>
          <span class="badge-dot" id="request-badge" style="display:none;"></span>
        </a>
        <a class="menu-item" onclick="showSection('tours')" id="menu-tours">
          <i class="bi bi-map-fill"></i> <span>All Tours</span>
        </a>
        <a class="menu-item" onclick="showSection('addtour')" id="menu-addtour">
          <i class="bi bi-plus-circle-fill"></i> <span>Add Tour</span>
        </a>
        <a class="menu-item" onclick="showSection('chatlogs')" id="menu-chatlogs">
          <i class="bi bi-chat-dots-fill"></i> <span>Chatbot Logs</span>
        </a>
        <div class="sidebar-divider"></div>
        <a class="menu-item" href="index.php">
          <i class="bi bi-house-fill"></i> <span>View Site</span>
        </a>
        <a class="menu-item menu-logout" href="logout.php">
          <i class="bi bi-box-arrow-left"></i> <span>Logout</span>
        </a>
      </div>
      <div class="sidebar-footer">
        <i class="bi bi-person-circle"></i> <?= $admin_name ?>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
      <div class="admin-topbar">
        <h4 id="page-title"><i class="bi bi-grid-1x2-fill"></i> Dashboard</h4>
        <div class="topbar-right">
          <span class="topbar-time" id="live-time"></span>
        </div>
      </div>

      <!-- ========== DASHBOARD ========== -->
      <div class="content-section" id="section-dashboard">
        <div class="stats-grid">
          <div class="stat-card stat-users">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-info">
              <h3 id="stat-users">0</h3>
              <p>Total Users</p>
            </div>
          </div>
          <div class="stat-card stat-bookings">
            <div class="stat-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
            <div class="stat-info">
              <h3 id="stat-bookings">0</h3>
              <p>Total Bookings</p>
            </div>
          </div>
          <div class="stat-card stat-revenue">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-info">
              <h3 id="stat-revenue">৳0</h3>
              <p>Total Revenue</p>
            </div>
          </div>
          <div class="stat-card stat-pending">
            <div class="stat-icon"><i class="bi bi-envelope-paper-fill"></i></div>
            <div class="stat-info">
              <h3 id="stat-pending">0</h3>
              <p>Pending Requests</p>
            </div>
          </div>
          <div class="stat-card stat-tours">
            <div class="stat-icon"><i class="bi bi-map-fill"></i></div>
            <div class="stat-info">
              <h3 id="stat-tours">0</h3>
              <p>Active Tours</p>
            </div>
          </div>
        </div>
        <div class="recent-section">
          <h5><i class="bi bi-clock-history"></i> Recent Bookings</h5>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Customer</th>
                  <th>Destination</th>
                  <th>Price</th>
                  <th>Tour Date</th>
                </tr>
              </thead>
              <tbody id="recent-bookings-tbody">
                <tr>
                  <td colspan="5" class="text-center">Loading...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ========== USERS ========== -->
      <div class="content-section" id="section-users" style="display:none;">
        <div class="section-header">
          <h5><i class="bi bi-people-fill"></i> All Registered Users</h5>
          <input type="text" class="search-input" placeholder="Search users..."
            onkeyup="filterTable(this, 'users-tbody')">
        </div>
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Birthday</th>
                <th>NID</th>
                <th>Role</th>
              </tr>
            </thead>
            <tbody id="users-tbody">
              <tr>
                <td colspan="7" class="text-center">Loading...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ========== BOOKINGS ========== -->
      <div class="content-section" id="section-bookings" style="display:none;">
        <div class="section-header">
          <h5><i class="bi bi-journal-bookmark-fill"></i> All Bookings</h5>
          <input type="text" class="search-input" placeholder="Search bookings..."
            onkeyup="filterTable(this, 'bookings-tbody')">
        </div>
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>User</th>
                <th>Destination</th>
                <th>Team</th>
                <th>Price</th>
                <th>Tour Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="bookings-tbody">
              <tr>
                <td colspan="8" class="text-center">Loading...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ========== TOUR REQUESTS ========== -->
      <div class="content-section" id="section-requests" style="display:none;">
        <div class="section-header">
          <h5><i class="bi bi-envelope-paper-fill"></i> Tour Location Requests</h5>
        </div>
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>User</th>
                <th>Email</th>
                <th>Destination</th>
                <th>Description</th>
                <th>Preferred Dates</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="requests-tbody">
              <tr>
                <td colspan="9" class="text-center">Loading...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ========== ALL TOURS ========== -->
      <div class="content-section" id="section-tours" style="display:none;">
        <div class="section-header">
          <h5><i class="bi bi-map-fill"></i> All Tour Locations</h5>
        </div>
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Cost</th>
                <th>Bookings</th>
                <th>Next Tour</th>
                <th>Page</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tours-tbody">
              <tr>
                <td colspan="7" class="text-center">Loading...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ========== ADD TOUR ========== -->
      <div class="content-section" id="section-addtour" style="display:none;">
        <div class="section-header">
          <h5><i class="bi bi-plus-circle-fill"></i> Add New Tour Location</h5>
        </div>
        <div id="addtour-msg"></div>
        <form id="addTourForm" class="tour-form">
          <div class="form-grid">
            <div class="form-group">
              <label>Tour Name *</label>
              <input type="text" name="name" id="at-name" required placeholder="e.g., Rangamati">
            </div>
            <div class="form-group">
              <label>Category *</label>
              <select name="category" id="at-category" required>
                <option value="domestic">Domestic</option>
                <option value="international">International</option>
              </select>
            </div>
            <div class="form-group">
              <label>Cost (per person) *</label>
              <input type="number" name="cost" id="at-cost" required placeholder="e.g., 5600">
            </div>
            <div class="form-group">
              <label>Starting Point</label>
              <input type="text" name="starting_point" id="at-sp" value="Kamalapur Railway Station, Dhaka">
            </div>
            <div class="form-group">
              <label>Travel Duration</label>
              <input type="text" name="travel_duration" id="at-td" placeholder="e.g., 10 Hours">
            </div>
            <div class="form-group">
              <label>Tour Duration</label>
              <input type="text" name="tour_duration" id="at-tourd" placeholder="e.g., 2 Days & 3 Nights">
            </div>
            <div class="form-group">
              <label>Transportation</label>
              <input type="text" name="transportation" id="at-trans" placeholder="e.g., Bus, Train">
            </div>
            <div class="form-group">
              <label>Hotel / Stay</label>
              <input type="text" name="hotel" id="at-hotel" placeholder="e.g., Hotel Sea Queen">
            </div>
            <div class="form-group">
              <label>Next Tour Date & Time</label>
              <input type="datetime-local" name="next_tour" id="at-nexttour">
            </div>
            <div class="form-group">
              <label>Main Image Path *</label>
              <input type="text" name="image" id="at-image" required placeholder="e.g., Images/Cox_s bazar/c.jpg">
            </div>
            <div class="form-group">
              <label>Carousel Image 1</label>
              <input type="text" name="carousel_img1" placeholder="e.g., Images/place/1.jpg">
            </div>
            <div class="form-group">
              <label>Carousel Image 2</label>
              <input type="text" name="carousel_img2" placeholder="e.g., Images/place/2.jpg">
            </div>
            <div class="form-group">
              <label>Carousel Image 3</label>
              <input type="text" name="carousel_img3" placeholder="e.g., Images/place/3.jpg">
            </div>
          </div>
          <div class="form-group form-full">
            <label>Description</label>
            <textarea name="description" id="at-desc" rows="4" placeholder="Write about this destination..."></textarea>
          </div>
          <div class="form-group form-full">
            <label>Google Maps Embed URL</label>
            <input type="text" name="map_embed" id="at-map" placeholder="Paste the iframe src URL from Google Maps">
          </div>
          <button type="submit" class="btn-add-tour"><i class="bi bi-plus-lg"></i> Create Tour & Generate Page</button>
        </form>
      </div>

      <!-- ========== CHATBOT LOGS ========== -->
      <div class="content-section" id="section-chatlogs" style="display:none;">
        <div class="section-header">
          <h5><i class="bi bi-chat-dots-fill"></i> Chatbot Interaction Logs</h5>
          <input type="text" class="search-input" placeholder="Search logs..."
            onkeyup="filterTable(this, 'chatlogs-tbody')">
        </div>
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>User</th>
                <th>Query</th>
                <th>Response</th>
                <th>Timestamp</th>
              </tr>
            </thead>
            <tbody id="chatlogs-tbody">
              <tr>
                <td colspan="5" class="text-center">Loading...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>

  <script>
    // ===== SECTION NAVIGATION =====
    function showSection(name) {
      document.querySelectorAll('.content-section').forEach(s => s.style.display = 'none');
      document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
      document.getElementById('section-' + name).style.display = 'block';
      const menuEl = document.getElementById('menu-' + name);
      if (menuEl) menuEl.classList.add('active');

      const titles = {
        dashboard: '<i class="bi bi-grid-1x2-fill"></i> Dashboard',
        users: '<i class="bi bi-people-fill"></i> Users',
        bookings: '<i class="bi bi-journal-bookmark-fill"></i> Bookings',
        requests: '<i class="bi bi-envelope-paper-fill"></i> Tour Requests',
        tours: '<i class="bi bi-map-fill"></i> All Tours',
        addtour: '<i class="bi bi-plus-circle-fill"></i> Add New Tour',
        chatlogs: '<i class="bi bi-chat-dots-fill"></i> Chatbot Logs'
      };
      document.getElementById('page-title').innerHTML = titles[name] || 'Dashboard';

      // Load data
      if (name === 'dashboard') loadDashboard();
      else if (name === 'users') loadUsers();
      else if (name === 'bookings') loadBookings();
      else if (name === 'requests') loadRequests();
      else if (name === 'tours') loadTours();
      else if (name === 'chatlogs') loadChatLogs();
    }

    // ===== LIVE TIME =====
    function updateTime() {
      const now = new Date();
      document.getElementById('live-time').textContent = now.toLocaleString('en-BD', { dateStyle: 'medium', timeStyle: 'short' });
    }
    setInterval(updateTime, 1000);
    updateTime();

    // ===== API HELPER =====
    async function api(action, post = null) {
      const opts = post
        ? { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(post) }
        : {};
      const res = await fetch('admin_api.php?action=' + action, opts);
      return res.json();
    }

    // ===== DASHBOARD =====
    async function loadDashboard() {
      const d = await api('dashboard_stats');
      document.getElementById('stat-users').textContent = d.users;
      document.getElementById('stat-bookings').textContent = d.bookings;
      document.getElementById('stat-revenue').textContent = '৳' + Number(d.revenue).toLocaleString();
      document.getElementById('stat-pending').textContent = d.pending_requests;
      document.getElementById('stat-tours').textContent = d.tours;
      if (d.pending_requests > 0) document.getElementById('request-badge').style.display = 'inline-block';

      let html = '';
      if (d.recent_bookings && d.recent_bookings.length) {
        d.recent_bookings.forEach(b => {
          const tDate = b.tour_date ? b.tour_date : 'Pending';
          html += `<tr><td>${b.id}</td><td>${esc(b.name)}</td><td>${esc(b.destination)}</td><td>৳${Number(b.total_price).toLocaleString()}</td><td style="font-size:0.8rem;">${tDate}</td></tr>`;
        });
      } else {
        html = '<tr><td colspan="5" class="text-center">No bookings yet</td></tr>';
      }
      document.getElementById('recent-bookings-tbody').innerHTML = html;
    }

    // ===== USERS =====
    async function loadUsers() {
      const users = await api('get_users');
      let html = '';
      if (users.length) {
        users.forEach(u => {
          const role = parseInt(u.is_admin) === 1 ? '<span class="badge-admin">Admin</span>' : '<span class="badge-user">User</span>';
          html += `<tr><td>${u.id}</td><td>${esc(u.name)}</td><td>${esc(u.email)}</td><td>${esc(u.phone || 'N/A')}</td><td>${esc(u.birthday || 'N/A')}</td><td>${esc(u.nid || 'N/A')}</td><td>${role}</td></tr>`;
        });
      } else {
        html = '<tr><td colspan="7" class="text-center">No users found</td></tr>';
      }
      document.getElementById('users-tbody').innerHTML = html;
    }

    // ===== BOOKINGS =====
    async function loadBookings() {
      const bookings = await api('get_bookings');
      let html = '';
      if (bookings.length) {
        bookings.forEach(b => {
          const status = b.status === 'active'
            ? '<span class="badge bg-success" style="font-size:0.7rem;">Active</span>'
            : '<span class="badge bg-danger" style="font-size:0.7rem;">Cancelled</span>';

          const action = b.status === 'active'
            ? `<button class="btn btn-sm btn-outline-danger" style="font-size:0.7rem; padding:2px 5px;" onclick="cancelBooking(${b.id})">Cancel</button>`
            : '<span class="text-muted small">N/A</span>';

          const tDate = b.tour_date ? b.tour_date : 'Pending';

          html += `<tr><td>${b.id}</td><td>${esc(b.name)}</td><td>${esc(b.destination)}</td><td>${b.team_size}</td><td>৳${Number(b.total_price).toLocaleString()}</td><td style="font-size:0.75rem;">${tDate}</td><td>${status}</td><td>${action}</td></tr>`;
        });
      } else {
        html = '<tr><td colspan="8" class="text-center">No bookings found</td></tr>';
      }
      document.getElementById('bookings-tbody').innerHTML = html;
    }

    async function cancelBooking(id) {
      if (!confirm('Are you sure you want to cancel this booking? This will notify the user.')) return;
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = 'cancel_booking.php';
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'booking_id';
      input.value = id;
      form.appendChild(input);
      document.body.appendChild(form);
      form.submit();
    }

    // ===== TOUR REQUESTS =====
    async function loadRequests() {
      const reqs = await api('get_requests');
      let html = '';
      if (reqs.length) {
        reqs.forEach(r => {
          let statusBadge = '';
          if (r.status === 'pending') statusBadge = '<span class="badge-pending">Pending</span>';
          else if (r.status === 'approved') statusBadge = '<span class="badge-approved">Approved</span>';
          else statusBadge = '<span class="badge-rejected">Rejected</span>';

          let actions = '';
          if (r.status === 'pending') {
            actions = `<button class="btn-approve" onclick="approveRequest(${r.id})"><i class="bi bi-check-lg"></i> Approve</button>
                       <button class="btn-reject" onclick="rejectRequest(${r.id})"><i class="bi bi-x-lg"></i></button>`;
          } else if (r.status === 'approved') {
            actions = '<span style="color:#2ecc71; font-size:0.85rem;">✓ Done</span>';
          } else {
            actions = '<span style="color:#e74c3c; font-size:0.85rem;">✗ Rejected</span>';
          }

          html += `<tr><td>${r.id}</td><td>${esc(r.user_name || 'Unknown')}</td><td>${esc(r.user_email || '')}</td><td>${esc(r.destination_name)}</td><td style="max-width:200px;">${esc(r.description || '')}</td><td>${esc(r.preferred_dates || 'N/A')}</td><td>${statusBadge}</td><td>${r.created_at}</td><td>${actions}</td></tr>`;
        });
      } else {
        html = '<tr><td colspan="9" class="text-center">No requests yet</td></tr>';
      }
      document.getElementById('requests-tbody').innerHTML = html;
    }

    async function approveRequest(id) {
      const data = await api('approve_request', { action: 'approve_request', id: id });
      if (data.success) {
        // Switch to Add Tour tab and pre-fill name + description
        showSection('addtour');
        document.getElementById('at-name').value = data.destination_name || '';
        document.getElementById('at-desc').value = data.description || '';
        document.getElementById('addtour-msg').innerHTML = '<div class="alert-success-custom">Request approved! Fill in the details below and create the tour.</div>';
      } else {
        alert(data.error || 'Failed');
      }
    }

    async function rejectRequest(id) {
      if (!confirm('Reject this request?')) return;
      const data = await api('reject_request', { action: 'reject_request', id: id });
      if (data.success) loadRequests();
      else alert(data.error || 'Failed');
    }

    // ===== ALL TOURS =====
    async function loadTours() {
      const tours = await api('get_tours');
      let html = '';
      if (tours.length) {
        tours.forEach(t => {
          const btn = `<button class="btn btn-sm btn-outline-primary" style="font-size:0.7rem; padding:2px 5px;" onclick="updateTourDate(${t.id}, '${esc(t.name)}')">Update Date</button>`;
          html += `<tr><td>${t.id}</td><td>${esc(t.name)}</td><td><span class="badge-${t.category}">${t.category}</span></td><td>৳${Number(t.cost || 0).toLocaleString()}</td><td>${t.bookings_count || 0}</td><td>${t.next_tour_datetime || 'N/A'}</td><td><a href="${esc(t.link)}" target="_blank" class="link-view">${esc(t.link)}</a></td><td>${btn}</td></tr>`;
        });
      } else {
        html = '<tr><td colspan="7" class="text-center">No tours</td></tr>';
      }
      document.getElementById('tours-tbody').innerHTML = html;
    }

    // ===== ADD TOUR FORM =====
    document.getElementById('addTourForm').addEventListener('submit', async function (e) {
      e.preventDefault();
      const fd = new FormData(this);
      fd.append('action', 'add_tour');
      const params = new URLSearchParams(fd);

      const res = await fetch('admin_api.php?action=add_tour', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
      });
      const data = await res.json();

      if (data.success) {
        document.getElementById('addtour-msg').innerHTML = `<div class="alert-success-custom">Tour created! Page generated: <a href="${data.link}" target="_blank">${data.link}</a></div>`;
        this.reset();
        document.getElementById('at-sp').value = 'Kamalapur Railway Station, Dhaka';
      } else {
        document.getElementById('addtour-msg').innerHTML = `<div class="alert-error-custom">${data.error}</div>`;
      }
    });

    // ===== CHATBOT LOGS =====
    async function loadChatLogs() {
      const logs = await api('get_chatbot_logs');
      let html = '';
      if (logs.length) {
        logs.forEach(l => {
          const userName = l.user_name || ('Guest #' + (l.user_id || '?'));
          const query = esc(l.query_text);
          const resp = esc(l.response_text.length > 120 ? l.response_text.substring(0, 120) + '...' : l.response_text);
          html += `<tr><td>${l.id}</td><td>${esc(userName)}</td><td style="max-width:200px;">${query}</td><td style="max-width:300px; font-size:0.8rem; color:#8b8fa3;">${resp}</td><td>${l.queried_at}</td></tr>`;
        });
      } else {
        html = '<tr><td colspan="5" class="text-center">No chatbot interactions yet</td></tr>';
      }
      document.getElementById('chatlogs-tbody').innerHTML = html;
    }

    // ===== TABLE FILTER =====
    function filterTable(input, tbodyId) {
      const val = input.value.toLowerCase();
      const rows = document.getElementById(tbodyId).getElementsByTagName('tr');
      for (let row of rows) {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
      }
    }

    // ===== ESCAPE HTML =====
    function esc(str) {
      if (!str) return '';
      const d = document.createElement('div');
      d.textContent = str;
      return d.innerHTML;
    }

    // ===== INIT =====
    async function updateTourDate(id, name) {
      const newDate = prompt(`Enter new tour date for ${name} (YYYY-MM-DD HH:MM:SS):`, "");
      if (newDate === null) return;
      
      const res = await api('update_tour_date', { id, date: newDate });
      if (res.status === 'success') {
        alert('Date updated!');
        loadTours();
        loadBookings();
        loadDashboard();
      } else {
        alert('Error: ' + res.message);
      }
    }

    loadDashboard();
  </script>
</body>

</html>