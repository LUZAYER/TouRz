CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
);





CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    team_size INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);



CREATE TABLE tours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    link VARCHAR(255) NOT NULL,
    category ENUM('domestic', 'international') NOT NULL
);

INSERT INTO tours (name, image, link, category) VALUES
-- Domestic Tours
('Kuakata', 'Images/Cox_s bazar/c3.jpg', 'kuakata.html', 'domestic'),
('Saint Martins', 'Images/Saint Martin/s.jpg', 'saintmartin.html', 'domestic'),
('Sajek Valley', 'Images/Sajek/s.jpg', 'sajek.html', 'domestic'),
('Shundarbans', 'Images/Shundorbon/s.jpg', 'shundarban.html', 'domestic'),
('Bandarbans', 'Images/Bandorbon/b3.jpg', 'bandarban.html', 'domestic'),
('Cox\'s Bazar', 'Images/Cox_s bazar/c.jpg', 'cox.html', 'domestic'),
('Khagrachari', 'sss.jpg', 'khagrachari.html', 'domestic'),
('Sreemangal', 'Images/Sajek/s2.jpg', 'sreemangal.html', 'domestic'),

-- International Tours
('Ladakh', 'Images/Ladakh/l.jpg', 'ladakh.html', 'international'),
('Bali', 'Images/Bali/b.jpg', 'bali.html', 'international'),
('Saudi Arab', 'Images/Saudi/s.jpg', 'bali.html', 'international');


ALTER TABLE users
ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE users
ADD COLUMN birthday DATE,
ADD COLUMN nid VARCHAR(50),
ADD COLUMN profile_photo VARCHAR(255);