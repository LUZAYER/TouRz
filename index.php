<!DOCTYPE html>
<html>

<head>
    <title>TouRz</title>
    <link rel="shortcut icon" href="logo1.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body onload="loadHTML('footer.html', 'footer'); showOverlayWithSpinner();">

    <section class="showcase">
        <?php
        session_start();
        require_once 'db.php';

        // Fetch tours
        $domesticTours = $conn->query("SELECT * FROM tours WHERE category='domestic'");
        $internationalTours = $conn->query("SELECT * FROM tours WHERE category='international'");
        ?>
        <header>
            <img id="logo" src="logo2.png" alt="">
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
                            <li><a href="profile.php"><?= htmlspecialchars($_SESSION['username']) ?></a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="login.html">LogIn</a></li>
                    <?php endif; ?>
                    <li id="log"><a href="pay.html"><i class="bi bi-luggage-fill"></i></a></li>
                </ul>
            </div>
            <div class="toggle"></div>
        </header>

        <video id="stick"
            src="y2mate.com - Forest with trees in the morning sunlight 4K Premium Stock Video Without watermarkCinematic shot_1080.mp4"
            muted loop autoplay></video>
        <div class="overlay"></div>
        <div class="text">
            <h2>Explore, Experience</h2>
            <h3>Embrace the World!</h3>
            <p>Discover unforgettable journeys with TouRz - your gateway to exciting adventures, unique destinations,
                and memorable travel experiences worldwide.</p>
            <a href="#tour">Explore</a>
        </div>
    </section>

    <!-- Special Offers Ad Section -->
    <section class="offers-ad-section" style="padding: 60px 10vw; background: #fff;">
        <div class="offers-ad-container" style="
            background: linear-gradient(135deg, #1a4a4a 0%, #2d7d7d 100%);
            border-radius: 20px;
            padding: 50px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #fff;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
            flex-wrap: wrap;
        ">
            <!-- Decorative circle -->
            <div
                style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;">
            </div>

            <div class="ad-content" style="z-index: 2; flex: 1; min-width: 300px;">
                <h2 style="font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; margin-bottom: 10px; color: white;">
                    Exclusive Deals Await!</h2>
                <p style="font-size: 1.1rem; opacity: 0.9; margin-bottom: 25px;">Grab up to 15% discount on your dream
                    destinations. Limited time summer promotions are live now!</p>
                <div class="promo-preview" style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
                    <span
                        style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; border: 1px solid rgba(255,255,255,0.3);">#BaliSpecial</span>
                    <span
                        style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; border: 1px solid rgba(255,255,255,0.3);">#CoxDeals</span>
                    <span
                        style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; border: 1px solid rgba(255,255,255,0.3);">#Ladakh2026</span>
                </div>
                <a href="offers.html" style="
                    display: inline-block;
                    padding: 12px 35px;
                    background: #fff;
                    color: #1a4a4a;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    transition: transform 0.3s ease;
                ">
                    View All Offers
                </a>
            </div>

            <div class="ad-badge" style="z-index: 2; margin-top: 20px;">
                <div style="
                    width: 140px;
                    height: 140px;
                    background: #ff4757;
                    border-radius: 50%;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    transform: rotate(15deg);
                    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
                    border: 4px solid #fff;
                ">
                    <span style="font-size: 0.9rem; font-weight: 600;">UP TO</span>
                    <span style="font-size: 2.2rem; font-weight: 900; line-height: 1;">15%</span>
                    <span style="font-size: 1.1rem; font-weight: 700;">OFF</span>
                </div>
            </div>
        </div>
    </section>
    <section id="tour">
        <h2>Destinations</h2>
        <div class="place">
            <?php while ($tour = $domesticTours->fetch_assoc()): ?>
                <div>
                    <a href="<?= htmlspecialchars($tour['link']) ?>">
                        <img src="<?= htmlspecialchars($tour['image']) ?>" alt="<?= htmlspecialchars($tour['name']) ?>">
                        <p><?= htmlspecialchars($tour['name']) ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>

        <h1 id="inter">International Tours</h1>
        <div class="place" id="international">
            <?php while ($tour = $internationalTours->fetch_assoc()): ?>
                <div>
                    <a href="<?= htmlspecialchars($tour['link']) ?>">
                        <img src="<?= htmlspecialchars($tour['image']) ?>" alt="<?= htmlspecialchars($tour['name']) ?>">
                        <p><?= htmlspecialchars($tour['name']) ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <div id="footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>

</html>