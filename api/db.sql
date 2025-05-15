CREATE DATABASE schedule_tracker;

USE schedule_tracker;

CREATE TABLE schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_date DATE,
    time_slot VARCHAR(50),
    tour_name VARCHAR(100),
    match_info VARCHAR(50),
    status ENUM('Qualified', 'Disqualified', 'No Update') DEFAULT 'No Update',
    booking_price DECIMAL(10, 2),
    sell_earn_price DECIMAL(10, 2)
);
