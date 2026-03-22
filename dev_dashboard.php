<?php
session_start();
include("config.php");

// 1. STRICT SECURITY: Only dev@admin.com allowed
if (!isset($_SESSION['user_id']) || $_SESSION['email'] !== 'dev@admin.com') {
    die("<div style='display:flex;justify-content:center;align-items:center;height:100vh;background:#000;color:red;font-family:monospace;'><h1>🚫 ACCESS DENIED: UNAUTHORIZED TERMINAL</h1></div>");
}

// 2. Handle Logic: DELETE SINGLE FEEDBACK
if (isset($_GET['delete_id'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM feedback WHERE id = '$del_id'");
    header("Location: dev_dashboard.php"); // Refresh to clear URL
    exit();
}

// 3. Handle Logic: RESET WEEK (Timer)
if (isset($_POST['reset_week'])) {
    $current_week = date('W');
    // Delete this week's data so students can submit again
    mysqli_query($conn, "DELETE FROM feedback WHERE week_number = '$current_week'");
    echo "<script>alert('✅ SYSTEM RESET: Weekly cycle cleared.'); window.location.href='dev_dashboard.php';</script>";
}

// 4. Fetch Data
$sql = "SELECT f.*, u.name as student_name, s.subject_name 
        FROM feedback f
        JOIN users u ON f.student_id = u.id
        JOIN subjects s ON f.subject_id = s.id
        ORDER BY f.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satoshi :: DEV_PROTOCOL</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            /* VIPER / ANACONDA PALETTE */
            --toxin-black: #050a07;
            --toxin-dark: #0d1410;
            --acid-green: #ccff00;       /* The signature Viper neon */
            --poison-teal: #00bfa5;
            --dim-green: #1a3320;
            --hud-border: rgba(204, 255, 0, 0.3);
            
            /* DANGER RED (Kept for reset) */
            --danger-red: #ff3333;
            --danger-bg: rgba(50, 0, 0, 0.8);

            --font-display: 'Orbitron', sans-serif;
            --font-hud: 'Rajdhani', sans-serif;
            --font-code: 'Share Tech Mono', monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--toxin-black);
            background-image: 
                linear-gradient(rgba(0, 20, 10, 0.9), rgba(0, 10, 5, 0.95)),
                url('https://www.transparenttextures.com/patterns/carbon-fibre.png');
            color: var(--acid-green);
            font-family: var(--font-hud);
            min-height: 100vh;
            padding: 30px;
            overflow-x: hidden;
        }

        /* --- CRT SCANLINE EFFECT --- */
        .scanlines {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,0) 50%, rgba(0,0,0,0.2) 50%, rgba(0,0,0,0.2));
            background-size: 100% 4px;
            pointer-events: none;
            z-index: 999;
            opacity: 0.6;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        /* --- HEADER GLITCH ANIMATION --- */
        @keyframes glitch-anim {
            0% { text-shadow: 2px 2px 0px var(--poison-teal); transform: translate(0); }
            20% { text-shadow: -2px -2px 0px var(--danger-red); transform: translate(-2px, 2px); }
            40% { text-shadow: 2px -2px 0px var(--acid-green); transform: translate(2px, -2px); }
            60% { text-shadow: -2px 2px 0px var(--poison-teal); transform: translate(-2px, 2px); }
            80% { text-shadow: 2px 2px 0px var(--danger-red); transform: translate(2px, -2px); }
            100% { text-shadow: -2px -2px 0px var(--acid-green); transform: translate(0); }
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 50px;
            border-bottom: 2px solid var(--acid-green);
            padding-bottom: 15px;
        }

        .brand h1 {
            font-family: var(--font-display);
            font-size: 3rem;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
        }
        
        .brand h1:hover {
            animation: glitch-anim 0.3s infinite;
            color: var(--acid-green);
        }

        .brand span { color: var(--acid-green); font-size: 1rem; vertical-align: middle; letter-spacing: 5px;}

        .user-panel {
            font-family: var(--font-code);
            color: var(--poison-teal);
            font-size: 1.1rem;
        }

        /* --- RED ZONE (RESET) --- */
        .control-panel {
            background: repeating-linear-gradient(
                45deg,
                rgba(255, 0, 0, 0.05),
                rgba(255, 0, 0, 0.05) 10px,
                rgba(0, 0, 0, 0.2) 10px,
                rgba(0, 0, 0, 0.2) 20px
            );
            border: 2px solid var(--danger-red);
            box-shadow: 0 0 20px rgba(255, 51, 51, 0.2);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            position: relative;
        }
        .control-panel::before {
            content: '⚠ DANGER ZONE';
            position: absolute;
            top: -12px;
            left: 20px;
            background: var(--toxin-black);
            color: var(--danger-red);
            padding: 0 10px;
            font-weight: bold;
            font-family: var(--font-display);
        }

        .btn-nuke {
            background: var(--danger-bg);
            color: #fff;
            border: 1px solid var(--danger-red);
            padding: 15px 30px;
            font-family: var(--font-display);
            letter-spacing: 2px;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
        }
        .btn-nuke:hover {
            background: var(--danger-red);
            box-shadow: 0 0 30px var(--danger-red);
            transform: scale(1.05);
        }

        /* --- THE TABLE (HUD STYLE) --- */
        .table-container {
            border: 1px solid var(--dim-green);
            background: rgba(13, 20, 16, 0.8);
            box-shadow: 0 0 30px rgba(204, 255, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px; /* Space between rows */
        }

        thead th {
            text-align: left;
            padding: 20px;
            color: var(--poison-teal);
            font-family: var(--font-code);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--dim-green);
        }

        tbody tr {
            background: rgba(255, 255, 255, 0.02);
            transition: 0.2s;
        }
        tbody tr:hover {
            background: rgba(204, 255, 0, 0.05);
            transform: translateX(5px);
            border-left: 2px solid var(--acid-green);
        }

        td {
            padding: 15px 20px;
            vertical-align: middle;
            border-top: 1px solid rgba(255,255,255,0.05);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        /* --- SECTION 1: ATTENDANCE (IDENTITY) --- */
        .col-identity {
            border-left: 3px solid var(--poison-teal);
        }
        .student-box {
            display: flex;
            flex-direction: column;
        }
        .id-tag {
            font-family: var(--font-code);
            font-size: 0.75rem;
            color: #666;
            margin-bottom: 2px;
        }
        .name-tag {
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
            text-shadow: 0 0 5px rgba(255,255,255,0.3);
        }
        .subject-tag {
            display: inline-block;
            margin-top: 5px;
            background: var(--dim-green);
            color: var(--acid-green);
            padding: 2px 8px;
            font-size: 0.8rem;
            border: 1px solid var(--acid-green);
            font-family: var(--font-code);
        }

        /* --- SECTION 2: Q METRICS (HUD) --- */
        .q-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 5px;
            background: #000;
            padding: 8px;
            border: 1px solid #333;
            width: fit-content;
        }
        .stat-box {
            text-align: center;
            position: relative;
        }
        .stat-label {
            font-size: 0.7rem;
            color: #666;
            display: block;
            margin-bottom: 2px;
        }
        .stat-value {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: bold;
        }
        /* Color coding the stats */
        .val-c { color: #d8b4fe; } /* Purple */
        .val-p { color: #60a5fa; } /* Blue */
        .val-e { color: #34d399; } /* Green */
        .val-d { color: #fbbf24; } /* Orange */

        /* --- SECTION 3: COMMENT LOG --- */
        .log-terminal {
            font-family: var(--font-code);
            color: #aaa;
            font-size: 0.9rem;
            max-width: 400px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
            padding-left: 15px;
        }
        .log-terminal::before {
            content: '>';
            color: var(--acid-green);
            position: absolute;
            left: 0;
            animation: blink 1s infinite;
        }
        @keyframes blink { 50% { opacity: 0; } }

        /* Full reveal on hover */
        .log-wrapper:hover .log-terminal {
            white-space: normal;
            background: #000;
            border: 1px solid var(--acid-green);
            position: absolute;
            z-index: 10;
            padding: 15px;
            width: 350px;
            box-shadow: 0 0 20px var(--acid-green);
            color: #fff;
        }

        /* --- TRASH BUTTON --- */
        .btn-trash {
            color: #444;
            transition: 0.3s;
            font-size: 1.2rem;
        }
        .btn-trash:hover {
            color: var(--danger-red);
            text-shadow: 0 0 10px var(--danger-red);
        }

        /* Empty State */
        .system-idle {
            text-align: center;
            padding: 50px;
            color: #444;
            font-family: var(--font-code);
            font-size: 1.5rem;
            letter-spacing: 3px;
        }

    </style>
</head>
<body>

<div class="scanlines"></div>

<div class="container">
    
    <div class="header">
        <div class="brand">
            <h1>Satoshi<span>_OS</span> // DEV_MODE</h1>
        </div>
        <div class="user-panel">
            [ USER: dev@admin.com ] <a href="login.php" style="color:var(--danger-red); text-decoration:none; margin-left:15px;">[ TERMINATE ]</a>
        </div>
    </div>

    <div class="control-panel">
        <div style="color: #ffcccc;">
            <strong style="color: var(--danger-red); font-size: 1.2rem;">WEEKLY PURGE PROTOCOL</strong><br>
            Current Cycle ID: <strong><?php echo date('W'); ?></strong>. Executing this command destroys all data.
        </div>
        <form method="POST" onsubmit="return confirm('⚠️ CRITICAL WARNING ⚠️\n\nSYSTEM OVERRIDE INITIATED.\nThis will delete all student data for this week.\n\nProceed?');">
            <button type="submit" name="reset_week" class="btn-nuke">
                <i class="fas fa-biohazard"></i> INITIATE RESET
            </button>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th width="25%">// SECTION_1: ATTENDANCE_ID</th>
                    <th width="25%">// SECTION_2: Q_METRICS_HUD</th>
                    <th width="40%">// SECTION_3: SYSTEM_LOGS (COMMENTS)</th>
                    <th width="10%">// OPS</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) { 
                ?>
                <tr>
                    <td class="col-identity">
                        <div class="student-box">
                            <span class="id-tag">ID_REF: #<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></span>
                            <span class="name-tag"><?php echo strtoupper($row['student_name']); ?></span>
                            <div><span class="subject-tag"><?php echo $row['subject_name']; ?></span></div>
                        </div>
                    </td>

                    <td>
                        <div class="q-grid">
                            <div class="stat-box">
                                <span class="stat-label">CNCPT</span>
                                <span class="stat-value val-c"><?php echo $row['concept_clarity']; ?></span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">PACE</span>
                                <span class="stat-value val-p"><?php echo $row['teaching_pace']; ?></span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">EXPL</span>
                                <span class="stat-value val-e"><?php echo $row['examples_explanation']; ?></span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">DOUBT</span>
                                <span class="stat-value val-d"><?php echo $row['doubt_handling']; ?></span>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="log-wrapper">
                            <div class="log-terminal">
                                <?php echo !empty($row['additional_comments']) ? htmlspecialchars($row['additional_comments']) : 'null_void_data'; ?>
                            </div>
                            <div style="font-size:0.7rem; color:#444; margin-top:5px; font-family:var(--font-code);">
                                TS: <?php echo date('Y-m-d H:i:s', strtotime($row['created_at'])); ?>
                            </div>
                        </div>
                    </td>

                    <td style="text-align: center;">
                        <a href="dev_dashboard.php?delete_id=<?php echo $row['id']; ?>" 
                           class="btn-trash" 
                           onclick="return confirm('DELETE ROW DATA?');">
                           <i class="fas fa-times-circle"></i>
                        </a>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='4' class='system-idle'>// NO DATA DETECTED IN FEEDBACK STREAM...</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>