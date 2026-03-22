<?php
session_start();
include("config.php");

// Security: Only Principal (and NOT the dev account, though auth handles redirection)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'principal') {
    header("Location: login.php");
    exit();
}

// Fetch Teachers
$sql = "SELECT u.name AS teacher_name, s.subject_name, s.id AS subject_id 
        FROM users u 
        JOIN subjects s ON u.id = s.teacher_id 
        WHERE u.role = 'teacher'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Principal Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #fdfbf7; padding: 40px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        h1 { font-size: 2rem; margin: 0; }
        p.welcome { color: #666; margin: 5px 0 0 0; }
        
        .card { background: white; padding: 25px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card:hover { transform: translateY(-3px); }
        
        .btn-logout { background: #d9534f; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; }
        .btn-logout:hover { background: #c9302c; }
        
        .rating-box { background: #1a1a1a; color: white; padding: 12px 20px; border-radius: 8px; font-weight: bold; font-size: 1.2rem; }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <div>
                <h1>Principal Dashboard</h1>
                <p class="welcome">Welcome, <strong><?php echo $_SESSION['name']; ?></strong></p>
            </div>
            <a href="login.php" class="btn-logout">Logout</a>
        </div>

        <h2 style="margin-bottom: 25px;">Teacher Performance Overview</h2>

        <?php while ($row = mysqli_fetch_assoc($result)) { 
            $sub_id = $row['subject_id'];
            $avg_sql = "SELECT AVG((concept_clarity + teaching_pace + examples_explanation + doubt_handling)/4) as overall 
                        FROM feedback WHERE subject_id = '$sub_id'";
            $avg_res = mysqli_query($conn, $avg_sql);
            $avg_data = mysqli_fetch_assoc($avg_res);
            $score = $avg_data['overall'] ? number_format($avg_data['overall'], 1) : "0.0";
        ?>
            <div class="card">
                <div>
                    <h3 style="margin: 0 0 5px 0; font-size: 1.3rem;"><?php echo $row['teacher_name']; ?></h3>
                    <span style="color: #666; font-size: 0.95rem;"><?php echo $row['subject_name']; ?></span>
                </div>
                <div class="rating-box">
                    ★ <?php echo $score; ?> / 10
                </div>
            </div>
        <?php } ?>

    </div>
</body>
</html>