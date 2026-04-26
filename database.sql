CREATE DATABASE medical_ai_pro;
USE medical_ai_pro;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin','patient') DEFAULT 'patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    doctor VARCHAR(100),
    appointment_date DATE,
    status ENUM('Pending','Approved','Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Default admin
INSERT INTO users (name,email,password,role)
VALUES ('Admin','admin@mail.com',
'$2y$10$examplehashforadmin123456789','admin');



CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    experience INT,
    rating DECIMAL(2,1)
);

INSERT INTO doctors (name, specialization, experience, rating) VALUES
('Dr. John Smith', 'Cardiologist', 15, 4.8),
('Dr. Emily Brown', 'Cardiologist', 12, 4.6),
('Dr. David Wilson', 'Dermatologist', 10, 4.7),
('Dr. Sarah Johnson', 'Neurologist', 18, 4.9),
('Dr. Michael Lee', 'Pediatrician', 8, 4.5),
('Dr. Robert Taylor', 'Orthopedic', 14, 4.6);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id)
);



ALTER TABLE appointments ADD appointment_time VARCHAR(20) NOT NULL;


CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

INSERT INTO departments (name) VALUES
('Cardiology'),
('Neurology'),
('Orthopedics'),
('Dermatology'),
('ENT'),
('General Medicine');

ALTER TABLE appointments ADD department_id INT;

ALTER TABLE appointments 
ADD patient_id INT;

ALTER TABLE appointments
ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending';