<?php
session_start();
include("config.php");

// 1. Get Data from Form
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = mysqli_real_escape_string($conn, $_POST['password']);
$selected_role = strtolower($_POST['role']); 

// 2. Check Database
$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {
    
    $user = mysqli_fetch_assoc($result);

    // 3. Security Check: Role Match
    if ($user['role'] == $selected_role) {
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email']; // Added email to session for checking

        // --- ROUTING LOGIC ---
        
        if ($user['role'] == 'student') {
            header("Location: student_dashboard.php");
        }
        elseif ($user['role'] == 'teacher') {
            header("Location: teacher_dashboard.php");
        }
        elseif ($user['role'] == 'principal') {
            // SPECIAL CHECK: Is this the Developer?
            if ($user['email'] === 'dev@admin.com') {
                header("Location: dev_dashboard.php");
            } else {
                header("Location: principal_dashboard.php");
            }
        }
        exit();

    } else {
        echo "<script>alert('Role mismatch! Please select the correct role.'); window.location.href='login.php';</script>";
    }

} else {
    echo "<script>alert('Invalid Email or Password!'); window.location.href='login.php';</script>";
}
?>