CREATE DATABASE IF NOT EXISTS hostelcvm;
USE hostelcvm;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS floors;
DROP TABLE IF EXISTS seat_allocation;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS hostels;
DROP TABLE IF EXISTS colleges;

SET FOREIGN_KEY_CHECKS = 1;

-- ===============================
-- COLLEGES
-- ===============================
CREATE TABLE colleges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

INSERT INTO colleges (id,name) VALUES
(1,'ADIT'),
(4,'ISTAR'),
(5,'MBIT');

-- ===============================
-- HOSTELS
-- ===============================
CREATE TABLE hostels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    total_seats INT NOT NULL,
    maintenance_seats INT DEFAULT 0
);

INSERT INTO hostels (id,name,total_seats,maintenance_seats) VALUES
(4,'SHARDA',100,10),
(5,'A M PATEL',100,0);

-- ===============================
-- FLOORS
-- ===============================
CREATE TABLE floors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT NOT NULL,
    floor_number INT NOT NULL,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE
);

-- Floors for SHARDA hostel (id=4)
INSERT INTO floors (hostel_id,floor_number) VALUES
(4,1),
(4,2);

-- ===============================
-- ROOMS
-- ===============================
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT NOT NULL,
    floor_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    room_type ENUM('AC','NON-AC') NOT NULL,
    total_beds INT NOT NULL,
    filled_beds INT DEFAULT 0,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE,
    FOREIGN KEY (floor_id) REFERENCES floors(id) ON DELETE CASCADE
);

-- Rooms for SHARDA (hostel 4)
-- Floor IDs will be 1 & 2 because inserted first
INSERT INTO rooms (hostel_id,floor_id,room_number,room_type,total_beds,filled_beds) VALUES
(4,1,'101','AC',2,1),
(4,1,'102','NON-AC',3,0),
(4,2,'201','AC',2,0),
(4,2,'202','NON-AC',3,0);

-- ===============================
-- SEAT ALLOCATION
-- ===============================
CREATE TABLE seat_allocation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT NOT NULL,
    college_id INT NOT NULL,
    allocated_seats INT NOT NULL,
    ac_seats INT DEFAULT 0,
    non_ac_seats INT DEFAULT 0,
    ac_fees DECIMAL(10,2) DEFAULT 0,
    non_ac_fees DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE,
    FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE
);

INSERT INTO seat_allocation
(hostel_id,college_id,allocated_seats,ac_seats,non_ac_seats,ac_fees,non_ac_fees)
VALUES
(4,1,50,10,40,15000,10000),
(4,5,30,5,25,15000,10000);

-- ===============================
-- STUDENTS
-- ===============================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    college_id INT NOT NULL,
    hostel_id INT NOT NULL,
    room_type ENUM('AC','NON-AC') NOT NULL,
    room_id INT NULL,
    total_fees DECIMAL(10,2) DEFAULT 0,
    paid_amount DECIMAL(10,2) DEFAULT 0,
    status ENUM('reserved','allotted') DEFAULT 'reserved',
    FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
);

INSERT INTO students
(name,college_id,hostel_id,room_type,room_id,total_fees,paid_amount,status)
VALUES
('Vaibhav',1,4,'AC',NULL,15000,0,'reserved'),
('Vaibhav2',1,4,'AC',1,15000,7500,'allotted');

-- ===============================
-- USERS
-- ===============================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    role ENUM('superadmin','collegeadmin','rector') NOT NULL,
    college_id INT NULL,
    hostel_id INT NULL,
    FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE SET NULL,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE SET NULL
);

INSERT INTO users (name,email,password,role,college_id,hostel_id) VALUES
('Super Admin','superadmin@gmail.com','12345','superadmin',NULL,NULL),
('ADIT Admin','adit@gmail.com','12345','collegeadmin',1,NULL),
('Hostel A Rector','rector@gmail.com','12345','rector',NULL,4);
