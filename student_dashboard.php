<?php
session_start();
include("config.php");

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$current_week = date('W');

// 2. CHECK SUBMISSION STATUS FOR ALL SUBJECTS
// We want an array like: [1 => true, 2 => false, ...] meaning Subject ID 1 is submitted.
$submitted_subjects = [];
$check_sql = "SELECT subject_id FROM feedback WHERE student_id = '$student_id' AND week_number = '$current_week'";
$check_result = mysqli_query($conn, $check_sql);

while($row = mysqli_fetch_assoc($check_result)) {
    $submitted_subjects[$row['subject_id']] = true;
}

// 3. Helper function to check status
function isSubmitted($id, $list) {
    return isset($list[$id]) && $list[$id] === true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: #fdfbf7; background-image: linear-gradient(135deg, #fdfbf7 0%, #fffaf0 100%); color: #222; }

        .logout-container { position: absolute; top: 20px; right: 40px; }
        .logout-btn { text-decoration: none; color: #d9534f; font-weight: 600; border: 1px solid #d9534f; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; transition: all 0.2s; }
        .logout-btn:hover { background-color: #d9534f; color: white; }

        .hero-section { display: flex; min-height: 80vh; width: 100%; align-items: center; gap: 40px; padding: 60px 80px; }
        .image-side { flex: 4; text-align: left; }
        .text-side { flex: 6; display: flex; flex-direction: column; justify-content: center; padding: 60px 80px; }
        
        h1 { font-size: 3.5rem; line-height: 1.1; margin-bottom: 1rem; color: #1a1a1a; font-weight: 700; letter-spacing: -1px; }
        .hero-desc { font-size: 1.125rem; line-height: 1.6; color: #4a4a4a; margin-bottom: 20px; }

        /* Timer Box */
        .timer-box {
            background: #1a1a1a; color: #fff; padding: 15px 25px; border-radius: 12px; 
            display: inline-block; font-weight: 600; margin-top: 10px; font-size: 0.95rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .subjects-section { padding: 40px 80px 80px 80px; max-width: 1400px; margin: 0 auto; }
        h2 { font-size: 2.5rem; margin-bottom: 3rem; color: #1a1a1a; font-weight: 600; letter-spacing: -0.5px; }
        .subjects-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; }

        /* CARD STYLES */
        .subject-card {
            display: flex; flex-direction: row; gap: 15px; align-items: flex-start;
            text-decoration: none; color: inherit; padding: 15px; border-radius: 12px;
            background: white; border: 1px solid transparent; transition: all 0.2s;
        }

        /* Normal State */
        .subject-card:not(.submitted):hover {
            transform: translateY(-5px); background-color: white; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.05); cursor: pointer;
        }

        /* Submitted State (Disabled) */
        .subject-card.submitted {
            background-color: #f0fff4; /* Light Green */
            border-color: #c3e6cb;
            opacity: 0.8;
            cursor: default;
            pointer-events: none; /* Make unclickable */
        }

        .subject-icon { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; flex-shrink: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .subject-text h3 { font-size: 1.1rem; margin-bottom: 0.5rem; color: #1a1a1a; font-weight: 700; }
        .subject-text p { font-size: 0.95rem; line-height: 1.5; color: #555; }
        
        .tick-icon { color: #28a745; font-size: 1.2rem; margin-left: auto; display: none; }
        .submitted .tick-icon { display: block; }

        @media (max-width: 1024px) { .subjects-grid { grid-template-columns: repeat(2, 1fr); } h1 { font-size: 2.8rem; } }
        @media (max-width: 768px) { .hero-section { flex-direction: column; height: auto; padding: 40px 20px; } .subjects-grid { grid-template-columns: 1fr; } .timer-box { font-size: 0.85rem; } }
    </style>
</head>
<body>

    <div class="logout-container"><a href="login.php" class="logout-btn">Logout</a></div>

    <section class="hero-section">
        <div class="image-side">
            <img src="lap.PNG" alt="Workspace" style="max-width:80%; height:auto; border-radius:12px; margin-bottom:10px; box-shadow: 10px 10px 0px rgba(0,0,0,0.05);">
        </div>
        <div class="text-side">
            <h1>Welcome, <?php echo $_SESSION['name']; ?>!</h1>
            <p class="hero-desc">Access your subjects below. Once feedback is submitted, it is locked until the next cycle (Sunday).</p>
            
            <!-- TIMER -->
            <div class="timer-box">
                <i class="far fa-clock"></i> Next Feedback Cycle in: <span id="countdown">Loading...</span>
            </div>
        </div>
    </section>

    <section class="subjects-section">
        <h2>Your Subjects</h2>
        <div class="subjects-grid">
            
            <!-- Subject 1: Web Tech (ID 2) -->
            <?php $isDone = isSubmitted(2, $submitted_subjects); ?>
            <a href="<?php echo $isDone ? '#' : 'feedback.php?subject_id=2'; ?>" class="subject-card <?php echo $isDone ? 'submitted' : ''; ?>">
                <img src="https://images.unsplash.com/photo-1547658719-da2b51169166?q=80&w=200&auto=format&fit=crop" class="subject-icon">
                <div class="subject-text">
                    <h3>Web Technology</h3>
                    <p><?php echo $isDone ? 'Feedback Submitted Successfully' : 'Frontend and backend development'; ?></p>
                </div>
                <i class="fas fa-check-circle tick-icon"></i>
            </a>

            <!-- Subject 2: TOC (ID 4) -->
            <?php $isDone = isSubmitted(4, $submitted_subjects); ?>
            <a href="<?php echo $isDone ? '#' : 'feedback.php?subject_id=4'; ?>" class="subject-card <?php echo $isDone ? 'submitted' : ''; ?>">
                <img src="https://images.unsplash.com/photo-1635070041078-e363dbe005cb?q=80&w=200&auto=format&fit=crop" class="subject-icon">
                <div class="subject-text">
                    <h3>Theory of Comp</h3>
                    <p><?php echo $isDone ? 'Feedback Submitted Successfully' : 'Complexity theory & algorithms'; ?></p>
                </div>
                <i class="fas fa-check-circle tick-icon"></i>
            </a>

            <!-- Subject 3: AI (ID 3) -->
            <?php $isDone = isSubmitted(3, $submitted_subjects); ?>
            <a href="<?php echo $isDone ? '#' : 'feedback.php?subject_id=3'; ?>" class="subject-card <?php echo $isDone ? 'submitted' : ''; ?>">
                <img src="https://images.unsplash.com/photo-1677442136019-21780ecad995?q=80&w=200&auto=format&fit=crop" class="subject-icon">
                <div class="subject-text">
                    <h3>AI</h3>
                    <p><?php echo $isDone ? 'Feedback Submitted Successfully' : 'Neural networks & ML systems'; ?></p>
                </div>
                <i class="fas fa-check-circle tick-icon"></i>
            </a>

            <!-- Subject 4: Python (ID 1) -->
            <?php $isDone = isSubmitted(1, $submitted_subjects); ?>
            <a href="<?php echo $isDone ? '#' : 'feedback.php?subject_id=1'; ?>" class="subject-card <?php echo $isDone ? 'submitted' : ''; ?>">
                <img src="https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?q=80&w=200&auto=format&fit=crop" class="subject-icon">
                <div class="subject-text">
                    <h3>Python</h3>
                    <p><?php echo $isDone ? 'Feedback Submitted Successfully' : 'Data science & automation'; ?></p>
                </div>
                <i class="fas fa-check-circle tick-icon"></i>
            </a>

        </div>
    </section>

    <!-- JavaScript Timer Logic -->
    <script>
        function updateTimer() {
            const now = new Date();
            const nextSunday = new Date();
            
            // Set to next Sunday
            nextSunday.setDate(now.getDate() + (7 - now.getDay()) % 7);
            if(now.getDay() === 0 && now.getHours() > 0) { // If today is Sunday, move to next week
                 nextSunday.setDate(now.getDate() + 7);
            }
            nextSunday.setHours(23, 59, 59, 0); // End of Sunday

            const diff = nextSunday - now;

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

            document.getElementById("countdown").innerText = `${days}d ${hours}h ${minutes}m`;
        }
        setInterval(updateTimer, 1000);
        updateTimer();
    </script>

</body>
</html>