-- =============================================
-- TouRz New Schema - Import into phpMyAdmin
-- =============================================

-- 1. Tour Requests Table
CREATE TABLE IF NOT EXISTS tour_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    destination_name VARCHAR(255) NOT NULL,
    description TEXT,
    preferred_dates VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    admin_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 1b. Add tour_date and status to bookings
ALTER TABLE bookings
    ADD COLUMN IF NOT EXISTS tour_date DATETIME AFTER destination,
    ADD COLUMN IF NOT EXISTS status ENUM('active', 'cancelled') DEFAULT 'active' AFTER tour_date;

-- 1c. Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 2. Add extra columns to tours table for full tour details
ALTER TABLE tours
    ADD COLUMN IF NOT EXISTS description TEXT AFTER name,
    ADD COLUMN IF NOT EXISTS starting_point VARCHAR(255) DEFAULT 'Kamalapur Railway Station, Dhaka' AFTER description,
    ADD COLUMN IF NOT EXISTS cost INT DEFAULT 0 AFTER starting_point,
    ADD COLUMN IF NOT EXISTS travel_duration VARCHAR(100) AFTER cost,
    ADD COLUMN IF NOT EXISTS tour_duration VARCHAR(100) AFTER travel_duration,
    ADD COLUMN IF NOT EXISTS transportation VARCHAR(100) AFTER tour_duration,
    ADD COLUMN IF NOT EXISTS hotel VARCHAR(255) AFTER transportation,
    ADD COLUMN IF NOT EXISTS map_embed TEXT AFTER hotel,
    ADD COLUMN IF NOT EXISTS next_tour_datetime DATETIME AFTER map_embed,
    ADD COLUMN IF NOT EXISTS bookings_count INT DEFAULT 0 AFTER next_tour_datetime,
    ADD COLUMN IF NOT EXISTS carousel_images TEXT AFTER bookings_count,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- 3. Update existing tours with full details
UPDATE tours SET
    description = 'Cox''s Bazar (Bengali: কক্সবাজার) is a city, fishing port, tourism centre, and district headquarters in southeastern Bangladesh. Cox''s Bazar Beach, one of the most popular tourist attractions in Bangladesh, is the longest uninterrupted natural beach in the world.',
    starting_point = 'Kamalapur Railway Station, Dhaka',
    cost = 5600,
    travel_duration = '10 Hours',
    tour_duration = '2 Days & 3 Nights',
    transportation = 'Train',
    hotel = 'Hotel Sea Queen',
    next_tour_datetime = '2026-06-15 08:00:00',
    bookings_count = 0
WHERE name = 'Cox''s Bazar';

UPDATE tours SET
    description = 'Saint Martins Island (সেন্টমার্টিন দ্বীপ) is a small coral island in the northeastern part of the Bay of Bengal, about 9 km south of the tip of the Cox''s Bazar-Teknaf peninsula. It is the only coral island of Bangladesh.',
    starting_point = 'Kamalapur Railway Station, Dhaka',
    cost = 7200,
    travel_duration = '19 Hours',
    tour_duration = '4 Days & 5 Nights',
    transportation = 'Train & Ship',
    hotel = 'Coral View Resort',
    next_tour_datetime = '2026-06-23 08:00:00',
    bookings_count = 0
WHERE name = 'Saint Martins';

UPDATE tours SET
    description = 'Sajek Valley is situated among the hills of the Kasalong range of mountains in Sajek union, Baghaichhari Upazila, Rangamati District. Called the Queen of Hills, it sits 1,476 feet above sea level.',
    starting_point = 'Kamalapur Railway Station, Dhaka',
    cost = 4600,
    travel_duration = '8 Hours',
    tour_duration = '2 Days & 1 Night',
    transportation = 'Bus & Jeep',
    hotel = 'Sajek Resort',
    next_tour_datetime = '2026-06-10 07:00:00',
    bookings_count = 0
WHERE name = 'Sajek Valley';

UPDATE tours SET
    description = 'The Sundarbans is a mangrove area in the delta formed by the confluence of the Ganges, Brahmaputra and Meghna Rivers in the Bay of Bengal. It is the largest single block of tidal halophytic mangrove forest in the world.',
    starting_point = 'Kamalapur Railway Station, Dhaka',
    cost = 12000,
    travel_duration = '12 Hours',
    tour_duration = '3 Days & 2 Nights',
    transportation = 'Bus & Launch',
    hotel = 'Sundarban Tiger Camp',
    next_tour_datetime = '2026-07-01 06:00:00',
    bookings_count = 0
WHERE name = 'Shundarbans';

UPDATE tours SET
    description = 'Bandarban is a district in southeastern Bangladesh and a part of the Chittagong Hill Tracts. It is known for its stunning hill landscapes, waterfalls, and indigenous communities.',
    starting_point = 'Kamalapur Railway Station, Dhaka',
    cost = 4500,
    travel_duration = '9 Hours',
    tour_duration = '3 Days & 2 Nights',
    transportation = 'Bus',
    hotel = 'Hotel Hillbird',
    next_tour_datetime = '2026-06-20 07:00:00',
    bookings_count = 0
WHERE name = 'Bandarbans';

UPDATE tours SET
    description = 'Kuakata is a panoramic sea beach in southern Bangladesh. It is known as the daughter of the sea and is one of the rare spots where you can watch both sunrise and sunset from the beach.',
    starting_point = 'Kamalapur Railway Station, Dhaka',
    cost = 4200,
    travel_duration = '11 Hours',
    tour_duration = '2 Days & 1 Night',
    transportation = 'Bus & Launch',
    hotel = 'Hotel Neelanjana',
    next_tour_datetime = '2026-06-18 08:00:00',
    bookings_count = 0
WHERE name = 'Kuakata';

UPDATE tours SET
    description = 'Khagrachari is a hilly district in the Chittagong Hill Tracts of Bangladesh. Known for Alutila Cave, Richhang Waterfall, and serene hill landscapes surrounded by indigenous communities.',
    starting_point = 'Kamalapur Railway Station, Dhaka',
    cost = 3800,
    travel_duration = '7 Hours',
    tour_duration = '2 Days & 1 Night',
    transportation = 'Bus',
    hotel = 'Hotel Eco Inn',
    next_tour_datetime = '2026-06-25 07:00:00',
    bookings_count = 0
WHERE name = 'Khagrachari';

UPDATE tours SET
    description = 'Sreemangal is a town in the Moulvibazar District of Sylhet Division, known as the tea capital of Bangladesh. Surrounded by lush green tea gardens and rainforests including Lawachara National Park.',
    starting_point = 'Kamalapur Railway Station, Dhaka',
    cost = 5000,
    travel_duration = '6 Hours',
    tour_duration = '2 Days & 1 Night',
    transportation = 'Train',
    hotel = 'Grand Sultan Tea Resort',
    next_tour_datetime = '2026-06-12 06:30:00',
    bookings_count = 0
WHERE name = 'Sreemangal';

UPDATE tours SET
    description = 'Ladakh is a region administered by India as a union territory. Known as the Land of High Passes, Ladakh is renowned for its remote mountain beauty, Buddhist monasteries, and unique culture.',
    starting_point = 'Hazrat Shahjalal International Airport, Dhaka',
    cost = 21500,
    travel_duration = '8 Hours (Flight + Road)',
    tour_duration = '5 Days & 4 Nights',
    transportation = 'Flight & Car',
    hotel = 'The Grand Dragon Ladakh',
    next_tour_datetime = '2026-07-10 05:00:00',
    bookings_count = 0
WHERE name = 'Ladakh';

UPDATE tours SET
    description = 'Bali is a province of Indonesia and the westernmost of the Lesser Sunda Islands. Known for its forested volcanic mountains, iconic rice paddies, beaches and coral reefs.',
    starting_point = 'Hazrat Shahjalal International Airport, Dhaka',
    cost = 19500,
    travel_duration = '10 Hours (Flight)',
    tour_duration = '5 Days & 4 Nights',
    transportation = 'Flight',
    hotel = 'Padma Resort Ubud',
    next_tour_datetime = '2026-07-20 06:00:00',
    bookings_count = 0
WHERE name = 'Bali';

-- 4. Chatbot Logs Table
CREATE TABLE IF NOT EXISTS chatbot_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    query_text TEXT NOT NULL,
    response_text TEXT NOT NULL,
    queried_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 5. Promos Table
CREATE TABLE IF NOT EXISTS promos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    destination_name VARCHAR(255), -- For specific destination promos
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Insert default promos
INSERT INTO promos (code, discount_type, discount_value, destination_name, expiry_date) VALUES 
('BALI10', 'percentage', 10.00, 'Bali', '2026-12-31'),
('COX15', 'percentage', 15.00, 'Cox\'s Bazar', '2026-12-31'),
('LADAKH26', 'fixed', 5000.00, 'Ladakh', '2026-12-31');

-- 7. Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tour_id INT, -- Optional, can link to a specific tour
    rating INT NOT NULL DEFAULT 5,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 8. Sample Reviews (Assuming user ID 1 exists as an admin or first user)
-- Note: In a real scenario, these would be linked to actual users
INSERT INTO reviews (user_id, tour_id, rating, comment) VALUES 
(1, 1, 5, 'TouRz made my trip to Cox''s Bazar unforgettable! Everything was perfectly organized.'),
(1, 4, 5, 'The Sundarbans tour was like a dream. The guide was very knowledgeable.'),
(1, 10, 5, 'Bali was spectacular! Padma Resort was the best recommendation.'),
(1, 3, 4, 'Sajek Valley clouds are mesmerizing. Great service by TouRz team.'),
(1, 6, 5, 'Kuakata sunrise and sunset from the same beach – simply amazing!'),
(1, 9, 5, 'Ladakh is indeed the Land of High Passes. TouRz handled the high-altitude logistics perfectly.');

