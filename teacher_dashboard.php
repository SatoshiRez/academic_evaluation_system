<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Fetch Subject
$sub_sql = "SELECT * FROM subjects WHERE teacher_id = '$teacher_id'";
$sub_result = mysqli_query($conn, $sub_sql);
$sub_data = mysqli_fetch_assoc($sub_result);

if (!$sub_data) { die("Error: No subject assigned."); }

$subject_id = $sub_data['id'];
$subject_name = $sub_data['subject_name'];
$current_db_question = $sub_data['active_question'];

// Handle Question Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_question'])) {
    $new_question = mysqli_real_escape_string($conn, $_POST['custom_question']);
    $update_sql = empty(trim($new_question)) 
        ? "UPDATE subjects SET active_question = NULL WHERE id = '$subject_id'"
        : "UPDATE subjects SET active_question = '$new_question' WHERE id = '$subject_id'";
    
    if(mysqli_query($conn, $update_sql)) {
        header("Location: teacher_dashboard.php?msg=success");
        exit();
    }
}

// --- AUTOMATIC WEEKLY RESET LOGIC ---
$current_week = date('W'); // Gets current week number (1-52)

$stats_sql = "SELECT 
                AVG(concept_clarity) as avg_clarity,
                AVG(teaching_pace) as avg_pace,
                AVG(examples_explanation) as avg_examples,
                AVG(doubt_handling) as avg_doubt,
                COUNT(*) as total_responses
              FROM feedback 
              WHERE subject_id = '$subject_id' 
              AND week_number = '$current_week'"; // <--- THIS FILTERS BY CURRENT WEEK

$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

function fmt($val) { return $val ? number_format($val, 1) : "0.0"; }
$overall_score = ($stats['avg_clarity'] + $stats['avg_pace'] + $stats['avg_examples'] + $stats['avg_doubt']) / 4;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: #fdfbf7; background-image: linear-gradient(135deg, #fdfbf7 0%, #fffaf0 100%); color: #222; }
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .logout-btn { text-decoration: none; color: #d9534f; font-weight: 600; border: 1px solid #d9534f; padding: 8px 16px; border-radius: 20px; transition: all 0.2s; }
        .logout-btn:hover { background-color: #d9534f; color: white; }
        h1 { font-size: 2.5rem; font-weight: 700; color: #1a1a1a; margin-bottom: 5px; }
        p.subtitle { color: #666; font-size: 1.1rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #eaeaea; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.03); }
        .stat-val { font-size: 2.5rem; font-weight: 700; color: #1a1a1a; display: block; margin-top: 10px; }
        .stat-label { font-size: 0.9rem; color: #666; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .question-section { background: white; padding: 30px; border-radius: 16px; border: 1px solid #eaeaea; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 40px; }
        .q-input { width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; margin: 15px 0; font-family: 'Inter', sans-serif; }
        .btn-update { background: #1a1a1a; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background 0.2s; }
        .btn-update:hover { background: #333; }
        .success-box { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-weight: 600; }
        .timer-badge { background: #1a1a1a; color: white; padding: 8px 15px; border-radius: 20px; font-size: 0.9rem; margin-left: 10px; font-weight: 600; }

        /* Updated Badge Logic for 10 Point Scale */
        .overall-badge { 
            background: <?php echo ($overall_score >= 8) ? '#d4edda' : (($overall_score >= 5) ? '#fff3cd' : '#f8d7da'); ?>;
            color: <?php echo ($overall_score >= 8) ? '#155724' : (($overall_score >= 5) ? '#856404' : '#721c24'); ?>;
            padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: 700;
        }
        @media (max-width: 768px) { .stats-grid { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>Welcome, <?php echo $_SESSION['name']; ?></h1>
            <p class="subtitle">
                Subject: <strong><?php echo $subject_name; ?></strong> | 
                Week: <strong><?php echo $current_week; ?></strong> |
                Timer: <span id="countdown" class="timer-badge">Loading...</span>
            </p>
        </div>
        <a href="login.php" class="logout-btn">Logout</a>
    </div>

    <div class="question-section">
        <h2 style="font-size: 1.5rem; margin-bottom: 10px;">Weekly Question</h2>
        <p style="color: #666;">Set a custom question for your students this week.</p>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
            <div class="success-box">✅ Question Updated Successfully!</div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="custom_question" class="q-input" placeholder="Default: What topic was discussed this week?" value="<?php echo htmlspecialchars($current_db_question); ?>">
            <button type="submit" name="update_question" class="btn-update">Update Question</button>
        </form>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><span class="stat-label">Concept Clarity</span><span class="stat-val" style="color: #4e73df;"><?php echo fmt($stats['avg_clarity']); ?></span></div>
        <div class="stat-card"><span class="stat-label">Teaching Pace</span><span class="stat-val" style="color: #1cc88a;"><?php echo fmt($stats['avg_pace']); ?></span></div>
        <div class="stat-card"><span class="stat-label">Examples</span><span class="stat-val" style="color: #36b9cc;"><?php echo fmt($stats['avg_examples']); ?></span></div>
        <div class="stat-card"><span class="stat-label">Doubt Handling</span><span class="stat-val" style="color: #f6c23e;"><?php echo fmt($stats['avg_doubt']); ?></span></div>
    </div>

    <div class="stat-card" style="max-width: 400px; margin: 0 auto; border-color: #1a1a1a;">
        <span class="stat-label">Total Responses (This Week)</span>
        <span class="stat-val"><?php echo $stats['total_responses']; ?></span>
        <div style="margin-top: 15px;">
            Overall Rating: <span class="overall-badge"><?php echo fmt($overall_score); ?> / 10.0</span>
        </div>
    </div>
</div>

<script>
    function updateTimer() {
        const now = new Date();
        const nextSunday = new Date();
        nextSunday.setDate(now.getDate() + (7 - now.getDay()) % 7);
        if(now.getDay() === 0 && now.getHours() > 0) nextSunday.setDate(now.getDate() + 7);
        nextSunday.setHours(23, 59, 59, 0);
        const diff = nextSunday - now;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        document.getElementById("countdown").innerText = `${days}d ${hours}h left`;
    }
    setInterval(updateTimer, 1000);
    updateTimer();
</script>
</body>
</html>