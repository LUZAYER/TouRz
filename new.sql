-- Add tour_date and status to bookings table
ALTER TABLE bookings
    ADD COLUMN IF NOT EXISTS tour_date DATETIME AFTER destination,
    ADD COLUMN IF NOT EXISTS status ENUM('active', 'cancelled') DEFAULT 'active' AFTER tour_date;

-- Create notifications table for cancellation alerts
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Who the notification is for (user_id or 0 for admin)
    booking_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
