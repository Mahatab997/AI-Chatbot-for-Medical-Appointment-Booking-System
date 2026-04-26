DROP DATABASE IF EXISTS medical_ai_pro;
CREATE DATABASE medical_ai_pro;
USE medical_ai_pro;

-- =============================
-- USERS TABLE
-- =============================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','patient') DEFAULT 'patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin
INSERT INTO users (name,email,password,role)
VALUES (
'Admin',
'admin@mail.com',
'admin@gmail.com',
'admin'
);

-- =============================
-- DOCTORS TABLE
-- =============================
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
('Dr. Robert Taylor', 'Orthopedic', 14, 4.6), 
('Dr. Ayesha Rahman','Gynecologist',11,4.7),
('Dr. Tanvir Hasan','Psychiatrist',9,4.5),
('Dr. Mahmudul Karim','Oncologist',16,4.8),
('Dr. Nusrat Jahan','Endocrinologist',13,4.6),
('Dr. Farhan Ahmed','Gastroenterologist',10,4.7),
('Dr. Sabiha Islam','Ophthalmologist',8,4.4),
('Dr. Rakib Chowdhury','Urologist',12,4.6),
('Dr. Sharmeen Akter','Nephrologist',14,4.7),
('Dr. Imran Hossain','Pulmonologist',9,4.5),
('Dr. Mehedi Hasan','General Surgeon',15,4.8),

('Dr. Arif Mahmood','Cardiologist',18,4.9),
('Dr. Sadia Noor','Dermatologist',7,4.4),
('Dr. Kamrul Islam','Neurologist',17,4.8),
('Dr. Rifat Sultana','Pediatrician',10,4.6),
('Dr. Jannatul Ferdous','ENT Specialist',9,4.5),
('Dr. Zahid Hossain','Orthopedic',14,4.6),
('Dr. Hasan Murad','Radiologist',12,4.7),
('Dr. Tania Ahmed','Hematologist',11,4.6),
('Dr. Mizanur Rahman','Diabetologist',13,4.7),
('Dr. Shakil Ahmed','Anesthesiologist',15,4.8),

('Dr. Lubna Akter','Rheumatologist',10,4.5),
('Dr. Saifur Rahman','Plastic Surgeon',12,4.6),
('Dr. Nabila Karim','Allergist',8,4.4),
('Dr. Omar Faruq','Infectious Disease',14,4.7),
('Dr. Rashed Khan','Neurosurgeon',20,4.9),
('Dr. Fahmida Sultana','Pathologist',11,4.5),
('Dr. Zubair Ahmed','Cardiac Surgeon',19,4.8),
('Dr. Amina Begum','Family Medicine',7,4.3),
('Dr. Ashraful Islam','Sports Medicine',9,4.4),
('Dr. Iffat Ara','Geriatrician',13,4.6),

('Dr. Rezaul Karim','Emergency Medicine',12,4.7),
('Dr. Tasnim Chowdhury','Dentist',8,4.5),
('Dr. Belal Hossain','Maxillofacial Surgeon',14,4.6),
('Dr. Runa Laila','Physiotherapist',6,4.3),
('Dr. Sharif Uddin','Oncologist',17,4.8),
('Dr. Nasreen Akhter','Gynecologist',15,4.7),
('Dr. Monirul Hasan','Urologist',13,4.6),
('Dr. Sumaiya Rahman','Dermatologist',9,4.5),
('Dr. Faisal Ahmed','Pulmonologist',11,4.6),
('Dr. Shuvo Das','Orthopedic',10,4.5),

('Dr. Rafiq Islam','Cardiologist',16,4.7),
('Dr. Mim Akter','Pediatrician',7,4.4),
('Dr. Arman Hossain','Neurologist',14,4.7),
('Dr. Fahad Mahmud','Gastroenterologist',12,4.6),
('Dr. Laila Yasmin','Ophthalmologist',9,4.5),
('Dr. Shadman Sakib','ENT Specialist',8,4.4),
('Dr. Khaled Saif','General Surgeon',13,4.7),
('Dr. Tuba Rahman','Endocrinologist',11,4.6),
('Dr. Samiul Islam','Nephrologist',15,4.7),
('Dr. Roksana Parvin','Psychiatrist',10,4.5),

('Dr. Jahidul Islam','Radiologist',12,4.6),
('Dr. Nusrat Tamanna','Hematologist',9,4.5);

-- =============================
-- DEPARTMENTS TABLE
-- =============================
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

-- =============================
-- APPOINTMENTS TABLE (FINAL CLEAN VERSION)
-- =============================
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_id INT NOT NULL,
    department_id INT,
    appointment_date DATE NOT NULL,
    appointment_time VARCHAR(20) NOT NULL,
    status ENUM('Pending','Approved','Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);



--25/04/2026 theke db new kore add korchi ekhan theke 

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(100),
    doctor_name VARCHAR(100),
    appointment_date DATE,
    status VARCHAR(20) DEFAULT 'Pending'
);


DELETE FROM users WHERE role='admin';

INSERT INTO users (name, email, password, role)
VALUES (
'Admin',
'admin@gmail.com',
'123456',
'admin'
);



ALTER TABLE appointments 
MODIFY status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending';




CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT,
    user_id INT,
    amount DECIMAL(10,2) DEFAULT 500.00,
    payment_method VARCHAR(50),
    payment_status VARCHAR(20) DEFAULT 'Pending',
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);