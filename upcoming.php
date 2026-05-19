<?php
require_once 'db.php';

// Read tour schedules from JSON
$jsonPath = __DIR__ . '/tour_schedules.json';
$schedules = [];
if (file_exists($jsonPath)) {
    $schedules = json_decode(file_get_contents($jsonPath), true) ?: [];
}

// Sort by next_tour ascending
uasort($schedules, function($a, $b) {
    return strtotime($a['next_tour']) - strtotime($b['next_tour']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Upcoming Tours | TouRz</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="logo2.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="soothing-body" onload="loadHTML('header.php', 'header'); loadHTML('footer.html', 'footer'); showOverlayWithSpinner();">

    <div class="spinner-box">
        <div class="spinner">
            <?php for ($i = 0; $i < 10; $i++): ?>
                <div></div>
            <?php endfor; ?>
        </div>
    </div>

    <div id="header"></div>

    <div class="soothing-hero">
        <h1>Upcoming Journeys</h1>
        <p>Discover your next adventure. Our carefully curated upcoming tours are designed to soothe your soul and ignite your wanderlust.</p>
    </div>

    <div class="tour-grid">
        <?php foreach ($schedules as $slug => $tour): ?>
            <a href="<?= htmlspecialchars($slug) ?>.html" class="tour-card">
                <div>
                    <h2><i class="fa-solid fa-location-dot" style="font-size: 0.8em; margin-right: 10px; color: var(--sooth-accent);"></i><?= htmlspecialchars($tour['name']) ?></h2>
                    <div class="countdown-box" id="countdown-<?= htmlspecialchars($slug) ?>">
                        <div class="countdown-unit"><span class="countdown-val day">00</span><span class="countdown-label">Days</span></div>
                        <div class="countdown-unit"><span class="countdown-val hour">00</span><span class="countdown-label">Hrs</span></div>
                        <div class="countdown-unit"><span class="countdown-val min">00</span><span class="countdown-label">Mins</span></div>
                        <div class="countdown-unit"><span class="countdown-val sec">00</span><span class="countdown-label">Secs</span></div>
                    </div>
                </div>
                <div class="tour-info">
                    <span class="tour-cost"><i class="fa-solid fa-tag" style="margin-right: 5px; opacity: 0.7;"></i><?= number_format($tour['cost']) ?>/-</span>
                    <span class="tour-bookings"><i class="fa-solid fa-users" style="margin-right: 5px; opacity: 0.7;"></i><?= (int)$tour['bookings_count'] ?> Booked</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div id="footer"></div>

    <script src="script.js"></script>
    <script>
        // Load all countdowns from JSON
        fetch('tour_schedules.json')
            .then(r => r.json())
            .then(data => {
                Object.keys(data).forEach(slug => {
                    const container = document.getElementById('countdown-' + slug);
                    if (!container) return;

                    const dayEl = container.querySelector('.day');
                    const hourEl = container.querySelector('.hour');
                    const minEl = container.querySelector('.min');
                    const secEl = container.querySelector('.sec');
                    
                    const target = new Date(data[slug].next_tour).getTime();

                    function tick() {
                        const diff = target - Date.now();
                        if (diff <= 0) { 
                            container.innerHTML = '<div style="grid-column: 1 / -1; font-weight: bold; color: var(--sooth-primary); padding: 10px;">Journey Started!</div>'; 
                            return; 
                        }
                        
                        const d = Math.floor(diff / 86400000);
                        const h = Math.floor((diff % 86400000) / 3600000);
                        const m = Math.floor((diff % 3600000) / 60000);
                        const s = Math.floor((diff % 60000) / 1000);

                        dayEl.textContent = d.toString().padStart(2, '0');
                        hourEl.textContent = h.toString().padStart(2, '0');
                        minEl.textContent = m.toString().padStart(2, '0');
                        secEl.textContent = s.toString().padStart(2, '0');
                    }
                    tick();
                    setInterval(tick, 1000);
                });
            });
    </script>
</body>
</html>
