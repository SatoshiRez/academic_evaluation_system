<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['subject_id'])) {
    $subject_id = mysqli_real_escape_string($conn, $_GET['subject_id']);
    $sub_sql = "SELECT subject_name, active_question FROM subjects WHERE id = '$subject_id'";
    $sub_result = mysqli_query($conn, $sub_sql);
    if ($row = mysqli_fetch_assoc($sub_result)) {
        $subject_name = $row['subject_name'];
        $display_question = !empty($row['active_question']) ? $row['active_question'] : "Mention one topic discussed this week:";
    } else {
        header("Location: student_dashboard.php");
        exit();
    }
} else {
    header("Location: student_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['user_id'];
    $clarity = $_POST['clarity'];
    $pace = $_POST['pace'];
    $examples = $_POST['examples'];
    $doubt = $_POST['doubt'];
    $attendance = $_POST['attendance'];
    $topic_response = mysqli_real_escape_string($conn, $_POST['topic_response']); 
    $raw_comment = mysqli_real_escape_string($conn, $_POST['additional_comments']);
    $full_comment = "Attendance: $attendance | Q: $display_question | A: $topic_response | Comment: $raw_comment";
    $week_number = date('W'); 

    $sql = "INSERT INTO feedback (student_id, subject_id, week_number, concept_clarity, teaching_pace, examples_explanation, doubt_handling, additional_comments) 
            VALUES ('$student_id', '$subject_id', '$week_number', '$clarity', '$pace', '$examples', '$doubt', '$full_comment')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Feedback Submitted Successfully!'); window.location.href='student_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error: You have already submitted feedback for this subject this week.'); window.location.href='student_dashboard.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Feedback</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-color: #fdfbf7; --text-dark: #1a1a1a; --text-grey: #4a4a4a; --accent-gold: #eadc9d; --accent-gold-dark: #d4c06b; --border-light: #eaeaea; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); background-image: linear-gradient(135deg, #fdfbf7 0%, #fff 100%); color: var(--text-dark); line-height: 1.6; padding-bottom: 80px; }
        .container { max-width: 900px; margin: 0 auto; padding: 0 20px; }
        h1 { font-size: 3rem; font-weight: 700; letter-spacing: -1px; color: var(--text-dark); line-height: 1.2; margin-bottom: 20px;}
        h2 { font-size: 1.8rem; font-weight: 600; margin-bottom: 0.5rem; letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px; }
        h2 i { font-size: 1.4rem; color: var(--accent-gold-dark); opacity: 0.8; }
        p { color: var(--text-grey); margin-bottom: 1.5rem; font-size: 1rem; }
        .badge { display: inline-block; background: #fff; border: 1px solid var(--accent-gold-dark); color: #8a7a28; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px; }
        .section-spacer { margin-top: 50px; padding-top: 50px; border-top: 1px solid rgba(0,0,0,0.05); }
        .hero { display: flex; align-items: center; justify-content: space-between; padding: 60px 0; gap: 40px; }
        .hero-text { flex: 1; }
        .hero-image { flex: 1; max-width: 60%; height: auto; border-radius: 20px; background-color: #eee; box-shadow: 15px 15px 0px rgba(234, 220, 157, 0.4); display: block; }
        .white-card { background: white; border: 1px solid var(--border-light); border-radius: 16px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); transition: transform 0.2s; }
        .styled-input, .styled-select { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; color: #333; background: #fff; outline: none; transition: border-color 0.2s; }
        .styled-input:focus, .styled-select:focus { border-color: var(--accent-gold-dark); box-shadow: 0 0 0 3px rgba(212, 192, 107, 0.2); }
        
        /* 10 Star System Styles */
        .stars { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 4px; } /* Smaller gap for 10 stars */
        .stars input { display: none; }
        .stars label { font-size: 1.5rem; color: #e0e0e0; cursor: pointer; transition: color 0.2s; } /* Smaller stars to fit */
        .stars label:hover, .stars label:hover ~ label { color: #ffb400; } 
        .stars input:checked ~ label { color: #ffb400; } 
        
        .rating-label { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: #999; margin-bottom: 5px; display: block; }
        .split-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 40px; align-items: center; }
        .top-questions-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 20px; }
        .submit-area { text-align: center; margin-top: 60px; padding-bottom: 60px; }
        .submit-btn { background-color: var(--text-dark); color: #fff; border: none; padding: 16px 45px; font-size: 1.1rem; font-weight: 600; border-radius: 50px; cursor: pointer; transition: all 0.2s; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .submit-btn:hover { background-color: #333; transform: translateY(-3px); box-shadow: 0 15px 25px rgba(0,0,0,0.15); }
        .privacy-note { margin-top: 20px; font-size: 0.9rem; color: #777; display: flex; align-items: center; justify-content: center; gap: 8px; }
        @media (max-width: 768px) { .hero, .split-grid, .top-questions-grid { flex-direction: column; display: flex; gap: 20px; } .hero { flex-direction: column-reverse; } h1 { font-size: 2.2rem; } .section-spacer { margin-top: 40px; padding-top: 40px; } .split-grid { align-items: flex-start; } .stars label { font-size: 1.8rem; } }
    </style>
</head>
<body>

<form action="feedback.php?subject_id=<?php echo $subject_id; ?>" method="POST">
    <div class="container">

        <section class="hero">
            <div class="hero-text">
                <span class="badge">Teacher Rating Board</span>
                <h1>Weekly Class<br>Feedback</h1>
                <p>Subject: <strong style="color: #000; font-size: 1.2rem;"><?php echo $subject_name; ?></strong></p>
                <p>Please fill out the details below to help us improve.</p>
            </div>
            <img src="ri.png" alt="Classroom Illustration" class="hero-image">
        </section>

        <!-- Attendance & Topics -->
        <section class="section-spacer">
            <div class="top-questions-grid">
                <div>
                    <h2><i class="far fa-calendar-check"></i> Attendance</h2>
                    <p>How many classes did you attend this week?</p>
                    <div class="white-card">
                        <select class="styled-select" name="attendance" required>
                            <option value="">Select Option</option>
                            <option value="0-1">0–1</option>
                            <option value="2-3">2–3</option>
                            <option value="4-5">4–5</option>
                            <option value="6+">6+</option>
                        </select>
                    </div>
                </div>

                <div>
                    <h2><i class="fas fa-book"></i> Topic / Question</h2>
                    <p><?php echo $display_question; ?></p> 
                    <div class="white-card">
                        <input type="text" class="styled-input" name="topic_response" placeholder="Your Answer..." required>
                    </div>
                </div>
            </div>
        </section>

        <!-- 10 STAR RATING SYSTEM -->
        
        <?php 
        // Helper function to generate 10 stars HTML to keep code clean
        function renderStars($name) {
            echo '<div class="stars">';
            for ($i = 10; $i >= 1; $i--) {
                echo '<input type="radio" name="'.$name.'" id="'.$name.$i.'" value="'.$i.'" required><label for="'.$name.$i.'">★</label>';
            }
            echo '</div>';
        }
        ?>

        <!-- Concept Clarity -->
        <section class="section-spacer">
            <div class="split-grid">
                <div>
                    <h2><i class="far fa-lightbulb"></i> Concept Clarity</h2>
                    <p>Rate how clearly the concepts were explained (1-10).</p>
                </div>
                <div class="white-card">
                    <span class="rating-label">Rate 1-10 Stars</span>
                    <?php renderStars('clarity'); ?>
                </div>
            </div>
        </section>

        <!-- Teaching Pace -->
        <section class="section-spacer">
            <div class="split-grid">
                <div>
                    <h2><i class="fas fa-running"></i> Teaching Pace</h2>
                    <p>Was the speed of the class appropriate? (1-10)</p>
                </div>
                <div class="white-card">
                    <span class="rating-label">Rate 1-10 Stars</span>
                    <?php renderStars('pace'); ?>
                </div>
            </div>
        </section>

        <!-- Examples & Explanation -->
        <section class="section-spacer">
            <div class="split-grid">
                <div>
                    <h2><i class="fas fa-chalkboard-teacher"></i> Examples & Explanation</h2>
                    <p>Did the examples help you understand? (1-10)</p>
                </div>
                <div class="white-card">
                    <span class="rating-label">Rate 1-10 Stars</span>
                    <?php renderStars('examples'); ?>
                </div>
            </div>
        </section>

        <!-- Doubt Handling -->
        <section class="section-spacer">
            <div class="split-grid">
                <div>
                    <h2><i class="far fa-question-circle"></i> Doubt Handling</h2>
                    <p>Did you feel comfortable asking questions? (1-10)</p>
                </div>
                <div class="white-card">
                    <span class="rating-label">Rate 1-10 Stars</span>
                    <?php renderStars('doubt'); ?>
                </div>
            </div>
        </section>

        <!-- Additional Comments -->
        <section class="section-spacer">
            <div style="max-width: 800px; margin: 0 auto;">
                <h2><i class="far fa-comment-dots"></i> Additional Comments</h2>
                <span class="badge" style="border-color: #ddd; color: #888;">OPTIONAL</span>
                <p>This is your space to share specific suggestions.</p>
                <div class="white-card" style="padding: 0; overflow: hidden;">
                    <textarea rows="5" name="additional_comments" placeholder="Write your feedback here..." 
                    style="width:100%; padding: 25px; border:none; outline:none; font-size:1rem; line-height: 1.6; resize: vertical;"></textarea>
                </div>
            </div>
        </section>

        <div class="submit-area">
            <button type="submit" class="submit-btn">Submit Feedback</button>
            <div class="privacy-note">
                <i class="fas fa-shield-alt"></i> Feedback is <strong>Anonymous</strong> and Confidential.
            </div>
        </div>

    </div> 
</form>

</body>
</html>