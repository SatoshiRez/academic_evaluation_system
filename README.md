# Academic Evaluation System

A simple **web-based Academic/Teacher Evaluation System** built using **PHP, MySQL, HTML, and CSS**.
The system allows students to submit feedback and allows teachers and administrators to view evaluation results.

## 🚀 Features

* Student login system
* Teacher dashboard
* Principal/Admin dashboard
* Feedback submission by students
* Database-driven evaluation system
* Simple and clean interface

## 🛠 Technologies Used

* **Frontend:** HTML, CSS
* **Backend:** PHP
* **Database:** MySQL
* **Server:** XAMPP (Apache)

## 📂 Project Structure

academic_evaluation_system/

* login.php – User login page
* authenticate.php – Authentication logic
* config.php – Database connection
* student_dashboard.php – Student interface
* teacher_dashboard.php – Teacher interface
* principal_dashboard.php – Admin/Principal dashboard
* feedback.php – Feedback submission system
* images & assets – UI images and fonts
* teacher_evaluation_system.sql – Database file

## ⚙️ Installation & Setup

Follow these steps to run the project locally.

### 1. Install XAMPP

Download and install XAMPP:
https://www.apachefriends.org

### 2. Move Project Folder

Copy the project folder to:

xampp/htdocs/

Example:
xampp/htdocs/academic_evaluation_system

### 3. Start Server

Open **XAMPP Control Panel** and start:

* Apache
* MySQL

### 4. Setup Database

1. Open **phpMyAdmin**
2. Create a new database:

teacher_evaluation_system

3. Import the file:

teacher_evaluation_system.sql

### 5. Configure Database Connection

Open `config.php` and ensure the connection details are correct:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "teacher_evaluation_system";
```

### 6. Run the Project

Open your browser and go to:

http://localhost/academic_evaluation_system/login.php

## 👥 User Roles

* **Student:** Submit feedback about teachers
* **Teacher:** View evaluation results
* **Principal/Admin:** Monitor overall feedback and performance

## 📌 Notes

* Make sure Apache and MySQL are running in XAMPP.
* Import the provided SQL file before running the system.
* Default database user in XAMPP is `root` with no password.

## 📄 License

This project is for **educational purposes**.
