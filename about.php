<?php
require_once 'db.php';

// Fetch stats
$total_tours = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0] ?? 0;
$available_districts = $conn->query("SELECT COUNT(*) FROM tours")->fetch_row()[0] ?? 0;
$satisfied_travelers = $conn->query("SELECT SUM(team_size) FROM bookings")->fetch_row()[0] ?? 0;
$five_star_reviews = $conn->query("SELECT COUNT(*) FROM reviews WHERE rating = 5")->fetch_row()[0] ?? 0;

// Fetch actual reviews
$reviews_query = "SELECT r.*, u.name as user_name, t.name as tour_name, t.link as tour_link 
                  FROM reviews r 
                  JOIN users u ON r.user_id = u.id 
                  LEFT JOIN tours t ON r.tour_id = t.id 
                  ORDER BY r.created_at DESC";
$reviews_result = $conn->query($reviews_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | TouRz</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/8.1.0/mdb.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css">
    <style>
        .review-section {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            margin: 40px auto;
            max-width: 1200px;
        }

        .review-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .review-card:hover {
            transform: translateY(-5px);
        }

        .star-rating {
            color: #ffc107;
            margin-bottom: 10px;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .reviewer-name {
            font-weight: 700;
            color: var(--sooth-primary);
            font-size: 1.1rem;
        }

        .review-date {
            font-size: 0.85rem;
            color: #888;
        }

        #about-head h1 span {
            color: var(--sooth-accent);
            background-image: none;
            -webkit-text-fill-color: initial;
        }

        #about-right div h3 {
            color: var(--sooth-primary);
        }
    </style>
</head>

<body class="soothing-body"
    onload="loadHTML('header.php', 'header'); loadHTML('footer.html', 'footer'); showOverlayWithSpinner();">
    <div class="spinner-box">
        <div class="spinner">
            <?php for ($i = 0; $i < 10; $i++): ?>
                <div></div><?php endfor; ?>
        </div>
    </div>

    <div id="header"></div>

    <div id="about-head">
        <h1>About <span>TouRz</span></h1>
    </div>

    <div id="about-main">
        <div></div>
        <!-- Gallery Section (Static) -->
        <div class="row">
            <div class="col-lg-4 col-md-12 mb-4 mb-lg-0">
                <img src="https://mdbcdn.b-cdn.net/img/Photos/Horizontal/Nature/4-col/img%20(73).webp"
                    class="w-100 shadow-1-strong rounded mb-4" alt="Nature" />
                <img src="https://mdbcdn.b-cdn.net/img/Photos/Vertical/mountain1.webp"
                    class="w-100 shadow-1-strong rounded mb-4" alt="Mountains" />
            </div>
            <div class="col-lg-4 mb-4 mb-lg-0">
                <img src="https://mdbcdn.b-cdn.net/img/Photos/Vertical/mountain2.webp"
                    class="w-100 shadow-1-strong rounded mb-4" alt="Cloudy Mountains" />
                <img src="https://mdbcdn.b-cdn.net/img/Photos/Horizontal/Nature/4-col/img%20(18).webp"
                    class="w-100 shadow-1-strong rounded mb-4" alt="Waves" />
            </div>
            <div class="col-lg-4 mb-4 mb-lg-0">
                <img src="https://mdbcdn.b-cdn.net/img/Photos/Vertical/mountain3.webp"
                    class="w-100 shadow-1-strong rounded mb-4" alt="Yosemite" />
                <img src="https://mdbcdn.b-cdn.net/img/Photos/Horizontal/Nature/4-col/img%20(73).webp"
                    class="w-100 shadow-1-strong rounded mb-4" alt="Nature" />
            </div>
        </div>

        <!-- Dynamic Stats Section -->
        <div id="about-right">
            <div class="tours">
                <h4>Total Tours Completed</h4>
                <h3 id="about-tour"><?php echo sprintf("%02d", $total_tours); ?></h3>
            </div>
            <div class="available">
                <h4>Available Districts</h4>
                <h3 id="about-available"><?php echo sprintf("%02d", $available_districts); ?></h3>
            </div>
            <div class="tours">
                <h4>Satisfied Travelers</h4>
                <h3 id="about-travelers"><?php echo sprintf("%02d", $satisfied_travelers); ?></h3>
            </div>
            <div class="available">
                <h4>Total 5 * <br>Reviews</h4>
                <h3 id="about-review"><?php echo sprintf("%02d", $five_star_reviews); ?></h3>
            </div>
        </div>
    </div>

    <!-- Company Info Section -->
    <div style="max-width: 1200px; margin: 40px 0px; padding: 0 20px;">
        <h3 id="about-info">
            <span
                style="color: var(--sooth-primary); font-size: 1.8rem; border-left: 5px solid var(--sooth-accent); padding-left: 15px; margin-bottom: 10px; display: inline-block;">Who
                are we?</span> <br>
            Welcome to TouRz, your trusted companion for exploring the wonders of Bangladesh! Since 2023, we have been
            passionately dedicated to crafting unforgettable travel experiences for adventurers, explorers, and culture
            enthusiasts alike.
            <br><br>
            <span
                style="color: var(--sooth-primary); font-size: 1.8rem; border-left: 5px solid var(--sooth-accent); padding-left: 15px; margin-bottom: 10px; display: inline-block;">What
                is our goal?</span> <br>
            At TouRz, we believe that every journey tells a story, and we are here to make yours extraordinary. From
            serene beaches to majestic hills, bustling cities to tranquil villages, we curate tours that showcase the
            diverse beauty and rich heritage of Bangladesh.
            <br><br>
            <span
                style="color: var(--sooth-primary); font-size: 1.8rem; border-left: 5px solid var(--sooth-accent); padding-left: 15px; margin-bottom: 10px; display: inline-block;">"Let
                us guide you to discover the heart of Bangladesh, one memorable trip at a time. With TouRz, every
                journey is a masterpiece waiting to be explored"</span>
        </h3>
    </div>

    <!-- Dynamic Reviews Section -->
    <div class="review-section">
        <h2 style="text-align: center; color: var(--sooth-primary); margin-bottom: 40px; font-weight: 700;">What Our
            Travelers Say</h2>
        <div class="row">
            <?php if ($reviews_result && $reviews_result->num_rows > 0): ?>
                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="review-card">
                            <div class="review-header">
                                <div>
                                    <span class="reviewer-name"><?php echo htmlspecialchars($review['user_name']); ?></span>
                                    <?php if (!empty($review['tour_name'])): ?>
                                        <br>
                                        <a href="<?php echo htmlspecialchars($review['tour_link']); ?>" style="font-size: 0.8rem; color: var(--sooth-accent); text-decoration: none;">
                                            <i class="fas fa-location-dot" style="margin-right: 5px;"></i><?php echo htmlspecialchars($review['tour_name']); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <div class="star-rating">
                                <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                                <?php for ($i = $review['rating']; $i < 5; $i++): ?>
                                    <i class="far fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p style="color: #555; line-height: 1.6; font-style: italic;">
                                "<?php echo htmlspecialchars($review['comment']); ?>"
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; width: 100%; color: #666;">No reviews yet. Be the first to share your
                    experience!</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="footer"></div>
    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Stats Animation
            setTimeout(() => {
                const tour = document.getElementById("about-tour");
                if (tour) animateValue(tour, 0, <?php echo (int) $total_tours; ?>, 2500);

                const avail = document.getElementById("about-available");
                if (avail) animateValue(avail, 0, <?php echo (int) $available_districts; ?>, 2500);

                const travelers = document.getElementById("about-travelers");
                if (travelers) animateValue(travelers, 0, <?php echo (int) $satisfied_travelers; ?>, 2500);

                const review = document.getElementById("about-review");
                if (review) animateValue(review, 0, <?php echo (int) $five_star_reviews; ?>, 2500);
            }, 1000); // Small delay to allow page load/spinner
        });
    </script>
</body>

</html>