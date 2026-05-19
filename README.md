<div align="center">
  <img src="logo.png" alt="TouRz Logo" width="150"/>
  <h1>🌍 TouRz - Comprehensive Travel Management System</h1>
</div>

<p align="center">
  A full-stack, AI-integrated Web Application tailored for seamless tour discovery, booking management, and ticket generation. Built according to IEEE standard SRS principles for scalable, reliable software.
</p>

<p align="center">
  <a href="#-about-the-project">About</a> •
  <a href="#-core-features">Features</a> •
  <a href="#-user-roles--workflows">Workflows</a> •
  <a href="#-technology-stack">Tech Stack</a> •
  <a href="#-database-architecture">Database</a> •
  <a href="#-security-implementation">Security</a> •
  <a href="#-installation--local-setup">Setup</a>
</p>

---

## 📖 About the Project

Tourism heavily relies on fragmented, manually driven booking systems. **TouRz** centralizes this process by providing an interactive digital-first platform targeting destinations both within Bangladesh (e.g., Cox's Bazar, Bandarban) and internationally (e.g., Bali, Ladakh). 

By offering a highly responsive, custom-built MVC architecture without heavy third-party PHP frameworks, TouRz ensures minimal overhead, rapid server responses, and absolute flexibility. It combines modern UI paradigms with strong backend logic to process secure tour bookings.

## 🚀 Core Features

- **End-to-End Tour Booking**: Navigate from viewing tour itineraries to processing simulated payments to receiving a QR-coded PDF ticket.
- **On-Device AI Chatbot Integration**: Integrated with an Ollama Llama 3.1 microservice via `chatbot_api.php` for intelligent 24/7 customer query handling without third-party API costs.
- **Custom Tour Requests**: Customers can propose custom dates and destinations via a dedicated dashboard. Admins can review, accept, or decline these requests.
- **Dynamic Tour Portfolios**: Includes detailed itineraries, accommodation specifics (hotels/resorts), estimated transport, and next departure dates.
- **Robust Admin Dashboard**: Real-time analytical overview of active users, total revenue, bookings, tour scheduling, and chatbot logging.

---

## 👥 User Roles & Workflows

### 1. Guests
- Browse available domestic and international tours.
- Read general itineraries, FAQs, and use the AI Chatbot for quick queries.
- Prompted to register or login when trying to book.

### 2. Registered Customers
- **Authentication**: Create secure accounts with bcrypt-hashed passwords.
- **Booking Flow**: 
  1. Select a tour and click "Book Now".
  2. Confirm group size, review the calculated price, and apply promotion codes (simulated).
  3. Finalize payment using a mock environment simulating mobile banking (bKash/Nagad) or cards.
  4. Receive immediate confirmation with a dynamically generated **QR Code** via PHP QR Code library.
- **Dashboard**: Track upcoming trips, download PDF tickets for past bookings, and cancel active bookings prior to deadlines.
- **Customization**: Submit private tour requests for unlisted destinations.

### 3. System Administrators
- Accessible securely via `$BASE_URL/admin-panel.php`.
- Dashboard summary showing global system statistics.
- **Tour Management (CRUD)**: Dynamically author new tours, suspend inactive ones, or adjust prices and next departure dates.
- **User Management**: Oversee registered accounts, resolve support requests, and validate custom tour propositions.

---

## 💻 Technology Stack

### Frontend Architecture
- **Structure**: Deeply semantic HTML5.
- **Styling**: Vanilla CSS3 customized per component (`styles.css`, `admin.css`, `reg-styles.css`) utilizing modern flexbox and grid layouts for complete cross-device responsiveness.
- **Interactivity**: Vanilla ES6 JavaScript for dynamic DOM updates, asynchronous form submissions, and chatbot widget integrations.

### Backend Infrastructure
- **Server Environment**: Apache HTTP Server (typically via XAMPP or LAMP stack).
- **Language**: PHP 8.x
- **Architecture Pattern**: Model-View-Controller (MVC) paradigm segregating database logic (`db.php`), business logic (`save_booking.php`, `cancel_booking.php`), and presentation (`header.php`, `index.php`).
- **External Scripts**: `phpqrcode` for ticket rendering.

### Database & Microservices
- **Database Engine**: MySQL 8.x Server
- **AI Microservice**: Local Ollama Instance loading `llama3.1` tied to `chatbot_api.php` over HTTP.

---

## 🗄️ Database Architecture

The system utilizes a 3NF normalized MySQL database with relational integrity mapped via Foreign Keys. Key schemas include:

- **`users`**: Manages credentials, contact information, and role definitions (customer vs. admin).
- **`tours`**: Stores extensive metadata (name, description, starting points, costs, transport types, maps, schedules).
- **`bookings`**: Relates `users` and `tours`. Tracks payment statuses, booking dates, and cancellation states.
- **`tour_requests`**: Handles the custom propositions submitted by customers.
- **`notifications`**: Async alerts tied to a `user_id` for booking approvals or declines.

---

## 🛡️ Security Implementation

Security is considered a first-class citizen inside the TouRz ecosystem:

1. **Password Hashing**: Utilization of PHP's native `password_hash()` implementing the `bcrypt` algorithm.
2. **Prepared Statements**: All SQL queries interact via PDO (PHP Data Objects) with bound parameters avoiding all forms of SQL injections.
3. **Session Highjacking Mitigations**: Secure, HTTP-only session cookies tied to strict lifecycle management across `login.php` and `logout.php`.
4. **XSS & CSRF Prevention**: Usage of `htmlspecialchars()` when rendering dynamic data to the view, coupled with hidden CSRF tokens in state-modifying requests.
5. **Authorization Checks**: Role-based access control preventing direct URL navigation (e.g., stopping non-admins accessing `admin_api.php`).

---

## ⚙️ Installation & Local Setup

### Prerequisites
1. [XAMPP](https://www.apachefriends.org/index.html) or a dedicated LAMP environment (PHP 8.0+ and MySQL 8.0+).
2. [Ollama](https://ollama.ai/) (Required only if fully utilizing the AI chatbot feature).

### Step-by-Step Guide

**1. Clone the repository**
Download the project and move the `TouRz` directory into your local server root.
- Windows/XAMPP: `C:\xampp\htdocs\TouRz`
- Linux/LAMP: `/var/www/html/TouRz`

**2. Configure the Database**
- Open phpMyAdmin (usually `http://localhost/phpmyadmin`).
- Create a new database named `tour_management` (or preferred name).
- Import the provided schema: Navigate to the `Import` tab and upload `data_new.sql`.
- Verify database connection config in `db.php`:
  ```php
  $host = "localhost";
  $user = "root";  // default XAMPP username
  $pass = "";      // default XAMPP password
  $dbname = "tour_management"; 
  ```

**3. Setup the AI Chatbot Service (Optional but Highly Recommended)**
- Launch the Ollama command line.
- Pull the required model:
  ```bash
  ollama run llama3.1
  ```
- Ensure Ollama's HTTP server is running on its default `11434` port as expected by `chatbot_api.php`.

**4. Launch the application**
- Open your browser and navigate to: `http://localhost/TouRz/index.php`.
- *Note: To test the Admin panel, login using the admin credentials injected into the database during the `data_new.sql` import.*

---

## 🔮 Roadmap (Version 2.0)

While Version 1.0 covers the comprehensive core functionality, future expansions include:
- **Sandbox Payment Gateway**: Upgrading the mock payment to the official Stripe API and bKash Checkout Sandbox.
- **SMTP Email Notifications**: Hooking PHPMailer to trigger automated emails complementing the existing PDF ticket downloads.
- **Forgotten Password Flow**: Secure recovery tokens delivered via email.
- **RESTful Architecture Upgrade**: Migrating backend views fully into API endpoints yielding JSON for mobile application integration.

---

<div align="center">
  <b>Developed for a comprehensive university software engineering project.</b><br>
  <i>See the `/Documentations/` folder for the rigorous IEEE Std 830-1998 Software Requirements Specifications and Justification Reports.</i>
</div>
